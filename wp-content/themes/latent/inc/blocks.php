<?php
/**
 * Block registration for the Latent theme.
 *
 * Every block lives in its own directory under `blocks/` and is described by a
 * `block.json` file. Registering the directory lets WordPress wire up the
 * editor script, its `index.asset.php` dependencies and the server-side
 * render template declared in the metadata — no build step required.
 *
 * @package latent
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers all blocks scaffolded under `blocks/<slug>/block.json`.
 */
function latent_register_blocks(): void {
	$block_files = glob( get_theme_file_path( 'blocks/*/block.json' ) );

	if ( empty( $block_files ) ) {
		return;
	}

	foreach ( $block_files as $block_file ) {
		if ( ! file_exists( $block_file ) ) {
			continue;
		}

		register_block_type( dirname( $block_file ) );
	}
}
add_action( 'init', 'latent_register_blocks' );
