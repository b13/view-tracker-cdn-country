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

#[AsEventListener]
final class CdnCountryListener
{
    /**
     * CDN headers in priority order.
     */
    private const CDN_HEADERS = [
        'CF-IPCountry',             // Cloudflare
        'X-Country-Code',           // Fastly / generic
        'CloudFront-Viewer-Country', // AWS CloudFront
    ];

    /**
     * Cloudflare pseudo-codes that don't represent real countries.
     */
    private const CLOUDFLARE_SPECIALS = ['XX', 'T1'];

    public function __invoke(EnrichTrackingDataEvent $event): void
    {
        if ($event->trackingData->country !== '') {
            return;
        }

        foreach (self::CDN_HEADERS as $header) {
            $value = $event->request->getHeaderLine($header);
            if ($value === '') {
                continue;
            }

            $code = strtoupper(trim($value));

            if (preg_match('/^[A-Z]{2}$/', $code) !== 1) {
                continue;
            }

            if (in_array($code, self::CLOUDFLARE_SPECIALS, true)) {
                continue;
            }

            $event->trackingData->country = $code;
            return;
        }
    }
}
