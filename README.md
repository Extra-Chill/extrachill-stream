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

- **Artist-Only Access**: Requires artist platform membership via `ec_get_user_artist_ids()`
- **Complete UI Framework**: Professional streaming interface ready for backend implementation
- **Platform Selection**: UI for selecting multiple streaming destinations
- **Quality Settings**: Resolution, frame rate, and bitrate controls
- **Video Preview**: 16:9 aspect ratio container with status overlays
- **Stream Controls**: Start/stop buttons with state management
- **Responsive Design**: Mobile-friendly interface

## Requirements

- WordPress 5.0+ multisite installation
- PHP 7.4+
- [extrachill-artist-platform](https://github.com/Extra-Chill/extrachill-artist-platform) plugin (provides member validation)
- [extrachill](https://github.com/Extra-Chill/extrachill) theme

## Installation

1. Upload the `extrachill-stream` folder to `/wp-content/plugins/`
2. Activate on stream.extrachill.com site (site-activate, NOT network-activate)
3. Ensure extrachill-artist-platform plugin is active
4. Verify extrachill theme is active on stream site

## Architecture

### Current Implementation (Phase 1)

- **Site-Activated Plugin**: Activated only on stream.extrachill.com
- **Artist Member Validation**: Uses `ec_get_user_artist_ids()` for access control
- **Template Override**: Replaces theme homepage via `extrachill_template_homepage` filter
- **Non-Functional UI**: Complete visual interface with placeholder interactions
- **State Management**: JavaScript handles UI state changes without backend calls

### Future Architecture (Phase 2+)

**Two-Server Infrastructure**:
- **WordPress Server**: User interface, configuration management, billing
- **nginx-rtmp VPS**: Video relay server handling multi-platform broadcasting

**Communication Flow**:
```
Artist (OBS) → RTMP stream → nginx-rtmp VPS → Multiple platforms
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

# Run tests
composer test

# Create production build
./build.sh
```

### Project Structure

```
extrachill-stream/
├── extrachill-stream.php           # Main plugin file
├── inc/
│   ├── core/
│   │   ├── authentication.php      # Artist member validation (404 for non-members)
│   │   └── assets.php              # CSS/JS enqueuing with conditional loading
│   └── templates/
│       └── stream-interface.php    # Main streaming interface template
├── assets/
│   ├── css/
│   │   └── stream.css              # Streaming interface styles (Phase 1 UI)
│   └── js/
│       └── stream.js               # UI interactions (non-functional, visual only)
├── build.sh -> ../../.github/build.sh  # Universal build script
├── .buildignore                    # Build exclusion patterns
├── composer.json                   # Development dependencies
├── CLAUDE.md                       # Comprehensive technical documentation
├── plan.md                         # Future implementation planning
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

- **Member Validation**: `ec_get_user_artist_ids()` for artist membership verification
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
- `/build/extrachill-stream/` - Clean production directory
- `/build/extrachill-stream.zip` - Deployment package

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

- **CLAUDE.md** - Comprehensive technical documentation for developers
- **plan.md** - Detailed future implementation roadmap

## Support

For support, please contact the Extra Chill development team or create an issue in this repository.

---

**Extra Chill Platform** - Empowering Music Communities</content>
<parameter name="filePath">/Users/chubes/Developer/Extra Chill Platform/extrachill-plugins/extrachill-stream/README.md