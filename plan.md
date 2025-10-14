# ExtraChill Stream - Multi-Platform Live Streaming Plugin

**Status**: Planning Phase - Comprehensive Implementation Plan
**Current State**: Phase 1 Complete (Non-functional UI only)
**Target Site**: stream.extrachill.com (site #8 in multisite network)
**Architecture**: WordPress UI + nginx-rtmp VPS relay + HTTP client standardization
**Monetization**: Metered billing (pay-per-minute streaming via shop.extrachill.com WooCommerce)

**IMPORTANT**: This document describes the planned full implementation. The plugin currently has only a non-functional UI (Phase 1). All backend integrations, streaming functionality, and billing systems described below are not yet implemented.

## Project Overview

ExtraChill Stream enables artists on the Extra Chill Platform to broadcast live video streams simultaneously to multiple platforms (YouTube, Twitch, Facebook, Instagram, etc.) through a single RTMP endpoint. WordPress provides the user interface, configuration management, and metered billing system, while a separate VPS running nginx-rtmp-module handles the actual video relay.

### Core Value Proposition

- **Multi-Platform Broadcasting**: Stream to unlimited destinations simultaneously
- **Artist Platform Integration**: Seamless integration with existing artist profiles and authentication
- **Metered Billing**: Fair pay-per-minute pricing with wallet system
- **Zero Storage**: Pure video relay with no data persistence (event documentation only)
- **Bootstrapped Infrastructure**: No external streaming services, full control

## Technical Architecture

### Two-Server Infrastructure

#### Server 1: WordPress (Cloudways - Existing)
**Responsibilities**:
- User interface and dashboard
- Stream configuration management
- RTMP stream key generation
- Destination platform settings (YouTube keys, Twitch keys, etc.)
- nginx-rtmp configuration file generation
- Metered billing and payment processing
- Stream event documentation (Livestreams CPT)
- Artist platform integration
- Real-time stream status display

#### Server 2: Streaming VPS (New - DigitalOcean/Linode)
**Responsibilities**:
- Receive RTMP streams from artists (port 1935)
- Simultaneously rebroadcast to multiple platforms
- Execute WordPress REST API callbacks on stream events
- Zero video data storage (pure passthrough)
- nginx-rtmp-module handles all video processing

**VPS Specifications**:
- **Light Usage** (1-2 concurrent streams): $5-6/month - 1 vCPU, 1GB RAM
- **Medium Usage** (5-10 concurrent streams): $12/month - 2 vCPU, 2GB RAM
- **Heavy Usage** (20+ concurrent streams): $24-40/month - 4 vCPU, 4GB RAM

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

### Data Flow Pattern

**Inbound** (Artist → VPS):
- Single 1080p stream: ~5 Mbps upload from artist

**Outbound** (VPS → Platforms):
- 4 platforms × 5 Mbps = 20 Mbps total per stream
- VPS bandwidth: ~25 Mbps per concurrent stream

**WordPress Load**:
- Minimal (REST API callbacks, status checks, configuration)
- Zero video bandwidth (all video bypasses WordPress entirely)

## WordPress Plugin Architecture

### Plugin Structure

```
extrachill-stream/
├── extrachill-stream.php           # Main plugin file
├── inc/
│   ├── core/
│   │   ├── stream-interface.php    # Main streaming UI template
│   │   ├── http-client.php         # Standardized HTTP client for API interactions
│   │   ├── authentication.php      # Multisite authentication
│   │   ├── assets.php              # CSS/JS enqueuing
│   │   └── stream-hooks.php        # WordPress action/filter hooks
│   ├── providers/
│   │   └── youtube/                # YouTube provider (streaming.php, auth.php)
│   │       ├── streaming.php       # Stream creation, RTMP key management
│   │       └── auth.php            # OAuth flow, token handling
│   └── [future providers: twitch/, facebook/, etc.]
├── assets/
│   ├── css/
│   │   ├── stream.css              # Main streaming interface styles
│   └── js/
│       ├── stream.js               # Video/audio capture and UI interactions
├── build.sh -> ../../.github/build.sh  # Symlink to universal build script
├── .buildignore                    # Build exclusions
├── composer.json                   # Dev dependencies only
├── CLAUDE.md                       # Implementation documentation
└── plan.md                         # This planning document
```

### HTTP Client Architecture

**Standardized API Interactions** (`inc/core/http-client.php`):
- Singleton pattern for consistent API handling across all providers
- Support for Bearer token and OAuth 2.0 authentication
- Automatic error logging and response parsing
- WordPress `wp_remote_*` functions for secure HTTP requests

**Key Features**:
```php
class ExtraChill_Stream_HTTP_Client {
    private static $instance = null;

    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function get($url, $headers = array()) {
        // GET request with auth headers
    }

    public function post($url, $data = array(), $headers = array()) {
        // POST request with auth headers
    }

    private function make_request($method, $url, $args = array()) {
        // Unified request handling with error logging
    }
}
```

### Provider Architecture

**Composition over Inheritance**:
- No base provider classes - each platform implements its own logic
- Direct instantiation: `$youtube_provider = new ExtraChill_Stream_YouTube_Provider()`
- HTTP client injected via dependency injection
- Platform-specific authentication and stream management

**YouTube Provider Structure** (`inc/providers/youtube/`):
```
inc/providers/youtube/
├── streaming.php       # Stream creation, RTMP key retrieval
└── auth.php           # OAuth flow, token management
```

**Provider Interface Contract**:
```php
interface ExtraChill_Stream_Provider_Interface {
    public function authenticate($user_id);
    public function create_stream($user_id, $title, $description = '');
    public function get_rtmp_key($stream_id);
    public function end_stream($stream_id);
    public function validate_credentials($user_id);
}
```

**Benefits**:
- **Flexibility**: Each platform can implement unique requirements
- **Testability**: Individual providers can be unit tested independently
- **Maintainability**: Platform changes don't affect other providers
- **Performance**: No inheritance overhead or abstract method constraints

### Custom Post Type: Livestreams

**Post Type**: `ec_livestream`
**Purpose**: Document streaming events (no video storage)
**Rewrite**: `/streams/` archive, `/streams/{slug}` single

**Post Meta**:
```php
_ec_stream_user_id          // Artist/user who streamed
_ec_stream_artist_id        // Associated artist profile (optional)
_ec_stream_key_used         // RTMP key used for stream
_ec_stream_start_time       // Timestamp when stream started
_ec_stream_end_time         // Timestamp when stream ended
_ec_stream_duration_seconds // Total duration in seconds
_ec_stream_platforms        // JSON array of platforms streamed to
_ec_stream_cost_calculated  // Total cost in dollars
_ec_stream_payment_status   // paid|pending|failed
_ec_stream_wallet_balance_before // Balance before stream
_ec_stream_wallet_balance_after  // Balance after stream
_ec_stream_session_id       // Unique session identifier
_ec_stream_title            // Stream title (user-provided)
_ec_stream_description      // Stream description
_ec_stream_thumbnail_id     // Featured image attachment ID
_ec_stream_vod_links        // JSON: Links to VODs on each platform
```

### Database Schema

#### Stream Sessions Table
```sql
CREATE TABLE {prefix}_ec_stream_sessions (
    session_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT(20) UNSIGNED NOT NULL,
    artist_id BIGINT(20) UNSIGNED NULL DEFAULT NULL,
    stream_key VARCHAR(255) NOT NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME NULL DEFAULT NULL,
    duration_seconds INT UNSIGNED NULL DEFAULT NULL,
    platforms_used TEXT NULL DEFAULT NULL,
    cost_calculated DECIMAL(10,2) NULL DEFAULT NULL,
    payment_status VARCHAR(20) NOT NULL DEFAULT 'pending',
    livestream_post_id BIGINT(20) UNSIGNED NULL DEFAULT NULL,
    PRIMARY KEY (session_id),
    KEY user_id (user_id),
    KEY artist_id (artist_id),
    KEY stream_key (stream_key),
    KEY start_time (start_time),
    KEY payment_status (payment_status)
);
```

#### Billing Transactions Table
```sql
CREATE TABLE {prefix}_ec_stream_billing (
    transaction_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT(20) UNSIGNED NOT NULL,
    transaction_type VARCHAR(20) NOT NULL,  -- credit_purchase|stream_deduction|refund
    amount DECIMAL(10,2) NOT NULL,
    balance_before DECIMAL(10,2) NOT NULL,
    balance_after DECIMAL(10,2) NOT NULL,
    session_id BIGINT(20) UNSIGNED NULL DEFAULT NULL,
    woocommerce_order_id BIGINT(20) UNSIGNED NULL DEFAULT NULL,
    transaction_date DATETIME NOT NULL,
    notes TEXT NULL DEFAULT NULL,
    PRIMARY KEY (transaction_id),
    KEY user_id (user_id),
    KEY transaction_type (transaction_type),
    KEY session_id (session_id),
    KEY transaction_date (transaction_date)
);
```

#### User Wallet Meta
```php
// WordPress user meta
_ec_stream_wallet_balance   // Current balance in dollars (DECIMAL stored as string)
_ec_stream_total_minutes    // Lifetime streaming minutes
_ec_stream_total_spent      // Lifetime spending in dollars
```

## nginx-rtmp Configuration

### VPS Setup Requirements

**Operating System**: Ubuntu 22.04 LTS
**Software Stack**:
- nginx with nginx-rtmp-module
- PHP 8.1+ (for config deployment script)
- SSH server with key authentication

**Installation Steps**:
```bash
# Install nginx with RTMP module
sudo apt update
sudo apt install -y nginx libnginx-mod-rtmp

# Verify RTMP module loaded
nginx -V 2>&1 | grep rtmp

# Create config directory
sudo mkdir -p /etc/nginx/rtmp-streams

# Set permissions for WordPress config deployment
sudo chown www-data:www-data /etc/nginx/rtmp-streams
```

### Dynamic nginx Configuration

**Generated by WordPress** (`inc/nginx-config/config-generator.php`):

```nginx
# /etc/nginx/nginx.conf (RTMP block added)
rtmp {
    server {
        listen 1935;
        chunk_size 4096;

        # ExtraChill Stream Application
        application live {
            live on;
            record off;  # CRITICAL: Zero data storage

            # WordPress callbacks (REST API endpoints)
            on_publish http://stream.extrachill.com/wp-json/extrachill-stream/v1/stream-start;
            on_publish_done http://stream.extrachill.com/wp-json/extrachill-stream/v1/stream-stop;
            on_update http://stream.extrachill.com/wp-json/extrachill-stream/v1/stream-heartbeat;

            # Authentication callback
            on_play http://stream.extrachill.com/wp-json/extrachill-stream/v1/stream-auth;

            # Multi-platform destinations (dynamically generated per user)
            # Example for user with stream key abc123:
            push rtmp://a.rtmp.youtube.com/live2/USER_YOUTUBE_KEY;
            push rtmp://live.twitch.tv/app/USER_TWITCH_KEY;
            push rtmp://live-api-s.facebook.com:80/rtmp/USER_FACEBOOK_KEY;
            # ... additional platforms as configured
        }
    }
}
```

**Per-User Configuration Pattern**:
```nginx
# WordPress generates individual config files
# /etc/nginx/rtmp-streams/{stream_key}.conf

# Included in main rtmp block via:
include /etc/nginx/rtmp-streams/*.conf;

# Example: /etc/nginx/rtmp-streams/abc123xyz.conf
application live_abc123xyz {
    live on;
    record off;

    on_publish http://stream.extrachill.com/wp-json/extrachill-stream/v1/stream-start?key=abc123xyz;
    on_publish_done http://stream.extrachill.com/wp-json/extrachill-stream/v1/stream-stop?key=abc123xyz;

    push rtmp://a.rtmp.youtube.com/live2/YOUTUBE_KEY_FOR_USER;
    push rtmp://live.twitch.tv/app/TWITCH_KEY_FOR_USER;
}
```

### Configuration Deployment Flow

1. User saves destinations in WordPress dashboard
2. WordPress generates nginx config file for user's stream key
3. WordPress deploys config to VPS via SSH/SFTP
4. WordPress triggers `nginx -s reload` via SSH command
5. nginx reloads configuration without dropping active streams
6. User's new destinations immediately available for next stream

**SSH Deployment** (`inc/nginx-config/config-deploy.php`):
```php
function ec_stream_deploy_nginx_config( $stream_key, $config_content ) {
    $ssh_host = get_option( 'ec_stream_vps_host' );
    $ssh_user = get_option( 'ec_stream_vps_user' );
    $ssh_key  = get_option( 'ec_stream_vps_private_key' );

    // Use phpseclib for SSH connection
    $ssh = new \phpseclib3\Net\SSH2( $ssh_host );
    $key = \phpseclib3\Crypt\PublicKeyLoader::load( $ssh_key );

    if ( ! $ssh->login( $ssh_user, $key ) ) {
        return new WP_Error( 'ssh_auth_failed', 'SSH authentication failed' );
    }

    // Write config file
    $remote_path = "/etc/nginx/rtmp-streams/{$stream_key}.conf";
    $ssh->exec( "echo " . escapeshellarg( $config_content ) . " > {$remote_path}" );

    // Reload nginx
    $ssh->exec( 'sudo nginx -t && sudo nginx -s reload' );

    return true;
}
```

## Stream Key Management

### Key Generation

**Format**: `ec_live_{user_id}_{random_hash}`
**Example**: `ec_live_42_a7f3e9d2c1b8`

**Generation Logic** (`inc/stream-keys/key-generation.php`):
```php
function ec_stream_generate_key( $user_id ) {
    $random = bin2hex( random_bytes( 8 ) );
    $key = "ec_live_{$user_id}_{$random}";

    // Store as user meta
    update_user_meta( $user_id, '_ec_stream_key', $key );
    update_user_meta( $user_id, '_ec_stream_key_created', current_time( 'mysql' ) );

    return $key;
}
```

### Key Rotation

**Triggers**:
- User manually requests new key
- Security breach detected
- Key compromised or shared publicly

**Process**:
1. Generate new key
2. Update user meta
3. Regenerate nginx config with new key
4. Deploy to VPS and reload nginx
5. Old key immediately invalid

### OBS Configuration

**User receives**:
- **Server URL**: `rtmp://stream.extrachill.com/live`
- **Stream Key**: `ec_live_42_a7f3e9d2c1b8`

**In OBS**:
```
Settings → Stream
Service: Custom
Server: rtmp://stream.extrachill.com/live
Stream Key: ec_live_42_a7f3e9d2c1b8
```

## Billing System Architecture

### Network Activation Pattern

**Plugin Activation**: Network-activated (like extrachill-multisite and extrachill-ai-client)
- **Dashboard UI**: Only renders on stream.extrachill.com
- **WooCommerce Hooks**: Only active on shop.extrachill.com
- **User Balance**: Accessible from all sites (network-wide user meta)
- **Billing Functions**: Available network-wide for cross-site integration

### Wallet System

**User Balance Storage (Network-Wide)**:
- User meta: `_ec_stream_wallet_balance`
- Type: DECIMAL(10,2) stored as string
- Currency: USD
- **Network-Wide**: Accessible from any site without `switch_to_blog()`

**Additional User Meta**:
```php
_ec_stream_wallet_balance   // Current balance in dollars
_ec_stream_total_minutes    // Lifetime streaming minutes
_ec_stream_total_spent      // Lifetime spending in dollars
_ec_stream_destinations     // Platform configurations (serialized array)
_ec_stream_key              // Unique RTMP stream key
_ec_stream_key_created      // Key generation timestamp
```

### Cross-Site Credit Purchase Flow

**Purchase Journey**:
```
1. stream.extrachill.com/dashboard
   ↓ User clicks "Add Credits"

2. Redirect to shop.extrachill.com/product/streaming-credits/
   ↓ Add ?return_to=stream parameter

3. WooCommerce checkout on shop.extrachill.com
   ↓ User completes purchase

4. Order completion hook on shop.extrachill.com
   ↓ Add credits to user meta (network-wide)
   ↓ Record transaction in billing table on stream site

5. Redirect to stream.extrachill.com/dashboard?credits_added=true
   ↓ Show success message with updated balance
```

**"Add Credits" Link** (on stream.extrachill.com):
```php
$shop_url = 'https://shop.extrachill.com/product/streaming-credits/';
$return_url = add_query_arg( 'return_to', 'stream', $shop_url );

echo '<a href="' . esc_url( $return_url ) . '" class="ec-add-credits-btn">Add Credits</a>';
```

### WooCommerce Integration on shop.extrachill.com

**Site-Specific Hook Initialization** (`inc/billing/woocommerce-integration.php`):
```php
function ec_stream_init_woocommerce_hooks() {
    // Only register WooCommerce hooks on shop site
    $shop_blog_id = get_blog_id_from_url( 'shop.extrachill.com', '/' );

    if ( get_current_blog_id() !== $shop_blog_id ) {
        return; // Not on shop site, skip WooCommerce integration
    }

    // Register hooks for shop site only
    add_action( 'woocommerce_order_status_completed', 'ec_stream_add_credits_on_purchase' );
    add_action( 'woocommerce_thankyou', 'ec_stream_redirect_after_purchase', 20 );
    add_filter( 'woocommerce_product_data_tabs', 'ec_stream_product_tab' );
    add_action( 'woocommerce_product_data_panels', 'ec_stream_product_panel' );
    add_action( 'woocommerce_process_product_meta', 'ec_stream_save_product_meta' );
}
add_action( 'plugins_loaded', 'ec_stream_init_woocommerce_hooks' );
```

**Credit Addition on Purchase** (runs on shop.extrachill.com):
```php
function ec_stream_add_credits_on_purchase( $order_id ) {
    $order = wc_get_order( $order_id );

    foreach ( $order->get_items() as $item ) {
        $product_id = $item->get_product_id();

        // Check if this is a streaming credits product
        if ( get_post_meta( $product_id, '_ec_stream_is_credits_product', true ) === 'yes' ) {
            $credit_amount = floatval( get_post_meta( $product_id, '_ec_stream_credit_amount', true ) );
            $user_id = $order->get_user_id();

            if ( $credit_amount > 0 && $user_id ) {
                // Add to wallet balance (network-wide user meta)
                ec_stream_add_wallet_balance( $user_id, $credit_amount, $order_id );
            }
        }
    }
}
```

**Wallet Balance Functions** (network-wide):
```php
function ec_stream_add_wallet_balance( $user_id, $amount, $order_id = null ) {
    $current_balance = floatval( get_user_meta( $user_id, '_ec_stream_wallet_balance', true ) );
    $new_balance = $current_balance + $amount;

    // Update balance (user meta is network-wide)
    update_user_meta( $user_id, '_ec_stream_wallet_balance', number_format( $new_balance, 2, '.', '' ) );

    // Record transaction on stream site
    $stream_blog_id = get_blog_id_from_url( 'stream.extrachill.com', '/' );

    switch_to_blog( $stream_blog_id );

    global $wpdb;
    $wpdb->insert(
        $wpdb->prefix . 'ec_stream_billing',
        array(
            'user_id'               => $user_id,
            'transaction_type'      => 'credit_purchase',
            'amount'                => $amount,
            'balance_before'        => $current_balance,
            'balance_after'         => $new_balance,
            'woocommerce_order_id'  => $order_id,
            'transaction_date'      => current_time( 'mysql' ),
        ),
        array( '%d', '%s', '%f', '%f', '%f', '%d', '%s' )
    );

    restore_current_blog();

    return $new_balance;
}

function ec_stream_get_wallet_balance( $user_id ) {
    return floatval( get_user_meta( $user_id, '_ec_stream_wallet_balance', true ) );
}

function ec_stream_deduct_from_wallet( $user_id, $amount, $session_id = null ) {
    $current_balance = ec_stream_get_wallet_balance( $user_id );
    $new_balance = max( 0, $current_balance - $amount );

    update_user_meta( $user_id, '_ec_stream_wallet_balance', number_format( $new_balance, 2, '.', '' ) );

    // Record deduction transaction on stream site
    $stream_blog_id = get_blog_id_from_url( 'stream.extrachill.com', '/' );

    switch_to_blog( $stream_blog_id );

    global $wpdb;
    $wpdb->insert(
        $wpdb->prefix . 'ec_stream_billing',
        array(
            'user_id'          => $user_id,
            'transaction_type' => 'stream_deduction',
            'amount'           => -$amount,
            'balance_before'   => $current_balance,
            'balance_after'    => $new_balance,
            'session_id'       => $session_id,
            'transaction_date' => current_time( 'mysql' ),
        ),
        array( '%d', '%s', '%f', '%f', '%f', '%d', '%s' )
    );

    restore_current_blog();

    return $new_balance;
}
```

**Return Redirect After Purchase** (on shop.extrachill.com):
```php
function ec_stream_redirect_after_purchase( $order_id ) {
    // Check if user came from stream site
    if ( isset( $_GET['return_to'] ) && $_GET['return_to'] === 'stream' ) {
        $redirect_url = add_query_arg(
            'credits_added',
            'true',
            'https://stream.extrachill.com/dashboard'
        );

        wp_safe_redirect( $redirect_url );
        exit;
    }
}
```

### Streaming Credits Product Configuration

**Product Setup on shop.extrachill.com**:
- **Product Type**: Variable Product
- **Product Variations**:
  - **$10 Variation**: `_ec_stream_credit_amount = 10.00` (100 minutes @ $0.10/min)
  - **$25 Variation**: `_ec_stream_credit_amount = 25.00` (250 minutes @ $0.10/min)
  - **$50 Variation**: `_ec_stream_credit_amount = 50.00` (500 minutes @ $0.10/min)
  - **$100 Variation**: `_ec_stream_credit_amount = 100.00` (1000 minutes @ $0.10/min)

**Product Meta Fields**:
```php
_ec_stream_is_credits_product  // 'yes' or 'no' (checkbox in admin)
_ec_stream_credit_amount       // Dollar amount to add to wallet (per variation)
```

**Admin Product Tab** (shop.extrachill.com admin):
```php
function ec_stream_product_tab( $tabs ) {
    $tabs['streaming_credits'] = array(
        'label'    => __( 'Streaming Credits', 'extrachill-stream' ),
        'target'   => 'ec_stream_credits_data',
        'priority' => 75,
    );
    return $tabs;
}

function ec_stream_product_panel() {
    global $post;
    ?>
    <div id="ec_stream_credits_data" class="panel woocommerce_options_panel">
        <?php
        woocommerce_wp_checkbox( array(
            'id'          => '_ec_stream_is_credits_product',
            'label'       => __( 'Streaming Credits Product', 'extrachill-stream' ),
            'description' => __( 'Enable this to mark product as streaming credits', 'extrachill-stream' ),
        ) );

        woocommerce_wp_text_input( array(
            'id'          => '_ec_stream_credit_amount',
            'label'       => __( 'Credit Amount ($)', 'extrachill-stream' ),
            'desc_tip'    => true,
            'description' => __( 'Dollar amount to add to user wallet', 'extrachill-stream' ),
            'type'        => 'number',
            'custom_attributes' => array(
                'step' => '0.01',
                'min'  => '0',
            ),
        ) );
        ?>
    </div>
    <?php
}
```

### Metered Usage Tracking

**Stream Start** (`inc/api/stream-start.php`):
```php
// REST API endpoint: /wp-json/extrachill-stream/v1/stream-start
function ec_stream_handle_start( $request ) {
    $stream_key = sanitize_text_field( wp_unslash( $request->get_param( 'name' ) ) );

    // Validate key and get user
    $user_id = ec_stream_get_user_from_key( $stream_key );
    if ( ! $user_id ) {
        return new WP_Error( 'invalid_key', 'Invalid stream key', array( 'status' => 403 ) );
    }

    // Check balance
    $balance = ec_stream_get_wallet_balance( $user_id );
    $min_balance = ec_stream_get_minimum_balance();

    if ( $balance < $min_balance ) {
        ec_stream_send_low_balance_notification( $user_id );
        return new WP_Error( 'insufficient_funds', 'Insufficient balance', array( 'status' => 402 ) );
    }

    // Create session record
    $session_id = ec_stream_create_session( array(
        'user_id'    => $user_id,
        'stream_key' => $stream_key,
        'start_time' => current_time( 'mysql' ),
    ) );

    // Start billing cron (check balance every 5 minutes)
    wp_schedule_single_event( time() + 300, 'ec_stream_check_balance', array( $session_id ) );

    return rest_ensure_response( array(
        'success'    => true,
        'session_id' => $session_id,
    ) );
}
```

**Stream Stop** (`inc/api/stream-stop.php`):
```php
// REST API endpoint: /wp-json/extrachill-stream/v1/stream-stop
function ec_stream_handle_stop( $request ) {
    $stream_key = sanitize_text_field( wp_unslash( $request->get_param( 'name' ) ) );

    // Get active session
    $session = ec_stream_get_active_session_by_key( $stream_key );
    if ( ! $session ) {
        return new WP_Error( 'no_session', 'No active session found', array( 'status' => 404 ) );
    }

    $end_time = current_time( 'mysql' );
    $start = strtotime( $session->start_time );
    $end = strtotime( $end_time );
    $duration_seconds = $end - $start;
    $duration_minutes = ceil( $duration_seconds / 60 );

    // Calculate cost
    $rate_per_minute = ec_stream_get_rate_per_minute();
    $cost = $duration_minutes * $rate_per_minute;

    // Deduct from balance
    $balance_before = ec_stream_get_wallet_balance( $session->user_id );
    ec_stream_deduct_from_wallet( $session->user_id, $cost, $session->session_id );
    $balance_after = ec_stream_get_wallet_balance( $session->user_id );

    // Update session
    ec_stream_update_session( $session->session_id, array(
        'end_time'         => $end_time,
        'duration_seconds' => $duration_seconds,
        'cost_calculated'  => $cost,
        'payment_status'   => 'paid',
    ) );

    // Create livestream post
    $post_id = ec_stream_create_livestream_post( $session );

    // Clear balance check cron
    wp_clear_scheduled_hook( 'ec_stream_check_balance', array( $session->session_id ) );

    return rest_ensure_response( array(
        'success'     => true,
        'cost'        => $cost,
        'duration'    => $duration_minutes,
        'balance'     => $balance_after,
        'livestream'  => $post_id,
    ) );
}
```

**Real-Time Balance Checking** (`inc/billing/billing-cron.php`):
```php
// Cron job runs every 5 minutes during active stream
function ec_stream_check_balance_during_stream( $session_id ) {
    $session = ec_stream_get_session( $session_id );

    // Calculate current cost
    $start = strtotime( $session->start_time );
    $now = time();
    $duration_minutes = ceil( ( $now - $start ) / 60 );
    $cost = $duration_minutes * ec_stream_get_rate_per_minute();

    $balance = ec_stream_get_wallet_balance( $session->user_id );
    $min_balance = ec_stream_get_minimum_balance();

    // Balance warnings
    if ( $balance < $min_balance ) {
        ec_stream_send_low_balance_notification( $session->user_id );
    }

    if ( $balance <= 0 ) {
        // Force disconnect stream
        ec_stream_force_disconnect( $session->stream_key );
        return;
    }

    // Schedule next check in 5 minutes
    wp_schedule_single_event( time() + 300, 'ec_stream_check_balance', array( $session_id ) );
}
add_action( 'ec_stream_check_balance', 'ec_stream_check_balance_during_stream' );
```

### Pricing Structure

**Base Rate**: $0.10 per minute (configurable in admin settings)

**Volume Discounts** (applied automatically):
- 0-600 minutes/month: $0.10/min (base rate)
- 601-3,000 minutes/month: $0.08/min (20% discount)
- 3,001+ minutes/month: $0.06/min (40% discount)

**Discount Calculation** (`inc/billing/billing-functions.php`):
```php
function ec_stream_calculate_discounted_rate( $user_id ) {
    $current_month_start = date( 'Y-m-01 00:00:00' );

    // Get total minutes this month
    global $wpdb;
    $total_minutes = $wpdb->get_var( $wpdb->prepare(
        "SELECT SUM(CEIL(duration_seconds / 60))
         FROM {$wpdb->prefix}ec_stream_sessions
         WHERE user_id = %d
         AND start_time >= %s",
        $user_id,
        $current_month_start
    ) );

    $base_rate = floatval( get_option( 'ec_stream_rate_per_minute', '0.10' ) );

    if ( $total_minutes > 3000 ) {
        return $base_rate * 0.6;  // 40% discount
    } elseif ( $total_minutes > 600 ) {
        return $base_rate * 0.8;  // 20% discount
    }

    return $base_rate;  // Base rate
}
```

**Destination Pricing** (Future Enhancement):
- Single platform: Base rate
- 2-3 platforms: Base rate × 1.0 (no increase)
- 4-5 platforms: Base rate × 1.2 (20% increase)
- 6+ platforms: Base rate × 1.4 (40% increase)

### Force Disconnect System

**Triggers**:
- Balance reaches $0
- User manually stops stream in dashboard
- Admin intervention

**Implementation** (`inc/nginx-config/nginx-reload.php`):
```php
function ec_stream_force_disconnect( $stream_key ) {
    $ssh_host = get_option( 'ec_stream_vps_host' );
    $ssh_user = get_option( 'ec_stream_vps_user' );
    $ssh_key  = get_option( 'ec_stream_vps_private_key' );

    $ssh = new \phpseclib3\Net\SSH2( $ssh_host );
    $key = \phpseclib3\Crypt\PublicKeyLoader::load( $ssh_key );

    if ( $ssh->login( $ssh_user, $key ) ) {
        // Kill specific stream connection
        $ssh->exec( "sudo killall -9 ffmpeg {$stream_key}" );

        // Alternative: Temporarily block stream key in nginx config
        // Then reload nginx to drop connection
    }
}
```

## Platform Destination Management

### Supported Platforms

**Initial Support**:
1. **YouTube Live** - `rtmp://a.rtmp.youtube.com/live2/{key}`
2. **Twitch** - `rtmp://live.twitch.tv/app/{key}`
3. **Facebook Live** - `rtmp://live-api-s.facebook.com:80/rtmp/{key}`
4. **Instagram Live** - `rtmps://live-upload.instagram.com:443/rtmp/{key}`
5. **X/Twitter (Periscope)** - `rtmp://va.pscp.tv:80/x/{key}`
6. **LinkedIn Live** - `rtmps://rtmp-api.linkedin.com:443/live/{key}`
7. **TikTok Live** - `rtmp://push.live.tiktok.com/live/{key}`
8. **Custom RTMP** - User-provided RTMP URL

**Platform Configuration Schema**:
```php
// User meta: _ec_stream_destinations (serialized array)
array(
    'youtube' => array(
        'enabled'     => true,
        'stream_key'  => 'abc-defg-hijk-lmno',
        'stream_name' => 'My YouTube Channel',
    ),
    'twitch' => array(
        'enabled'     => true,
        'stream_key'  => 'live_12345678_AbCdEfGhIjKlMnOp',
        'stream_name' => 'My Twitch Channel',
    ),
    'custom' => array(
        array(
            'enabled'    => true,
            'url'        => 'rtmp://custom-server.com/live',
            'stream_key' => 'custom_key_here',
            'name'       => 'Custom Server 1',
        ),
    ),
)
```

### Destination Management UI

**Location**: stream.extrachill.com/dashboard/destinations

**Interface**:
```
┌─────────────────────────────────────────────────────────┐
│ Streaming Destinations                                  │
├─────────────────────────────────────────────────────────┤
│                                                          │
│ [✓] YouTube Live                                         │
│     Stream Name: [My YouTube Channel              ]     │
│     Stream Key:  [abc-defg-hijk-lmno             ]     │
│     [Test Connection]                                    │
│                                                          │
│ [✓] Twitch                                               │
│     Stream Name: [My Twitch Channel               ]     │
│     Stream Key:  [live_12345678_AbCdEfGh         ]     │
│     [Test Connection]                                    │
│                                                          │
│ [ ] Facebook Live                                        │
│     Stream Name: [                                ]     │
│     Stream Key:  [                                ]     │
│     [Test Connection]                                    │
│                                                          │
│ [+ Add Custom RTMP Destination]                         │
│                                                          │
│ [Save Destinations]                                      │
└─────────────────────────────────────────────────────────┘
```

**Features**:
- Toggle platforms on/off
- Platform-specific stream key input
- Optional stream name for identification
- Test connection button (WordPress pings platform RTMP server)
- Custom RTMP destination support
- Validation and error messaging

**AJAX Handlers** (`inc/ajax/destination-ajax.php`):
```php
// Action: ec_stream_save_destinations
function ec_stream_ajax_save_destinations() {
    check_ajax_referer( 'ec_stream_destinations_nonce' );

    $user_id = get_current_user_id();
    if ( ! $user_id ) {
        wp_send_json_error( 'User not authenticated' );
    }

    $destinations = isset( $_POST['destinations'] ) ? $_POST['destinations'] : array();

    // Validate and sanitize
    $sanitized = ec_stream_sanitize_destinations( $destinations );

    // Save to user meta
    update_user_meta( $user_id, '_ec_stream_destinations', $sanitized );

    // Regenerate nginx config
    ec_stream_regenerate_user_config( $user_id );

    wp_send_json_success( array(
        'message' => 'Destinations saved successfully',
    ) );
}
add_action( 'wp_ajax_ec_stream_save_destinations', 'ec_stream_ajax_save_destinations' );
```

## Dashboard Interface

### Main Dashboard View

**URL**: stream.extrachill.com/dashboard

**Interface Sections**:

1. **Stream Status Widget** (Top)
```
┌─────────────────────────────────────────────────────────┐
│ 🔴 LIVE                                                  │
│ Streaming for 23 minutes                                │
│ Current cost: $2.30                                     │
│ Balance: $47.70 remaining                               │
│ [End Stream]                                             │
└─────────────────────────────────────────────────────────┘
```

2. **Quick Start Widget** (When not streaming)
```
┌─────────────────────────────────────────────────────────┐
│ Ready to Go Live                                         │
│                                                          │
│ Server: rtmp://stream.extrachill.com/live              │
│ Key: ec_live_42_a7f3e9d2c1b8                            │
│ [Copy RTMP URL] [Copy Stream Key]                       │
│                                                          │
│ Balance: $47.70 ($0.10/minute)                          │
│ Streaming to: YouTube, Twitch                           │
│                                                          │
│ [Setup Destinations] [Add Credits] [View History]       │
└─────────────────────────────────────────────────────────┘
```

3. **Recent Streams** (Bottom)
```
┌─────────────────────────────────────────────────────────┐
│ Stream History                                           │
├─────────────────────────────────────────────────────────┤
│ Oct 9, 2025 - 45 minutes - $4.50 - YouTube, Twitch     │
│ Oct 8, 2025 - 120 minutes - $12.00 - YouTube           │
│ Oct 5, 2025 - 30 minutes - $3.00 - Twitch              │
│                                                          │
│ [View All Streams]                                       │
└─────────────────────────────────────────────────────────┘
```

**Template**: `inc/templates/dashboard.php`
**Assets**: `assets/css/dashboard.css`, `assets/js/dashboard.js`

### Stream Setup Wizard

**URL**: stream.extrachill.com/setup

**Step-by-Step Flow**:

**Step 1: Welcome**
- Explains what ExtraChill Stream does
- Overview of multi-platform broadcasting
- Pricing structure display

**Step 2: Generate Stream Key**
- Automatically generates unique RTMP key
- Display OBS configuration instructions
- "Copy to clipboard" functionality

**Step 3: Configure Destinations**
- Select platforms to stream to
- Enter platform stream keys
- Test connections

**Step 4: Add Credits**
- Link to WooCommerce credits product
- Display pricing tiers and discounts
- Minimum $5 to get started

**Step 5: Test Stream**
- Instructions for test stream in OBS
- "I'm ready to test" button
- Real-time connection monitoring

**Template**: `inc/templates/setup-wizard.php`
**Assets**: `assets/css/stream-setup.css`

### Billing Widget

**Display Locations**:
- Dashboard (top right corner)
- WordPress admin bar (when on stream site)
- Artist profile page (via integration)

**Widget Display**:
```
┌──────────────────────────┐
│ Streaming Balance        │
│ $47.70                   │
│ (~477 minutes)           │
│ [Add Credits]            │
└──────────────────────────┘
```

**Real-Time Updates**:
- Balance updates every 30 seconds via AJAX when streaming
- Shows countdown if streaming ("Balance dropping...")
- Warning states: < $5 (yellow), < $1 (red)

**Template**: `inc/templates/billing-widget.php`
**Assets**: `assets/css/billing.css`, `assets/js/billing-widget.js`

## Artist Platform Integration

### Integration Points

**1. Artist Profile Page Enhancement**
```php
// inc/integration/artist-platform-integration.php
add_action( 'ec_artist_profile_content', 'ec_stream_artist_live_badge' );

function ec_stream_artist_live_badge( $artist_id ) {
    $user_id = ec_get_artist_user_id( $artist_id );

    if ( ec_stream_is_user_live( $user_id ) ) {
        echo '<div class="ec-live-badge">';
        echo '🔴 LIVE NOW';
        echo '<a href="' . esc_url( ec_stream_get_artist_watch_url( $user_id ) ) . '">Watch Stream</a>';
        echo '</div>';
    }
}
```

**2. Avatar Menu Integration**
```php
// inc/integration/avatar-menu.php
add_filter( 'ec_avatar_menu_items', 'ec_stream_avatar_menu_items', 15, 2 );

function ec_stream_avatar_menu_items( $menu_items, $user_id ) {
    $is_live = ec_stream_is_user_live( $user_id );

    $menu_items[] = array(
        'url'      => 'https://stream.extrachill.com/dashboard',
        'label'    => $is_live ? '🔴 Streaming Dashboard' : 'Go Live',
        'priority' => 15,
    );

    return $menu_items;
}
```

**3. Homepage Template Override**
```php
// inc/integration/homepage-override.php
add_filter( 'extrachill_template_homepage', 'ec_stream_override_homepage' );

function ec_stream_override_homepage( $template ) {
    $stream_blog_id = get_blog_id_from_url( 'stream.extrachill.com', '/' );

    if ( get_current_blog_id() === $stream_blog_id ) {
        return EC_STREAM_DIR . 'inc/templates/dashboard.php';
    }

    return $template;
}
```

**4. Artist Grid "LIVE" Indicator**
```php
// Filter existing artist grid display
add_filter( 'ec_artist_grid_item_classes', 'ec_stream_artist_grid_live_class', 10, 2 );

function ec_stream_artist_grid_live_class( $classes, $artist_id ) {
    $user_id = ec_get_artist_user_id( $artist_id );

    if ( ec_stream_is_user_live( $user_id ) ) {
        $classes[] = 'artist-is-live';
    }

    return $classes;
}
```

### Cross-Site Data Access

**Check Live Status** (from any site):
```php
function ec_stream_is_user_live( $user_id ) {
    $stream_blog_id = get_blog_id_from_url( 'stream.extrachill.com', '/' );

    switch_to_blog( $stream_blog_id );

    global $wpdb;
    $active_session = $wpdb->get_row( $wpdb->prepare(
        "SELECT session_id FROM {$wpdb->prefix}ec_stream_sessions
         WHERE user_id = %d
         AND end_time IS NULL
         LIMIT 1",
        $user_id
    ) );

    restore_current_blog();

    return ! empty( $active_session );
}
```

## Admin Settings

### Site Settings Page

**Location**: Site Admin → ExtraChill Stream
**Capability**: `manage_options`

**Settings Sections**:

**1. VPS Connection**
```
VPS Hostname: [stream-vps.extrachill.com      ]
SSH Username: [www-data                        ]
SSH Private Key: [textarea for private key     ]
[Test VPS Connection]
```

**2. Billing Configuration**
```
Base Rate (per minute): [$0.10]
Minimum Balance Required: [$5.00]
Low Balance Warning Threshold: [$10.00]

Volume Discounts:
[✓] Enable automatic volume discounts
    Tier 2 (600+ min/month): [20]% discount
    Tier 3 (3000+ min/month): [40]% discount
```

**3. Platform Defaults**
```
Default Platforms (suggested to new users):
[✓] YouTube Live
[✓] Twitch
[ ] Facebook Live
[ ] Instagram Live
```

**4. Stream Limits**
```
Max Concurrent Streams Per User: [1]
Max Stream Duration (minutes): [240] (0 = unlimited)
Max Destinations Per User: [10]
```

**5. Notifications**
```
Low Balance Email Notification:
[✓] Enable low balance warnings

Force Disconnect at Zero Balance:
[✓] Automatically disconnect streams at $0 balance

Email Template:
[textarea for custom email template]
```

**File**: `inc/admin/admin-settings.php`

### Admin Overview Page

**Location**: Site Admin → ExtraChill Stream → All Streams
**Capability**: `manage_options`

**Display**:
- Table of all streams (all users)
- Filters: Date range, user, status (live/ended)
- Columns: User, Start Time, Duration, Cost, Platforms, Status
- Bulk actions: Export CSV, Refund
- Search by user or stream title

**File**: `inc/admin/admin-overview.php`

## Security Implementation

### Authentication & Authorization

**Stream Dashboard Access**:
```php
// inc/core/authentication.php
function ec_stream_require_auth() {
    if ( ! is_user_logged_in() ) {
        wp_die( 'You must be logged in to access streaming features.', 'Authentication Required', array( 'response' => 401 ) );
    }
}
add_action( 'template_redirect', 'ec_stream_require_auth', 5 );
```

**RTMP Key Validation** (nginx callback):
```php
// inc/api/stream-auth.php
function ec_stream_validate_key( $request ) {
    $stream_key = sanitize_text_field( wp_unslash( $request->get_param( 'name' ) ) );

    // Verify key exists and belongs to active user
    $user_id = ec_stream_get_user_from_key( $stream_key );

    if ( ! $user_id ) {
        return new WP_Error( 'invalid_key', 'Invalid stream key', array( 'status' => 403 ) );
    }

    // Check user has sufficient balance
    $balance = ec_stream_get_wallet_balance( $user_id );
    if ( $balance < ec_stream_get_minimum_balance() ) {
        return new WP_Error( 'insufficient_funds', 'Insufficient balance', array( 'status' => 402 ) );
    }

    return rest_ensure_response( array( 'allowed' => true ) );
}
```

### Data Sanitization

**Stream Key Input**:
```php
$stream_key = sanitize_text_field( wp_unslash( $_POST['stream_key'] ) );
```

**Destination Configuration**:
```php
function ec_stream_sanitize_destinations( $destinations ) {
    $clean = array();

    foreach ( $destinations as $platform => $config ) {
        $clean[ sanitize_key( $platform ) ] = array(
            'enabled'     => (bool) ( $config['enabled'] ?? false ),
            'stream_key'  => sanitize_text_field( wp_unslash( $config['stream_key'] ?? '' ) ),
            'stream_name' => sanitize_text_field( wp_unslash( $config['stream_name'] ?? '' ) ),
        );
    }

    return $clean;
}
```

**nginx Config Generation**:
```php
function ec_stream_generate_nginx_config( $user_destinations ) {
    $config = "application live_" . esc_attr( $stream_key ) . " {\n";
    $config .= "    live on;\n";
    $config .= "    record off;\n\n";

    foreach ( $user_destinations as $platform => $dest ) {
        if ( ! $dest['enabled'] ) {
            continue;
        }

        $rtmp_url = ec_stream_get_platform_rtmp_url( $platform );
        $key = esc_attr( $dest['stream_key'] );

        $config .= "    push {$rtmp_url}/{$key};\n";
    }

    $config .= "}\n";

    return $config;
}
```

### Nonce Verification

**All AJAX Requests**:
```php
check_ajax_referer( 'ec_stream_destinations_nonce' );
```

**Form Submissions**:
```php
wp_verify_nonce( $_POST['_wpnonce'], 'ec_stream_save_settings' );
```

### Capability Checks

**Admin Settings**:
```php
if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( 'Insufficient permissions' );
}
```

**Stream Management**:
```php
// User can only manage their own streams
if ( $stream->user_id !== get_current_user_id() && ! current_user_can( 'manage_options' ) ) {
    wp_die( 'You cannot manage this stream' );
}
```

### SQL Injection Prevention

**All Database Queries**:
```php
global $wpdb;

// CORRECT - Always use prepared statements
$sessions = $wpdb->get_results( $wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}ec_stream_sessions
     WHERE user_id = %d
     AND start_time >= %s
     ORDER BY start_time DESC
     LIMIT %d",
    $user_id,
    $start_date,
    $limit
) );
```

## REST API Endpoints

### Public Endpoints (nginx callbacks)

**1. Stream Start**
```
POST /wp-json/extrachill-stream/v1/stream-start
Parameters: name (stream key)
Response: { success: true, session_id: 123 }
```

**2. Stream Stop**
```
POST /wp-json/extrachill-stream/v1/stream-stop
Parameters: name (stream key)
Response: { success: true, cost: 4.50, duration: 45, balance: 42.50 }
```

**3. Stream Auth**
```
POST /wp-json/extrachill-stream/v1/stream-auth
Parameters: name (stream key)
Response: { allowed: true } or { allowed: false, reason: "Insufficient balance" }
```

**4. Stream Heartbeat** (optional - for health monitoring)
```
POST /wp-json/extrachill-stream/v1/stream-heartbeat
Parameters: name (stream key)
Response: { alive: true, duration: 23 }
```

### Authenticated Endpoints (AJAX)

**5. Save Destinations**
```
POST /wp-json/extrachill-stream/v1/destinations/save
Authentication: WordPress cookie
Nonce: X-WP-Nonce header
Body: { destinations: { youtube: { enabled: true, stream_key: "abc" } } }
Response: { success: true, message: "Destinations saved" }
```

**6. Get Stream Status**
```
GET /wp-json/extrachill-stream/v1/status
Authentication: WordPress cookie
Response: { is_live: true, session_id: 123, duration: 23, cost: 2.30 }
```

**7. Force Stop Stream**
```
POST /wp-json/extrachill-stream/v1/force-stop
Authentication: WordPress cookie
Nonce: X-WP-Nonce header
Response: { success: true, cost: 4.50 }
```

**8. Get Wallet Balance**
```
GET /wp-json/extrachill-stream/v1/wallet/balance
Authentication: WordPress cookie
Response: { balance: 47.70, formatted: "$47.70", minutes: 477 }
```

## Frontend Assets

### CSS Architecture

**Dashboard Styles** (`assets/css/dashboard.css`):
- Uses ExtraChill theme CSS custom properties from `root.css`
- Responsive grid layout for dashboard widgets
- Stream status indicator animations (pulsing "LIVE" badge)
- Billing widget styling with warning states
- Mobile breakpoint at 768px

**Billing Widget** (`assets/css/billing.css`):
- Compact widget display for balance
- Color-coded warning states (green/yellow/red)
- Animated balance countdown during stream
- Responsive font sizing

**Stream Setup Wizard** (`assets/css/stream-setup.css`):
- Multi-step wizard progress indicator
- Form styling consistent with WordPress admin
- OBS configuration code blocks
- Test connection status indicators

### JavaScript Modules

**Dashboard** (`assets/js/dashboard.js`):
```javascript
(function($) {
    'use strict';

    const StreamDashboard = {
        init: function() {
            this.cacheDom();
            this.bindEvents();
            this.startStatusPolling();
        },

        cacheDom: function() {
            this.$statusWidget = $('.ec-stream-status-widget');
            this.$balanceDisplay = $('.ec-stream-balance');
            this.$endStreamBtn = $('.ec-stream-end');
        },

        bindEvents: function() {
            this.$endStreamBtn.on('click', this.handleEndStream.bind(this));
        },

        startStatusPolling: function() {
            if (this.isLive()) {
                setInterval(this.updateStreamStatus.bind(this), 30000); // 30 seconds
            }
        },

        updateStreamStatus: function() {
            $.ajax({
                url: ecStream.ajaxUrl,
                data: {
                    action: 'ec_stream_get_status',
                    nonce: ecStream.nonce
                },
                success: function(response) {
                    if (response.success) {
                        this.updateUI(response.data);
                    }
                }.bind(this)
            });
        }
    };

    $(document).ready(function() {
        StreamDashboard.init();
    });

})(jQuery);
```

**Destination Manager** (`assets/js/destination-manager.js`):
- Platform toggle handling
- Stream key validation
- Test connection functionality
- AJAX save with loading states
- Error display and recovery

**Billing Widget** (`assets/js/billing-widget.js`):
- Real-time balance updates during stream
- Cost calculation display
- Warning notifications
- Low balance modal trigger

## Implementation Phases

### Phase 1: MVP Core Functionality (Weeks 1-3) ✅ COMPLETED

**Week 1: Foundation** ✅
- [x] Plugin structure and file organization
- [x] Custom post type registration (Livestreams)
- [x] Database table creation (sessions, billing)
- [x] Stream key generation system
- [x] Basic authentication and multisite integration
- [x] Homepage template override
- [x] HTTP client implementation (standardized API interactions)
- [x] UI template creation and simplification (removed manual quality settings)
- [x] Frontend video/audio capture (getUserMedia API integration)

**Week 2: VPS Integration** 🔄 CURRENT
- [ ] nginx-rtmp VPS setup and configuration
- [ ] Basic nginx config generator
- [ ] SSH deployment system (phpseclib)
- [ ] REST API endpoints (stream start/stop)
- [ ] nginx callback testing

**Week 3: Dashboard UI**
- [ ] Main dashboard template
- [ ] Stream status widget
- [ ] Quick start interface
- [ ] OBS configuration display
- [ ] Basic CSS styling

### Phase 2: Billing System (Weeks 4-5)

**Week 4: Wallet Implementation**
- [ ] Wallet balance user meta
- [ ] WooCommerce credits product creation
- [ ] Credit purchase hook integration
- [ ] Billing transactions table
- [ ] Basic usage tracking (start/stop)

**Week 5: Metered Billing**
- [ ] Duration calculation on stream stop
- [ ] Cost calculation engine
- [ ] Automatic balance deduction
- [ ] Billing widget UI
- [ ] Low balance warnings

### Phase 3: Destination Management (Weeks 6-7)

**Week 6: Platform Integration**
- [ ] Supported platforms definition
- [ ] Destination storage schema
- [ ] Destination management UI
- [ ] Per-user nginx config generation
- [ ] Config deployment and reload

**Week 7: Platform Features**
- [ ] Test connection functionality
- [ ] Custom RTMP destination support
- [ ] Platform-specific validation
- [ ] Destination AJAX handlers

### Phase 4: Advanced Features (Weeks 8-10)

**Week 8: Real-Time Features**
- [ ] Balance checking cron system
- [ ] Force disconnect implementation
- [ ] Real-time status polling (AJAX)
- [ ] Stream heartbeat monitoring
- [ ] Live dashboard updates

**Week 9: Artist Integration**
- [ ] Avatar menu "Go Live" link
- [ ] Artist profile live badge
- [ ] Artist grid live indicator
- [ ] Cross-site live status function
- [ ] Watch stream links

**Week 10: History & Analytics**
- [ ] Stream history display
- [ ] Livestream post creation on end
- [ ] Usage analytics dashboard
- [ ] Export functionality
- [ ] Admin overview page

### Phase 5: Polish & Launch (Weeks 11-12)

**Week 11: Admin & Settings**
- [ ] Site settings page
- [ ] VPS connection configuration
- [ ] Billing settings
- [ ] Email notification system
- [ ] Admin documentation

**Week 12: Testing & Launch**
- [ ] Comprehensive testing (all flows)
- [ ] Security audit
- [ ] Performance optimization
- [ ] User documentation
- [ ] Soft launch to beta users

## Future Enhancements

### Phase 6: Chat Aggregation (Post-Launch)

**Node.js Chat Service**:
- Connects to YouTube, Twitch, Facebook, Instagram chat APIs
- Aggregates messages in real-time
- Forwards to WordPress via WebSocket or REST API
- Unified chat display in dashboard

**WordPress Integration**:
- Chat widget in dashboard during stream
- Message display with platform badges
- Optional: Respond to chat from dashboard

### Phase 7: Advanced Analytics

**Stream Analytics**:
- Viewer count aggregation across platforms
- Peak concurrent viewers
- Average watch time per platform
- Engagement metrics

**Business Analytics**:
- Revenue tracking per artist
- Cost per platform analysis
- Volume discount impact reporting
- Monthly usage reports

### Phase 8: Enhanced Features

**VOD Management**:
- Automatic VOD link capture from platforms
- Embedded VOD player on livestream posts
- VOD archive display on artist profiles

**Stream Scheduling**:
- Schedule future streams
- Email notifications to followers
- Social media auto-posting

**Multi-Camera Support**:
- Multiple RTMP inputs per stream
- Camera switching in dashboard
- Scene management

**Stream Overlays**:
- Customizable stream overlays
- Chat display on stream
- Donation alerts integration

## Dependencies

### WordPress Core
- WordPress 5.0+ multisite installation
- PHP 7.4+
- MySQL 5.7+ or MariaDB 10.3+

### Required Plugins
- **extrachill-artist-platform** - Artist profile integration, user-to-artist mapping
- **extrachill-multisite** - Cross-site functionality, team member system
- **extrachill-users** - Avatar menu integration
- **extrachill-shop** - WooCommerce integration on shop.extrachill.com (credits purchase system)

### Required Theme
- **extrachill** - Template override filters and action hooks

### PHP Libraries (via Composer)
- **phpseclib/phpseclib** (^3.0) - SSH connection for nginx config deployment

### External Services
- **nginx-rtmp VPS** - Video relay server (DigitalOcean, Linode, or similar)

### Platform Requirements
- **Stream Platforms** - Active accounts on YouTube, Twitch, Facebook, etc. with streaming keys

## Risk Assessment

### Technical Risks

**1. VPS Downtime**
- **Risk**: VPS failure means no streaming capability
- **Mitigation**:
  - VPS uptime monitoring
  - Automatic failover to backup VPS (future)
  - Status page showing VPS health

**2. nginx Configuration Errors**
- **Risk**: Bad config could break all streaming
- **Mitigation**:
  - Config validation before deployment (`nginx -t`)
  - Rollback capability for last known good config
  - Per-user config isolation (one user's bad config doesn't affect others)

**3. Billing Race Conditions**
- **Risk**: User starts stream, balance check passes, but payment fails
- **Mitigation**:
  - Reserve minimum balance on stream start
  - Frequent balance checks during stream (every 5 minutes)
  - Grace period before force disconnect

**4. SSH Security**
- **Risk**: Compromised SSH key grants VPS access
- **Mitigation**:
  - SSH key stored in WordPress options (encrypted recommended)
  - Limited SSH user permissions (can only modify nginx configs)
  - IP whitelist on VPS (only allow WordPress server)

### Business Risks

**1. Platform ToS Violations**
- **Risk**: Platforms ban multi-streaming
- **Status**: YouTube, Twitch, Facebook all allow multi-streaming (verify current ToS)
- **Mitigation**: Clear user disclaimers, stay updated on platform policies

**2. Cost Recovery**
- **Risk**: VPS costs exceed revenue from users
- **Mitigation**:
  - Pricing structure covers VPS costs at 10 concurrent streams
  - Volume discounts ensure heavy users still profitable
  - Monitor costs vs revenue monthly

**3. Support Burden**
- **Risk**: Users need help with OBS setup, platform configurations
- **Mitigation**:
  - Comprehensive setup wizard and documentation
  - Video tutorials for common tasks
  - Community forum for peer support

## Success Metrics

### Launch Metrics (First 3 Months)

**Adoption**:
- 50+ artists create stream accounts
- 25+ artists complete first stream
- 10+ artists become monthly active streamers

**Revenue**:
- $500+ monthly recurring streaming revenue
- 80%+ of revenue covers VPS costs
- Average wallet top-up: $25

**Technical**:
- 99%+ uptime for streaming VPS
- <5% failed stream starts
- Average stream duration: 60+ minutes

### Growth Metrics (Months 4-12)

**Adoption**:
- 200+ total streaming accounts
- 50+ monthly active streamers
- 10+ concurrent streams during peak hours

**Revenue**:
- $2,000+ monthly streaming revenue
- 50%+ profit margin after VPS costs
- Wallet system retention: 70%+ users maintain balance

**Feature Usage**:
- Average 3+ platforms per stream
- 30%+ users utilize volume discounts
- Chat aggregation adoption: 40%+ users

## Development Standards

### Code Organization
- **Procedural WordPress Pattern**: Direct `require_once` includes throughout
- **Single Responsibility**: Each file handles one specific domain
- **Modular Structure**: Features organized in `inc/` directories by domain
- **Template Separation**: All templates in `inc/templates/`

### Security Practices
- **Input Sanitization**: `sanitize_text_field()` with `wp_unslash()` throughout
- **Nonce Verification**: All AJAX and form submissions
- **Capability Checks**: `manage_options` for admin, user ID verification for streams
- **SQL Injection Prevention**: Always use `$wpdb->prepare()` with placeholders
- **Output Escaping**: `esc_html()`, `esc_attr()`, `esc_url()` for all output

### WordPress Patterns
- **Custom Post Type**: Standard WordPress CPT registration
- **User Meta**: Wallet balance, destinations, stream keys stored as user meta
- **Cron System**: `wp_schedule_single_event()` for balance checks during streams
- **REST API**: WordPress REST API for nginx callbacks
- **AJAX**: WordPress AJAX with `wp_ajax_*` actions

### Build System
- **Universal Build Script**: Symlink to `../../.github/build.sh`
- **Production Build**: `./build.sh` creates `/build/extrachill-stream/` and `/build/extrachill-stream.zip`
- **File Exclusions**: `.buildignore` excludes dev files, tests, git, node_modules
- **Composer**: Development dependencies only (PHPUnit, PHPCS, phpseclib)

## Documentation Requirements

### User Documentation
- **Setup Guide**: Complete walkthrough from account creation to first stream
- **OBS Configuration**: Step-by-step OBS setup with screenshots
- **Platform Keys**: Where to find stream keys for each platform
- **Billing Guide**: How credits work, pricing tiers, top-up process
- **Troubleshooting**: Common issues and solutions

### Developer Documentation
- **CLAUDE.md**: Comprehensive plugin architecture documentation
- **API Documentation**: REST API endpoints with request/response examples
- **Database Schema**: Table structures and relationships
- **Integration Guide**: How to integrate streaming with other plugins
- **nginx Config**: Example configurations and customization guide

### Admin Documentation
- **VPS Setup**: Complete VPS configuration guide
- **SSH Configuration**: How to set up SSH keys securely
- **Monitoring**: How to monitor VPS health and streaming status
- **Troubleshooting**: Admin-level troubleshooting guide
- **Cost Management**: Tracking costs vs revenue

## User Info

- Name: Chris Huber
- Dev website: https://chubes.net
- GitHub: https://github.com/chubes4
- Founder & Editor: https://extrachill.com
- Creator: https://saraichinwag.com
