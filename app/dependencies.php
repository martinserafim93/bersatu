<?php

declare(strict_types=1);

use App\Application\Settings\SettingsInterface;
use App\Infrastructure\Persistence\Database\PdoConnectionFactory;
use App\Infrastructure\Security\IdentityCipher;
use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        PDO::class => function (ContainerInterface $c): PDO {
            /** @var SettingsInterface $settings */
            $settings = $c->get(SettingsInterface::class);
            /** @var array<string, mixed> $dbSettings */
            $dbSettings = $settings->get('db');

            return (new PdoConnectionFactory())->create($dbSettings);
        },
        IdentityCipher::class => function (ContainerInterface $c): IdentityCipher {
            /** @var SettingsInterface $settings */
            $settings = $c->get(SettingsInterface::class);
            /** @var array<string, mixed> $security */
            $security = $settings->get('security');

            return new IdentityCipher((string) ($security['app_key'] ?? ''));
        },
        LoggerInterface::class => function (ContainerInterface $c) {
            $settings = $c->get(SettingsInterface::class);

            $loggerSettings = $settings->get('logger');
            $logger = new Logger($loggerSettings['name']);

            $processor = new UidProcessor();
            $logger->pushProcessor($processor);

            $handler = new StreamHandler($loggerSettings['path'], $loggerSettings['level']);
            $logger->pushHandler($handler);

            return $logger;
        },
    ]);
};
