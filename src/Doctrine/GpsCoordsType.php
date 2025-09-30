<?php
declare(strict_types = 1);

namespace Clear01\Maps\Doctrine;

use Clear01\Maps\Models\GpsCoords;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;
use function count;
use function explode;
use function is_string;

class GpsCoordsType extends StringType
{
	public const NAME = 'gps_coords';

	public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
	{
		$fieldDeclaration['length'] = 50;
		return $platform->getVarcharTypeDeclarationSQL($fieldDeclaration);
	}

	public function convertToPHPValue($value, AbstractPlatform $platform): ?GpsCoords
	{
		if (is_string($value) && !empty($value)) {
			$parts = explode(" ", $value);
			if (count($parts) != 2) {
				return null;
			}

			return new GpsCoords((float) $parts[0], (float) $parts[1]);
		}
		return null;
	}


	public function convertToDatabaseValue($value, AbstractPlatform $platform): string
	{
		return (string) $value;
	}


	public function getName()
	{
		return self::NAME;
	}
}
