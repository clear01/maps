<?php
declare(strict_types = 1);

namespace Clear01\Maps\Doctrine;

use Doctrine\ORM\Mapping as ORM;
use Clear01\Maps\Models\GpsCoords;

trait GpsTrait
{
	/**
	 * @var string|null
	 * @ORM\Column(name="latitude", type="decimal", precision=20, scale=16, nullable=true)
	 */
	protected $latitude;

	/**
	 * @var string|null
	 * @ORM\Column(name="longitude", type="decimal", precision=20, scale=16, nullable=true)
	 */
	protected $longitude;


	public function getGps(): ?GpsCoords
	{
		return $this->longitude && $this->latitude ? new GpsCoords((float) $this->latitude, (float) $this->longitude) : null;
	}

	public function setGps(?GpsCoords $gps): void
	{
		$this->longitude = $gps ? (string) $gps->getLongitude() : null;
		$this->latitude = $gps ? (string) $gps->getLatitude() : null;
	}

	public function getLatitude(): ?float
	{
		return $this->getGps() ? $this->getGps()->getLatitude() : null;
	}


	public function getLongitude(): ?float
	{
		return $this->getGps() ? $this->getGps()->getLongitude() : null;
	}
}