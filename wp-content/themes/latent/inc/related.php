<?php
/**
 * Related posts helpers.
 *
 * Powers the `latent/related-posts` block. Matching is intentionally simple:
 * posts that share a category are preferred, then posts that share a tag.
 * When fewer than the requested number of matches exist, the list is filled
 * with the latest posts so the block never leaves an empty grid cell.
 *
 * @package latent
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Returns published posts related to the given post.
 *
 * The current post is always excluded and results are unique. Related posts
 * are ordered newest first.
 *
 * @param int $post_id Post ID to find relations for.
 * @param int $limit   Maximum number of posts to return.
 * @return array<int, WP_Post>
 */
function latent_related_get_posts( int $post_id, int $limit = 3 ): array {
	if ( $post_id <= 0 || $limit < 1 ) {
		return array();
	}

	$post = get_post( $post_id );

	if ( ! $post instanceof WP_Post || 'post' !== $post->post_type ) {
		return array();
	}

	$base_args = array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => $limit,
		'orderby'             => 'date',
		'order'               => 'DESC',
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	);

	$related = array();
	$seen    = array( $post_id => true );

	/*
	 * Build one tax query per taxonomy so category matches are searched before
	 * tag matches, and a post that shares both is only returned once.
	 */
	$tax_queries = array();

	foreach ( array( 'category', 'post_tag' ) as $taxonomy ) {
		$term_ids = wp_get_post_terms( $post_id, $taxonomy, array( 'fields' => 'ids' ) );

		if ( is_wp_error( $term_ids ) || empty( $term_ids ) ) {
			continue;
		}

		$tax_queries[] = array(
			'taxonomy' => $taxonomy,
			'field'    => 'term_id',
			'terms'    => array_map( 'intval', $term_ids ),
		);
	}

	foreach ( $tax_queries as $tax_query ) {
		$remaining = $limit - count( $related );

		if ( $remaining < 1 ) {
			break;
		}

		$args                   = $base_args;
		$args['posts_per_page'] = $remaining;
		$args['post__not_in']   = array_keys( $seen );
		$args['tax_query']      = array( $tax_query ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query

		$matches = get_posts( $args );

		foreach ( $matches as $match ) {
			if ( isset( $seen[ $match->ID ] ) ) {
				continue;
			}

			$seen[ $match->ID ] = true;
			$related[]          = $match;
		}
	}

	// Fill any remaining slots with the latest posts so exactly $limit render.
	if ( count( $related ) < $limit ) {
		$args                   = $base_args;
		$args['posts_per_page'] = $limit - count( $related );
		$args['post__not_in']   = array_keys( $seen );

		$latest = get_posts( $args );

		foreach ( $latest as $match ) {
			if ( isset( $seen[ $match->ID ] ) ) {
				continue;
			}

			$seen[ $match->ID ] = true;
			$related[]          = $match;
		}
	}

	return array_slice( $related, 0, $limit );
}
