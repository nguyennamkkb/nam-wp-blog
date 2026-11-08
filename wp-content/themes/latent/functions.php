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

require_once __DIR__ . '/inc/assets.php';
require_once __DIR__ . '/inc/blocks.php';

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
		'latent-theme'      => 'assets/css/theme.css',
		'latent-components' => 'assets/css/components.css',
	);

	foreach ( $styles as $handle => $relative_path ) {
		$stylesheet = get_theme_file_path( $relative_path );

		if ( ! file_exists( $stylesheet ) ) {
			continue;
		}

		wp_enqueue_style(
			$handle,
			get_theme_file_uri( $relative_path ),
			array(),
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
		esc_html__( '© %s Latent', 'latent' ),
		esc_html( wp_date( 'Y' ) )
	);
}
add_shortcode( 'latent_copyright', 'latent_copyright' );
