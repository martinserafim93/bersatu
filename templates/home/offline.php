<?php

declare(strict_types=1);

?>
<section class="container">
    <div class="auth-shell">
        <h1>Perangkat Sedang Offline</h1>
        <p class="auth-intro">
            Koneksi internet tidak terdeteksi di lokasi Anda (wilayah 3TP / blank spot).
            Halaman yang belum tersimpan di perangkat tidak dapat dimuat saat ini.
        </p>

        <div class="form-feedback is-warning" role="status">
            <strong>Kesiapan Layanan Offline:</strong> Akses ulang beranda setelah sinyal tersedia untuk
            memperbarui data status kondusivitas 5 Kabupaten/Kota dan agenda kerukunan terbaru.
        </div>

        <div class="hero-actions">
            <a href="/" class="btn btn-primary">Buka Beranda Tersimpan</a>
            <button type="button" id="btn-reload-page" class="btn btn-secondary">
                Muat Ulang Halaman
            </button>
        </div>
    </div>
</section>
