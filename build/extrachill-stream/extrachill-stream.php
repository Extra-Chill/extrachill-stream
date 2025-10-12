<?php
/**
 * Plugin Name: Extra Chill Stream
 * Plugin URI: https://extrachill.com
 * Description: Live streaming platform for artist platform members
 * Version: 1.0.0
 * Author: Chris Huber
 * Author URI: https://chubes.net
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * Text Domain: extrachill-stream
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'EXTRACHILL_STREAM_VERSION', '1.0.0' );
define( 'EXTRACHILL_STREAM_PLUGIN_FILE', __FILE__ );
define( 'EXTRACHILL_STREAM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'EXTRACHILL_STREAM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

add_action( 'plugins_loaded', 'extrachill_stream_init' );

function extrachill_stream_init() {
	require_once EXTRACHILL_STREAM_PLUGIN_DIR . 'inc/core/authentication.php';
	require_once EXTRACHILL_STREAM_PLUGIN_DIR . 'inc/core/assets.php';
	require_once EXTRACHILL_STREAM_PLUGIN_DIR . 'inc/core/http-client.php';
}

add_filter( 'extrachill_template_homepage', 'ec_stream_override_homepage_template' );

function ec_stream_override_homepage_template( $template ) {
	return EXTRACHILL_STREAM_PLUGIN_DIR . 'inc/core/stream-interface.php';
}

add_filter( 'extrachill_enable_sticky_header', '__return_false' );
