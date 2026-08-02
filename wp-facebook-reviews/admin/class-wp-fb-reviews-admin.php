<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    WP_FB_Reviews
 * @subpackage WP_FB_Reviews/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    WP_FB_Reviews
 * @subpackage WP_FB_Reviews/admin
 * @author     Your Name <email@example.com>
 */
class WP_FB_Reviews_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugintoken    The ID of this plugin.
	 */
	private $plugintoken;
	private $_token;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugintoken       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugintoken, $version ) {

		$this->_token = $plugintoken;
		//$this->version = $version;
		//for testing==============
		$this->version = time();
		//===================
		

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in WP_FB_Reviews_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The WP_FB_Reviews_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		//only load for this plugin
		if(isset($_GET['page'])){
			if($_GET['page']=="wpfb-facebook" || $_GET['page']=="wpfb-get_twitter" || $_GET['page']=="wp_fb-settings" || $_GET['page']=="wpfb-reviews" || $_GET['page']=="wpfb-templates_posts" || $_GET['page']=="wp_fb-get_pro" || $_GET['page']=="wpfb-welcome-slug"){
				wp_enqueue_style( $this->_token, plugin_dir_url( __FILE__ ) . 'css/wprev_admin.css', array(), $this->version, 'all' );
				wp_enqueue_style( $this->_token."_wprev_w3", plugin_dir_url( __FILE__ ) . 'css/wprev_w3.css', array(), $this->version, 'all' );
				
				// load font awesome on all pages.
				wp_register_style( 'Font_Awesome', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css' );
				wp_enqueue_style('Font_Awesome');
			
			}
			
			if($_GET['page']=="wpfb-get_twitter"){
				wp_enqueue_style( $this->_token."_wprevpro", plugin_dir_url( __FILE__ ) . 'css/wprevpro_admin.css', array(), $this->version, 'all' );
			
			}
			
			//load template styles for wp_pro-templates_posts page
			if($_GET['page']=="wpfb-templates_posts" || $_GET['page']=="wp_fb-get_pro" || $_GET['page']=="wpfb-welcome-slug"){
				//enque template styles for preview
				wp_enqueue_style( $this->_token."_style1", plugin_dir_url(dirname(__FILE__)) . 'public/css/wprev-fb-combine.css', array(), $this->version, 'all' );
				//style 6 layout css for the preview
				wp_enqueue_style( $this->_token."_style6", plugin_dir_url(dirname(__FILE__)) . 'public/css/wprev-public_template6.css', array(), $this->version, 'all' );

			}
			
		}

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in WP_FB_Reviews_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The WP_FB_Reviews_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		

		//scripts for all pages in this plugin
		if(isset($_GET['page'])){
			if($_GET['page']=="wpfb-facebook" || $_GET['page']=="wp_fb-settings" || $_GET['page']=="wpfb-reviews" || $_GET['page']=="wpfb-templates_posts" || $_GET['page']=="wp_fb-get_pro" || $_GET['page']=="wpfb-get_twitter"){
				//pop-up script
				wp_register_script( 'simple-popup-js',  plugin_dir_url( __FILE__ ) . 'js/wprev_simple-popup.min.js' , '', $this->version, false );
				wp_enqueue_script( 'simple-popup-js' );
			}
		}
		
		//scripts for get fb reviews page Finished!
		if(isset($_GET['page'])){
			if($_GET['page']=="wpfb-facebook"){
				//facebook js
				wp_enqueue_script( $this->_token, plugin_dir_url( __FILE__ ) . 'js/wprev_facebook.js', array( 'jquery' ), $this->version, false );
				//used for ajax
				wp_localize_script($this->_token, 'adminjs_script_vars', 
					array(
					'wpfb_nonce'=> wp_create_nonce('randomnoncestring'),
					'popuptitle' => esc_html__('Downloading Reviews', 'wp-fb-reviews'),
					'popupbody' => esc_html__('Retrieving Facebook reviews and saving them to your Wordpress database...', 'wp-fb-reviews'),
					'finished1'=> esc_html__('Finished!', 'wp-fb-reviews'),
					'finished2'=>esc_html__('Finished! The reviews should now show up on the Review List page of the plugin.', 'wp-fb-reviews'),
					)
				);
			}
			/*
			if($_GET['page']=="wpfb-facebook" || $_GET['page']=="wp_fb-settings"){
				//admin js
				wp_enqueue_script( $this->_token, plugin_dir_url( __FILE__ ) . 'js/wprev_admin.js', array( 'jquery' ), $this->version, false );
				//used for ajax
				wp_localize_script($this->_token, 'adminjs_script_vars', 
					array(
					'wpfb_nonce'=> wp_create_nonce('randomnoncestring')
					)
				);
			
			}
			*/
		}
		
		
		//scripts for review list page
		if(isset($_GET['page'])){
			if($_GET['page']=="wpfb-reviews"){
				//admin js
				// Depend only on jQuery. thickbox/media-upload are enqueued separately below
				// for the avatar uploader; declaring them as hard dependencies here risks
				// WordPress silently dropping this script if either handle isn't registered,
				// which would break the edit popup, hide, and delete AJAX handlers.
				wp_enqueue_script('review_list_page-js', plugin_dir_url( __FILE__ ) . 'js/review_list_page.js', array( 'jquery' ), $this->version, false );
				wp_localize_script('review_list_page-js', 'adminjs_script_vars', 
					array(
					'wpfb_nonce'=> wp_create_nonce('randomnoncestring'),
					'popuptitle' => esc_html__('Tips', 'wp-fb-reviews'),
					'popupbody' => esc_html__('<p>- If you\'re using the pro version you can hide certain reviews by clicking the <i class="dashicons dashicons-visibility text_green" aria-hidden="true"></i> in the table below. There are also ways to hide certain types of reviews under the Templates page.</p>	
		  <p><b>- Remove All Reviews:</b> Allows you to delete all reviews in your Wordpress database and start over. It Does NOT affect your reviews on Facebook.</p> 
		  <p><b>- Manually Add Review:</b> Allows you to manaully insert a review in to your Wordpress database.</p> 
		  <p><b>- Download a CSV File:</b> Save a CSV file to your computer containing all the reviews in this table.</p> ', 'wp-fb-reviews'),
					'popuptitle2' => esc_html__('Are you sure?', 'wp-fb-reviews'),
					'popupbody2' => esc_html__('This will delete all reviews in your Wordpress database including the ones you manually entered. It Does NOT affect your reviews on Facebook or X (Twitter).', 'wp-fb-reviews'),
					'popupbody3' => esc_html__('Remove Facebook', 'wp-fb-reviews'),
					'popupbody4' => esc_html__('Remove X (Twitter)', 'wp-fb-reviews'),
					'popupbody5' => esc_html__('Remove All Reviews', 'wp-fb-reviews'),
					)
				);

				//lity lightbox for review media thumbnails
				wp_enqueue_style( $this->_token."_lity_min", plugin_dir_url( __FILE__ ) . 'css/lity.min.css', array(), $this->version, 'all' );
				wp_enqueue_script('wpfb_lity-js', plugin_dir_url( __FILE__ ) . 'js/lity.min.js', array( 'jquery' ), $this->version, false );

				//avatar uploader (thickbox/media) for the edit popup
				wp_enqueue_script('thickbox');
				wp_enqueue_style('thickbox');
				wp_enqueue_script('media-upload');
			}
			
			//scripts for templates posts page
			if($_GET['page']=="wpfb-templates_posts"){
				//admin js
				$date_format = get_option( 'date_format' );
				$sampledate = date($date_format,'1547391507');
		
				//add color picker here
				wp_enqueue_style( 'wp-color-picker' );
				// Alpha add-on (v3) — extends Iris without replacing the WP color-result button markup
				wp_enqueue_script( 'wp-color-picker-alpha', plugin_dir_url( __FILE__ ) . 'js/wprevpro-wp-color-picker-alpha.js', array( 'wp-color-picker' ), '3.0.0', false );

				wp_enqueue_media();
				wp_enqueue_script('templates_posts_page-js', plugin_dir_url( __FILE__ ) . 'js/templates_posts_page.js', array( 'jquery', 'wp-color-picker', 'wp-color-picker-alpha' ), $this->version, false );
				wp_localize_script('templates_posts_page-js', 'adminjs_script_vars', 
					array(
					'wpfb_nonce'=> wp_create_nonce('randomnoncestring'),
					'pluginsUrl' => wpfbrev_plugin_url,
					'sampledate' => $sampledate,
					'popuptitle' => esc_html__('Tips', 'wp-fb-reviews'),
					'popupbody' => esc_html__('This page will let you create multiple Reviews Templates that you can then add to your Posts or Pages via a shortcode or template function.', 'wp-fb-reviews'),
					'popuptitle2' => esc_html__('Widget Instructions', 'wp-fb-reviews'),
					'popupbody2' => esc_html__('To display this in your Sidebar or other Widget areas, add the WP Reviews widget under Appearance > Widgets, and then select this template in the drop down.', 'wp-fb-reviews'),
					'popuptitle3' => esc_html__('How to Display', 'wp-fb-reviews'),
					'popupbody3' => esc_html__('Enter this shortcode on a post, page or text widget:', 'wp-fb-reviews'),
					'popupbody4' => esc_html__('Or you can add the following php code to your template:', 'wp-fb-reviews'),
					)
				);

				//slider engine so the live preview can build working sliders
				wp_enqueue_script( $this->_token."_unslider-swipe-min", plugin_dir_url( dirname( __FILE__ ) ) . 'public/js/wprs-unslider-swipe.js', array( 'jquery' ), $this->version, false );

				//lity lightbox for review media thumbnails in the preview
				wp_enqueue_style( $this->_token."_lity_min", plugin_dir_url( __FILE__ ) . 'css/lity.min.css', array(), $this->version, 'all' );
				wp_enqueue_script( 'wpfb_lity-js', plugin_dir_url( __FILE__ ) . 'js/lity.min.js', array( 'jquery' ), $this->version, false );

				// Badge business image uses wp.media (enqueued above).
				// Keep thickbox for any remaining inline tip dialogs on this page.
				wp_enqueue_script( 'thickbox' );
				wp_enqueue_style( 'thickbox' );
			}
			
			//scripts for get_twitter page itunes
			if($_GET['page']=="wpfb-get_twitter"){
			//$typearray = unserialize(WPREV_TYPE_ARRAY);
				//admin js
				wp_enqueue_script('wprevpro_get_twitter_page-js', plugin_dir_url( __FILE__ ) . 'js/wprevpro_get_twitter_page.js', array( 'jquery' ), $this->version, false );
				//used for ajax
				wp_localize_script('wprevpro_get_twitter_page-js', 'adminjs_script_vars', 
					array(
					'wpfb_nonce'=> wp_create_nonce('randomnoncestring'),
					'pluginsUrl' => wpfbrev_plugin_url
					)
				);
				
			}
		}
		
	}
	
	public function add_menu_pages() {
		
		//$menu_slug = 'wpdocs-orders-slug';
//add_menu_page( 'WP Docs Orders', 'WP Docs Orders', 'manage_options', $menu_slug, false );
//add_submenu_page( $menu_slug, 'Existing WP Docs Orders', 'Existing WP Docs Orders', 'manage_options', $menu_slug, 'wpdocs_orders_function' );

		/**
		 * adds the menu pages to wordpress
		 */

		$page_title = 'WP Reviews Welcome';
		$menu_title = 'WP Reviews';
		$capability = 'manage_options';
		$menu_slug = 'wpfb-welcome-slug';

		add_menu_page($menu_title, $menu_title, $capability, $menu_slug, array($this,'wp_fb_welcome'),'dashicons-star-half');
		
		// We add this submenu page with the same slug as the parent to ensure we don't get duplicates
		$sub_menu_title = __('Welcome', 'wp-fb-reviews');
		add_submenu_page($menu_slug, $page_title, $sub_menu_title, $capability, $menu_slug, array($this,'wp_fb_welcome'));
		
		
		// Now add the hidden submenu page for twitter
		$submenu_page_title = 'WP Reviews : Get Facebook Reviews';
		$submenu_title = 'Facebook';
		$submenu_slug = 'wpfb-facebook';
		add_submenu_page($menu_slug, $submenu_page_title, $submenu_title, $capability, $submenu_slug, array($this,'wp_fb_facebook'));

		
		// Now add the hidden submenu page for X (Twitter)
		$submenu_page_title = 'WP Reviews : X (Twitter)';
		$submenu_title = 'X (Twitter)';
		$submenu_slug = 'wpfb-get_twitter';
		add_submenu_page($menu_slug, $submenu_page_title, $submenu_title, $capability, $submenu_slug, array($this,'wp_fb_gettwitter'));
		
		// Now add the submenu page for the actual reviews list
		$submenu_page_title = 'WP Reviews : Review List';
		$submenu_title = __('Review List', 'wp-fb-reviews');
		$submenu_slug = 'wpfb-reviews';
		add_submenu_page($menu_slug, $submenu_page_title, $submenu_title, $capability, $submenu_slug, array($this,'wp_fb_reviews'));
		
		// Now add the submenu page for the reviews templates
		$submenu_page_title = 'WP Reviews : Templates';
		$submenu_title = __('Templates', 'wp-fb-reviews');
		$submenu_slug = 'wpfb-templates_posts';
		add_submenu_page($menu_slug, $submenu_page_title, $submenu_title, $capability, $submenu_slug, array($this,'wp_fb_templates_posts'));
	}
	
	public function wprev_add_external_link_admin_submenu() {
		global $submenu;
		$menu_slug = 'wpfb-welcome-slug'; // used as "key" in menus
		//make sure this user can see menu.
		if (array_key_exists($menu_slug, $submenu)) {
			// add the external links to the slug you used when adding the top level menu
			$submenu[$menu_slug][] = array('<div id="wprev-66021">Go Pro!</div>', 'manage_options', 'https://wpreviewslider.com/');
		}
	}
	public function wpse_66021_add_jquery() 
	{
		?>
		<script type="text/javascript">
			jQuery(document).ready( function($) {   
				$('#wprev-66021').parent().attr('target','_blank');  
			});
		</script>
		<?php
	}

	
	
	
	
	public function wp_fb_welcome() {
		require_once plugin_dir_path( __FILE__ ) . '/partials/welcome.php';
	}
	
	public function wp_fb_facebook() {
		require_once plugin_dir_path( __FILE__ ) . '/partials/facebook.php';
	}
	
	//public function wp_fb_settings() {
	//	require_once plugin_dir_path( __FILE__ ) . '/partials/settings.php';
	//}

	public function wp_fb_reviews() {
		require_once plugin_dir_path( __FILE__ ) . '/partials/review_list.php';
	}
	
	public function wp_fb_templates_posts() {
		require_once plugin_dir_path( __FILE__ ) . '/partials/templates_posts.php';
	}
	public function wp_fb_getpro() {
		require_once plugin_dir_path( __FILE__ ) . '/partials/get_pro.php';
	}
	
	public function wp_fb_gettwitter() {
		require_once plugin_dir_path( __FILE__ ) . '/partials/get_twitter.php';
	}

	/**
	 * custom option and settings on new facebook settings page
	 */
	public function wpfbr_facebook_init()
	{
			
		// register a new setting for page
		register_setting('wpfb-facebook', 'wpfbr_facebook', array($this,'wpfbr_validate_input'));
		
		// register a new section in the page
		add_settings_section(
			'wpfbr_facebook_code',
			'',
			array($this,'wpfbr_facebook_code_cb'),
			'wpfb-facebook'
		);
		//register fb app id input field
		add_settings_field(
			'fb_app_code', // as of WP 4.6 this value is used only internally
			__( 'Secret Access Code', 'wp-fb-reviews'),
			array($this,'wpfbr_field_fb_code_cb'),
			'wpfb-facebook',
			'wpfbr_facebook_code',
			[
				'label_for'         => 'fb_app_code',
				'class'             => 'wpfbr_row',
				'wpfbr_custom_data' => 'custom',
			]
		);

		//recommendations as star value
		add_settings_field(
			'fb_recommendation_to_star', // as of WP 4.6 this value is used only internally
			__( 'Recommendations', 'wp-fb-reviews'),
			array($this,'wprevpro_field_fb_recommendation_to_star'),
			'wpfb-facebook',
			'wpfbr_facebook_code',
			[
				'label_for'         => 'fb_recommendation_to_star',
				'class'             => ''
			]
		);
		
	}
	
	public function wpfbr_validate_input( $input ) {
		// Create our array for storing the validated options 
		$output = array();
		
		// Loop through each of the incoming options 
		foreach( $input as $key => $value ) {
			
			// Check to see if the current option has a value. If so, process it. 
			if( isset( $input[$key] ) ) {
			
				// Strip all HTML and PHP tags and properly handle quoted strings 
				$output[$key] = strip_tags( stripslashes( $input[ $key ] ) );
				
			} // end if 
			
		} // end foreach 
		
		// Return the array processing any additional functions filtered by this action 
		return apply_filters( 'wpfbr_validate_input', $output, $input );
	}
	
	//==== developers section cb ====
	// section callbacks can accept an $args parameter, which is an array.
	// $args have the following keys defined: title, id, callback.
	// the values are defined at the add_settings_section() function.
	public function wpfbr_facebook_code_cb($args)
	{
		//echos out at top of section
	}	
	//==== field cb =====
	// field callbacks can accept an $args parameter, which is an array.
	// $args is defined at the add_settings_field() function.
	// wordpress has magic interaction with the following keys: label_for, class.
	// the "label_for" key value is used for the "for" attribute of the <label>.
	// the "class" key value is used for the "class" attribute of the <tr> containing the field.
	// you can add custom key value pairs to be used inside your callbacks.
	public function wpfbr_field_fb_code_cb($args)
	{

		// get the value of the setting we've registered with register_setting()
		$options = get_option('wpfbr_facebook');
		if(!isset($options[$args['label_for']])){
			//$options[$args['label_for']] = "";
		}
		// output the field
		
				
		
		?>
	<input id="<?= esc_attr($args['label_for']); ?>" data-custom="<?= esc_attr($args['wpfbr_custom_data']); ?>" type="text" name="wpfbr_facebook[<?= esc_attr($args['label_for']); ?>]" placeholder="" value="<?php echo esc_html($options[$args['label_for']]); ?>">
		
		<p class="description">
			<?= esc_html__('Enter the Access Code that you copied from the link above. Do not share this code.', 'wp-fb-reviews'); ?>
		</p>
		<?php
	}
		public function wprevpro_field_fb_recommendation_to_star($args)
	{
		// get the value of the setting we've registered with register_setting()
		$options = get_option('wpfbr_facebook');
		if(!isset($options[$args['label_for']])){
			$options[$args['label_for']]='';
		}
		// output the field
		?>
		<input type="checkbox" id="<?php echo  esc_attr($args['label_for']); ?>" name="wpfbr_facebook[<?php echo esc_attr($args['label_for']); ?>]" value="1" <?php checked( $options[$args['label_for']], "1" ); ?>/>
		
		<?php echo  esc_html__(' Save Positive Recommendations as 5 Star and Negative as 2 Star.', 'wp-fb-reviews'); ?>
		<p class="description">
			<?php echo  esc_html__('This will allow you to display the stars with the review.', 'wp-fb-reviews'); ?>
		</p>
		<?php
	}
	
	/**
	 * custom option and settings on settings page
	 
	public function wpfbr_settings_init()
	{
		// register a new setting for "wp_fb-settings" page
		register_setting('wp_fb-settings', 'wpfbr_options');
	 
		// register a new section in the "wp_fb-settings" page
		add_settings_section(
			'wpfbr_section_developers',
			'',
			array($this,'wpfbr_section_developers_cb'),
			'wp_fb-settings'
		);
	 
		//register fb app id input field
		add_settings_field(
			'fb_app_ID', // as of WP 4.6 this value is used only internally
			'Facebook App ID',
			array($this,'wpfbr_field_fb_app_id_cb'),
			'wp_fb-settings',
			'wpfbr_section_developers',
			[
				'label_for'         => 'fb_app_ID',
				'class'             => 'wpfbr_row',
				'wpfbr_custom_data' => 'custom',
			]
		);
		//register get access token btn field
		add_settings_field(
			'fb_user_token_btn', // as of WP 4.6 this value is used only internally
			'Get Access Token',
			array($this,'wpfbr_gettoken_cb'),
			'wp_fb-settings',
			'wpfbr_section_developers',
			[
				'label_for'         => 'fb_user_token_btn',
				'class'             => 'wpfbr_row'
			]
		);
		//register fb Access Token input field
		add_settings_field(
			'fb_user_token_field_display', // as of WP 4.6 this value is used only internally
			'FB Access Token',
			array($this,'wpfbr_field_fb_access_cb'),
			'wp_fb-settings',
			'wpfbr_section_developers',
			[
				'label_for'         => 'fb_user_token_field_display',
				'class'             => 'wpfbr_row hide'
			]
		);
		
	}
	*/
	/**
	 * custom option and settings:
	 * callback functions
	 */
	 
	//==== developers section cb ====
	// section callbacks can accept an $args parameter, which is an array.
	// $args have the following keys defined: title, id, callback.
	// the values are defined at the add_settings_section() function.
	//public function wpfbr_section_developers_cb($args)
	//{
		//echos out at top of section
	//}
	
	//==== field cb =====
	// field callbacks can accept an $args parameter, which is an array.
	// $args is defined at the add_settings_field() function.
	// wordpress has magic interaction with the following keys: label_for, class.
	// the "label_for" key value is used for the "for" attribute of the <label>.
	// the "class" key value is used for the "class" attribute of the <tr> containing the field.
	// you can add custom key value pairs to be used inside your callbacks.
	/*
	public function wpfbr_field_fb_app_id_cb($args)
	{
		// get the value of the setting we've registered with register_setting()
		$options = get_option('wpfbr_options');

		// output the field
		?>
		<input id="<?= esc_attr($args['label_for']); ?>" data-custom="<?= esc_attr($args['wpfbr_custom_data']); ?>" type="text" name="wpfbr_options[<?= esc_attr($args['label_for']); ?>]" placeholder="" value="<?php echo $options[$args['label_for']]; ?>">
		
		<p class="description">
			<?= esc_html__('Enter the Facebook App ID of your newly created app and click Save Settings. Look for the Facebook window that pops up, make sure it is not being blocked.', 'wp-fb-reviews'); ?>
		</p>
		<?php
	}
	public function wpfbr_gettoken_cb($args)
	{
		?>
		<button id="<?= esc_attr($args['label_for']); ?>" type="button" class="btn_green">Get Authorization &amp; Pages</button>
		<p class="description">
			<?= esc_html__('Click to allow your newly created Facebook app to pull reviews from your Facebook Pages.', 'wp-fb-reviews'); ?>
		</p>
		<?php
	}
	public function wpfbr_field_fb_access_cb($args)
	{
		// get the value of the setting we've registered with register_setting()
		$options = get_option('wpfbr_options');

		// output the field
		?>
		<input id="<?= esc_attr($args['label_for']); ?>" type="text" name="wpfbr_options[<?= esc_attr($args['label_for']); ?>]" placeholder="" value="<?php echo $options[$args['label_for']]; ?>">
		
		<p class="description">
			<?= esc_html__('This gives the plugin authorization to pull your reviews from a Facebook page.', 'wp-fb-reviews'); ?>
		</p>
		<?php
	}
	*/
	/**
	 * AJAX: hide/unhide or delete a single review without a page reload.
	 * Called from admin/js/review_list_page.js.
	 *
	 * @access public
	 * @return void
	 */
	public function wpfb_hidereview_ajax() {
		check_ajax_referer( 'randomnoncestring', 'wpfb_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			echo '0-noperm-fail';
			die();
		}

		$rid      = isset( $_POST['reviewid'] ) ? intval( $_POST['reviewid'] ) : 0;
		$myaction = isset( $_POST['myaction'] ) ? sanitize_text_field( wp_unslash( $_POST['myaction'] ) ) : '';

		global $wpdb;
		$table_name = $wpdb->prefix . 'wpfb_reviews';

		if ( $myaction === 'hideshow' ) {
			$myreview = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table_name} WHERE id = %d", $rid ) );
			if ( ! $myreview ) {
				echo $rid . '-' . $myaction . '-fail';
				die();
			}

			$newvalue = ( $myreview->hide === 'yes' ) ? '' : 'yes';

			$updatetempquery = $wpdb->update(
				$table_name,
				array( 'hide' => $newvalue ),
				array( 'id' => $rid ),
				array( '%s' ),
				array( '%d' )
			);
			if ( false !== $updatetempquery ) {
				echo $rid . '-' . $myaction . '-' . $newvalue;
			} else {
				echo $rid . '-' . $myaction . '-fail';
			}
		}

		if ( $myaction === 'deleterev' ) {
			$deletereview = $wpdb->delete( $table_name, array( 'id' => $rid ), array( '%d' ) );
			if ( $deletereview > 0 ) {
				echo $rid . '-' . $myaction . '-success';
			} else {
				echo $rid . '-' . $myaction . '-fail';
			}
		}

		die();
	}

	/**
	 * AJAX: save an edited review (reviewer photo URL + display date) without a
	 * page reload, called from admin/js/review_list_page.js.
	 *
	 * @access public
	 * @return void
	 */
	public function wpfb_savereview_ajax() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'wp-fb-reviews' ) ) );
			return;
		}

		check_ajax_referer( 'randomnoncestring', 'wpfb_nonce' );

		global $wpdb;
		$table_name = $wpdb->prefix . 'wpfb_reviews';

		$r_id       = isset( $_POST['editrid'] ) ? absint( $_POST['editrid'] ) : 0;
		$avatar_url = isset( $_POST['avatar_url'] ) ? esc_url_raw( wp_unslash( $_POST['avatar_url'] ) ) : '';
		$rdate_raw  = isset( $_POST['review_date'] ) ? sanitize_text_field( wp_unslash( $_POST['review_date'] ) ) : '';

		if ( $r_id <= 0 ) {
			wp_send_json_error( array( 'message' => __( 'Invalid review.', 'wp-fb-reviews' ) ) );
			return;
		}

		$existing = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table_name} WHERE id = %d", $r_id ) );
		if ( ! $existing ) {
			wp_send_json_error( array( 'message' => __( 'Review not found.', 'wp-fb-reviews' ) ) );
			return;
		}

		// Update both userpic and userpiclocal so the front-end (which prefers the
		// local copy for Facebook avatars) reflects the edited image.
		$data   = array(
			'userpic'      => $avatar_url,
			'userpiclocal' => $avatar_url,
		);
		$format = array( '%s', '%s' );

		$parsed_stamp = $rdate_raw !== '' ? strtotime( $rdate_raw ) : false;
		if ( ! $parsed_stamp ) {
			wp_send_json_error( array( 'message' => __( 'Invalid date. Use the format YYYY-MM-DD HH:MM:SS.', 'wp-fb-reviews' ) ) );
			return;
		}

		$created_time               = date( 'Y-m-d H:i:s', $parsed_stamp );
		$data['created_time']       = $created_time;
		$data['created_time_stamp'] = $parsed_stamp;
		$format[]                   = '%s';
		$format[]                   = '%d';

		$updated = $wpdb->update(
			$table_name,
			$data,
			array( 'id' => $r_id ),
			$format,
			array( '%d' )
		);

		if ( false === $updated ) {
			wp_send_json_error( array( 'message' => __( 'Database error while saving. Please try again.', 'wp-fb-reviews' ) ) );
			return;
		}

		wp_send_json_success(
			array(
				'id'      => $r_id,
				'userpic' => $avatar_url !== '' ? esc_url( $avatar_url ) : '',
				'date'    => esc_html( $created_time ),
			)
		);
	}

	/**
	 * Store reviews in table, called from javascript file admin.js
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function wpfb_process_ajax(){
	//ini_set('display_errors',1);  
	//error_reporting(E_ALL);
		
		check_ajax_referer('randomnoncestring', 'wpfb_nonce');
		
		// Security: Only allow administrators to insert reviews
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Unauthorized access', 403 );
			wp_die();
		}
		
		$postreviewarray = $_POST['postreviewarray'];
		
		//var_dump($postreviewarray);

		//loop through each one and insert in to db
		global $wpdb;
		$table_name = $wpdb->prefix . 'wpfb_reviews';
		
		$stats = array();
		$seen_keys = array();
		
		foreach($postreviewarray as $item) { //foreach element in $arr
			if ( empty( $item['pageid'] ) || empty( $item['created_time'] ) ) {
				continue;
			}

			$pageid = $item['pageid'];
			$pagename = $item['pagename'];
			$created_time = $item['created_time'];
			$created_time_stamp = strtotime( $created_time );
			if ( false === $created_time_stamp ) {
				continue;
			}
			$reviewer_name = $item['reviewer_name'];
			$reviewer_id = $item['reviewer_id'];
			$reviewer_imgurl = $item['reviewer_imgurl'];
			if(isset($item['rating'])){
				$rating = $item['rating'];
			} else {
				$rating ="";
			}
			if(isset($item['recommendation_type']) && $item['recommendation_type']){
				$recommendation_type = $item['recommendation_type'];
			} else {
				$recommendation_type ="";
			}
			$review_text = $item['review_text'];
			$review_length = str_word_count($review_text);
			if($review_length <1 && $review_text !=""){		//fix for other language error
				$review_length = substr_count($review_text, ' ');
			}
		$rtype = $item['type'];

		$dedup_key = $pageid . '|' . $reviewer_id . '|' . $created_time_stamp;
		if ( isset( $seen_keys[ $dedup_key ] ) ) {
			continue;
		}
		
		//check to see if row is in db already
		$checkrow = $wpdb->get_row( $wpdb->prepare(
			"SELECT id FROM {$table_name} WHERE created_time = %s",
			$created_time
		) );

		// Re-download sends API datetime format; DB stores normalized values.
		if ( null === $checkrow ) {
			$checkrow = $wpdb->get_row( $wpdb->prepare(
				"SELECT id FROM {$table_name} WHERE pageid = %s AND reviewer_id = %s AND created_time_stamp = %d",
				$pageid,
				$reviewer_id,
				$created_time_stamp
			) );
		}
		
		$from_url = "https://www.facebook.com/pg/".$pageid."/reviews/";
			
			//option for saving positive recommendation_type as 5 start
			$option = get_option('wpfbr_facebook');
			if(isset($option['fb_recommendation_to_star'])){
				if($option['fb_recommendation_to_star'] =='1'){
					if($rating=='' && $recommendation_type=="positive"){
						$rating=5;
					}
					if($rating=='' && $recommendation_type=="negative"){
						$rating=2;
					}
				}
			}
			
		if ( null === $checkrow ) {
			$seen_keys[ $dedup_key ] = true;
			$stats[] =array( 
					'pageid' => $pageid, 
					'pagename' => $pagename, 
					'created_time' => date('Y-m-d H:i:s', $created_time_stamp),
					'created_time_stamp' => $created_time_stamp,
					'reviewer_name' => $reviewer_name,
					'reviewer_id' => $reviewer_id,
					'rating' => $rating,
					'recommendation_type' => $recommendation_type,
					'review_text' => $review_text,
					'hide' => '',
					'review_length' => $review_length,
					'type' => $rtype,
					'userpic' => $reviewer_imgurl,
					'from_url' => $from_url,
				);
		}
		}
		$i = 0;
		$insertnum = 0;
		foreach ( $stats as $stat ){
			$insertnum = $wpdb->insert( $table_name, $stat );
			$i=$i + 1;
		}
	
		$insertid = $wpdb->insert_id;

		//header('Content-Type: application/json');
		echo $insertnum."-".$insertid."-".$i;

		die();
	}
	
	 /**
	 * download a copy of the avatars to local server and serve from there, update these
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */		
	private function wppro_resizeimage($source,$size){
		$image = wp_get_image_editor( $source );
		if ( ! is_wp_error( $image ) ) {
			$imagesize = $image->get_size();
			if($imagesize['width']>$size){
				$image->resize( $size, $size, true );
				$image->save( $source );
			}
		} else {
			$error_string = $image->get_error_message();
			echo '<div id="message" class="error"><p>' . $error_string . '</p></div>';
		}
	}

	public function wprevpro_download_avatar_tolocal() {
		
		//being called from js file after all reviews are downloaded.
		check_ajax_referer('randomnoncestring', 'wpfb_nonce');
		$img_locations_option = json_decode(get_option( 'wprev_img_locations' ),true);
		$imageuploadedir =$img_locations_option['upload_dir_wprev_avatars'];
		
		//$imagecachedir = plugin_dir_path( __DIR__ ).'/public/partials/avatars/';
		
		//get array of all reviews, check to see if the image exists
		global $wpdb;
		$table_name = $wpdb->prefix . 'wpfb_reviews';
		$currentreviews = $wpdb->get_results("SELECT id, reviewer_id, created_time_stamp, reviewer_name, type, userpic FROM $table_name");

		foreach ( $currentreviews as $review ) 
		{
			//$review->id
			$id= $review->id;
			$revid = $review->reviewer_id;
			$newfilename = $review->created_time_stamp.'_'.$revid;
			
			
			//$newfile = $imagecachedir . $newfilename.'.jpg';
			//$newfileurl = esc_url( plugins_url( 'public/partials/avatars/',  dirname(__FILE__)  ) ). $newfilename.'.jpg';
			$newfile = $imageuploadedir . $newfilename.'.jpg';
			$newfileurl = esc_url( $img_locations_option['upload_url_wprev_avatars']). $newfilename.'.jpg';
			
			//echo $newfile;
			
			//$userpic = $review->userpic;
			$userpic = htmlspecialchars_decode($review->userpic);
			//check for avatar
			if(@filesize($newfile)<200){
				if($userpic!=''){
					if ( @copy($userpic, $newfile) ) {
						//echo "Copy success!";
						$this->wppro_resizeimage($newfile,60);
						//update db with new image location, userpiclocal
						$wpdb->query( $wpdb->prepare("UPDATE $table_name SET userpiclocal = '$newfileurl' WHERE id = %d AND reviewer_id = %s",$id, $revid) );
					} else {
						//echo "Copy failed.";
						//try to curl the image
						if (function_exists('curl_init')) {
							$fh = @fopen($newfile, 'w');
							if ($fh) {
								$curl = curl_init();
								curl_setopt($curl, CURLOPT_URL, $userpic);
								curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
								$result = curl_exec($curl);
								if ($result !== false) {
									fwrite($fh, $result);
								}
								fclose($fh);
								curl_close($curl);

								if ( is_file($newfile) ) {
									//$this->wppro_resizeimage($newfile,60);
									//update db with new image location, userpiclocal
									$wpdb->query( $wpdb->prepare("UPDATE $table_name SET userpiclocal = %s WHERE id = %d AND reviewer_id = %s",$newfileurl,$id, $revid) );
								}
							}
						}
					}
				}
			} else {
					//echo "image exists:".$newfile;
					//image does exist, just update db with this filename
					$wpdb->query( $wpdb->prepare("UPDATE $table_name SET userpiclocal = '$newfileurl' WHERE id = %d AND reviewer_id = %s",$id, $revid) );
			}
			//--------------------------
			//do another check in case copy failed.
			if(@filesize($newfile)<200){
				$wpdb->query( $wpdb->prepare("UPDATE $table_name SET userpiclocal = '' WHERE id = %d AND reviewer_id = %s",$id, $revid) );
				
			}

		}
		
	}

	/**
	 * adds drop down menu of templates on post edit screen
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */	
	/* 
	public function add_sc_select(){
		//get id's and names of templates that are post type 
		global $wpdb;
		$table_name = $wpdb->prefix . 'wpfb_post_templates';
		$currentforms = $wpdb->get_results("SELECT id, title, template_type FROM $table_name WHERE template_type = 'post'");
		if(count($currentforms)>0){
		echo '&nbsp;<select id="wprs_sc_select"><option value="select">Review Template</option>';
		foreach ( $currentforms as $currentform ){
			echo '<option value="[wprevpro_usetemplate tid=\''.$currentform->id.'\']">'.$currentform->title.'</option>';
		}
		 echo '</select>';
		}
	}
	//add_action('admin_head', 'button_js');
	public function button_js() {
			echo '<script type="text/javascript">
			jQuery(document).ready(function(){
			   jQuery("#wprs_sc_select").change(function() {
							if(jQuery("#wprs_sc_select :selected").val()!="select"){
							  send_to_editor(jQuery("#wprs_sc_select :selected").val());
							}
							  return false;
					});
			});
			</script>';
	}
	
	
	*/
//==========================================================================================	
	/**
	 * download fb backup method, only used if we get an error from the fb API reviews
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 
	 
	//for ajax call to fb backup master
	public function wprevpro_ajax_download_fb_backup() {
		
		check_ajax_referer('randomnoncestring', 'wpfb_nonce');
		$thisurlpageid = $_POST['pid'];
		$thisurlpagename = $_POST['pname'];
		$getresponse = $this->wprevpro_download_fb_backup($thisurlpageid,$thisurlpagename);
		//echo $getresponse;
		die();
	}
	 
	 
	public function wprevpro_download_fb_backup($downloadurlpageid = 'all',$pagename) {
			$options = get_option('wprevpro_yelp_settings');
			
			//check to see if only downloading one here, if not that skip and continue
			if($downloadurlnum!='all'){
					//build url with pageid  https://www.facebook.com/pg/102152479925798/reviews/
					$currenturlmore = "https://www.facebook.com/".$downloadurlpageid."/reviews/";
					$this->wprevpro_download_fb_backup_perurl($currenturlmore,$downloadurlpageid,$pagename);

			} else {
				//get all pageids that are checked
			}

	}
	
 
	public function wprevpro_download_fb_backup_perurl($currenturl,$pageid,$pagename) {
		ini_set('memory_limit','256M');
			global $wpdb;
			$table_name = $wpdb->prefix . 'wpfb_reviews';
			
				$reviews = [];
				$n=1;
				$urlvalue = $currenturl;
				//$urlvalue ='https://www.facebook.com/pg/Mutinytattoos/reviews/';

									
				$response = wp_remote_get( $urlvalue );
				if ( is_array( $response ) ) {
				  $header = $response['headers']; // array of http header lines
				  $fileurlcontents = $response['body']; // use the content
				}
				//need to trim the string down by removing all script tags
					$dom = new DOMDocument();
					$dom->loadHTML('<?xml encoding="utf-8" ?>' . $fileurlcontents);
					$script = $dom->getElementsByTagName('script');
					$remove = [];
					foreach($script as $item)
					{
					  $remove[] = $item;
					}
					foreach ($remove as $item)
					{
					  $item->parentNode->removeChild($item); 
					}
					$htmlstripped = $dom->saveHTML();

					//save file to see what we're getting
					//$tempurlvalue = plugin_dir_path( __FILE__ ).'fbbackup'.$pageid.'.html';
					//$savefile = file_put_contents($tempurlvalue,$htmlstripped );
				
				$html = wpfbrev_str_get_html($htmlstripped);


				$pagename = $pagename;
				$pageid = $pageid;

				//find total and average number here and end break loop early if total number less than 50. review-count
				
				if($html->find('meta[itemprop=ratingValue]',0)){
					$avgrating = $html->find('meta[itemprop=ratingValue]',0)->content;
					$avgrating = (float)$avgrating;
				}
				if($html->find('meta[itemprop=ratingCount]',0)){
					$totalreviews = $html->find('meta[itemprop=ratingCount]',0)->content;
					$totalreviews = intval($totalreviews);
				}
					
				
				//print_r($allreviewsarray);
				//foreach ($html->find('div._1dwg') as $review) {
				for ($x = 0; $x <= 10; $x++) {
					
					if($html->find('div.userContentWrapper',$x)){
					$review = $html->find('div.userContentWrapper',$x);
					
					
						$user_name='';
						$userimage='';
						$rating='';
						$datesubmitted='';
						$rtext='';
						// Find user_name
						if($review->find('span.profileLink', 0)){
							$user_name = $review->find('span.profileLink', 0)->plaintext;
							$user_name = sanitize_text_field($user_name);
							$user_name = addslashes($user_name);
						}
						if($user_name==''){
						if($review->find('a.profileLink', 0)){
							$user_name = $review->find('a.profileLink', 0)->plaintext;
							$user_name = sanitize_text_field($user_name);
							$user_name = $user_name;
							$user_name_slash = addslashes($user_name);
						}
						}
						if(mb_detect_encoding($user_name) != 'UTF-8') {$user_name = utf8_encode($user_name);}
											

						// Find userimage
						if($review->find('img', 0)){
							$userimage = $review->find('img', 0)->src;
						}
						
						// find rating
						if($review->find('i._51mq', 0)){
							$rating = $review->find('i._51mq', 0)->plaintext;
							$rating = intval($rating);
						}
						
						//first method find the uttimstamp $results->getAttribute("data-name");
						$uttimstamp='';
						if($review->find('abbr._5ptz', 0)->getAttribute("data-utime")){
							$uttimstamp = $review->find('abbr._5ptz', 0)->getAttribute("data-utime");
						}

						// find date
						if($review->find('span.timestampContent', 0)){
							$datesubmitted = $review->find('span.timestampContent', 0)->plaintext;
							$datesubmitted = strstr($datesubmitted, ' at ', true) ?: $datesubmitted;
							//fix for hrs ago hrs
							if (strpos($datesubmitted, 'hrs') !== false) {
								$datesubmitted = date('Y-m-d');
							}
						}
						//backup date method
						$utdate='';
						if($review->find('abbr._5ptz', 0)){
							$utdate = $review->find('abbr._5ptz', 0)->title;
						}

						// find text
						$rtext ='';
						if($review->find('div.userContent', 0)){
							$rtext = $review->find('div.userContent', 0)->plaintext;
							$rtext = sanitize_text_field($rtext);
							$rtext = addslashes($rtext);
							//remove See More
							$rtext =str_replace("See More","",$rtext);
							$rtext =str_replace("&#65533;","",$rtext);
							
						}
						if(mb_detect_encoding($rtext) != 'UTF-8') {$rtext = utf8_encode($rtext);}
						
						if($rating>0){
							//$review_length = str_word_count($rtext);
							//if($review_length <2 && $rtext !=""){		//fix for other language error
								$review_length = substr_count($rtext, ' ');
							//}
							$pos = strpos($userimage, 'default_avatars');
							if (is_numeric($uttimstamp)) {
								$timestamput = $uttimstamp;
							} else {
								if($datesubmitted!=''){
									$timestamput = strtotime($datesubmitted);
								} else {
									$timestamput = strtotime($utdate);
								}
							}
							$timestamp = date("Y-m-d H:i:s", $timestamput);
							
							//check to see if in database already
										//check to see if row is in db already
							$reviewindb = 'no';

							$checkrow = $wpdb->get_var( "SELECT id FROM ".$table_name." WHERE reviewer_name = '".trim($user_name)."' " );
								if( empty( $checkrow ) )
								{
									$reviewindb = 'no';
								} else {
									$reviewindb = 'yes';
								}
							//check again for ' in name
							$checkrow2 = $wpdb->get_var( "SELECT id FROM ".$table_name." WHERE reviewer_name = '".trim($user_name_slash)."' " );
								if( empty( $checkrow2 ) )
								{
									$reviewindb2 = 'no';
								} else {
									$reviewindb2 = 'yes';
								}

							if( $reviewindb == 'no' && $reviewindb2 == 'no')
							{
								$reviews[] = [
										'reviewer_name' => trim($user_name),
										'reviewer_id' => '',
										'pageid' => trim($pageid),
										'pagename' => trim($pagename),
										'userpic' => $userimage,
										'rating' => $rating,
										'created_time' => $timestamp,
										'created_time_stamp' => $timestamput,
										'review_text' => trim($rtext),
										'hide' => '',
										'review_length' => $review_length,
										'type' => 'Facebook'
								];
							}
							$review_length ='';
						}
				 
						$i++;
					}
				}
				
				//print_r($reviews);

				//sleep for random 2 seconds
				sleep(rand(0,1));
				$n++;
				
				//var_dump($reviews);
				// clean up memory
				if (!empty($html)) {
					$html->clear();
					unset($html);
				}


				//go ahead and delete first, only if we have new ones and turned on.
				if(count($reviews)>0){
					//add all new yelp reviews to db
					foreach ( $reviews as $stat ){
						$insertnum = $wpdb->insert( $table_name, $stat );
					}
					//reviews added to db
					if(isset($insertnum)){
						$errormsg = ' ------'.count($reviews).' Most Helpful FB reviews downloaded.';
						$this->errormsg = $errormsg;
			
					}
				} else {
					$errormsg = 'No new reviews found.';
					$this->errormsg = $errormsg;
				}
				echo $errormsg;

	}
//--======================= end fb tempmethod =======================--//	
	*/	
	
		//====================X (Twitter)======================
	//search X for posts using the X API v2
	public function wprp_twitter_gettweets_ajax() {

		check_ajax_referer('randomnoncestring', 'wpfb_nonce');
		$searchquery = sanitize_text_field($_POST['query']);
		$searchendpoint = sanitize_text_field($_POST['endpoint']);
		$formid = sanitize_text_field($_POST['fid']);
		$resultarray['searchquery'] = $searchquery;
		$resultarray['searchendpoint'] = $searchendpoint;

		//update the searchquery for the form id, this is because of the input on the pop-up.
		global $wpdb;
		$table_name = $wpdb->prefix . 'wpfb_gettwitter_forms';
		$timenow = time();
		$data = array('query' => "$searchquery",'last_ran' =>"$timenow");
		$format = array('%s','%d');
		$updatetempquery = $wpdb->update($table_name, $data, array( 'id' => $formid ), $format, array( '%d' ));

		$wprevpro_twitter_api_key = trim((string) get_option('wprevfb_twitterapi_key'));
		$wprevpro_twitter_api_key_secret = trim((string) get_option('wprevfb_twitterapi_key_secret'));
		$wprevpro_twitter_api_token = trim((string) get_option('wprevfb_twitterapi_token'));
		$wprevpro_twitter_api_token_secret = trim((string) get_option('wprevfb_twitterapi_token_secret'));
		$wprevpro_twitter_bearer = trim((string) get_option('wprevfb_twitterapi_bearer'));
		//Users sometimes paste the header value including the "Bearer " prefix.
		if(stripos($wprevpro_twitter_bearer, 'Bearer ') === 0){
			$wprevpro_twitter_bearer = trim(substr($wprevpro_twitter_bearer, 7));
		}

		$has_oauth1 = ($wprevpro_twitter_api_key!='' && $wprevpro_twitter_api_key_secret!='' && $wprevpro_twitter_api_token!='' && $wprevpro_twitter_api_token_secret!='');
		$has_bearer = ($wprevpro_twitter_bearer!='');

		//X API v2 requires the site owner's own credentials, there is no shared fallback.
		if(!$has_oauth1 && !$has_bearer){
			$resultarray['ack'] = 'error';
			$resultarray['msg'] = 'Please enter your X (Twitter) API credentials using the "Enter/Modify API Keys" button before searching. Prefer OAuth 1.0a (Consumer Key, Consumer Key Secret, Access Token, Access Token Secret), or a Bearer Token.';
			$resultarray['statuses'] = array('results' => array());
			$resultarray['savedreviews'] = array();
			echo json_encode($resultarray);
			die();
		}

		//Prefer OAuth 1.0a user context when all four keys are present; otherwise use OAuth 2.0 app-only Bearer.
		$authmethod = '';
		if($has_oauth1){
			$connection = new Abraham\TwitterOAuth\TwitterOAuth($wprevpro_twitter_api_key, $wprevpro_twitter_api_key_secret, $wprevpro_twitter_api_token, $wprevpro_twitter_api_token_secret);
			$authmethod = 'oauth1';
		} else {
			$connection = new Abraham\TwitterOAuth\TwitterOAuth('', '');
			$connection->setBearer($wprevpro_twitter_bearer);
			$authmethod = 'bearer';
		}
		$connection->setApiVersion('2');
		$connection->setDecodeJsonAsArray(true);

		//'all' uses the full-archive search (needs elevated/paid access), everything else uses recent (last 7 days).
		if($searchendpoint=='all'){
			$searchpath = 'tweets/search/all';
		} else {
			$searchpath = 'tweets/search/recent';
		}

		$params = array(
			'query' => $searchquery,
			'max_results' => '100',
			'tweet.fields' => 'created_at,lang,public_metrics,author_id,note_tweet',
			'expansions' => 'author_id',
			'user.fields' => 'name,username,profile_image_url',
		);
		$apiresult = $connection->get($searchpath, $params);

		//build a lookup of users by id from the includes payload.
		$usersbyid = array();
		if(isset($apiresult['includes']['users']) && is_array($apiresult['includes']['users'])){
			foreach($apiresult['includes']['users'] as $u){
				$usersbyid[$u['id']] = $u;
			}
		}

		//normalize the v2 response into the legacy statuses.results shape the picker JS expects.
		$statuses = array('results' => array());
		if(isset($apiresult['data']) && is_array($apiresult['data'])){
			foreach($apiresult['data'] as $tweet){
				$authorid = isset($tweet['author_id']) ? $tweet['author_id'] : '';
				$u = isset($usersbyid[$authorid]) ? $usersbyid[$authorid] : array();

				//note_tweet holds the full text for posts longer than 280 characters.
				if(isset($tweet['note_tweet']['text']) && $tweet['note_tweet']['text']!=''){
					$tweettext = $tweet['note_tweet']['text'];
				} else {
					$tweettext = isset($tweet['text']) ? $tweet['text'] : '';
				}

				$metrics = isset($tweet['public_metrics']) ? $tweet['public_metrics'] : array();

				$statuses['results'][] = array(
					'id_str' => isset($tweet['id']) ? (string)$tweet['id'] : '',
					'text' => $tweettext,
					'truncated' => false,
					'created_at' => isset($tweet['created_at']) ? $tweet['created_at'] : '',
					'lang' => isset($tweet['lang']) ? $tweet['lang'] : '',
					'retweet_count' => isset($metrics['retweet_count']) ? $metrics['retweet_count'] : 0,
					'favorite_count' => isset($metrics['like_count']) ? $metrics['like_count'] : 0,
					'reply_count' => isset($metrics['reply_count']) ? $metrics['reply_count'] : 0,
					'user' => array(
						'screen_name' => isset($u['username']) ? $u['username'] : '',
						'name' => isset($u['name']) ? $u['name'] : '',
						'profile_image_url_https' => isset($u['profile_image_url']) ? $u['profile_image_url'] : '',
					),
				);
			}
		}

		//get an array of all tweets in db and pass back so we can know what we already have.
		$reviews_table = $wpdb->prefix . 'wpfb_reviews';
		$resultarray['savedreviews'] = $wpdb->get_col( "SELECT unique_id FROM ".$reviews_table." WHERE type = 'Twitter'" );

		$httpcode = $connection->getLastHttpCode();
		if ($httpcode == 200) {
			$resultarray['ack'] = 'success';
			$resultarray['msg'] ='';
			$resultarray['statuses'] =$statuses;
		} else {
			// Handle error case with a human-readable message when possible.
			$resultarray['ack'] = 'error';
			$resultarray['statuses'] =$statuses;
			$apibody = $connection->getLastBody();
			$apidetail = '';
			if(is_array($apibody)){
				if(isset($apibody['detail'])){
					$apidetail = $apibody['detail'];
				} else if(isset($apibody['title'])){
					$apidetail = $apibody['title'];
				}
			}

			if($httpcode == 401 || $httpcode == 403){
				$authlabel = ($authmethod === 'oauth1') ? 'OAuth 1.0a user-context keys' : 'Bearer Token (app-only)';
				$resultarray['msg'] = 'X API returned '.$httpcode.' Unauthorized/Forbidden using '.$authlabel.'. Confirm the App is on Pay Per Use (or another paid plan) with Recent Search access, that credentials belong to that App/Project, and try regenerating Access Token + Secret (or Bearer). See https://developer.x.com/en/portal/products';
				if($apidetail!=''){
					$resultarray['msg'] .= ' X said: '.$apidetail;
				}
			} else if($apidetail!=''){
				$resultarray['msg'] = 'X API error ('.$httpcode.'): '.$apidetail;
			} else {
				$resultarray['msg'] = 'X API error ('.$httpcode.'): '.json_encode($apibody);
			}
		}

		echo json_encode($resultarray);
		die();
	}
	//for saving or deleting tweets in db
	public function wprp_twitter_savetweet_ajax() {
		check_ajax_referer('randomnoncestring', 'wpfb_nonce');

		$resultarray = array();
		$saveordel =  sanitize_text_field($_POST['saveordel']);
		
		$review_text = sanitize_text_field($_POST['tw_text']);
		$tw_rtc = sanitize_text_field($_POST['tw_rtc']);
		$tw_rc = sanitize_text_field($_POST['tw_rc']);
		$tw_fc = sanitize_text_field($_POST['tw_fc']);
		$tw_time = sanitize_text_field($_POST['tw_time']);
		$tw_id = sanitize_text_field($_POST['tw_id']);
		$tw_sname = sanitize_text_field($_POST['tw_sname']);
		$tw_name = sanitize_text_field($_POST['tw_name']);
		$tw_img = sanitize_text_field($_POST['tw_img']);
		$tw_lang = sanitize_text_field($_POST['tw_lang']);
		
		$fid = sanitize_text_field($_POST['fid']);
		$limage = sanitize_text_field($_POST['limage']);

		$pagename = sanitize_text_field($_POST['title']);
		$pageid = str_replace(" ","",$pagename)."_".$fid;
		$pageid = str_replace("'","",$pageid);
		$pageid = str_replace('"',"",$pageid);

		$timestamp = $this->myStrtotime($tw_time);
		if(!$timestamp){
			//X API v2 returns ISO-8601 (e.g. 2026-08-02T01:32:10.000Z).
			$timestamp = strtotime($tw_time);
		}
		if(!$timestamp){
			$timestamp = time();
		}
		$unixtimestamp = $timestamp;
		$timestamp = date("Y-m-d H:i:s", $timestamp);
		
		$review_length = mb_substr_count($review_text, ' ');
		
		//find character length
		if (extension_loaded('mbstring')) {
			$review_length_char = mb_strlen($review_text);
		} else {
			$review_length_char = strlen($review_text);
		}
		
		$from_url = "https://x.com/".$tw_sname."/status/".$tw_id;

		$cats = isset($_POST['cats']) ? sanitize_text_field($_POST['cats']) : '';
		$cats = str_replace("'",'"',$cats);
		$posts = isset($_POST['posts']) ? sanitize_text_field($_POST['posts']) : '';
		$posts = str_replace("'",'"',$posts);

		//save likes, retweets, and replies in meta_data
		//===============================================
		$meta_data['user_url'] = "https://x.com/".$tw_sname;
		$meta_data['favorite_count'] = $tw_fc;
		$meta_data['retweet_count'] = $tw_rtc;
		$meta_data['reply_count'] = $tw_rc;
		$meta_data['screenname'] = $tw_sname;
		$meta_json = json_encode($meta_data);
		//===============================================
		
		
		global $wpdb;
		$table_name = $wpdb->prefix . 'wpfb_reviews';
		//if saving in db
		if($saveordel=='save'){
			
			$stat = [
						'reviewer_name' => $tw_name,
						'reviewer_id' => trim($tw_sname),
						'pagename' => trim($pagename),
						'pageid' => trim($pageid),
						'userpic' => $tw_img,
						'recommendation_type' => 'positive',
						'created_time' => $timestamp,
						'created_time_stamp' => $unixtimestamp,
						'review_text' => $review_text,
						'hide' => '',
						'review_length' => $review_length,
						'review_length_char' => $review_length_char,
						'type' => 'Twitter',
						'from_url' => trim($from_url),
						'from_url_review' => trim($from_url),
						'language_code' => $tw_lang,
						'unique_id' => $tw_id,
						'meta_data' => $meta_json,
						'categories' => trim($cats),
						'posts' => trim($posts),
					];
			
		$insertnum = $wpdb->insert( $table_name, $stat );
		$resultarray['insertnum']=$insertnum;
			
			//Avatar caching is handled by the separate wprevpro_download_avatar_tolocal AJAX
			//action. Do not call it from here — it re-checks the nonce and can fatally error
			//if the uploads directory is not writable, which breaks the save response.
		}
		
		
		echo json_encode($resultarray);
		die();
		
	}
	//to delete tweet via ajax
	public function wprp_twitter_deltweet_ajax() {
		check_ajax_referer('randomnoncestring', 'wpfb_nonce');
		$tw_id = sanitize_text_field($_POST['tw_id']);
		
		global $wpdb;
		$table_name = $wpdb->prefix . 'wpfb_reviews';

		//remove this tweets
		$deletereview = $wpdb->delete( $table_name, array( 'unique_id' => $tw_id ), array( '%s' ) );
		$resultarray['deletenum']=$deletereview;
		
		echo json_encode($resultarray);
		die();
		
	}
	
		//fix stringtotime for other languages
	private function myStrtotime($date_string) { 
		$monthnamearray = array(
		'janvier'=>'jan',
		'février'=>'feb',
		'mars'=>'march',
		'avril'=>'apr',
		'mai'=>'may',
		'juin'=>'jun',
		'juillet'=>'jul',
		'août'=>'aug',
		'septembre'=>'sep',
		'octobre'=>'oct',
		'novembre'=>'nov',
		'décembre'=>'dec',
		'gennaio'=>'jan',
		'febbraio'=>'feb',
		'marzo'=>'march',
		'aprile'=>'apr',
		'maggio'=>'may',
		'giugno'=>'jun',
		'luglio'=>'jul',
		'agosto'=>'aug',
		'settembre'=>'sep',
		'ottobre'=>'oct',
		'novembre'=>'nov',
		'dicembre'=>'dec',
		'janeiro'=>'jan',
		'fevereiro'=>'feb',
		'março'=>'march',
		'abril'=>'apr',
		'maio'=>'may',
		'junho'=>'jun',
		'julho'=>'jul',
		'agosto'=>'aug',
		'setembro'=>'sep',
		'outubro'=>'oct',
		'novembro'=>'nov',
		'dezembro'=>'dec',
		'enero'=>'jan',
		'febrero'=>'feb',
		'marzo'=>'march',
		'abril'=>'apr',
		'mayo'=>'may',
		'junio'=>'jun',
		'julio'=>'jul',
		'agosto'=>'aug',
		'septiembre'=>'sep',
		'octubre'=>'oct',
		'noviembre'=>'nov',
		'diciembre'=>'dec',
		'januari'=>'jan',
		'februari'=>'feb',
		'maart'=>'march',
		'april'=>'apr',
		'mei'=>'may',
		'juni'=>'jun',
		'juli'=>'jul',
		'augustus'=>'aug',
		'september'=>'sep',
		'oktober'=>'oct',
		'november'=>'nov',
		'december'=>'dec',
		' de '=>'',
		'dezember'=>'dec',
		'januar '=>'jan ',
		'stycznia'=>'jan',
		'lutego'=>'feb',
		'februar'=>'feb',
		'marca'=>'march',
		'märz'=>'march',
		'kwietnia'=>'apr',
		'maja'=>'may',
		'czerwca'=>'jun',
		'lipca'=>'jul',
		'sierpnia'=>'aug',
		'września'=>'sep',
		'października'=>'oct',
		'listopada'=>'nov',
		'grudnia'=>'dec',
		);
		//echo strtr(strtolower($date_string), $monthnamearray);
		return strtotime(strtr(strtolower($date_string), $monthnamearray)); 
	}
	
	/**
	 * displays message in admin if it's been longer than 30 days.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function wprp_admin_notice__success () {

		$activatedtime = get_option('wprev_activated_time');
		//if this is an old install then use 23 days ago
		if($activatedtime==''){
			$activatedtime= time() - (86400*23);
			update_option( 'wprev_activated_time', $activatedtime );
		}
		$thirtydaysago = time() - (86400*30);
		
		//check if an option was clicked on
		if (isset($_GET['wprevpronotice'])) {
		  $wprevpronotice = $_GET['wprevpronotice'];
		} else {
		  //Handle the case where there is no parameter
		   $wprevpronotice = '';
		}
		if($wprevpronotice=='mlater'){		//hide the notice for another 30 days
			update_option( 'wprev_notice_hide', 'later' );
			$newtime = time() - (86400*21);
			update_option( 'wprev_activated_time', $newtime );
			$activatedtime = $newtime;
			
		} else if($wprevpronotice=='notagain'){		//hide the notice forever
			update_option( 'wprev_notice_hide', 'never' );
		}
		
		$wprev_notice_hide = get_option('wprev_notice_hide');
		
		if($activatedtime<$thirtydaysago && $wprev_notice_hide!='never'){
		
			$urltrimmedtab = remove_query_arg( array('taction', 'tid', 'sortby', 'sortdir', 'opt') );
			$urlmayberlater = esc_url( add_query_arg( 'wprevpronotice', 'mlater',$urltrimmedtab ) );
			$urlnotagain = esc_url( add_query_arg( 'wprevpronotice', 'notagain',$urltrimmedtab ) );
			
			$temphtml = '<p>Hey, I noticed you\'ve been using my <b>WP Review Slider</b> plugin for a while now – that’s awesome! Could you please do me a BIG favor and give it a 5-star rating on WordPress? <br>
			Thanks!<br>
			~ Josh W.<br></p>
			<ul>
			<li><a href="https://wordpress.org/support/plugin/wp-facebook-reviews/reviews/#new-post" target="_blank">Ok, you deserve it</a></li>
			<li><a href="'.$urlmayberlater.'">Not right now, maybe later</a></li>
			<li><a href="'.$urlnotagain.'">Don\'t remind me again</a></li>
			</ul>
			<p>P.S. If you\'ve been thinking about upgrading to the <a href="https://wpreviewslider.com/" target="_blank">Pro</a> version, here\'s a 10% off coupon code you can use! ->  <b>wprevpro10off</b></p>';
			
			?>
			<div class="notice notice-info">
				<div class="wprevpro_admin_notice" style="color: #007500;">
				<?php _e( $temphtml, $this->_token ); ?>
				</div>
			</div>
			<?php
		}

	}
	
		/**
	 * add dashboard widget to wordpress admin
	 * @access  public
	 * @since   9.1
	 * @return  void
	 */
	public function wprevpro_dashboard_widget() {
		global $wp_meta_boxes;
		//wp_add_dashboard_widget('custom_help_widget', 'Theme Support', 'custom_dashboard_help');
		add_meta_box( 'id', 'WP Review Slider Recent Reviews', array($this,'custom_dashboard_help'), 'dashboard', 'side', 'high' );
	}
	 
	public function custom_dashboard_help() {
		global $wpdb;
		$reviews_table_name = $wpdb->prefix . 'wpfb_reviews';
		$tempquery = "select * from ".$reviews_table_name." ORDER by created_time_stamp Desc limit 4";
		$reviewrows = $wpdb->get_results($tempquery);
		$now = time(); // or your date as well
		
		echo '<style>
			img.wprev_dash_avatar {float: left;margin-right: 8px;border-radius: 20px;}
			.wprev_dash_stars {float: right;}
			p.wprev_dash_text {margin-top: -6px;}
			span.wprev_dash_timeago {font-size: 12px;font-style: italic;}
			.wprev_dash_revdiv {min-height: 50px;}
			</style>';
		echo '<ul>';
		foreach ( $reviewrows as $review ) 
		{
			$timesince = '';
			if(strlen($review->review_text)>130){
				$reviewtext = substr($review->review_text,0,130).'...';
			} else {
				$reviewtext = $review->review_text;
			}
			
			$your_date = $review->created_time_stamp;
			$datediff = $now - $your_date;
			$daysago = round($datediff / (60 * 60 * 24));
			if($daysago==1){
				$daysagohtml = $daysago.' day ago';
			} else {
				$daysagohtml = $daysago.' days ago';
			}
			if($review->rating<1){
				if($review->recommendation_type=='positive'){
					$review->rating=5;
				} else {
					$review->rating=2;
				}
			}

			$imgs_url = plugin_dir_url(__DIR__).'/public/partials/imgs/';
			$starfile = 'stars_'.$review->rating.'_yellow.png';
			$starhtml='<img src="'.$imgs_url."".$starfile.'" alt="'.$review->rating.' star rating" class="wprev_dash_stars">';
			
		$avatarhtml = '';
		if(isset($review->userpic) && $review->userpic!=''){
			$avatarhtml = '<img alt="" src="'.esc_url($review->userpic).'" class="wprev_dash_avatar" height="40" width="40">';
		}
		
		echo '<li><div class="wprev_dash_revdiv">'.$avatarhtml.'<div class="wprev_dash_stars">'.$starhtml.'</div><h4 class="wprev_dash_name">'.esc_html($review->reviewer_name).' - <span class="wprev_dash_timeago">'.$daysagohtml.'</span></h4><p class="wprev_dash_text">'.esc_html($reviewtext).'</p></div></li>';
			
		}
		echo '</ul>';
		
		echo '<div><a href="admin.php?page=wpfb-reviews">All Reviews</a> - <a href="https://wpreviewslider.com/" target="_blank">Go Pro For More Cool Features!</a></div>';
	}

	/**
	 * Allow a hex or rgb(a) color string, otherwise return empty.
	 *
	 * @param string $color Raw color value.
	 * @return string
	 */
	private function wpfb_sanitize_css_color( $color ) {
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
	}

	/**
	 * Ajax: return the rendered template HTML for the live preview.
	 */
	public function wpfb_previewtemplate_ajax() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Insufficient permissions' ) );
			return;
		}
		check_ajax_referer( 'randomnoncestring', 'wpfb_nonce' );

		$tid         = isset( $_POST['tid'] ) ? absint( $_POST['tid'] ) : 0;
		$returnarray = $this->wpfb_previewtemplate_ajax_get( $tid );
		echo wp_json_encode( $returnarray );
		die();
	}

	/**
	 * Build preview HTML for a template id using the public shortcode renderer.
	 *
	 * @param int $tid Template id.
	 * @return array
	 */
	public function wpfb_previewtemplate_ajax_get( $tid ) {
		$atts = array( 'tid' => absint( $tid ) );
		require_once plugin_dir_path( __DIR__ ) . 'public/class-wp-fb-reviews-public.php';
		$plugin_public_class = new WP_FB_Reviews_Public( $this->_token, $this->version );
		$templatehtml        = $plugin_public_class->wprev_usetemplate_func( $atts, null );

		return array(
			'tid'          => absint( $tid ),
			'ack'          => 'success',
			'templatehtml' => $templatehtml,
		);
	}

	/**
	 * Ajax: save (insert/update) a review template then return its preview HTML.
	 *
	 * Mirrors the page-POST save logic in admin/partials/templates_posts.php so
	 * the live preview reflects exactly what will be stored.
	 */
	public function wpfb_savetemplate_ajax() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Insufficient permissions' ) );
			return;
		}
		check_ajax_referer( 'randomnoncestring', 'wpfb_nonce' );

		$formdata  = isset( $_POST['data'] ) ? stripslashes( $_POST['data'] ) : '';
		$formarray = json_decode( $formdata, true );
		if ( ! is_array( $formarray ) ) {
			echo wp_json_encode( array( 'ack' => 'error', 'ackmessage' => 'Invalid form data.' ) );
			die();
		}

		global $wpdb;
		$table_name = $wpdb->prefix . 'wpfb_post_templates';

		$get = function ( $key, $default = '' ) use ( $formarray ) {
			return isset( $formarray[ $key ] ) ? $formarray[ $key ] : $default;
		};

		$t_id             = sanitize_text_field( $get( 'edittid' ) );
		$title            = sanitize_text_field( $get( 'wpfbr_template_title' ) );
		$template_type    = sanitize_text_field( $get( 'wpfbr_template_type', 'post' ) );
		$style            = sanitize_text_field( $get( 'wprevpro_template_style', '1' ) );
		$display_num      = sanitize_text_field( $get( 'wpfbr_t_display_num', '3' ) );
		$display_num_rows = sanitize_text_field( $get( 'wpfbr_t_display_num_rows', '1' ) );
		$display_order    = sanitize_text_field( $get( 'wpfbr_t_display_order', 'newest' ) );
		$hide_no_text     = sanitize_text_field( $get( 'wpfbr_t_hidenotext', 'no' ) );
		$template_css     = sanitize_textarea_field( $get( 'wpfbr_template_css' ) );
		$createslider     = sanitize_text_field( $get( 'wpfbr_t_createslider', 'no' ) );
		$numslides        = sanitize_text_field( $get( 'wpfbr_t_numslides', '3' ) );
		$read_more        = sanitize_text_field( $get( 'wprevpro_t_read_more', 'no' ) );
		$read_more_text   = sanitize_text_field( $get( 'wprevpro_t_read_more_text', 'read more' ) );
		$read_more_num    = absint( $get( 'wprevpro_t_read_more_num', '30' ) );
		$min_rating       = sanitize_text_field( $get( 'wpfbr_t_min_rating', '1' ) );
		$slidermobileview = sanitize_text_field( $get( 'wpfbr_slidermobileview' ) );
		$review_same_hgt  = sanitize_text_field( $get( 'wpfbr_t_review_same_height', 'no' ) );
		$filtersource     = sanitize_text_field( $get( 'wpfbr_t_filtersource' ) );
		$filterrtype      = sanitize_text_field( $get( 'wpfbr_t_rtype', 'fb' ) );
		if ( $filterrtype === 'twitter' ) {
			$rtype_save = '["twitter"]';
		} elseif ( $filterrtype === 'both' ) {
			$rtype_save = '["fb","twitter"]';
		} else {
			$rtype_save = '["fb"]';
		}

		// template misc (style + slider + badge, stored as JSON)
		$templatemiscarray = array();
		$templatemiscarray['showstars'] = sanitize_text_field( $get( 'wprevpro_template_misc_showstars', 'yes' ) );
		$templatemiscarray['showdate']  = sanitize_text_field( $get( 'wprevpro_template_misc_showdate', 'yes' ) );
		$templatemiscarray['bgcolor1']  = $this->wpfb_sanitize_css_color( $get( 'wprevpro_template_misc_bgcolor1' ) );
		$templatemiscarray['bgcolor2']  = $this->wpfb_sanitize_css_color( $get( 'wprevpro_template_misc_bgcolor2' ) );
		$templatemiscarray['tcolor1']   = $this->wpfb_sanitize_css_color( $get( 'wprevpro_template_misc_tcolor1' ) );
		$templatemiscarray['tcolor2']   = $this->wpfb_sanitize_css_color( $get( 'wprevpro_template_misc_tcolor2' ) );
		$templatemiscarray['tcolor3']   = $this->wpfb_sanitize_css_color( $get( 'wprevpro_template_misc_tcolor3' ) );
		$templatemiscarray['bradius']   = sanitize_text_field( $get( 'wprevpro_template_misc_bradius', '0' ) );
		$templatemiscarray['showmedia'] = sanitize_text_field( $get( 'wpfbr_t_showmedia', 'yes' ) );

		// Style-tab options.
		$templatemiscarray['verified']       = sanitize_text_field( $get( 'wprevpro_template_misc_verified', 'no' ) );
		$templatemiscarray['lastnameformat'] = sanitize_text_field( $get( 'wprevpro_template_misc_lastname', 'show' ) );
		$templatemiscarray['avataropt']      = sanitize_text_field( $get( 'wprevpro_template_misc_avataropt', 'show' ) );
		$templatemiscarray['showicon']       = sanitize_text_field( $get( 'wprevpro_template_misc_showicon', 'lin' ) );
		$ajax_tfont1 = absint( $get( 'wprevpro_template_misc_tfont1', 0 ) );
		$ajax_tfont2 = absint( $get( 'wprevpro_template_misc_tfont2', 0 ) );
		$templatemiscarray['tfont1'] = $ajax_tfont1 > 0 ? (string) $ajax_tfont1 : '';
		$templatemiscarray['tfont2'] = $ajax_tfont2 > 0 ? (string) $ajax_tfont2 : '';

		// General-tab options (slider + read more).
		$templatemiscarray['slidespeed']         = sanitize_text_field( $get( 'wpfbr_t_slidespeed', '1' ) );
		$templatemiscarray['slideautodelay']     = sanitize_text_field( $get( 'wpfbr_t_slideautodelay', '5' ) );
		$templatemiscarray['sliderautoplay']     = sanitize_text_field( $get( 'wpfbr_sliderautoplay' ) );
		$templatemiscarray['sliderhideprevnext'] = sanitize_text_field( $get( 'wpfbr_sliderhideprevnext' ) );
		$templatemiscarray['sliderhidedots']     = sanitize_text_field( $get( 'wpfbr_sliderhidedots' ) );
		$templatemiscarray['sliderfixedheight']  = sanitize_text_field( $get( 'wpfbr_sliderfixedheight' ) );
		$templatemiscarray['slidermobileview']   = $slidermobileview;
		$templatemiscarray['review_same_height'] = $review_same_hgt;
		$templatemiscarray['read_more_num']      = (string) $read_more_num;
		$templatemiscarray['read_more_color']    = $this->wpfb_sanitize_css_color( $get( 'wprevpro_t_read_more_color' ) );

		// Filter source lives in misc (the public renderer reads template_misc['filtersource']).
		$templatemiscarray['filtersource'] = $filtersource;

		// Badge options.
		$templatemiscarray['blocation'] = sanitize_text_field( $get( 'wpfbr_t_blocation' ) );
		$templatemiscarray['bname']     = sanitize_text_field( $get( 'wpfbr_t_bname' ) );
		$templatemiscarray['bnameurl']  = esc_url_raw( $get( 'wpfbr_t_bnameurl' ) );
		$templatemiscarray['bimgurl']   = esc_url_raw( $get( 'wpfbr_t_bimgurl' ) );
		$templatemiscarray['bshape']    = sanitize_text_field( $get( 'wpfbr_t_bshape' ) );
		$templatemiscarray['bimgsize']  = sanitize_text_field( $get( 'wpfbr_t_bimgsize', '50' ) );
		$templatemiscarray['bbtnurl']   = esc_url_raw( $get( 'wpfbr_t_bbtnurl' ) );
		$templatemiscarray['bbradius']  = sanitize_text_field( $get( 'wpfbr_t_bbradius', '0' ) );
		$templatemiscarray['bbwidth']   = sanitize_text_field( $get( 'wpfbr_t_bbwidth', '0' ) );
		$templatemiscarray['bbtncolor'] = $this->wpfb_sanitize_css_color( $get( 'wpfbr_t_bbtncolor', '#1877f2' ) );
		$templatemiscarray['bbkcolor']  = $this->wpfb_sanitize_css_color( $get( 'wpfbr_t_bbkcolor', '#ffffff' ) );
		$templatemiscarray['bbcolor']   = $this->wpfb_sanitize_css_color( $get( 'wpfbr_t_bbcolor', '#eeeeee' ) );
		$templatemiscarray['bdropsh']   = sanitize_text_field( $get( 'wpfbr_t_bdropsh' ) );
		$templatemiscarray['bcenter']   = sanitize_text_field( $get( 'wpfbr_t_bcenter' ) );
		$templatemiscarray['bhname']    = sanitize_text_field( $get( 'wpfbr_t_bhname' ) );
		$templatemiscarray['bhphoto']   = sanitize_text_field( $get( 'wpfbr_t_bhphoto' ) );
		$templatemiscarray['bhbased']   = sanitize_text_field( $get( 'wpfbr_t_bhbased' ) );
		$templatemiscarray['bhbtn']     = sanitize_text_field( $get( 'wpfbr_t_bhbtn' ) );
		$templatemiscarray['bhpow']     = sanitize_text_field( $get( 'wpfbr_t_bhpow' ) );
		$templatemiscarray['bhreviews'] = sanitize_text_field( $get( 'wpfbr_t_bhreviews' ) );
		$templatemiscarray['bobasedon'] = sanitize_text_field( $get( 'wpfbr_t_bobasedon' ) );
		$templatemiscarray['borevus']   = sanitize_text_field( $get( 'wpfbr_t_borevus' ) );

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

		$returnarray = array(
			'iu'         => '',
			'ack'        => '',
			'ackmessage' => '',
			't_id'       => '',
		);

		if ( $t_id === '' ) {
			$returnarray['iu'] = 'insert';
			$inserttemplate    = $wpdb->insert( $table_name, $data, $format );
			$t_id              = $wpdb->insert_id;
			if ( ! $inserttemplate ) {
				$returnarray['ack']        = 'error';
				$returnarray['ackmessage'] = __( 'Unable to update. Try refreshing the page.', 'wp-fb-reviews' );
			} else {
				$returnarray['ack']        = 'success';
				$returnarray['ackmessage'] = __( 'Template Saved!', 'wp-fb-reviews' );
			}
		} else {
			$returnarray['iu'] = 'update';
			$updatetempquery   = $wpdb->update( $table_name, $data, array( 'id' => absint( $t_id ) ), $format, array( '%d' ) );
			if ( false === $updatetempquery ) {
				$returnarray['ack']        = 'error';
				$returnarray['ackmessage'] = __( 'Unable to update. Try refreshing the page.', 'wp-fb-reviews' );
			} else {
				$returnarray['ack']        = 'success';
				$returnarray['ackmessage'] = __( 'Template Updated!', 'wp-fb-reviews' );
			}
		}

		$returnarray['t_id']         = absint( $t_id );
		$returnpreview               = $this->wpfb_previewtemplate_ajax_get( absint( $t_id ) );
		$returnarray['templatehtml'] = $returnpreview['templatehtml'];

		echo wp_json_encode( $returnarray );
		die();
	}


}