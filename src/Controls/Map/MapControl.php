<?php
declare(strict_types = 1);

namespace Clear01\Maps\Controls\Map;

use Clear01\Maps\Models\Marker;
use Clear01\Maps\Models\Settings;
use Nette\Application\UI\Control;
use function rand;

class MapControl extends Control
{

	const TEMPLATE = __DIR__ . '/template.latte';

	/**
	 * @var Marker[]
	 */
	protected $markers = [];


	protected $settings;


	public function __construct(string $apiKey, array $settings = [])
	{

		$this->settings = new Settings(['apiKey' => $apiKey] + $settings);
	}


	public function addMarker(Marker $marker)
	{
		$this->settings->markers[] = (string) $marker;
	}


	public function updateSettings(string $key, $value)
	{
		$this->settings[$key] = $value;
	}

	public function getIdentifier()
	{
		return "map-" . $this->getUniqueId() . '-' . rand(1, 1000000);
	}


	public function render()
	{
		$this->template->identifier = $this->getIdentifier();
		$this->template->settings = $this->settings;
		$this->template->setFile(self::TEMPLATE);
		$this->template->render();
	}
}
