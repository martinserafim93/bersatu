<?php

require __DIR__ . '/../vendor/autoload.php';

// Pre-load PHP-DI classes and trigger autowire to avoid PHP 8.4 deprecation notices during test execution
$builder = new \DI\ContainerBuilder();
$builder->addDefinitions([
    'dummy' => \DI\autowire(\stdClass::class),
]);
$c = $builder->build();
try {
    $c->get('dummy');
} catch (\Throwable) {
}
