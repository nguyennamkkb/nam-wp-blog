<?php
/**
 * Plugin Name: Latent Core
 * Description: Core functionality for the Latent site (taxonomy, content behavior).
 * Version: 0.1.0
 * Requires PHP: 8.2
 * Author: Latent
 * Text Domain: latent
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package LatentCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'LATENT_CORE_VERSION', '0.1.0' );
define( 'LATENT_CORE_PATH', plugin_dir_path( __FILE__ ) );

require_once LATENT_CORE_PATH . 'inc/taxonomy-series.php';
require_once LATENT_CORE_PATH . 'inc/comments.php';

/**
 * Run on plugin activation.
 */
function latent_core_activate(): void {
	latent_core_register_series_taxonomy();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'latent_core_activate' );

/**
 * Run on plugin deactivation.
 */
function latent_core_deactivate(): void {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'latent_core_deactivate' );
