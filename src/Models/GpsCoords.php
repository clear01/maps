<?php
declare(strict_types = 1);

namespace Clear01\Maps\Models;

use Clear01\Maps\Exceptions\MapException;
use Stringable;

class GpsCoords implements Stringable
{

	protected $latitude;

	protected $longitude;


	public function __construct(
		float $latitude,
		float $longitude

	) {
		$this->setLatitude($latitude);
		$this->setLongitude($longitude);
	}


	public function setLatitude(float $latitude)
	{
		if ($latitude < -90 || $latitude > 90) {
			throw new MapException(
				"ERROR: Invalid latitude",
				MapException::INVALID_LATITUDE
			);
		}
		$this->latitude = $latitude;

		return $this;
	}


	public function setLongitude(float $longitude)
	{
		if ($longitude < -180 || $longitude > 180) {
			throw new MapException(
				"ERROR: Invalid longitude",
				MapException::INVALID_LONGITUDE
			);
		}
		$this->longitude = $longitude;

		return $this;
	}


	public function getLatitude(): float
	{
		return $this->latitude;
	}


	public function getLongitude(): float
	{
		return $this->longitude;
	}




	public function __toString(): string
	{
		return $this->latitude . ' ' . $this->longitude;
	}
}
