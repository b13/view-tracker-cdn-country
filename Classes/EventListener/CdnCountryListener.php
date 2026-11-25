<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 CMS-based extension "view_tracker_cdn_country" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

namespace B13\ViewTrackerCdnCountry\EventListener;

use B13\ViewTracker\Event\EnrichTrackingDataEvent;
use TYPO3\CMS\Core\Attribute\AsEventListener;

/**
 * Reads the visitor's country from a header set by the CDN. Runs before
 * b13/view-tracker-geoip, which then skips its lookup.
 */
#[AsEventListener(
    identifier: 'b13-view-tracker-cdn-country/country',
    before: 'b13-view-tracker-geoip/country',
)]
final class CdnCountryListener
{
    /**
     * CDN headers in priority order, used unless configured otherwise.
     */
    public const DEFAULT_HEADERS = [
        'CF-IPCountry',              // Cloudflare
        'X-Country-Code',            // Fastly / generic
        'CloudFront-Viewer-Country', // AWS CloudFront
    ];

    /**
     * Pseudo codes that don't represent real countries: unknown (XX)
     * and Tor (T1) at Cloudflare.
     */
    private const SPECIAL_CODES = ['XX', 'T1'];

    public function __invoke(EnrichTrackingDataEvent $event): void
    {
        if ($event->trackingData->country !== '') {
            return;
        }

        foreach ($this->getHeaders() as $header) {
            $code = strtoupper(trim($event->request->getHeaderLine($header)));
            if (preg_match('/^[A-Z]{2}$/', $code) !== 1 || in_array($code, self::SPECIAL_CODES, true)) {
                continue;
            }
            $event->trackingData->country = $code;
            return;
        }
    }

    /**
     * @return list<string>
     */
    private function getHeaders(): array
    {
        $headers = $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['view_tracker_cdn_country']['headers'] ?? self::DEFAULT_HEADERS;
        if (is_string($headers)) {
            $headers = explode(',', $headers);
        }
        return array_values(array_filter(array_map('trim', (array)$headers), static fn(string $header): bool => $header !== ''));
    }
}
