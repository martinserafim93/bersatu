<?php

declare(strict_types=1);

use App\Domain\Home\HomeSummaryRepository;
use App\Domain\KategoriIsu\KategoriIsuRepository;
use App\Domain\User\UserRepository;
use App\Domain\Wilayah\WilayahRepository;
use App\Infrastructure\Persistence\Home\PdoHomeSummaryRepository;
use App\Infrastructure\Persistence\KategoriIsu\PdoKategoriIsuRepository;
use App\Infrastructure\Persistence\User\PdoUserRepository;
use App\Infrastructure\Persistence\Wilayah\PdoWilayahRepository;
use DI\ContainerBuilder;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        HomeSummaryRepository::class => \DI\autowire(PdoHomeSummaryRepository::class),
        UserRepository::class => \DI\autowire(PdoUserRepository::class),
        KategoriIsuRepository::class => \DI\autowire(PdoKategoriIsuRepository::class),
        WilayahRepository::class => \DI\autowire(PdoWilayahRepository::class),
    ]);
};
