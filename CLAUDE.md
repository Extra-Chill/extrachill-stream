# ExtraChill Stream

WordPress plugin providing live streaming platform for artist platform members. Currently in Phase 1: Non-functional UI phase with visual interface complete. Backend streaming integrations to be added platform-by-platform in future phases.

## Plugin Information

- **Name**: Extra Chill Stream
- **Version**: 1.0.0
- **Text Domain**: `extrachill-stream`
- **Author**: Chris Huber
- **Author URI**: https://chubes.net
- **License**: GPL v2 or later
- **Network**: false (site-activated on stream.extrachill.com only)
- **Requires at least**: 5.0
- **Tested up to**: 6.4
- **Requires PHP**: 7.4

## Current Status: Phase 1 - Non-Functional UI

**What Works**:
- Plugin activates on stream.extrachill.com
- Artist platform member authentication
- Streaming interface renders with complete UI
- Visual state changes (offline/connecting/live)
- Form controls and interactions
- Platform selection interface
- Responsive design

**What Doesn't Work** (intentionally):
- No actual video capture
- No streaming functionality
- Platform connections are placeholders
- Start/Stop buttons only change UI state
- No backend integrations
- No API calls to streaming platforms

This is intentional - we're building the visual framework first, then adding backend integrations one platform at a time.

## Architecture

### Plugin Loading Pattern
- **Procedural WordPress Pattern**: Uses direct `require_once` includes for all plugin functionality
- **Site-Activated Plugin**: Activated only on stream.extrachill.com site
- **Artist-Only Access**: Requires artist platform membership via `ec_get_user_artist_ids()`
- **Template Override System**: Replaces theme homepage via `extrachill_template_homepage` filter

### Core Features

#### Authentication System (`inc/core/authentication.php`)
- **404 for Non-Members**: Uses `wp_die()` with 404 status for unauthenticated access
- **Artist Validation**: Uses `ec_get_user_artist_ids()` from artist platform plugin
- **Early Hook**: `template_redirect` at priority 5 for immediate authentication check
- **Network-Wide Access**: Any logged-in artist platform member from any multisite site can access

#### Template Override System
- **Homepage Override**: Uses `extrachill_template_homepage` filter to replace theme homepage
- **Domain Detection**: `get_blog_id_from_url('stream.extrachill.com', '/')` for site identification
- **Sticky Header Disabled**: `add_filter('extrachill_enable_sticky_header', '__return_false')`

#### Asset Management (`inc/core/assets.php`)
- **Conditional Loading**: Assets load only on stream site pages
- **Cache Busting**: `filemtime()` versioning for CSS/JS
- **jQuery Dependency**: JavaScript requires jQuery
- **Localized Data**: Artist information, AJAX URL, and nonces passed to JavaScript

## File Structure

```
extrachill-stream/
├── extrachill-stream.php           # Main plugin file
├── inc/
│   ├── core/
│   │   ├── authentication.php      # Member-only access validation
│   │   └── assets.php             # CSS/JS enqueuing
│   └── templates/
│       └── stream-interface.php   # Main streaming interface template
├── assets/
│   ├── css/
│   │   └── stream.css            # Streaming interface styles
│   └── js/
│       └── stream.js             # UI interactions (non-functional)
├── build.sh -> ../../.github/build.sh  # Symlink to universal build script
├── .buildignore                   # Build exclusion patterns
├── composer.json                  # Dev dependencies
└── CLAUDE.md                      # This documentation
```

## Streaming Interface UI

### Header Section
- Page title: "Live Stream Studio"
- Artist name or dropdown (if user has multiple artists)
- Stream status badge (Offline/Connecting/Live)

### Video Preview Section
- 16:9 aspect ratio video container
- Placeholder with "Stream Offline" message
- Stream stats overlay (viewers, duration, bitrate) - shows when "live"

### Stream Setup Section (Left Sidebar)
- Video source dropdown (Camera, Screen Share, Browser Tab)
- Audio source dropdown (Microphone, System Audio, Both)
- Quality settings:
  - Resolution (1080p, 720p, 480p)
  - Frame rate (60fps, 30fps)
  - Bitrate slider (500-6000 kbps)

### Platform Connections Section (Right Sidebar)
Platform cards with connection status:
- **Twitch**: Logo, status, connect button
- **YouTube**: Logo, status, connect button
- **Facebook Live**: Logo, status, connect button
- **TikTok Live**: Logo, status, connect button

Each shows: Connected/Disconnected status, "Connect" button (disabled in Phase 1)

### Stream Controls Section (Bottom)
- Large "Start Stream" button
- "Stop Stream" button (hidden initially)
- Settings button (placeholder)
- Platform selection (which platforms to stream to)

### Info Section
- Getting started tips
- System requirements
- Platform documentation links
- Alpha testing phase notice

## CSS Architecture

Uses ExtraChill theme custom properties:
- Colors from `var(--color-*)`
- Spacing from `var(--spacing-*)`
- Typography from `var(--font-*)`

**Layout**:
- Two-column layout: main content + sidebar
- Responsive (mobile stacks vertically)
- Card-based design
- Status badges with color coding

**Components**:
- Video preview (16:9 aspect ratio)
- Platform connection cards with SVG icons
- Form controls (dropdowns, sliders, checkboxes)
- Stream control buttons
- Status indicators

## JavaScript Architecture

**Module Pattern**: Self-contained IIFE with jQuery

**State Management**:
```javascript
state: {
    isStreaming: false,
    platforms: { twitch: false, youtube: false, ... },
    startTime: null,
    durationInterval: null
}
```

**UI Interactions**:
- Video/audio source selection (visual feedback)
- Quality settings changes
- Bitrate slider with live value display
- Platform checkbox selection
- Start/Stop stream buttons (state changes only)
- Platform connect buttons (show "coming soon")

**Visual States**:
- Offline → Connecting → Live transitions
- Show/hide controls based on state
- Duration counter simulation
- Viewer count simulation (random 1-50)

**No Backend Calls**: All interactions are visual only, no AJAX requests

## Build System

- **Universal Build Script**: Symlinked to `../../.github/build.sh`
- **Auto-Detection**: Script detects plugin from `Plugin Name:` header
- **Production Build**: `./build.sh` creates `/build/extrachill-stream/` directory and `/build/extrachill-stream.zip` file
- **File Exclusions**: `.buildignore` excludes development files
- **Composer Integration**: Development dependencies only

## Dependencies

### Required Plugins
- **extrachill-artist-platform** - Provides `ec_get_user_artist_ids()` for member validation (enforced via WordPress native plugin dependency system)

### Required Theme
- **extrachill** - Provides `extrachill_template_homepage` filter and CSS custom properties

### WordPress Requirements
- WordPress 5.0+ multisite installation
- PHP 7.4+

## Installation & Setup

### 1. Prerequisites
- WordPress multisite network installed
- extrachill-artist-platform plugin active
- extrachill theme active on stream site

### 2. Create Stream Site
- Network Admin → Sites → Add New
- Site URL: stream.extrachill.com
- Site Title: "ExtraChill Live Stream"
- Verify domain resolves correctly

### 3. Activate Plugin
- Visit stream.extrachill.com/wp-admin
- Plugins → Activate "ExtraChill Stream"
- **Note**: Site-activate only (NOT network activate)

### 4. Test Functionality
- Log in as artist platform member
- Visit stream.extrachill.com
- Verify streaming interface renders
- Test UI interactions (buttons, dropdowns, sliders)
- Verify responsive design on mobile
- Test as non-member (should 404)

## Common Development Commands

```bash
# Install dependencies
composer install

# Create production build
./build.sh

# Run PHP linting
composer run lint:php

# Fix PHP coding standards
composer run lint:fix

# Run tests
composer run test
```

## Future Phases

### Phase 2: Platform Integrations

Will add backend functionality one platform at a time:

**Platform Integration Steps** (per platform):
1. Research platform's streaming API requirements
2. Set up developer account and application
3. Implement OAuth flow for authentication
4. Add stream key management
5. Implement RTMP/WebRTC integration
6. Add platform-specific handlers
7. Test end-to-end streaming

**Planned Platforms**:
- Twitch (RTMP)
- YouTube Live (RTMP/HLS)
- Facebook Live (RTMP)
- TikTok Live (RTMP)
- Instagram Live (requires mobile API)

**Technical Requirements**:
- VPS with RTMP server (Nginx RTMP or similar)
- WebRTC for browser-based capture
- Platform OAuth applications
- Stream key encryption/storage
- Bandwidth management
- Quality adaptation

### Phase 3: Advanced Features

After core streaming works:
- Multi-bitrate streaming
- Custom RTMP endpoints
- Stream scheduling
- Chat integration
- Analytics dashboard
- Stream recording/VOD
- Clip creation
- Multi-camera support

## Technical Notes

### Why Non-Functional UI First?

Building the complete UI first provides several advantages:

1. **Visual Framework**: Complete interface to build backend into
2. **User Experience**: Design and iterate on UX without backend complexity
3. **Progressive Enhancement**: Add functionality incrementally
4. **Testing**: Test authentication and basic plugin functionality
5. **Stakeholder Review**: Get feedback on interface before implementing complex backend

### Platform Integration Complexity

Each streaming platform requires:
- Developer application setup
- OAuth 2.0 implementation
- Platform-specific API integration
- Stream key management
- Different RTMP configurations
- Rate limiting handling
- Error handling specific to platform

Building UI first lets us tackle these one at a time.

### RTMP Server Requirements

Future phases will need:
- Dedicated VPS or streaming server
- Nginx with RTMP module (or alternatives like SRS, Node-Media-Server)
- Sufficient bandwidth for multiple simultaneous streams
- Stream transcoding capabilities
- CDN integration for distribution

## Development Standards

### Code Organization
- **Procedural Pattern**: Direct `require_once` includes throughout
- **WordPress Standards**: Full compliance with WordPress coding standards
- **Security First**: Nonces, capability checks, input sanitization, output escaping
- **Error Handling**: Comprehensive error logging and user-friendly messages

### Security Implementation
- **Authentication**: `is_user_logged_in()` check on every request
- **Member Validation**: `ec_get_user_artist_ids()` for artist membership
- **Output Escaping**: `esc_html()`, `esc_attr()`, `esc_url()` throughout
- **Future**: Nonce verification for AJAX calls, stream key encryption

## Troubleshooting

### Streaming Interface Doesn't Render
- Verify plugin is site-activated on stream.extrachill.com
- Check extrachill-artist-platform plugin is active
- Verify user has artist platform membership
- Check template override filter is registered

### 404 Error When Logged In
- Verify user has artist profile via `ec_get_user_artist_ids()`
- Check authentication.php is loaded
- Review template_redirect hook execution

### UI Not Styled Correctly
- Verify extrachill theme is active
- Check assets are enqueuing (view page source)
- Verify theme root.css loads before stream.css
- Check browser console for CSS/JS errors

### JavaScript Not Working
- Check browser console for errors
- Verify jQuery is loaded
- Check ecStreamData is localized correctly
- Verify stream.js file exists and is enqueued

## User Info

- Name: Chris Huber
- Dev website: https://chubes.net
- GitHub: https://github.com/chubes4
- Founder & Editor: https://extrachill.com
- Creator: https://saraichinwag.com
