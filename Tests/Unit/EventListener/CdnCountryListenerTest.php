<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 CMS-based extension "view_tracker_cdn_country" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

namespace B13\ViewTrackerCdnCountry\Tests\Unit\EventListener;

use B13\ViewTracker\DTO\TrackingData;
use B13\ViewTracker\Event\EnrichTrackingDataEvent;
use B13\ViewTrackerCdnCountry\EventListener\CdnCountryListener;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class CdnCountryListenerTest extends UnitTestCase
{
    protected function tearDown(): void
    {
        unset($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['view_tracker_cdn_country']);
        parent::tearDown();
    }

    public static function headerProvider(): \Generator
    {
        yield 'Cloudflare' => [['CF-IPCountry' => 'DE'], 'DE'];
        yield 'Fastly' => [['X-Country-Code' => 'fr'], 'FR'];
        yield 'CloudFront' => [['CloudFront-Viewer-Country' => ' US '], 'US'];
        yield 'Cloudflare wins over CloudFront' => [['CloudFront-Viewer-Country' => 'US', 'CF-IPCountry' => 'CH'], 'CH'];
        yield 'unknown at Cloudflare' => [['CF-IPCountry' => 'XX'], ''];
        yield 'Tor at Cloudflare falls through' => [['CF-IPCountry' => 'T1', 'X-Country-Code' => 'NL'], 'NL'];
        yield 'invalid value' => [['CF-IPCountry' => 'Germany'], ''];
        yield 'markup' => [['CF-IPCountry' => '<b>'], ''];
        yield 'no header' => [[], ''];
    }

    #[Test]
    #[DataProvider('headerProvider')]
    public function countryIsReadFromCdnHeader(array $headers, string $expected): void
    {
        self::assertSame($expected, $this->dispatch($headers)->country);
    }

    #[Test]
    public function countryFromEarlierListenerIsKept(): void
    {
        $data = new TrackingData(1);
        $data->country = 'AT';
        $request = new ServerRequest('https://example.com/', 'GET', 'php://input', ['CF-IPCountry' => 'DE']);
        (new CdnCountryListener())(new EnrichTrackingDataEvent($data, $request));

        self::assertSame('AT', $data->country);
    }

    #[Test]
    public function onlyConfiguredHeadersAreRead(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['view_tracker_cdn_country']['headers'] = ['X-Geo-Country'];

        self::assertSame('', $this->dispatch(['CF-IPCountry' => 'DE'])->country);
        self::assertSame('PL', $this->dispatch(['CF-IPCountry' => 'DE', 'X-Geo-Country' => 'PL'])->country);
    }

    #[Test]
    public function headersCanBeConfiguredAsCommaSeparatedString(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['view_tracker_cdn_country']['headers'] = 'X-Geo-Country, CF-IPCountry';

        self::assertSame('DE', $this->dispatch(['CF-IPCountry' => 'DE'])->country);
    }

    #[Test]
    public function emptyConfigurationDisablesTheListener(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['view_tracker_cdn_country']['headers'] = [];

        self::assertSame('', $this->dispatch(['CF-IPCountry' => 'DE'])->country);
    }

    private function dispatch(array $headers): TrackingData
    {
        $data = new TrackingData(1);
        $request = new ServerRequest('https://example.com/_b13vt?page=1', 'GET', 'php://input', $headers);
        (new CdnCountryListener())(new EnrichTrackingDataEvent($data, $request));
        return $data;
    }
}
