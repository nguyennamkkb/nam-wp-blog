<?php
/**
 * Table of contents helpers.
 *
 * Collects the H2/H3 headings of a post from its serialized block content and
 * derives stable, unique anchors. The same algorithm feeds the `render_block_data`
 * filter so the anchors rendered in the post body and the links built by the
 * `latent/toc` block always agree.
 *
 * @package latent
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Returns the ordered H2/H3 headings for a post.
 *
 * @param int $post_id Post ID.
 * @return array<int, array{level: int, text: string, anchor: string}>
 */
function latent_toc_get_headings( int $post_id ): array {
	$content = (string) get_post_field( 'post_content', $post_id );

	if ( '' === $content ) {
		return array();
	}

	$headings = array();
	$seen     = array();

	latent_toc_collect_headings( parse_blocks( $content ), $headings, $seen );

	return $headings;
}

/**
 * Recursively collects H2/H3 headings from parsed blocks.
 *
 * @param array<int, mixed>                                           $blocks   Parsed blocks.
 * @param array<int, array{level: int, text: string, anchor: string}> $headings Collected headings, by reference.
 * @param array<string, bool>                                         $seen     Anchors already used, by reference.
 * @return void
 */
function latent_toc_collect_headings( array $blocks, array &$headings, array &$seen ): void {
	foreach ( $blocks as $block ) {
		if ( ! is_array( $block ) ) {
			continue;
		}

		if ( 'core/heading' === ( $block['blockName'] ?? '' ) ) {
			$level = isset( $block['attrs']['level'] ) ? (int) $block['attrs']['level'] : 2;

			if ( 2 === $level || 3 === $level ) {
				$text = latent_toc_get_heading_text( $block );

				if ( '' !== $text ) {
					$stored_anchor = isset( $block['attrs']['anchor'] ) && is_string( $block['attrs']['anchor'] )
						? trim( $block['attrs']['anchor'] )
						: '';

					if ( '' !== $stored_anchor ) {
						$anchor          = $stored_anchor;
						$seen[ $anchor ] = true;
					} else {
						$anchor = latent_toc_unique_anchor( $text, $seen );
					}

					$headings[] = array(
						'level'  => $level,
						'text'   => $text,
						'anchor' => $anchor,
					);
				}
			}
		}

		if ( ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
			latent_toc_collect_headings( $block['innerBlocks'], $headings, $seen );
		}
	}
}

/**
 * Extracts the readable text from a heading block.
 *
 * @param array<string, mixed> $block Parsed block.
 * @return string
 */
function latent_toc_get_heading_text( array $block ): string {
	$html = isset( $block['innerHTML'] ) && is_string( $block['innerHTML'] ) ? $block['innerHTML'] : '';

	if ( '' === trim( $html ) && isset( $block['innerContent'][0] ) && is_string( $block['innerContent'][0] ) ) {
		$html = $block['innerContent'][0];
	}

	$text = wp_strip_all_tags( $html );
	$text = html_entity_decode( $text, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	$text = preg_replace( '/\s+/u', ' ', $text );

	return trim( (string) $text );
}

/**
 * Returns a unique anchor for a heading.
 *
 * Uses `sanitize_title()` so Vietnamese diacritics become readable ASCII
 * slugs, then appends `-2`, `-3`, ... for repeated headings.
 *
 * @param string              $text Heading text.
 * @param array<string, bool> $seen Anchors already used, by reference.
 * @return string
 */
function latent_toc_unique_anchor( string $text, array &$seen ): string {
	$base = sanitize_title( $text );

	if ( '' === $base ) {
		$base = 'section';
	}

	$anchor  = $base;
	$counter = 2;

	while ( isset( $seen[ $anchor ] ) ) {
		$anchor = $base . '-' . $counter;
		++$counter;
	}

	$seen[ $anchor ] = true;

	return $anchor;
}

/**
 * Returns the shared heading/anchor state for the current render.
 *
 * @return array{post_id: int, seen: array<string, bool>}
 */
function &latent_toc_heading_state(): array {
	static $state = array(
		'post_id' => 0,
		'seen'    => array(),
	);

	return $state;
}

/**
 * Resets the anchor state so each request starts from a clean slate.
 *
 * @return void
 */
function latent_toc_reset_heading_state(): void {
	$state            = &latent_toc_heading_state();
	$state['post_id'] = 0;
	$state['seen']    = array();
}
add_action( 'wp', 'latent_toc_reset_heading_state' );

/**
 * Resolves the post currently being rendered.
 *
 * @param WP_Block|null $parent_block Parent block instance, when available.
 * @return int
 */
function latent_toc_get_context_post_id( ?WP_Block $parent_block = null ): int {
	if ( $parent_block instanceof WP_Block && isset( $parent_block->context['postId'] ) ) {
		return (int) $parent_block->context['postId'];
	}

	return (int) get_the_ID();
}

/**
 * Assigns a stable `anchor` attribute to H2/H3 headings that lack one.
 *
 * @param array<string, mixed> $block        Parsed block being rendered.
 * @param array<string, mixed> $source_block Unmodified parsed block.
 * @param WP_Block|null        $parent_block Parent block instance.
 * @return array<string, mixed>
 */
function latent_toc_assign_heading_anchor( array $block, array $source_block, ?WP_Block $parent_block = null ): array {
	if ( 'core/heading' !== ( $block['blockName'] ?? '' ) ) {
		return $block;
	}

	$level = isset( $block['attrs']['level'] ) ? (int) $block['attrs']['level'] : 2;

	if ( 2 !== $level && 3 !== $level ) {
		return $block;
	}

	$state   = &latent_toc_heading_state();
	$post_id = latent_toc_get_context_post_id( $parent_block );

	if ( $state['post_id'] !== $post_id ) {
		$state['post_id'] = $post_id;
		$state['seen']    = array();
	}

	$anchor = isset( $block['attrs']['anchor'] ) && is_string( $block['attrs']['anchor'] )
		? trim( $block['attrs']['anchor'] )
		: '';

	if ( '' !== $anchor ) {
		$state['seen'][ $anchor ] = true;

		return $block;
	}

	$text = latent_toc_get_heading_text( $block );

	if ( '' === $text ) {
		return $block;
	}

	if ( ! isset( $block['attrs'] ) || ! is_array( $block['attrs'] ) ) {
		$block['attrs'] = array();
	}

	$block['attrs']['anchor'] = latent_toc_unique_anchor( $text, $state['seen'] );

	return $block;
}
add_filter( 'render_block_data', 'latent_toc_assign_heading_anchor', 10, 3 );

/**
 * Adds the generated anchor to the heading markup when core did not.
 *
 * `render_block_data` only supplies the attribute; the core heading render
 * callback does not print wrapper attributes, so the `id` is written here.
 *
 * @param string               $block_content Rendered block markup.
 * @param array<string, mixed> $block         Parsed block.
 * @return string
 */
function latent_toc_inject_heading_id( string $block_content, array $block ): string {
	if ( 'core/heading' !== ( $block['blockName'] ?? '' ) ) {
		return $block_content;
	}

	$level = isset( $block['attrs']['level'] ) ? (int) $block['attrs']['level'] : 2;

	if ( 2 !== $level && 3 !== $level ) {
		return $block_content;
	}

	$anchor = isset( $block['attrs']['anchor'] ) && is_string( $block['attrs']['anchor'] )
		? trim( $block['attrs']['anchor'] )
		: '';

	if ( '' === $anchor ) {
		return $block_content;
	}

	$processor = new WP_HTML_Tag_Processor( $block_content );

	while ( $processor->next_tag() ) {
		if ( in_array( $processor->get_tag(), array( 'H2', 'H3' ), true ) ) {
			if ( null === $processor->get_attribute( 'id' ) ) {
				$processor->set_attribute( 'id', $anchor );
			}

			break;
		}
	}

	return $processor->get_updated_html();
}
add_filter( 'render_block', 'latent_toc_inject_heading_id', 10, 2 );
