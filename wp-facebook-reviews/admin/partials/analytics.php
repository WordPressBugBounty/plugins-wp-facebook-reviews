<?php
/**
 * Analytics admin page for WP FB Reviews.
 *
 * @package    WP_FB_Reviews
 * @subpackage WP_FB_Reviews/admin/partials
 */

if ( ! current_user_can( 'manage_options' ) ) {
	return;
}

global $wpdb;
$reviews_table_name = $wpdb->prefix . 'wpfb_reviews';
$reviewtotalcount   = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$reviews_table_name} WHERE LOWER(type) IN ('facebook','twitter')" );
$dbmsg              = '';
if ( $reviewtotalcount < 1 ) {
	$dbmsg = '<div id="setting-error-wprevpro_message" class="updated settings-error notice is-dismissible"><p><strong>' .
		esc_html__( 'No reviews found. Please visit the Facebook or X (Twitter) page to retrieve reviews.', 'wp-fb-reviews' ) .
		'</strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
}

$typearray   = $wpdb->get_col( "SELECT type FROM {$reviews_table_name} WHERE LOWER(type) IN ('facebook','twitter') GROUP BY type" );
$fbpagesrows = $wpdb->get_results( "SELECT * FROM {$reviews_table_name} WHERE LOWER(type) IN ('facebook','twitter') GROUP BY pageid" );
$mystery_man = trailingslashit( wpfbrev_plugin_url ) . 'public/partials/imgs/facebook_mystery_man.png';
?>
<div class="">
	<h1></h1>
	<div class="wrap" id="wp_rev_maindiv">
		<img class="wprev_headerimg" src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'logo.png' ); ?>">
		<?php
		include 'tabmenu.php';
		echo $dbmsg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built above with escaped text
		?>

		<div class="w3-row">
			<div class="w3-col m3 boxouter analyticsbox">
				<select id="location_multiple_select" class="js-example-basic-multiple" name="wprevlocations[]" multiple="multiple" style="width: 100%">
					<?php
					foreach ( $fbpagesrows as $fbpage ) {
						echo '<option value="' . esc_attr( $fbpage->pageid ) . '">' . esc_html( $fbpage->pagename ) . ' (' . esc_html( $fbpage->type ) . ')</option>';
					}
					?>
				</select>
			</div>
			<div class="w3-col m3 boxouter analyticsbox">
				<select id="type_multiple_select" class="js-example-basic-multiple" name="wprevtypes[]" multiple="multiple" style="width: 100%">
					<?php
					for ( $x = 0; $x < count( $typearray ); $x++ ) {
						$typelowercase = strtolower( $typearray[ $x ] );
						echo '<option value="' . esc_attr( $typelowercase ) . '">' . esc_html( $typearray[ $x ] ) . '</option>';
					}
					?>
				</select>
			</div>
			<div class="w3-col m3 boxouter analyticsbox">
				<input id="wprevpro_analytics_filter_string" type="text" name="wprevpro_analytics_filter_string" placeholder="<?php esc_attr_e( 'Enter Search Text', 'wp-fb-reviews' ); ?>">
			</div>
			<div class="w3-col m3 boxouter analyticsbox">
				<div id="reportrange" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #e5e5e5; width: 100%">
					<i class="fa fa-calendar"></i>&nbsp;
					<span></span> <i class="fa fa-caret-down"></i>
				</div>
			</div>
		</div>
		<div class="w3-row">
			<div class="w3-col m3 boxouter analyticsbox">
				<div class="boxcontent analyticsboxinner">
					<div id="avg_rating" class="wppro_smallheader w3-center"><?php esc_html_e( 'Average Rating:', 'wp-fb-reviews' ); ?>  <span id="avg_rating_num"></span> <span class="svgicons svg-wprsp-star w3-text-gold"></span></div>
					<div class="w3-row">
						<div class="w3-col s1">&nbsp;</div>
						<div class="w3-col s7 starrowdivs">
							<div class="starrow wprevpro_star_imgs"><span class="svgicons svg-wprsp-star"></span><span class="svgicons svg-wprsp-star"></span><span class="svgicons svg-wprsp-star"></span><span class="svgicons svg-wprsp-star"></span><span class="svgicons svg-wprsp-star"></span></div>
							<div class="starrow wprevpro_star_imgs"><span class="svgicons svg-wprsp-star"></span><span class="svgicons svg-wprsp-star"></span><span class="svgicons svg-wprsp-star"></span><span class="svgicons svg-wprsp-star"></span></div>
							<div class="starrow wprevpro_star_imgs"><span class="svgicons svg-wprsp-star"></span><span class="svgicons svg-wprsp-star"></span><span class="svgicons svg-wprsp-star"></span></div>
							<div class="starrow wprevpro_star_imgs"><span class="svgicons svg-wprsp-star"></span><span class="svgicons svg-wprsp-star"></span></div>
							<div class="starrow wprevpro_star_imgs"><span class="svgicons svg-wprsp-star"></span></div>
						</div>
						<div class="w3-col s3 w3-right-align">
							<div id="num_stars_5" class="wppro_smallheader">0</div>
							<div id="num_stars_4" class="wppro_smallheader">0</div>
							<div id="num_stars_3" class="wppro_smallheader">0</div>
							<div id="num_stars_2" class="wppro_smallheader">0</div>
							<div id="num_stars_1" class="wppro_smallheader">0</div>
						</div>
						<div class="w3-col s1">&nbsp;</div>
					</div>
				</div>
				<div class="boxcontent analyticsboxinner">
					<div id="revtypebox" class="w3-row wppro_smallheader">
						<div class="w3-col s7" id="temphtml1"></div>
						<div class="w3-col s5" id="temphtml2"></div>
					</div>
				</div>
			</div>
			<div class="w3-col m9 boxouter w3-center analyticsbox">
				<div class="boxcontent w3-center analyticsboxinner">
					<div id="overallChartspinner" class="loadingspinner"></div>
					<canvas id="overallChart" width="400" height="200"></canvas>
				</div>
			</div>
		</div>

		<div class="w3-row">
			<div class="w3-col m6 boxouter analyticsbox">
				<div class="boxcontent wordclouddivouter analyticsboxinner">
					<div class="wppro_smallheader w3-center"><?php esc_html_e( 'Positive Word Cloud', 'wp-fb-reviews' ); ?></div>
					<div id="positive_word_cloud" class="wordclouddiv"></div>
				</div>
			</div>
			<div class="w3-col m6 boxouter analyticsbox">
				<div class="boxcontent wordclouddivouter analyticsboxinner">
					<div class="wppro_smallheader w3-center"><?php esc_html_e( 'Negative Word Cloud', 'wp-fb-reviews' ); ?></div>
					<div id="negative_word_cloud" class="wordclouddiv"></div>
				</div>
			</div>
		</div>
		<div class="w3-row">
			<div class="w3-col m12 boxouter w3-center analyticsbox">
				<div class="boxcontent w3-center analyticsboxinner">
					<div id="distroChartspinner" class="loadingspinner"></div>
					<canvas id="ratingdistrochart"></canvas>
				</div>
			</div>
		</div>

		<div class="w3-row">
			<div class="w3-col m6 boxouter analyticsbox">
				<div class="boxcontent w3-center analyticsboxinner">
					<div class="wppro_smallheader w3-center"><?php esc_html_e( 'Review Volume Timeline', 'wp-fb-reviews' ); ?></div>
					<div id="volumeChartspinner" class="loadingspinner"></div>
					<canvas id="volumeChart" width="400" height="200"></canvas>
				</div>
			</div>
			<div class="w3-col m6 boxouter analyticsbox">
				<div class="boxcontent w3-center analyticsboxinner">
					<div class="wppro_smallheader w3-center"><?php esc_html_e( 'Platform Rating Comparison', 'wp-fb-reviews' ); ?></div>
					<div id="platformChartspinner" class="loadingspinner"></div>
					<canvas id="platformChart" width="400" height="200"></canvas>
				</div>
			</div>
		</div>

		<div class="w3-row">
			<div class="w3-col m6 boxouter analyticsbox">
				<div class="boxcontent w3-center analyticsboxinner">
					<div class="wppro_smallheader w3-center"><?php esc_html_e( 'Platform Volume Distribution', 'wp-fb-reviews' ); ?></div>
					<div id="platformVolumeChartspinner" class="loadingspinner"></div>
					<canvas id="platformVolumeChart" width="400" height="200"></canvas>
				</div>
			</div>
			<div class="w3-col m6 boxouter analyticsbox">
				<div class="boxcontent w3-center analyticsboxinner">
					<div class="wppro_smallheader w3-center"><?php esc_html_e( 'Rating Trends Over Time', 'wp-fb-reviews' ); ?></div>
					<div id="ratingTrendsChartspinner" class="loadingspinner"></div>
					<canvas id="ratingTrendsChart" width="400" height="200"></canvas>
				</div>
			</div>
		</div>

		<div id="tb_content_popup" style="display:none;">
			<div id="review_details">
				<div class="wpproslider_t6_DIV_1 w3_wprs-col l12">
					<div class="wpproslider_t6_DIV_2 wprev_preview_bg1 wprev_preview_bradius" style="border: 1px solid rgb(238, 238, 238); border-radius: 0px; background: rgb(253, 253, 253);">
						<div class="wpproslider_t6_STRONG_5 wprev_preview_tcolor2">
							<?php esc_html_e( 'Review Source Details', 'wp-fb-reviews' ); ?>
						</div>
						<div class="wpproslider_t6_DIV_4 sourcerevdetails"></div>
					</div>
				</div>
				<div class="wpproslider_t6_DIV_1 w3_wprs-col l12">
					<div class="wpproslider_t6_DIV_2 wprev_preview_bg1 wprev_preview_bradius" style="border: 1px solid rgb(238, 238, 238); border-radius: 0px; background: rgb(253, 253, 253);">
						<div class="wpproslider_t6_DIV_2_top" style="line-height:24px;">
							<div class="wpproslider_t6_DIV_3L">
								<a id="from_url_review" target="_blank">
									<img src="<?php echo esc_url( $mystery_man ); ?>" class="wprev_avatar_opt wpproslider_t6_IMG_2" alt="">
								</a>
							</div>
							<div class="wpproslider_t6_DIV_3">
								<div class="wpproslider_t6_STRONG_5 wprev_preview_tcolor2 t6displayname">
									<span id="wprev_showname"><?php esc_html_e( 'John Smith', 'wp-fb-reviews' ); ?></span>
								</div>
								<div class="wpproslider_t6_star_DIV">
									<span id="starloc1" class="wprevpro_star_imgs" style="color: rgb(253, 211, 20);">
										<span class="svgicons svg-wprsp-star"></span><span class="svgicons svg-wprsp-star"></span><span class="svgicons svg-wprsp-star"></span><span class="svgicons svg-wprsp-star"></span><span class="svgicons svg-wprsp-star-o"></span>
									</span>
								</div>
								<div class="wpproslider_t6_SPAN_6 wprev_preview_tcolor2 t6datediv" style="color: rgb(85, 85, 85);">
									<span id="wprev_showdate">1/12/2017</span>
								</div>
							</div>
						</div>
						<div class="wpproslider_t6_DIV_4">
							<p class="wpproslider_t6_P_4 wprev_preview_tcolor1" style="color: rgb(85, 85, 85);"></p>
						</div>
						<div class="wpproslider_t6_DIV_3_logo">
							<a id="from_url" href="" target="_blank"><img src="" alt="" class="wprevpro_t6_site_logo siteicon"></a>
						</div>
					</div>
				</div>
			</div>
			<div id="review_list" style="display:none;">
				<table class="wp-list-table widefat striped posts">
					<thead>
						<tr>
							<th scope="col" width="80px" sortdir="DESC" sorttype="name" class="wprevpro_tablesort manage-column"><?php esc_html_e( 'Name', 'wp-fb-reviews' ); ?></th>
							<th scope="col" width="70px" sortdir="DESC" sorttype="rating" class="wprevpro_tablesort manage-column"><?php esc_html_e( 'Rating', 'wp-fb-reviews' ); ?></th>
							<th scope="col" class="manage-column"><?php esc_html_e( 'Review Title/Text', 'wp-fb-reviews' ); ?></th>
							<th scope="col" width="75px" sortdir="DESC" sorttype="stime" class="wprevpro_tablesort manage-column"><?php esc_html_e( 'Date', 'wp-fb-reviews' ); ?></th>
							<th scope="col" width="100px" sortdir="DESC" sorttype="stext" class="wprevpro_tablesort manage-column"><?php esc_html_e( 'Words/Char', 'wp-fb-reviews' ); ?></th>
							<th scope="col" width="100px" sortdir="DESC" sorttype="pagename" class="wprevpro_tablesort manage-column"><?php esc_html_e( 'Social Page', 'wp-fb-reviews' ); ?></th>
						</tr>
					</thead>
					<tbody id="review_list_body"></tbody>
				</table>
			</div>
		</div>
	</div>
</div>
</br></br></br></br>
