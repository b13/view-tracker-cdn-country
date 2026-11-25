# View Tracker CDN Country for TYPO3

Country detection for [View Tracker](https://github.com/b13/view-tracker) from
the country header your CDN already sends. No database, no lookup, no external
request.

## How It Works

When View Tracker counts a page view, this extension reads the visitor's
country from a request header set by the CDN and stores it as an ISO 3166-1
alpha-2 code (e.g. `DE`, `US`, `FR`). By default it checks these headers in
this order and uses the first valid one:

| CDN | Header |
|-----|--------|
| Cloudflare | `CF-IPCountry` |
| Fastly | `X-Country-Code` (set it yourself, see below) |
| AWS CloudFront | `CloudFront-Viewer-Country` |

Cloudflare's pseudo codes `XX` (unknown) and `T1` (Tor) are ignored. Only the
country code is stored; no IP address is involved.

## Compatibility

TYPO3 v13.4 and v14, PHP 8.2 or newer, `b13/view-tracker` 1.0 or newer.

Release notes live in [CHANGELOG.md](CHANGELOG.md).

## Installation

```bash
composer require b13/view-tracker-cdn-country
```

No configuration is needed if your CDN sends one of the headers above.

## Only Trust the Header Behind the CDN

The header is only trustworthy if every request passes through your CDN.
If the origin server is reachable directly, anyone can send
`CF-IPCountry: XY` and choose the country that gets counted. Lock the origin
down to your CDN, for example with Cloudflare Authenticated Origin Pulls, an
IP allowlist of the CDN's address ranges, or a secret header checked by the
web server.

## Configuration

To read other headers, or only the one your CDN sets, list them in
`config/system/additional.php`. The first header with a valid country code
wins:

```php
$GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['view_tracker_cdn_country']['headers'] = ['CF-IPCountry'];
```

Reading only the header of your own CDN is the safer choice: a client cannot
then fill a header your CDN does not overwrite.

## CDN Setup

### Cloudflare

`CF-IPCountry` is sent when "IP Geolocation" is enabled under Network
settings (on by default).

### Fastly

Add the header in your VCL or via the Fastly UI:

```
set req.http.X-Country-Code = client.geo.country_code;
```

### AWS CloudFront

Add the `CloudFront-Viewer-Country` header to your origin request policy.

## Together With View Tracker GeoIP

Both extensions can be installed at the same time. This extension runs first;
if it finds a country, [View Tracker GeoIP](https://github.com/b13/view-tracker-geoip)
skips its lookup. That way the CDN header is used where it exists, and the
local database is the fallback, for example on a staging system without a CDN.

## Credits

View Tracker CDN Country was created by David Steeb and is maintained by
[b13 GmbH](https://b13.com/?utm_source=view_tracker_cdn_country&utm_medium=readme), Stuttgart, Germany.

[Find more TYPO3 extensions we have developed](https://b13.com/useful-typo3-extensions-from-b13-to-you?utm_source=view_tracker_cdn_country&utm_medium=readme)
that help us deliver value in client projects. As part of our work, we focus on
testing and best practices to ensure long-term performance, reliability, and
results in all our code.
