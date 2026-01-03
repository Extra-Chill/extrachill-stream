# Streaming Platform

## Overview
Live streaming platform for artist members on stream.extrachill.com (Blog ID 8). Currently in Phase 1 UI prototype with backend integrations offloaded to the **Extra Chill VPS** compute infrastructure.

## Architecture
The platform utilizes a **WordPress UI + Python Compute** architecture:
- **WordPress**: Handles user interface, member authentication, and metered billing.
- **Extra Chill VPS**: Dedicated compute infrastructure (Python/FastAPI) that handles CPU-intensive video transcoding and high-bandwidth multi-platform relay. This offloading strategy is shared with the instagram-bot automation to preserve WordPress performance.

## Current Implementation
- Homepage template override using `extrachill_template_homepage` filter
- UI scaffolding for streaming dashboard
- Artist member access control framework
- Roadmap for integration with Extra Chill VPS compute services

## Future Features
- **VPS-Driven Ingestion**: RTMP ingest service on Extra Chill VPS
- **Transcoding Offloading**: Real-time transcoding for multi-platform broadcasting
- **Member Access Control**: Integrated with artist platform data
- **Metered Billing**: Pay-per-minute streaming tracked via VPS callbacks to WordPress

## Integration
- Shares multisite authentication
- Leverages theme template system
- Offloads resource-heavy tasks to Extra Chill VPS
- Follows platform architectural patterns for compute offloading
