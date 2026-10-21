<?php
declare(strict_types = 1);

use Clear01\Maps\Controls\Map\MapControl;
use Clear01\Maps\Models\GpsCoords;
use Clear01\Maps\Models\Marker;
use Clear01\Maps\Tests\Helpers;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

$presenter = Helpers::createPresenter();
$control = new MapControl('map-key', ['height' => '500px']);
$control->addMarker(new Marker(new GpsCoords(49.5, 16.25), '<strong>A</strong>', ['divIcon' => ['className' => 'pin']]));
$control->addMarker(new Marker(new GpsCoords(50.0, 15.0)));
$control->updateSettings('maxZoom', 15);
$presenter->addComponent($control, 'map');

ob_start();
$control->render();
$html = ob_get_clean();

Assert::match('<div id="map-map-%d%" style="width: 100%%; height: 500px" data-map></div>%A%', $html);
Assert::match('%A%mapControl.init("map-map-%d%");%A%', $html);
Assert::true((bool) preg_match('~createMapControl\((\{.*\})\);~', $html, $matches));

#settings are a JSON object in the JS context, markers are JSON strings (parsed in assets/maps.js)
$settings = json_decode($matches[1], true);
Assert::same('map-key', $settings['apiKey']);
Assert::same('500px', $settings['height']);
Assert::same(15, $settings['maxZoom']);
Assert::count(2, $settings['markers']);
Assert::same(['latitude' => 49.5, 'longitude' => 16.25, 'title' => '<strong>A</strong>', 'divIcon' => ['className' => 'pin']], json_decode($settings['markers'][0], true));
#</script> can not break out of the script element
Assert::notContains('</strong>', $html);
