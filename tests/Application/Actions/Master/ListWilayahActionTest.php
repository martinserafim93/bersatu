<?php

declare(strict_types=1);

namespace Tests\Application\Actions\Master;

use App\Application\Actions\ActionPayload;
use App\Domain\Wilayah\Wilayah;
use App\Domain\Wilayah\WilayahRepository;
use DI\Container;
use Tests\TestCase;

class ListWilayahActionTest extends TestCase
{
    public function testListAllWilayahSummary(): void
    {
        $app = $this->getAppInstance();

        /** @var Container $container */
        $container = $app->getContainer();

        $wilayah = new Wilayah(
            1,
            '65.01.01',
            'Kabupaten Bulungan',
            'Tanjung Palas',
            2.833333,
            117.35,
            '{"type":"Feature"}'
        );

        $repo = $this->prophesize(WilayahRepository::class);
        $repo->findAll(false)->willReturn([$wilayah])->shouldBeCalledOnce();
        $repo->listKabupaten()->willReturn(['Kabupaten Bulungan'])->shouldBeCalledOnce();

        $container->set(WilayahRepository::class, $repo->reveal());

        $request = $this->createRequest('GET', '/api/master/wilayah');
        $response = $app->handle($request);

        $this->assertSame(200, $response->getStatusCode());

        $payload = (string) $response->getBody();
        $expectedPayload = new ActionPayload(200, [
            'total' => 1,
            'kabupaten_list' => ['Kabupaten Bulungan'],
            'items' => [$wilayah->toSummaryArray()],
        ]);

        $this->assertEquals(json_encode($expectedPayload, JSON_PRETTY_PRINT), $payload);
    }

    public function testListWilayahFilterByKabupatenWithGeojson(): void
    {
        $app = $this->getAppInstance();

        /** @var Container $container */
        $container = $app->getContainer();

        $wilayah = new Wilayah(
            11,
            '65.02.01',
            'Kabupaten Malinau',
            'Mentarang',
            3.483333,
            116.483333,
            '{"type":"Feature"}'
        );

        $repo = $this->prophesize(WilayahRepository::class);
        $repo->findByKabupaten('Kabupaten Malinau', true)->willReturn([$wilayah])->shouldBeCalledOnce();
        $repo->listKabupaten()->willReturn(['Kabupaten Malinau'])->shouldBeCalledOnce();

        $container->set(WilayahRepository::class, $repo->reveal());

        $request = $this->createRequest('GET', '/api/master/wilayah')
            ->withQueryParams([
                'kabupaten' => 'Kabupaten Malinau',
                'include_geojson' => '1',
            ]);
        $response = $app->handle($request);

        $this->assertSame(200, $response->getStatusCode());

        $payload = (string) $response->getBody();
        $expectedPayload = new ActionPayload(200, [
            'total' => 1,
            'kabupaten_list' => ['Kabupaten Malinau'],
            'items' => [$wilayah->jsonSerialize()],
        ]);

        $this->assertEquals(json_encode($expectedPayload, JSON_PRETTY_PRINT), $payload);
    }
}
