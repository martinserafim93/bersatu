<?php

declare(strict_types=1);

use App\Application\Views\PhpRenderer;
use App\Domain\Home\PublicCondusivityStatus;
use App\Domain\User\Role;
use App\Domain\User\User;

/** @var array<int, array<string, mixed>> $kabupatenSummary */
/** @var array<int, array<string, mixed>> $agendaTicker */
/** @var array<int, array<string, mixed>> $mediaHighlights */
/** @var User|null $currentUser */

$kabList = $kabupatenSummary ?? [];
$agendaList = $agendaTicker ?? [];
$mediaList = $mediaHighlights ?? [];
$userRole = ($currentUser ?? null) instanceof User ? $currentUser->getRole() : null;

$hijauCount = 0;
$totalKecamatan = 0;
foreach ($kabList as $item) {
    $totalKecamatan += (int) ($item['jumlah_kecamatan'] ?? 0);
    if (($item['status_kerawanan'] ?? PublicCondusivityStatus::HIJAU) === PublicCondusivityStatus::HIJAU) {
        $hijauCount++;
    }
}

$tipeKontenLabels = [
    'video' => 'Video Tokoh',
    'animasi' => 'Animasi Edukasi',
    'infografis' => 'Infografis',
    'podcast' => 'Podcast',
    'naskah_khotbah' => 'Naskah Khotbah',
];
?>
<section class="ticker-bar" aria-label="Agenda Lintas Iman Terkini">
    <div class="container ticker-inner">
        <span class="ticker-label">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <rect x="3" y="4" width="18" height="18" rx="2"/>
                <path d="M16 2v4M8 2v4M3 10h18"/>
            </svg>
            <span>Kabar &amp; Agenda Lintas Iman</span>
        </span>
        <ul class="ticker-list">
            <?php foreach ($agendaList as $agenda) : ?>
                <li class="ticker-item">
                    <strong><?= PhpRenderer::e((string) ($agenda['judul_agenda'] ?? '')) ?></strong>
                    - <?= PhpRenderer::e((string) ($agenda['nama_kabupaten'] ?? '')) ?>
                    (<span class="tabular-num"><?= PhpRenderer::e(substr((string) ($agenda['tanggal_mulai'] ?? ''), 0, 10)) ?></span>)
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>

<section class="hero-persuade">
    <div class="container hero-layout">
        <div class="hero-copy">
            <h1>Merajut Harmoni, Menjaga Kedamaian di Beranda Batas NKRI</h1>
            <p class="hero-lead">
                Portal BERSATU menyatukan Early Warning System (EWS) kerukunan umat beragama dan promosi moderasi
                berbasis ekoteologi di Provinsi Kalimantan Utara, siap mendukung pelaporan cepat hingga wilayah 3TP.
            </p>
            <div class="hero-badges">
                <span class="hero-pill">Peringkat #1 Nasional IKUB 2025</span>
                <span class="hero-pill">5 Kabupaten/Kota &amp; 55 Kecamatan</span>
                <span class="hero-pill">Lokus Piloting: Kabupaten Malinau</span>
            </div>
            <div class="hero-actions">
                <?php if ($userRole === Role::SUPERADMIN) : ?>
                    <a href="/admin" class="btn btn-gold">Kelola Akun &amp; Master Data</a>
                <?php elseif ($userRole === Role::VERIFIKATOR) : ?>
                    <a href="/ews/verifikasi" class="btn btn-gold">Buka Meja Verifikator</a>
                <?php elseif ($userRole === Role::PIMPINAN) : ?>
                    <a href="/ews/peta-gis" class="btn btn-gold">Dashboard Peta GIS Eksekutif</a>
                <?php else : ?>
                    <a href="/ews/lapor" class="btn btn-gold">Lapor Dini EWS</a>
                <?php endif; ?>
                <a href="/peta-kerukunan" class="btn btn-outline-light">Pantau Peta Kerukunan</a>
            </div>
        </div>

        <aside class="hero-ledger" aria-label="Ringkasan Cakupan Kalimantan Utara">
            <h2 class="hero-ledger-title">Ringkasan Pantauan Kalimantan Utara</h2>
            <dl class="ledger-rows">
                <div class="ledger-row">
                    <dt>Cakupan Wilayah</dt>
                    <dd class="tabular-num"><?= count($kabList) ?> Kab/Kota, <?= $totalKecamatan ?> Kecamatan</dd>
                </div>
                <div class="ledger-row">
                    <dt>Wilayah Kondusif Saat Ini</dt>
                    <dd class="tabular-num"><?= $hijauCount ?> dari <?= count($kabList) ?> Kab/Kota</dd>
                </div>
                <div class="ledger-row">
                    <dt>Lokus Piloting Tahap Awal</dt>
                    <dd>Kabupaten Malinau</dd>
                </div>
                <div class="ledger-row">
                    <dt>Dukungan Wilayah 3TP</dt>
                    <dd>PWA Siap Offline</dd>
                </div>
                <div class="ledger-row">
                    <dt>Biaya Layanan Publik</dt>
                    <dd>Gratis Untuk Semua</dd>
                </div>
            </dl>
        </aside>
    </div>
</section>

<section class="page-section" id="status-kondusivitas">
    <div class="container">
        <div class="section-header">
            <div>
                <h2>Status Kondusivitas Wilayah Kalimantan Utara</h2>
                <p class="section-subtitle">
                    Menampilkan agregat wilayah menggunakan label publik netral dari laporan yang telah diverifikasi
                    valid tanpa membuka identitas pelapor maupun detail sensitif.
                </p>
            </div>
            <a href="/peta-kerukunan" class="btn btn-secondary btn-sm">Lihat Sebaran Kecamatan</a>
        </div>

        <div class="condusivity-grid">
            <?php foreach ($kabList as $kab) : ?>
                <?php
                $level = PublicCondusivityStatus::normalizeLevel((string) ($kab['status_kerawanan'] ?? 'hijau'));
                $isPiloting = !empty($kab['is_piloting']);
                ?>
                <article class="kab-card">
                    <div class="kab-card-head">
                        <h3 class="kab-name"><?= PhpRenderer::e((string) ($kab['nama_kabupaten'] ?? '')) ?></h3>
                        <?php if ($isPiloting) : ?>
                            <span class="pilot-tag">Lokus Piloting</span>
                        <?php endif; ?>
                    </div>

                    <div class="status-pill status-<?= PhpRenderer::e($level) ?>">
                        <span class="status-dot" aria-hidden="true"></span>
                        <span><?= PhpRenderer::e((string) ($kab['label_publik'] ?? 'Kondusif & Harmonis')) ?></span>
                    </div>

                    <p class="kab-desc"><?= PhpRenderer::e((string) ($kab['deskripsi_publik'] ?? '')) ?></p>

                    <div class="kab-stats">
                        <span>Kecamatan:
                            <strong class="tabular-num"><?= (int) ($kab['jumlah_kecamatan'] ?? 0) ?></strong>
                        </span>
                        <span>Laporan Valid:
                            <strong class="tabular-num"><?= (int) ($kab['total_laporan_valid'] ?? 0) ?></strong>
                        </span>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="page-section page-section-tinted" id="aksi-cepat">
    <div class="container">
        <div class="section-header">
            <div>
                <h2>Aksi Cepat Layanan Kerukunan &amp; EWS</h2>
                <p class="section-subtitle">
                    Jalur cepat pelaporan lapangan, pemetaan kerukunan wilayah, edukasi regulasi PBM 2006, dan layanan FKUB.
                </p>
            </div>
        </div>

        <div class="quick-actions-grid">
            <?php if ($userRole === Role::SUPERADMIN) : ?>
                <a href="/admin" class="action-card">
                    <span class="action-icon" aria-hidden="true">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2L4 6v6c0 5 3.5 9.5 8 10 4.5-.5 8-5 8-10V6l-8-4z"/>
                            <path d="M9 12l2 2 4-4"/>
                        </svg>
                    </span>
                    <h3>Panel Super Admin</h3>
                    <p>Verifikasi pendaftaran akun, kelola master wilayah Kaltara, dan kurasi konten layanan publik.</p>
                </a>
            <?php else : ?>
                <a href="/ews/lapor" class="action-card">
                    <span class="action-icon" aria-hidden="true">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2L4 6v6c0 5 3.5 9.5 8 10 4.5-.5 8-5 8-10V6l-8-4z"/>
                            <path d="M12 8v5M12 16h.01"/>
                        </svg>
                    </span>
                    <h3>Lapor Dini EWS</h3>
                    <p>Sampaikan deteksi dini lapangan atau aduan indikasi awal dengan dukungan penyimpanan offline.</p>
                </a>
            <?php endif; ?>

            <a href="<?= ($userRole === Role::PIMPINAN || $userRole === Role::VERIFIKATOR) ? '/ews/peta-gis' : '/peta-kerukunan' ?>"
               class="action-card">
                <span class="action-icon" aria-hidden="true">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21"/>
                        <path d="M9 3v15M15 6v15"/>
                    </svg>
                </span>
                <h3>Peta Spasial Kerukunan</h3>
                <p>Tinjau kondisi kondusivitas 55 kecamatan se-Kalimantan Utara secara spasial dan informatif.</p>
            </a>

            <a href="/tanya" class="action-card">
                <span class="action-icon" aria-hidden="true">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                        <path d="M9 10h6M9 14h4"/>
                    </svg>
                </span>
                <h3>Chatbot &quot;Tanya&quot; PBM 2006</h3>
                <p>Tanya jawab regulasi pendirian rumah ibadah PBM No. 9 &amp; 8 Tahun 2006 serta klarifikasi hoaks.</p>
            </a>

            <a href="/media-hub" class="action-card">
                <span class="action-icon" aria-hidden="true">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="5 3 19 12 5 21 5 3"/>
                    </svg>
                </span>
                <h3>Media Hub &amp; Bank Khotbah</h3>
                <p>Jelajahi podcast Suara Harmoni Perbatasan, infografis moderasi, dan naskah khotbah lintas iman.</p>
            </a>

            <a href="/informasi-kerukunan" class="action-card">
                <span class="action-icon" aria-hidden="true">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="9"/>
                        <path d="M12 10v6M12 7h.01"/>
                    </svg>
                </span>
                <h3>Pusat Informasi &amp; Hotline FKUB</h3>
                <p>Direktori resmi rumah ibadah terverifikasi, kalender dialog antarumat, dan kontak FKUB Kaltara.</p>
            </a>
        </div>
    </div>
</section>

<section class="page-section" id="sorotan-media">
    <div class="container">
        <div class="section-header">
            <div>
                <h2>Sorotan Media Hub &amp; Nilai Ekoteologi</h2>
                <p class="section-subtitle">
                    Penguatan imunitas sosial dan ekoteologi lintas iman di wilayah beranda batas negara.
                </p>
            </div>
            <a href="/media-hub" class="btn btn-secondary btn-sm">Buka Seluruh Galeri</a>
        </div>

        <div class="media-grid">
            <?php foreach ($mediaList as $media) : ?>
                <?php $rawTipe = (string) ($media['tipe_konten'] ?? 'infografis'); ?>
                <article class="media-card">
                    <div class="media-meta">
                        <span><?= PhpRenderer::e($tipeKontenLabels[$rawTipe] ?? $rawTipe) ?></span>
                        <span><?= PhpRenderer::e(strtoupper((string) ($media['agama'] ?? 'semua_agama'))) ?></span>
                    </div>
                    <h3><?= PhpRenderer::e((string) ($media['judul'] ?? '')) ?></h3>
                    <p class="media-author">
                        Narasumber: <?= PhpRenderer::e((string) ($media['tokoh_narasumber'] ?? '')) ?>
                    </p>
                </article>
            <?php endforeach; ?>
        </div>

        <div class="ecoteologi-note">
            <h3>Terobosan Nilai Ekoteologi Kalimantan Utara</h3>
            <p>
                Merawat kerukunan antarumat beragama berjalan beriringan dengan menjaga kelestarian hutan, sungai,
                dan ruang hidup bersama di Kalimantan Utara sebagai amanah keagamaan dan kemanusiaan lintas generasi.
            </p>
        </div>
    </div>
</section>
