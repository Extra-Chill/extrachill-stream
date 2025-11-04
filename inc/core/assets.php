<?php
/**
 * Asset Management
 *
 * Handles CSS and JavaScript enqueuing for streaming interface.
 *
 * @package ExtraChillStream
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_enqueue_scripts', 'ec_stream_enqueue_assets' );

/**
 * Enqueue streaming interface assets
 */
function ec_stream_enqueue_assets() {
	// Only load on stream site
	if ( get_current_blog_id() !== 8 ) {
		return;
	}

	// Enqueue CSS
	$css_file = EXTRACHILL_STREAM_PLUGIN_DIR . 'assets/css/stream.css';
	if ( file_exists( $css_file ) ) {
		wp_enqueue_style(
			'extrachill-stream',
			EXTRACHILL_STREAM_PLUGIN_URL . 'assets/css/stream.css',
			array(),
			filemtime( $css_file )
		);
	}

	// Enqueue JavaScript
	$js_file = EXTRACHILL_STREAM_PLUGIN_DIR . 'assets/js/stream.js';
	if ( file_exists( $js_file ) ) {
		wp_enqueue_script(
			'extrachill-stream',
			EXTRACHILL_STREAM_PLUGIN_URL . 'assets/js/stream.js',
			array( 'jquery' ),
			filemtime( $js_file ),
			true
		);

		// Localize script with user data
		$user_id = get_current_user_id();

		// Get artist site blog ID
		$artists = array();

		// Switch to artist site to get artist data
		switch_to_blog( 4 );

			// Get user's artist profile IDs from global user meta
			$artist_profile_ids = get_user_meta( $user_id, '_artist_profile_ids', true );

			if ( is_array( $artist_profile_ids ) && ! empty( $artist_profile_ids ) ) {
				foreach ( $artist_profile_ids as $artist_id ) {
					$artist_id_int = absint( $artist_id );
					if ( $artist_id_int > 0 && get_post_status( $artist_id_int ) === 'publish' ) {
						$artists[] = array(
							'id'   => $artist_id_int,
							'name' => get_the_title( $artist_id_int ),
						);
					}
				}
			}

			restore_current_blog();
		}

		wp_localize_script(
			'extrachill-stream',
			'ecStreamData',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'ec_stream_nonce' ),
				'userId'  => $user_id,
				'artists' => $artists,
			)
		);
	}
}
