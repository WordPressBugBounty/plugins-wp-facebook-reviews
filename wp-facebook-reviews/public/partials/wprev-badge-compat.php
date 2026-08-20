<?php
/**
 * Shared helpers so Google and Facebook free sliders can coexist on one page.
 *
 * Both plugins register [wprevpro_usetemplate] and share wpfb_post_templates.
 * These helpers pick the correct display file and badge branding from rtype.
 *
 * @package WP_FB_Reviews
 */

if ( ! function_exists( 'wprev_free_badge_source_from_rtype' ) ) {
	/**
	 * Map a saved template rtype value to a badge source key.
	 *
	 * @param string $rtype_raw JSON or legacy string from wpfb_post_templates.rtype.
	 * @return string google|facebook|twitter
	 */
	function wprev_free_badge_source_from_rtype( $rtype_raw ) {
		$decoded = json_decode( (string) $rtype_raw, true );
		if ( ! is_array( $decoded ) ) {
			$decoded = array();
			if ( (string) $rtype_raw === '["google"]' ) {
				$decoded[] = 'google';
			} elseif ( (string) $rtype_raw === '["fb"]' ) {
				$decoded[] = 'fb';
			} elseif ( (string) $rtype_raw === '["twitter"]' ) {
				$decoded[] = 'twitter';
			}
		}

		$has_google  = in_array( 'google', $decoded, true );
		$has_fb      = in_array( 'fb', $decoded, true );
		$has_twitter = in_array( 'twitter', $decoded, true );

		if ( $has_twitter && ! $has_fb && ! $has_google ) {
			return 'twitter';
		}
		if ( $has_google && ! $has_fb && ! $has_twitter ) {
			return 'google';
		}
		if ( $has_fb ) {
			return 'facebook';
		}
		if ( $has_twitter ) {
			return 'twitter';
		}

		return '';
	}
}

if ( ! function_exists( 'wprev_free_badge_branding' ) ) {
	/**
	 * Powered-by markup and review-us icon for a badge source.
	 *
	 * @param string $source google|facebook|twitter
	 * @return array{powered:string,icon:string,default_reviewus:string}
	 */
	function wprev_free_badge_branding( $source ) {
		$source = strtolower( (string) $source );

		$google_icon = '<svg viewBox="0 0 512 512" height="18" width="18" aria-hidden="true"><g fill="none" fill-rule="evenodd"><path d="M482.56 261.36c0-16.73-1.5-32.83-4.29-48.27H256v91.29h127.01c-5.47 29.5-22.1 54.49-47.09 71.23v59.21h76.27c44.63-41.09 70.37-101.59 70.37-173.46z" fill="#4285f4"></path><path d="M256 492c63.72 0 117.14-21.13 156.19-57.18l-76.27-59.21c-21.13 14.16-48.17 22.53-79.92 22.53-61.47 0-113.49-41.51-132.05-97.3H45.1v61.15c38.83 77.13 118.64 130.01 210.9 130.01z" fill="#34a853"></path><path d="M123.95 300.84c-4.72-14.16-7.4-29.29-7.4-44.84s2.68-30.68 7.4-44.84V150.01H45.1C29.12 181.87 20 217.92 20 256c0 38.08 9.12 74.13 25.1 105.99l78.85-61.15z" fill="#fbbc05"></path><path d="M256 113.86c34.65 0 65.76 11.91 90.22 35.29l67.69-67.69C373.03 43.39 319.61 20 256 20c-92.25 0-172.07 52.89-210.9 130.01l78.85 61.15c18.56-55.78 70.59-97.3 132.05-97.3z" fill="#ea4335"></path><path d="M20 20h472v472H20V20z"></path></g></svg>';

		$facebook_icon = '<svg viewBox="0 0 512 512" height="18" width="18" aria-hidden="true"><circle cx="256" cy="256" r="256" fill="#ffffff"></circle><path fill="#1877f2" d="M355.6 330.2l11.4-74.2h-71.2v-48.1c0-20.3 9.9-40.1 41.8-40.1h32.4V104.9s-29.4-5-57.5-5c-58.7 0-97 35.6-97 100v56.6h-65.2v74.2h65.2V512h80.3V330.2h59.5z"></path></svg>';

		$twitter_icon = '<svg viewBox="0 0 24 24" height="16" width="16" aria-hidden="true"><path fill="#ffffff" d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"></path></svg>';

		if ( 'google' === $source ) {
			$powered_img = defined( 'WPREV_GOOGLE_PLUGIN_URL' ) ? WPREV_GOOGLE_PLUGIN_URL . '/public/partials/imgs/poweredbygooglew.png' : '';
			$powered     = $powered_img
				? '<img class="wprev-google-powered-img" src="' . esc_url( $powered_img ) . '" alt="powered by Google" width="144" height="18" title="powered by Google">'
				: '<span class="wprev-google-powered-txt" style="font-size:12px;color:#777;">powered by Google</span>';

			return array(
				'powered'          => $powered,
				'icon'             => $google_icon,
				'default_reviewus' => 'Review us on',
			);
		}

		if ( 'twitter' === $source ) {
			return array(
				'powered'          => '<span class="wprev-google-powered-txt" style="font-size:12px;color:#777;">powered by X</span>',
				'icon'             => $twitter_icon,
				'default_reviewus' => 'Find us on X!',
			);
		}

		return array(
			'powered'          => '<span class="wprev-google-powered-txt" style="font-size:12px;color:#777;">powered by Facebook</span>',
			'icon'             => $facebook_icon,
			'default_reviewus' => 'Review us on Facebook!',
		);
	}
}

if ( ! function_exists( 'wprev_free_resolve_template_display_file' ) ) {
	/**
	 * Choose the display file that matches the template source.
	 *
	 * @param int    $tid          Template ID.
	 * @param string $fallback     This plugin's display file.
	 * @param string $own_source   google or facebook (this plugin's default).
	 * @return string Absolute path to a display PHP file.
	 */
	function wprev_free_resolve_template_display_file( $tid, $fallback, $own_source ) {
		$tid      = absint( $tid );
		$fallback = (string) $fallback;
		if ( $tid < 1 ) {
			return $fallback;
		}

		global $wpdb;
		$rtype  = $wpdb->get_var( $wpdb->prepare( "SELECT rtype FROM {$wpdb->prefix}wpfb_post_templates WHERE id = %d", $tid ) );
		$source = wprev_free_badge_source_from_rtype( $rtype );
		if ( '' === $source ) {
			$source = $own_source;
		}

		$google_file = defined( 'WPREV_GOOGLE_PLUGIN_DIR' )
			? WPREV_GOOGLE_PLUGIN_DIR . 'public/partials/wp-google-reviews-public-display.php'
			: '';
		$fb_file     = defined( 'wpfbrev_plugin_dir' )
			? wpfbrev_plugin_dir . 'public/partials/wp-fb-reviews-public-display.php'
			: '';

		if ( 'google' === $source && $google_file && file_exists( $google_file ) ) {
			return $google_file;
		}
		if ( ( 'facebook' === $source || 'twitter' === $source ) && $fb_file && file_exists( $fb_file ) ) {
			return $fb_file;
		}

		return $fallback;
	}
}
