<?php
/**
 * Welcome page: "Check Out My Other Plugins" grid.
 *
 * The same file ships in every free review slider plugin. Only $wprev_op_self (this
 * plugin's WordPress.org slug, left out of the grid) and the text domain change.
 *
 * @package WP_Review_Slider
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$wprev_op_self   = 'wp-facebook-reviews';
$wprev_op_img    = plugin_dir_url( __FILE__ ) . 'imgs/other-plugins/';

if ( ! function_exists( 'get_plugins' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

// Install buttons open WordPress's own plugin details window, which has the Install button.
add_thickbox();
wp_enqueue_script( 'plugin-install' );

/*
 * Each plugin: WordPress.org slug (or '' for Pro), the names it may be installed under
 * (its folder name can differ from the slug), its logo, and a short description.
 */
$wprev_op_plugins = array(
	array(
		'slug'  => 'pinglo-site-answers',
		'names' => array( 'Pinglo Site Answers AI' ),
		'title' => 'Pinglo Site Answers AI',
		'logo'  => 'pinglo-site-answers.png',
		'icon'  => true,
		'new'   => true,
		'text'  => __( 'An AI chatbot trained on your own website. Visitors ask a question and get answers from your pages, posts, and the notes you upload. Uses your own Gemini API key.', 'wp-fb-reviews' ),
	),
	array(
		'slug'  => 'wp-google-places-review-slider',
		'names' => array( 'WP Google Review Slider' ),
		'title' => 'WP Google Review Slider',
		'logo'  => 'wp-google-places-review-slider.png',
		'text'  => __( 'Show your Google Business Profile reviews in a slider or grid with a rating badge. Works for service area businesses too.', 'wp-fb-reviews' ),
	),
	array(
		'slug'  => 'wp-tripadvisor-review-slider',
		'names' => array( 'WP TripAdvisor Review Slider' ),
		'title' => 'WP TripAdvisor Review Slider',
		'logo'  => 'wp-tripadvisor-review-slider.png',
		'text'  => __( 'Show TripAdvisor reviews of your hotel, restaurant, or attraction, with guest photos and a rating badge.', 'wp-fb-reviews' ),
	),
	array(
		'slug'  => 'wp-facebook-reviews',
		'names' => array( 'WP Review Slider' ),
		'title' => 'WP Review Slider for Facebook',
		'logo'  => 'wp-facebook-reviews.png',
		'text'  => __( 'Show your Facebook Page reviews and recommendations, plus X (Twitter) posts, in a slider or grid.', 'wp-fb-reviews' ),
	),
	array(
		'slug'  => 'wp-yelp-review-slider',
		'names' => array( 'WP Yelp Review Slider' ),
		'title' => 'WP Yelp Review Slider',
		'logo'  => 'wp-yelp-review-slider.png',
		'text'  => __( 'Show your Yelp reviews in a slider or grid with a rating badge and review photos.', 'wp-fb-reviews' ),
	),
	array(
		'slug'  => 'wp-airbnb-review-slider',
		'names' => array( 'WP Airbnb Review Slider' ),
		'title' => 'WP Airbnb Review Slider',
		'logo'  => 'wp-airbnb-review-slider.png',
		'text'  => __( 'Show your Airbnb guest reviews in a slider or grid, for one listing or several.', 'wp-fb-reviews' ),
	),
	array(
		'slug'  => 'review-slider-for-woocommerce',
		'names' => array( 'Review Slider for WooCommerce' ),
		'title' => 'Review Slider for WooCommerce',
		'logo'  => 'review-slider-for-woocommerce.png',
		'text'  => __( 'Show your WooCommerce product reviews in a slider or grid on any page, with a block or shortcode.', 'wp-fb-reviews' ),
	),
	array(
		'slug'  => 'wp-zillow-review-slider',
		'names' => array( 'WP Zillow Review Slider' ),
		'title' => 'WP Zillow Review Slider',
		'logo'  => 'wp-zillow-review-slider.png',
		'text'  => __( 'Show your Zillow agent reviews in a slider or grid with a rating badge.', 'wp-fb-reviews' ),
	),
	array(
		'slug'  => 'wp-thumbtack-review-slider',
		'names' => array( 'WP Thumbtack Review Slider' ),
		'title' => 'WP Thumbtack Review Slider',
		'logo'  => 'wp-thumbtack-review-slider.png',
		'text'  => __( 'Show your Thumbtack reviews, with reviewer photos, in a slider or grid with a rating badge.', 'wp-fb-reviews' ),
	),
);

$wprev_op_installed = get_plugins();

/**
 * The installed plugin file for a slug or one of its names, or ''.
 */
$wprev_op_find = static function ( $slug, $names, $name_prefix = '' ) use ( $wprev_op_installed ) {
	foreach ( $wprev_op_installed as $file => $headers ) {
		$name = isset( $headers['Name'] ) ? $headers['Name'] : '';
		if ( ( '' !== $slug && dirname( $file ) === $slug )
			|| in_array( $name, $names, true )
			|| ( '' !== $name_prefix && 0 === strpos( $name, $name_prefix ) ) ) {
			return $file;
		}
	}
	return '';
};

/**
 * The button (or label) for a free plugin: Active, Activate, or Install.
 */
$wprev_op_action = static function ( $plugin ) use ( $wprev_op_find ) {
	$file = $wprev_op_find( $plugin['slug'], $plugin['names'] );
	if ( $file && is_plugin_active( $file ) ) {
		return '<span class="wprev-op__status"><span class="dashicons dashicons-yes-alt" aria-hidden="true"></span>' . esc_html__( 'Active', 'wp-fb-reviews' ) . '</span>';
	}
	if ( $file ) {
		if ( ! current_user_can( 'activate_plugin', $file ) ) {
			return '<span class="wprev-op__status">' . esc_html__( 'Installed', 'wp-fb-reviews' ) . '</span>';
		}
		$url = wp_nonce_url( self_admin_url( 'plugins.php?action=activate&plugin=' . rawurlencode( $file ) ), 'activate-plugin_' . $file );
		/* translators: %s: plugin name. */
		$label = sprintf( __( 'Activate %s', 'wp-fb-reviews' ), $plugin['title'] );
		return '<a class="button wprev-op__btn" href="' . esc_url( $url ) . '" aria-label="' . esc_attr( $label ) . '">' . esc_html__( 'Activate', 'wp-fb-reviews' ) . '</a>';
	}
	if ( current_user_can( 'install_plugins' ) ) {
		$url = self_admin_url( 'plugin-install.php?tab=plugin-information&plugin=' . $plugin['slug'] . '&TB_iframe=true&width=772&height=600' );
		/* translators: %s: plugin name. */
		$label = sprintf( __( 'Install %s', 'wp-fb-reviews' ), $plugin['title'] );
		return '<a class="button wprev-op__btn thickbox open-plugin-details-modal" href="' . esc_url( $url ) . '" aria-label="' . esc_attr( $label ) . '" data-title="' . esc_attr( $plugin['title'] ) . '">' . esc_html__( 'Install free', 'wp-fb-reviews' ) . '</a>';
	}
	/* translators: %s: plugin name. */
	$label = sprintf( __( '%s on WordPress.org (opens in a new tab)', 'wp-fb-reviews' ), $plugin['title'] );
	return '<a class="button wprev-op__btn" href="' . esc_url( 'https://wordpress.org/plugins/' . $plugin['slug'] . '/' ) . '" target="_blank" rel="noopener" aria-label="' . esc_attr( $label ) . '">' . esc_html__( 'View details', 'wp-fb-reviews' ) . '</a>';
};

$wprev_op_pro_file = $wprev_op_find( '', array(), 'WP Review Slider Pro' );
?>
<div class="wprev-op">
	<h3 class="wprev-op__heading"><?php esc_html_e( 'Check Out My Other Plugins', 'wp-fb-reviews' ); ?></h3>
	<p class="wprev-op__lead"><?php esc_html_e( 'More tools from the same developer. The free ones install right from here.', 'wp-fb-reviews' ); ?></p>

	<ul class="wprev-op__grid">
		<li class="wprev-op__card wprev-op__card--pro">
			<div class="wprev-op__logo wprev-op__logo--pro">
				<img src="<?php echo esc_url( $wprev_op_img . 'wp-review-slider-pro.png' ); ?>" width="226" height="56" alt="<?php esc_attr_e( 'WP Review Slider Pro', 'wp-fb-reviews' ); ?>">
			</div>
			<div class="wprev-op__body">
				<p class="wprev-op__text"><?php esc_html_e( 'Every review in one place. Get reviews from Google, Facebook, Yelp, TripAdvisor, Airbnb, and 90+ other sites, downloaded for you every day. Show them with 17 template styles, badges, floats, pop-ins, review forms, and AI summaries, with personal support from the developer.', 'wp-fb-reviews' ); ?></p>
				<div class="wprev-op__actions">
					<?php if ( $wprev_op_pro_file && is_plugin_active( $wprev_op_pro_file ) ) : ?>
						<span class="wprev-op__status"><span class="dashicons dashicons-yes-alt" aria-hidden="true"></span><?php esc_html_e( 'Active', 'wp-fb-reviews' ); ?></span>
					<?php else : ?>
						<a class="button button-primary wprev-op__btn wprev-op__btn--pro" href="https://wpreviewslider.com/" target="_blank" rel="noopener"><?php esc_html_e( 'Get Pro', 'wp-fb-reviews' ); ?><span class="screen-reader-text"> <?php esc_html_e( '(opens in a new tab)', 'wp-fb-reviews' ); ?></span></a>
						<span class="wprev-op__code">
							<?php
							/* translators: %s: promo code. */
							printf( esc_html__( 'Save 15%% with code %s', 'wp-fb-reviews' ), '<code>WPPRO15</code>' );
							?>
						</span>
					<?php endif; ?>
				</div>
			</div>
		</li>

		<?php
		foreach ( $wprev_op_plugins as $wprev_op_plugin ) :
			if ( $wprev_op_self === $wprev_op_plugin['slug'] ) {
				continue;
			}
			?>
			<li class="wprev-op__card<?php echo ! empty( $wprev_op_plugin['new'] ) ? ' wprev-op__card--new' : ''; ?>">
				<div class="wprev-op__logo">
					<?php if ( ! empty( $wprev_op_plugin['icon'] ) ) : ?>
						<img class="wprev-op__icon" src="<?php echo esc_url( $wprev_op_img . $wprev_op_plugin['logo'] ); ?>" width="44" height="44" alt="">
						<span class="wprev-op__name"><?php echo esc_html( $wprev_op_plugin['title'] ); ?></span>
					<?php else : ?>
						<img src="<?php echo esc_url( $wprev_op_img . $wprev_op_plugin['logo'] ); ?>" alt="<?php echo esc_attr( $wprev_op_plugin['title'] ); ?>">
					<?php endif; ?>
					<?php if ( ! empty( $wprev_op_plugin['new'] ) ) : ?>
						<span class="wprev-op__badge"><?php esc_html_e( 'New', 'wp-fb-reviews' ); ?></span>
					<?php endif; ?>
				</div>
				<p class="wprev-op__text"><?php echo esc_html( $wprev_op_plugin['text'] ); ?></p>
				<div class="wprev-op__actions">
					<?php echo $wprev_op_action( $wprev_op_plugin ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built from escaped parts above. ?>
				</div>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
