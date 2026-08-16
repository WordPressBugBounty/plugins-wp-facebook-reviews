<?php
/**
 * Sample AI Analysis admin page (Pro teaser).
 *
 * @package    WP_FB_Reviews
 * @subpackage WP_FB_Reviews/admin/partials
 */

if ( ! current_user_can( 'manage_options' ) ) {
	return;
}
$mystery_man = trailingslashit( wpfbrev_plugin_url ) . 'public/partials/imgs/facebook_mystery_man.png';
?>
<div class="">
	<h1></h1>
	<div class="wrap" id="wp_rev_maindiv">
		<img class="wprev_headerimg" src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'logo.png' ); ?>">
		<?php include 'tabmenu.php'; ?>

		<div class="wpfbr-pro-feature-panel wpfbr-pro-feature-panel--notice">
			<p style="margin: 0; font-size: 14px;">
				<strong><?php esc_html_e( 'Sample AI Analysis', 'wp-fb-reviews' ); ?></strong> —
				<?php esc_html_e( 'This is a sample AI analysis. Generate your own custom analysis by upgrading to the Pro version.', 'wp-fb-reviews' ); ?>
				<a href="https://wpreviewslider.com/" target="_blank" rel="noopener noreferrer" style="font-weight: bold; text-decoration: underline;"><?php esc_html_e( 'Upgrade to Pro', 'wp-fb-reviews' ); ?></a>
				<span style="margin-left:8px;"><?php esc_html_e( 'Use code', 'wp-fb-reviews' ); ?> <code>WPPRO15</code> <?php esc_html_e( 'for 15% off.', 'wp-fb-reviews' ); ?></span>
			</p>
		</div>

		<div class="w3-row">
			<div class="w3-col m12">
				<div id="ai_report_cards" class="ai_report_cardsclass"></div>
			</div>
		</div>
		<div class="w3-col m12 boxouter sentiment_over_time_div">
			<div class="boxcontent">
				<div class="wppro_smallheader"><?php esc_html_e( 'Sentiment Over Time', 'wp-fb-reviews' ); ?></div>
				<div style="height:240px;max-height:240px">
					<canvas id="ai_sentiment_timeline" style="width:100%;height:100%"></canvas>
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
							<th scope="col" width="80px" class="manage-column"><?php esc_html_e( 'Name', 'wp-fb-reviews' ); ?></th>
							<th scope="col" width="70px" class="manage-column"><?php esc_html_e( 'Rating', 'wp-fb-reviews' ); ?></th>
							<th scope="col" class="manage-column"><?php esc_html_e( 'Review Title/Text', 'wp-fb-reviews' ); ?></th>
							<th scope="col" width="75px" class="manage-column"><?php esc_html_e( 'Date', 'wp-fb-reviews' ); ?></th>
							<th scope="col" width="100px" class="manage-column"><?php esc_html_e( 'Words/Char', 'wp-fb-reviews' ); ?></th>
							<th scope="col" width="100px" class="manage-column"><?php esc_html_e( 'Social Page', 'wp-fb-reviews' ); ?></th>
						</tr>
					</thead>
					<tbody id="review_list_body"></tbody>
				</table>
			</div>
		</div>

		<div class="w3-row">
			<div class="w3-col m12 boxouter">
				<div class="boxcontent executive_overview_div">
					<div class="wppro_smallheader"><?php esc_html_e( 'Executive Overview', 'wp-fb-reviews' ); ?></div>
					<div id="ai_report_markdown" style="white-space:pre-wrap"></div>
				</div>
			</div>
		</div>

		<div class="w3-row">
			<div class="w3-col m12 boxouter">
				<div class="wppro_smallheader" style="display:flex;justify-content:space-between;align-items:center">
					<span>
						<button type="button" id="ai_export_md" class="button"><?php esc_html_e( 'Export Full Report', 'wp-fb-reviews' ); ?></button>
						<button type="button" id="ai_toggle_json" class="button"><?php esc_html_e( 'Show Structured JSON', 'wp-fb-reviews' ); ?></button>
						<button type="button" id="ai_export_json" class="button"><?php esc_html_e( 'Export JSON', 'wp-fb-reviews' ); ?></button>
					</span>
				</div>
				<div id="ai_json_container" style="display:none">
					<textarea id="ai_report_json" style="width:100%;height:420px" readonly></textarea>
				</div>
			</div>
		</div>
	</div>
</div>
</br></br></br></br>
