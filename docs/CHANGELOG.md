# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.1.1] - 2025-12-04

### Fixed
- Homepage integration now uses `extrachill_homepage_content` action instead of template filter
- Added try/finally block for multisite blog switching to prevent context issues
- Stream interface template updated to render as homepage content without header/footer

### Documentation
- Renamed CLAUDE.md to AGENTS.md for comprehensive plugin documentation
- Updated all documentation references to use AGENTS.md
- Aligned version numbers across documentation files

### Dependencies
- Updated composer dependencies to latest versions