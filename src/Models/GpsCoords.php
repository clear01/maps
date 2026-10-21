<?php
declare(strict_types = 1);

namespace Clear01\Maps\Models;

use Clear01\Maps\Exceptions\MapException;
use Stringable;
use function count;
use function is_numeric;
use function preg_split;
use function rtrim;
use function trim;

class GpsCoords implements Stringable
{

	protected float $latitude;

	protected float $longitude;


	public function __construct(
		float $latitude,
		float $longitude
	) {
		$this->setLatitude($latitude);
		$this->setLongitude($longitude);
	}


	/**
	 * Parses "latitude longitude" separated by whitespace (optional N / E suffix).
	 * Returns null for an empty or malformed string, throws MapException for values out of range.
	 */
	public static function fromString(?string $value): ?self
	{
		$value = trim((string) $value);
		if ($value === '') {
			return null;
		}
		$parts = preg_split('~\s+~', $value);
		if ($parts === false || count($parts) !== 2) {
			return null;
		}
		$latitude = rtrim($parts[0], 'Nn');
		$longitude = rtrim($parts[1], 'Ee');
		if (!is_numeric($latitude) || !is_numeric($longitude)) {
			return null;
		}

		return new self((float) $latitude, (float) $longitude);
	}


	public function setLatitude(float $latitude): static
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


	public function setLongitude(float $longitude): static
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
