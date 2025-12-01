/**
 * ExtraChill Stream - UI Interactions & Media Capture
 *
 * Handles video/audio capture and streaming interface interactions.
 */

(function() {
	'use strict';

	const StreamUI = {
		state: {
			isStreaming: false,
			stream: null,
			platforms: {
				twitch: false,
				youtube: false,
				facebook: false,
				tiktok: false
			},
			startTime: null,
			durationInterval: null
		},

		elements: {},

		init: function() {
			this.cacheElements();
			this.bindEvents();
			this.setupVideoElement();
			console.log('ExtraChill Stream UI initialized');
		},

		cacheElements: function() {
			this.elements = {
				statusBadge: document.getElementById('ec-stream-status-badge'),
				statusText: document.querySelector('.ec-stream-status-text'),
				videoContainer: document.getElementById('ec-stream-video-container'),
				videoStats: document.getElementById('ec-stream-video-stats'),
				startBtn: document.getElementById('ec-stream-btn-start'),
				stopBtn: document.getElementById('ec-stream-btn-stop'),
				videoSource: document.getElementById('ec-video-source'),
				audioSource: document.getElementById('ec-audio-source'),
				platformConnectBtns: document.querySelectorAll('.ec-stream-platform-connect'),
				artistSelect: document.getElementById('ec-stream-artist-select')
			};
		},

		setupVideoElement: function() {
			// Create video element for preview
			this.videoElement = document.createElement('video');
			this.videoElement.className = 'ec-stream-video-preview';
			this.videoElement.muted = true; // Prevent audio feedback
			this.videoElement.playsInline = true;
			this.videoElement.style.display = 'none';

			this.elements.videoContainer.appendChild(this.videoElement);
		},

		bindEvents: function() {
			const self = this;

			// Video source change
			this.elements.videoSource.addEventListener('change', function() {
				if (self.state.isStreaming) {
					self.restartStream();
				}
			});

			// Audio source change
			this.elements.audioSource.addEventListener('change', function() {
				if (self.state.isStreaming) {
					self.restartStream();
				}
			});

			// Start stream button
			this.elements.startBtn.addEventListener('click', function() {
				self.startStream();
			});

			// Stop stream button
			this.elements.stopBtn.addEventListener('click', function() {
				self.stopStream();
			});

			// Platform connect buttons
			this.elements.platformConnectBtns.forEach(function(btn) {
				btn.addEventListener('click', function() {
					const platform = this.getAttribute('data-platform');
					self.platformConnect(platform, this);
				});
			});

		// Artist select (guard for pages without artist selector)
		if (this.elements.artistSelect) {
			this.elements.artistSelect.addEventListener('change', function() {
				console.log('Artist changed to:', this.value);
			});
		}
		},

		startStream: function() {
			const self = this;
		const videoSource = this.elements.videoSource.value;
		const audioSource = this.elements.audioSource.value;

			console.log('Starting stream with video:', videoSource, 'audio:', audioSource);

			// Get media constraints
			const constraints = this.getMediaConstraints(videoSource, audioSource);

			// Request user media
			navigator.mediaDevices.getUserMedia(constraints)
				.then(function(stream) {
					self.state.stream = stream;
					self.videoElement.srcObject = stream;
					self.videoElement.style.display = 'block';
					self.videoElement.play();

					// Update state
					self.state.isStreaming = true;
					self.state.startTime = Date.now();

					// Update UI
					self.setStatus('live');
					self.elements.startBtn.style.display = 'none';
					self.elements.stopBtn.style.display = 'inline-block';
					document.querySelector('.ec-stream-video-placeholder').style.display = 'none';

					// Start duration counter
					self.startDurationCounter();

					console.log('Stream started - backend integration pending');
				})
				.catch(function(error) {
					console.error('Error accessing media devices:', error);
					alert('Unable to access camera/microphone. Please check permissions and try again.');
					self.setStatus('offline');
				});
		},

		stopStream: function() {
			console.log('Stopping stream...');

			// Stop media tracks
			if (this.state.stream) {
				this.state.stream.getTracks().forEach(track => track.stop());
				this.state.stream = null;
			}

			// Hide video element
			this.videoElement.style.display = 'none';
			this.videoElement.srcObject = null;

			// Update state
			this.state.isStreaming = false;

			// Stop duration counter
			this.stopDurationCounter();

			// Update UI
			this.setStatus('offline');
			this.elements.stopBtn.style.display = 'none';
			this.elements.startBtn.style.display = 'inline-block';
			document.querySelector('.ec-stream-video-placeholder').style.display = 'flex';

			// Reset stats
			document.getElementById('ec-stream-duration').textContent = '00:00:00';
			document.getElementById('ec-stream-status').textContent = 'Ready';
			document.getElementById('ec-stream-viewers').textContent = '0';

			console.log('Stream stopped');
		},

		restartStream: function() {
			this.stopStream();
			setTimeout(() => this.startStream(), 500);
		},

		getMediaConstraints: function(videoSource, audioSource) {
			const constraints = {
				video: false,
				audio: false
			};

			// Video constraints - auto-optimized for best quality
			if (videoSource === 'camera') {
				constraints.video = {
					width: { ideal: 1920, min: 640 },
					height: { ideal: 1080, min: 480 },
					frameRate: { ideal: 30, min: 15 }
				};
			} else if (videoSource === 'screen') {
				constraints.video = {
					mediaSource: 'screen',
					width: { ideal: 1920 },
					height: { ideal: 1080 }
				};
			} else if (videoSource === 'tab') {
				// Browser tab capture (if supported)
				constraints.video = {
					mediaSource: 'tab',
					width: { ideal: 1920 },
					height: { ideal: 1080 }
				};
			}

			// Audio constraints
			if (audioSource === 'microphone') {
				constraints.audio = {
					echoCancellation: true,
					noiseSuppression: true
				};
			} else if (audioSource === 'system') {
				constraints.audio = {
					mediaSource: 'system'
				};
			} else if (audioSource === 'both') {
				constraints.audio = {
					echoCancellation: true,
					noiseSuppression: true
					// System audio would need additional handling
				};
			}

			return constraints;
		},

		setStatus: function(status) {
			// Remove all status classes
			this.elements.statusBadge.classList.remove('ec-stream-status-offline', 'ec-stream-status-connecting', 'ec-stream-status-live');

			// Add new status class
			this.elements.statusBadge.classList.add('ec-stream-status-' + status);

			// Update status text
			const statusText = {
				offline: 'Offline',
				connecting: 'Connecting...',
				live: 'Live'
			};
			this.elements.statusText.textContent = statusText[status] || 'Unknown';
		},

		startDurationCounter: function() {
			const self = this;
			this.state.durationInterval = setInterval(function() {
				const elapsed = Date.now() - self.state.startTime;
				const duration = self.formatDuration(elapsed);
				document.getElementById('ec-stream-duration').textContent = duration;
				document.getElementById('ec-stream-status').textContent = 'Streaming';

				document.getElementById('ec-stream-viewers').textContent = '0';
			}, 1000);
		},

		stopDurationCounter: function() {
			if (this.state.durationInterval) {
				clearInterval(this.state.durationInterval);
				this.state.durationInterval = null;
			}
			document.getElementById('ec-stream-duration').textContent = '00:00:00';
		},

		formatDuration: function(milliseconds) {
			const seconds = Math.floor(milliseconds / 1000);
			const hours = Math.floor(seconds / 3600);
			const minutes = Math.floor((seconds % 3600) / 60);
			const secs = seconds % 60;

			return [hours, minutes, secs]
				.map(val => val < 10 ? '0' + val : val)
				.join(':');
		},

		platformConnect: function(platform, button) {
			console.log('Platform connect clicked:', platform);

			// Show "coming soon" message
			button.disabled = true;
			button.textContent = 'Coming Soon';

			// Reset after 2 seconds
			setTimeout(function() {
				button.disabled = false;
				button.textContent = 'Connect';
			}, 2000);

			// In the future, this will trigger OAuth flow
			console.log('Future: Will open OAuth window for', platform);
		}
	};

	// Initialize on document ready
	document.addEventListener('DOMContentLoaded', function() {
		StreamUI.init();
	});

})();
