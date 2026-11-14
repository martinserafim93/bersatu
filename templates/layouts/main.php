<?php

declare(strict_types=1);

use App\Application\Views\PhpRenderer;
use App\Domain\User\Role;
use App\Domain\User\User;

/** @var string $content */
/** @var string|null $pageTitle */
/** @var string|null $pageDescription */
/** @var string|null $surfaceMode */
/** @var string|null $activePath */
/** @var User|null $currentUser */
/** @var array<int, string>|null $pageScripts */

$title = $pageTitle ?? 'Portal BERSATU - Kemenag Kalimantan Utara';
$description = $pageDescription
    ?? 'Portal Early Warning System (EWS) dan Promosi Kerukunan Umat Beragama Kanwil Kemenag Kalimantan Utara.';
$mode = $surfaceMode ?? 'persuade';
$currentPath = $activePath ?? '/';
$user = $currentUser ?? null;
$userRole = $user instanceof User ? $user->getRole() : null;

$roleLabels = [
    Role::SUPERADMIN => 'Super Admin',
    Role::PIMPINAN => 'Pimpinan',
    Role::VERIFIKATOR => 'Verifikator',
    Role::PENYULUH_IPARI => 'Penyuluh Agama (IPARI)',
    Role::KUA => 'Kepala / Petugas KUA',
    Role::FKUB => 'Pengurus FKUB / Tokoh',
    Role::MASYARAKAT => 'Masyarakat Terverifikasi',
];

$navLinks = [
    ['href' => '/', 'label' => 'Beranda'],
];

if ($userRole !== Role::SUPERADMIN && $userRole !== Role::VERIFIKATOR && $userRole !== Role::PIMPINAN) {
    $navLinks[] = ['href' => '/ews/lapor', 'label' => 'Lapor Dini'];
}
if ($userRole === Role::VERIFIKATOR) {
    $navLinks[] = ['href' => '/ews/verifikasi', 'label' => 'Meja Verifikator'];
}
if ($userRole === Role::PIMPINAN || $userRole === Role::VERIFIKATOR) {
    $navLinks[] = ['href' => '/ews/peta-gis', 'label' => 'Peta GIS Eksekutif'];
} else {
    $navLinks[] = ['href' => '/peta-kerukunan', 'label' => 'Peta Kerukunan'];
}

$navLinks[] = ['href' => '/media-hub', 'label' => 'Media Hub'];
$navLinks[] = ['href' => '/tanya', 'label' => 'Tanya'];
$navLinks[] = ['href' => '/informasi-kerukunan', 'label' => 'Info Kerukunan'];

if ($userRole === Role::SUPERADMIN) {
    $navLinks[] = ['href' => '/admin', 'label' => 'Panel Admin'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0b5d46">
    <meta name="description" content="<?= PhpRenderer::e($description) ?>">
    <title><?= PhpRenderer::e($title) ?></title>
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" href="/assets/icons/icon-192.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/assets/icons/icon-192.svg">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="surface-<?= PhpRenderer::e($mode) ?>">
    <a class="skip-link" href="#main-content">Langsung ke konten utama</a>

    <div id="network-status-banner" class="network-banner" role="status" aria-live="polite" hidden>
        <div class="container network-banner-inner">
            <span class="network-dot" aria-hidden="true"></span>
            <p id="network-status-text">Mode Offline Wilayah 3TP: Konten yang tersimpan tetap dapat dibaca.</p>
        </div>
    </div>

    <header class="site-header">
        <div class="container header-inner">
            <a class="brand-lockup" href="/">
                <img src="/assets/icons/icon-192.svg" alt="" width="38" height="38" class="brand-mark">
                <span class="brand-text">
                    <strong class="brand-title">Portal BERSATU</strong>
                    <span class="brand-subtitle">Kanwil Kemenag Prov. Kalimantan Utara</span>
                </span>
            </a>

            <button type="button"
                    class="nav-toggle"
                    id="nav-toggle-btn"
                    aria-expanded="false"
                    aria-controls="site-nav-panel">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2" stroke-linecap="round" aria-hidden="true">
                    <path d="M4 7h16M4 12h16M4 17h16"/>
                </svg>
                <span>Menu</span>
            </button>

            <div class="nav-panel" id="site-nav-panel">
                <nav class="main-nav" aria-label="Navigasi Utama">
                    <?php foreach ($navLinks as $item) : ?>
                        <?php $isCurrent = $currentPath === $item['href']; ?>
                        <a href="<?= PhpRenderer::e($item['href']) ?>"
                           class="nav-link<?= $isCurrent ? ' is-active' : '' ?>"
                           <?= $isCurrent ? 'aria-current="page"' : '' ?>>
                            <?= PhpRenderer::e($item['label']) ?>
                        </a>
                    <?php endforeach; ?>
                </nav>

                <div class="auth-controls">
                    <?php if ($user instanceof User) : ?>
                        <span class="user-badge">
                            <span class="user-name"><?= PhpRenderer::e($user->getNamaLengkap()) ?></span>
                            <span class="user-role">
                                <?= PhpRenderer::e($roleLabels[$user->getRole()] ?? $user->getRole()) ?>
                            </span>
                        </span>
                        <button type="button" id="btn-logout" class="btn btn-outline-light btn-sm">
                            Keluar
                        </button>
                    <?php else : ?>
                        <a href="/auth/login"
                           class="btn btn-outline-light btn-sm<?= $currentPath === '/auth/login' ? ' is-current' : '' ?>">
                            Masuk
                        </a>
                        <a href="/auth/register" class="btn btn-gold btn-sm">
                            Daftar Agen &amp; Warga
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <main id="main-content" class="site-main">
        <?= $content ?>
    </main>

    <footer class="site-footer">
        <div class="container footer-grid">
            <div class="footer-brand">
                <p class="footer-title">Portal BERSATU (BERsama Saling jAga Toleransi Umat)</p>
                <p class="footer-copy">
                    Merajut Harmoni, Menjaga Kedamaian di Beranda Batas NKRI Melalui Portal EWS.
                    Layanan publik terpadu Kanwil Kementerian Agama Provinsi Kalimantan Utara.
                </p>
            </div>
            <div class="footer-meta">
                <p><strong>Cakupan Layanan:</strong> 5 Kabupaten/Kota &amp; 55 Kecamatan se-Kalimantan Utara</p>
                <p><strong>Lokus Piloting Awal:</strong> Kabupaten Malinau</p>
                <p><strong>Akses Layanan:</strong> Gratis untuk seluruh masyarakat &amp; tokoh lintas agama</p>
            </div>
        </div>
    </footer>

    <script src="/assets/js/pwa.js" defer></script>
    <?php if (is_array($pageScripts ?? null)) : ?>
        <?php foreach ($pageScripts as $scriptSrc) : ?>
            <script src="<?= PhpRenderer::e((string) $scriptSrc) ?>" defer></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
