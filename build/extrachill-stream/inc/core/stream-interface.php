<?php
/**
 * Streaming Interface Template
 *
 * Main streaming interface for artist platform members.
 * Non-functional UI - backend integrations to be added later.
 *
 * @package ExtraChillStream
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$user_id = get_current_user_id();
$artist_ids = function_exists( 'ec_get_user_artist_ids' ) ? ec_get_user_artist_ids( $user_id ) : array();
$current_artist_name = ! empty( $artist_ids ) ? get_the_title( $artist_ids[0] ) : 'Artist';
?>

<div class="ec-stream-container">
	<div class="ec-stream-header">
		<div class="ec-stream-header-left">
			<h1 class="ec-stream-title">Live Stream Studio</h1>
			<?php if ( count( $artist_ids ) > 1 ) : ?>
				<select class="ec-stream-artist-select" id="ec-stream-artist-select">
					<?php foreach ( $artist_ids as $artist_id ) : ?>
						<option value="<?php echo esc_attr( $artist_id ); ?>">
							<?php echo esc_html( get_the_title( $artist_id ) ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			<?php else : ?>
				<p class="ec-stream-artist-name"><?php echo esc_html( $current_artist_name ); ?></p>
			<?php endif; ?>
		</div>
		<div class="ec-stream-header-right">
			<span class="ec-stream-status-badge ec-stream-status-offline" id="ec-stream-status-badge">
				<span class="ec-stream-status-dot"></span>
				<span class="ec-stream-status-text">Offline</span>
			</span>
		</div>
	</div>

	<div class="ec-stream-layout">
		<div class="ec-stream-sidebar ec-stream-sidebar-left">
			<div class="ec-stream-card">
				<h2 class="ec-stream-card-title">Stream Setup</h2>

				<div class="ec-stream-form-group">
					<label for="ec-video-source">Video Source</label>
					<select id="ec-video-source" class="ec-stream-select">
						<option value="camera">Camera</option>
						<option value="screen">Screen Share</option>
						<option value="tab">Browser Tab</option>
					</select>
				</div>

				<div class="ec-stream-form-group">
					<label for="ec-audio-source">Audio Source</label>
					<select id="ec-audio-source" class="ec-stream-select">
						<option value="microphone">Microphone</option>
						<option value="system">System Audio</option>
						<option value="both">Both</option>
					</select>
				</div>

				<p class="ec-stream-info-note">
					<strong>Note:</strong> Quality settings are automatically optimized for the best possible stream based on your device and connection.
				</p>
			</div>
		</div>

		<div class="ec-stream-main">
			<div class="ec-stream-video-container" id="ec-stream-video-container">
				<div class="ec-stream-video-placeholder">
					<div class="ec-stream-video-icon">
						<svg width="100" height="100" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<path d="M23 7l-7 5 7 5V7z"></path>
							<rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect>
						</svg>
					</div>
					<p class="ec-stream-video-status">Stream Offline</p>
					<p class="ec-stream-video-hint">Configure your settings and select platforms to begin</p>
				</div>
			</div>

			<div class="ec-stream-controls">
				<div class="ec-stream-controls-left">
					<button
						type="button"
						class="button-1 button-medium ec-stream-btn-start"
						id="ec-stream-btn-start"
					>
						Start Stream
					</button>
					<button
						type="button"
						class="button-danger button-medium ec-stream-btn-stop"
						id="ec-stream-btn-stop"
						style="display: none;"
					>
						Stop Stream
					</button>
				</div>
				<div class="ec-stream-controls-right">
					<div class="ec-stream-video-stats" id="ec-stream-video-stats">
						<div class="ec-stream-stat">
							<span class="ec-stream-stat-label">Duration:</span>
							<span class="ec-stream-stat-value" id="ec-stream-duration">00:00:00</span>
						</div>
						<div class="ec-stream-stat">
							<span class="ec-stream-stat-label">Status:</span>
							<span class="ec-stream-stat-value" id="ec-stream-status">Ready</span>
						</div>
						<div class="ec-stream-stat">
							<span class="ec-stream-stat-label">Viewers:</span>
							<span class="ec-stream-stat-value" id="ec-stream-viewers">0</span>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="ec-stream-sidebar ec-stream-sidebar-right">
			<div class="ec-stream-card">
				<h2 class="ec-stream-card-title">Streaming Platforms</h2>
				<p class="ec-stream-card-subtitle">Connect your accounts to stream</p>

				<div class="ec-stream-platform-cards">
					<div class="ec-stream-platform-card" data-platform="youtube">
						<div class="ec-stream-platform-header">
							<div class="ec-stream-platform-info">
								<div class="ec-stream-platform-logo">
									<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
										<path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
									</svg>
								</div>
								<div class="ec-stream-platform-name">YouTube</div>
							</div>
							<div class="ec-stream-platform-status ec-stream-platform-status-disconnected">
								Disconnected
							</div>
						</div>
						<button type="button" class="button-2 button-medium ec-stream-btn-block ec-stream-platform-connect" data-platform="youtube">
							Connect
						</button>
					</div>

					<div class="ec-stream-platform-card" data-platform="facebook">
						<div class="ec-stream-platform-header">
							<div class="ec-stream-platform-info">
								<div class="ec-stream-platform-logo">
									<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
										<path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
									</svg>
								</div>
								<div class="ec-stream-platform-name">Facebook Live</div>
							</div>
							<div class="ec-stream-platform-status ec-stream-platform-status-disconnected">
								Disconnected
							</div>
						</div>
						<button type="button" class="button-2 button-medium ec-stream-btn-block ec-stream-platform-connect" data-platform="facebook">
							Connect
						</button>
					</div>

					<div class="ec-stream-platform-card" data-platform="instagram">
						<div class="ec-stream-platform-header">
							<div class="ec-stream-platform-info">
								<div class="ec-stream-platform-logo">
									<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
										<path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
									</svg>
								</div>
								<div class="ec-stream-platform-name">Instagram Live</div>
							</div>
							<div class="ec-stream-platform-status ec-stream-platform-status-disconnected">
								Disconnected
							</div>
						</div>
						<button type="button" class="button-2 button-medium ec-stream-btn-block ec-stream-platform-connect" data-platform="instagram">
							Connect
						</button>
					</div>

					<div class="ec-stream-platform-card" data-platform="tiktok">
						<div class="ec-stream-platform-header">
							<div class="ec-stream-platform-info">
								<div class="ec-stream-platform-logo">
									<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
										<path d="M12.525.02c1.31-.02 2.61-.01 3.91 0 1.58.16 2.84.73 3.79 1.68 1.45 1.45 2.49 3.84 2.63 6.39L23 8.17c-.06 1.48-.08 2.96-.07 4.44-.01 1.48.01 2.96.07 4.44l-.26.09c-.14 2.55-1.18 4.94-2.63 6.39-1.45 1.45-3.84 2.49-6.39 2.63l-.09.26c-1.48.06-2.96.08-4.44.07-1.48.01-2.96-.01-4.44-.07l-.09-.26c-2.55-.14-4.94-1.18-6.39-2.63C.73 19.14.16 17.88 0 16.3c-.02-1.3-.01-2.6 0-3.9.16-1.58.73-2.84 1.68-3.79C3.13 7.16 5.52 6.12 8.07 5.98l.09-.26c1.48-.06 2.96-.08 4.44-.07 1.48-.01 2.96.01 4.44.07l.26.09c.14-2.55 1.18-4.94 2.63-6.39 1.45-1.45 3.84-2.49 6.39-2.63zM10.23 6.74c-.74 0-1.46.1-2.15.28-.69.18-1.31.45-1.85.79-.54.34-.97.76-1.28 1.26-.31.5-.47 1.05-.47 1.64 0 .87.27 1.61.81 2.21.54.6 1.26.98 2.15 1.15.89.17 1.85.19 2.87.06v1.78c-1.26.14-2.41.03-3.46-.33-.29-.09-.56-.22-.81-.39-.25-.17-.46-.37-.63-.6-.17-.23-.3-.49-.39-.78-.09-.29-.14-.6-.14-.93h-1.97v7.52c.05 1.04.22 1.91.52 2.61.3.7.74 1.26 1.31 1.69.57.43 1.26.73 2.08.89.82.16 1.75.2 2.8.11 1.05-.09 2.03-.31 2.94-.65 1.04-.39 1.92-.98 2.64-1.78.72-.8 1.21-1.78 1.47-2.94.26-1.16.34-2.41.24-3.75V8.68h1.97v-1.8c0-.58-.16-1.13-.47-1.64-.31-.5-.74-.92-1.28-1.26-.54-.34-1.16-.61-1.85-.79-.69-.18-1.41-.28-2.15-.28-.74 0-1.46-.1-2.15-.28-.69-.18-1.31-.45-1.85-.79-.54-.34-.97-.76-1.28-1.26-.31-.5-.47-1.05-.47-1.64V.02c.58 0 1.13.16 1.64.47.5.31.92.74 1.26 1.28.34.54.61 1.16.79 1.85.18.69.28 1.41.28 2.15s.1 1.46.28 2.15c.18.69.45 1.31.79 1.85.34.54.76.97 1.26 1.28.5.31 1.05.47 1.64.47z"/>
									</svg>
								</div>
								<div class="ec-stream-platform-name">TikTok Live</div>
							</div>
							<div class="ec-stream-platform-status ec-stream-platform-status-disconnected">
								Disconnected
							</div>
						</div>
						<button type="button" class="button-2 button-medium ec-stream-btn-block ec-stream-platform-connect" data-platform="tiktok">
							Connect
						</button>
					</div>

					<div class="ec-stream-platform-card" data-platform="twitch">
						<div class="ec-stream-platform-header">
							<div class="ec-stream-platform-info">
								<div class="ec-stream-platform-logo">
									<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
										<path d="M11.571 4.714h1.715v5.143H11.57zm4.715 0H18v5.143h-1.714zM6 0L1.714 4.286v15.428h5.143V24l4.286-4.286h3.428L22.286 12V0zm14.571 11.143l-3.428 3.428h-3.429l-3 3v-3H6.857V1.714h13.714Z"/>
									</svg>
								</div>
								<div class="ec-stream-platform-name">Twitch</div>
							</div>
							<div class="ec-stream-platform-status ec-stream-platform-status-disconnected">
								Disconnected
							</div>
						</div>
						<button type="button" class="button-2 button-medium ec-stream-btn-block ec-stream-platform-connect" data-platform="twitch">
							Connect
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<?php get_footer(); ?>