<?php
/**
 * Stream Breadcrumb Integration
 *
 * Integrates with theme's breadcrumb system to provide stream-specific
 * breadcrumbs with "Extra Chill → Stream" root link.
 *
 * @package ExtraChillStream
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Change breadcrumb root to "Extra Chill → Stream" on stream pages
 *
 * Uses theme's extrachill_breadcrumbs_root filter to override the root link.
 * Only applies on blog ID 8 (stream.extrachill.com).
 *
 * @param string $root_link Default root breadcrumb link HTML
 * @return string Modified root link
 * @since 1.0.0
 */
function ec_stream_breadcrumb_root( $root_link ) {
	// Only apply on stream.extrachill.com (blog ID 8)
	if ( get_current_blog_id() !== 8 ) {
		return $root_link;
	}

	// On homepage, just "Extra Chill" (trail will add "Stream")
	if ( is_front_page() ) {
		return '<a href="https://extrachill.com">Extra Chill</a>';
	}

	// On other pages, include "Stream" in root
	return '<a href="https://extrachill.com">Extra Chill</a> › <a href="' . esc_url( home_url() ) . '">Stream</a>';
}
add_filter( 'extrachill_breadcrumbs_root', 'ec_stream_breadcrumb_root' );

/**
 * Override breadcrumb trail for stream homepage
 *
 * Displays just "Stream" (no link) on the homepage to prevent "Archives" suffix.
 *
 * @param string $custom_trail Existing custom trail from other plugins
 * @return string Breadcrumb trail HTML
 * @since 1.0.0
 */
function ec_stream_breadcrumb_trail_homepage( $custom_trail ) {
	// Only apply on stream.extrachill.com (blog ID 8)
	if ( get_current_blog_id() !== 8 ) {
		return $custom_trail;
	}

	// Only on front page (homepage)
	if ( is_front_page() ) {
		return '<span>Stream</span>';
	}

	return $custom_trail;
}
add_filter( 'extrachill_breadcrumbs_override_trail', 'ec_stream_breadcrumb_trail_homepage' );

/**
 * Override back-to-home link label for stream pages
 *
 * Changes "Back to Extra Chill" to "Back to Stream" on stream pages.
 * Uses theme's extrachill_back_to_home_label filter.
 * Only applies on blog ID 8 (stream.extrachill.com).
 *
 * @param string $label Default back-to-home link label
 * @param string $url   Back-to-home link URL
 * @return string Modified label
 * @since 1.0.0
 */
function ec_stream_back_to_home_label( $label, $url ) {
	// Only apply on stream.extrachill.com (blog ID 8)
	if ( get_current_blog_id() !== 8 ) {
		return $label;
	}

	// Don't override on homepage (homepage should say "Back to Extra Chill")
	if ( is_front_page() ) {
		return $label;
	}

	return '← Back to Stream';
}
add_filter( 'extrachill_back_to_home_label', 'ec_stream_back_to_home_label', 10, 2 );
