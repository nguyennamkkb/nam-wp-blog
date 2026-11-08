<?php
/**
 * Series helpers.
 *
 * Small wrappers around the `series` taxonomy used by the series navigation
 * and series list blocks. Parts are always ordered oldest-first, matching the
 * reading order of a multi-part series.
 *
 * @package latent
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Returns the series terms assigned to a post.
 *
 * @param int $post_id Post ID.
 * @return array<int, WP_Term>
 */
function latent_series_get_post_terms( int $post_id ): array {
	$terms = wp_get_post_terms( $post_id, 'series' );

	if ( is_wp_error( $terms ) ) {
		return array();
	}

	return array_values( $terms );
}

/**
 * Returns the primary series term for a post.
 *
 * @param int $post_id Post ID.
 * @return WP_Term|null
 */
function latent_series_get_primary_term( int $post_id ): ?WP_Term {
	$terms = latent_series_get_post_terms( $post_id );

	return $terms[0] ?? null;
}

/**
 * Returns the ordered parts of a series.
 *
 * @param int $term_id Series term ID.
 * @param int $limit   Maximum number of parts, -1 for all.
 * @return array<int, WP_Post>
 */
function latent_series_get_parts( int $term_id, int $limit = -1 ): array {
	if ( $term_id <= 0 ) {
		return array();
	}

	$parts = get_posts(
		array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => $limit,
			'order'               => 'ASC',
			'orderby'             => 'date',
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
			'tax_query'           => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				array(
					'taxonomy' => 'series',
					'field'    => 'term_id',
					'terms'    => $term_id,
				),
			),
		)
	);

	return $parts;
}

/**
 * Returns the 1-based position of a post within its series parts.
 *
 * @param array<int, WP_Post> $parts   Ordered parts.
 * @param int                 $post_id Post ID.
 * @return int Zero when the post is not part of the given list.
 */
function latent_series_get_part_index( array $parts, int $post_id ): int {
	foreach ( $parts as $index => $part ) {
		if ( (int) $part->ID === $post_id ) {
			return $index + 1;
		}
	}

	return 0;
}

/**
 * Returns a series archive URL, or an empty string on failure.
 *
 * @param WP_Term $term Series term.
 * @return string
 */
function latent_series_get_archive_url( WP_Term $term ): string {
	$url = get_term_link( $term );

	return is_wp_error( $url ) ? '' : $url;
}
