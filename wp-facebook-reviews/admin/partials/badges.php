<?php
/**
 * Pro feature teaser: Review Badges.
 *
 * @package    WP_FB_Reviews
 * @subpackage WP_FB_Reviews/admin/partials
 */

if ( ! current_user_can( 'manage_options' ) ) {
	return;
}
?>
<div class="">
	<h1></h1>
	<div class="wrap" id="wp_rev_maindiv">
		<img class="wprev_headerimg" src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'logo.png' ); ?>">
		<?php include 'tabmenu.php'; ?>

		<div class="wprevpro_margin10 wpfbr-pro-feature-panel">
			<h2><?php esc_html_e( 'Review Badges - Pro Feature', 'wp-fb-reviews' ); ?></h2>
			<p><?php esc_html_e( 'Review Badges let you create beautiful summary badges that display your review ratings and totals. Customize badge styles, colors, text, and icons, and add Google Rich Snippets for better SEO. Badges can be shown on posts, pages, or in widget areas, and can link to a URL or open a review slider/popup.', 'wp-fb-reviews' ); ?></p>

			<div style="margin: 20px 0;">
				<h3><?php esc_html_e( 'Badge Examples', 'wp-fb-reviews' ); ?></h3>
				<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 15px; background: #fff; padding: 20px;">
					<div style="text-align: center; background: #fff;">
						<img src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'imgs/badge2.png' ); ?>" alt="<?php esc_attr_e( 'Badge Example 1', 'wp-fb-reviews' ); ?>" style="max-width: 100%; height: auto; background: #fff;">
					</div>
					<div style="text-align: center; background: #fff;">
						<img src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'imgs/Google_badge.png' ); ?>" alt="<?php esc_attr_e( 'Badge Example 2', 'wp-fb-reviews' ); ?>" style="max-width: 100%; height: auto; background: #fff;">
					</div>
					<div style="text-align: center; background: #fff;">
						<img src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'imgs/badge6a.png' ); ?>" alt="<?php esc_attr_e( 'Badge Example 3', 'wp-fb-reviews' ); ?>" style="max-width: 100%; height: auto; background: #fff;">
					</div>
					<div style="text-align: center; background: #fff;">
						<img src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'imgs/badgetemplate4.png' ); ?>" alt="<?php esc_attr_e( 'Badge Example 4', 'wp-fb-reviews' ); ?>" style="max-width: 100%; height: auto; background: #fff;">
					</div>
					<div style="text-align: center; background: #fff;">
						<img src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'imgs/ljapps_badge.png' ); ?>" alt="<?php esc_attr_e( 'Badge Example 5', 'wp-fb-reviews' ); ?>" style="max-width: 100%; height: auto; background: #fff;">
					</div>
					<div style="text-align: center; background: #fff;">
						<img src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'imgs/badge5.png' ); ?>" alt="<?php esc_attr_e( 'Badge Example 6', 'wp-fb-reviews' ); ?>" style="max-width: 100%; height: auto; background: #fff;">
					</div>
				</div>
			</div>

			<p>
				<a href="https://wpreviewslider.com/" target="_blank" class="button button-primary"><?php esc_html_e( 'Upgrade to Pro!', 'wp-fb-reviews' ); ?></a>
				<span style="margin-left:10px;"><?php esc_html_e( 'Use code', 'wp-fb-reviews' ); ?> <code>WPPRO15</code> <?php esc_html_e( 'for 15% off.', 'wp-fb-reviews' ); ?></span>
			</p>
		</div>
	</div>
</div>
