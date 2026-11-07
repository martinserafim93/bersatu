# AGENTS.md

Instructions for AI coding agents working in this project. This is the cross-tool
entry point. Codex, Google Antigravity, and OpenCode read `AGENTS.md`. Other
compatible tools do too. Claude Code reads `CLAUDE.md`, which imports this file,
so there is a single source of truth.

Claude Code, Codex, and every other AI tool must not add AI attribution to
commits or pull requests, including AI `Co-Authored-By` trailers or generated-by
signatures. Preserve genuine human attribution. See
[Commit and PR attribution](blueprint/context/ai-interaction.md#commit-and-pr-attribution)
for optional tool settings.

## What this is

**Portal BERSATU** (*BERsama Saling jAga Toleransi Umat*) - portal web responsif berbasis Progressive Web App (PWA) untuk *Strategi Penguatan Early Warning System (EWS) Melalui Promosi Kerukunan Umat Beragama Berbasis Digital* pada Kantor Wilayah Kementerian Agama Provinsi Kalimantan Utara (Proyek Perubahan PKN Tk. II Angkatan XXX Tahun 2026 - H. Iramsyah Noor, S.Ag., M.A.P., dengan lokus piloting awal di Kabupaten Malinau dan cakupan penuh 5 Kabupaten/Kota serta 55 Kecamatan se-Kalimantan Utara).

Sistem ini menggabungkan dua modul utama dalam satu ekosistem pada Slim Framework 4 (PHP):
1. **Digital EWS Terintegrasi** - pelaporan lapangan "Lapor Dini" dengan *auto-geotagging* GPS dan dukungan draft offline untuk wilayah 3TP/*blank spot*, verifikasi berjenjang dan perlindungan identitas pelapor (*whistleblower protection*), pemetaan kerawanan spasial GIS berbasis Leaflet.js dengan 3 indikator baku (`Hijau` = Kondusif, `Kuning` = Potensi Ketegangan/Mediasi, `Merah` = Ancaman Konflik Mendesak), serta *instant alert* otomatis via Webhook Telegram/WhatsApp ke pimpinan dan tim respons cepat.
2. **Moderation & Tolerance Media Hub, Chatbot "Tanya" & Pusat Informasi Kerukunan** - galeri konten moderasi beragama dan ekoteologi (video tokoh agama/adat, infografis, podcast *"Suara Harmoni Perbatasan"*, dan Bank Naskah Khotbah penyuluh lintas agama), asisten virtual Chatbot "Tanya" untuk edukasi PBM No. 9 & No. 8 Tahun 2006 serta klarifikasi hoaks SARA perbatasan, ditambah direktori geospasial rumah ibadah, kalender agenda lintas iman, dan layanan hotline FKUB se-Kaltara.

This project is built with the **AI Blueprint**, a workflow layer, not an
app skeleton. To start a new project, scaffold the app first in an empty folder
(create-next-app, Vite, etc.), then overlay these files on top. Never run a
framework scaffolder inside a directory that already holds the blueprint files
(`AGENTS.md`, `CLAUDE.md`, `.agents/`, `.claude/`, `blueprint/`); it fails
because the directory isn't empty.

The workflow is defined by the local skills and context files below.

## Proportional engineering

Build for established requirements, not hypothetical scale, threats, or future
flexibility. Reuse existing code, the standard library, native platform features,
and installed dependencies before adding machinery.

- Unknown scale or extensibility defaults to the smaller reversible design. Do
  not infer enterprise, multi-tenant, hostile-user, or compliance requirements.
- Derive trust and data-integrity boundaries from actual reachability: untrusted
  input, auth/session/ownership, shared persisted data, destructive operations,
  payments, secrets, and sensitive data.
- Ask only when an unknown materially changes behavior, architecture, persisted
  data, interoperability, a real security boundary, or cost. Otherwise choose the
  simplest repository-native implementation.
- Add an abstraction, dependency, service, configuration surface, compatibility
  layer, or security mechanism only for a current requirement.
- Simplicity never removes real trust-boundary validation, data-loss prevention,
  accessibility, explicit security requirements, configured tests, or project rules.
- Stack-specific template standards apply only when the project uses that stack.

## Read these when relevant

- `blueprint/config.json` - deterministic project workflow settings
- `blueprint/workflow.md` - workflow reference for configuration, skills, dashboard activity, and checks
- `blueprint/context/project-overview.md` - the project's source of truth
- `blueprint/context/coding-standards.md` - read before changing code
- `blueprint/context/ai-interaction.md` - read when running the Blueprint workflow
- `blueprint/context/current-feature.md` - the one feature, fix, or rollback being built in this checkout

Reuse relevant context already loaded in the session. Claude Code imports only
this file; its Blueprint skills load the other files on demand.

## Workflow

Build one feature, fix, or rollback at a time, behind review gates. The feature
loop is `/feature` -> `/implement` -> `/check` -> `/audit current` ->
`/complete`; approve the Feature spec before Implement. Each command is a local
skill at `.agents/skills/<skill>/SKILL.md` or `.claude/skills/<skill>/SKILL.md`
that carries its own instructions. Claude Code and Google Antigravity use slash
commands such as `/feature`, Codex uses `$feature`, and other tools can follow
the matching `SKILL.md` when asked.

Skills: `adopt`, `audit`, `blueprint-doctor`, `blueprint-status`, `brief`,
`check`, `ci`, `complete`, `debug`, `discovery`, `explore`, `feature`, `fix`,
`implement`, `onboard`, `overview`, `prototype`, `release`, `rollback`, and
`tests`, plus the explicit-only `autopilot` and `continuous`.

- Keep one active work item per checkout. Parallel work needs its own clone or
  Git worktree and branch; never replace another worker's active spec.
- `blueprint/config.json` can make review or verification stricter and tune
  local workflow settings. It never grants permission to commit, merge, push,
  deploy, publish, send, delete data, waive a failing check, or accept a finding.
- Never create or edit `blueprint/.state/run.json` directly. Tracked skills
  record dashboard activity through the managed helper.

## Commands

For this PHP / Slim Framework 4 project (managed with Composer):

- Dev server: `composer start` (`php -S localhost:8080 -t public` at http://localhost:8080)
- Docker dev server: `docker-compose up -d` (http://localhost:8080)
- Test: `composer test` (runs `phpunit` via `phpunit.xml`)
- Lint (PSR-12): `vendor/bin/phpcs`
- Typecheck / static analysis: `vendor/bin/phpstan`
- Build: None (interpreted PHP application; use `composer install` to install dependencies)

Unit testing is configured with PHPUnit (`composer test`) and acts as the test
gate for logic-bearing steps.

Browser testing is opt-in. Run `/tests browser` or `$tests browser` to add
or normalize a browser harness and document its exact command as `Browser
tests`. Check and Continuous Mode can then reuse it without installing tooling
mid-feature.
