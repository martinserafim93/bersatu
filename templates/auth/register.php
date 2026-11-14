<?php

declare(strict_types=1);

use App\Application\Views\PhpRenderer;

/** @var array<int, array{id: int, nama_kabupaten: string, nama_kecamatan: string}> $wilayahOptions */
/** @var array<string, string> $roleOptions */

$wilayahList = $wilayahOptions ?? [];
$roles = $roleOptions ?? [];
?>
<section class="container">
    <div class="auth-shell">
        <h1>Pendaftaran Akun Agen Kerukunan &amp; Warga</h1>
        <p class="auth-intro">
            Lengkapi identitas diri sesuai data resmi. Akun yang mendaftar mandiri otomatis berstatus
            <strong>menunggu_verifikasi</strong> hingga diverifikasi oleh Super Admin Kanwil Kemenag Kaltara.
        </p>

        <div id="register-feedback" class="form-feedback" role="alert" hidden></div>

        <form id="register-form" class="form-grid" method="post" action="/api/auth/register">
            <div class="form-field">
                <label for="reg-role">Peran Pengguna</label>
                <select id="reg-role" name="role" required>
                    <?php foreach ($roles as $value => $label) : ?>
                        <option value="<?= PhpRenderer::e($value) ?>"><?= PhpRenderer::e($label) ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="field-hint">
                    Peran dapat dikoreksi oleh Super Admin saat proses verifikasi berkas identitas.
                </p>
            </div>

            <div class="form-field">
                <label for="reg-nama">Nama Lengkap (Sesuai KTP/SK)</label>
                <input id="reg-nama"
                       name="nama_lengkap"
                       type="text"
                       required
                       maxlength="150"
                       autocomplete="name"
                       placeholder="Nama lengkap beserta gelar bila ada">
            </div>

            <div class="form-field" id="reg-instansi-group">
                <label for="reg-instansi">Instansi / Lembaga</label>
                <input id="reg-instansi"
                       name="instansi"
                       type="text"
                       maxlength="150"
                       placeholder="Contoh: KUA Malinau Kota / FKUB Malinau">
                <p class="field-hint">Wajib diisi bagi pendaftar Penyuluh Agama (IPARI), KUA, dan FKUB.</p>
            </div>

            <div class="form-field">
                <label for="reg-nip-nik">NIK (16 digit) atau NIP (18 digit)</label>
                <input id="reg-nip-nik"
                       name="nip_nik"
                       type="text"
                       required
                       pattern="(\d{16}|\d{18})"
                       inputmode="numeric"
                       placeholder="16 atau 18 digit angka tanpa spasi">
                <p class="field-hint" id="reg-nip-hint">
                    Identitas ini dienkripsi AES-256-GCM dan dilindungi sebagai data sensitif pelapor.
                </p>
            </div>

            <div class="form-field">
                <label for="reg-wilayah">Wilayah Tugas / Domisili (Kecamatan se-Kaltara)</label>
                <select id="reg-wilayah" name="wilayah_id" required>
                    <option value="">-- Pilih Kecamatan --</option>
                    <?php foreach ($wilayahList as $wilayah) : ?>
                        <option value="<?= (int) $wilayah['id'] ?>">
                            <?= PhpRenderer::e($wilayah['nama_kabupaten'] . ' - ' . $wilayah['nama_kecamatan']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-field">
                <label for="reg-wa">Nomor WhatsApp Aktif</label>
                <input id="reg-wa"
                       name="no_wa"
                       type="tel"
                       required
                       autocomplete="tel"
                       placeholder="Contoh: 081234567890">
            </div>

            <div class="form-field">
                <label for="reg-email">Alamat Email</label>
                <input id="reg-email"
                       name="email"
                       type="email"
                       required
                       maxlength="100"
                       autocomplete="email"
                       placeholder="nama@contoh.id">
            </div>

            <div class="form-field">
                <label for="reg-password">Kata Sandi</label>
                <input id="reg-password"
                       name="password"
                       type="password"
                       required
                       minlength="8"
                       maxlength="72"
                       autocomplete="new-password"
                       placeholder="Minimal 8 karakter">
            </div>

            <div>
                <button type="submit" id="register-submit" class="btn btn-primary">Kirim Pendaftaran</button>
            </div>
        </form>

        <div class="auth-footer-links">
            Sudah memiliki akun aktif? <a href="/auth/login">Masuk ke Portal BERSATU</a>
        </div>
    </div>
</section>
