<?php

declare(strict_types=1);

?>
<section class="container">
    <div class="auth-shell">
        <h1>Masuk ke Portal BERSATU</h1>
        <p class="auth-intro">
            Gunakan email dan kata sandi akun yang telah diverifikasi dan berstatus aktif untuk mengakses
            layanan pelaporan EWS maupun pengelolaan sesuai peran.
        </p>

        <div id="login-feedback" class="form-feedback" role="alert" hidden></div>

        <div class="form-feedback is-warning">
            Akun pendaftar mandiri baru berstatus <strong>Menunggu Verifikasi</strong> hingga disetujui oleh
            <strong>Super Admin</strong> sebelum dapat masuk.
        </div>

        <form id="login-form" class="form-grid" method="post" action="/api/auth/login">
            <div class="form-field">
                <label for="login-email">Alamat Email</label>
                <input id="login-email"
                       name="email"
                       type="email"
                       required
                       maxlength="100"
                       autocomplete="email"
                       placeholder="nama@instansi.go.id">
            </div>

            <div class="form-field">
                <label for="login-password">Kata Sandi</label>
                <input id="login-password"
                       name="password"
                       type="password"
                       required
                       minlength="8"
                       autocomplete="current-password"
                       placeholder="Minimal 8 karakter">
            </div>

            <div>
                <button type="submit" id="login-submit" class="btn btn-primary">Masuk Sekarang</button>
            </div>
        </form>

        <div class="auth-footer-links">
            Belum memiliki akun pelapor atau warga?
            <a href="/auth/register">Daftar Agen Kerukunan &amp; Masyarakat</a>
        </div>
    </div>
</section>
