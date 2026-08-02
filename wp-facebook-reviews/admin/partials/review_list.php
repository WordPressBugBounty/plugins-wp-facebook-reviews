<?php

/**
 * Provide a admin area view for the plugin
 *
 * Review List — hide, edit, delete, media thumbnails, and Facebook/Twitter columns.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    WP_FB_Reviews
 * @subpackage WP_FB_Reviews/admin/partials
 */

// check user capabilities
if ( ! current_user_can( 'manage_options' ) ) {
	return;
}

$nonce = wp_create_nonce( 'my-nonce' );
$html  = '';
// db function variables
global $wpdb;
$table_name  = $wpdb->prefix . 'wpfb_reviews';
$rowsperpage = 20;

$dbmsg         = '';
$currentreview = new stdClass();
$currentreview->id                 = '';
$currentreview->rating             = '';
$currentreview->review_title       = '';
$currentreview->review_text        = '';
$currentreview->reviewer_name      = '';
$currentreview->reviewer_id        = '';
$currentreview->created_time       = '';
$currentreview->created_time_stamp = '';
$currentreview->userpic            = '';
$currentreview->userpiclocal       = '';
$currentreview->review_length      = '';
$currentreview->type               = '';
$currentreview->pagename           = '';
$currentreview->from_url           = '';
$currentreview->hide               = '';

// Neutral gray silhouette used when a review has no avatar to show.
$default_avatar = 'data:image/svg+xml;charset=UTF-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%2060%2060%22%3E%3Crect%20width%3D%2260%22%20height%3D%2260%22%20fill%3D%22%23e4e6eb%22%2F%3E%3Ccircle%20cx%3D%2230%22%20cy%3D%2223%22%20r%3D%2211%22%20fill%3D%22%23b0b3b8%22%2F%3E%3Cpath%20d%3D%22M10%2054c0-11%209-18%2020-18s20%207%2020%2018z%22%20fill%3D%22%23b0b3b8%22%2F%3E%3C%2Fsvg%3E';

/**
 * Verify action nonce; on failure ignore the action instead of dying the whole admin page.
 *
 * @return bool
 */
$wpfb_action_nonce_ok = static function () {
	$nonce_check = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '';
	return (bool) wp_verify_nonce( $nonce_check, 'my-nonce' );
};

// Load review for editing.
if ( isset( $_GET['editrev'] ) ) {
	if ( ! $wpfb_action_nonce_ok() ) {
		unset( $_GET['editrev'] );
	} else {
		$rid = absint( $_GET['editrev'] );
		if ( $rid > 0 ) {
			$currentreview = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table_name} WHERE id = %d", $rid ) );
			if ( ! $currentreview ) {
				$currentreview     = new stdClass();
				$currentreview->id = '';
			}
		}
	}
}

// Delete one review (GET fallback when JS/AJAX is unavailable).
if ( isset( $_GET['deleterev'] ) ) {
	if ( $wpfb_action_nonce_ok() ) {
		$rid = absint( $_GET['deleterev'] );
		if ( $rid > 0 ) {
			$wpdb->delete( $table_name, array( 'id' => $rid ), array( '%d' ) );
		}
	}
}

// Hide / unhide one review (GET fallback when JS/AJAX is unavailable).
if ( isset( $_GET['hiderev'] ) ) {
	if ( $wpfb_action_nonce_ok() ) {
		$rid      = absint( $_GET['hiderev'] );
		$newvalue = isset( $_GET['newvalue'] ) ? sanitize_text_field( wp_unslash( $_GET['newvalue'] ) ) : '';
		if ( $newvalue !== 'yes' ) {
			$newvalue = '';
		}
		if ( $rid > 0 ) {
			$wpdb->update(
				$table_name,
				array( 'hide' => $newvalue ),
				array( 'id' => $rid ),
				array( '%s' ),
				array( '%d' )
			);
		}
	}
}

// Remove all Facebook reviews (and their cached avatars).
if ( isset( $_GET['opt'] ) && $_GET['opt'] === 'delallfb' ) {
	if ( $wpfb_action_nonce_ok() ) {
		$wpdb->query( "DELETE FROM `{$table_name}` WHERE `type` = 'Facebook'" );

		// delete all cached avatars (used for FB images)
		$img_locations_option = json_decode( get_option( 'wprev_img_locations' ), true );
		if ( is_array( $img_locations_option ) && ! empty( $img_locations_option['upload_dir_wprev_avatars'] ) ) {
			$avatar_dir = $img_locations_option['upload_dir_wprev_avatars'];
			$localfiles = glob( $avatar_dir . '*' );
			if ( is_array( $localfiles ) ) {
				foreach ( $localfiles as $file ) {
					if ( is_file( $file ) ) {
						unlink( $file );
					}
				}
			}
		}
	}
}

// Remove all Twitter reviews.
if ( isset( $_GET['opt'] ) && $_GET['opt'] === 'delalltw' ) {
	if ( $wpfb_action_nonce_ok() ) {
		$wpdb->query( "DELETE FROM `{$table_name}` WHERE `type` = 'Twitter'" );
	}
}

// Remove all reviews (and their cached avatars).
if ( isset( $_GET['opt'] ) && $_GET['opt'] === 'delall' ) {
	if ( $wpfb_action_nonce_ok() ) {
		$wpdb->query( "DELETE FROM `{$table_name}`" );

		$img_locations_option = json_decode( get_option( 'wprev_img_locations' ), true );
		if ( is_array( $img_locations_option ) && ! empty( $img_locations_option['upload_dir_wprev_avatars'] ) ) {
			$avatar_dir = $img_locations_option['upload_dir_wprev_avatars'];
			$localfiles = glob( $avatar_dir . '*' );
			if ( is_array( $localfiles ) ) {
				foreach ( $localfiles as $file ) {
					if ( is_file( $file ) ) {
						unlink( $file );
					}
				}
			}
		}
	}
}

// Save edited review (avatar URL + date) — POST fallback if JS/AJAX is unavailable.
if ( isset( $_POST['wprevpro_submitreviewbtn'] ) ) {
	check_admin_referer( 'wprevpro_save_review' );
	$r_id       = isset( $_POST['editrid'] ) ? absint( $_POST['editrid'] ) : 0;
	$avatar_url = isset( $_POST['wprevpro_nr_avatar_url'] ) ? esc_url_raw( wp_unslash( $_POST['wprevpro_nr_avatar_url'] ) ) : '';
	$rdate_raw  = isset( $_POST['wprevpro_nr_date'] ) ? sanitize_text_field( wp_unslash( $_POST['wprevpro_nr_date'] ) ) : '';

	if ( $r_id > 0 ) {
		// Update both userpic and userpiclocal so the front-end (which prefers the
		// local copy for Facebook avatars) reflects the edited image.
		$data   = array(
			'userpic'      => $avatar_url,
			'userpiclocal' => $avatar_url,
		);
		$format = array( '%s', '%s' );

		$parsed_stamp = $rdate_raw !== '' ? strtotime( $rdate_raw ) : false;
		if ( $parsed_stamp ) {
			$data['created_time']       = date( 'Y-m-d H:i:s', $parsed_stamp );
			$data['created_time_stamp'] = $parsed_stamp;
			$format[]                   = '%s';
			$format[]                   = '%d';
		}

		$updatetempquery = $wpdb->update(
			$table_name,
			$data,
			array( 'id' => $r_id ),
			$format,
			array( '%d' )
		);
		if ( false !== $updatetempquery ) {
			$dbmsg = '<div id="setting-error-wprevpro_message" class="updated settings-error notice is-dismissible"><p><strong>' . esc_html__( 'Review Updated!', 'wp-fb-reviews' ) . '</strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
		}

		$currentreview     = new stdClass();
		$currentreview->id = '';
	}
}
?>
<div class="">
<h1></h1>
<div class="wrap" id="wp_rev_maindiv">

	<img class="wprev_headerimg" src="<?php echo plugin_dir_url( __FILE__ ) . 'logo.png?id=' . $this->_token; ?>">

<?php
include 'tabmenu.php';
?>
<div class="welcomecontainer wpfbr_margin10 w3-row-padding w3-section w3-stretch">

<div class="w3-col w3-container ">
<div class="welcomediv w3-white w3-border w3-border-light-gray2 w3-round-small">

<div class="wpfbr_margin10">
	<a id="wpfbr_helpicon" class="wpfbr_btnicononly button dashicons-before dashicons-editor-help"></a>
	<a id="wpfbr_removeallbtn" data-sec="<?php echo esc_attr( $nonce ); ?>" class="button dashicons-before dashicons-no"><?php _e( 'Remove All Reviews', 'wp-fb-reviews' ); ?></a>
	<p>
	<?php
	esc_html_e( 'Click the eye icon to hide or show a review, the wrench to edit the reviewer photo, or the trash icon to delete. More features are available in the', 'wp-fb-reviews' );
	?>
	<a href="?page=wp_fb-get_pro"><?php esc_html_e( 'Pro Version', 'wp-fb-reviews' ); ?></a>.
	</p>
	<div id="wprevpro_notices_area"><?php echo $dbmsg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- admin notice built above ?></div>
</div>

<div class="wprevpro_modal_overlay<?php echo empty( $currentreview->id ) ? '' : ' is-open'; ?>" id="wpfb_new_review">
<div class="wprevpro_modal_box wpfbr_margin10 w3-container w3-white w3-round-small">
<button type="button" class="wprevpro_modal_closebtn" id="wpfb_modal_closebtn" aria-label="<?php esc_attr_e( 'Close', 'wp-fb-reviews' ); ?>">&times;</button>
<h2><?php esc_html_e( 'Edit Review', 'wp-fb-reviews' ); ?></h2>
<div id="wprevpro_save_review_msg"></div>
<form name="newreviewform" id="newreviewform" action="?page=wpfb-reviews" method="post">
	<table class="form-table ">
		<tbody>
			<tr class="wprevpro_row">
				<th scope="row">
					<?php esc_html_e( 'Review Rating (1 - 5):', 'wp-fb-reviews' ); ?>
				</th>
				<td><div id="divtemplatestyles">
				<?php $tempdisable = 'disabled'; ?>
					<input type="radio" name="wprevpro_nr_rating" id="wprevpro_nr_rating1-radio" value="1" <?php checked( isset( $currentreview->rating ) ? (string) $currentreview->rating : '', '1' ); ?> <?php echo $tempdisable; ?>>
					<label for="wprevpro_nr_rating1-radio">1</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<input type="radio" name="wprevpro_nr_rating" id="wprevpro_nr_rating2-radio" value="2" <?php checked( isset( $currentreview->rating ) ? (string) $currentreview->rating : '', '2' ); ?> <?php echo $tempdisable; ?>>
					<label for="wprevpro_nr_rating2-radio">2</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<input type="radio" name="wprevpro_nr_rating" id="wprevpro_nr_rating3-radio" value="3" <?php checked( isset( $currentreview->rating ) ? (string) $currentreview->rating : '', '3' ); ?> <?php echo $tempdisable; ?>>
					<label for="wprevpro_nr_rating3-radio">3</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<input type="radio" name="wprevpro_nr_rating" id="wprevpro_nr_rating4-radio" value="4" <?php checked( isset( $currentreview->rating ) ? (string) $currentreview->rating : '', '4' ); ?> <?php echo $tempdisable; ?>>
					<label for="wprevpro_nr_rating4-radio">4</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<input type="radio" name="wprevpro_nr_rating" id="wprevpro_nr_rating5-radio" value="5" <?php checked( isset( $currentreview->rating ) ? (string) $currentreview->rating : '', '5' ); ?> <?php echo $tempdisable; ?>>
					<label for="wprevpro_nr_rating5-radio">5</label>
					</div>
				</td>
			</tr>
			<tr class="wprevpro_row">
				<th scope="row">
					<?php esc_html_e( 'Review Text:', 'wp-fb-reviews' ); ?>
				</th>
				<td>
					<textarea name="wprevpro_nr_text" id="wprevpro_nr_text" cols="50" rows="4" readonly><?php echo esc_textarea( isset( $currentreview->review_text ) ? $currentreview->review_text : '' ); ?></textarea>
				</td>
			</tr>
			<tr class="wprevpro_row">
				<th scope="row">
					<?php esc_html_e( 'Reviewer Name:', 'wp-fb-reviews' ); ?>
				</th>
				<td>
					<input id="wprevpro_nr_name" type="text" name="wprevpro_nr_name" value="<?php echo esc_attr( isset( $currentreview->reviewer_name ) ? $currentreview->reviewer_name : '' ); ?>" readonly class="regular-text">
				</td>
			</tr>
			<tr class="wprevpro_row">
				<th scope="row">
					<?php esc_html_e( 'Page Name:', 'wp-fb-reviews' ); ?>
				</th>
				<td>
					<input id="wprevpro_nr_pagename" type="text" name="wprevpro_nr_pagename" value="<?php echo esc_attr( isset( $currentreview->pagename ) ? $currentreview->pagename : '' ); ?>" readonly class="regular-text">
				</td>
			</tr>
			<tr class="wprevpro_row">
				<th scope="row">
					<?php esc_html_e( 'Reviewer Pic URL:', 'wp-fb-reviews' ); ?>
				</th>
				<td>
					<?php
					$edit_pic = '';
					if ( ! empty( $currentreview->userpiclocal ) ) {
						$edit_pic = $currentreview->userpiclocal;
					} elseif ( ! empty( $currentreview->userpic ) ) {
						$edit_pic = $currentreview->userpic;
					} else {
						$edit_pic = $default_avatar;
					}
					?>
					<input id="wprevpro_nr_avatar_url" type="text" name="wprevpro_nr_avatar_url" value="<?php echo esc_attr( $edit_pic ); ?>" class="regular-text">
					<a id="upload_avatar_button" class="button"><?php esc_html_e( 'Upload', 'wp-fb-reviews' ); ?></a>
					<br><p class="description">
					<?php esc_html_e( 'Avatar for the person who wrote the review. Click the following image to insert a generic avatar URL.', 'wp-fb-reviews' ); ?>
					</p>
					<div class="avatar_images_list">
					<img src="<?php echo esc_attr( $default_avatar ); ?>" alt="thumb" class="rlimg default_avatar_img">&nbsp;&nbsp;&nbsp;
					</div>
					</br>
					<img class="" height="100px" id="avatar_preview" src="<?php echo esc_attr( $edit_pic ); ?>" alt="">
				</td>
			</tr>
			<tr class="wprevpro_row">
				<th scope="row">
					<?php esc_html_e( 'Review Date:', 'wp-fb-reviews' ); ?>
				</th>
				<td>
					<input id="wprevpro_nr_date" type="text" name="wprevpro_nr_date" class="regular-text" value="<?php echo esc_attr( ! empty( $currentreview->created_time ) ? $currentreview->created_time : date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ) ); ?>" required>
					<p class="description"><?php esc_html_e( 'Format: YYYY-MM-DD HH:MM:SS.', 'wp-fb-reviews' ); ?></p>
				</td>
			</tr>
		</tbody>
	</table>
	<?php wp_nonce_field( 'wprevpro_save_review' ); ?>
	<input type="hidden" name="editrid" id="editrid" value="<?php echo esc_attr( isset( $currentreview->id ) ? $currentreview->id : '' ); ?>">
	<input type="hidden" name="editrtype" id="editrtype" value="<?php echo esc_attr( isset( $currentreview->type ) ? $currentreview->type : '' ); ?>">
	<input type="submit" name="wprevpro_submitreviewbtn" id="wprevpro_submitreviewbtn" class="button button-primary" value="<?php esc_attr_e( 'Save Review', 'wp-fb-reviews' ); ?>">
	<a id="wpfb_addnewreview_cancel" class="button button-secondary"><?php esc_html_e( 'Cancel', 'wp-fb-reviews' ); ?></a>
</form>
</div>
</div>

<?php
// Pagination.
if ( isset( $_GET['pnum'] ) ) {
	$temppagenum = $_GET['pnum'];
} else {
	$temppagenum = '';
}
if ( $temppagenum === '' ) {
	$pagenum = 1;
} elseif ( is_numeric( $temppagenum ) ) {
	$pagenum = absint( $temppagenum );
} else {
	$pagenum = 1;
}

if ( ! isset( $_GET['sortdir'] ) ) {
	$_GET['sortdir'] = '';
}
if ( $_GET['sortdir'] === '' || $_GET['sortdir'] === 'DESC' ) {
	$sortdirection = '&sortdir=ASC';
} else {
	$sortdirection = '&sortdir=DESC';
}
$currenturl = remove_query_arg( array( 'sortdir', 'editrev', 'deleterev', 'hiderev', 'newvalue', '_wpnonce', 'opt', 'opt_type' ) );

if ( ! isset( $_GET['sortby'] ) ) {
	$_GET['sortby'] = '';
}
$allowed_keys = array( 'created_time_stamp', 'reviewer_name', 'rating', 'review_length', 'pagename', 'type', 'recommendation_type' );
$checkorderby = sanitize_key( $_GET['sortby'] );

if ( in_array( $checkorderby, $allowed_keys, true ) && $_GET['sortby'] !== '' ) {
	$sorttable = $_GET['sortby'] . ' ';
} else {
	$sorttable = 'created_time_stamp ';
}
if ( $_GET['sortdir'] === 'ASC' || $_GET['sortdir'] === 'DESC' ) {
	$sortdir = $_GET['sortdir'];
} else {
	$sortdir = 'DESC';
}

$sorticoncolor = array_fill( 0, 11, '' );
if ( $sorttable === 'reviewer_name ' ) {
	$sorticoncolor[1] = 'text_green';
} elseif ( $sorttable === 'rating ' ) {
	$sorticoncolor[2] = 'text_green';
} elseif ( $sorttable === 'created_time_stamp ' ) {
	$sorticoncolor[3] = 'text_green';
} elseif ( $sorttable === 'review_length ' ) {
	$sorticoncolor[4] = 'text_green';
} elseif ( $sorttable === 'pagename ' ) {
	$sorticoncolor[5] = 'text_green';
} elseif ( $sorttable === 'type ' ) {
	$sorticoncolor[6] = 'text_green';
} elseif ( $sorttable === 'recommendation_type ' ) {
	$sorticoncolor[7] = 'text_green';
}

$html .= '
		<table class="wp-list-table widefat striped posts">
			<thead>
				<tr>
					<th scope="col" width="70px" class="manage-column">' . esc_html__( 'Edit', 'wp-fb-reviews' ) . '</th>
					<th scope="col" width="50px" class="manage-column">' . esc_html__( 'Pic', 'wp-fb-reviews' ) . '</th>
					<th scope="col" style="min-width:70px" class="manage-column"><a href="' . esc_url( add_query_arg( 'sortby', 'reviewer_name', $currenturl ) ) . $sortdirection . '"><i class="dashicons dashicons-sort ' . $sorticoncolor[1] . '" aria-hidden="true"></i> ' . esc_html__( 'Name', 'wp-fb-reviews' ) . '</a></th>
					<th scope="col" width="70px" class="manage-column"><a href="' . esc_url( add_query_arg( 'sortby', 'rating', $currenturl ) ) . $sortdirection . '"><i class="dashicons dashicons-sort ' . $sorticoncolor[2] . '" aria-hidden="true"></i> ' . esc_html__( 'Rating', 'wp-fb-reviews' ) . '</a></th>
					<th scope="col" width="70px" class="manage-column"><a href="' . esc_url( add_query_arg( 'sortby', 'recommendation_type', $currenturl ) ) . $sortdirection . '"><i class="dashicons dashicons-sort ' . $sorticoncolor[7] . '" aria-hidden="true"></i> ' . esc_html__( 'R_Type', 'wp-fb-reviews' ) . '</a></th>
					<th scope="col" class="manage-column">' . esc_html__( 'Review Text', 'wp-fb-reviews' ) . '</th>
					<th scope="col" width="100px" class="manage-column"><a href="' . esc_url( add_query_arg( 'sortby', 'created_time_stamp', $currenturl ) ) . $sortdirection . '"><i class="dashicons dashicons-sort ' . $sorticoncolor[3] . '" aria-hidden="true"></i> ' . esc_html__( 'Date', 'wp-fb-reviews' ) . '</a></th>
					<th scope="col" width="70px" class="manage-column"><a href="' . esc_url( add_query_arg( 'sortby', 'review_length', $currenturl ) ) . $sortdirection . '"><i class="dashicons dashicons-sort ' . $sorticoncolor[4] . '" aria-hidden="true"></i> ' . esc_html__( 'Length', 'wp-fb-reviews' ) . '</a></th>
					<th scope="col" width="100px" class="manage-column"><a href="' . esc_url( add_query_arg( 'sortby', 'pagename', $currenturl ) ) . $sortdirection . '"><i class="dashicons dashicons-sort ' . $sorticoncolor[5] . '" aria-hidden="true"></i> ' . esc_html__( 'Page Name', 'wp-fb-reviews' ) . '</a></th>
					<th scope="col" width="100px" class="manage-column"><a href="' . esc_url( add_query_arg( 'sortby', 'type', $currenturl ) ) . $sortdirection . '"><i class="dashicons dashicons-sort ' . $sorticoncolor[6] . '" aria-hidden="true"></i> ' . esc_html__( 'Type', 'wp-fb-reviews' ) . '</a></th>
				</tr>
				</thead>
			<tbody id="review_list">';

$lowlimit    = ( $pagenum - 1 ) * $rowsperpage;
$tablelimit  = $lowlimit . ',' . $rowsperpage;
$reviewsrows = $wpdb->get_results(
	$wpdb->prepare(
		"SELECT * FROM {$table_name}
		WHERE id>%d AND (Type='Facebook' OR Type='Twitter')
		ORDER BY {$sorttable} {$sortdir}
		LIMIT {$tablelimit}",
		0
	)
);
$reviewtotalcount = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name}" );
$totalpages       = (int) ceil( $reviewtotalcount / $rowsperpage );

if ( $reviewtotalcount > 0 ) {
	foreach ( $reviewsrows as $reviewsrow ) {
		$editicon   = '<i class="dashicons dashicons-admin-tools editrev" aria-hidden="true"></i>';
		$deleteicon = '<i class="dashicons dashicons-trash deleterev" aria-hidden="true"></i>';

		if ( $reviewsrow->hide === 'yes' ) {
			$hideicon      = '<i class="dashicons dashicons-hidden hiderev" aria-hidden="true"></i>';
			$hiddentrclass = 'hiddenrow';
		} else {
			$hideicon      = '<i class="dashicons dashicons-visibility hiderev" aria-hidden="true"></i>';
			$hiddentrclass = '';
		}

		// Facebook avatars prefer the local cached copy.
		if ( $reviewsrow->userpiclocal !== '' ) {
			$userpicsrc = $reviewsrow->userpiclocal;
		} elseif ( $reviewsrow->userpic !== '' ) {
			$userpicsrc = $reviewsrow->userpic;
		} else {
			$userpicsrc = $default_avatar;
		}
		$userpic = '<img style="-webkit-user-select: none;width: 50px;" src="' . esc_url( $userpicsrc ) . '" alt="">';

		// Data for the JS-driven edit popup, so clicking "edit" never needs a page reload.
		$editdata = ' data-rid="' . esc_attr( $reviewsrow->id ) . '"'
			. ' data-rating="' . esc_attr( $reviewsrow->rating ) . '"'
			. ' data-title="' . esc_attr( $reviewsrow->review_title ) . '"'
			. ' data-text="' . esc_attr( $reviewsrow->review_text ) . '"'
			. ' data-name="' . esc_attr( $reviewsrow->reviewer_name ) . '"'
			. ' data-pagename="' . esc_attr( $reviewsrow->pagename ) . '"'
			. ' data-userpic="' . esc_attr( $userpicsrc ) . '"'
			. ' data-date="' . esc_attr( $reviewsrow->created_time ) . '"'
			. ' data-type="' . esc_attr( $reviewsrow->type ) . '"';

		$mediahtml = '';
		if ( $reviewsrow->mediaurlsarrayjson !== '' ) {
			$imagesarray = json_decode( $reviewsrow->mediaurlsarrayjson, true );
			if ( is_array( $imagesarray ) ) {
				$mediahtml = '<div class="mediaimgsdiv">';
				foreach ( $imagesarray as $imgurl ) {
					$mediahtml .= '<a href="' . esc_url( $imgurl ) . '" data-lity target="_blank"><img src="' . esc_url( $imgurl ) . '" height="50" alt=""></a> ';
				}
				$mediahtml .= '</div>';
			}
		}

		$revtitle = '';
		if ( $reviewsrow->review_title !== '' ) {
			$revtitle = '<b>' . esc_html( $reviewsrow->review_title ) . '</b></br>';
		}

		$typecolumn = esc_html( $reviewsrow->type );
		if ( ! empty( $reviewsrow->from_url ) ) {
			$typecolumn = '<a href="' . esc_url( $reviewsrow->from_url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $reviewsrow->type ) . '</a>';
		}

		$html .= '<tr id="' . esc_attr( $reviewsrow->id ) . '" class="' . esc_attr( $hiddentrclass ) . '">
						<th scope="col" class="manage-column"><span title="edit" role="button" tabindex="0" class="wprevpro_editrev_link wprevpro_iconbtn"' . $editdata . '>' . $editicon . '</span><br><span title="delete" role="button" tabindex="0" class="wprevpro_deleterev_link wprevpro_iconbtn" data-rid="' . esc_attr( $reviewsrow->id ) . '">' . $deleteicon . '</span><br>
						<span title="hide/unhide" role="button" tabindex="0" class="wprevpro_hiderev_link wprevpro_iconbtn" data-rid="' . esc_attr( $reviewsrow->id ) . '">' . $hideicon . '</span>
						</th>
						<th scope="col" class="manage-column wprevpro_pic_cell">' . $userpic . '</th>
						<th scope="col" class="manage-column">' . esc_html( $reviewsrow->reviewer_name ) . '</th>
						<th scope="col" class="manage-column">' . esc_html( $reviewsrow->rating ) . '</th>
						<th scope="col" class="manage-column">' . esc_html( $reviewsrow->recommendation_type ) . '</th>
						<th scope="col" class="manage-column">' . $revtitle . '<span title="' . esc_attr( $reviewsrow->review_text ) . '">' . esc_html( $reviewsrow->review_text ) . '</span>' . $mediahtml . '</th>
						<th scope="col" class="manage-column wprevpro_date_cell">' . esc_html( $reviewsrow->created_time ) . '</th>
						<th scope="col" class="manage-column">' . esc_html( $reviewsrow->review_length ) . '</th>
						<th scope="col" class="manage-column">' . esc_html( $reviewsrow->pagename ) . '</th>
						<th scope="col" class="manage-column">' . $typecolumn . '</th>
					</tr>';
	}
} else {
	$html .= '<tr>
						<th colspan="10" scope="col" class="manage-column">' . __( 'No reviews found. Please visit the <a href="?page=wpfb-facebook">Get FB Reviews</a> page to retrieve reviews from Facebook, or manually add one. If you\'ve already done that, then try de-activating and re-activating the plugin.', 'wp-fb-reviews' ) . '</th>
					</tr>';
}

$html .= '</tbody>
		</table>';

$html .= '<div id="wpfb_review_list_pagination_bar">';
$currenturl = remove_query_arg( 'pnum' );
for ( $x = 1; $x <= $totalpages; $x++ ) {
	$blue_grey = ( $x == $pagenum ) ? 'blue_grey' : '';
	$html     .= '<a href="' . esc_url( add_query_arg( 'pnum', $x, $currenturl ) ) . '" class="button ' . $blue_grey . '">' . $x . '</a>';
}
$html .= '</div><br>';
$html .= '</div>';

echo $html;
?>
	<div id="popup_review_list" class="popup-wrapper wpfbr_hide">
	  <div class="popup-content">
		<div class="popup-title">
		  <button type="button" class="popup-close">&times;</button>
		  <h3 id="popup_titletext"></h3>
		</div>
		<div class="popup-body">
		  <div id="popup_bobytext1"></div>
		  <div id="popup_bobytext2"></div>
		</div>
	  </div>
	</div>

	</div></div>
