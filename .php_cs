<?php

$config = new Prooph\CS\Config\Prooph();
$config->getFinder()
    ->in(__DIR__)
    ->exclude('var');

$cacheDir = getenv('TRAVIS') ? getenv('HOME') . '/.php-cs-fixer' : __DIR__;

$config->setCacheFile($cacheDir . '/.php_cs.cache');

return $config;
