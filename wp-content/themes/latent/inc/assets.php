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
 * Enqueues the dark palette and the theme toggle script.
 *
 * `dark.css` depends on the last core stylesheet so its token overrides win;
 * the script is deferred because first paint is handled by the inline
 * bootstrap below.
 */
function latent_enqueue_theme_assets(): void {
	$dark_stylesheet = get_theme_file_path( 'assets/css/dark.css' );

	if ( file_exists( $dark_stylesheet ) ) {
		wp_enqueue_style(
			'latent-dark',
			get_theme_file_uri( 'assets/css/dark.css' ),
			array( 'latent-components' ),
			(string) filemtime( $dark_stylesheet )
		);
	}

	$theme_script = get_theme_file_path( 'assets/js/theme.js' );

	if ( file_exists( $theme_script ) ) {
		wp_enqueue_script(
			'latent-theme',
			get_theme_file_uri( 'assets/js/theme.js' ),
			array(),
			(string) filemtime( $theme_script ),
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
