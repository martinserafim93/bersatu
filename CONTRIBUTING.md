# Panduan Kontribusi - Portal BERSATU

## Alur Branch & Perubahan

1. Buat branch baru dari `main` untuk setiap fitur (`feature/<nama>`), perbaikan (`fix/<nama>`), atau rollback (`rollback/<nama>`).
2. Ikuti cakupan rencana di `blueprint/build-plan.md`, spesifikasi aktif di `blueprint/context/current-feature.md`, serta panduan di `AGENTS.md`.
3. Gunakan pesan commit bergaya *conventional commits* (`feat:`, `fix:`, `chore:`, `docs:`) tanpa menambahkan atributsi AI pada commit maupun pull request.

## Standar Kode & Desain

- Patuhi PSR-12 dan larangan sintaks array panjang (`[]` saja) dengan `declare(strict_types=1);` pada setiap berkas PHP di `app/`, `bin/`, `public/`, `src/`, dan `tests/`.
- Rujuk [blueprint/context/coding-standards.md](blueprint/context/coding-standards.md) untuk struktur Slim 4, repository PDO, keamanan enkripsi, dan cakupan pengujian.
- Rujuk [DESIGN.md](DESIGN.md) dan [PRODUCT.md](PRODUCT.md) untuk semua perubahan antarmuka web dan PWA.
- Jaga ukuran setiap berkas `.php`, `.js`, dan `.css` di bawah 1 MB sesuai batas hosting InfinityFree.

## Verifikasi Sebelum Merge

Pastikan seluruh pemeriksaan berikut lulus sebelum mengajukan review atau merge:

```bash
vendor/bin/phpcs
vendor/bin/phpstan
composer test
```
