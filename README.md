# ExtraChill Stream

**Multi-Platform Live Streaming Plugin for WordPress**

ExtraChill Stream enables artists on the Extra Chill Platform to broadcast live video streams simultaneously to multiple platforms (YouTube, Twitch, Facebook, Instagram, TikTok, etc.) through a single RTMP endpoint.

## Features

- **Multi-Platform Broadcasting**: Stream to unlimited destinations simultaneously
- **Artist Platform Integration**: Seamless integration with existing artist profiles and authentication
- **Metered Billing**: Fair pay-per-minute pricing with wallet system
- **Zero Storage**: Pure video relay with no data persistence
- **Bootstrapped Infrastructure**: No external streaming services, full control

## Supported Platforms

- YouTube Live
- Twitch
- Facebook Live
- Instagram Live
- TikTok Live
- X/Twitter (Periscope)
- LinkedIn Live
- Custom RTMP destinations

## Requirements

- WordPress 5.0+ multisite installation
- PHP 7.4+
- MySQL 5.7+ or MariaDB 10.3+
- nginx-rtmp VPS (DigitalOcean, Linode, or similar)

## Installation

1. Upload the `extrachill-stream` folder to `/wp-content/plugins/`
2. Network activate the plugin
3. Configure VPS connection in admin settings
4. Set up nginx-rtmp on your streaming VPS
5. Configure platform destinations in user dashboards

## Architecture

### Two-Server Infrastructure

- **WordPress Server**: User interface, configuration management, billing
- **nginx-rtmp VPS**: Video relay server handling multi-platform broadcasting

### Communication Flow

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
│   │   ├── stream-interface.php    # Main streaming UI template
│   │   ├── http-client.php         # Standardized HTTP client
│   │   ├── assets.php              # CSS/JS enqueuing
│   │   └── authentication.php      # Multisite authentication
│   ├── providers/                  # Platform-specific implementations
│   └── [future modules]
├── assets/
│   ├── css/
│   │   └── stream.css              # Main streaming interface styles
│   └── js/
│       └── stream.js               # Video/audio capture and UI
├── build.sh -> ../../.github/build.sh  # Universal build script
├── composer.json                   # Development dependencies
├── CLAUDE.md                       # Implementation documentation
├── plan.md                         # Comprehensive planning document
└── README.md                       # This file
```

## Billing System

- **Pricing**: $0.10 per minute
- **Volume Discounts**: Automatic discounts for high-usage accounts
- **Wallet System**: Pre-paid credits with WooCommerce integration
- **Network-Wide**: Balance accessible across all Extra Chill sites

## Security

- RTMP key authentication
- Nonce verification on all requests
- Capability checks for admin functions
- Prepared statements for database queries
- Input sanitization throughout

## Contributing

1. Follow WordPress Coding Standards (WPCS)
2. Use direct `require_once` includes (no PSR-4 autoloading)
3. Add comprehensive documentation
4. Test thoroughly before submitting PRs

## License

Proprietary - Extra Chill Platform

## Support

For support, please contact the Extra Chill development team or create an issue in this repository.

---

**Extra Chill Platform** - Empowering Music Communities</content>
<parameter name="filePath">/Users/chubes/Developer/Extra Chill Platform/extrachill-plugins/extrachill-stream/README.md