<?php
declare(strict_types = 1);

namespace Clear01\Maps\Doctrine;

use Clear01\Maps\Exceptions\MapException;
use Clear01\Maps\Models\GpsCoords;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\StringType;
use function is_string;

/**
 * Stores GpsCoords as "latitude longitude" in VARCHAR(50). Requires doctrine/dbal ^3.10.
 */
class GpsCoordsType extends StringType
{
	public const NAME = 'gps_coords';


	public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
	{
		$column['length'] = 50;

		return $platform->getStringTypeDeclarationSQL($column);
	}


	public function convertToPHPValue($value, AbstractPlatform $platform): ?GpsCoords
	{
		if ($value instanceof GpsCoords) {
			return $value;
		}
		if (!is_string($value) || $value === '') {
			return null;
		}
		try {
			return GpsCoords::fromString($value);
		} catch (MapException $e) {
			#out of range value in the database is treated as missing
			return null;
		}
	}


	public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
	{
		if ($value === null || $value === '') {
			return null;
		}
		if ($value instanceof GpsCoords) {
			return (string) $value;
		}
		if (is_string($value)) {
			try {
				$gps = GpsCoords::fromString($value);
			} catch (MapException $e) {
				$gps = null;
			}
			if ($gps !== null) {
				return (string) $gps;
			}
		}

		throw ConversionException::conversionFailedInvalidType($value, self::NAME, ['null', GpsCoords::class, 'string']);
	}


	public function getName(): string
	{
		return self::NAME;
	}


	public function requiresSQLCommentHint(AbstractPlatform $platform): bool
	{
		return true;
	}
}
