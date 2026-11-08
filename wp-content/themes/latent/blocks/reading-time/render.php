<?php
/**
 * Server-side render for the latent/reading-time block.
 *
 * Counts the words of the post being viewed and reports the time needed to
 * read it at 200 words per minute, rounded up with a floor of one minute.
 *
 * @package latent
 *
 * @var array<string, mixed> $attributes Block attributes.
 * @var string               $content    Block content.
 * @var WP_Block             $block      Block instance.
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$latent_post_id = $block->context['postId'] ?? get_the_ID();
$latent_text    = '';

if ( $latent_post_id ) {
	$latent_post = get_post( $latent_post_id );

	if ( $latent_post instanceof WP_Post ) {
		$latent_text = $latent_post->post_content;
	}
}

$latent_words      = preg_split( '/\s+/u', wp_strip_all_tags( $latent_text ), -1, PREG_SPLIT_NO_EMPTY );
$latent_word_count = is_array( $latent_words ) ? count( $latent_words ) : 0;
$latent_minutes    = max( 1, (int) ceil( $latent_word_count / 200 ) );

printf(
	'<span class="latent-reading-time">%s</span>',
	esc_html(
		sprintf(
			/* translators: %d: estimated reading time in minutes. */
			__( '%d phút đọc', 'latent' ),
			$latent_minutes
		)
	)
);
