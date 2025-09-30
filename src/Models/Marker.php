<?php
declare(strict_types = 1);

namespace Clear01\Maps\Models;

use Clear01\Maps\Exceptions\MapException;
use Nette\Utils\ArrayHash;
use Nette\Utils\Json;

class Marker
{
	private $data = [];


	/**
	 * @param array $icon https://leafletjs.com/examples/custom-icons/
	 */
	public function __construct(
		GpsCoords $gps,
		?string $title = null,
		array $icon = []
	) {
		$this->data = new ArrayHash();
		$this->setLatitude($gps->getLatitude());
		$this->setLongitude($gps->getLongitude());
		$this->setTitle($title);
		$this->setIcon($icon);
	}


	public function setLatitude(float $latitude): void
	{
		$this->data->latitude = $latitude;
	}


	public function setLongitude(float $longitude): void
	{
		$this->data->longitude = $longitude;
	}


	public function setIcon(array $icon = []): void
	{
		$this->data->icon = $icon;
	}


	public function setTitle(?string $title): void
	{
		$title == null ?: $this->data->title = $title;
	}


	public function __toString(): string
	{
		return Json::encode($this->data);
	}
}
