<?php
/**
 * Security hardening for the Latent theme.
 *
 * Reduces the public attack surface without touching how WordPress serves
 * content: XML-RPC and the pingback discovery it depends on are disabled, the
 * WordPress version generator is removed, and a small set of response headers
 * is added only when nothing else has set them already. REST, feeds, sitemaps,
 * search and the block editor keep working exactly as before.
 *
 * Production-only hardening lives outside this file and must be configured on
 * the host/plugin layer:
 *
 * - Two-factor authentication for every administrator (Solid Security or
 *   equivalent).
 * - Login attempt rate limiting plus strong passwords and least privilege.
 * - `DISALLOW_FILE_EDIT` (and usually `DISALLOW_FILE_MODS`) in production
 *   `wp-config.php`.
 * - HSTS (`Strict-Transport-Security`) once HTTPS is permanent, and a
 *   report-only CSP before enforcing one.
 * - A WAF/CDN in front of the origin, offsite backups and quarterly restore
 *   drills. See `docs/operations/backup.md`.
 *
 * @package latent
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Disables XML-RPC authentication.
 *
 * The site does not use the mobile app, Jetpack or remote publishing, so every
 * XML-RPC request should be rejected. Returning false from `xmlrpc_enabled`
 * makes the XML-RPC server refuse authenticated methods and is the supported
 * way to turn the endpoint off.
 *
 * @return bool
 */
function latent_security_disable_xmlrpc(): bool {
	return false;
}
add_filter( 'xmlrpc_enabled', 'latent_security_disable_xmlrpc' );

/**
 * Rejects every request to `xmlrpc.php` before the server dispatches it.
 *
 * `xmlrpc_enabled` only gates authenticated methods; the IXR base class still
 * answers `system.listMethods` and `system.multicall` without authentication.
 * Blocking the request at `init` closes the whole endpoint (including
 * pingbacks) instead of leaving a method-discovery surface exposed. The
 * constant is defined by `xmlrpc.php` before WordPress loads, so this never
 * affects REST, feeds, sitemaps, search or the block editor.
 *
 * @return void
 */
function latent_security_block_xmlrpc_requests(): void {
	if ( ! defined( 'XMLRPC_REQUEST' ) || true !== constant( 'XMLRPC_REQUEST' ) ) {
		return;
	}

	status_header( 403 );
	nocache_headers();
	header( 'Content-Type: text/plain; charset=UTF-8' );
	exit( 'XML-RPC is disabled.' );
}
add_action( 'init', 'latent_security_block_xmlrpc_requests', 1 );

/**
 * Removes the pingback discovery link and the `X-Pingback` header.
 *
 * Pingbacks are delivered through XML-RPC, which is disabled above, so
 * advertising the endpoint only helps scanners. The `wp_headers` filter is
 * used because core adds the header conditionally on singular requests.
 *
 * @param array<string, string> $headers Response headers.
 * @return array<string, string>
 */
function latent_security_remove_pingback( array $headers ): array {
	unset( $headers['X-Pingback'] );

	return $headers;
}
add_filter( 'wp_headers', 'latent_security_remove_pingback' );
remove_action( 'wp_head', 'pingback_header' );

/**
 * Removes the WordPress generator and RSD discovery link from the document.
 *
 * Both tags expose the core version or an XML-RPC discovery document that no
 * client needs once XML-RPC is off. Feeds and sitemaps are left untouched.
 *
 * @return void
 */
function latent_security_clean_head(): void {
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'rsd_link' );
}
add_action( 'init', 'latent_security_clean_head' );

/**
 * Tells whether a response header is already present.
 *
 * Header names are compared case-insensitively, matching HTTP semantics. Only
 * headers set by PHP are visible here; server-level headers are never
 * overwritten because they are not part of `headers_list()`.
 *
 * @param string   $name    Header name.
 * @param string[] $headers Lines returned by `headers_list()`.
 * @return bool
 */
function latent_security_header_is_set( string $name, array $headers ): bool {
	$needle = strtolower( $name ) . ':';

	foreach ( $headers as $header ) {
		if ( str_starts_with( strtolower( ltrim( $header ) ), $needle ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Adds conservative security headers to every response.
 *
 * The headers only constrain MIME sniffing, referrer leakage, browser feature
 * access and framing; they do not require a nonce or inline-script allowlist,
 * so the block editor, REST API, feeds and sitemaps continue to load. A header
 * that a plugin or the host already sent is left untouched.
 *
 * HSTS is intentionally absent in local development. Enable it in production
 * only after HTTPS is permanent and the redirect from HTTP is in place.
 *
 * @return void
 */
function latent_security_send_headers(): void {
	if ( headers_sent() ) {
		return;
	}

	$headers = array(
		'X-Content-Type-Options' => 'nosniff',
		'Referrer-Policy'        => 'strict-origin-when-cross-origin',
		'Permissions-Policy'     => 'camera=(), microphone=(), geolocation=()',
		'X-Frame-Options'        => 'SAMEORIGIN',
	);

	$existing = headers_list();

	foreach ( $headers as $name => $value ) {
		if ( ! latent_security_header_is_set( $name, $existing ) ) {
			header( $name . ': ' . $value );
		}
	}
}
add_action( 'send_headers', 'latent_security_send_headers' );
