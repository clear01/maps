<?php
declare(strict_types = 1);

namespace Clear01\Maps\Controls\Map;

use Clear01\Maps\Models\Marker;
use Clear01\Maps\Models\Settings;
use Nette\Application\UI\Control;
use function random_int;

class MapControl extends Control
{

	public const TEMPLATE = __DIR__ . '/template.latte';

	protected Settings $settings;


	public function __construct(string $apiKey, array $settings = [])
	{
		$this->settings = new Settings(['apiKey' => $apiKey] + $settings);
	}


	public function addMarker(Marker $marker): void
	{
		$markers = $this->settings->markers;
		$markers[] = (string) $marker;
		$this->settings->markers = $markers;
	}


	public function updateSettings(string $key, mixed $value): void
	{
		$this->settings[$key] = $value;
	}


	public function getSettings(): Settings
	{
		return $this->settings;
	}


	public function getIdentifier(): string
	{
		return "map-" . $this->getUniqueId() . '-' . random_int(1, 1000000);
	}


	public function render(): void
	{
		$template = $this->template;
		$template->identifier = $this->getIdentifier();
		$template->settings = $this->settings;
		$template->setFile(self::TEMPLATE);
		$template->render();
	}
}
