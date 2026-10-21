<?php
declare(strict_types = 1);

use Clear01\Maps\Exceptions\MapException;
use Clear01\Maps\Models\GpsCoords;
use Clear01\Maps\Models\Marker;
use Clear01\Maps\Models\Settings;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

$gps = new GpsCoords(49.19523, 16.60796);
Assert::same(49.19523, $gps->getLatitude());
Assert::same(16.60796, $gps->getLongitude());
Assert::same('49.19523 16.60796', (string) $gps);

Assert::exception(fn() => new GpsCoords(91.0, 16.0), MapException::class, null, MapException::INVALID_LATITUDE);
Assert::exception(fn() => new GpsCoords(49.0, -181.0), MapException::class, null, MapException::INVALID_LONGITUDE);
Assert::type(InvalidArgumentException::class, new MapException());

#parsing
Assert::equal(new GpsCoords(49.1, 16.2), GpsCoords::fromString('49.1 16.2'));
Assert::equal(new GpsCoords(49.1, 16.2), GpsCoords::fromString("  49.1N \t 16.2E "));
Assert::equal(new GpsCoords(-33.5, -70.25), GpsCoords::fromString('-33.5 -70.25'));
Assert::null(GpsCoords::fromString(null));
Assert::null(GpsCoords::fromString(''));
Assert::null(GpsCoords::fromString('49.1'));
Assert::null(GpsCoords::fromString('49.1 16.2 3'));
Assert::null(GpsCoords::fromString('abc def'));
Assert::exception(fn() => GpsCoords::fromString('100 16'), MapException::class);

#marker serializes to JSON consumed by assets/maps.js
$marker = new Marker($gps, '<b>Title</b>', ['divIcon' => ['className' => 'x']]);
Assert::same(['latitude' => 49.19523, 'longitude' => 16.60796, 'title' => '<b>Title</b>', 'divIcon' => ['className' => 'x']], json_decode((string) $marker, true));
Assert::same(['latitude' => 49.19523, 'longitude' => 16.60796], (new Marker($gps))->getData());

#settings: defaults overridden by given values, JSON has only the setting keys
$settings = new Settings(['height' => '400px', 'apiKey' => 'key']);
Assert::same('400px', $settings->height);
Assert::same('100%', $settings['width']);
Assert::same(
	['height' => '400px', 'apiKey' => 'key', 'width' => '100%', 'center' => ['latitude' => 49.75, 'longitude' => 15.47], 'defaultZoom' => 7, 'maxZoom' => 12, 'markers' => []],
	json_decode(json_encode($settings), true)
);
