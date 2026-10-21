<?php
declare(strict_types = 1);

use Clear01\Maps\Controls\AddressSuggestion\AddressSuggestion;
use Clear01\Maps\Controls\GpsPicker\GpsPicker;
use Clear01\Maps\Controls\Map\IMapControlFactory;
use Clear01\Maps\Controls\Map\MapControl;
use Clear01\Maps\DI\MapsExtension;
use Nette\DI\Compiler;
use Nette\DI\ContainerLoader;
use Nette\Forms\Form;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

$loader = new ContainerLoader(TEMP_DIR . '/container', true);
$class = $loader->load(function (Compiler $compiler): void {
	$compiler->addExtension('maps', new MapsExtension());
	$compiler->addConfig([
		'maps' => [
			'mapApiKey' => 'map-key',
			'suggestionApiKey' => 'suggestion-key',
		],
	]);
});

/** @var Nette\DI\Container $container */
$container = new $class();
$container->initialize();

$factory = $container->getByType(IMapControlFactory::class);
Assert::type(IMapControlFactory::class, $factory);
Assert::same($factory, $container->getService('maps.factory'));

$control = $factory->create(['height' => '300px']);
Assert::type(MapControl::class, $control);
Assert::same('map-key', $control->getSettings()->apiKey);
Assert::same('300px', $control->getSettings()->height);
Assert::notSame($control, $factory->create());

#extension methods registered by container initialization
$form = new Form();
$picker = $form->addGpsPicker('gps', 'Location');
$suggestion = $form->addAddressSuggestion('street', 'Street', 100);
Assert::type(GpsPicker::class, $picker);
Assert::type(AddressSuggestion::class, $suggestion);
Assert::same($picker, $form['gps']);
Assert::contains('"apiKey":"map-key"', (string) $picker->getControl());
Assert::contains('"suggestionApiKey":"suggestion-key"', (string) $picker->getControl());
Assert::contains('createSuggestion("suggestion-key")', (string) $suggestion->getControl());

#unknown option is rejected by the schema
Assert::exception(function (): void {
	$compiler = new Compiler();
	$compiler->addExtension('maps', new MapsExtension());
	$compiler->addConfig(['maps' => ['unknown' => 1]]);
	$compiler->compile();
}, Nette\DI\InvalidConfigurationException::class, "~Unexpected item 'maps.+unknown'~");

#defaults
$compiler = new Compiler();
$compiler->addExtension('maps', new MapsExtension());
Assert::contains("GpsPicker::register('', '')", $compiler->compile());
