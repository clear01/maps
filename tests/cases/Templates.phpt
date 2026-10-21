<?php
declare(strict_types = 1);

use Clear01\Maps\Tests\Helpers;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

#every template compiles in Latte 3 with Nette UI and Forms extensions
$latte = Helpers::createLatteFactory()->create();
$files = glob(__DIR__ . '/../../src/Controls/*/template.latte');
Assert::count(3, $files);

foreach ($files as $file) {
	$code = $latte->compile($file);
	Assert::contains('final class Template', $code, $file);
	#lint: no deprecations or warnings
	Assert::noError(fn() => $latte->compile($file));
}

Assert::same(Latte\Engine::VersionId >= 30018, true);
