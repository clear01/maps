<?php
declare(strict_types = 1);

namespace Clear01\Maps\Models;

use Nette\Utils\Json;
use Stringable;
use function array_merge;

class Marker implements Stringable
{
	private array $data = [];


	/**
	 * @param array $data extra marker options, e.g. 'icon' or 'divIcon' (https://leafletjs.com/examples/custom-icons/)
	 */
	public function __construct(
		GpsCoords $gps,
		?string $title = null,
		array $data = []
	) {

		$this->setLatitude($gps->getLatitude());
		$this->setLongitude($gps->getLongitude());
		$this->setTitle($title);
		$this->addData($data);
	}


	public function setLatitude(float $latitude): void
	{
		$this->data['latitude'] = $latitude;
	}


	public function setLongitude(float $longitude): void
	{
		$this->data['longitude'] = $longitude;
	}


	public function addData(array $data = []): void
	{
		$this->data = array_merge($this->data, $data);
	}


	public function setTitle(?string $title): void
	{
		if ($title !== null && $title !== '') {
			$this->data['title'] = $title;
		}
	}


	public function getData(): array
	{
		return $this->data;
	}


	public function __toString(): string
	{
		return Json::encode($this->data);
	}
}
