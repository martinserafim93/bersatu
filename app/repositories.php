<?php

declare(strict_types=1);

use App\Domain\KategoriIsu\KategoriIsuRepository;
use App\Domain\User\UserRepository;
use App\Domain\Wilayah\WilayahRepository;
use App\Infrastructure\Persistence\KategoriIsu\PdoKategoriIsuRepository;
use App\Infrastructure\Persistence\User\InMemoryUserRepository;
use App\Infrastructure\Persistence\Wilayah\PdoWilayahRepository;
use DI\ContainerBuilder;

return function (ContainerBuilder $containerBuilder) {
    // Here we map our UserRepository interface to its in memory implementation
    $containerBuilder->addDefinitions([
        UserRepository::class => \DI\autowire(InMemoryUserRepository::class),
        KategoriIsuRepository::class => \DI\autowire(PdoKategoriIsuRepository::class),
        WilayahRepository::class => \DI\autowire(PdoWilayahRepository::class),
    ]);
};
