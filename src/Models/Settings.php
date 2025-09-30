<?php declare(strict_types = 1);

namespace Clear01\Maps\Models;

use Nette\Utils\ArrayHash;

class Settings extends ArrayHash
{

	protected $settings = [
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
		foreach ($this->settings as $key => $value) {
			$this->$key = $value;
		}
		foreach ($settings as $key => $value) {
			$this->$key = $value;
		}
	}



}
