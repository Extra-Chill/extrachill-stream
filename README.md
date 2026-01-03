# ExtraChill Stream

**Live Streaming Platform for Artist Members**

ExtraChill Stream provides a live streaming platform for artist platform members on stream.extrachill.com. Currently in Phase 1: Non-functional UI phase with complete visual interface ready for backend streaming integrations.

## Current Status: Phase 1 - Non-Functional UI

**What Works**:
- Plugin activates on stream.extrachill.com
- Artist platform member authentication (requires artist membership)
- Streaming interface renders with complete UI
- Visual state changes (offline/connecting/live)
- Form controls and interactions
- Platform selection interface
- Responsive design

**What Doesn't Work** (intentionally):
- No actual video capture or streaming
- No streaming functionality
- Platform connections are placeholders
- Start/Stop buttons only change UI state
- No backend integrations
- No API calls to streaming platforms

This is intentional - we're building the visual framework first, then adding backend integrations one platform at a time.

## Features (Phase 1)

- **Artist-Only Access**: Requires artist platform membership verified with `is_user_member_of_blog()` against artist.extrachill.com (blog ID 4)
- **Homepage Rendering**: Interface renders via `extrachill_homepage_content` action on stream.extrachill.com and disables the sticky header for a focused layout
- **Breadcrumb Integration**: Custom breadcrumb root/trail ensures “Extra Chill → Stream” navigation context
- **Camera Preview UI**: Start/Stop controls trigger local `getUserMedia` preview only (no streaming backend)
- **Platform Cards**: Twitch/YouTube/Facebook/Instagram/TikTok buttons exist as placeholders with Coming Soon messaging
- **Responsive Layout**: Two-column HQ UI driven entirely by theme CSS variables

## Requirements

- WordPress 5.0+ multisite installation
- PHP 7.4+
- [extrachill-artist-platform](https://github.com/Extra-Chill/extrachill-artist-platform) plugin (provides artist membership data)
- [extrachill](https://github.com/Extra-Chill/extrachill) theme (exposes homepage action + breadcrumbs)

## Installation

1. Upload the `extrachill-stream` folder to `/wp-content/plugins/`
2. Activate on stream.extrachill.com site (site-activate, NOT network-activate)
3. Ensure extrachill-artist-platform plugin is active (artist membership required)
4. Verify extrachill theme is active on stream site (action/breadcrumb hooks must exist)

## Architecture

### Current Implementation (Phase 1)

- **Site-Activated Plugin**: Activated only on stream.extrachill.com (blog ID 8)
- **Authentication Gate**: `inc/core/authentication.php` checks `is_user_member_of_blog()` for artist site membership and returns 404 for others
- **Homepage Rendering**: `extrachill_homepage_content` action includes `inc/core/stream-interface.php` inside the theme shell while `extrachill_enable_sticky_header` filter disables the sticky header
- **Breadcrumb Integration**: `inc/core/breadcrumbs.php` customizes the breadcrumb root/trail/back link to match Stream branding
- **Asset Loading**: `inc/core/assets.php` conditionally enqueues CSS/JS on blog ID 8, switches to blog ID 4 to load artist data, and localizes it for vanilla JS
- **REST Placeholder**: `inc/core/rest-api.php` registers a stub `/status` route used only as a future extension point
- **UI State Machine**: `assets/js/stream.js` manages camera preview via `getUserMedia`, Start/Stop buttons, and placeholder platform interactions with no backend calls

### Future Phases

The sections below outline the planned architecture for full streaming support (not implemented yet).

**Unified Compute Infrastructure**:
- **WordPress Server**: User interface, configuration management, billing (Cloudways)
- **Extra Chill VPS**: Python/FastAPI compute infrastructure handling video transcoding, RTMP relay, and multi-platform broadcasting. This offloads resource-heavy tasks similar to the instagram-bot automation.

**Communication Flow**:
```
Artist (OBS) → RTMP stream → Extra Chill VPS → Multiple platforms
                                     ↓
                            WordPress REST API
                               ↓         ↓
                          Start Event  Stop Event
                               ↓         ↓
                       Create Session  Calculate Cost
```

## Development

### Build System

```bash
# Install dependencies
composer install

# Run lint/test suites
composer run lint:php
composer run test

# Create production build (zip only)
./build.sh
```

### Project Structure

```
extrachill-stream/
├── extrachill-stream.php           # Main plugin file
├── inc/
│   └── core/
│       ├── authentication.php      # Artist member validation (404 for non-members)
│       ├── assets.php              # CSS/JS enqueuing + localization
│       ├── rest-api.php            # Placeholder REST route(s)
│       ├── breadcrumbs.php         # Breadcrumb overrides
│       └── stream-interface.php    # Main streaming interface template
├── assets/
│   ├── css/
│   │   └── stream.css              # Streaming interface styles (Phase 1 UI)
│   └── js/
│       └── stream.js               # Vanilla JS UI + local preview
├── docs/
│   └── CHANGELOG.md
├── plan.md                         # Future implementation planning
├── build.sh -> ../../.github/build.sh  # Universal build script
├── .buildignore                    # Build exclusion patterns
├── composer.json                   # Development dependencies
└── README.md                       # This file
```

## Future Features (Phase 2+)

### Planned Platform Support
- YouTube Live
- Twitch
- Facebook Live
- Instagram Live
- TikTok Live
- Custom RTMP destinations

### Planned Billing System
- **Pricing**: $0.10 per minute
- **Volume Discounts**: Automatic discounts for high-usage accounts
- **Wallet System**: Pre-paid credits with WooCommerce integration
- **Network-Wide**: Balance accessible across all Extra Chill sites

## Security (Phase 1)

- **Member Validation**: `is_user_member_of_blog()` against artist site (blog ID 4) for artist membership verification
- **WordPress Authentication**: `is_user_logged_in()` check on all requests
- **Output Escaping**: `esc_html()`, `esc_attr()`, `esc_url()` throughout templates
- **Future Security**: RTMP key authentication, nonce verification, prepared statements

## Development

### Phase 1 Focus
The current development phase focuses on building a complete, professional streaming UI that can be incrementally enhanced with backend functionality. This approach allows for:

- **UI/UX Iteration**: Refine the user experience without backend complexity
- **Progressive Enhancement**: Add streaming functionality one platform at a time
- **Stakeholder Review**: Get feedback on interface before implementing complex integrations
- **Framework Ready**: Complete foundation for future RTMP and platform integrations

### Commands

```bash
# Install dependencies
composer install

# Create production build
./build.sh

# Run PHP linting
composer run lint:php

# Fix coding standards
composer run lint:fix

# Run tests
composer run test
```

### Build Output
- `/build/extrachill-stream.zip` - Deployment package (ZIP file only)

Note: The intermediate `/build/extrachill-stream/` directory is temporary and removed during the build.

## Future Phases

### Phase 2: Platform Integrations
Add backend functionality one platform at a time:
1. Research platform streaming APIs and requirements
2. Implement OAuth flows for authentication
3. Add RTMP/WebRTC video capture
4. Integrate platform-specific APIs
5. Test end-to-end streaming workflows

### Phase 3: Advanced Features
- Multi-bitrate streaming
- Stream scheduling and management
- Analytics dashboard
- Chat integration
- Stream recording/VOD
- Mobile streaming support

## Contributing

1. Follow WordPress Coding Standards (WPCS)
2. Use direct `require_once` includes (no PSR-4 autoloading)
3. Add comprehensive documentation
4. Test thoroughly before submitting PRs

## License

GPL v2 or later

## Documentation

- **AGENTS.md** - Comprehensive technical documentation for developers
- **plan.md** - Detailed future implementation roadmap

## Support

For support, please contact the Extra Chill development team or create an issue in this repository.

---

**Extra Chill Platform** - Empowering Music Communities</content>
<parameter name="filePath">/Users/chubes/Developer/Extra Chill Platform/extrachill-plugins/extrachill-stream/README.md