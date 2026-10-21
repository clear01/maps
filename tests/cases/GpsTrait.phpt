<?php
declare(strict_types = 1);

use Clear01\Maps\Doctrine\GpsTrait;
use Clear01\Maps\Models\GpsCoords;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

#[ORM\Entity]
class Place
{
	use GpsTrait;

	#[ORM\Id]
	#[ORM\Column(type: 'integer')]
	private ?int $id = null;
}

$place = new Place();
Assert::null($place->getGps());
Assert::null($place->getLatitude());

$place->setGps(new GpsCoords(49.5, 16.25));
Assert::equal(new GpsCoords(49.5, 16.25), $place->getGps());
Assert::same(49.5, $place->getLatitude());
Assert::same(16.25, $place->getLongitude());

$place->setGps(null);
Assert::null($place->getGps());

#attribute mapping of the trait columns (ORM 2.20)
$metadata = new ClassMetadata(Place::class);
(new AttributeDriver([__DIR__]))->loadMetadataForClass(Place::class, $metadata);
foreach (['latitude', 'longitude'] as $field) {
	$mapping = $metadata->getFieldMapping($field);
	Assert::same('decimal', $mapping['type']);
	Assert::same($field, $mapping['columnName']);
	Assert::same(20, $mapping['precision']);
	Assert::same(16, $mapping['scale']);
	Assert::true($mapping['nullable']);
}
