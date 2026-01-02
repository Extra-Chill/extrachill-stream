# ExtraChill Stream

WordPress plugin providing live streaming platform for artist platform members. Currently in Phase 1: Non-functional UI phase with visual interface complete. Backend streaming integrations to be added platform-by-platform in future phases.

This plugin is part of the Extra Chill Platform, a WordPress multisite network serving music communities across 10 active sites.

## Plugin Information

- **Name**: Extra Chill Stream
- **Version**: 0.1.1
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
- **Artist-Only Access**: Requires artist platform membership via `is_user_member_of_blog()` against blog ID 4 (artist.extrachill.com)
- **Homepage Rendering**: Outputs UI via `extrachill_homepage_content` action hook and disables sticky header with `add_filter( 'extrachill_enable_sticky_header', '__return_false' )`

### Core Features

#### Authentication System (`inc/core/authentication.php`)
- **404 for Non-Members**: Uses `wp_die()` with 404 status for unauthenticated access
- **Artist Validation**: Uses WordPress core `is_user_member_of_blog()` against artist site (blog ID 4)
- **Early Hook**: `template_redirect` at priority 5 for immediate authentication check
- **Network-Wide Access**: Any logged-in artist platform member from any multisite site can access

#### Homepage Rendering
- **Homepage Injection**: Uses `extrachill_homepage_content` action to output UI inside the theme layout
- **Sticky Header Disabled**: Filter `extrachill_enable_sticky_header` returns false for a distraction-free layout
- **Breadcrumbs Integration**: `inc/core/breadcrumbs.php` customizes theme breadcrumbs to show “Extra Chill → Stream”

#### Asset Management (`inc/core/assets.php`)
- **Conditional Loading**: Assets load only on stream site pages (blog ID 8)
- **Cache Busting**: `filemtime()` versioning for CSS/JS
- **Vanilla JavaScript**: Single `stream.js` module with no jQuery dependency
- **Localized Data**: Artist list pulled via `switch_to_blog( 4 )` and passed to JS via `wp_localize_script()`
- **Camera Preview Support**: JavaScript receives artist context plus REST base URL/nonce for future integrations

#### REST API Placeholder (`inc/core/rest-api.php`)
- **Status Endpoint**: Registered as `extrachill/v1/stream/status` (via extrachill-api) returning canned response
- **Permissions**: Uses `is_user_member_of_blog()` to keep the endpoint limited to artist members
- **Future Expansion**: File exists to extend once backend streaming/billing APIs are ready

## File Structure

```
extrachill-stream/
├── extrachill-stream.php           # Main plugin file
├── inc/
│   └── core/
│       ├── authentication.php      # Member-only access validation
│       ├── assets.php              # CSS/JS enqueuing + localization
│       ├── rest-api.php            # Placeholder REST route(s)
│       ├── breadcrumbs.php         # Breadcrumb overrides
│       └── stream-interface.php    # Main streaming interface template
├── assets/
│   ├── css/
│   │   └── stream.css             # Streaming interface styles
│   └── js/
│       └── stream.js              # Vanilla JS UI + media preview
├── docs/
│   └── CHANGELOG.md
├── plan.md
├── build.sh -> ../../.github/build.sh  # Symlink to universal build script
├── .buildignore                   # Build exclusion patterns
├── composer.json                  # Dev dependencies
└── AGENTS.md                      # This documentation
```

## Streaming Interface UI & JavaScript

### Header Section
- Page title: "Live Stream Studio"
- Artist name or dropdown (if user has multiple artists)
- Stream status badge (Offline/Connecting/Live)

### Video Preview Section
- 16:9 aspect ratio video container
- Placeholder with "Stream Offline" message until camera preview starts
- Local-only camera/screen preview powered by `navigator.mediaDevices.getUserMedia`
- Stream stats overlay (viewers, duration, status) driven entirely by UI state

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
- **Instagram Live**: Logo, status, connect button

Each card toggles between placeholder Connected/Disconnected labels but never performs OAuth or saved state writes in Phase 1.

### Stream Controls Section (Bottom)
- Large "Start Stream" button
- "Stop Stream" button (hidden initially)
- Settings button (placeholder)
- Platform selection (which platforms to stream to)
- Duration/Status counters that update purely on the client

### Info Section
- Getting started tips
- System requirements
- Platform documentation links
- Alpha testing phase notice (UI-only state)

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

**Theme Alignment**:
- Relies entirely on theme-provided CSS variables/root styles
- No custom template directories; template renders inside default theme homepage shell
- Styles focus on layout/spacing only, no bespoke animations beyond simple state changes

## JavaScript Architecture

**Module Pattern**: Self-contained vanilla JS module initialized on `DOMContentLoaded`

**State Management**:
```javascript
state: {
    isStreaming: false,
    stream: null,
    platforms: { twitch: false, youtube: false, facebook: false, tiktok: false },
    startTime: null,
    durationInterval: null
}
```

**UI Interactions**:
- Video/audio source selection triggers local preview restart when already “live”
- Start/Stop stream buttons request camera/microphone access or tear down preview
- Platform connect buttons show placeholder “Coming Soon” feedback
- Artist selector (when present) only logs selection for future integration

**Visual States**:
- Offline → Connecting → Live transitions entirely in CSS/JS state
- Duration counter and viewer numbers update locally (no randomization logic left)
- Status badge + stats modules toggle purely via DOM manipulation

**Media Handling**:
- Uses `navigator.mediaDevices.getUserMedia()` for camera/screen capture preview
- Stops all media tracks when user hits Stop to release devices

**No Backend Calls**: All interactions are visual only, and REST endpoints are placeholders for future work

## Build System

- **Universal Build Script**: Symlinked to `../../.github/build.sh`
- **Auto-Detection**: Script detects plugin from `Plugin Name:` header
- **Production Build**: `./build.sh` creates `/build/extrachill-stream.zip` file only.
- **File Exclusions**: `.buildignore` excludes development files
- **Composer Integration**: Development dependencies only

## Dependencies

### Required Plugins
- **extrachill-artist-platform** - Provides artist membership data stored against blog ID 4

### Required Theme
- **extrachill** - Provides `extrachill_homepage_content` action, breadcrumb filters, and CSS custom properties

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
- **Member Validation**: `is_user_member_of_blog()` against artist site (blog ID 4) for artist membership
- **Output Escaping**: `esc_html()`, `esc_attr()`, `esc_url()` throughout
- **Future**: Nonce verification for AJAX calls, stream key encryption

## Troubleshooting

### Streaming Interface Doesn't Render
- Verify plugin is site-activated on stream.extrachill.com
- Check extrachill-artist-platform plugin is active
- Verify user has artist platform membership
- Confirm `extrachill_homepage_content` action runs on theme homepage

### 404 Error When Logged In
- Verify user has artist platform membership via `is_user_member_of_blog()` against blog ID 4
- Check authentication.php is loaded
- Review template_redirect hook execution

### UI Not Styled Correctly
- Verify extrachill theme is active
- Check assets are enqueuing (view page source)
- Verify theme root.css loads before stream.css
- Check browser console for CSS/JS errors

### JavaScript Not Working
- Check browser console for errors
- Verify vanilla JavaScript module loaded correctly
- Check ecStreamData is localized correctly
- Verify stream.js file exists and is enqueued

## User Info

- Name: Chris Huber
- Dev website: https://chubes.net
- GitHub: https://github.com/chubes4
- Founder & Editor: https://extrachill.com
- Creator: https://saraichinwag.com
