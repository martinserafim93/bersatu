<?php

declare(strict_types=1);

namespace Tests\Application\Actions\Master;

use App\Application\Actions\ActionPayload;
use App\Domain\KategoriIsu\KategoriIsu;
use App\Domain\KategoriIsu\KategoriIsuRepository;
use DI\Container;
use Tests\TestCase;

class ListKategoriIsuActionTest extends TestCase
{
    public function testListAllKategoriIsu(): void
    {
        $app = $this->getAppInstance();

        /** @var Container $container */
        $container = $app->getContainer();

        $kategori = new KategoriIsu(1, 'Pendirian Rumah Ibadah', 'Deskripsi...');

        $repo = $this->prophesize(KategoriIsuRepository::class);
        $repo->findAll()->willReturn([$kategori])->shouldBeCalledOnce();

        $container->set(KategoriIsuRepository::class, $repo->reveal());

        $request = $this->createRequest('GET', '/api/master/kategori-isu');
        $response = $app->handle($request);

        $this->assertSame(200, $response->getStatusCode());

        $payload = (string) $response->getBody();
        $expectedPayload = new ActionPayload(200, [$kategori]);

        $this->assertEquals(json_encode($expectedPayload, JSON_PRETTY_PRINT), $payload);
    }
}
