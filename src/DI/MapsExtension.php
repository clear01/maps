<?php
declare(strict_types = 1);

namespace Clear01\Maps\DI;

use Clear01\Maps\Controls\AddressSuggestion\AddressSuggestion;
use Clear01\Maps\Controls\GpsPicker\GpsPicker;
use Clear01\Maps\Controls\Map\IMapControlFactory;
use Nette\DI\CompilerExtension;
use Nette\Schema\Expect;
use Nette\Schema\Schema;

/**
 * Registers IMapControlFactory and form methods addGpsPicker() / addAddressSuggestion().
 *
 * @property-read \stdClass $config
 */
class MapsExtension extends CompilerExtension
{

	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'mapApiKey' => Expect::string(''),
			'suggestionApiKey' => Expect::string(''),
		]);
	}


	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();
		$builder->addFactoryDefinition($this->prefix('factory'))
		        ->setImplement(IMapControlFactory::class)
		        ->getResultDefinition()
		        ->setArguments(['apiKey' => $this->config->mapApiKey]);

		#extension methods are registered when the container is initialized
		$this->initialization->addBody(
			'\\' . GpsPicker::class . '::register(?, ?);', [$this->config->mapApiKey, $this->config->suggestionApiKey]
		);
		$this->initialization->addBody(
			'\\' . AddressSuggestion::class . '::register(?);', [$this->config->suggestionApiKey]
		);
	}
}
