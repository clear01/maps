<?php
declare(strict_types = 1);

namespace Clear01\Maps\Exceptions;

use InvalidArgumentException;

/**
 * Thrown for invalid map input (e.g. GPS coordinates out of range).
 * The class was referenced since 0.1.0 but was missing in the package.
 */
class MapException extends InvalidArgumentException
{
	public const INVALID_LATITUDE = 1;

	public const INVALID_LONGITUDE = 2;
}
