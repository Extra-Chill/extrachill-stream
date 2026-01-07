# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.1.3] - 2026-01-07

### Fixed
- Corrected stream breadcrumb back-link label regression (removed corrupted character sequence)

## [0.1.2] - 2026-01-01

### Changed
- Migrated streaming interface CSS to use theme custom properties for typography (`--font-size-*`) instead of hardcoded pixel values
- Standardized artist validation to use WordPress core `is_user_member_of_blog()` against blog ID 4
- Optimized asset loading to use resolved multisite blog IDs for stream/artist sites via `ec_get_blog_id()`
- Switched to vanilla JS module pattern for streaming UI, removing jQuery dependency
- Implemented `getUserMedia` for local camera/screen preview in the streaming interface
- Customized breadcrumb integration for better navigation context within the Extra Chill platform
- Added placeholder REST API endpoints for future streaming state management

### Dependencies
- Cleaned up vendor directory and synchronized development dependencies

## [0.1.1] - 2025-12-04

### Fixed
- Homepage integration now uses `extrachill_homepage_content` action instead of template filter
- Added try/finally block for multisite blog switching to prevent context issues
- Stream interface template updated to render as homepage content without header/footer

### Documentation
- Renamed CLAUDE.md to AGENTS.md for comprehensive plugin documentation
- Updated all documentation references to use AGENTS.md
- Aligned version numbers across documentation files
- Realigned AGENTS.md and README.md to describe current Phase 1 UI-only implementation

### Dependencies
- Updated composer dependencies to latest versions