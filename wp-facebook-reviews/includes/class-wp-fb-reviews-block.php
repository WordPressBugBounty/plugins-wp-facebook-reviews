<?php
/**
 * Gutenberg block for inserting a saved review template.
 *
 * @package WP_FB_Reviews
 */

/**
 * Registers the Facebook Reviews block and renders the selected template.
 *
 * @since 15.4
 */
class WP_FB_Reviews_Block {

	/**
	 * Editor script handle generated from the block name.
	 *
	 * @var string
	 */
	const EDITOR_SCRIPT_HANDLE = 'wp-fb-reviews-template-editor-script';

	/**
	 * Register the block from block.json.
	 *
	 * @since 15.4
	 */
	public function register_block() {
		if ( ! function_exists( 'register_block_type_from_metadata' ) ) {
			return;
		}

		register_block_type_from_metadata(
			wpfbrev_plugin_dir . 'blocks/template',
			array(
				'render_callback' => array( $this, 'render_block' ),
			)
		);

		$style_handle = 'wp-fb-reviews-template-editor-style';
		if ( wp_style_is( $style_handle, 'registered' ) ) {
			wp_styles()->registered[ $style_handle ]->ver = $this->asset_version( 'blocks/template/editor.css' );
		}
	}

	/**
	 * Pass saved templates to the block editor.
	 *
	 * @since 15.4
	 */
	public function enqueue_editor_data() {
		static $added = false;

		if ( $added || ! wp_script_is( self::EDITOR_SCRIPT_HANDLE, 'registered' ) ) {
			return;
		}

		$added = true;

		wp_add_inline_script(
			self::EDITOR_SCRIPT_HANDLE,
			'window.wprevFbReviewsBlock = ' . wp_json_encode( $this->editor_config() ) . ';',
			'before'
		);
	}

	/**
	 * Load the front-end template assets in the editor canvas.
	 *
	 * @since 15.4
	 */
	public function enqueue_preview_assets() {
		if ( ! is_admin() ) {
			return;
		}

		$public = new WP_FB_Reviews_Public( 'wp-fb-reviews', '15.4' );
		$public->enqueue_styles();
		$public->enqueue_scripts();

		wp_enqueue_script(
			'wp-fb-reviews-block-preview',
			wpfbrev_plugin_url . '/public/js/wprev-block-preview.js',
			array( 'jquery', 'wp-fb-reviews_plublic', 'wp-fb-reviews_unslider-swipe-min' ),
			$this->asset_version( 'public/js/wprev-block-preview.js' ),
			true
		);
	}

	/**
	 * Cache-busting version for a plugin file.
	 *
	 * @since 15.4
	 *
	 * @param string $relative Path relative to the plugin root.
	 * @return string
	 */
	private function asset_version( $relative ) {
		$path = wpfbrev_plugin_dir . ltrim( $relative, '/' );
		$time = file_exists( $path ) ? filemtime( $path ) : false;

		return $time ? (string) $time : '15.4';
	}

	/**
	 * Render the selected template on the front end.
	 *
	 * @since 15.4
	 *
	 * @param array $attributes Block attributes.
	 * @return string
	 */
	public function render_block( $attributes ) {
		$template_id = isset( $attributes['templateId'] ) ? absint( $attributes['templateId'] ) : 0;
		if ( $template_id < 1 ) {
			return '';
		}

		$html = $this->render_template( $template_id );
		if ( '' === trim( $html ) ) {
			return '';
		}

		$wrapper_attributes = function_exists( 'get_block_wrapper_attributes' )
			? get_block_wrapper_attributes()
			: 'class="wp-block-wp-fb-reviews-template"';

		return '<div ' . $wrapper_attributes . '>' . $html . '</div>';
	}

	/**
	 * Build the template markup through the same display file as the shortcode.
	 *
	 * @since 15.4
	 *
	 * @param int $template_id Template ID.
	 * @return string
	 */
	private function render_template( $template_id ) {
		$display_file = wpfbrev_plugin_dir . 'public/partials/wp-fb-reviews-public-display.php';
		
		require_once wpfbrev_plugin_dir . 'public/partials/wprev-badge-compat.php';
		if ( function_exists( 'wprev_free_resolve_template_display_file' ) ) {
			$display_file = wprev_free_resolve_template_display_file( $template_id, $display_file, 'facebook' );
		}

		// The display partial reads $a['tid'], the same contract as the shortcode.
		$a = array(
			'tid' => (string) $template_id,
		);

		ob_start();
		include $display_file;
		return (string) ob_get_clean();
	}

	/**
	 * Data used by the editor template dropdown.
	 *
	 * @since 15.4
	 *
	 * @return array{templates:array<int,array{id:int,title:string}>,templatesUrl:string,editUrl:string}
	 */
	private function editor_config() {
		$edit_url = '';
		if ( current_user_can( 'manage_options' ) ) {
			// Keep raw ampersands. wp_nonce_url() HTML-escapes them, which turns taction into amp;taction.
			$edit_url = add_query_arg(
				array(
					'page'     => 'wpfb-templates_posts',
					'taction'  => 'edit',
					'_wpnonce' => wp_create_nonce( 'tedit_' ),
				),
				admin_url( 'admin.php' )
			);
		}

		return array(
			'templates'    => $this->get_templates(),
			'templatesUrl' => admin_url( 'admin.php?page=wpfb-templates_posts' ),
			'editUrl'      => $edit_url,
		);
	}

	/**
	 * Saved post/page templates for the editor dropdown.
	 *
	 * Widget templates are omitted. That template type is being phased out.
	 *
	 * @since 15.4
	 *
	 * @return array<int,array{id:int,title:string}>
	 */
	private function get_templates() {
		global $wpdb;

		$table = $wpdb->prefix . 'wpfb_post_templates';
		$rows  = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, title FROM {$table} WHERE ( rtype LIKE %s OR rtype LIKE %s ) AND ( template_type IS NULL OR template_type != %s ) AND title != %s ORDER BY title ASC, id ASC", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				'%fb%',
				'%twitter%',
				'widget',
				''
			)
		);

		if ( ! is_array( $rows ) ) {
			return array();
		}

		$templates = array();
		foreach ( $rows as $row ) {
			$title = trim( wp_strip_all_tags( (string) $row->title ) );
			if ( '' === $title ) {
				continue;
			}
			$templates[] = array(
				'id'    => (int) $row->id,
				'title' => $title,
			);
		}

		return $templates;
	}
}
