<?php
/**
 * Privacy-first analytics for the Latent theme.
 *
 * Prints the Umami tracker only when both the script URL and the website ID
 * are configured. Nothing is emitted (and the analytics bridge script is not
 * loaded) until then, so a fresh install collects no data by default.
 *
 * @package latent
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Returns the validated Umami configuration.
 *
 * The script URL must be an absolute HTTPS URL: a missing scheme is rejected
 * rather than silently upgraded, so a typo cannot point the tracker at an
 * unintended host. The website ID is limited to the characters Umami uses
 * (UUID, or a short slug for self-hosted instances).
 *
 * @return array{src: string, website_id: string}|null
 */
function latent_analytics_config(): ?array {
	$src        = get_option( 'latent_umami_src', '' );
	$website_id = get_option( 'latent_umami_website_id', '' );

	if ( ! is_string( $src ) || ! is_string( $website_id ) ) {
		return null;
	}

	$src        = trim( $src );
	$website_id = trim( $website_id );

	if ( '' === $src || '' === $website_id ) {
		return null;
	}

	// Rejects http://, protocol-relative and malformed values outright.
	$src = esc_url_raw( $src, array( 'https' ) );

	if ( '' === $src ) {
		return null;
	}

	$parts = wp_parse_url( $src );

	if (
		! is_array( $parts )
		|| ! isset( $parts['scheme'], $parts['host'] )
		|| 'https' !== strtolower( (string) $parts['scheme'] )
		|| '' === (string) $parts['host']
	) {
		return null;
	}

	if ( 1 !== preg_match( '/^[A-Za-z0-9][A-Za-z0-9._:-]{7,127}$/', $website_id ) ) {
		return null;
	}

	return array(
		'src'        => $src,
		'website_id' => $website_id,
	);
}

/**
 * Prints the Umami script tag once in the document head.
 *
 * @return void
 */
function latent_analytics_print_script(): void {
	static $printed = false;

	if ( $printed ) {
		return;
	}

	$config = latent_analytics_config();

	if ( null === $config ) {
		return;
	}

	$printed = true;

	wp_print_script_tag(
		array(
			'async'           => true,
			'defer'           => true,
			'data-website-id' => $config['website_id'],
			'src'             => $config['src'],
		)
	);
}
add_action( 'wp_head', 'latent_analytics_print_script', 20 );

/**
 * Keeps the event bridge out of the page while analytics is unconfigured.
 *
 * `inc/assets.php` enqueues `assets/js/analytics.js` by convention. Dequeuing
 * it here means an unconfigured site ships no tracking code at all, while the
 * file still loads automatically once the options are filled in.
 *
 * @return void
 */
function latent_analytics_maybe_dequeue(): void {
	if ( null !== latent_analytics_config() ) {
		return;
	}

	wp_dequeue_script( 'latent-analytics' );
}
add_action( 'wp_enqueue_scripts', 'latent_analytics_maybe_dequeue', 30 );
