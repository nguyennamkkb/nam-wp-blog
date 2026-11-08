<?php
/**
 * Register the "series" taxonomy.
 *
 * @package LatentCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the series taxonomy for posts.
 */
function latent_core_register_series_taxonomy(): void {
	$labels = array(
		'name'                       => __( 'Series', 'latent' ),
		'singular_name'              => __( 'Series', 'latent' ),
		'menu_name'                  => __( 'Series', 'latent' ),
		'all_items'                  => __( 'All Series', 'latent' ),
		'edit_item'                  => __( 'Edit Series', 'latent' ),
		'view_item'                  => __( 'View Series', 'latent' ),
		'update_item'                => __( 'Update Series', 'latent' ),
		'add_new_item'               => __( 'Add New Series', 'latent' ),
		'new_item_name'              => __( 'New Series Name', 'latent' ),
		'search_items'               => __( 'Search Series', 'latent' ),
		'popular_items'              => __( 'Popular Series', 'latent' ),
		'separate_items_with_commas' => __( 'Separate series with commas', 'latent' ),
		'add_or_remove_items'        => __( 'Add or remove series', 'latent' ),
		'choose_from_most_used'      => __( 'Choose from the most used series', 'latent' ),
		'not_found'                  => __( 'No series found.', 'latent' ),
		'back_to_items'              => __( 'Back to series', 'latent' ),
	);

	$args = array(
		'labels'            => $labels,
		'public'            => true,
		'show_in_rest'      => true,
		'hierarchical'      => false,
		'rewrite'           => array(
			'slug'       => 'series',
			'with_front' => false,
		),
		'show_admin_column' => true,
	);

	register_taxonomy( 'series', array( 'post' ), $args );
}
add_action( 'init', 'latent_core_register_series_taxonomy' );

/**
 * Order series archives from the oldest part to the newest.
 *
 * Only the main front-end query for the series taxonomy is affected, so
 * admin screens and secondary queries keep their default ordering.
 *
 * @param WP_Query $query The query being prepared.
 */
function latent_core_order_series_archive( WP_Query $query ): void {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( ! $query->is_tax( 'series' ) ) {
		return;
	}

	$query->set( 'order', 'ASC' );
	$query->set( 'orderby', 'date' );
}
add_action( 'pre_get_posts', 'latent_core_order_series_archive' );
