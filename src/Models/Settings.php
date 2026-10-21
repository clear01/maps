<?php declare(strict_types = 1);

namespace Clear01\Maps\Models;

use Nette\Utils\ArrayHash;

/**
 * Map settings passed to the JS driver (serialized to JSON in templates).
 * Every key is a public (dynamic) property of the ArrayHash.
 */
class Settings extends ArrayHash
{

	public const DEFAULTS = [
		"width"       => '100%',
		"height"      => '600px',
		"center"      => [
			"latitude"  => 49.75,
			"longitude" => 15.47,
		],
		"defaultZoom" => 7,
		"maxZoom"     => 12,
		"markers"     => [],
	];


	public function __construct(array $settings = [])
	{
		foreach ($settings + self::DEFAULTS as $key => $value) {
			$this->$key = $value;
		}
	}
}
