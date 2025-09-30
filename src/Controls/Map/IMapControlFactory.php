<?php
declare(strict_types = 1);

namespace Clear01\Maps\Controls\Map;

use Clear01\Maps\Controls\Map\MapControl;

interface IMapControlFactory
{

	public function create(array $settings = []): MapControl;
}
