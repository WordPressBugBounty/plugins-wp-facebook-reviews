<?php
/**
 * Editor script dependencies for the Facebook Reviews block.
 *
 * @package WP_FB_Reviews
 */

return array(
	'dependencies' => array(
		'wp-block-editor',
		'wp-blocks',
		'wp-components',
		'wp-element',
		'wp-i18n',
		'wp-server-side-render',
	),
	'version'      => (string) filemtime( __DIR__ . '/index.js' ),
);
