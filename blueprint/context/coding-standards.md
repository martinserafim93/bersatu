# Coding Standards

> Project conventions for **Portal BERSATU** (PHP / Slim Framework 4).
> Review and refine these standards as new architectural layers (database,
> templates/PWA frontend, authentication) are added during the build plan.

## PHP and Static Analysis

- Declare `declare(strict_types=1);` at the top of every PHP file in `app/`, `public/`, `src/`, and `tests/`
- Follow PSR-12 coding style enforced by `phpcs.xml` (`vendor/bin/phpcs`)
- Use short array syntax `[]` only (`Generic.Arrays.DisallowLongArraySyntax` is enforced)
- Add explicit parameter, property, and return types where supported; keep `src/` passing PHPStan level 4 (`phpstan.neon.dist`, `vendor/bin/phpstan`)
- Package management uses Composer (`composer.json` and `composer.lock`)

## Slim 4 Architecture and Routing

- Entry point is `public/index.php`, which boots PHP-DI and Slim 4
- Application wiring lives in `app/`:
  - `app/settings.php` - environment and `SettingsInterface` configuration
  - `app/dependencies.php` - PHP-DI service definitions (such as Monolog `LoggerInterface`)
  - `app/middleware.php` - global PSR-15 middleware stack (`SessionMiddleware`, etc.)
  - `app/repositories.php` - bindings from domain repository interfaces to persistence implementations
  - `app/routes.php` - HTTP route definitions and route groups
- Keep route handlers thin by delegating to single-purpose Action classes under `src/Application/Actions/`
- Use PSR-7 request/response interfaces (`Psr\Http\Message\ServerRequestInterface`, `Psr\Http\Message\ResponseInterface`) and PSR-15 middleware interfaces

## File Organization

- Bootstrap and DI config: `app/*.php`
- Web root and front controller: `public/index.php`
- Actions (controllers): `src/Application/Actions/[Feature]/[Verb][Noun]Action.php`
- Middleware: `src/Application/Middleware/[Name]Middleware.php`
- Error and shutdown handlers: `src/Application/Handlers/`
- Domain models, exceptions, and repository contracts: `src/Domain/[Feature]/`
- Persistence implementations: `src/Infrastructure/Persistence/[Feature]/`
- Unit and action tests: `tests/` mirroring the `src/` directory layout (`tests/Application/`, `tests/Domain/`, `tests/Infrastructure/`)

## Naming

- Namespaces: PSR-4 `App\` mapped to `src/`, `Tests\` mapped to `tests/`
- Classes, Interfaces, Traits, Exceptions: PascalCase matching the filename (`ViewUserAction.php`, `UserRepository.php`)
- Methods and variables: camelCase
- Constants: SCREAMING_SNAKE_CASE

## Styling and Frontend

- > TODO: Frontend rendering approach (server-rendered templates vs SPA/PWA assets, CSS framework, and Leaflet.js GIS integration) is not yet present in the scaffold. Establish and document once the first UI feature is planned.

## Database and Persistence

- Depend on domain repository interfaces (`src/Domain/[Feature]/*Repository.php`) inside Actions and services, and bind concrete implementations in `app/repositories.php`
- > TODO: The scaffold currently ships with `InMemoryUserRepository`. Document the relational database layer (PDO / query builder / migration tool for MySQL or PostgreSQL) when database persistence is introduced.

## Data Access, Validation, and Error Handling

- Domain lookup failures throw domain exceptions extending `DomainException` (such as `DomainRecordNotFoundException`)
- JSON/API actions extend `App\Application\Actions\Action` and return structured `ActionPayload` responses via `$this->respondWithData(...)` or `$this->respond(...)`
- Uncaught HTTP and domain errors are formatted consistently by `HttpErrorHandler` into `ActionPayload` / `ActionError`
- > TODO: Define request input validation, CSRF protection, file-upload validation, and RBAC session/middleware conventions when implementing authenticated and form-submission features.

## Testing

The blueprint installs no test runner; testing is opt-in at the project level,
because the overlay can't know your stack. Adding unit testing is an explicit
setup task the AI can do through the normal workflow, either as a build-plan item
or with `/tests`. The setup should choose the stack-native runner, wire the
scripts or commands, add a small example test, and update the Commands section
of `AGENTS.md`.

When `AGENTS.md` declares a `Verify` command, treat it as the umbrella automated
gate. It combines only the checks this project actually has, in this order when
available: typecheck, tests, then build. The command does not enable an absent
test runner or replace focused evidence. It gives local work and optional CI one
exact command to run. `/ci` owns Verify and CI setup. `/tests` adds the real test
command to Verify when it already exists, but never creates CI only because
testing was configured.

**The opt-in switch is one signal: a `test` command in the Commands section of
`AGENTS.md`.** Declare one and **tests become a gate for logic-bearing steps**,
not an optional extra; leave it out and the loop verifies logic with the evidence
it already uses (run it, a screenshot, the build). Adding the runner is itself a
deliberate step, never a silent mid-step install. This is the single definition
of the switch; the skills and `ai-interaction.md` only point back here.

- **What to test (the scope rule):** pure logic where a wrong answer is possible -
  parsers, formatters, validators, id/slug builders, domain models, repositories,
  and action handlers. These have assertable inputs and outputs and real edge
  cases (empty, missing, malformed).
- **What not to test:** UI components and integration-level surfaces (render or
  export routes, anything driving a real browser or external service). Verify those
  with a screenshot and the running app, not brittle unit tests.
- **The gate (when a runner is configured):** a build step that adds in-scope logic
  must ship a passing test in the same reviewable diff. The project's test command
  must be green before the step is approved, before any checkpoint commit, and
  before `/complete` merges. UI and integration-only steps are exempt and ride on
  screenshot plus runtime evidence.
- **When it's named:** the `/feature` spec's Testing section predicts the coverage,
  `/implement` writes the test with the step, and if a step surfaces logic the spec
  didn't foresee, add a focused test then.
- An empty suite should fail, not pass, so "no tests ran" never looks like "passed".
- Test files live under `tests/` mirroring `src/` (for example `tests/Domain/User/UserTest.php`) and extend `Tests\TestCase`.
- Run them via the project's test command (see Commands in `AGENTS.md`), not a
  hardcoded tool name.

Stack binding: this PHP / Slim 4 app uses PHPUnit (`composer test` / `phpunit.xml`)
with `phpspec/prophecy-phpunit` (`$this->prophesize(...)`) for mocking dependencies
in `Tests\TestCase`.

## Browser Verification

For UI and integration behavior, prefer real browser evidence over reading the
code and assuming it works.

- Browser automation is separately opt-in through `/tests browser`. That setup
  reuses a compatible runner or prefers Playwright for supported projects, then
  documents the exact command as `Browser tests` in `AGENTS.md`.
- When `Browser tests` is declared, add focused coverage for stable behavioral
  done-whens when it is proportionate, and run the documented command during
  `/check`. Do not assume it proves visual fidelity, real authenticated-profile
  behavior, browser chrome, or another claim the test does not observe.
- If no Browser tests command is declared, do not add a runner silently in the
  middle of an unrelated feature. Use the available dev server, browser
  screenshots, build output, API output, or manual evidence instead.
- Browser tests are not part of the default Verify command or CI unless the user
  separately chooses that slower gate.
- Browser evidence is especially important for flows that click, type, submit,
  navigate, download files, render complex layouts, or depend on client-side
  state.

## Code Quality

- No commented-out code unless specified
- No unused imports or variables
- Keep functions under 50 lines when possible

## Comments

Write code that explains itself; comment only what the code cannot say.
Over-commenting is a common AI tell, so resist it.

- Comment the **why**, not the **what**. Delete any comment that restates the code.
- No banner/header blocks, section dividers, or step-by-step narration of obvious
  code. A file does not need a comment announcing each region.
- A comment earns its place only when it captures something the code can't: a
  non-obvious decision, a gotcha or workaround, why a value is what it is, or a
  link to a spec or issue.
- Prefer self-documenting names and small functions over explanatory comments.
- Keep doc comments minimal: a one-line purpose on an exported type or function is
  plenty; don't write JSDoc that just repeats the signature.
- When in doubt, leave the comment out.

## Writing

- No em dashes (U+2014) in generated content: docs, comments, commit messages,
  READMEs, specs. They read as AI-generated.
- Use a hyphen for `term - description` separators; rephrase prose with commas,
  parentheses, or a colon. Avoid en dashes and the ellipsis character too.
