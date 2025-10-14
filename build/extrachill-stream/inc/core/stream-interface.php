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
						<i class="fas fa-video" style="font-size: 100px;"></i>
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
									<i class="fab fa-youtube"></i>
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
									<i class="fab fa-facebook"></i>
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
									<i class="fab fa-instagram"></i>
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
									<i class="fab fa-tiktok"></i>
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
									<i class="fab fa-twitch"></i>
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