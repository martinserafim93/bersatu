<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Database;

class KategoriIsuSeedData
{
    /**
     * @return array<int, array{id: int, nama_kategori: string, deskripsi: string}>
     */
    public static function getRows(): array
    {
        return [
            [
                'id' => 1,
                'nama_kategori' => 'Pendirian Rumah Ibadah',
                'deskripsi' => 'Potensi sengketa, keberatan warga, atau kendala administratif terkait pendirian,'
                    . ' renovasi, dan alih fungsi rumah ibadah mengacu pada PBM No. 9 dan No. 8 Tahun 2006.',
            ],
            [
                'id' => 2,
                'nama_kategori' => 'Gesekan Antarumat/Ormas SARA',
                'deskripsi' => 'Ketegangan horizontal antarindividu, kelompok masyarakat, atau organisasi'
                    . ' kemasyarakatan yang bermuatan suku, agama, ras, dan antargolongan.',
            ],
            [
                'id' => 3,
                'nama_kategori' => 'Penyiaran Agama/Penistaan',
                'deskripsi' => 'Polemik materi dakwah/ceramah provokatif, penyiaran agama kepada pemeluk agama lain'
                    . ' yang menimbulkan keresahan, atau dugaan penodaan simbol keagamaan.',
            ],
            [
                'id' => 4,
                'nama_kategori' => 'Aliran Keagamaan Baru',
                'deskripsi' => 'Kemunculan atau aktivitas kelompok/paham keagamaan baru yang menimbulkan keresahan'
                    . ' sosial dan membutuhkan pembinaan serta dialog lintas pihak.',
            ],
            [
                'id' => 5,
                'nama_kategori' => 'Hoaks Transnasional Perbatasan',
                'deskripsi' => 'Penyebaran informasi palsu (hoaks), ujaran kebencian, atau infiltrasi paham'
                    . ' intoleran lintas batas negara di wilayah perbatasan Kalimantan Utara.',
            ],
        ];
    }

    /**
     * @return array<int, array{id: int, nama_kategori: string, slug: string}>
     */
    public static function getKategoriMediaRows(): array
    {
        return [
            [
                'id' => 1,
                'nama_kategori' => 'Moderasi Beragama',
                'slug' => 'moderasi-beragama',
            ],
            [
                'id' => 2,
                'nama_kategori' => 'Ekoteologi',
                'slug' => 'ekoteologi',
            ],
            [
                'id' => 3,
                'nama_kategori' => 'Regulasi PBM 2006',
                'slug' => 'regulasi-pbm-2006',
            ],
            [
                'id' => 4,
                'nama_kategori' => 'Harmoni Perbatasan',
                'slug' => 'harmoni-perbatasan',
            ],
        ];
    }
}
