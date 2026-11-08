<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Database;

class WilayahSeedData
{
    /**
     * @param string $kodeWilayah
     * @param string $namaKabupaten
     * @param string $namaKecamatan
     * @param float $lat
     * @param float $lng
     * @param float $delta
     * @return string
     */
    public static function buildBoundingPolygonGeojson(
        string $kodeWilayah,
        string $namaKabupaten,
        string $namaKecamatan,
        float $lat,
        float $lng,
        float $delta = 0.045
    ): string {
        $feature = [
            'type' => 'Feature',
            'properties' => [
                'kode_wilayah' => $kodeWilayah,
                'nama_kabupaten' => $namaKabupaten,
                'nama_kecamatan' => $namaKecamatan,
            ],
            'geometry' => [
                'type' => 'Polygon',
                'coordinates' => [
                    [
                        [round($lng - $delta, 8), round($lat - $delta, 8)],
                        [round($lng + $delta, 8), round($lat - $delta, 8)],
                        [round($lng + $delta, 8), round($lat + $delta, 8)],
                        [round($lng - $delta, 8), round($lat + $delta, 8)],
                        [round($lng - $delta, 8), round($lat - $delta, 8)],
                    ],
                ],
            ],
        ];

        return (string) json_encode($feature, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * @return array<int, array{
     *     kode_wilayah: string,
     *     nama_kabupaten: string,
     *     nama_kecamatan: string,
     *     latitude_pusat: float,
     *     longitude_pusat: float,
     *     polygon_geojson: string
     * }>
     */
    public static function getRows(): array
    {
        /** @var array<int, array{string, string, string, float, float}> $raw */
        $raw = [
            // Kabupaten Bulungan (10)
            ['65.01.01', 'Kabupaten Bulungan', 'Tanjung Palas', 2.83333300, 117.35000000],
            ['65.01.02', 'Kabupaten Bulungan', 'Tanjung Palas Barat', 2.70000000, 117.11666700],
            ['65.01.03', 'Kabupaten Bulungan', 'Tanjung Palas Utara', 2.95000000, 117.28333300],
            ['65.01.04', 'Kabupaten Bulungan', 'Tanjung Palas Timur', 2.76666700, 117.65000000],
            ['65.01.05', 'Kabupaten Bulungan', 'Tanjung Selor', 2.83750000, 117.36527800],
            ['65.01.06', 'Kabupaten Bulungan', 'Tanjung Palas Tengah', 2.93333300, 117.48333300],
            ['65.01.07', 'Kabupaten Bulungan', 'Peso', 2.73333300, 116.71666700],
            ['65.01.08', 'Kabupaten Bulungan', 'Peso Hilir', 2.78333300, 116.95000000],
            ['65.01.09', 'Kabupaten Bulungan', 'Sekatak', 3.11666700, 117.08333300],
            ['65.01.10', 'Kabupaten Bulungan', 'Bunyu', 3.46666700, 117.83333300],

            // Kabupaten Malinau (15 - Lokus Piloting Awal)
            ['65.02.01', 'Kabupaten Malinau', 'Mentarang', 3.48333300, 116.48333300],
            ['65.02.02', 'Kabupaten Malinau', 'Malinau Kota', 3.58333300, 116.63333300],
            ['65.02.03', 'Kabupaten Malinau', 'Pujungan', 2.61666700, 115.65000000],
            ['65.02.04', 'Kabupaten Malinau', 'Kayan Hilir', 2.18333300, 115.93333300],
            ['65.02.05', 'Kabupaten Malinau', 'Kayan Hulu', 1.81666700, 115.23333300],
            ['65.02.06', 'Kabupaten Malinau', 'Malinau Selatan', 3.30000000, 116.51666700],
            ['65.02.07', 'Kabupaten Malinau', 'Malinau Utara', 3.66666700, 116.65000000],
            ['65.02.08', 'Kabupaten Malinau', 'Malinau Barat', 3.55000000, 116.56666700],
            ['65.02.09', 'Kabupaten Malinau', 'Sungai Boh', 1.45000000, 115.08333300],
            ['65.02.10', 'Kabupaten Malinau', 'Kayan Selatan', 1.66666700, 115.11666700],
            ['65.02.11', 'Kabupaten Malinau', 'Bahau Hulu', 2.95000000, 115.81666700],
            ['65.02.12', 'Kabupaten Malinau', 'Mentarang Hulu', 3.75000000, 116.16666700],
            ['65.02.13', 'Kabupaten Malinau', 'Malinau Selatan Hilir', 3.41666700, 116.60000000],
            ['65.02.14', 'Kabupaten Malinau', 'Malinau Selatan Hulu', 3.10000000, 116.35000000],
            ['65.02.15', 'Kabupaten Malinau', 'Sungai Tubu', 3.26666700, 116.10000000],

            // Kabupaten Nunukan (21)
            ['65.03.01', 'Kabupaten Nunukan', 'Sebatik', 4.13333300, 117.86666700],
            ['65.03.02', 'Kabupaten Nunukan', 'Nunukan', 4.13333300, 117.66666700],
            ['65.03.03', 'Kabupaten Nunukan', 'Sembakung', 3.81666700, 117.16666700],
            ['65.03.04', 'Kabupaten Nunukan', 'Lumbis', 4.15000000, 116.63333300],
            ['65.03.05', 'Kabupaten Nunukan', 'Krayan', 3.88333300, 115.80000000],
            ['65.03.06', 'Kabupaten Nunukan', 'Sebuku', 4.05000000, 117.15000000],
            ['65.03.07', 'Kabupaten Nunukan', 'Krayan Selatan', 3.71666700, 115.76666700],
            ['65.03.08', 'Kabupaten Nunukan', 'Sebatik Barat', 4.15000000, 117.78333300],
            ['65.03.09', 'Kabupaten Nunukan', 'Nunukan Selatan', 4.05000000, 117.68333300],
            ['65.03.10', 'Kabupaten Nunukan', 'Sebatik Timur', 4.15000000, 117.90000000],
            ['65.03.11', 'Kabupaten Nunukan', 'Sebatik Utara', 4.16666700, 117.88333300],
            ['65.03.12', 'Kabupaten Nunukan', 'Sebatik Tengah', 4.13333300, 117.83333300],
            ['65.03.13', 'Kabupaten Nunukan', 'Sei Menggaris', 4.23333300, 117.43333300],
            ['65.03.14', 'Kabupaten Nunukan', 'Tulin Onsoi', 4.18333300, 117.06666700],
            ['65.03.15', 'Kabupaten Nunukan', 'Lumbis Ogong', 4.26666700, 116.45000000],
            ['65.03.16', 'Kabupaten Nunukan', 'Sembakung Atulai', 3.86666700, 116.95000000],
            ['65.03.17', 'Kabupaten Nunukan', 'Krayan Tengah', 3.80000000, 115.95000000],
            ['65.03.18', 'Kabupaten Nunukan', 'Krayan Timur', 3.95000000, 115.96666700],
            ['65.03.19', 'Kabupaten Nunukan', 'Krayan Barat', 3.85000000, 115.68333300],
            ['65.03.20', 'Kabupaten Nunukan', 'Lumbis Pansiangan', 4.31666700, 116.26666700],
            ['65.03.21', 'Kabupaten Nunukan', 'Lumbis Hulu', 4.28333300, 116.10000000],

            // Kabupaten Tana Tidung (5)
            ['65.04.01', 'Kabupaten Tana Tidung', 'Sesayap', 3.58333300, 117.01666700],
            ['65.04.02', 'Kabupaten Tana Tidung', 'Sesayap Hilir', 3.56666700, 117.28333300],
            ['65.04.03', 'Kabupaten Tana Tidung', 'Tana Lia', 3.68333300, 117.65000000],
            ['65.04.04', 'Kabupaten Tana Tidung', 'Betayau', 3.43333300, 117.06666700],
            ['65.04.05', 'Kabupaten Tana Tidung', 'Muruk Rian', 3.51666700, 116.85000000],

            // Kota Tarakan (4)
            ['65.71.01', 'Kota Tarakan', 'Tarakan Barat', 3.31666700, 117.58333300],
            ['65.71.02', 'Kota Tarakan', 'Tarakan Tengah', 3.31666700, 117.61666700],
            ['65.71.03', 'Kota Tarakan', 'Tarakan Timur', 3.28333300, 117.63333300],
            ['65.71.04', 'Kota Tarakan', 'Tarakan Utara', 3.38333300, 117.56666700],
        ];

        $rows = [];
        foreach ($raw as $item) {
            $rows[] = [
                'kode_wilayah' => $item[0],
                'nama_kabupaten' => $item[1],
                'nama_kecamatan' => $item[2],
                'latitude_pusat' => $item[3],
                'longitude_pusat' => $item[4],
                'polygon_geojson' => self::buildBoundingPolygonGeojson(
                    $item[0],
                    $item[1],
                    $item[2],
                    $item[3],
                    $item[4]
                ),
            ];
        }

        return $rows;
    }
}
