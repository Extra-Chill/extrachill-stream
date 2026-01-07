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
	if ( is_front_page() ) {
		$main_site_url = ec_get_site_url( 'main' );
		return '<a href="' . esc_url( $main_site_url ) . '">Extra Chill</a>';
	}

	$main_site_url = ec_get_site_url( 'main' );
	return '<a href="' . esc_url( $main_site_url ) . '">Extra Chill</a> › <a href="' . esc_url( home_url() ) . '">Stream</a>';
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
	if ( is_front_page() ) {
		return '<span class="network-dropdown-target">Stream</span>';
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
	if ( is_front_page() ) {
		return $label;
	}

	return 'Back to Stream';
}
add_filter( 'extrachill_back_to_home_label', 'ec_stream_back_to_home_label', 10, 2 );

/**
 * Override schema breadcrumb items for stream site
 *
 * Aligns schema breadcrumbs with visual breadcrumbs for stream.extrachill.com.
 * Only applies on blog ID 8 (stream.extrachill.com).
 *
 * Output patterns:
 * - Homepage: [Extra Chill, Stream]
 * - Other pages: [Extra Chill, Stream, Page Title]
 *
 * @hook extrachill_seo_breadcrumb_items
 * @param array $items Default breadcrumb items from SEO plugin
 * @return array Modified breadcrumb items for stream context
 * @since 1.1.0
 */
function ec_stream_schema_breadcrumb_items( $items ) {
	$stream_blog_id = function_exists( 'ec_get_blog_id' ) ? ec_get_blog_id( 'stream' ) : null;
	if ( ! $stream_blog_id || get_current_blog_id() !== $stream_blog_id ) {
		return $items;
	}

	$main_site_url = function_exists( 'ec_get_site_url' ) ? ec_get_site_url( 'main' ) : 'https://extrachill.com';

	// Homepage: Extra Chill → Stream
	if ( is_front_page() ) {
		return array(
			array(
				'name' => 'Extra Chill',
				'url'  => $main_site_url,
			),
			array(
				'name' => 'Stream',
				'url'  => '',
			),
		);
	}

	// Other pages: Extra Chill → Stream → Page Title
	if ( is_singular() ) {
		return array(
			array(
				'name' => 'Extra Chill',
				'url'  => $main_site_url,
			),
			array(
				'name' => 'Stream',
				'url'  => home_url( '/' ),
			),
			array(
				'name' => get_the_title(),
				'url'  => '',
			),
		);
	}

	return $items;
}
add_filter( 'extrachill_seo_breadcrumb_items', 'ec_stream_schema_breadcrumb_items' );
