#!/usr/bin/env bash
#
# Idempotent fixture seed for the Playwright suite.
#
# Usage:
#   bash tests/fixtures/seed.sh
#   WP_CLI="wp" bash tests/fixtures/seed.sh          # non-DDEV WP-CLI
#   WP_CLI="ddev wp --environment=staging" bash tests/fixtures/seed.sh
#
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT_DIR"

if [[ -n "${WP_CLI:-}" ]]; then
	# shellcheck disable=SC2206
	WP_CMD=(${WP_CLI})
else
	WP_CMD=(ddev wp)
fi

if ! command -v "${WP_CMD[0]}" >/dev/null 2>&1; then
	echo "error: '${WP_CMD[0]}' not found. Install DDEV or set WP_CLI='wp'." >&2
	exit 1
fi

if ! "${WP_CMD[@]}" core is-installed >/dev/null 2>&1; then
	echo "error: WordPress is not installed or the DDEV project is not running." >&2
	echo "       Run 'ddev start' and 'ddev wp core is-installed' first." >&2
	exit 1
fi

echo "Seeding Latent fixtures…"
"${WP_CMD[@]}" eval-file tests/fixtures/seed.php
echo "Fixture seed complete."
