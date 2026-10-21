<?php
declare(strict_types = 1);

use Clear01\Maps\Doctrine\GpsCoordsType;
use Clear01\Maps\Models\GpsCoords;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

#any DBAL deprecation triggered by the type fails the test
Doctrine\Deprecations\Deprecation::enableWithTriggerError();

Type::addType(GpsCoordsType::NAME, GpsCoordsType::class);
$type = Type::getType(GpsCoordsType::NAME);
$platform = new MySQLPlatform();

Assert::type(GpsCoordsType::class, $type);
Assert::same('gps_coords', $type->getName());
Assert::same('gps_coords', Type::getTypeRegistry()->lookupName($type));
Assert::same('VARCHAR(50)', $type->getSQLDeclaration([], $platform));
Assert::true($type->requiresSQLCommentHint($platform));

#PHP -> DB
Assert::same('49.19523 16.60796', $type->convertToDatabaseValue(new GpsCoords(49.19523, 16.60796), $platform));
Assert::same('49.1 16.2', $type->convertToDatabaseValue('49.1  16.2', $platform));
Assert::null($type->convertToDatabaseValue(null, $platform));
Assert::null($type->convertToDatabaseValue('', $platform));
Assert::exception(fn() => $type->convertToDatabaseValue('nonsense', $platform), ConversionException::class);
Assert::exception(fn() => $type->convertToDatabaseValue(123, $platform), ConversionException::class);

#DB -> PHP
Assert::equal(new GpsCoords(49.19523, 16.60796), $type->convertToPHPValue('49.19523 16.60796', $platform));
Assert::null($type->convertToPHPValue(null, $platform));
Assert::null($type->convertToPHPValue('', $platform));
Assert::null($type->convertToPHPValue('broken', $platform));
Assert::null($type->convertToPHPValue('100 200', $platform));

#round trip
$gps = new GpsCoords(-12.345678901234, 179.999999);
Assert::equal($gps, $type->convertToPHPValue($type->convertToDatabaseValue($gps, $platform), $platform));
