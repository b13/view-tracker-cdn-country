# Changelog

All notable changes to `b13/view-tracker-cdn-country` are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.1.0] — 2026-11-25

First public release on GitHub and Packagist.

### Added

- **Configurable headers.** `$GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['view_tracker_cdn_country']['headers']`
  sets which headers are read and in which order. Reading only the header of
  your own CDN keeps clients from choosing their country.

### Changed

- **Runs before View Tracker GeoIP** when both are installed, so a country
  header from the CDN always wins and no lookup is needed.

### Documentation

- README explains when the header can be trusted: only if the origin server
  is not reachable around the CDN.

### Tests

- Unit tests for all supported headers, pseudo codes, invalid values, and
  the header configuration.

## [1.0.1] — 2026-04-14

### Changed

- New extension icon.

## [1.0.0] — 2026-02-24

- Initial release.
