# Clear01 Maps
Map gps picker, map with marker control and address suggestion for Nette Framework.

Inspired by https://github.com/occ2/nette-mapy-cz

## Installation
```bash
composer require clear01/maps
```

## Install JS file
Add assets/maps.js into your js loader

Add selected driver into your js loader

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
Register control factory as service in your config.neon register extension add picker to you forms
```yaml
# register method addGpsPicker to your forms
extensions:
  maps: Clear01\Maps\DI\MapsExtension

maps:
  #api key for map based on selected driver 
  mapApiKey: "your_api_key" #https://developer.mapy.com/cs/rest-api/jak-zacit/
  #api key for suggestion based on selected driver
  suggestionApiKey: "your_api_key" 

```

## Usage 
In your presenter
```php
use Clear01\Maps\Controls\MapControl\Factories\IMapControlFactory;
use Clear01\Maps\Controls\MapControl;
use Nette\Application\UI\Form;

/**
 * @var IMapControlFactory
 * @inject
 */
public $mapControlFactory;

...


protected function createComponentMap(string $name): MapControl
{
    $control = $this->mapControlFactory->create();
    $control->addMarker(new Marker(50.2, 17.1, 'Titletext'));
   
    return $control;
}

...


protected function createComponentForm(string $name): Form
{
	$form = new Form();
	$gpsInput = $form->addGpsPicker('gps', 'Select location');
	$form->addAddressSuggestion('street', 'Address');
	$form->addSubmit('send', 'Submit');
	
	$form->onSuccess[] = function (Form $form, $values) use ($gpsInput) {
		//$gpsInput->getGps() returns GpsCoords or null
		$gpsInput->getGps();
	};
	return $form;
}
```
You must set callback in template for suggestion (event - maps.address.select):
Structure for mapy.cz API can be found here: https://developer.mapy.com/cs/rest-api/funkce/geokodovani/

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