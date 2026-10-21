<?php
declare(strict_types = 1);

use Clear01\Maps\Controls\AddressSuggestion\AddressSuggestion;
use Clear01\Maps\Tests\Helpers;
use Nette\Application\UI\Form as UIForm;
use Nette\Forms\Form;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

AddressSuggestion::register('suggestion-key');

test('value', function (): void {
	$form = new Form();
	$input = $form->addAddressSuggestion('street', 'Street', 50);
	$input->setValue('Masarykova 12');
	Assert::same('Masarykova 12', $input->getValue());
	$input->setValue(null);
	Assert::same('', $input->getValue());
});

test('render without presenter', function (): void {
	$form = new Form();
	$input = $form->addAddressSuggestion('street', 'Street', 50);
	$input->setValue('Masarykova "12"');
	$html = (string) $input->getControl();

	Assert::match('<input type="text" name="street" maxlength="50" id="frm-street" value=\'Masarykova "12"\' class="form-control">%A%', $html);
	Assert::contains('Maps.Factory.createSuggestion("suggestion-key");', $html);
	Assert::contains('document.getElementById("frm-street")', $html);
});

test('render in presenter', function (): void {
	$presenter = Helpers::createPresenter();
	$form = new UIForm();
	$form->setAction('/submit');
	$form->addAddressSuggestion('street', 'Street');
	$presenter->addComponent($form, 'address');

	#{input} with extra attributes (as projectplus Address/template.latte uses it) must not fail
	$latte = Helpers::createLatteFactory()->create($presenter);
	$latte->setLoader(new Latte\Loaders\StringLoader());
	$html = $latte->renderToString("{form address}{input street, class => 'is-valid form-control'}{/form}", ['control' => $presenter]);
	Assert::contains('Maps.Factory.createSuggestion("suggestion-key");', $html);

	$html = (string) $form['street']->getControl();
	Assert::contains('document.getElementById("frm-address-street")', $html);
	Assert::contains('class="form-control"', $html);
	#rendered through the presenter's template factory
	Assert::true(Helpers::$templatesCreated > 0);
});
