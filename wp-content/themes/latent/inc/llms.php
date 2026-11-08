<?php
/**
 * Endpoint exposing llms.txt for AI search crawlers.
 *
 * Serves an llms.txt index of the site in plain text.
 * Only published posts, non-empty series and the main pages are listed, so
 * drafts and private content never leak into a crawler-visible document.
 *
 * @package latent
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the llms.txt rewrite rule.
 *
 * @return void
 */
function latent_llms_register_rewrite(): void {
	add_rewrite_rule( '^llms\.txt$', 'index.php?latent_llms=1', 'top' );
}
add_action( 'init', 'latent_llms_register_rewrite' );

/**
 * Registers the `latent_llms` public query var.
 *
 * @param string[] $vars Public query vars.
 * @return string[]
 */
function latent_llms_query_vars( array $vars ): array {
	$vars[] = 'latent_llms';

	return $vars;
}
add_filter( 'query_vars', 'latent_llms_query_vars' );

/**
 * Prevents WordPress from canonicalising `/llms.txt` to `/llms.txt/`.
 *
 * @param string|false $redirect Canonical redirect URL, or false when disabled.
 * @return string|false
 */
function latent_llms_disable_canonical_redirect( string|false $redirect ): string|false {
	if ( '1' === (string) get_query_var( 'latent_llms' ) ) {
		return false;
	}

	return $redirect;
}
add_filter( 'redirect_canonical', 'latent_llms_disable_canonical_redirect' );

/**
 * Flushes rewrite rules once the theme is activated.
 *
 * @return void
 */
function latent_llms_flush_rewrites(): void {
	latent_llms_register_rewrite();
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'latent_llms_flush_rewrites' );

/**
 * Returns a single-line, Markdown-safe label.
 *
 * @param string $text Raw text, possibly containing HTML.
 * @return string
 */
function latent_llms_label( string $text ): string {
	$text = latent_seo_plain_text( $text );

	return str_replace( array( '[', ']' ), array( '\[', '\]' ), $text );
}

/**
 * Returns the public URL for a post, or an empty string.
 *
 * @param WP_Post $post Post object.
 * @return string
 */
function latent_llms_url( WP_Post $post ): string {
	return esc_url_raw( (string) get_permalink( $post ) );
}

/**
 * Appends a Markdown list item for a post with its excerpt.
 *
 * @param WP_Post $post Post object.
 * @return string
 */
function latent_llms_post_line( WP_Post $post ): string {
	$url   = latent_llms_url( $post );
	$title = latent_llms_label( (string) get_the_title( $post ) );

	if ( '' === $title ) {
		$title = 'Bài viết #' . (string) $post->ID;
	}

	if ( '' === $url ) {
		return '';
	}

	$line = sprintf( '- [%s](%s)', $title, $url );
	$text = latent_seo_plain_text( (string) get_the_excerpt( $post ) );

	if ( '' !== $text ) {
		$line .= ': ' . $text;
	}

	return $line . "\n";
}

/**
 * Builds the llms.txt document.
 *
 * @return string
 */
function latent_llms_content(): string {
	$site_name = latent_seo_plain_text( (string) get_bloginfo( 'name', 'display' ) );
	$tagline   = latent_seo_plain_text( (string) get_bloginfo( 'description', 'display' ) );

	if ( '' === $site_name ) {
		$site_name = 'Latent';
	}

	$output  = '# ' . $site_name . "\n";
	$output .= '> ' . ( '' !== $tagline ? $tagline : 'Ghi chú thực chiến về AI, LLM và hệ thống.' ) . "\n\n";

	$posts = get_posts(
		array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => -1,
			'orderby'             => 'date',
			'order'               => 'DESC',
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		)
	);

	$output .= "## Bài viết\n";

	foreach ( $posts as $post ) {
		$output .= latent_llms_post_line( $post );
	}

	$output .= "\n## Series\n";

	$series = get_terms(
		array(
			'taxonomy'   => 'series',
			'hide_empty' => true,
			'orderby'    => 'name',
			'order'      => 'ASC',
		)
	);

	if ( ! is_wp_error( $series ) ) {
		foreach ( $series as $term ) {
			$url  = get_term_link( $term );
			$name = latent_llms_label( (string) $term->name );

			if ( ! is_string( $url ) || '' === $url ) {
				continue;
			}

			$line = sprintf( '- [%s](%s)', $name, esc_url_raw( $url ) );
			$text = latent_seo_plain_text( (string) term_description( (int) $term->term_id ) );

			if ( '' !== $text ) {
				$line .= ': ' . $text;
			}

			$output .= $line . "\n";
		}
	}

	$output .= "\n## Trang\n";

	$page_slugs = array( 'about', 'now', 'uses', 'contact', 'privacy', 'series' );

	foreach ( $page_slugs as $slug ) {
		$page = get_page_by_path( $slug, OBJECT, 'page' );

		if ( ! $page instanceof WP_Post || 'publish' !== $page->post_status ) {
			continue;
		}

		$url = latent_llms_url( $page );

		if ( '' === $url ) {
			continue;
		}

		$output .= sprintf( "- [%s](%s)\n", latent_llms_label( (string) $page->post_title ), $url );
	}

	return $output;
}

/**
 * Serves `/llms.txt` as plain text.
 *
 * @return void
 */
function latent_llms_render(): void {
	if ( '1' !== (string) get_query_var( 'latent_llms' ) ) {
		return;
	}

	status_header( 200 );
	header( 'Content-Type: text/plain; charset=utf-8' );
	header( 'X-Content-Type-Options: nosniff' );

	echo latent_llms_content(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Plain-text response, not HTML.

	exit;
}
add_action( 'template_redirect', 'latent_llms_render' );

/**
 * Advertises the llms.txt endpoint in the document head.
 *
 * @return void
 */
function latent_llms_head_link(): void {
	printf( '<link rel="llms" href="%s" />' . "\n", esc_url( home_url( '/llms.txt' ) ) );
}
add_action( 'wp_head', 'latent_llms_head_link', 4 );
