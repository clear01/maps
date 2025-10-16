<?php
declare(strict_types = 1);

namespace Clear01\Maps\Models;

use Clear01\Maps\Exceptions\MapException;
use Nette\Utils\Json;
use function array_merge;
use const JSON_OBJECT_AS_ARRAY;

class Marker
{
	private $data = [];


	/**
	 * @param array $icon https://leafletjs.com/examples/custom-icons/
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
		$title == null ?: $this->data['title'] = $title;
	}


	public function __toString(): string
	{
		return Json::encode($this->data, JSON_OBJECT_AS_ARRAY);
	}
}
