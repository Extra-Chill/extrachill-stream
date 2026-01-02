<?php
/**
 * REST API Endpoints
 *
 * Placeholder REST API structure for future streaming functionality.
 *
 * @package ExtraChillStream
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Check REST API permissions
 *
 * @return bool|WP_Error
 */
function ec_stream_rest_permissions_check() {
	if ( ! is_user_logged_in() ) {
		return new WP_Error( 'rest_forbidden', 'You must be logged in.', array( 'status' => 401 ) );
	}

	$user_id = get_current_user_id();
	$artist_blog_id = function_exists( 'ec_get_blog_id' ) ? ec_get_blog_id( 'artist' ) : null;

	if ( ! $artist_blog_id ) {
		return new WP_Error( 'rest_forbidden', 'Access denied.', array( 'status' => 403 ) );
	}

	if ( ! is_user_member_of_blog( $user_id, $artist_blog_id ) ) {
		return new WP_Error( 'rest_forbidden', 'Access denied.', array( 'status' => 403 ) );
	}

	return true;
}

/**
 * Get stream status endpoint
 *
 * Placeholder for future stream status functionality.
 *
 * @param WP_REST_Request $request Request object.
 * @return WP_REST_Response
 */
function ec_stream_rest_get_status( $request ) {
	// Placeholder response for Phase 1
	return rest_ensure_response( array(
		'is_live'   => false,
		'message'   => 'Streaming functionality not yet implemented (Phase 1)',
		'platforms' => array(),
	) );
}