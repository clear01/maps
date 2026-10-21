# Clear01 Maps
Map gps picker, map with marker control and address suggestion for Nette Framework.

Inspired by https://github.com/occ2/nette-mapy-cz

## Requirements
- PHP 8.3+ (tested on 8.4)
- Nette Application / Forms / DI 3.2+, Nette Utils 4, Latte 3.0.18+ (3.0 and 3.1)
- optional: `doctrine/dbal` ^3.10 for `Clear01\Maps\Doctrine\GpsCoordsType`, `doctrine/orm` ^2.20 for `Clear01\Maps\Doctrine\GpsTrait`

Upgrading from 0.x (Nette 2.4 / Latte 2)? See [UPGRADE.md](UPGRADE.md).

## Installation
```bash
composer require clear01/maps
```

## Install JS file
Add assets/maps.js into your js loader

Add selected driver into your js loader

The inline scripts of the controls use jQuery (`$(modal)`) only for the Bootstrap modal fix.

## Add Leaflet js and project javascript files to your page heading
```html
<head>
    <!-- Leaflet   -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <!-- Base -->
    <script src="assets/maps.js"></script>
    <!-- Mapy.cz   -->
    <script src="assets/drivers/mapycz.js"></script>
    <!-- Mapy.cz for suggestion   -->
    <script src="assets/drivers/mapycz.suggestion.js"></script>
</head>
```

## Setup config
Register the extension. It registers `IMapControlFactory` as a service and the form methods `addGpsPicker()` and `addAddressSuggestion()`.
```yaml
extensions:
  maps: Clear01\Maps\DI\MapsExtension

maps:
  #api key for map based on selected driver
  mapApiKey: "your_api_key" #https://developer.mapy.com/cs/rest-api/jak-zacit/
  #api key for suggestion based on selected driver
  suggestionApiKey: "your_api_key"
```
Both keys are optional strings (default `''`), unknown keys are rejected by the config schema.

Doctrine type (DBAL 3, e.g. with nettrine/dbal):
```yaml
nettrine.dbal:
  connection:
    types:
      gps_coords: Clear01\Maps\Doctrine\GpsCoordsType
```

## Usage
In your presenter
```php
use Clear01\Maps\Controls\Map\IMapControlFactory;
use Clear01\Maps\Controls\Map\MapControl;
use Clear01\Maps\Models\GpsCoords;
use Clear01\Maps\Models\Marker;
use Nette\Application\UI\Form;

#[Nette\DI\Attributes\Inject]
public IMapControlFactory $mapControlFactory;

...


protected function createComponentMap(): MapControl
{
    $control = $this->mapControlFactory->create(['height' => '400px']);
    $control->addMarker(new Marker(new GpsCoords(50.2, 17.1), 'Titletext'));
    #custom icon: https://leafletjs.com/examples/custom-icons/
    $control->addMarker(new Marker(new GpsCoords(50.3, 17.2), 'Other', ['divIcon' => ['className' => 'pin', 'html' => '<span></span>']]));

    return $control;
}

...


protected function createComponentForm(): Form
{
	$form = new Form();
	$gpsInput = $form->addGpsPicker('gps', 'Select location');
	$form->addAddressSuggestion('street', 'Address');
	$form->addSubmit('send', 'Submit');

	#default value can be GpsCoords, string "lat lng" or null
	$form->setDefaults(['gps' => new GpsCoords(49.19, 16.60)]);

	$form->onSuccess[] = function (Form $form, $values) use ($gpsInput) {
		//$gpsInput->getGps() returns GpsCoords or null
		$gpsInput->getGps();
	};
	return $form;
}
```
GpsPicker validates the value: format `"latitude longitude"` (whitespace separated, optional `N`/`E` suffix, negative values allowed) and the range of the coordinates. Invalid coordinates throw `Clear01\Maps\Exceptions\MapException` from `new GpsCoords()` / `GpsCoords::fromString()`.

You must set callback in template for suggestion (event - maps.address.select):
Structure for mapy.cz API can be found here: https://developer.mapy.com/cs/rest-api/funkce/geokodovani/

Latte 3 prints variables in `<script>` as JS values (JSON) — never put `{$var}` inside a JS string literal (`"#{$id}"` is a compile error), concatenate instead:

```html
{input street}
<script>
    document.querySelector("#" + {$form['street']->getHtmlId()}).addEventListener('maps.address.select', function (e) {
        let origData = e.detail;
        if (origData.regionalStructure) {
            origData.regionalStructure.forEach(function (item) {
                if (item.type === 'regional.address') {
                    document.querySelector('#frm-address-form-houseNo').value = item.name;
                }
                if (item.type === 'regional.street') {
                    document.querySelector('#frm-address-form-street').value = item.name;
                }
                if (item.type === 'regional.municipality') {
                    document.querySelector('#frm-address-form-city').value = item.name;
                }
            })
        }
        if (origData.zip) {
            document.querySelector('#frm-address-form-postcode').value = origData.zip;
        }

    })
</script>

```

For suggestion you need to install https://tarekraafat.github.io/autoComplete.js/

## Tests
```bash
composer install
vendor/bin/tester -C tests
```
