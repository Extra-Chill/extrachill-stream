<?php
/**
 * HTTP Client for Streaming Providers
 *
 * Centralized HTTP handling for all streaming platform integrations.
 * Provides standardized request methods, authentication, and error handling.
 *
 * @package ExtraChillStream
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Extra Chill Stream HTTP Client
 *
 * Handles HTTP requests to streaming provider APIs with standardized
 * authentication, error handling, and logging.
 */
class ExtraChill_Stream_HTTP_Client {

	/**
	 * Default request timeout in seconds.
	 */
	const DEFAULT_TIMEOUT = 30;

	/**
	 * User agent string for requests.
	 */
	const USER_AGENT = 'ExtraChill-Stream/1.0.0';

	/**
	 * Make an HTTP request.
	 *
	 * @param string $method HTTP method (GET, POST, etc.).
	 * @param string $url    Request URL.
	 * @param array  $args   Request arguments.
	 * @return array|WP_Error Response data or error.
	 */
	public function request( $method, $url, $args = array() ) {
		$defaults = array(
			'method'  => strtoupper( $method ),
			'timeout' => self::DEFAULT_TIMEOUT,
			'headers' => array(
				'User-Agent' => self::USER_AGENT,
			),
		);

		$args = wp_parse_args( $args, $defaults );

		// Add security headers if not present.
		if ( ! isset( $args['headers']['X-Requested-With'] ) ) {
			$args['headers']['X-Requested-With'] = 'XMLHttpRequest';
		}

		$response = wp_remote_request( $url, $args );

		if ( is_wp_error( $response ) ) {
			$this->log_error( 'HTTP Request Failed', array(
				'method' => $method,
				'url'    => $url,
				'error'  => $response->get_error_message(),
			) );
			return $response;
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		$body        = wp_remote_retrieve_body( $response );

		// Log non-2xx responses.
		if ( $status_code < 200 || $status_code >= 300 ) {
			$this->log_error( 'HTTP Request Error', array(
				'method'      => $method,
				'url'         => $url,
				'status_code' => $status_code,
				'body'        => $body,
			) );
		}

		return array(
			'code' => $status_code,
			'body' => $body,
			'headers' => wp_remote_retrieve_headers( $response ),
		);
	}

	/**
	 * Make a GET request.
	 *
	 * @param string $url  Request URL.
	 * @param array  $args Request arguments.
	 * @return array|WP_Error Response data or error.
	 */
	public function get( $url, $args = array() ) {
		return $this->request( 'GET', $url, $args );
	}

	/**
	 * Make a POST request.
	 *
	 * @param string $url  Request URL.
	 * @param array  $args Request arguments.
	 * @return array|WP_Error Response data or error.
	 */
	public function post( $url, $args = array() ) {
		return $this->request( 'POST', $url, $args );
	}

	/**
	 * Make an authenticated request with Bearer token.
	 *
	 * @param string $method HTTP method.
	 * @param string $url    Request URL.
	 * @param string $token  Bearer token.
	 * @param array  $args   Additional request arguments.
	 * @return array|WP_Error Response data or error.
	 */
	public function request_with_bearer( $method, $url, $token, $args = array() ) {
		$args['headers']['Authorization'] = 'Bearer ' . $token;
		return $this->request( $method, $url, $args );
	}

	/**
	 * Make an authenticated request with OAuth token.
	 *
	 * @param string $method HTTP method.
	 * @param string $url    Request URL.
	 * @param string $token  OAuth token.
	 * @param array  $args   Additional request arguments.
	 * @return array|WP_Error Response data or error.
	 */
	public function request_with_oauth( $method, $url, $token, $args = array() ) {
		$args['headers']['Authorization'] = 'OAuth ' . $token;
		return $this->request( $method, $url, $args );
	}

	/**
	 * Log an error.
	 *
	 * @param string $message Error message.
	 * @param array  $data    Additional error data.
	 */
	private function log_error( $message, $data = array() ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( '[ExtraChill Stream HTTP] ' . $message . ': ' . wp_json_encode( $data ) );
		}
	}
}

if ( ! function_exists( 'extrachill_stream_http_client' ) ) {
	/**
	 * Get the HTTP client instance.
	 *
	 * @return ExtraChill_Stream_HTTP_Client
	 */
	function extrachill_stream_http_client() {
		static $instance = null;
		if ( null === $instance ) {
			$instance = new ExtraChill_Stream_HTTP_Client();
		}
		return $instance;
	}
}