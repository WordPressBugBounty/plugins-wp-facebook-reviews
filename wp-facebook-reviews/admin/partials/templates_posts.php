<?php

/**
 * Provide a admin area view for the plugin
 *
 * Template editor — tabbed Style / General / Filter / Badge settings with live preview.
 *
 * @link       http://ljapps.com
 * @since      1.0.0
 *
 * @package    WP_FB_Reviews
 * @subpackage WP_FB_Reviews/admin/partials
 */

// check user capabilities
if ( ! current_user_can( 'manage_options' ) ) {
	return;
}

$dbmsg = '';
$html  = '';

$currenttemplate = new stdClass();
$currenttemplate->id                 = '';
$currenttemplate->title              = '';
$currenttemplate->template_type      = '';
$currenttemplate->style              = '';
$currenttemplate->created_time_stamp = '';
$currenttemplate->display_num        = '';
$currenttemplate->display_num_rows   = '';
$currenttemplate->display_order      = '';
$currenttemplate->hide_no_text       = '';
$currenttemplate->template_css       = '';
$currenttemplate->min_rating         = '';
$currenttemplate->min_words          = '';
$currenttemplate->max_words          = '';
$currenttemplate->rtype              = '';
$currenttemplate->rpage              = '';
$currenttemplate->showreviewsbyid    = '';
$currenttemplate->createslider       = '';
$currenttemplate->numslides          = '';
$currenttemplate->slidermobileview   = '';
$currenttemplate->review_same_height = '';
$currenttemplate->template_misc      = '';
$currenttemplate->read_more          = '';
$currenttemplate->read_more_num      = '';
$currenttemplate->read_more_text     = 'read more';

// db function variables
global $wpdb;
$table_name = $wpdb->prefix . 'wpfb_post_templates';

/**
 * Small helper: allow hex or rgb(a) colors, otherwise empty.
 *
 * @param string $color Raw color value.
 * @return string
 */
$wpfb_sanitize_color = static function ( $color ) {
	$color = trim( (string) $color );
	if ( $color === '' ) {
		return '';
	}
	if ( preg_match( '/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{4}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/', $color ) ) {
		return $color;
	}
	if ( preg_match( '/^rgba?\(\s*[\d.]+\s*,\s*[\d.]+\s*,\s*[\d.]+\s*(,\s*[\d.]+\s*)?\)$/i', $color ) ) {
		return $color;
	}
	return '';
};

// form deleting and updating here---------------------------
if ( isset( $_GET['taction'] ) ) {
	$tid = intval( htmlentities( $_GET['tid'] ) );
	// for deleting
	if ( $_GET['taction'] === 'del' && $tid > 0 ) {
		check_admin_referer( 'tdel_' );
		$wpdb->delete( $table_name, array( 'id' => $tid ), array( '%d' ) );
	}
	// for updating
	if ( $_GET['taction'] === 'edit' && $tid > 0 ) {
		check_admin_referer( 'tedit_' );
		$currenttemplate = $wpdb->get_row( "SELECT * FROM " . $table_name . " WHERE id = " . $tid );
	}
}
//------------------------------------------

// form posting here (Save & Close fallback for when AJAX is unavailable)------------
if ( isset( $_POST['wpfbr_submittemplatebtn'] ) ) {
	check_admin_referer( 'wpfbr_save_template' );

	$t_id             = htmlentities( $_POST['edittid'] );
	$title            = sanitize_text_field( wp_unslash( $_POST['wpfbr_template_title'] ) );
	$template_type    = sanitize_text_field( wp_unslash( $_POST['wpfbr_template_type'] ) );
	$style            = sanitize_text_field( wp_unslash( $_POST['wprevpro_template_style'] ) );
	$display_num      = intval( $_POST['wpfbr_t_display_num'] );
	$display_num_rows = intval( $_POST['wpfbr_t_display_num_rows'] );
	$display_order    = sanitize_text_field( wp_unslash( $_POST['wpfbr_t_display_order'] ) );
	$hide_no_text     = sanitize_text_field( wp_unslash( $_POST['wpfbr_t_hidenotext'] ) );
	$template_css     = sanitize_textarea_field( wp_unslash( $_POST['wpfbr_template_css'] ) );
	$createslider     = sanitize_text_field( wp_unslash( $_POST['wpfbr_t_createslider'] ) );
	$numslides        = intval( $_POST['wpfbr_t_numslides'] );
	$read_more        = sanitize_text_field( wp_unslash( $_POST['wprevpro_t_read_more'] ) );
	$read_more_text   = sanitize_text_field( wp_unslash( $_POST['wprevpro_t_read_more_text'] ) );
	$read_more_num    = isset( $_POST['wprevpro_t_read_more_num'] ) ? intval( $_POST['wprevpro_t_read_more_num'] ) : 30;
	$min_rating       = isset( $_POST['wpfbr_t_min_rating'] ) ? intval( $_POST['wpfbr_t_min_rating'] ) : 1;
	$slidermobileview = isset( $_POST['wpfbr_slidermobileview'] ) ? sanitize_text_field( wp_unslash( $_POST['wpfbr_slidermobileview'] ) ) : '';
	$review_same_hgt  = isset( $_POST['wpfbr_t_review_same_height'] ) ? sanitize_text_field( wp_unslash( $_POST['wpfbr_t_review_same_height'] ) ) : 'no';
	$filtersource     = isset( $_POST['wpfbr_t_filtersource'] ) ? sanitize_text_field( wp_unslash( $_POST['wpfbr_t_filtersource'] ) ) : '';
	$filterrtype      = isset( $_POST['wpfbr_t_rtype'] ) ? sanitize_text_field( wp_unslash( $_POST['wpfbr_t_rtype'] ) ) : 'fb';
	if ( $filterrtype === 'twitter' ) {
		$rtype_save = '["twitter"]';
	} elseif ( $filterrtype === 'both' ) {
		$rtype_save = '["fb","twitter"]';
	} else {
		$rtype_save = '["fb"]';
	}

	// template misc (style + slider + badge, stored as JSON the public renderer reads)
	$templatemiscarray = array();
	$templatemiscarray['showstars'] = isset( $_POST['wprevpro_template_misc_showstars'] ) ? sanitize_text_field( wp_unslash( $_POST['wprevpro_template_misc_showstars'] ) ) : 'yes';
	$templatemiscarray['showdate']  = isset( $_POST['wprevpro_template_misc_showdate'] ) ? sanitize_text_field( wp_unslash( $_POST['wprevpro_template_misc_showdate'] ) ) : 'yes';
	$templatemiscarray['bgcolor1']  = $wpfb_sanitize_color( isset( $_POST['wprevpro_template_misc_bgcolor1'] ) ? wp_unslash( $_POST['wprevpro_template_misc_bgcolor1'] ) : '' );
	$templatemiscarray['bgcolor2']  = $wpfb_sanitize_color( isset( $_POST['wprevpro_template_misc_bgcolor2'] ) ? wp_unslash( $_POST['wprevpro_template_misc_bgcolor2'] ) : '' );
	$templatemiscarray['tcolor1']   = $wpfb_sanitize_color( isset( $_POST['wprevpro_template_misc_tcolor1'] ) ? wp_unslash( $_POST['wprevpro_template_misc_tcolor1'] ) : '' );
	$templatemiscarray['tcolor2']   = $wpfb_sanitize_color( isset( $_POST['wprevpro_template_misc_tcolor2'] ) ? wp_unslash( $_POST['wprevpro_template_misc_tcolor2'] ) : '' );
	$templatemiscarray['tcolor3']   = $wpfb_sanitize_color( isset( $_POST['wprevpro_template_misc_tcolor3'] ) ? wp_unslash( $_POST['wprevpro_template_misc_tcolor3'] ) : '' );
	$templatemiscarray['bradius']   = isset( $_POST['wprevpro_template_misc_bradius'] ) ? sanitize_text_field( wp_unslash( $_POST['wprevpro_template_misc_bradius'] ) ) : '0';
	$templatemiscarray['showmedia'] = isset( $_POST['wpfbr_t_showmedia'] ) ? sanitize_text_field( wp_unslash( $_POST['wpfbr_t_showmedia'] ) ) : 'yes';

	// Style-tab options.
	$templatemiscarray['verified']       = isset( $_POST['wprevpro_template_misc_verified'] ) ? sanitize_text_field( wp_unslash( $_POST['wprevpro_template_misc_verified'] ) ) : 'no';
	$templatemiscarray['lastnameformat'] = isset( $_POST['wprevpro_template_misc_lastname'] ) ? sanitize_text_field( wp_unslash( $_POST['wprevpro_template_misc_lastname'] ) ) : 'show';
	$templatemiscarray['avataropt']      = isset( $_POST['wprevpro_template_misc_avataropt'] ) ? sanitize_text_field( wp_unslash( $_POST['wprevpro_template_misc_avataropt'] ) ) : 'show';
	$templatemiscarray['showicon']       = isset( $_POST['wprevpro_template_misc_showicon'] ) ? sanitize_text_field( wp_unslash( $_POST['wprevpro_template_misc_showicon'] ) ) : 'lin';
	$tfont1_val                          = isset( $_POST['wprevpro_template_misc_tfont1'] ) ? absint( $_POST['wprevpro_template_misc_tfont1'] ) : 0;
	$tfont2_val                          = isset( $_POST['wprevpro_template_misc_tfont2'] ) ? absint( $_POST['wprevpro_template_misc_tfont2'] ) : 0;
	$templatemiscarray['tfont1']         = $tfont1_val > 0 ? (string) $tfont1_val : '';
	$templatemiscarray['tfont2']         = $tfont2_val > 0 ? (string) $tfont2_val : '';

	// General-tab options (slider + read more).
	$templatemiscarray['slidespeed']         = isset( $_POST['wpfbr_t_slidespeed'] ) ? sanitize_text_field( wp_unslash( $_POST['wpfbr_t_slidespeed'] ) ) : '1';
	$templatemiscarray['slideautodelay']     = isset( $_POST['wpfbr_t_slideautodelay'] ) ? sanitize_text_field( wp_unslash( $_POST['wpfbr_t_slideautodelay'] ) ) : '5';
	$templatemiscarray['sliderautoplay']     = isset( $_POST['wpfbr_sliderautoplay'] ) ? sanitize_text_field( wp_unslash( $_POST['wpfbr_sliderautoplay'] ) ) : '';
	$templatemiscarray['sliderhideprevnext'] = isset( $_POST['wpfbr_sliderhideprevnext'] ) ? sanitize_text_field( wp_unslash( $_POST['wpfbr_sliderhideprevnext'] ) ) : '';
	$templatemiscarray['sliderhidedots']     = isset( $_POST['wpfbr_sliderhidedots'] ) ? sanitize_text_field( wp_unslash( $_POST['wpfbr_sliderhidedots'] ) ) : '';
	$templatemiscarray['sliderfixedheight']  = isset( $_POST['wpfbr_sliderfixedheight'] ) ? sanitize_text_field( wp_unslash( $_POST['wpfbr_sliderfixedheight'] ) ) : '';
	$templatemiscarray['slidermobileview']   = $slidermobileview;
	$templatemiscarray['review_same_height'] = $review_same_hgt;
	$templatemiscarray['read_more_num']      = (string) $read_more_num;
	$templatemiscarray['read_more_color']    = $wpfb_sanitize_color( isset( $_POST['wprevpro_t_read_more_color'] ) ? wp_unslash( $_POST['wprevpro_t_read_more_color'] ) : '' );

	// Filter source lives in misc (the public renderer reads template_misc['filtersource']).
	$templatemiscarray['filtersource'] = $filtersource;

	// Badge options (stored in template_misc JSON).
	$templatemiscarray['blocation'] = isset( $_POST['wpfbr_t_blocation'] ) ? sanitize_text_field( wp_unslash( $_POST['wpfbr_t_blocation'] ) ) : '';
	$templatemiscarray['bname']     = isset( $_POST['wpfbr_t_bname'] ) ? sanitize_text_field( wp_unslash( $_POST['wpfbr_t_bname'] ) ) : '';
	$templatemiscarray['bnameurl']  = isset( $_POST['wpfbr_t_bnameurl'] ) ? esc_url_raw( wp_unslash( $_POST['wpfbr_t_bnameurl'] ) ) : '';
	$templatemiscarray['bimgurl']   = isset( $_POST['wpfbr_t_bimgurl'] ) ? esc_url_raw( wp_unslash( $_POST['wpfbr_t_bimgurl'] ) ) : '';
	$templatemiscarray['bshape']    = isset( $_POST['wpfbr_t_bshape'] ) ? sanitize_text_field( wp_unslash( $_POST['wpfbr_t_bshape'] ) ) : '';
	$templatemiscarray['bimgsize']  = isset( $_POST['wpfbr_t_bimgsize'] ) ? sanitize_text_field( wp_unslash( $_POST['wpfbr_t_bimgsize'] ) ) : '50';
	$templatemiscarray['bbtnurl']   = isset( $_POST['wpfbr_t_bbtnurl'] ) ? esc_url_raw( wp_unslash( $_POST['wpfbr_t_bbtnurl'] ) ) : '';
	$templatemiscarray['bbradius']  = isset( $_POST['wpfbr_t_bbradius'] ) ? sanitize_text_field( wp_unslash( $_POST['wpfbr_t_bbradius'] ) ) : '0';
	$templatemiscarray['bbwidth']   = isset( $_POST['wpfbr_t_bbwidth'] ) ? sanitize_text_field( wp_unslash( $_POST['wpfbr_t_bbwidth'] ) ) : '0';
	$templatemiscarray['bbtncolor'] = $wpfb_sanitize_color( isset( $_POST['wpfbr_t_bbtncolor'] ) ? wp_unslash( $_POST['wpfbr_t_bbtncolor'] ) : '#1877f2' );
	$templatemiscarray['bbkcolor']  = $wpfb_sanitize_color( isset( $_POST['wpfbr_t_bbkcolor'] ) ? wp_unslash( $_POST['wpfbr_t_bbkcolor'] ) : '#ffffff' );
	$templatemiscarray['bbcolor']   = $wpfb_sanitize_color( isset( $_POST['wpfbr_t_bbcolor'] ) ? wp_unslash( $_POST['wpfbr_t_bbcolor'] ) : '#eeeeee' );
	$templatemiscarray['bdropsh']   = isset( $_POST['wpfbr_t_bdropsh'] ) ? sanitize_text_field( wp_unslash( $_POST['wpfbr_t_bdropsh'] ) ) : '';
	$templatemiscarray['bcenter']   = isset( $_POST['wpfbr_t_bcenter'] ) ? sanitize_text_field( wp_unslash( $_POST['wpfbr_t_bcenter'] ) ) : '';
	$templatemiscarray['bhname']    = isset( $_POST['wpfbr_t_bhname'] ) ? sanitize_text_field( wp_unslash( $_POST['wpfbr_t_bhname'] ) ) : '';
	$templatemiscarray['bhphoto']   = isset( $_POST['wpfbr_t_bhphoto'] ) ? sanitize_text_field( wp_unslash( $_POST['wpfbr_t_bhphoto'] ) ) : '';
	$templatemiscarray['bhbased']   = isset( $_POST['wpfbr_t_bhbased'] ) ? sanitize_text_field( wp_unslash( $_POST['wpfbr_t_bhbased'] ) ) : '';
	$templatemiscarray['bhbtn']     = isset( $_POST['wpfbr_t_bhbtn'] ) ? sanitize_text_field( wp_unslash( $_POST['wpfbr_t_bhbtn'] ) ) : '';
	$templatemiscarray['bhpow']     = isset( $_POST['wpfbr_t_bhpow'] ) ? sanitize_text_field( wp_unslash( $_POST['wpfbr_t_bhpow'] ) ) : '';
	$templatemiscarray['bhreviews'] = isset( $_POST['wpfbr_t_bhreviews'] ) ? sanitize_text_field( wp_unslash( $_POST['wpfbr_t_bhreviews'] ) ) : '';
	$templatemiscarray['bobasedon'] = isset( $_POST['wpfbr_t_bobasedon'] ) ? sanitize_text_field( wp_unslash( $_POST['wpfbr_t_bobasedon'] ) ) : '';
	$templatemiscarray['borevus']   = isset( $_POST['wpfbr_t_borevus'] ) ? sanitize_text_field( wp_unslash( $_POST['wpfbr_t_borevus'] ) ) : '';

	$templatemiscjson = wp_json_encode( $templatemiscarray );
	$timenow          = time();

	$data = array(
		'title'              => $title,
		'template_type'      => $template_type,
		'style'              => $style,
		'created_time_stamp' => $timenow,
		'display_num'        => $display_num,
		'display_num_rows'   => $display_num_rows,
		'display_order'      => $display_order,
		'hide_no_text'       => $hide_no_text,
		'template_css'       => $template_css,
		'min_rating'         => $min_rating,
		'min_words'          => '',
		'max_words'          => '',
		'rtype'              => $rtype_save,
		'rpage'              => $filtersource,
		'createslider'       => $createslider,
		'numslides'          => $numslides,
		'slidermobileview'   => $slidermobileview,
		'showreviewsbyid'    => '',
		'template_misc'      => $templatemiscjson,
		'read_more'          => $read_more,
		'read_more_num'      => $read_more_num,
		'read_more_text'     => $read_more_text,
		'review_same_height' => $review_same_hgt,
	);
	$format = array(
		'%s', '%s', '%d', '%d', '%d', '%d', '%s', '%s', '%s', '%d',
		'%d', '%d', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s',
		'%d', '%s', '%s',
	);

	if ( $t_id === '' ) {
		$wpdb->insert( $table_name, $data, $format );
	} else {
		$updatetempquery = $wpdb->update( $table_name, $data, array( 'id' => $t_id ), $format, array( '%d' ) );
		if ( $updatetempquery > 0 ) {
			$dbmsg = '<div id="setting-error-wpfbr_message" class="updated settings-error notice is-dismissible">' . __( '<p><strong>Template Updated!</strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>', 'wp-fb-reviews' ) . '</div>';
		}
	}
}

// Get list of all current forms--------------------------
$currentforms = $wpdb->get_results( "SELECT id, title, template_type, created_time_stamp, style, createslider FROM $table_name WHERE `rtype` LIKE '%fb%' OR `rtype` LIKE '%twitter%' " );

// check to see if reviews are in database
$reviews_table_name = $wpdb->prefix . 'wpfb_reviews';
$reviewtotalcount   = $wpdb->get_var( 'SELECT COUNT(*) FROM ' . $reviews_table_name );
if ( $reviewtotalcount < 1 ) {
	$dbmsg = $dbmsg . '<div id="setting-error-wpfbr_message" class="updated settings-error notice is-dismissible">' . __( '<p><strong>No reviews found. Please visit the <a href="?page=wpfb-facebook">Get Facebook Reviews</a> or <a href="?page=wpfb-get_twitter">Get X (Twitter) Posts</a> page to retrieve reviews, or manually add one on the <a href="?page=wpfb-reviews">Review List</a> page. </strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>', 'wp-fb-reviews' ) . '</div>';
}

// add thickbox
add_thickbox();
?>
<div id="mythickboxid" style="display:none;">
	<p>
		<img src="<?php echo plugin_dir_url( __FILE__ ); ?>pro_settings.png">
	</p>
</div>

<div class="">
<h1></h1>
<div class="wrap" id="wp_rev_maindiv">

	<img class="wprev_headerimg" src="<?php echo plugin_dir_url( __FILE__ ) . 'logo.png?id=' . $this->_token; ?>">

<?php
include 'tabmenu.php';
?>
<div class="wpfbr_margin10">
<div class="w3-col welcomediv w3-container w3-white w3-border w3-border-light-gray2 w3-round-small">

<div class="wpfbr_margin10">
	<a id="wpfbr_helpicon_posts" class="wpfbr_btnicononly button dashicons-before dashicons-editor-help"></a>
	<a id="wpfbr_addnewtemplate" class="button dashicons-before dashicons-plus-alt"><?php _e( 'Add New Reviews Template', 'wp-fb-reviews' ); ?></a>
</div>

<?php
// display message
echo $dbmsg;
$html .= '
		<table class="wp-list-table widefat striped posts">
			<thead>
				<tr>
					<th scope="col" width="30px" class="manage-column">' . __( 'ID', 'wp-fb-reviews' ) . '</th>
					<th scope="col" class="manage-column">' . __( 'Title', 'wp-fb-reviews' ) . '</th>
					<th scope="col" width="100px" class="manage-column">' . __( 'Slider', 'wp-fb-reviews' ) . '</th>
					<th scope="col" width="170px" class="manage-column">' . __( 'Date Created', 'wp-fb-reviews' ) . '</th>
					<th scope="col" width="400px" class="manage-column">' . __( 'Action', 'wp-fb-reviews' ) . '</th>
				</tr>
				</thead>
			<tbody id="review_list">';
$haswidgettemplate = false; // for hiding widget type, going to be phasing widget types out.
foreach ( $currentforms as $currentform ) {
	// remove query args we just used
	$urltrimmed  = remove_query_arg( array( 'taction', 'id' ) );
	$tempeditbtn = add_query_arg(
		array(
			'taction' => 'edit',
			'tid'     => "$currentform->id",
		),
		$urltrimmed
	);
	$url_tempeditbtn = wp_nonce_url( $tempeditbtn, 'tedit_' );

	$tempdelbtn = add_query_arg(
		array(
			'taction' => 'del',
			'tid'     => "$currentform->id",
		),
		$urltrimmed
	);
	$url_tempdelbtn = wp_nonce_url( $tempdelbtn, 'tdel_' );
	if ( $currentform->template_type === 'widget' ) {
		$haswidgettemplate = true;
	}
	$html .= '<tr id="' . $currentform->id . '">
				<th scope="col" class="wpfbr_upgrade_needed manage-column">' . $currentform->id . '</th>
				<th scope="col" class="wpfbr_upgrade_needed manage-column"><b>' . esc_html( $currentform->title ) . '</b></th>
				<th scope="col" class="wpfbr_upgrade_needed manage-column"><b>' . esc_html( $currentform->createslider ) . '</b></th>
				<th scope="col" class="wpfbr_upgrade_needed manage-column">' . date( "F j, Y", $currentform->created_time_stamp ) . '</th>
				<th scope="col" class="manage-column" templateid="' . $currentform->id . '" templatetype="' . $currentform->template_type . '"><a href="' . $url_tempeditbtn . '" class="button button-primary dashicons-before dashicons-admin-generic">' . __( 'Edit', 'wp-fb-reviews' ) . '</a> <a href="' . $url_tempdelbtn . '" class="button button-secondary dashicons-before dashicons-trash">' . __( 'Delete', 'wp-fb-reviews' ) . '</a> <a class="wpfbr_displayshortcode button button-secondary dashicons-before dashicons-visibility">' . __( 'Shortcode', 'wp-fb-reviews' ) . '</a></th>
			</tr>';
}
$html .= '</tbody></table>';

echo $html;
?>
<div class="wpfbr_margin10" id="wpfbr_new_template">
<form name="newtemplateform" id="newtemplateform" action="?page=wpfb-templates_posts" method="post" onsubmit="return validateForm()">
	<table class="wpfbr_margin10 form-table ">
		<tbody>
			<tr class="wpfbr_row">
				<th scope="row">
					<?php _e( 'Template Title:', 'wp-fb-reviews' ); ?>
				</th>
				<td>
					<input id="wpfbr_template_title" data-custom="custom" type="text" name="wpfbr_template_title" placeholder="" value="<?php echo esc_attr( $currenttemplate->title ); ?>" required>
					<p class="description">
					<?php _e( 'Enter a title or name for this template.', 'wp-fb-reviews' ); ?></p>
				</td>
			</tr>
			<tr <?php if ( $haswidgettemplate === false ) { echo "style='display:none;'"; } ?> class="wpfbr_row">
				<th scope="row">
					<?php _e( 'Choose Template Type:', 'wp-fb-reviews' ); ?>
				</th>
				<td><div id="divtemplatestyles">
					<input type="radio" name="wpfbr_template_type" id="wpfbr_template_type1-radio" value="post" checked="checked">
					<label for="wpfbr_template_type1-radio"><?php _e( 'Post or Page', 'wp-fb-reviews' ); ?></label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<input type="radio" name="wpfbr_template_type" id="wpfbr_template_type2-radio" value="widget" <?php if ( $currenttemplate->template_type === 'widget' ) { echo 'checked="checked"'; } ?>>
					<label for="wpfbr_template_type2-radio"><?php _e( 'Widget Area', 'wp-fb-reviews' ); ?></label>
					</div>
					<p class="description">
					<?php _e( 'Are you going to use this on a Page/Post or in a Widget area like your sidebar?', 'wp-fb-reviews' ); ?></p>
				</td>
			</tr>
		</tbody>
	</table>

<?php
$template_misc_array = json_decode( $currenttemplate->template_misc, true );
if ( ! is_array( $template_misc_array ) ) {
	$template_misc_array             = array();
	$template_misc_array['showstars'] = '';
	$template_misc_array['showdate']  = '';
	$template_misc_array['bgcolor1']  = '';
	$template_misc_array['bgcolor2']  = '';
	$template_misc_array['tcolor1']   = '';
	$template_misc_array['tcolor2']   = '';
	$template_misc_array['tcolor3']   = '';
	$template_misc_array['bradius']   = '0';
}
if ( ! isset( $template_misc_array['showmedia'] ) || $template_misc_array['showmedia'] === '' ) {
	$template_misc_array['showmedia'] = 'yes';
}
// style-tab defaults
if ( ! isset( $template_misc_array['verified'] ) ) { $template_misc_array['verified'] = 'yes1'; }
if ( ! isset( $template_misc_array['lastnameformat'] ) ) { $template_misc_array['lastnameformat'] = 'show'; }
if ( ! isset( $template_misc_array['avataropt'] ) ) { $template_misc_array['avataropt'] = 'show'; }
if ( ! isset( $template_misc_array['showicon'] ) ) { $template_misc_array['showicon'] = 'lin'; }
if ( ! isset( $template_misc_array['tfont1'] ) ) { $template_misc_array['tfont1'] = ''; }
if ( ! isset( $template_misc_array['tfont2'] ) ) { $template_misc_array['tfont2'] = ''; }
// general-tab defaults (slider + read more)
if ( ! isset( $template_misc_array['slidespeed'] ) ) { $template_misc_array['slidespeed'] = '1'; }
if ( ! isset( $template_misc_array['slideautodelay'] ) ) { $template_misc_array['slideautodelay'] = '5'; }
if ( ! isset( $template_misc_array['sliderhideprevnext'] ) ) { $template_misc_array['sliderhideprevnext'] = ''; }
if ( ! isset( $template_misc_array['sliderhidedots'] ) ) { $template_misc_array['sliderhidedots'] = ''; }
if ( ! isset( $template_misc_array['sliderautoplay'] ) ) { $template_misc_array['sliderautoplay'] = ''; }
if ( ! isset( $template_misc_array['sliderfixedheight'] ) ) { $template_misc_array['sliderfixedheight'] = ''; }
if ( ! isset( $template_misc_array['slidermobileview'] ) ) { $template_misc_array['slidermobileview'] = $currenttemplate->slidermobileview; }
if ( ! isset( $template_misc_array['review_same_height'] ) ) { $template_misc_array['review_same_height'] = ( $currenttemplate->review_same_height !== '' ? $currenttemplate->review_same_height : 'no' ); }
if ( ! isset( $template_misc_array['read_more_num'] ) || $template_misc_array['read_more_num'] === '' ) { $template_misc_array['read_more_num'] = ( $currenttemplate->read_more_num > 0 ? $currenttemplate->read_more_num : '30' ); }
if ( ! isset( $template_misc_array['read_more_color'] ) ) { $template_misc_array['read_more_color'] = ''; }

// Selected review type for this template (fb, twitter, or both).
$wpfb_selected_rtype = 'fb';
$wpfb_rtype_decoded  = json_decode( isset( $currenttemplate->rtype ) ? $currenttemplate->rtype : '["fb"]', true );
if ( is_array( $wpfb_rtype_decoded ) ) {
	$has_fb      = in_array( 'fb', $wpfb_rtype_decoded, true );
	$has_twitter = in_array( 'twitter', $wpfb_rtype_decoded, true );
	if ( $has_fb && $has_twitter ) {
		$wpfb_selected_rtype = 'both';
	} elseif ( $has_twitter ) {
		$wpfb_selected_rtype = 'twitter';
	} else {
		$wpfb_selected_rtype = 'fb';
	}
}

// Build the source (pageid) list for the Choose Source filter from downloaded reviews.
$wpfb_source_ids  = array();
$wpfb_source_urls = array();
$wpfb_source_rows = $wpdb->get_results( "SELECT pageid, pagename, from_url, type FROM {$reviews_table_name} WHERE pageid != '' AND type IN ('Facebook','Twitter') GROUP BY pageid, type" );
if ( is_array( $wpfb_source_rows ) ) {
	foreach ( $wpfb_source_rows as $srow ) {
		if ( $srow->pageid === '' ) {
			continue;
		}
		$type_label = ( $srow->type === 'Twitter' ) ? 'X (Twitter)' : 'Facebook';
		$base_name  = ( $srow->pagename !== '' ) ? $srow->pagename : $srow->pageid;
		$wpfb_source_ids[ $srow->pageid ]  = $type_label . ': ' . $base_name;
		if ( ! empty( $srow->from_url ) ) {
			$wpfb_source_urls[ $srow->pageid ] = $srow->from_url;
		} elseif ( $srow->type === 'Twitter' ) {
			$wpfb_source_urls[ $srow->pageid ] = 'https://x.com/';
		} else {
			$wpfb_source_urls[ $srow->pageid ] = 'https://www.facebook.com/pg/' . $srow->pageid . '/reviews/';
		}
	}
}
// selected source: prefer misc filtersource, fall back to rpage column
$wpfb_selected_source = '';
if ( isset( $template_misc_array['filtersource'] ) && $template_misc_array['filtersource'] !== '' ) {
	$wpfb_selected_source = $template_misc_array['filtersource'];
} elseif ( isset( $currenttemplate->rpage ) ) {
	$wpfb_selected_source = $currenttemplate->rpage;
}
if ( $wpfb_selected_source === '' && $currenttemplate->id === '' && ! empty( $wpfb_source_ids ) ) {
	$wpfb_source_keys     = array_keys( $wpfb_source_ids );
	$wpfb_selected_source = end( $wpfb_source_keys );
}
$wpfb_default_badge_url = 'https://www.facebook.com/';
if ( $wpfb_selected_rtype === 'twitter' ) {
	$wpfb_default_badge_url = 'https://x.com/';
}
if ( $wpfb_selected_source !== '' && ! empty( $wpfb_source_urls[ $wpfb_selected_source ] ) ) {
	$wpfb_default_badge_url = $wpfb_source_urls[ $wpfb_selected_source ];
} elseif ( ! empty( $wpfb_source_urls ) ) {
	$wpfb_default_badge_url = end( $wpfb_source_urls );
}
$wpfb_imgs_base = trailingslashit( wpfbrev_plugin_url ) . 'public/partials/imgs/';
?>

<h2 class="nav-tab-wrapper">
	<span id="settingtab0" class="settingtab nav-tab cursorpointer gotopage0 nav-tab-active"><?php _e( 'Template Style', 'wp-fb-reviews' ); ?></span>
	<span id="settingtab1" class="settingtab nav-tab cursorpointer gotopage1"><?php _e( 'General Settings', 'wp-fb-reviews' ); ?></span>
	<span id="settingtab2" class="settingtab nav-tab cursorpointer gotopage2"><?php _e( 'Filter Settings', 'wp-fb-reviews' ); ?></span>
	<span id="settingtab3" class="settingtab nav-tab cursorpointer gotopage3"><?php _e( 'Badge Settings', 'wp-fb-reviews' ); ?></span>
	<span id="settingtab4" class="settingtab nav-tab cursorpointer gotopage4"><?php _e( 'AI Summary', 'wp-fb-reviews' ); ?> <span style="background:#ff6b35;color:#fff;padding:1px 5px;border-radius:3px;font-size:10px;font-weight:bold;margin-left:4px;">PRO</span></span>
</h2>

<table id="settingtable0" class="form-table settingstable ">
	<tr class="wpfbr_row">
		<td colspan="2" class="wpfbr-pro-tab-banner-cell">
			<div class="wpfbr-pro-tab-banner">
				<p style="margin:0;">
					<?php echo wp_kses_post( __( 'Unlock <strong>15 review layouts</strong>, custom star styles, date formats, avatar controls, hover effects, and more with Pro.', 'wp-fb-reviews' ) ); ?>
					<a href="https://wpreviewslider.com/" target="_blank"><strong><?php esc_html_e( 'Explore Pro Features', 'wp-fb-reviews' ); ?></strong></a>
					— <?php esc_html_e( 'Use code', 'wp-fb-reviews' ); ?> <code>WPPRO15</code> <?php esc_html_e( 'for 15% off.', 'wp-fb-reviews' ); ?>
				</p>
			</div>
		</td>
	</tr>
	<tr class="wpfbr_row">
		<td>
			<div class="w3_wprs-row">
				  <div class="w3_wprs-col s6">
					<div class="w3_wprs-col s6">
						<div class="wprevpre_temp_label_row"><?php _e( 'Style:', 'wp-fb-reviews' ); ?></div>
						<div class="wprevpre_temp_label_row"><?php _e( 'Show Stars:', 'wp-fb-reviews' ); ?></div>
						<div class="wprevpre_temp_label_row"><?php _e( 'Show Verified:', 'wp-fb-reviews' ); ?></div>
						<div class="wprevpre_temp_label_row"><?php _e( 'Show Date:', 'wp-fb-reviews' ); ?></div>
						<div class="wprevpre_temp_label_row"><?php _e( 'Last Name:', 'wp-fb-reviews' ); ?></div>
						<div class="wprevpre_temp_label_row"><?php _e( 'Display Avatar:', 'wp-fb-reviews' ); ?></div>
						<div class="wprevpre_temp_label_row"><?php _e( 'Show Icon:', 'wp-fb-reviews' ); ?></div>
						<div class="wprevpre_temp_label_row"><?php _e( 'Border Radius:', 'wp-fb-reviews' ); ?></div>
						<div class="wprevpre_temp_label_row"><?php _e( 'Background Color 1:', 'wp-fb-reviews' ); ?></div>
						<div class="wprevpre_temp_label_row wprevpre_bgcolor2"><?php _e( 'Background Color 2:', 'wp-fb-reviews' ); ?></div>
						<div class="wprevpre_temp_label_row"><?php _e( 'Text Color 1:', 'wp-fb-reviews' ); ?></div>
						<div class="wprevpre_temp_label_row"><?php _e( 'Text Color 2:', 'wp-fb-reviews' ); ?></div>
						<div class="wprevpre_temp_label_row wprevpre_tcolor3"><?php _e( 'Text Color 3:', 'wp-fb-reviews' ); ?></div>
						<div class="wprevpre_temp_label_row"><?php _e( 'Review Font Size:', 'wp-fb-reviews' ); ?></div>
						<div class="wprevpre_temp_label_row"><?php _e( 'Name/Date Font Size:', 'wp-fb-reviews' ); ?></div>
					</div>
					<div class="w3_wprs-col s6">
						<div class="wprevpre_temp_label_row">
							<select name="wprevpro_template_style" id="wprevpro_template_style">
							  <option value="1" <?php if ( $currenttemplate->style == '1' || $currenttemplate->style === '' ) { echo 'selected'; } ?>>Style 1</option>
							  <option value="6" <?php if ( $currenttemplate->style == '6' ) { echo 'selected'; } ?>>Style 6</option>
							</select>
							<a href="https://wpreviewslider.com/features/#templatedivid" target="_blank" rel="noopener noreferrer" style="font-size: 11px; margin-left: 8px; vertical-align: middle;"><?php esc_html_e( '15 styles in Pro Version!', 'wp-fb-reviews' ); ?></a>
						</div>
						<div class="wprevpre_temp_label_row">
							<select name="wprevpro_template_misc_showstars" id="wprevpro_template_misc_showstars">
							  <option value="yes" <?php if ( $template_misc_array['showstars'] == 'yes' ) { echo 'selected'; } ?>>Yes</option>
							  <option value="no" <?php if ( $template_misc_array['showstars'] == 'no' ) { echo 'selected'; } ?>>No</option>
							</select>
						</div>
						<div class="wprevpre_temp_label_row">
							<select name="wprevpro_template_misc_verified" id="wprevpro_template_misc_verified">
							  <option value="no" <?php if ( $template_misc_array['verified'] == 'no' || $template_misc_array['verified'] == '' ) { echo 'selected'; } ?>><?php _e( 'No', 'wp-fb-reviews' ); ?></option>
							  <option value="yes1" <?php if ( $template_misc_array['verified'] == 'yes1' ) { echo 'selected'; } ?>><?php _e( 'Yes', 'wp-fb-reviews' ); ?></option>
							</select>
						</div>
						<div class="wprevpre_temp_label_row">
							<select name="wprevpro_template_misc_showdate" id="wprevpro_template_misc_showdate">
							  <option value="yes" <?php if ( $template_misc_array['showdate'] == 'yes' ) { echo 'selected'; } ?>>Yes</option>
							  <option value="no" <?php if ( $template_misc_array['showdate'] == 'no' ) { echo 'selected'; } ?>>No</option>
							</select>
						</div>
						<div class="wprevpre_temp_label_row">
							<select name="wprevpro_template_misc_lastname" id="wprevpro_template_misc_lastname">
							  <option value="show" <?php if ( $template_misc_array['lastnameformat'] == 'show' ) { echo 'selected'; } ?>><?php _e( 'Show', 'wp-fb-reviews' ); ?></option>
							  <option value="hide" <?php if ( $template_misc_array['lastnameformat'] == 'hide' ) { echo 'selected'; } ?>><?php _e( 'Hide', 'wp-fb-reviews' ); ?></option>
							  <option value="initial" <?php if ( $template_misc_array['lastnameformat'] == 'initial' ) { echo 'selected'; } ?>><?php _e( 'Initial', 'wp-fb-reviews' ); ?></option>
							</select>
						</div>
						<div class="wprevpre_temp_label_row">
							<select name="wprevpro_template_misc_avataropt" id="wprevpro_template_misc_avataropt">
							  <option value="show" <?php if ( $template_misc_array['avataropt'] == 'show' ) { echo 'selected'; } ?>><?php _e( 'Yes', 'wp-fb-reviews' ); ?></option>
							  <option value="hide" <?php if ( $template_misc_array['avataropt'] == 'hide' ) { echo 'selected'; } ?>><?php _e( 'No', 'wp-fb-reviews' ); ?></option>
							  <option value="mystery" <?php if ( $template_misc_array['avataropt'] == 'mystery' ) { echo 'selected'; } ?>><?php _e( 'Mystery', 'wp-fb-reviews' ); ?></option>
							  <option value="init" <?php if ( $template_misc_array['avataropt'] == 'init' ) { echo 'selected'; } ?>><?php _e( 'Initial', 'wp-fb-reviews' ); ?></option>
							</select>
						</div>
						<div class="wprevpre_temp_label_row">
							<select name="wprevpro_template_misc_showicon" id="wprevpro_template_misc_showicon">
							  <option value="no" <?php if ( $template_misc_array['showicon'] == 'no' ) { echo 'selected'; } ?>><?php _e( 'No', 'wp-fb-reviews' ); ?></option>
							  <option value="yes" <?php if ( $template_misc_array['showicon'] == 'yes' ) { echo 'selected'; } ?>><?php _e( 'Yes', 'wp-fb-reviews' ); ?></option>
							  <option value="lin" <?php if ( $template_misc_array['showicon'] == 'lin' ) { echo 'selected'; } ?>><?php _e( 'Yes + Link', 'wp-fb-reviews' ); ?></option>
							</select>
						</div>
						<div class="wprevpre_temp_label_row">
							<input id="wprevpro_template_misc_bradius" type="number" min="0" name="wprevpro_template_misc_bradius" placeholder="" value="<?php echo esc_attr( $template_misc_array['bradius'] ); ?>" style="width: 4em">
						</div>
						<div class="wprevpre_temp_label_row">
							<input type="text" data-alpha="true" value="<?php echo esc_attr( $template_misc_array['bgcolor1'] ); ?>" name="wprevpro_template_misc_bgcolor1" id="wprevpro_template_misc_bgcolor1" class="my-color-field" />
						</div>
						<div class="wprevpre_temp_label_row wprevpre_bgcolor2">
							<input type="text" data-alpha="true" value="<?php echo esc_attr( $template_misc_array['bgcolor2'] ); ?>" name="wprevpro_template_misc_bgcolor2" id="wprevpro_template_misc_bgcolor2" class="my-color-field" />
						</div>
						<div class="wprevpre_temp_label_row">
							<input type="text" value="<?php echo esc_attr( $template_misc_array['tcolor1'] ); ?>" name="wprevpro_template_misc_tcolor1" id="wprevpro_template_misc_tcolor1" class="my-color-field" />
						</div>
						<div class="wprevpre_temp_label_row">
							<input type="text" value="<?php echo esc_attr( $template_misc_array['tcolor2'] ); ?>" name="wprevpro_template_misc_tcolor2" id="wprevpro_template_misc_tcolor2" class="my-color-field" />
						</div>
						<div class="wprevpre_temp_label_row wprevpre_tcolor3">
							<input type="text" value="<?php echo esc_attr( $template_misc_array['tcolor3'] ); ?>" name="wprevpro_template_misc_tcolor3" id="wprevpro_template_misc_tcolor3" class="my-color-field" />
						</div>
						<div class="wprevpre_temp_label_row">
							<input type="number" value="<?php echo esc_attr( $template_misc_array['tfont1'] ); ?>" style="width: 4em;min-width: 4em;" min="0" name="wprevpro_template_misc_tfont1" id="wprevpro_template_misc_tfont1" />&nbsp;px
						</div>
						<div class="wprevpre_temp_label_row">
							<input type="number" value="<?php echo esc_attr( $template_misc_array['tfont2'] ); ?>" style="width: 4em;min-width: 4em;" min="0" name="wprevpro_template_misc_tfont2" id="wprevpro_template_misc_tfont2" />&nbsp;px
						</div>
						<a id="wprevpro_pre_resetbtn" class="button"><?php _e( 'Reset Colors', 'wp-fb-reviews' ); ?></a>
					</div>
				  </div>
				  <div class="w3_wprs-col s6">
						<div class="wprevpre_temp_label_row"><strong><?php _e( 'Live Preview:', 'wp-fb-reviews' ); ?></strong></div>
						<div id="wprevpro_template_preview"></div>
						<p class="description"><i><?php _e( 'This preview updates as you change the settings on the left. Date format is based on your WordPress > Settings value.', 'wp-fb-reviews' ); ?></i></p>
						<div>
							<?php _e( 'Custom CSS:', 'wp-fb-reviews' ); ?><br>
							<textarea name="wpfbr_template_css" id="wpfbr_template_css" cols="50" rows="4"><?php echo esc_textarea( $currenttemplate->template_css ); ?></textarea>
							<p class="description"><?php _e( 'Enter custom CSS code to control the look even more.', 'wp-fb-reviews' ); ?></p>
						</div>
				  </div>
			</div>
			<p class="description">
			<?php _e( 'More styles available in <a href="https://wpreviewslider.com/" target="_blank">Pro Version</a> of plugin!', 'wp-fb-reviews' ); ?></p>
		</td>
	</tr>
	<tr class="wpfbr_row">
		<th scope="row" colspan="1">
			<span class="nextprevbtn w3-green button button-secondary dashicons-before dashicons-arrow-right-after gotopage1"><?php _e( 'Next', 'wp-fb-reviews' ); ?></span>
		</th>
	</tr>
</table>

<table id="settingtable1" class="form-table settingstable " style="display:none;">
	<tr class="wpfbr_row">
		<td colspan="2" class="wpfbr-pro-tab-banner-cell">
			<div class="wpfbr-pro-tab-banner">
				<p style="margin:0;">
					<?php echo wp_kses_post( __( 'Build <strong>masonry grids</strong> and advanced sliders with up to <strong>200 slides</strong>, Load More, endless scroll, fade effects, and custom breakpoints with Pro.', 'wp-fb-reviews' ) ); ?>
				</p>
			</div>
		</td>
	</tr>
	<tr class="wpfbr_row">
		<th scope="row"><?php _e( 'Number of Reviews:', 'wp-fb-reviews' ); ?><a class="wpfbr_helpicon_p wpfbr_btnicononlyhelp dashicons-before dashicons-editor-help"></a></th>
		<td><div class="divtemplatestyles">
			<label for="wpfbr_t_display_num"><?php _e( 'How many per a row?', 'wp-fb-reviews' ); ?></label>
			<select name="wpfbr_t_display_num" id="wpfbr_t_display_num">
			  <option value="1" <?php if ( $currenttemplate->display_num == 1 ) { echo 'selected'; } ?>>1</option>
			  <option value="2" <?php if ( $currenttemplate->display_num == 2 ) { echo 'selected'; } ?>>2</option>
			  <option value="3" <?php if ( $currenttemplate->display_num == 3 || $currenttemplate->display_num === '' ) { echo 'selected'; } ?>>3</option>
			  <option value="4" <?php if ( $currenttemplate->display_num == 4 ) { echo 'selected'; } ?>>4</option>
			</select>
			<label for="wpfbr_t_display_num_rows"><?php _e( 'How many total rows?', 'wp-fb-reviews' ); ?></label>
			<input id="wpfbr_t_display_num_rows" type="number" style="width: 4em" name="wpfbr_t_display_num_rows" placeholder="" value="<?php if ( $currenttemplate->display_num_rows > 0 ) { echo esc_attr( $currenttemplate->display_num_rows ); } else { echo '1'; } ?>">
			</div>
			<p class="description"><?php _e( 'How many reviews to display on the page at a time. Widget style templates can only display 1 per row.', 'wp-fb-reviews' ); ?></p>
		</td>
	</tr>
	<tr class="wpfbr_row">
		<th scope="row"><?php _e( 'Show Review Photos:', 'wp-fb-reviews' ); ?><a class="wpfbr_helpicon_p wpfbr_btnicononlyhelp dashicons-before dashicons-editor-help"></a></th>
		<td>
			<select name="wpfbr_t_showmedia" id="wpfbr_t_showmedia">
				<option value="yes" <?php if ( $template_misc_array['showmedia'] == 'yes' ) { echo 'selected'; } ?>><?php _e( 'Yes', 'wp-fb-reviews' ); ?></option>
				<option value="no" <?php if ( $template_misc_array['showmedia'] == 'no' ) { echo 'selected'; } ?>><?php _e( 'No', 'wp-fb-reviews' ); ?></option>
			</select>
			<p class="description"><?php _e( 'Display any photos attached to a review. Click a photo to open it in a pop-up.', 'wp-fb-reviews' ); ?></p>
		</td>
	</tr>
	<tr class="wpfbr_row">
		<th scope="row" style="min-width:220px"><?php _e( 'Slider or Grid:', 'wp-fb-reviews' ); ?><a class="wpfbr_helpicon_p wpfbr_btnicononlyhelp dashicons-before dashicons-editor-help"></a></th>
		<td>
			<div class="divtemplatestyles">
				<select name="wpfbr_t_createslider" id="wpfbr_t_createslider">
					<option value="no" <?php if ( $currenttemplate->createslider == 'no' ) { echo 'selected'; } ?>><?php _e( 'Grid', 'wp-fb-reviews' ); ?></option>
					<option value="yes" <?php if ( $currenttemplate->createslider == 'yes' ) { echo 'selected'; } ?>><?php _e( 'Slider', 'wp-fb-reviews' ); ?></option>
				</select>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<label for="wpfbr_t_numslides"><?php _e( 'Total slides:', 'wp-fb-reviews' ); ?>&nbsp;</label>
				<select name="wpfbr_t_numslides" id="wpfbr_t_numslides">
					<?php for ( $si = 2; $si <= 10; $si++ ) : ?>
					<option value="<?php echo $si; ?>" <?php if ( $currenttemplate->numslides == (string) $si ) { echo 'selected'; } ?>><?php echo $si; ?></option>
					<?php endfor; ?>
				</select>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<label for="wpfbr_t_slidespeed"><?php _e( 'Slide speed:', 'wp-fb-reviews' ); ?>&nbsp;</label>
				<input id="wpfbr_t_slidespeed" type="number" name="wpfbr_t_slidespeed" placeholder="" value="<?php echo esc_attr( $template_misc_array['slidespeed'] > 0 ? $template_misc_array['slidespeed'] : '1' ); ?>" style="width: 4em">
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<label for="wpfbr_t_slideautodelay"><?php _e( 'Auto-play delay:', 'wp-fb-reviews' ); ?>&nbsp;</label>
				<input id="wpfbr_t_slideautodelay" type="number" name="wpfbr_t_slideautodelay" placeholder="" value="<?php echo esc_attr( $template_misc_array['slideautodelay'] > 0 ? $template_misc_array['slideautodelay'] : '5' ); ?>" style="width: 4em">
			</div>
			<div class="divtemplatestyles" style="margin-top:8px;">
				<label style="margin-right:12px;"><input type="checkbox" id="wpfbr_slidermobileview" name="wpfbr_slidermobileview" value="one" <?php if ( $template_misc_array['slidermobileview'] == 'one' ) { echo 'checked="checked"'; } ?>> <?php _e( 'One review on mobile', 'wp-fb-reviews' ); ?></label>
				<label style="margin-right:12px;"><input type="checkbox" id="wpfbr_sliderhideprevnext" name="wpfbr_sliderhideprevnext" value="yes" <?php if ( $template_misc_array['sliderhideprevnext'] == 'yes' ) { echo 'checked="checked"'; } ?>> <?php _e( 'Hide Prev/Next', 'wp-fb-reviews' ); ?></label>
				<label style="margin-right:12px;"><input type="checkbox" id="wpfbr_sliderhidedots" name="wpfbr_sliderhidedots" value="yes" <?php if ( $template_misc_array['sliderhidedots'] == 'yes' ) { echo 'checked="checked"'; } ?>> <?php _e( 'Hide Dots', 'wp-fb-reviews' ); ?></label>
				<label style="margin-right:12px;"><input type="checkbox" id="wpfbr_sliderautoplay" name="wpfbr_sliderautoplay" value="yes" <?php if ( $template_misc_array['sliderautoplay'] == 'yes' ) { echo 'checked="checked"'; } ?>> <?php _e( 'Auto-play', 'wp-fb-reviews' ); ?></label>
				<label style="margin-right:12px;"><input type="checkbox" id="wpfbr_sliderfixedheight" name="wpfbr_sliderfixedheight" value="yes" <?php if ( $template_misc_array['sliderfixedheight'] == 'yes' ) { echo 'checked="checked"'; } ?>> <?php _e( 'Static Height', 'wp-fb-reviews' ); ?></label>
			</div>
			<p class="description"><?php _e( 'Allows you to create a slide show with your reviews.', 'wp-fb-reviews' ); ?></p>
		</td>
	</tr>
	<?php
	if ( ! isset( $currenttemplate->read_more ) ) {
		$currenttemplate->read_more      = '';
		$currenttemplate->read_more_text = '';
	}
	?>
	<tr class="wpfbr_row">
		<th scope="row"><?php _e( 'Add Read More Link:', 'wp-fb-reviews' ); ?><a class="wpfbr_helpicon_p wpfbr_btnicononlyhelp dashicons-before dashicons-editor-help"></a></th>
		<td><div class="divtemplatestyles">
			<select name="wprevpro_t_read_more" id="wprevpro_t_read_more" class="mt2">
				<option value="no" <?php if ( $currenttemplate->read_more == 'no' || $currenttemplate->read_more == '' ) { echo 'selected'; } ?>>No</option>
				<option value="yes" <?php if ( $currenttemplate->read_more == 'yes' ) { echo 'selected'; } ?>>Yes</option>
			</select>
			<label for="wprevpro_t_read_more_text">&nbsp;&nbsp;<?php _e( 'Read More Text:', 'wp-fb-reviews' ); ?></label>
			<input id="wprevpro_t_read_more_text" type="text" name="wprevpro_t_read_more_text" placeholder="read more" value="<?php if ( $currenttemplate->read_more_text != '' ) { echo esc_attr( $currenttemplate->read_more_text ); } else { echo 'read more'; } ?>" style="width: 8em">
			<label for="wprevpro_t_read_more_num">&nbsp;&nbsp;<?php _e( 'Number of Words:', 'wp-fb-reviews' ); ?>&nbsp;</label>
			<input id="wprevpro_t_read_more_num" type="number" name="wprevpro_t_read_more_num" placeholder="30" value="<?php echo esc_attr( $template_misc_array['read_more_num'] != '' ? $template_misc_array['read_more_num'] : '30' ); ?>" style="width: 4em">
			<label for="wprevpro_t_read_more_color">&nbsp;&nbsp;<?php _e( 'Color:', 'wp-fb-reviews' ); ?>&nbsp;</label>
			<input type="text" value="<?php echo esc_attr( $template_misc_array['read_more_color'] ); ?>" name="wprevpro_t_read_more_color" id="wprevpro_t_read_more_color" class="my-color-field" />
			</div>
			<p class="description"><?php _e( 'Allows you to cut off long reviews and add a read more link that will show the rest of the review when clicked.', 'wp-fb-reviews' ); ?></p>
		</td>
	</tr>
	<tr class="wpfbr_row">
		<th scope="row"><?php _e( 'Reviews Same Height:', 'wp-fb-reviews' ); ?><a class="wpfbr_helpicon_p wpfbr_btnicononlyhelp dashicons-before dashicons-editor-help"></a></th>
		<td>
			<select name="wpfbr_t_review_same_height" id="wpfbr_t_review_same_height">
				<option value="no" <?php if ( $template_misc_array['review_same_height'] == 'no' || $template_misc_array['review_same_height'] == '' ) { echo 'selected'; } ?>><?php _e( 'No', 'wp-fb-reviews' ); ?></option>
				<option value="yes" <?php if ( $template_misc_array['review_same_height'] == 'yes' ) { echo 'selected'; } ?>><?php _e( 'Yes', 'wp-fb-reviews' ); ?></option>
			</select>
			<p class="description"><?php _e( 'The individual review boxes will all be equal to the biggest one in all slides.', 'wp-fb-reviews' ); ?></p>
		</td>
	</tr>
	<tr class="wpfbr_row">
		<th scope="row" colspan="2">
			<span class="nextprevbtn w3-green button button-secondary dashicons-before dashicons-arrow-left gotopage0"><?php _e( 'Previous', 'wp-fb-reviews' ); ?></span>
			<span class="nextprevbtn w3-green button button-secondary dashicons-before dashicons-arrow-right-after gotopage2"><?php _e( 'Next', 'wp-fb-reviews' ); ?></span>
		</th>
	</tr>
</table>

<table id="settingtable2" class="form-table settingstable " style="display:none;">
	<tr class="wpfbr_row">
		<td colspan="2" class="wpfbr-pro-tab-banner-cell">
			<div class="wpfbr-pro-tab-banner">
				<p style="margin:0;">
					<?php echo wp_kses_post( __( 'Filter reviews by <strong>keywords</strong>, length, tags, language, media, multiple locations, or hand-pick specific reviews with Pro.', 'wp-fb-reviews' ) ); ?>
				</p>
			</div>
		</td>
	</tr>
	<tr class="wpfbr_row">
		<th scope="row"><?php _e( 'Review Type:', 'wp-fb-reviews' ); ?><a class="wpfbr_helpicon_p wpfbr_btnicononlyhelp dashicons-before dashicons-editor-help"></a></th>
		<td>
			<select name="wpfbr_t_rtype" id="wpfbr_t_rtype">
				<option value="fb" <?php selected( $wpfb_selected_rtype, 'fb' ); ?>><?php esc_html_e( 'Facebook', 'wp-fb-reviews' ); ?></option>
				<option value="twitter" <?php selected( $wpfb_selected_rtype, 'twitter' ); ?>><?php esc_html_e( 'X (Twitter)', 'wp-fb-reviews' ); ?></option>
				<option value="both" <?php selected( $wpfb_selected_rtype, 'both' ); ?>><?php esc_html_e( 'Facebook & X (Twitter)', 'wp-fb-reviews' ); ?></option>
			</select>
			<p class="description"><?php _e( 'Which review sources should this template display?', 'wp-fb-reviews' ); ?></p>
		</td>
	</tr>
	<tr class="wpfbr_row">
		<th scope="row"><?php _e( 'Choose Source:', 'wp-fb-reviews' ); ?><a class="wpfbr_helpicon_p wpfbr_btnicononlyhelp dashicons-before dashicons-editor-help"></a></th>
		<td>
			<select name="wpfbr_t_filtersource" id="wpfbr_t_filtersource">
			<?php if ( empty( $wpfb_source_ids ) ) : ?>
				<option value=""><?php esc_html_e( 'All sources', 'wp-fb-reviews' ); ?></option>
			<?php else : ?>
				<option value="" <?php selected( $wpfb_selected_source, '' ); ?>><?php esc_html_e( 'All sources', 'wp-fb-reviews' ); ?></option>
				<?php foreach ( $wpfb_source_ids as $spageid => $sname ) : ?>
					<option value="<?php echo esc_attr( $spageid ); ?>" data-fromurl="<?php echo esc_attr( isset( $wpfb_source_urls[ $spageid ] ) ? $wpfb_source_urls[ $spageid ] : '' ); ?>" <?php selected( $wpfb_selected_source, $spageid ); ?>><?php echo esc_html( $sname ); ?></option>
				<?php endforeach; ?>
			<?php endif; ?>
			</select>
			<p class="description"><?php _e( 'Optionally limit this template to one Facebook page or X (Twitter) download source. Leave as All sources to include every matching review of the selected type(s).', 'wp-fb-reviews' ); ?></p>
		</td>
	</tr>
	<tr class="wpfbr_row">
		<th scope="row"><?php _e( 'Display Order:', 'wp-fb-reviews' ); ?><a class="wpfbr_helpicon_p wpfbr_btnicononlyhelp dashicons-before dashicons-editor-help"></a></th>
		<td>
			<select name="wpfbr_t_display_order" id="wpfbr_t_display_order">
				<option value="random" <?php if ( $currenttemplate->display_order == 'random' ) { echo 'selected'; } ?>><?php _e( 'Random', 'wp-fb-reviews' ); ?></option>
				<option value="newest" <?php if ( $currenttemplate->display_order == 'newest' ) { echo 'selected'; } ?>><?php _e( 'Newest', 'wp-fb-reviews' ); ?></option>
			</select>
			<p class="description"><?php _e( 'The order in which the reviews are displayed.', 'wp-fb-reviews' ); ?></p>
		</td>
	</tr>
	<tr class="wpfbr_row">
		<th scope="row" style="min-width:220px"><?php _e( 'Hide Reviews Without Text:', 'wp-fb-reviews' ); ?><a class="wpfbr_helpicon_p wpfbr_btnicononlyhelp dashicons-before dashicons-editor-help"></a></th>
		<td>
			<select name="wpfbr_t_hidenotext" id="wpfbr_t_hidenotext">
				<option value="yes" <?php if ( $currenttemplate->hide_no_text == 'yes' ) { echo 'selected'; } ?>><?php _e( 'Yes', 'wp-fb-reviews' ); ?></option>
				<option value="no" <?php if ( $currenttemplate->hide_no_text == 'no' ) { echo 'selected'; } ?>><?php _e( 'No', 'wp-fb-reviews' ); ?></option>
			</select>
			<p class="description"><?php _e( 'Set to Yes and only display reviews that have text included.', 'wp-fb-reviews' ); ?></p>
		</td>
	</tr>
	<?php
	if ( ! isset( $currenttemplate->min_rating ) || $currenttemplate->min_rating === '' ) {
		$currenttemplate->min_rating = 1;
	}
	?>
	<tr class="wpfbr_row">
		<th scope="row"><?php _e( 'Filter By Rating:', 'wp-fb-reviews' ); ?><a class="wpfbr_helpicon_p wpfbr_btnicononlyhelp dashicons-before dashicons-editor-help"></a></th>
		<td>
			<select name="wpfbr_t_min_rating" id="wpfbr_t_min_rating">
			  <option value="1" <?php if ( $currenttemplate->min_rating == 1 ) { echo 'selected'; } ?>><?php _e( 'Show All', 'wp-fb-reviews' ); ?></option>
			  <option value="2" <?php if ( $currenttemplate->min_rating == 2 ) { echo 'selected'; } ?>><?php _e( '2 & Higher', 'wp-fb-reviews' ); ?></option>
			  <option value="3" <?php if ( $currenttemplate->min_rating == 3 ) { echo 'selected'; } ?>><?php _e( '3 & Higher', 'wp-fb-reviews' ); ?></option>
			  <option value="4" <?php if ( $currenttemplate->min_rating == 4 ) { echo 'selected'; } ?>><?php _e( '4 & Higher', 'wp-fb-reviews' ); ?></option>
			  <option value="5" <?php if ( $currenttemplate->min_rating == 5 ) { echo 'selected'; } ?>><?php _e( 'Only 5 Star', 'wp-fb-reviews' ); ?></option>
			</select>
			<p class="description"><?php _e( 'Show only reviews with at least this value rating. Also shows positive recommendations.', 'wp-fb-reviews' ); ?></p>
		</td>
	</tr>
	<tr class="wpfbr_row">
		<th scope="row" colspan="2">
			<span class="nextprevbtn w3-green button button-secondary dashicons-before dashicons-arrow-left gotopage1"><?php _e( 'Previous', 'wp-fb-reviews' ); ?></span>
			<span class="nextprevbtn w3-green button button-secondary dashicons-before dashicons-arrow-right-after gotopage3"><?php _e( 'Next', 'wp-fb-reviews' ); ?></span>
		</th>
	</tr>
</table>

<table id="settingtable3" class="form-table settingstable " style="display:none;">
<?php
if ( ! isset( $template_misc_array['blocation'] ) ) { $template_misc_array['blocation'] = ''; }
if ( ! isset( $template_misc_array['bname'] ) ) { $template_misc_array['bname'] = ''; }
if ( ! isset( $template_misc_array['bimgurl'] ) ) { $template_misc_array['bimgurl'] = $wpfb_imgs_base . 'facebook_badge_icon.svg'; }
if ( ! isset( $template_misc_array['bbtncolor'] ) ) { $template_misc_array['bbtncolor'] = '#1877f2'; }
if ( ! isset( $template_misc_array['bbtnurl'] ) ) { $template_misc_array['bbtnurl'] = $wpfb_default_badge_url; }
if ( ! isset( $template_misc_array['bnameurl'] ) ) { $template_misc_array['bnameurl'] = $wpfb_default_badge_url; }
if ( ! isset( $template_misc_array['bbkcolor'] ) ) { $template_misc_array['bbkcolor'] = '#ffffff'; }
if ( ! isset( $template_misc_array['bbradius'] ) ) { $template_misc_array['bbradius'] = '0'; }
if ( ! isset( $template_misc_array['bbwidth'] ) ) { $template_misc_array['bbwidth'] = '0'; }
if ( ! isset( $template_misc_array['bbcolor'] ) ) { $template_misc_array['bbcolor'] = '#eeeeee'; }
if ( ! isset( $template_misc_array['bshape'] ) ) { $template_misc_array['bshape'] = ''; }
if ( ! isset( $template_misc_array['bimgsize'] ) ) { $template_misc_array['bimgsize'] = '50'; }
if ( ! isset( $template_misc_array['bdropsh'] ) ) { $template_misc_array['bdropsh'] = 'yes'; }
if ( ! isset( $template_misc_array['bcenter'] ) ) { $template_misc_array['bcenter'] = ''; }
if ( ! isset( $template_misc_array['bhname'] ) ) { $template_misc_array['bhname'] = ''; }
if ( ! isset( $template_misc_array['bhphoto'] ) ) { $template_misc_array['bhphoto'] = ''; }
?>
	<tr class="wpfbr_row">
		<td colspan="2" class="wpfbr-pro-tab-banner-cell">
			<div class="wpfbr-pro-tab-banner">
				<p style="margin:0;">
					<?php echo wp_kses_post( __( 'Create <strong>standalone review badges</strong>, dynamically display review totals, and get more control over your Review Us button with Pro.', 'wp-fb-reviews' ) ); ?>
				</p>
			</div>
		</td>
	</tr>
<?php
if ( ! isset( $template_misc_array['bhbased'] ) ) { $template_misc_array['bhbased'] = ''; }
if ( ! isset( $template_misc_array['bhbtn'] ) ) { $template_misc_array['bhbtn'] = ''; }
if ( ! isset( $template_misc_array['bhpow'] ) ) { $template_misc_array['bhpow'] = ''; }
if ( ! isset( $template_misc_array['bhreviews'] ) ) { $template_misc_array['bhreviews'] = ''; }
if ( ! isset( $template_misc_array['bobasedon'] ) ) { $template_misc_array['bobasedon'] = 'Based on # reviews'; }
if ( ! isset( $template_misc_array['borevus'] ) ) { $template_misc_array['borevus'] = 'Review us on Facebook!'; }
?>
	<tr class="wpfbr_row tabnoterow">
		<td colspan="2">
			<div class="tabnote">&nbsp;&nbsp;<?php _e( 'Use this page to place a badge next to your reviews. This is a brand new feature so let me know if you see any formatting issues.', 'wp-fb-reviews' ); ?></div>
		</td>
	</tr>
	<tr class="wpfbr_row">
		<td colspan="2">
		<div class="badgeinfo">
			<div class="badgeinfosetting">
				<div class="bsetlabel"><?php _e( 'Location:', 'wp-fb-reviews' ); ?></div>
				<select name="wpfbr_t_blocation" id="wpfbr_t_blocation">
					<option value="" <?php if ( $template_misc_array['blocation'] == '' ) { echo 'selected'; } ?>><?php _e( 'Select One', 'wp-fb-reviews' ); ?></option>
					<option value="left" <?php if ( $template_misc_array['blocation'] == 'left' ) { echo 'selected'; } ?>><?php _e( 'Left', 'wp-fb-reviews' ); ?></option>
					<option value="leftmid" <?php if ( $template_misc_array['blocation'] == 'leftmid' ) { echo 'selected'; } ?>><?php _e( 'Left Middle', 'wp-fb-reviews' ); ?></option>
					<option value="above" <?php if ( $template_misc_array['blocation'] == 'above' ) { echo 'selected'; } ?>><?php _e( 'Above', 'wp-fb-reviews' ); ?></option>
					<option value="abovewide" <?php if ( $template_misc_array['blocation'] == 'abovewide' ) { echo 'selected'; } ?>><?php _e( 'Above Wide', 'wp-fb-reviews' ); ?></option>
					<option value="right" <?php if ( $template_misc_array['blocation'] == 'right' ) { echo 'selected'; } ?>><?php _e( 'Right', 'wp-fb-reviews' ); ?></option>
					<option value="rightmid" <?php if ( $template_misc_array['blocation'] == 'rightmid' ) { echo 'selected'; } ?>><?php _e( 'Right Middle', 'wp-fb-reviews' ); ?></option>
				</select>
			</div>
			<div class="badgeinfosetting badgehide">
				<div class="bsetlabel"><?php _e( 'Name:', 'wp-fb-reviews' ); ?></div>
				<input id="wpfbr_t_bname" type="text" name="wpfbr_t_bname" value="<?php echo esc_attr( $template_misc_array['bname'] ); ?>" style="width: 15em">
			</div>
			<div class="badgeinfosetting badgehide">
				<div class="bsetlabel"><?php _e( 'Name Link URL:', 'wp-fb-reviews' ); ?></div>
				<input id="wpfbr_t_bnameurl" type="text" name="wpfbr_t_bnameurl" value="<?php echo esc_attr( $template_misc_array['bnameurl'] ); ?>" style="width: 15em">
			</div>
		</div>
		</td>
	</tr>
	<tr class="wpfbr_row badgehide">
		<td colspan="2">
		<div class="badgeinfo">
			<div class="badgeinfosetting">
				<div class="bsetlabel"><?php _e( 'Business Image URL:', 'wp-fb-reviews' ); ?></div>
				<input id="wpfbr_t_bimgurl" type="text" name="wpfbr_t_bimgurl" value="<?php echo esc_attr( $template_misc_array['bimgurl'] ); ?>" style="width: 15em"><a id="upload_licon_button" class="button"><?php _e( 'Upload', 'wp-fb-reviews' ); ?></a>
			</div>
			<div class="badgeinfosetting">
				<div class="bsetlabel"><?php _e( 'Image Shape:', 'wp-fb-reviews' ); ?></div>
				<select name="wpfbr_t_bshape" id="wpfbr_t_bshape">
					<option value="" <?php if ( $template_misc_array['bshape'] == '' ) { echo 'selected'; } ?>>&nbsp;<?php _e( 'Square', 'wp-fb-reviews' ); ?>&nbsp;&nbsp;&nbsp;</option>
					<option value="round" <?php if ( $template_misc_array['bshape'] == 'round' ) { echo 'selected'; } ?>>&nbsp;<?php _e( 'Round', 'wp-fb-reviews' ); ?>&nbsp;&nbsp;&nbsp;</option>
				</select>
			</div>
			<div class="badgeinfosetting">
				<div class="bsetlabel"><?php _e( 'Image Size:', 'wp-fb-reviews' ); ?></div>
				<input id="wpfbr_t_bimgsize" type="number" name="wpfbr_t_bimgsize" value="<?php echo esc_attr( $template_misc_array['bimgsize'] ); ?>" style="width: 6em">
			</div>
			<div class="badgeinfosetting">
				<div class="bsetlabel"><?php _e( 'Button Color:', 'wp-fb-reviews' ); ?></div>
				<input type="text" data-alpha="true" value="<?php echo esc_attr( $template_misc_array['bbtncolor'] ); ?>" name="wpfbr_t_bbtncolor" id="wpfbr_t_bbtncolor" class="my-color-field" />
			</div>
			<div class="badgeinfosetting">
				<div class="bsetlabel"><?php _e( 'Button Link URL:', 'wp-fb-reviews' ); ?></div>
				<input id="wpfbr_t_bbtnurl" type="text" name="wpfbr_t_bbtnurl" value="<?php echo esc_attr( $template_misc_array['bbtnurl'] ); ?>" style="width: 15em">
			</div>
			<div class="badgeinfosetting">
				<div class="bsetlabel"><?php _e( 'Background:', 'wp-fb-reviews' ); ?></div>
				<input type="text" data-alpha="true" value="<?php echo esc_attr( $template_misc_array['bbkcolor'] ); ?>" name="wpfbr_t_bbkcolor" id="wpfbr_t_bbkcolor" class="my-color-field" />
			</div>
			<div class="badgeinfosetting">
				<div class="bsetlabel"><?php _e( 'Border Radius:', 'wp-fb-reviews' ); ?></div>
				<input id="wpfbr_t_bbradius" type="number" min="0" name="wpfbr_t_bbradius" value="<?php echo esc_attr( $template_misc_array['bbradius'] ); ?>" style="width: 7em">
			</div>
			<div class="badgeinfosetting">
				<div class="bsetlabel"><?php _e( 'Border Size:', 'wp-fb-reviews' ); ?></div>
				<input id="wpfbr_t_bbwidth" type="number" min="0" name="wpfbr_t_bbwidth" value="<?php echo esc_attr( $template_misc_array['bbwidth'] ); ?>" style="width: 7em">
			</div>
			<div class="badgeinfosetting">
				<div class="bsetlabel"><?php _e( 'Border Color:', 'wp-fb-reviews' ); ?></div>
				<input type="text" data-alpha="true" value="<?php echo esc_attr( $template_misc_array['bbcolor'] ); ?>" name="wpfbr_t_bbcolor" id="wpfbr_t_bbcolor" class="my-color-field" />
			</div>
		</div>
		</td>
	</tr>
	<tr class="wpfbr_row badgehide">
		<td colspan="2">
		<div class="badgeinfo">
			<div class="badgeinfosetting checkboxes">
				<input type="checkbox" id="wpfbr_t_bdropsh" name="wpfbr_t_bdropsh" value="yes" <?php if ( $template_misc_array['bdropsh'] == 'yes' ) { echo 'checked="checked"'; } ?>>
				<label for="wpfbr_t_bdropsh"><?php _e( 'Drop Shadow', 'wp-fb-reviews' ); ?></label>
			</div>
			<div class="badgeinfosetting checkboxes">
				<input type="checkbox" id="wpfbr_t_bcenter" name="wpfbr_t_bcenter" value="yes" <?php if ( $template_misc_array['bcenter'] == 'yes' ) { echo 'checked="checked"'; } ?>>
				<label for="wpfbr_t_bcenter"><?php _e( 'Center Text', 'wp-fb-reviews' ); ?></label>
			</div>
			<div class="badgeinfosetting checkboxes">
				<input type="checkbox" id="wpfbr_t_bhphoto" name="wpfbr_t_bhphoto" value="yes" <?php if ( $template_misc_array['bhphoto'] == 'yes' ) { echo 'checked="checked"'; } ?>>
				<label for="wpfbr_t_bhphoto"><?php _e( 'Hide Photo', 'wp-fb-reviews' ); ?></label>
			</div>
			<div class="badgeinfosetting checkboxes">
				<input type="checkbox" id="wpfbr_t_bhname" name="wpfbr_t_bhname" value="yes" <?php if ( $template_misc_array['bhname'] == 'yes' ) { echo 'checked="checked"'; } ?>>
				<label for="wpfbr_t_bhname"><?php _e( 'Hide Name', 'wp-fb-reviews' ); ?></label>
			</div>
			<div class="badgeinfosetting checkboxes">
				<input type="checkbox" id="wpfbr_t_bhbased" name="wpfbr_t_bhbased" value="yes" <?php if ( $template_misc_array['bhbased'] == 'yes' ) { echo 'checked="checked"'; } ?>>
				<label for="wpfbr_t_bhbased"><?php _e( 'Hide "Based On..."', 'wp-fb-reviews' ); ?></label>
			</div>
			<div class="badgeinfosetting checkboxes">
				<input type="checkbox" id="wpfbr_t_bhpow" name="wpfbr_t_bhpow" value="yes" <?php if ( $template_misc_array['bhpow'] == 'yes' ) { echo 'checked="checked"'; } ?>>
				<label for="wpfbr_t_bhpow"><?php _e( 'Hide "powered By..."', 'wp-fb-reviews' ); ?></label>
			</div>
			<div class="badgeinfosetting checkboxes">
				<input type="checkbox" id="wpfbr_t_bhbtn" name="wpfbr_t_bhbtn" value="yes" <?php if ( $template_misc_array['bhbtn'] == 'yes' ) { echo 'checked="checked"'; } ?>>
				<label for="wpfbr_t_bhbtn"><?php _e( 'Hide "Review Us..."', 'wp-fb-reviews' ); ?></label>
			</div>
			<div class="badgeinfosetting checkboxes">
				<input type="checkbox" id="wpfbr_t_bhreviews" name="wpfbr_t_bhreviews" value="yes" <?php if ( $template_misc_array['bhreviews'] == 'yes' ) { echo 'checked="checked"'; } ?>>
				<label for="wpfbr_t_bhreviews"><?php _e( 'Hide Reviews', 'wp-fb-reviews' ); ?></label>
			</div>
		</div>
		</td>
	</tr>
	<tr class="wpfbr_row">
		<td colspan="2">
		<div class="badgeinfo">
			<div class="badgeinfosetting badgehide">
				<div class="bsetlabel"><?php _e( 'Override "Based on..":', 'wp-fb-reviews' ); ?></div>
				<input id="wpfbr_t_bobasedon" type="text" name="wpfbr_t_bobasedon" value="<?php echo esc_attr( $template_misc_array['bobasedon'] ); ?>" style="width: 15em">
			</div>
			<div class="badgeinfosetting badgehide">
				<div class="bsetlabel"><?php _e( 'Override "Review us..":', 'wp-fb-reviews' ); ?></div>
				<input id="wpfbr_t_borevus" type="text" name="wpfbr_t_borevus" value="<?php echo esc_attr( $template_misc_array['borevus'] ); ?>" style="width: 15em">
			</div>
		</div>
		</td>
	</tr>
	<tr class="wpfbr_row">
		<th scope="row" colspan="2">
			<span class="nextprevbtn w3-green button button-secondary dashicons-before dashicons-arrow-left gotopage2"><?php _e( 'Previous', 'wp-fb-reviews' ); ?></span>
			<span class="nextprevbtn w3-green button button-secondary dashicons-before dashicons-arrow-right-after gotopage4"><?php _e( 'Next', 'wp-fb-reviews' ); ?></span>
		</th>
	</tr>
</table>

<table id="settingtable4" class="form-table settingstable " style="display:none;">
	<tbody>
		<tr class="wpfbr_row">
			<td colspan="2" class="wpfbr-pro-tab-banner-cell">
				<div class="wpfbr-pro-tab-banner">
					<p style="margin:0;">
						<?php echo wp_kses_post( __( '<strong>AI Review Summary</strong> turns your Facebook and X (Twitter) reviews into a clear visitor-facing summary with overall sentiment, common praise, and areas for improvement.', 'wp-fb-reviews' ) ); ?>
					</p>
				</div>
			</td>
		</tr>
		<tr class="wpfbr_row">
			<th scope="row">
				<?php esc_html_e( 'Example', 'wp-fb-reviews' ); ?>
			</th>
			<td>
				<?php
				$ai_summary_img1 = plugin_dir_url( __FILE__ ) . 'imgs/ai_summary_example.png';
				$ai_summary_img2 = plugin_dir_url( __FILE__ ) . 'imgs/ai_summary_example2.png';
				?>
				<div class="wpfbr-ai-summary-thumbs">
					<a href="<?php echo esc_url( $ai_summary_img1 ); ?>" data-lity class="wpfbr-ai-summary-thumb">
						<img src="<?php echo esc_url( $ai_summary_img1 ); ?>" alt="<?php esc_attr_e( 'AI Review Summary example', 'wp-fb-reviews' ); ?>">
					</a>
					<a href="<?php echo esc_url( $ai_summary_img2 ); ?>" data-lity class="wpfbr-ai-summary-thumb">
						<img src="<?php echo esc_url( $ai_summary_img2 ); ?>" alt="<?php esc_attr_e( 'AI Review Summary example 2', 'wp-fb-reviews' ); ?>">
					</a>
				</div>
			</td>
		</tr>
		<tr class="wpfbr_row">
			<th scope="row">
				<?php esc_html_e( 'Settings in Pro', 'wp-fb-reviews' ); ?>
			</th>
			<td>
				<ul style="list-style: disc; margin: 0 0 0 1.5em; line-height: 1.7;">
					<li><?php esc_html_e( 'Turn AI Summary on or off for each review template.', 'wp-fb-reviews' ); ?></li>
					<li><?php esc_html_e( 'Choose the language of the generated summary (or leave blank to match the reviews).', 'wp-fb-reviews' ); ?></li>
					<li><?php esc_html_e( 'Set the approximate number of sentences for summary length.', 'wp-fb-reviews' ); ?></li>
					<li><?php esc_html_e( 'Generate AI Instructions for use with your own AI tool, or generate the summary with the Pro AI service.', 'wp-fb-reviews' ); ?></li>
					<li><?php esc_html_e( 'Choose where it displays: as the first review, above the reviews, in a badge, and/or in the header.', 'wp-fb-reviews' ); ?></li>
					<li><?php esc_html_e( 'Show or hide “What customers love” (common praises).', 'wp-fb-reviews' ); ?></li>
					<li><?php esc_html_e( 'Show or hide “Areas for improvement” (common complaints).', 'wp-fb-reviews' ); ?></li>
				</ul>
			</td>
		</tr>
		<tr class="wprevpro_row">
			<th scope="row" colspan="2">
				<span class="nextprevbtn w3-green button button-secondary dashicons-before dashicons-arrow-left gotopage3"><?php _e( 'Previous', 'wp-fb-reviews' ); ?></span>
			</th>
		</tr>
	</tbody>
</table>
	<?php
	// security nonce
	wp_nonce_field( 'wpfbr_save_template' );
	?>
	<input type="hidden" name="edittid" id="edittid" value="<?php echo esc_attr( $currenttemplate->id ); ?>">
	<a id="wpfbr_addnewtemplate_cancel" class="button button-secondary"><?php _e( 'Cancel', 'wp-fb-reviews' ); ?></a>
	<input type="submit" name="wpfbr_submittemplatebtn" id="wpfbr_submittemplatebtn" class="button button-primary" value="<?php _e( 'Save &amp; Close', 'wp-fb-reviews' ); ?>">
	<a id="wpfbr_addnewtemplate_update" class="button button-primary"><?php _e( 'Update', 'wp-fb-reviews' ); ?></a>
	<div id="update_form_msg_div">
		<span class="spinner wpfbr_form_spinner" id="savingformimg"></span>
		<span id="update_form_msg" style="display:none;"><span class="dashicons dashicons-saved"></span> <?php _e( 'Saved!', 'wp-fb-reviews' ); ?></span>
	</div>
	</form>
</div>

<div class="wpfbr_margin10 w3-white" id="wpfbr_preview_outermost" style="display:none;">
	<div id="wpfbr_loading_prev_div">
		<span class="spinner wpfbr_preview_spinner" id="loadingpreview"></span>
	</div>
	<div class="wpfbr_margin10 w3-white" id="wpfbr_preview_outer"></div>
</div>
<div class=""><p><?php _e( 'Do you like this plugin? If so please take a moment to leave me a review <a href="https://wordpress.org/plugins/wp-facebook-reviews/" target="blank">here!</a> If it\'s missing something then please contact me <a href="https://wpreviewslider.com/contact/" target="blank">here</a>. Thanks!', 'wp-fb-reviews' ); ?></p></div>

</div></div></div>

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
</div>
