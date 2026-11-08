# Latent Playwright suite

End-to-end, accessibility (axe) and visual regression tests for the Latent
WordPress site. The suite runs against a live site — DDEV locally — so
Playwright does not start WordPress itself (`webServer` is intentionally absent).

## Prerequisites

- Node.js 24+ and npm.
- DDEV project running and reachable at `https://wp-blog.ddev.site`.
- WordPress installed with the `latent` theme and `latent-core` plugin active.
- Chromium downloaded for Playwright:

```bash
npm ci
npx playwright install chromium
```

Firefox is also downloaded by `npx playwright install firefox`. WebKit needs
extra OS libraries on this machine (`sudo npx playwright install-deps`); see
[Known limits](#known-limits).

## Fixtures

Seed (or verify) the test data:

```bash
bash tests/fixtures/seed.sh
```

The script calls `tests/fixtures/seed.php` through `ddev wp eval-file` and is
idempotent. It only creates content that is missing, keyed by slug, so running
it against the existing demo database creates nothing. It guarantees at least:

- 8 published posts, including the four fixture posts with H2 headings and a
  Python code block used by the single/TOC/copy tests.
- 3 `series` terms: `rag-tu-so-0`, `production-ai-series`,
  `ai-agents-thuc-chien`, with deterministic publish dates so the
  oldest-to-newest assertions are stable.
- 8 published pages (`home`, `blog`, `series`, `about`, `now`, `uses`,
  `contact`, `privacy`).
- A featured image on every fixture post (generated as a 1200×630 PNG through
  GD when one is missing).
- The static front page / posts page options when they are unset.

Run it with plain WP-CLI by overriding the command:

```bash
WP_CLI="wp" bash tests/fixtures/seed.sh
```

For CI, run the same command after WordPress is installed and the theme/plugin
are active. There is no CI E2E job yet; see [CI status](#ci-status).

## Commands

```bash
# Functional E2E, desktop Chromium (19 tests)
npm run test:e2e

# axe accessibility, 8 templates × light/dark (16 tests)
npm run test:a11y

# Visual baselines, 4 templates × light/dark (8 screenshots)
npm run test:visual

# Alias for test:e2e
npm test

# Chromium desktop + mobile Chromium (390×844)
npm run test:browsers
```

Useful raw commands:

```bash
# Single spec / project
npx playwright test --project=chromium tests/e2e/search.spec.ts

# All configured projects (Firefox works locally; WebKit needs install-deps)
npx playwright test

# Open the last HTML report
npx playwright show-report
```

`PLAYWRIGHT_BASE_URL` overrides the target environment:

```bash
PLAYWRIGHT_BASE_URL=https://staging.example.com npx playwright test --project=chromium tests/e2e
```

`PLAYWRIGHT_BASE_URL` is read by `playwright.config.ts` and defaults to
`https://wp-blog.ddev.site`. HTTPS verification is relaxed with
`ignoreHTTPSErrors: true` so DDEV's local certificate works.

## Visual baselines

Baselines live next to the spec:

```text
tests/visual/visual.spec.ts-snapshots/
├── home-light-chromium-linux.png
├── home-dark-chromium-linux.png
├── single-light-chromium-linux.png
├── single-dark-chromium-linux.png
├── blog-light-chromium-linux.png
├── blog-dark-chromium-linux.png
├── series-light-chromium-linux.png
└── series-dark-chromium-linux.png
```

Regenerate them after an intentional design/content change:

```bash
npm run test:visual:update
```

Then review the PNG diff and commit the updated baselines. The baseline name
includes the project and platform (`chromium-linux`). Visual tests only run in
the desktop `chromium` project; the other projects skip them explicitly. Fonts
and rendering are platform-specific, so baselines are not portable between
macOS, Windows and Linux CI without regenerating.

Remote avatar images from `secure.gravatar.com` are stubbed with a transparent
pixel so screenshots never depend on the network.

## Test map

| File | Covers |
| --- | --- |
| `tests/e2e/home.spec.ts` | Hero H1, featured post, six latest cards, series strip, console errors |
| `tests/e2e/single.spec.ts` | One H1, TOC/heading parity, reading time, author, related posts, code copy feedback |
| `tests/e2e/series.spec.ts` | Archive oldest→newest, `aria-current`, Series page with 3 series |
| `tests/e2e/search.spec.ts` | Button/Ctrl+K, 2-char minimum, results, arrows/Enter/Esc, XSS probe, `/?s=rag` fallback |
| `tests/e2e/theme.spec.ts` | Pre-paint `data-theme`, OS preference, toggle persistence, light/dark palette |
| `tests/e2e/states.spec.ts` | HTTP 404, search empty state, no horizontal overflow at 320/390 |
| `tests/a11y/a11y.spec.ts` | axe on 8 templates in light and dark; fails on serious/critical |
| `tests/visual/visual.spec.ts` | `toHaveScreenshot` for home/single/blog/series in light and dark |

The axe spec asserts zero `serious` and zero `critical` violations. Minor and
moderate findings are reported by axe in the HTML report but do not fail the
gate, matching the T3.4 acceptance criteria.

## Known limits

- **DDEV is external.** `webServer` is not configured; start the project first
  with `ddev start`. `bash tests/fixtures/seed.sh` fails fast if WordPress is
  not installed.
- **WebKit is configured but not run locally.** The browser downloads, but this
  Ubuntu host is missing `libevent-2.1-7t64` and `libavif16`. Install them with
  `sudo npx playwright install-deps` (or the `apt-get` command Playwright
  prints) before running `--project=webkit`.
- **Firefox is configured and passes the E2E suite locally.** Visual baselines
  are Chromium-only by design.
- **Visual baselines are Linux/Chromium-specific.** Do not copy them from
  another OS.
- **No CI E2E job.** See below.

## CI status

The `lint` job in `.github/workflows/ci.yml` remains the only workflow job.
A reproducible E2E job needs a WordPress site with the theme, plugin and seeded
database, which the current GitHub Actions workflow does not provision. Options
for Phase 4:

1. Add `.wp-env.json` (WordPress + theme/plugin mounts), run `npx wp-env start`,
   seed with `npx wp-env run cli wp eval-file tests/fixtures/seed.php`, then run
   `PLAYWRIGHT_BASE_URL=http://localhost:8888 npx playwright test --project=chromium`.
2. Or provision WordPress + MariaDB as service containers, install WP-CLI,
   activate the theme/plugin, seed, and run Playwright against the local URL.

Until one of those is validated, adding a job would be a broken red build, so
CI E2E is explicitly deferred to Phase 4 and the local suite is kept green.
