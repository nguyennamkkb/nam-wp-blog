<?php
/**
 * SEO metadata, social cards and structured data.
 *
 * A lean, dependency-free replacement for a third-party SEO plugin. Each
 * request gets one meta description, one canonical URL, one Open Graph set,
 * one Twitter card set and one JSON-LD graph. WordPress core keeps ownership
 * of the singular canonical tag; this file only fills the gaps.
 *
 * @package latent
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Collapses a string into plain, single-spaced text.
 *
 * @param string $text Raw text, possibly containing HTML or shortcodes.
 * @return string
 */
function latent_seo_plain_text( string $text ): string {
	$text = strip_shortcodes( $text );
	$text = wp_strip_all_tags( $text );
	$text = html_entity_decode( $text, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	$text = preg_replace( '/\s+/u', ' ', $text );

	return trim( (string) $text );
}

/**
 * Trims a description to the 140-160 character window.
 *
 * Shorter descriptions are returned untouched: inventing filler would hurt
 * the snippet. Longer ones are cut on a word boundary and marked with an
 * ellipsis so the result never exceeds 160 characters.
 *
 * @param string $text Plain text.
 * @return string
 */
function latent_seo_trim_description( string $text ): string {
	$text = latent_seo_plain_text( $text );

	if ( '' === $text || mb_strlen( $text ) <= 160 ) {
		return $text;
	}

	$snippet = mb_substr( $text, 0, 160 );
	$space   = mb_strrpos( $snippet, ' ' );

	if ( false !== $space && $space >= 140 ) {
		$snippet = mb_substr( $snippet, 0, $space );
	} else {
		$snippet = mb_substr( $snippet, 0, 159 );
	}

	return rtrim( $snippet, " \t\n\r\0\x0B.,;:!?…-–—" ) . '…';
}

/**
 * Returns the queried post when the request is singular.
 *
 * @return WP_Post|null
 */
function latent_seo_queried_post(): ?WP_Post {
	$object = get_queried_object();

	return $object instanceof WP_Post ? $object : null;
}

/**
 * Returns the first paragraph of a block post, falling back to all its text.
 *
 * Headings and code samples make poor descriptions, so the first paragraph is
 * preferred when an excerpt is too short.
 *
 * @param string $html Serialized post content.
 * @return string
 */
function latent_seo_first_paragraph( string $html ): string {
	if ( 1 === preg_match( '#<p[^>]*>(.*?)</p>#is', $html, $matches ) ) {
		return (string) $matches[1];
	}

	return $html;
}

/**
 * Builds the meta description for the current view.
 *
 * Uses the manual excerpt first, extends it with the post body when it is too
 * short, then falls back to the site tagline. Archives use their term or
 * author description when available.
 *
 * @return string
 */
function latent_seo_description(): string {
	$description = '';

	if ( is_singular() ) {
		$post = latent_seo_queried_post();

		if ( $post instanceof WP_Post ) {
			$description = latent_seo_plain_text( (string) get_the_excerpt( $post ) );
			$content     = latent_seo_plain_text( latent_seo_first_paragraph( (string) $post->post_content ) );

			if ( mb_strlen( $description ) < 140 && '' !== $content ) {
				if ( '' === $description || str_starts_with( $content, $description ) ) {
					$description = $content;
				} elseif ( ! str_contains( $content, $description ) ) {
					$description .= ' ' . $content;
				}
			}
		}
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$term = get_queried_object();

		if ( $term instanceof WP_Term ) {
			$description = (string) $term->description;
		}
	} elseif ( is_author() ) {
		$description = (string) get_the_author_meta( 'description', (int) get_queried_object_id() );
	}

	if ( '' === trim( $description ) ) {
		$description = (string) get_bloginfo( 'description', 'display' );
	}

	if ( '' === trim( $description ) ) {
		$description = (string) get_bloginfo( 'name', 'display' );
	}

	return latent_seo_trim_description( $description );
}

/**
 * Builds the social/archive title for the current view.
 *
 * @return string
 */
function latent_seo_title(): string {
	if ( is_front_page() ) {
		return latent_seo_plain_text( (string) get_bloginfo( 'name', 'display' ) );
	}

	if ( is_singular() ) {
		$post = latent_seo_queried_post();

		if ( $post instanceof WP_Post ) {
			$title = latent_seo_plain_text( (string) get_the_title( $post ) );

			if ( '' !== $title ) {
				return $title;
			}
		}
	}

	return latent_seo_plain_text( wp_get_document_title() );
}

/**
 * Returns the canonical URL for the current view.
 *
 * On singular views this mirrors `rel_canonical()` so the Open Graph URL and
 * the canonical tag always agree. Archives use the paginated archive URL.
 *
 * @return string
 */
function latent_seo_canonical_url(): string {
	$object_id = (int) get_queried_object_id();

	if ( is_singular() ) {
		$url = wp_get_canonical_url( $object_id );

		if ( is_string( $url ) && '' !== $url ) {
			return $url;
		}

		$permalink = get_permalink( $object_id );

		return is_string( $permalink ) ? $permalink : '';
	}

	if ( is_404() ) {
		return home_url( '/' );
	}

	return (string) get_pagenum_link( max( 1, (int) get_query_var( 'paged' ) ) );
}

/**
 * Extracts the full-size image URL, dimensions and alt text for an attachment.
 *
 * @param int    $attachment_id Attachment ID.
 * @param string $alt           Preferred alt text.
 * @return array{url: string, width: int, height: int, alt: string}|array{}
 */
function latent_seo_attachment_image( int $attachment_id, string $alt ): array {
	if ( $attachment_id <= 0 ) {
		return array();
	}

	$image = wp_get_attachment_image_src( $attachment_id, 'full' );

	if ( ! is_array( $image ) ) {
		return array();
	}

	$alt_text = latent_seo_plain_text( $alt );

	if ( '' === $alt_text ) {
		$alt_text = latent_seo_plain_text( (string) get_the_title( $attachment_id ) );
	}

	return array(
		'url'    => (string) $image[0],
		'width'  => (int) $image[1],
		'height' => (int) $image[2],
		'alt'    => $alt_text,
	);
}

/**
 * Returns image data for social cards and structured data.
 *
 * Priority: featured image, filtered fallback attachment, site icon, newest
 * image attachment. The last resort keeps Open Graph valid on the front page
 * before a dedicated site cover exists.
 *
 * @param int $post_id Post ID, or 0 for archive views.
 * @return array{url: string, width: int, height: int, alt: string}|array{}
 */
function latent_seo_image( int $post_id ): array {
	$site_name = latent_seo_plain_text( (string) get_bloginfo( 'name', 'display' ) );

	if ( $post_id > 0 && has_post_thumbnail( $post_id ) ) {
		$attachment_id = (int) get_post_thumbnail_id( $post_id );
		$alt           = (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );

		if ( '' === trim( $alt ) ) {
			$alt = (string) get_the_title( $post_id );
		}

		$image = latent_seo_attachment_image( $attachment_id, $alt );

		if ( array() !== $image ) {
			return $image;
		}
	}

	$fallback_id = (int) apply_filters( 'latent_seo_fallback_image_id', 0 );
	$image       = latent_seo_attachment_image( $fallback_id, $site_name );

	if ( array() !== $image ) {
		return $image;
	}

	$icon = get_site_icon_url( 512 );

	if ( '' !== $icon ) {
		return array(
			'url'    => $icon,
			'width'  => 512,
			'height' => 512,
			'alt'    => $site_name,
		);
	}

	$latest = get_posts(
		array(
			'post_type'           => 'attachment',
			'post_mime_type'      => 'image',
			'post_status'         => 'inherit',
			'posts_per_page'      => 1,
			'orderby'             => 'date',
			'order'               => 'DESC',
			'no_found_rows'       => true,
			'ignore_sticky_posts' => true,
		)
	);

	if ( isset( $latest[0] ) ) {
		return latent_seo_attachment_image( $latest[0]->ID, $site_name );
	}

	return array();
}

/**
 * Returns the WebSite node with the site search action.
 *
 * @return array<string, mixed>
 */
function latent_seo_website_node(): array {
	$node = array(
		'@type'           => 'WebSite',
		'@id'             => home_url( '/#website' ),
		'url'             => home_url( '/' ),
		'name'            => latent_seo_plain_text( (string) get_bloginfo( 'name', 'display' ) ),
		'inLanguage'      => (string) get_bloginfo( 'language' ),
		'potentialAction' => array(
			'@type'       => 'SearchAction',
			'target'      => array(
				'@type'       => 'EntryPoint',
				'urlTemplate' => home_url( '/?s={search_term_string}' ),
			),
			'query-input' => 'required name=search_term_string',
		),
	);

	$description = latent_seo_plain_text( (string) get_bloginfo( 'description', 'display' ) );

	if ( '' !== $description ) {
		$node['description'] = $description;
	}

	return $node;
}

/**
 * Returns the Organization node used as the article publisher.
 *
 * @return array<string, mixed>
 */
function latent_seo_publisher_node(): array {
	$node = array(
		'@type' => 'Organization',
		'@id'   => home_url( '/#organization' ),
		'name'  => latent_seo_plain_text( (string) get_bloginfo( 'name', 'display' ) ),
		'url'   => home_url( '/' ),
	);

	$icon = get_site_icon_url( 512 );

	if ( '' !== $icon ) {
		$node['logo'] = array(
			'@type' => 'ImageObject',
			'url'   => $icon,
		);
	}

	return $node;
}

/**
 * Returns the Person node for a post author.
 *
 * Only public profile data is exposed: name, author archive URL and bio.
 *
 * @param WP_Post $post Post being rendered.
 * @return array<string, mixed>
 */
function latent_seo_person_node( WP_Post $post ): array {
	$author = get_userdata( (int) $post->post_author );

	if ( ! $author instanceof WP_User ) {
		return array();
	}

	$url  = get_author_posts_url( $author->ID, $author->user_nicename );
	$node = array(
		'@type' => 'Person',
		'@id'   => $url . '#person',
		'name'  => latent_seo_plain_text( (string) $author->display_name ),
		'url'   => $url,
	);

	$description = latent_seo_plain_text( (string) get_the_author_meta( 'description', $author->ID ) );

	if ( '' !== $description ) {
		$node['description'] = $description;
	}

	return $node;
}

/**
 * Returns the primary category for a post.
 *
 * Uncategorized is skipped when any real category is assigned.
 *
 * @param int $post_id Post ID.
 * @return WP_Term|null
 */
function latent_seo_primary_category( int $post_id ): ?WP_Term {
	$categories = get_the_category( $post_id );

	if ( array() === $categories ) {
		return null;
	}

	foreach ( $categories as $category ) {
		if ( 'uncategorized' !== $category->slug ) {
			return $category;
		}
	}

	return $categories[0];
}

/**
 * Returns the BlogPosting node for a single post.
 *
 * @param WP_Post              $post      Post being rendered.
 * @param array<string, mixed> $person    Person node, or an empty array.
 * @param array<string, mixed> $publisher Publisher node.
 * @return array<string, mixed>
 */
function latent_seo_article_node( WP_Post $post, array $person, array $publisher ): array {
	$permalink = (string) get_permalink( $post );

	$node = array(
		'@type'            => 'BlogPosting',
		'@id'              => $permalink . '#article',
		'isPartOf'         => array( '@id' => home_url( '/#website' ) ),
		'mainEntityOfPage' => array(
			'@type' => 'WebPage',
			'@id'   => $permalink,
		),
		'headline'         => latent_seo_plain_text( (string) get_the_title( $post ) ),
		'datePublished'    => (string) get_post_time( 'c', false, $post ),
		'dateModified'     => (string) get_post_modified_time( 'c', false, $post ),
		'inLanguage'       => (string) get_bloginfo( 'language' ),
		'url'              => $permalink,
	);

	$description = latent_seo_description();

	if ( '' !== $description ) {
		$node['description'] = $description;
	}

	if ( array() !== $person && isset( $person['@id'] ) ) {
		$node['author'] = array( '@id' => $person['@id'] );
	}

	if ( isset( $publisher['@id'] ) ) {
		$node['publisher'] = array( '@id' => $publisher['@id'] );
	}

	$image = latent_seo_image( (int) $post->ID );

	if ( array() !== $image ) {
		$node['image'] = array(
			'@type'  => 'ImageObject',
			'url'    => $image['url'],
			'width'  => $image['width'],
			'height' => $image['height'],
		);
	}

	return $node;
}

/**
 * Returns the BreadcrumbList node for a single post.
 *
 * @param WP_Post $post Post being rendered.
 * @return array<string, mixed>
 */
function latent_seo_breadcrumb_node( WP_Post $post ): array {
	$items   = array();
	$items[] = array(
		'@type'    => 'ListItem',
		'position' => 1,
		'name'     => __( 'Trang chủ', 'latent' ),
		'item'     => home_url( '/' ),
	);

	$category = latent_seo_primary_category( (int) $post->ID );

	if ( $category instanceof WP_Term ) {
		$category_url = get_term_link( $category );

		if ( ! is_wp_error( $category_url ) ) {
			$items[] = array(
				'@type'    => 'ListItem',
				'position' => count( $items ) + 1,
				'name'     => latent_seo_plain_text( (string) $category->name ),
				'item'     => esc_url_raw( $category_url ),
			);
		}
	}

	$permalink = (string) get_permalink( $post );

	$items[] = array(
		'@type'    => 'ListItem',
		'position' => count( $items ) + 1,
		'name'     => latent_seo_plain_text( (string) get_the_title( $post ) ),
		'item'     => $permalink,
	);

	return array(
		'@type'           => 'BreadcrumbList',
		'@id'             => $permalink . '#breadcrumb',
		'itemListElement' => $items,
	);
}

/**
 * Prints the canonical tag when core does not already output one.
 *
 * `rel_canonical()` only runs on singular views, so archives need their own
 * tag. Singular views defer to core; if core cannot build a URL, the permalink
 * is printed instead. Search and 404 views are left without a canonical.
 *
 * @return void
 */
function latent_seo_render_canonical(): void {
	if ( is_singular() ) {
		$url = wp_get_canonical_url( (int) get_queried_object_id() );

		if ( is_string( $url ) && '' !== $url ) {
			return;
		}
	} elseif ( is_404() || is_search() ) {
		return;
	}

	$url = latent_seo_canonical_url();

	if ( '' === $url ) {
		return;
	}

	printf( '<link rel="canonical" href="%s" />' . "\n", esc_url( $url ) );
}

/**
 * Prints the description, canonical, Open Graph and Twitter meta tags.
 *
 * @return void
 */
function latent_seo_render_meta(): void {
	$description = latent_seo_description();
	$title       = latent_seo_title();
	$url         = latent_seo_canonical_url();
	$site_name   = latent_seo_plain_text( (string) get_bloginfo( 'name', 'display' ) );
	$post_id     = is_singular() ? (int) get_queried_object_id() : 0;
	$image       = latent_seo_image( $post_id );

	if ( '' !== $description ) {
		printf( '<meta name="description" content="%s" />' . "\n", esc_attr( $description ) );
	}

	latent_seo_render_canonical();

	printf( '<meta property="og:type" content="%s" />' . "\n", is_single() ? 'article' : 'website' );

	if ( '' !== $title ) {
		printf( '<meta property="og:title" content="%s" />' . "\n", esc_attr( $title ) );
		printf( '<meta name="twitter:title" content="%s" />' . "\n", esc_attr( $title ) );
	}

	if ( '' !== $description ) {
		printf( '<meta property="og:description" content="%s" />' . "\n", esc_attr( $description ) );
		printf( '<meta name="twitter:description" content="%s" />' . "\n", esc_attr( $description ) );
	}

	if ( '' !== $url ) {
		printf( '<meta property="og:url" content="%s" />' . "\n", esc_url( $url ) );
	}

	if ( '' !== $site_name ) {
		printf( '<meta property="og:site_name" content="%s" />' . "\n", esc_attr( $site_name ) );
	}

	if ( array() !== $image ) {
		printf( '<meta property="og:image" content="%s" />' . "\n", esc_url( $image['url'] ) );
		printf( '<meta property="og:image:width" content="%s" />' . "\n", esc_attr( (string) $image['width'] ) );
		printf( '<meta property="og:image:height" content="%s" />' . "\n", esc_attr( (string) $image['height'] ) );
		printf( '<meta property="og:image:alt" content="%s" />' . "\n", esc_attr( $image['alt'] ) );
		printf( '<meta name="twitter:image" content="%s" />' . "\n", esc_url( $image['url'] ) );
	}

	printf( '<meta name="twitter:card" content="summary_large_image" />' . "\n" );

	if ( is_single() ) {
		$post = latent_seo_queried_post();

		if ( $post instanceof WP_Post ) {
			$published = (string) get_post_time( 'c', false, $post );
			$modified  = (string) get_post_modified_time( 'c', false, $post );

			if ( '' !== $published ) {
				printf( '<meta property="article:published_time" content="%s" />' . "\n", esc_attr( $published ) );
			}

			if ( '' !== $modified ) {
				printf( '<meta property="article:modified_time" content="%s" />' . "\n", esc_attr( $modified ) );
			}
		}
	}
}

/**
 * Prints one JSON-LD graph for the current view.
 *
 * 404 and search views only receive the WebSite node. Single posts add the
 * publisher, author, BlogPosting and breadcrumb nodes.
 *
 * @return void
 */
function latent_seo_render_json_ld(): void {
	$graph = array( latent_seo_website_node() );

	if ( is_single() ) {
		$post = latent_seo_queried_post();

		if ( $post instanceof WP_Post ) {
			$publisher = latent_seo_publisher_node();
			$person    = latent_seo_person_node( $post );

			$graph[] = $publisher;

			if ( array() !== $person ) {
				$graph[] = $person;
			}

			$graph[] = latent_seo_article_node( $post, $person, $publisher );
			$graph[] = latent_seo_breadcrumb_node( $post );
		}
	}

	$data = array(
		'@context' => 'https://schema.org',
		'@graph'   => $graph,
	);

	$json = wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );

	if ( ! is_string( $json ) ) {
		return;
	}

	// A literal </script> inside user data would close the element early.
	$json = str_replace( '</script', '<\\/script', $json );

	printf( '<script type="application/ld+json">%s</script>' . "\n", $json ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON-LD is JSON-encoded, not HTML.
}

add_action( 'wp_head', 'latent_seo_render_meta', 2 );
add_action( 'wp_head', 'latent_seo_render_json_ld', 3 );
