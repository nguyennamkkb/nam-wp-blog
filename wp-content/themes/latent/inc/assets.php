<?php
/**
 * Asset helpers for the Latent theme.
 *
 * Keeps font preloads and (later) stylesheet/script wiring in one place.
 *
 * @package latent
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Preloads the two critical variable font files.
 *
 * Only the Latin subsets are preloaded: Vietnamese glyphs are picked up on
 * demand by the browser via the unicode-range declared in theme.json.
 */
function latent_preload_fonts(): void {
	$fonts = array(
		'assets/fonts/inter-latin-var.woff2',
		'assets/fonts/newsreader-latin-var.woff2',
	);

	foreach ( $fonts as $font ) {
		$font_path = get_theme_file_path( $font );

		if ( ! file_exists( $font_path ) ) {
			continue;
		}

		printf(
			'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
			esc_url( get_theme_file_uri( $font ) )
		);
	}
}
add_action( 'wp_head', 'latent_preload_fonts', 1 );
