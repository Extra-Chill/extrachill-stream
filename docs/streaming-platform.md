# Streaming Platform

## Overview
Live streaming platform for artist members on stream.extrachill.com (Blog ID 8). The plugin currently provides a Phase 1 UI prototype and access control.

## Current Implementation
- Homepage content renders via the theme action hook `extrachill_homepage_content`.
- UI scaffolding for a streaming dashboard.
- Artist-only access control runs during `template_redirect` using multisite membership (artist site membership) and returns a 404 for non-members.
- Minimal REST endpoints exist in `inc/core/rest-api.php` (Phase 1 scaffolding; not a full streaming pipeline).

## Future Features
This plugin is intended to evolve into a WordPress UI that can integrate with external streaming infrastructure (ingest, relay/transcoding, billing callbacks). Those systems are not implemented in this plugin yet.

## Integration
- Shares multisite authentication
- Uses the ExtraChill theme homepage hook and template shell
