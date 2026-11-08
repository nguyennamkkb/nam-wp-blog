<?php
/**
 * Theme functions for Latent.
 *
 * @package latent
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$latent_includes = array(
	'assets',
	'blocks',
	'toc',
	'series',
	'search',
	'block-styles',
	'related',
	'seo',
	'llms',
	'feed',
	'performance',
	'security',
	'analytics',
);

foreach ( $latent_includes as $latent_include ) {
	$latent_include_path = __DIR__ . '/inc/' . $latent_include . '.php';

	if ( file_exists( $latent_include_path ) ) {
		require_once $latent_include_path;
	}
}

/**
 * Sets up theme defaults and registers support for various WordPress features.
 */
function latent_setup(): void {
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'editor-styles' );
}
add_action( 'after_setup_theme', 'latent_setup' );

/**
 * Enqueues theme stylesheet if it exists.
 */
function latent_enqueue_styles(): void {
	$styles = array(
		'latent-theme'      => array(
			'path' => 'assets/css/theme.css',
			'deps' => array(),
		),
		'latent-components' => array(
			'path' => 'assets/css/components.css',
			'deps' => array( 'latent-theme' ),
		),
	);

	foreach ( $styles as $handle => $style ) {
		$stylesheet = get_theme_file_path( $style['path'] );

		if ( ! file_exists( $stylesheet ) ) {
			continue;
		}

		wp_enqueue_style(
			$handle,
			get_theme_file_uri( $style['path'] ),
			$style['deps'],
			(string) filemtime( $stylesheet )
		);
	}
}
add_action( 'wp_enqueue_scripts', 'latent_enqueue_styles' );

/**
 * Returns the footer copyright line.
 *
 * @return string
 */
function latent_copyright(): string {
	return sprintf(
		/* translators: %s: current year. */
		esc_html_x( '© %1$s %2$s', 'year, site name', 'latent' ),
		esc_html( wp_date( 'Y' ) ),
		esc_html( get_bloginfo( 'name', 'display' ) )
	);
}
add_shortcode( 'latent_copyright', 'latent_copyright' );
