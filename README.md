# View Tracker CDN Country

Country enrichment for [b13/view-tracker](https://github.com/b13/view-tracker) via CDN headers. Zero overhead — no database lookups, no external requests.

## How it works

When a page view is tracked, this extension reads the visitor's country from HTTP headers set by your CDN. It supports:

| CDN | Header |
|-----|--------|
| Cloudflare | `CF-IPCountry` |
| Fastly | `X-Country-Code` |
| AWS CloudFront | `CloudFront-Viewer-Country` |

The country is stored as an ISO 3166-1 alpha-2 code (e.g. `DE`, `US`, `FR`).

If the country was already resolved by another listener (e.g. `b13/view-tracker-geoip`), this extension skips processing.

## Requirements

- PHP 8.1+
- TYPO3 12+
- `b13/view-tracker`
- Your site must be behind a supported CDN

## Installation

```bash
composer require b13/view-tracker-cdn-country
```

That's it — no configuration needed. The event listener is auto-discovered via the `#[AsEventListener]` attribute.

## CDN setup

### Cloudflare

The `CF-IPCountry` header is sent automatically when "IP Geolocation" is enabled (on by default). No action required.

### Fastly

Add a header in your VCL or via the Fastly UI:

```
set req.http.X-Country-Code = client.geo.country_code;
```

### AWS CloudFront

Enable the `CloudFront-Viewer-Country` header in your origin request policy under "CloudFront Headers".

## Coexistence with b13/view-tracker-geoip

Both extensions can be installed simultaneously. Each listener checks if the country is already set and skips if so. This allows using the CDN header in production and GeoIP as a fallback in environments without a CDN.

## Background, authors & maintenance

This extension was created by David Steeb in 2026 for [b13 GmbH, Stuttgart](https://b13.com).

[Find more TYPO3 extensions we have developed](https://b13.com/useful-typo3-extensions-from-b13-to-you) that help us deliver
value in client projects. As part of our work, we focus on testing and best practices to ensure long-term performance,
reliability, and results in all our code.

