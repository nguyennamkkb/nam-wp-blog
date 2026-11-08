<?php
/**
 * Asset helpers for the Latent theme.
 *
 * Keeps font preloads, stylesheet/script wiring and the no-flash theme
 * bootstrap in one place.
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

/**
 * Enqueues feature stylesheets and scripts that exist in the theme.
 *
 * Files are discovered by convention: a feature can ship `assets/css/<name>.css`
 * or `assets/js/<name>.js` and this loader picks it up without touching PHP.
 * `dark.css` is loaded last so its token overrides win.
 */
function latent_enqueue_theme_assets(): void {
	$styles = array(
		'latent-toc'     => 'assets/css/toc.css',
		'latent-series'  => 'assets/css/series.css',
		'latent-search'  => 'assets/css/search.css',
		'latent-code'    => 'assets/css/code.css',
		'latent-related' => 'assets/css/related.css',
		'latent-states'  => 'assets/css/states.css',
		'latent-a11y'    => 'assets/css/a11y.css',
		'latent-dark'    => 'assets/css/dark.css',
	);

	foreach ( $styles as $handle => $relative_path ) {
		$stylesheet = get_theme_file_path( $relative_path );

		if ( ! file_exists( $stylesheet ) ) {
			continue;
		}

		wp_enqueue_style(
			$handle,
			get_theme_file_uri( $relative_path ),
			array( 'latent-components' ),
			(string) filemtime( $stylesheet )
		);
	}

	$scripts = array(
		'latent-theme'     => 'assets/js/theme.js',
		'latent-toc'       => 'assets/js/toc.js',
		'latent-search'    => 'assets/js/search.js',
		'latent-code'      => 'assets/js/code-copy.js',
		'latent-analytics' => 'assets/js/analytics.js',
	);

	foreach ( $scripts as $handle => $relative_path ) {
		$script = get_theme_file_path( $relative_path );

		if ( ! file_exists( $script ) ) {
			continue;
		}

		wp_enqueue_script(
			$handle,
			get_theme_file_uri( $relative_path ),
			array(),
			(string) filemtime( $script ),
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);
	}
}
add_action( 'wp_enqueue_scripts', 'latent_enqueue_theme_assets', 20 );

/**
 * Applies the stored or system theme before the stylesheets render.
 *
 * Runs at `wp_head` priority 1 so `<html>` already carries `data-theme` when
 * CSS is parsed, preventing a flash of the wrong palette. The whole read is
 * guarded: if localStorage or matchMedia is unavailable, the CSS
 * `prefers-color-scheme` fallback applies instead.
 */
function latent_inline_theme_script(): void {
	$script = <<<'JS'
(function(){try{var t=localStorage.getItem('latent-theme');if(t!=='light'&&t!=='dark'){t=window.matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light';}document.documentElement.dataset.theme=t;}catch(e){}})();
JS;

	wp_print_inline_script_tag( $script, array( 'id' => 'latent-theme-init' ) );
}
add_action( 'wp_head', 'latent_inline_theme_script', 1 );
