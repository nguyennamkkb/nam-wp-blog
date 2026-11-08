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
	$stylesheet = get_theme_file_path( 'assets/css/theme.css' );
	if ( file_exists( $stylesheet ) ) {
		wp_enqueue_style(
			'latent-theme',
			get_theme_file_uri( 'assets/css/theme.css' ),
			array(),
			(string) filemtime( $stylesheet )
		);
	}
}
add_action( 'wp_enqueue_scripts', 'latent_enqueue_styles' );

/**
 * Returns the current year for the footer copyright.
 *
 * @return string
 */
function latent_current_year(): string {
	return wp_date( 'Y' );
}
add_shortcode( 'current_year', 'latent_current_year' );
