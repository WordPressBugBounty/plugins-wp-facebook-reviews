<?php
/**
 * Setup wizard: from a connected Facebook page to reviews on a page.
 *
 * Steps: get reviews, choose a look, add to the site, finish. Every step uses
 * the same code as the regular screens (the Facebook page download, template
 * save, the block), so a wizard-made template is an ordinary template.
 *
 * Facebook reviews come through the plugin's Facebook app: the Facebook page
 * of the plugin saves an access code, and the browser asks fbapp.ljapps.com
 * (JSONP) for the pages and reviews, then posts them to the wpfb_get_results
 * ajax action. The wizard script does the same, so no server-side download
 * code is needed here.
 *
 * @package    WP_FB_Reviews
 * @subpackage WP_FB_Reviews/admin
 * @since      15.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Setup wizard screen and its ajax actions.
 */
class WP_FB_Reviews_Wizard {

	const PAGE         = 'wpfb-setup';
	const WELCOME      = 'wpfb-welcome-slug';
	const STATE_OPTION = 'wpfb_wizard_state';
	const DONE_OPTION  = 'wpfb_setup_done';
	const NONCE        = 'wpfb_wizard';
	const TEXT_DOMAIN  = 'wp-fb-reviews';
	// Where the plugin's Facebook app shows the Secret Access Code (same as "Get Access Code Here" on the Facebook page).
	const CODE_URL     = 'https://fbapp.ljapps.com/login.php?ut=fr';

	/**
	 * Admin class, for its template save helper.
	 *
	 * @var WP_FB_Reviews_Admin
	 */
	private $admin;

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	private $version;

	/**
	 * @param WP_FB_Reviews_Admin $admin   Admin class instance.
	 * @param string              $version Plugin version.
	 */
	public function __construct( $admin, $version ) {
		$this->admin   = $admin;
		$this->version = $version;
	}

	/**
	 * Wizard URL.
	 *
	 * @return string
	 */
	public static function url() {
		return admin_url( 'admin.php?page=' . self::PAGE );
	}

	/**
	 * Whether the wizard has been finished at least once.
	 *
	 * @return bool
	 */
	public static function is_done() {
		return (bool) get_option( self::DONE_OPTION );
	}

	/**
	 * Hidden admin page (no menu item; opened from the Welcome screen).
	 */
	public function menu() {
		add_submenu_page( '', __( 'Set up WP Review Slider', self::TEXT_DOMAIN ), '', 'manage_options', self::PAGE, array( $this, 'render' ) );
	}

	/**
	 * Hidden pages have no menu title, so give the browser tab one.
	 *
	 * @param string $admin_title Full title.
	 * @param string $title       Page title.
	 * @return string
	 */
	public function admin_title( $admin_title, $title ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only screen check.
		if ( isset( $_GET['page'] ) && self::PAGE === $_GET['page'] && '' === (string) $title ) {
			return __( 'Set up WP Review Slider', self::TEXT_DOMAIN ) . $admin_title;
		}
		return $admin_title;
	}

	/**
	 * Keep the plugin's menu open, with Welcome highlighted, on the wizard screen.
	 *
	 * @param string $parent_file Parent menu slug.
	 * @return string
	 */
	public function parent_file( $parent_file ) {
		global $plugin_page, $submenu_file, $submenu;
		if ( self::PAGE === $plugin_page ) {
			// Core looks up the parent again right after this filter and finds this page under ''
			// (hidden pages live there), which would undo the highlight. Access is already checked.
			if ( isset( $submenu[''] ) ) {
				foreach ( $submenu[''] as $i => $item ) {
					if ( isset( $item[2] ) && self::PAGE === $item[2] ) {
						unset( $submenu[''][ $i ] ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
					}
				}
			}
			$submenu_file = self::WELCOME; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Core's way to pick the highlighted submenu item.
			return self::WELCOME;
		}
		return $parent_file;
	}

	/**
	 * Assets on the wizard screen only.
	 *
	 * @param string $hook Hook suffix.
	 */
	public function assets( $hook ) {
		if ( 'admin_page_' . self::PAGE !== $hook ) {
			return;
		}

		// Front-end template CSS/JS, the lightbox, and window.wprevInitReviewSliders for the live preview.
		// The public class always loads the style 1 (wprev-fb-combine) and style 6 CSS, so the preview can show either.
		$block = new WP_FB_Reviews_Block();
		$block->enqueue_preview_assets();

		wp_enqueue_style( 'wpfb-setup-wizard', plugin_dir_url( __FILE__ ) . 'css/wpfb_setup_wizard.css', array(), $this->asset_version( 'css/wpfb_setup_wizard.css' ) );
		wp_enqueue_script( 'wpfb-setup-wizard', plugin_dir_url( __FILE__ ) . 'js/wpfb_setup_wizard.js', array( 'jquery', 'wp-fb-reviews-block-preview' ), $this->asset_version( 'js/wpfb_setup_wizard.js' ), true );
		wp_localize_script(
			'wpfb-setup-wizard',
			'wpfbWizard',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( self::NONCE ),
				// The Facebook page's own ajax actions (wpfb_get_results, wpfb_avatar_tolocal) check this nonce.
				'fbNonce' => wp_create_nonce( 'randomnoncestring' ),
				// The plugin's Facebook app, the same endpoints admin/js/wprev_facebook.js calls.
				'fbapp'   => array(
					'pages'   => 'https://fbapp.ljapps.com/ajaxlistpages.php',
					'reviews' => 'https://fbapp.ljapps.com/ajaxgetpagerevs-click.php',
				),
				'state'   => $this->js_state(),
				'i18n'    => array(
					'stepOf'         => __( 'Step %1$d of %2$d', self::TEXT_DOMAIN ),
					'continue'       => __( 'Continue', self::TEXT_DOMAIN ),
					'finish'         => __( 'Finish', self::TEXT_DOMAIN ),
					'connectFirst'   => __( 'Connect your Facebook page first', self::TEXT_DOMAIN ),
					'connectAgain'   => __( 'Connect your Facebook page to download new reviews', self::TEXT_DOMAIN ),
					'connectChange'  => __( 'Connect with a different access code', self::TEXT_DOMAIN ),
					'needConnect'    => __( 'Connect your Facebook page first. The steps are above.', self::TEXT_DOMAIN ),
					'needPick'       => __( 'Pick the Facebook page whose reviews you want to show.', self::TEXT_DOMAIN ),
					'needReviews'    => __( 'Get your reviews first, or pick a page that already has reviews.', self::TEXT_DOMAIN ),
					'noCode'         => __( 'No access code is saved yet. Follow the steps above.', self::TEXT_DOMAIN ),
					'codeEmpty'      => __( 'Paste the Secret Access Code from the Facebook window first.', self::TEXT_DOMAIN ),
					'codeChecking'   => __( 'Checking your access code with Facebook…', self::TEXT_DOMAIN ),
					'codeRejected'   => __( 'Facebook didn\'t accept that code. Click Get my access code, then copy and paste the new code. Facebook said:', self::TEXT_DOMAIN ),
					'codeSaved'      => __( 'Connected. Pick your page below and click Get my reviews.', self::TEXT_DOMAIN ),
					'popupBlocked'   => __( 'Your browser blocked the Facebook window, so it opened in a new tab. Come back to this tab when you have the code.', self::TEXT_DOMAIN ),
					'codeFailed'     => __( 'Facebook didn\'t accept the saved access code. Get a new one with the steps above and connect again. Facebook said:', self::TEXT_DOMAIN ),
					'noPages'        => __( 'Your access code works, but no Facebook pages are shared with our app. Click Get my access code again, turn on the page you want, and connect with the new code.', self::TEXT_DOMAIN ),
					'unreachable'    => __( 'Couldn\'t reach the plugin\'s Facebook app. Check your connection and try again.', self::TEXT_DOMAIN ),
					'downloading'    => __( 'Downloading your reviews from Facebook…', self::TEXT_DOMAIN ),
					'downloadingN'   => __( 'Downloading your reviews from Facebook… %s new so far.', self::TEXT_DOMAIN ),
					'fbFailed'       => __( 'Facebook didn\'t send your reviews this time. Wait a minute, then click Get my reviews again. If it keeps happening, contact support with this message:', self::TEXT_DOMAIN ),
					'saveFailed'     => __( 'The reviews came from Facebook, but your site didn\'t save them. Reload this page and try again. If it keeps happening, contact support with this message:', self::TEXT_DOMAIN ),
					'savedOne'       => __( '%s review saved', self::TEXT_DOMAIN ),
					'savedMany'      => __( '%s reviews saved', self::TEXT_DOMAIN ),
					'noneSaved'      => __( 'No reviews saved yet', self::TEXT_DOMAIN ),
					'previewWaiting' => __( 'There are no reviews to show yet. Download your reviews in the first step and this preview fills in.', self::TEXT_DOMAIN ),
					'sampleNote'     => __( 'Sample reviews. Your own Facebook reviews take their place as soon as they\'re saved.', self::TEXT_DOMAIN ),
					'requestFailed'  => __( 'The request failed. Check your connection and try again.', self::TEXT_DOMAIN ),
					'saving'         => __( 'Updating the preview…', self::TEXT_DOMAIN ),
					'noTemplate'     => __( 'The preview is still being made. Try again in a moment.', self::TEXT_DOMAIN ),
					'creatingPage'   => __( 'Creating the page…', self::TEXT_DOMAIN ),
					'copied'         => __( 'Copied', self::TEXT_DOMAIN ),
					'copy'           => __( 'Copy', self::TEXT_DOMAIN ),
				),
			)
		);
	}

	/**
	 * Cache-busting version for a file in admin/.
	 *
	 * @param string $relative Path relative to admin/.
	 * @return string
	 */
	private function asset_version( $relative ) {
		$path = plugin_dir_path( __FILE__ ) . $relative;
		return file_exists( $path ) ? (string) filemtime( $path ) : $this->version;
	}

	/* ---------- Facts the steps use ---------- */

	/**
	 * Saved wizard progress.
	 *
	 * @return array{pageid:string,pagename:string,tid:int,page_id:int}
	 */
	private function state() {
		$state = get_option( self::STATE_OPTION, array() );
		if ( ! is_array( $state ) ) {
			$state = array();
		}
		return array(
			'pageid'   => isset( $state['pageid'] ) ? (string) $state['pageid'] : '',
			'pagename' => isset( $state['pagename'] ) ? (string) $state['pagename'] : '',
			'tid'      => isset( $state['tid'] ) ? absint( $state['tid'] ) : 0,
			'page_id'  => isset( $state['page_id'] ) ? absint( $state['page_id'] ) : 0,
		);
	}

	/**
	 * Merge values into the saved wizard progress.
	 *
	 * @param array $values Keys of state().
	 */
	private function update_state( $values ) {
		update_option( self::STATE_OPTION, array_merge( $this->state(), $values ), false );
	}

	/**
	 * Access code saved on the plugin's Facebook page ('' when not connected).
	 *
	 * @return string
	 */
	private function access_code() {
		$options = get_option( 'wpfbr_facebook' );
		return ( is_array( $options ) && ! empty( $options['fb_app_code'] ) ) ? trim( (string) $options['fb_app_code'] ) : '';
	}

	/**
	 * Whether a value looks like a Facebook page id.
	 *
	 * @param string $pageid Page id.
	 * @return bool
	 */
	private function valid_pageid( $pageid ) {
		return (bool) preg_match( '/^[A-Za-z0-9_.-]{1,150}$/', (string) $pageid );
	}

	/**
	 * Facebook pages with saved reviews, and how many each has.
	 *
	 * @return array<int,array{pageid:string,name:string,url:string,count:int}>
	 */
	private function saved_sources() {
		global $wpdb;
		$table = $wpdb->prefix . 'wpfb_reviews';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Small count for the setup screen.
		$rows    = $wpdb->get_results( $wpdb->prepare( "SELECT pageid, MAX(pagename) AS pagename, MAX(from_url) AS from_url, COUNT(*) AS n FROM {$table} WHERE type = %s AND pageid != '' GROUP BY pageid ORDER BY n DESC", 'Facebook' ) );
		$sources = array();
		foreach ( (array) $rows as $row ) {
			$pageid = (string) $row->pageid;
			// The Facebook page stores apostrophes as %27 (and the ajax post adds slashes).
			$name      = str_replace( '%27', "'", wp_unslash( (string) $row->pagename ) );
			$sources[] = array(
				'pageid' => $pageid,
				'name'   => '' !== $name ? $name : $pageid,
				'url'    => '' !== (string) $row->from_url ? (string) $row->from_url : $this->reviews_url( $pageid ),
				'count'  => (int) $row->n,
			);
		}
		return $sources;
	}

	/**
	 * One saved source by page id.
	 *
	 * @param string $pageid Page id.
	 * @return array{pageid:string,name:string,url:string,count:int}|null
	 */
	private function saved_source( $pageid ) {
		foreach ( $this->saved_sources() as $source ) {
			if ( $source['pageid'] === (string) $pageid ) {
				return $source;
			}
		}
		return null;
	}

	/**
	 * Reviews address of a Facebook page, as the download code saves it in from_url.
	 *
	 * @param string $pageid Page id.
	 * @return string
	 */
	private function reviews_url( $pageid ) {
		return 'https://www.facebook.com/pg/' . rawurlencode( (string) $pageid ) . '/reviews/';
	}

	/**
	 * A saved template's title, or '' when it no longer exists.
	 *
	 * @param int $tid Template id.
	 * @return string
	 */
	private function template_title( $tid ) {
		if ( $tid < 1 ) {
			return '';
		}
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$title = $wpdb->get_var( $wpdb->prepare( "SELECT title FROM {$wpdb->prefix}wpfb_post_templates WHERE id = %d", $tid ) );
		return null === $title ? '' : (string) $title;
	}

	/**
	 * Links for a page the wizard created, if it still exists.
	 *
	 * @param int $page_id Page id.
	 * @return array{id:int,title:string,edit:string,view:string,status:string}|null
	 */
	private function page_info( $page_id ) {
		$post = $page_id ? get_post( $page_id ) : null;
		if ( ! $post || 'page' !== $post->post_type || 'trash' === $post->post_status ) {
			return null;
		}
		return array(
			'id'     => (int) $post->ID,
			'title'  => get_the_title( $post ),
			'edit'   => (string) get_edit_post_link( $post->ID, 'raw' ),
			'view'   => 'publish' === $post->post_status ? (string) get_permalink( $post ) : (string) get_preview_post_link( $post ),
			'status' => (string) $post->post_status,
		);
	}

	/**
	 * State handed to the wizard script.
	 *
	 * The connected pages are not stored on the site; the script asks the
	 * plugin's Facebook app for them with the access code.
	 *
	 * @return array
	 */
	private function js_state() {
		$state  = $this->state();
		$saved  = $this->saved_sources();
		$pageid = $state['pageid'];
		if ( '' === $pageid && $saved ) {
			$pageid = $saved[0]['pageid'];
		}
		$title = $this->template_title( $state['tid'] );
		return array(
			'code'     => $this->access_code(),
			'saved'    => $saved,
			'pageid'   => $pageid,
			'pagename' => $state['pagename'],
			'tid'      => '' === $title ? 0 : $state['tid'],
			'title'    => $title,
			'page'     => $this->page_info( $state['page_id'] ),
		);
	}

	/* ---------- Screen ---------- */

	/**
	 * Render the wizard.
	 */
	public function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$d            = self::TEXT_DOMAIN;
		$steps        = array(
			'source' => __( 'Get reviews', $d ),
			'look'   => __( 'Choose a look', $d ),
			'place'  => __( 'Add to your site', $d ),
			'finish' => __( 'Finish', $d ),
		);
		$welcome      = admin_url( 'admin.php?page=' . self::WELCOME );
		$facebook_url = admin_url( 'admin.php?page=wpfb-facebook' );
		$twitter_url  = admin_url( 'admin.php?page=wpfb-get_twitter' );
		$pro_url      = 'https://wpreviewslider.com/';
		?>
		<div class="wrap wpfb-wiz" data-wpfb-wizard>
			<hr class="wp-header-end">
			<div class="wpfb-wiz__header">
				<img class="wpfb-wiz__logo" src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'partials/logo.png?v=' . $this->version ); ?>" alt="">
				<div class="wpfb-wiz__titles">
					<h1><?php esc_html_e( 'Set up WP Review Slider', $d ); ?></h1>
					<p><?php esc_html_e( 'About three minutes. You can change all of it later.', $d ); ?></p>
				</div>
				<a class="button wpfb-wiz__exit" href="<?php echo esc_url( $welcome ); ?>"><?php esc_html_e( 'Exit setup', $d ); ?></a>
			</div>

			<ol class="wpfb-wiz__steps" aria-label="<?php esc_attr_e( 'Setup steps', $d ); ?>">
				<?php
				$number = 0;
				foreach ( $steps as $key => $label ) :
					++$number;
					?>
					<li class="wpfb-wiz__stepitem" data-step-item="<?php echo esc_attr( $key ); ?>">
						<button type="button" class="wpfb-wiz__stepbtn" data-goto="<?php echo esc_attr( $key ); ?>" disabled>
							<span class="wpfb-wiz__stepnum" aria-hidden="true"><span class="wpfb-wiz__n"><?php echo esc_html( number_format_i18n( $number ) ); ?></span><span class="dashicons dashicons-yes"></span></span>
							<span class="wpfb-wiz__steplabel"><?php echo esc_html( $label ); ?></span>
						</button>
					</li>
				<?php endforeach; ?>
			</ol>

			<?php /* ---- Step 1: get reviews ---- */ ?>
			<section class="wpfb-wiz__card" data-step="source" aria-labelledby="wpfb-wiz-h-source">
				<h2 id="wpfb-wiz-h-source" tabindex="-1"><?php esc_html_e( 'Get your Facebook reviews', $d ); ?></h2>
				<p class="wpfb-wiz__lead"><?php esc_html_e( 'Pick a Facebook page you manage. The plugin downloads its reviews and recommendations and saves them on your site, so they load fast and keep showing even if Facebook is slow.', $d ); ?></p>

				<div class="wpfb-wiz__connect" data-connect hidden>
					<h3 data-connect-title><?php esc_html_e( 'Connect your Facebook page first', $d ); ?></h3>
					<p><?php esc_html_e( 'Facebook only shares reviews with the plugin\'s Facebook app after you allow it. It takes about a minute, and you stay in this setup:', $d ); ?></p>
					<ol class="wpfb-wiz__list wpfb-wiz__list--steps">
						<li><?php esc_html_e( 'Click Get my access code. A Facebook window opens: log in, choose the page whose reviews you want to show, and allow the app to read them.', $d ); ?></li>
						<li><?php esc_html_e( 'Copy the Secret Access Code the app shows you at the end.', $d ); ?></li>
						<li><?php esc_html_e( 'Paste it below and click Connect.', $d ); ?></li>
					</ol>
					<p class="wpfb-wiz__actions">
						<a class="button button-primary" href="<?php echo esc_url( self::CODE_URL ); ?>" target="_blank" rel="noopener" data-get-code><?php esc_html_e( 'Get my access code', $d ); ?><span class="screen-reader-text"> <?php esc_html_e( '(opens a Facebook window)', $d ); ?></span></a>
					</p>
					<p class="wpfb-wiz__codefield">
						<label for="wpfb-wiz-code"><strong><?php esc_html_e( 'Secret Access Code', $d ); ?></strong></label><br>
						<span class="wpfb-wiz__actions">
							<input type="text" id="wpfb-wiz-code" class="wpfb-wiz__input wpfb-wiz__input--short" autocomplete="off" spellcheck="false" data-code-input>
							<button type="button" class="button button-primary" data-connect-code><?php esc_html_e( 'Connect', $d ); ?></button>
							<span class="spinner" data-check-spinner></span>
						</span>
					</p>
					<div class="wpfb-wiz__status" data-connect-status role="status" aria-live="polite" hidden></div>
					<p class="description">
						<?php esc_html_e( 'Setting this up for a client? Delete your access code on the plugin\'s Facebook page after their reviews are downloaded, so they can\'t see the other pages you manage.', $d ); ?>
						<button type="button" class="button-link" data-check-again><?php esc_html_e( 'Saved a code on the Facebook page already? Check again', $d ); ?></button>
					</p>
				</div>

				<div class="wpfb-wiz__status is-info" data-pages-loading role="status" aria-live="polite" hidden><?php esc_html_e( 'Loading your Facebook pages…', $d ); ?></div>

				<fieldset class="wpfb-wiz__sources" data-sources hidden>
					<legend><?php esc_html_e( 'Which page\'s reviews do you want to show?', $d ); ?></legend>
					<div class="wpfb-wiz__sourcelist" data-source-list></div>
				</fieldset>

				<div data-get-wrap hidden>
					<p class="wpfb-wiz__actions">
						<button type="button" class="button button-primary" data-get-reviews><?php esc_html_e( 'Get my reviews', $d ); ?></button>
						<span class="spinner" data-get-spinner></span>
					</p>
					<p class="description">
						<?php esc_html_e( 'Page missing? Get a new access code, turn that page on in the Facebook window, and connect again.', $d ); ?>
						<button type="button" class="button-link" data-change-code><?php esc_html_e( 'Use a different access code', $d ); ?></button>
					</p>
				</div>

				<div class="wpfb-wiz__status" data-source-status role="status" aria-live="polite" hidden></div>

				<p class="wpfb-wiz__note">
					<?php
					echo wp_kses_post(
						sprintf(
							/* translators: 1: link to the X (Twitter) page, 2: link to the Pro version. */
							__( 'Want to show posts from X (Twitter) too? Add them on the %1$s page after setup. %2$s checks for new Facebook reviews every day and adds Google and 100+ other sites.', $d ),
							'<a href="' . esc_url( $twitter_url ) . '">' . esc_html__( 'X (Twitter)', $d ) . '</a>',
							'<a href="' . esc_url( $pro_url ) . '" target="_blank" rel="noopener">' . esc_html__( 'WP Review Slider Pro', $d ) . '</a>'
						)
					);
					?>
				</p>
			</section>

			<?php /* ---- Step 2: choose a look ---- */ ?>
			<section class="wpfb-wiz__card" data-step="look" aria-labelledby="wpfb-wiz-h-look" hidden>
				<h2 id="wpfb-wiz-h-look" tabindex="-1"><?php esc_html_e( 'Choose how your reviews look', $d ); ?></h2>
				<p class="wpfb-wiz__lead"><?php esc_html_e( 'The preview uses your own reviews and updates as you choose.', $d ); ?></p>

				<div class="wpfb-wiz__controls">
					<fieldset class="wpfb-wiz__group">
						<legend><?php esc_html_e( 'Style', $d ); ?></legend>
						<div class="wpfb-wiz__segmented">
							<label><input type="radio" name="wpfb-wiz-style" value="1" checked><span><?php esc_html_e( 'Speech bubble', $d ); ?></span></label>
							<label><input type="radio" name="wpfb-wiz-style" value="6"><span><?php esc_html_e( 'Card', $d ); ?></span></label>
						</div>
					</fieldset>
					<fieldset class="wpfb-wiz__group">
						<legend><?php esc_html_e( 'Layout', $d ); ?></legend>
						<div class="wpfb-wiz__segmented">
							<label><input type="radio" name="wpfb-wiz-layout" value="slider" checked><span><?php esc_html_e( 'Slider', $d ); ?></span></label>
							<label><input type="radio" name="wpfb-wiz-layout" value="grid"><span><?php esc_html_e( 'Grid', $d ); ?></span></label>
						</div>
					</fieldset>
					<fieldset class="wpfb-wiz__group">
						<legend><?php esc_html_e( 'Reviews per row', $d ); ?></legend>
						<div class="wpfb-wiz__segmented">
							<?php foreach ( array( 1, 2, 3, 4 ) as $n ) : ?>
								<label><input type="radio" name="wpfb-wiz-perrow" value="<?php echo esc_attr( $n ); ?>" <?php checked( 3, $n ); ?>><span><?php echo esc_html( number_format_i18n( $n ) ); ?></span></label>
							<?php endforeach; ?>
						</div>
					</fieldset>
					<fieldset class="wpfb-wiz__group">
						<legend><?php esc_html_e( 'Number of rows', $d ); ?></legend>
						<div class="wpfb-wiz__segmented">
							<?php foreach ( array( 1, 2, 3, 4 ) as $n ) : ?>
								<label><input type="radio" name="wpfb-wiz-rows" value="<?php echo esc_attr( $n ); ?>" <?php checked( 1, $n ); ?>><span><?php echo esc_html( number_format_i18n( $n ) ); ?></span></label>
							<?php endforeach; ?>
						</div>
					</fieldset>
					<fieldset class="wpfb-wiz__group">
						<legend><?php esc_html_e( 'Rating badge', $d ); ?></legend>
						<div class="wpfb-wiz__segmented">
							<?php
							foreach ( array(
								'left'  => __( 'Left', $d ),
								'above' => __( 'Above', $d ),
								'right' => __( 'Right', $d ),
								'none'  => __( 'None', $d ),
							) as $value => $label ) :
								?>
								<label><input type="radio" name="wpfb-wiz-badge" value="<?php echo esc_attr( $value ); ?>" <?php checked( 'above', $value ); ?>><span><?php echo esc_html( $label ); ?></span></label>
							<?php endforeach; ?>
						</div>
					</fieldset>
					<fieldset class="wpfb-wiz__group">
						<legend><?php esc_html_e( 'Read more', $d ); ?></legend>
						<div class="wpfb-wiz__segmented">
							<?php
							// Long reviews are cut after this many words with a "read more" link. 30 keeps the cards even.
							foreach ( array( 'off', '20', '30', '50' ) as $value ) :
								/* translators: %s: number of words. */
								$label = 'off' === $value ? __( 'Off', $d ) : sprintf( __( '%s words', $d ), number_format_i18n( (int) $value ) );
								?>
								<label><input type="radio" name="wpfb-wiz-readmore" value="<?php echo esc_attr( $value ); ?>" <?php checked( '30', $value ); ?>><span><?php echo esc_html( $label ); ?></span></label>
							<?php endforeach; ?>
						</div>
					</fieldset>
				</div>
				<p class="wpfb-wiz__hint" data-badge-hint hidden><?php esc_html_e( 'Tip: with the badge beside the reviews, 1 or 2 reviews per row fit best.', $d ); ?></p>

				<div class="wpfb-wiz__preview" data-preview-wrap>
					<div class="wpfb-wiz__previewbar">
						<strong><?php esc_html_e( 'Preview', $d ); ?></strong>
						<span class="spinner" data-preview-spinner></span>
						<span class="wpfb-wiz__muted" data-preview-msg></span>
					</div>
					<div class="wpfb-wiz__previewbody" data-preview></div>
				</div>
				<p class="wpfb-wiz__note"><?php esc_html_e( 'More colors, filters, and settings are on the Templates page after setup. Pro adds 15 more styles.', $d ); ?></p>
			</section>

			<?php /* ---- Step 3: add to the site ---- */ ?>
			<section class="wpfb-wiz__card" data-step="place" aria-labelledby="wpfb-wiz-h-place" hidden>
				<h2 id="wpfb-wiz-h-place" tabindex="-1"><?php esc_html_e( 'Add the reviews to your site', $d ); ?></h2>
				<p class="wpfb-wiz__lead">
					<?php
					echo wp_kses_post(
						sprintf(
							/* translators: %s: template name. */
							__( 'Your reviews are saved as the template %s. Put it on a new page, or add it to a page you already have.', $d ),
							'<strong data-template-title></strong>'
						)
					);
					?>
				</p>

				<div class="wpfb-wiz__choices">
					<div class="wpfb-wiz__choice">
						<h3><?php esc_html_e( 'Create a new page', $d ); ?></h3>
						<p><?php esc_html_e( 'Makes a draft page with your reviews on it. Nothing is public until you publish it.', $d ); ?></p>
						<div data-page-form>
							<p>
								<label for="wpfb-wiz-pagetitle"><strong><?php esc_html_e( 'Page title', $d ); ?></strong></label><br>
								<input type="text" id="wpfb-wiz-pagetitle" class="wpfb-wiz__input wpfb-wiz__input--short" value="<?php esc_attr_e( 'Reviews', $d ); ?>">
							</p>
							<p class="wpfb-wiz__actions">
								<button type="button" class="button button-primary" data-create-page><?php esc_html_e( 'Create the page', $d ); ?></button>
								<span class="spinner" data-page-spinner></span>
							</p>
						</div>
						<div class="wpfb-wiz__status" data-page-status role="status" aria-live="polite" hidden></div>
						<p class="wpfb-wiz__actions" data-page-links hidden>
							<a class="button button-primary" data-page-edit href="#" target="_blank" rel="noopener"><?php esc_html_e( 'Edit and publish the page', $d ); ?></a>
							<a class="button" data-page-view href="#" target="_blank" rel="noopener"><?php esc_html_e( 'Preview', $d ); ?></a>
						</p>
					</div>

					<div class="wpfb-wiz__choice">
						<h3><?php esc_html_e( 'Add to a page you already have', $d ); ?></h3>
						<p>
							<?php
							echo wp_kses_post(
								sprintf(
									/* translators: 1: block name, 2: template name. */
									__( 'In the block editor, click the + button, search for %1$s, and pick %2$s from the block toolbar.', $d ),
									'<strong>' . esc_html__( 'WP Review Slider', $d ) . '</strong>',
									'<strong data-template-title></strong>'
								)
							);
							?>
						</p>
						<p><?php esc_html_e( 'Using the classic editor, a page builder, or a text widget? Paste this shortcode:', $d ); ?></p>
						<p class="wpfb-wiz__shortcode">
							<code data-shortcode></code>
							<button type="button" class="button" data-copy-shortcode><?php esc_html_e( 'Copy', $d ); ?></button>
						</p>
					</div>
				</div>
			</section>

			<?php /* ---- Step 4: finish ---- */ ?>
			<section class="wpfb-wiz__card" data-step="finish" aria-labelledby="wpfb-wiz-h-finish" hidden>
				<h2 id="wpfb-wiz-h-finish" tabindex="-1"><?php esc_html_e( 'You\'re all set', $d ); ?></h2>
				<p class="wpfb-wiz__status is-info" data-finish-empty hidden>
					<?php
					echo wp_kses_post(
						sprintf(
							/* translators: %s: link to the plugin's Facebook page. */
							__( 'No reviews from this Facebook page are saved yet, so your site shows them as soon as they are. Download them on the %s page whenever the page has reviews.', $d ),
							'<a href="' . esc_url( $facebook_url ) . '">' . esc_html__( 'Facebook', $d ) . '</a>'
						)
					);
					?>
				</p>
				<ul class="wpfb-wiz__list">
					<li>
						<?php
						echo wp_kses_post(
							sprintf(
								/* translators: %s: link to the Templates page. */
								__( 'Change the colors, filters, and other settings of your template on the %s page.', $d ),
								'<a href="' . esc_url( admin_url( 'admin.php?page=wpfb-templates_posts' ) ) . '">' . esc_html__( 'Templates', $d ) . '</a>'
							)
						);
						?>
					</li>
					<li>
						<?php
						echo wp_kses_post(
							sprintf(
								/* translators: %s: link to the Review List page. */
								__( 'Hide any review you don\'t want to show on the %s page.', $d ),
								'<a href="' . esc_url( admin_url( 'admin.php?page=wpfb-reviews' ) ) . '">' . esc_html__( 'Review List', $d ) . '</a>'
							)
						);
						?>
					</li>
					<li>
						<?php
						echo wp_kses_post(
							sprintf(
								/* translators: %s: link to the plugin's Facebook page. */
								__( 'When you get new Facebook reviews, download them again on the %s page. Pro does this for you every day.', $d ),
								'<a href="' . esc_url( $facebook_url ) . '">' . esc_html__( 'Facebook', $d ) . '</a>'
							)
						);
						?>
					</li>
				</ul>
				<p class="wpfb-wiz__note">
					<?php
					echo wp_kses_post(
						sprintf(
							/* translators: 1: link to the Pro version, 2: promo code. */
							__( 'Want reviews from Google, Yelp, and 100+ other sites, more styles, and badges? Try %1$s and use code %2$s for 15%% off.', $d ),
							'<a href="' . esc_url( $pro_url ) . '" target="_blank" rel="noopener">' . esc_html__( 'WP Review Slider Pro', $d ) . '</a>',
							'<code>WPPRO15</code>'
						)
					);
					?>
				</p>
			</section>

			<div class="wpfb-wiz__nav">
				<button type="button" class="button" data-prev><?php esc_html_e( 'Back', $d ); ?></button>
				<span class="wpfb-wiz__count" data-count aria-live="polite"></span>
				<span class="wpfb-wiz__navmsg" data-nav-msg role="alert"></span>
				<button type="button" class="button button-primary" data-next><?php esc_html_e( 'Continue', $d ); ?></button>
			</div>
		</div>
		<?php
	}

	/* ---------- Ajax ---------- */

	/**
	 * Common checks for the wizard's ajax actions.
	 */
	private function check_request() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You don\'t have permission to do this.', self::TEXT_DOMAIN ) ), 403 );
		}
		check_ajax_referer( self::NONCE, 'nonce' );
	}

	/**
	 * Save the Secret Access Code pasted in the wizard, to the same setting the
	 * Facebook page's "Secret Access Code" field uses. The script checks the code
	 * with the plugin's Facebook app before sending it here.
	 */
	public function ajax_save_code() {
		$this->check_request();
		$code = isset( $_POST['code'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['code'] ) ) ) : '';
		if ( ! preg_match( '/^\S{4,300}$/', $code ) ) {
			wp_send_json_error( array( 'message' => __( 'That doesn\'t look like a Secret Access Code. Copy it again from the Facebook window and paste it here.', self::TEXT_DOMAIN ) ) );
		}

		// Keep the page's other setting (Recommendations as stars).
		$options = get_option( 'wpfbr_facebook' );
		if ( ! is_array( $options ) ) {
			$options = array();
		}
		$options['fb_app_code'] = $code;
		update_option( 'wpfbr_facebook', $options );

		wp_send_json_success(
			array(
				'code'  => $this->access_code(),
				'saved' => $this->saved_sources(),
			)
		);
	}

	/**
	 * The access code and the pages with saved reviews ("Check again"), and,
	 * after a download, how many reviews that page now has.
	 */
	public function ajax_sources() {
		$this->check_request();
		$d    = self::TEXT_DOMAIN;
		$data = array(
			'code'  => $this->access_code(),
			'saved' => $this->saved_sources(),
		);

		$pageid = isset( $_POST['pageid'] ) ? sanitize_text_field( wp_unslash( $_POST['pageid'] ) ) : '';
		if ( '' === $pageid ) {
			wp_send_json_success( $data );
		}
		if ( ! $this->valid_pageid( $pageid ) ) {
			wp_send_json_error( array( 'message' => __( 'Pick your Facebook page again.', $d ) ) );
		}

		$pagename = isset( $_POST['pagename'] ) ? sanitize_text_field( wp_unslash( $_POST['pagename'] ) ) : '';
		$source   = $this->saved_source( $pageid );
		$count    = $source ? $source['count'] : 0;
		$name     = '' !== $pagename ? $pagename : ( $source ? $source['name'] : $pageid );

		$data['pageid'] = $pageid;
		$data['count']  = $count;
		if ( $count > 0 ) {
			$data['message'] = sprintf(
				/* translators: 1: number of reviews, 2: Facebook page name. */
				_n( '%1$s review from %2$s is saved on your site.', '%1$s reviews from %2$s are saved on your site.', $count, $d ),
				number_format_i18n( $count ),
				$name
			);
		} else {
			// The download worked, but Facebook had nothing to send. The wizard can still go on with sample reviews.
			$data['message'] = sprintf(
				/* translators: %s: Facebook page name. */
				__( 'Facebook didn\'t send any reviews for %s. If the page has reviews, check that its Reviews tab is turned on and public, then click Get my reviews again. You can also keep going now: the preview uses sample reviews until yours are saved.', $d ),
				$name
			);
		}

		$this->update_state(
			array(
				'pageid'   => $pageid,
				'pagename' => $name,
			)
		);
		wp_send_json_success( $data );
	}

	/**
	 * Create or update the wizard's template and return its preview.
	 */
	public function ajax_save_template() {
		$this->check_request();
		$d = self::TEXT_DOMAIN;

		$pageid   = isset( $_POST['pageid'] ) ? sanitize_text_field( wp_unslash( $_POST['pageid'] ) ) : '';
		$pagename = isset( $_POST['pagename'] ) ? sanitize_text_field( wp_unslash( $_POST['pagename'] ) ) : '';
		$style    = isset( $_POST['style'] ) && '6' === $_POST['style'] ? '6' : '1';
		$slider   = ! isset( $_POST['layout'] ) || 'grid' !== $_POST['layout'];
		$per_row  = isset( $_POST['per_row'] ) ? min( 4, max( 1, absint( $_POST['per_row'] ) ) ) : 3;
		$rows     = isset( $_POST['rows'] ) ? min( 4, max( 1, absint( $_POST['rows'] ) ) ) : 1;
		// Read more: 'off', or the number of words shown before the "read more" link.
		$readmore = isset( $_POST['read_more'] ) && in_array( $_POST['read_more'], array( 'off', '20', '30', '50' ), true ) ? $_POST['read_more'] : '30';
		// Badge position. "Above" is the full-width bar, which leaves every review card its full width.
		$badges = array(
			'left'  => 'left',
			'above' => 'abovewide',
			'right' => 'right',
			'none'  => '',
		);
		$badge  = ( isset( $_POST['badge'] ) && isset( $badges[ $_POST['badge'] ] ) ) ? $badges[ $_POST['badge'] ] : 'abovewide';
		$tid    = isset( $_POST['tid'] ) ? absint( $_POST['tid'] ) : 0;

		if ( ! $this->valid_pageid( $pageid ) ) {
			wp_send_json_error( array( 'message' => __( 'Pick your Facebook page in the first step.', $d ) ) );
		}
		if ( $tid && '' === $this->template_title( $tid ) ) {
			$tid = 0; // Deleted since; make a new one.
		}

		$source = $this->saved_source( $pageid );
		$count  = $source ? $source['count'] : 0;
		$name   = $source ? $source['name'] : ( '' !== $pagename ? $pagename : $pageid );
		$url    = $source ? $source['url'] : $this->reviews_url( $pageid );
		// Size the slider for the reviews saved, or for about 10 while none are saved yet.
		$planned = $count > 0 ? $count : 10;
		// A slide (or the grid) holds per_row x rows reviews; a slider gets enough slides for the rest.
		$slides = (int) ceil( $planned / ( $per_row * $rows ) );

		// Colors match what the Templates page fills in for a new template of each style.
		$colors = '6' === $style
			? array( 'bradius' => '4', 'bg1' => '#fdfdfd', 'bg2' => '#ffffff', 't1' => '#555555', 't2' => '#555555' )
			: array( 'bradius' => '0', 'bg1' => '#ffffff', 'bg2' => '#ffffff', 't1' => '#777777', 't2' => '#555555' );

		$fields = array(
			'edittid'                          => $tid ? (string) $tid : '',
			/* translators: %s: Facebook page name. */
			'wpfbr_template_title'             => sprintf( __( '%s Facebook Reviews', $d ), $name ),
			'wpfbr_template_type'              => 'post',
			'wprevpro_template_style'          => $style,
			'wpfbr_t_display_num'              => (string) $per_row,
			'wpfbr_t_display_num_rows'         => (string) $rows,
			'wpfbr_t_display_order'            => 'newest',
			'wpfbr_t_hidenotext'               => 'yes',
			'wpfbr_template_css'               => '',
			'wpfbr_t_createslider'             => $slider ? 'yes' : 'no',
			'wpfbr_t_numslides'                => (string) min( 10, max( 2, $slides ) ),
			'wprevpro_t_read_more'             => 'off' === $readmore ? 'no' : 'yes',
			'wprevpro_t_read_more_text'        => __( 'read more', $d ),
			'wprevpro_t_read_more_num'         => 'off' === $readmore ? '30' : $readmore,
			// "Show All" also includes positive recommendations.
			'wpfbr_t_min_rating'               => '1',
			// Facebook reviews only, from the page picked in step 1 (saved in template_misc filtersource and rpage).
			'wpfbr_t_rtype'                    => 'fb',
			'wpfbr_t_filtersource'             => $pageid,
			'wprevpro_template_misc_showstars' => 'yes',
			'wprevpro_template_misc_showdate'  => 'yes',
			'wprevpro_template_misc_bgcolor1'  => $colors['bg1'],
			'wprevpro_template_misc_bgcolor2'  => $colors['bg2'],
			'wprevpro_template_misc_tcolor1'   => $colors['t1'],
			'wprevpro_template_misc_tcolor2'   => $colors['t2'],
			'wprevpro_template_misc_tcolor3'   => '',
			'wprevpro_template_misc_bradius'   => $colors['bradius'],
			'wpfbr_t_showmedia'                => 'yes',
			'wprevpro_template_misc_verified'  => 'yes1',
			'wprevpro_template_misc_lastname'  => 'show',
			'wprevpro_template_misc_avataropt' => 'show',
			'wprevpro_template_misc_showicon'  => 'lin',
			'wpfbr_sliderautoplay'             => 'no',
			'wpfbr_t_slidespeed'               => '1',
			'wpfbr_t_slideautodelay'           => '5',
			'wpfbr_t_review_same_height'       => 'no',
			'wpfbr_t_blocation'                => $badge,
			'wpfbr_t_bname'                    => $name,
			'wpfbr_t_bnameurl'                 => $url,
			'wpfbr_t_bimgurl'                  => wpfbrev_plugin_url . '/public/partials/imgs/facebook_badge_icon.svg',
			'wpfbr_t_bshape'                   => '',
			'wpfbr_t_bimgsize'                 => '50',
			'wpfbr_t_bbtnurl'                  => $url,
			'wpfbr_t_bbradius'                 => '0',
			'wpfbr_t_bbwidth'                  => '1',
			'wpfbr_t_bbtncolor'                => '#1877f2',
			'wpfbr_t_bbkcolor'                 => '#ffffff',
			'wpfbr_t_bbcolor'                  => '#eeeeee',
			'wpfbr_t_bobasedon'                => __( 'Based on # reviews', $d ),
			'wpfbr_t_borevus'                  => __( 'Recommend us!', $d ),
		);

		// No reviews saved for this page yet: preview the template with sample reviews.
		// The filters only last for this request, so the real template on the site is never affected.
		$sample = $count < 1;
		if ( $sample ) {
			// Show no more samples than the template would show real reviews (one grid, or every slide).
			$shown   = $per_row * $rows * ( $slider ? (int) $fields['wpfbr_t_numslides'] : 1 );
			$samples = array_slice( $this->sample_reviews( $pageid, $name, $url ), 0, $shown );
			add_filter(
				'wpfb_template_reviews',
				static function ( $reviews ) use ( $samples ) {
					return empty( $reviews ) ? $samples : $reviews;
				}
			);
			add_filter(
				'wpfb_badge_avg_total',
				static function ( $avg_total ) {
					return ( '' === (string) $avg_total[0] ) ? array( '4.8', 126 ) : $avg_total;
				}
			);
		}

		$result = $this->admin->wpfb_save_template_fields( $fields );
		if ( empty( $result['ack'] ) || 'success' !== $result['ack'] || empty( $result['t_id'] ) ) {
			wp_send_json_error( array( 'message' => ! empty( $result['ackmessage'] ) ? $result['ackmessage'] : __( 'Couldn\'t save the template.', $d ) ) );
		}

		$tid = absint( $result['t_id'] );
		$this->update_state(
			array(
				'pageid'   => $pageid,
				'pagename' => $name,
				'tid'      => $tid,
			)
		);
		wp_send_json_success(
			array(
				'tid'       => $tid,
				'title'     => $this->template_title( $tid ),
				'html'      => isset( $result['templatehtml'] ) ? $result['templatehtml'] : '',
				'shortcode' => '[wprevpro_usetemplate tid="' . $tid . '"]',
				'count'     => $count,
				'sample'    => $sample,
			)
		);
	}

	/**
	 * Sample reviews for the preview while no real ones are saved.
	 *
	 * Same fields as rows of the wpfb_reviews table, so the real template code
	 * draws them. Facebook reviews are recommendations: no star rating, a
	 * "positive" recommendation type, which the templates show with the
	 * recommends icon. When the Facebook page saves positive recommendations
	 * as 5 stars, the samples get 5 stars too.
	 *
	 * @param string $pageid Page id the template is filtered to.
	 * @param string $name   Facebook page name.
	 * @param string $url    Facebook reviews address.
	 * @return object[]
	 */
	private function sample_reviews( $pageid, $name, $url ) {
		require_once wpfbrev_plugin_dir . 'public/partials/template_class.php';
		$avatars = new WP_FB_Reviews_Template_Functions();
		$d       = self::TEXT_DOMAIN;
		$options = get_option( 'wpfbr_facebook' );
		$as_star = is_array( $options ) && isset( $options['fb_recommendation_to_star'] ) && '1' === (string) $options['fb_recommendation_to_star'];
		$samples = array(
			array( 'Sarah Mitchell', 6, __( 'Highly recommend! The staff were friendly and the service was quick and careful. They explained everything up front, answered all of our questions without rushing us, and the final price matched the quote exactly. We\'ll definitely be back.', $d ) ),
			array( 'David Reyes', 13, __( 'Great experience from start to finish. Easy to book, right on time, and the result was even better than we hoped. I recommend them to anyone who asks.', $d ) ),
			array( 'Priya Kapoor', 21, __( 'I recommend them to all my friends. It was busy when we came in, but the team kept us updated the whole time and took really good care of us. You can tell they work well together and enjoy what they do.', $d ) ),
			array( 'Marcus Thompson', 34, __( 'Everyone here is so welcoming. You can tell they care about doing things right. Recommended!', $d ) ),
			array( 'Emily Walker', 48, __( 'Clean, organized, and professional. They went the extra mile to make sure we were happy. Definitely recommend.', $d ) ),
			array( 'Jordan Lee', 63, __( 'Solid service at a fair price. A couple of small hiccups, but they fixed them right away. Would recommend.', $d ) ),
		);

		$rows = array();
		foreach ( $samples as $i => $sample ) {
			list( $reviewer, $days_ago, $review_text ) = $sample;
			$time   = time() - $days_ago * DAY_IN_SECONDS;
			$rows[] = (object) array(
				'id'                      => -1 - $i,
				'pageid'                  => $pageid,
				'pagename'                => $name,
				'created_time'            => gmdate( 'Y-m-d H:i:s', $time ),
				'created_time_stamp'      => $time,
				'reviewer_name'           => $reviewer,
				'reviewer_email'          => '',
				'company_name'            => '',
				'company_title'           => '',
				'company_url'             => '',
				'reviewer_id'             => '',
				'rating'                  => $as_star ? '5' : '',
				'recommendation_type'     => 'positive',
				'review_text'             => $review_text,
				'hide'                    => '',
				'review_length'           => str_word_count( $review_text ),
				'review_length_char'      => strlen( $review_text ),
				'type'                    => 'Facebook',
				'userpic'                 => $avatars->wprev_get_initials_avatar_url( $reviewer, 100 ),
				'userpic_small'           => '',
				'userpiclocal'            => '',
				'from_name'               => '',
				'from_url'                => $url,
				'from_logo'               => '',
				'from_url_review'         => '',
				'review_title'            => '',
				'categories'              => '',
				'posts'                   => '',
				'consent'                 => '',
				'hidestars'               => '',
				'miscpic'                 => '',
				'location'                => '',
				'verified_order'          => '',
				'language_code'           => '',
				'unique_id'               => '',
				'meta_data'               => '',
				'custom_data'             => '',
				'custom_stars'            => '',
				'owner_response'          => '',
				'sort_weight'             => 0,
				'tags'                    => '',
				'mediaurlsarrayjson'      => '',
				'mediathumburlsarrayjson' => '',
				'reviewfunnel'            => '',
			);
		}
		return $rows;
	}

	/**
	 * Create a draft page that shows the template with the block.
	 */
	public function ajax_create_page() {
		$this->check_request();
		$d = self::TEXT_DOMAIN;

		if ( ! current_user_can( 'edit_pages' ) ) {
			wp_send_json_error( array( 'message' => __( 'You don\'t have permission to create pages.', $d ) ), 403 );
		}
		$tid   = isset( $_POST['tid'] ) ? absint( $_POST['tid'] ) : 0;
		$title = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
		if ( '' === $this->template_title( $tid ) ) {
			wp_send_json_error( array( 'message' => __( 'The template is missing. Go back a step to make it again.', $d ) ) );
		}
		if ( '' === $title ) {
			$title = __( 'Reviews', $d );
		}

		$page_id = wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_status'  => 'draft',
				'post_title'   => $title,
				'post_content' => '<!-- wp:wp-fb-reviews/template ' . wp_json_encode( array( 'templateId' => $tid ) ) . ' /-->',
			),
			true
		);
		if ( is_wp_error( $page_id ) ) {
			wp_send_json_error( array( 'message' => $page_id->get_error_message() ) );
		}

		$this->update_state( array( 'page_id' => (int) $page_id ) );
		wp_send_json_success(
			array(
				'page'    => $this->page_info( (int) $page_id ),
				'message' => __( 'Draft page created. Open it to check it and publish it when you\'re ready.', $d ),
			)
		);
	}

	/**
	 * Mark setup as done. A later run starts a new template instead of changing this one.
	 */
	public function ajax_finish() {
		$this->check_request();
		update_option( self::DONE_OPTION, time(), false );
		$state = $this->state();
		update_option(
			self::STATE_OPTION,
			array(
				'pageid'   => $state['pageid'],
				'pagename' => $state['pagename'],
			),
			false
		);
		wp_send_json_success( array( 'redirect' => admin_url( 'admin.php?page=wpfb-templates_posts' ) ) );
	}
}
