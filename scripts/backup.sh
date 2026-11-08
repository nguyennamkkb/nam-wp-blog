#!/usr/bin/env bash
#
# Backup the Latent site database and custom code.
#
# Usage: bash scripts/backup.sh
#
# Produces, in ./backups:
#   db-$STAMP.sql.gz       full database dump (UTC timestamp, gzip)
#   files-$STAMP.tar.gz    theme + site plugin source
#
# Uploads, WordPress core, vendor/, node_modules/ and wp-config.php are NOT
# included; host-level backups must cover those. See docs/operations/backup.md.
#
# Any failure aborts the script with a non-zero status.

set -euo pipefail

SCRIPT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd -- "$SCRIPT_DIR/.." && pwd)"

cd "$PROJECT_ROOT"

STAMP="$(date -u +%Y%m%dT%H%M%SZ)"
BACKUP_DIR="$PROJECT_ROOT/backups"
DB_BACKUP="$BACKUP_DIR/db-$STAMP.sql.gz"
FILES_BACKUP="$BACKUP_DIR/files-$STAMP.tar.gz"
DB_TMP=""
DB_TMP_REL=""

log() {
	printf '==> %s\n' "$*"
}

fail() {
	printf 'ERROR: %s\n' "$*" >&2
	exit 1
}

cleanup() {
	if [ -n "$DB_TMP" ] && [ -f "$DB_TMP" ]; then
		rm -f "$DB_TMP"
	fi
}
trap cleanup EXIT

log "Latent backup — $STAMP (UTC)"
log "Project root: $PROJECT_ROOT"

command -v ddev >/dev/null 2>&1 || fail "ddev is not installed or not on PATH."
command -v gzip >/dev/null 2>&1 || fail "gzip is not installed."
command -v tar >/dev/null 2>&1 || fail "tar is not installed."

if ! ddev describe >/dev/null 2>&1; then
	fail "DDEV project is not running. Start it with 'ddev start'."
fi

mkdir -p "$BACKUP_DIR"

# Export to a temporary file first: a failed wp db export must not leave a
# truncated .sql.gz that looks like a valid backup. WP-CLI runs inside the DDEV
# container, so it receives a path relative to the project root; the host path
# is used for compression and cleanup.
DB_TMP="$BACKUP_DIR/db-$STAMP.$$.sql"
DB_TMP_REL="backups/db-$STAMP.$$.sql"

log "Exporting database"
if ! ddev wp db export "$DB_TMP_REL"; then
	fail "Database export failed."
fi

if [ ! -s "$DB_TMP" ]; then
	fail "Database export produced an empty file."
fi

log "Compressing database dump"
gzip -9 -c "$DB_TMP" > "$DB_BACKUP"

log "Archiving theme and plugin files"
tar -czf "$FILES_BACKUP" \
	wp-content/themes/latent \
	wp-content/plugins/latent-core

log "Verifying archives"
gzip -t "$DB_BACKUP" || fail "gzip integrity check failed: $DB_BACKUP"
gzip -t "$FILES_BACKUP" || fail "gzip integrity check failed: $FILES_BACKUP"
tar -tzf "$FILES_BACKUP" >/dev/null || fail "tar integrity check failed: $FILES_BACKUP"

DB_BYTES="$(stat -c '%s' "$DB_BACKUP")"
FILES_BYTES="$(stat -c '%s' "$FILES_BACKUP")"

log "Backup complete"
printf '    %s (%s bytes, %s)\n' \
	"$DB_BACKUP" "$DB_BYTES" "$(du -h "$DB_BACKUP" | cut -f1)"
printf '    %s (%s bytes, %s)\n' \
	"$FILES_BACKUP" "$FILES_BYTES" "$(du -h "$FILES_BACKUP" | cut -f1)"
printf '    Total: %s\n' "$(du -ch "$DB_BACKUP" "$FILES_BACKUP" | tail -n 1 | cut -f1)"
