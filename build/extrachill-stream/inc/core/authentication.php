<?php
/**
 * Authentication System
 *
 * Restricts access to artist platform members only.
 * Non-members receive 404 error.
 *
 * @package ExtraChillStream
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'template_redirect', 'ec_stream_require_artist_membership', 5 );

/**
 * Require artist site membership for access
 *
 * Checks if user is a member of artist.extrachill.com using WordPress native
 * multisite membership function. Super admins always have access.
 */
function ec_stream_require_artist_membership() {
	// Check if user is logged in
	if ( ! is_user_logged_in() ) {
		wp_die(
			'<h1>404 Not Found</h1><p>The page you are looking for does not exist.</p>',
			'404 Not Found',
			array( 'response' => 404 )
		);
	}

	// Super admins always have access
	if ( current_user_can( 'manage_options' ) ) {
		return;
	}

	$user_id = get_current_user_id();

	// Get artist site blog ID
	$artist_blog_id = get_blog_id_from_url( 'artist.extrachill.com', '/' );

	if ( ! $artist_blog_id ) {
		// Artist site doesn't exist, deny access
		wp_die(
			'<h1>404 Not Found</h1><p>The page you are looking for does not exist.</p>',
			'404 Not Found',
			array( 'response' => 404 )
		);
	}

	// Check if user is a member of artist.extrachill.com
	if ( ! is_user_member_of_blog( $user_id, $artist_blog_id ) ) {
		wp_die(
			'<h1>404 Not Found</h1><p>The page you are looking for does not exist.</p>',
			'404 Not Found',
			array( 'response' => 404 )
		);
	}

	// User is a member of artist site, allow access
}
