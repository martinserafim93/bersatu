<?php

declare(strict_types=1);

namespace App\Application\Actions\Auth;

use App\Application\Actions\PageAction;
use App\Domain\User\Role;
use App\Domain\Wilayah\Wilayah;
use App\Domain\Wilayah\WilayahRepository;
use App\Infrastructure\Persistence\Database\WilayahSeedData;
use Psr\Http\Message\ResponseInterface as Response;
use Throwable;

final class RegisterPageAction extends PageAction
{
    protected function action(): Response
    {
        $wilayahOptions = $this->loadWilayahOptions();

        return $this->renderPage('auth/register.php', [
            'pageTitle' => 'Pendaftaran Akun - Portal BERSATU',
            'pageDescription' => 'Pendaftaran mandiri agen kerukunan lapangan dan masyarakat Kalimantan Utara '
                . 'dengan verifikasi pengaktifan akun oleh Super Admin.',
            'surfaceMode' => 'operate',
            'activePath' => '/auth/register',
            'wilayahOptions' => $wilayahOptions,
            'roleOptions' => [
                Role::MASYARAKAT => 'Masyarakat Terverifikasi',
                Role::PENYULUH_IPARI => 'Penyuluh Agama (IPARI)',
                Role::KUA => 'Kepala / Petugas KUA',
                Role::FKUB => 'Pengurus FKUB / Tokoh Agama & Adat',
            ],
            'pageScripts' => ['/assets/js/auth.js'],
        ]);
    }

    /**
     * @return array<int, array{id: int, nama_kabupaten: string, nama_kecamatan: string}>
     */
    private function loadWilayahOptions(): array
    {
        try {
            /** @var WilayahRepository $repo */
            $repo = $this->container->get(WilayahRepository::class);
            $items = $repo->findAll(false);
            if ($items !== []) {
                return array_map(
                    static fn (Wilayah $w): array => [
                        'id' => $w->getId(),
                        'nama_kabupaten' => $w->getNamaKabupaten(),
                        'nama_kecamatan' => $w->getNamaKecamatan(),
                    ],
                    $items
                );
            }
        } catch (Throwable) {
            // Gunakan referensi wilayah statis bila koneksi MySQL lokal belum aktif.
        }

        $options = [];
        foreach (WilayahSeedData::getRows() as $index => $row) {
            $options[] = [
                'id' => $index + 1,
                'nama_kabupaten' => $row['nama_kabupaten'],
                'nama_kecamatan' => $row['nama_kecamatan'],
            ];
        }

        return $options;
    }
}
