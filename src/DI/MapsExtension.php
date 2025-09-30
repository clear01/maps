<?php
declare(strict_types = 1);

namespace Clear01\Maps\DI;

use Clear01\Maps\Controls\Map\IMapControlFactory;
use Nette\DI\CompilerExtension;
use Nette\PhpGenerator\ClassType;

class MapsExtension extends CompilerExtension
{

	private $defaults = [
		'mapApiKey' => '',
		'suggestionApiKey' => '',
	];


	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$builder->addDefinition($this->prefix('factory'))
		        ->setImplement(IMapControlFactory::class)->setArguments(['apiKey' => $this->config['mapApiKey']]);

	}


	public function afterCompile(ClassType $class)
	{
		$initialize = $class->methods['initialize'];

		$initialize->addBody(
			'Clear01\\Maps\\Controls\\GpsPicker\\GpsPicker::register(?,?);', [$this->config['mapApiKey'], $this->config['suggestionApiKey']]
		);

		$initialize->addBody(
			'Clear01\\Maps\\Controls\\AddressSuggestion\\AddressSuggestion::register(?);', [$this->config['suggestionApiKey']]
		);
	}
}
