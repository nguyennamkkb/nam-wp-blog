<?php
/**
 * Block style registrations for the Latent theme.
 *
 * Registers the note/warning/tip callout variants for `core/group`. The
 * matching visual styles live in `assets/css/code.css`, which is loaded on the
 * front end by the theme asset loader.
 *
 * @package latent
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers theme block styles.
 */
function latent_register_block_styles(): void {
	if ( ! function_exists( 'register_block_style' ) ) {
		return;
	}

	$callouts = array(
		'note'    => __( 'Ghi chú', 'latent' ),
		'warning' => __( 'Cảnh báo', 'latent' ),
		'tip'     => __( 'Mẹo', 'latent' ),
	);

	foreach ( $callouts as $name => $label ) {
		register_block_style(
			'core/group',
			array(
				'name'  => $name,
				'label' => $label,
			)
		);
	}
}
add_action( 'init', 'latent_register_block_styles' );
