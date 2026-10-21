<?php
declare(strict_types = 1);

require __DIR__ . '/../vendor/autoload.php';

Tester\Environment::setup();
Tester\Environment::setupFunctions();
date_default_timezone_set('Europe/Prague');

#every test process gets its own temp directory
define('TEMP_DIR', __DIR__ . '/temp/' . getmypid());
@mkdir(dirname(TEMP_DIR));
Tester\Helpers::purge(TEMP_DIR);

require __DIR__ . '/Helpers.php';
