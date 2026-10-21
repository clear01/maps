<?php
declare(strict_types = 1);

use Clear01\Maps\Controls\GpsPicker\GpsPicker;
use Clear01\Maps\Models\GpsCoords;
use Clear01\Maps\Tests\Helpers;
use Nette\Application\UI\Form as UIForm;
use Nette\Forms\Form;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

GpsPicker::register('map-key', 'suggestion-key');

test('setValue / getValue / getGps', function (): void {
	$form = new Form();
	$picker = $form->addGpsPicker('gps', 'Location');

	Assert::same('', $picker->getValue());
	Assert::null($picker->getGps());

	$picker->setValue(new GpsCoords(49.19523, 16.60796));
	Assert::same('49.19523 16.60796', $picker->getValue());
	Assert::equal(new GpsCoords(49.19523, 16.60796), $picker->getGps());

	$picker->setValue('49.1 16.2');
	Assert::same('49.1 16.2', $picker->getValue());
	Assert::equal(new GpsCoords(49.1, 16.2), $picker->getGps());

	$picker->setValue(null);
	Assert::same('', $picker->getValue());
	Assert::null($picker->getGps());

	#default value from entity (projectplus passes (string) $entity->getGps() or GpsCoords)
	$form->setDefaults(['gps' => new GpsCoords(50.0, 15.0)]);
	Assert::same('50 15', $picker->getValue());
});

test('validation', function (): void {
	$validate = function (string $value): array {
		$form = new Form();
		$picker = $form->addGpsPicker('gps', 'Location');
		$picker->setValue($value);
		$picker->validate();

		return $picker->getErrors();
	};
	Assert::same([], $validate(''));
	Assert::same([], $validate('49.19523 16.60796'));
	Assert::same([], $validate('-33.5 -70.25'));
	Assert::count(1, $validate('49.19523,16.60796'));
	Assert::same(['Neplatné GPS souřadnice.'], $validate('100.5 16.6'));
});

test('loadHttpData', function (): void {
	$form = new class extends Form {
		protected function receiveHttpData(): ?array
		{
			return ['gps' => " 49.5\t16.25 ", 'send' => 'x'];
		}
	};
	$picker = $form->addGpsPicker('gps', 'Location');
	$form->addSubmit('send');
	Assert::true($form->isSuccess());
	Assert::same("49.5\t16.25", $picker->getValue());
	Assert::equal(new GpsCoords(49.5, 16.25), $picker->getGps());
	Assert::same(['gps' => "49.5\t16.25"], (array) $form->getValues());
});


test('render without presenter', function (): void {
	$form = new Form();
	$picker = $form->addGpsPicker('gps', 'Location');
	$picker->setValue('49.5 16.25');
	$picker->updateSettings('maxZoom', 18);
	$html = (string) $picker->getControl();

	Assert::contains('<div id="map-frm-gps" style="width: 100%; height: 400px" data-map></div>', $html);
	Assert::contains('id="map-frm-gps-autocomplete"', $html);
	Assert::match('%A%<input type="text" name="gps" id="frm-gps" data-nette-rules=%A% value="49.5 16.25" class="form-control">%A%', $html);
	Assert::contains('mapPicker.init("map-frm-gps");', $html);
	Assert::contains('mapPicker.setPosition(49.5, 16.25, true);', $html);
	Assert::contains('"inputId":"frm-gps"', $html);
	Assert::contains('"maxZoom":18', $html);
	Assert::contains('"defaultZoom":13', $html);

	#empty value => no setPosition
	$picker->setValue('');
	Assert::notContains('setPosition', (string) $picker->getControl());
	#invalid value => renders, no setPosition
	$picker->setValue('100 200');
	Assert::notContains('setPosition', (string) $picker->getControl());
});

test('render in presenter (template factory, {input} macro)', function (): void {
	$presenter = Helpers::createPresenter();
	$form = new UIForm();
	$form->setAction('/submit');
	$form->addGpsPicker('gps', 'Location')->setValue(new GpsCoords(49.5, 16.25));
	$presenter->addComponent($form, 'form');

	$latte = Helpers::createLatteFactory()->create($presenter);
	$latte->setLoader(new Latte\Loaders\StringLoader());
	$html = $latte->renderToString('{form form}{label gps /}{input gps}{/form}', ['presenter' => $presenter, 'control' => $presenter]);

	Assert::contains('<label for="frm-form-gps">Location</label>', $html);
	Assert::contains('mapPicker.init("map-frm-form-gps");', $html);
	Assert::contains('mapPicker.setPosition(49.5, 16.25, true);', $html);
	Assert::contains('data-nette-rules=', $html);
	#rendered through the presenter's template factory
	Assert::true(Helpers::$templatesCreated > 0);
});
