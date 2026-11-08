<?php
/**
 * RSS feed enhancements.
 *
 * WordPress core renders a valid RSS 2.0 feed but omits featured images. This
 * file adds the Media RSS namespace and one `media:content` element per item,
 * using the same image selection as the Open Graph tags so feed readers show
 * the same cover as social cards.
 *
 * @package latent
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Declares the Media RSS namespace on the RSS 2.0 root element.
 *
 * @return void
 */
function latent_feed_media_namespace(): void {
	echo 'xmlns:media="http://search.yahoo.com/mrss/"' . "\n";
}
add_action( 'rss2_ns', 'latent_feed_media_namespace' );

/**
 * Prints the featured image as a `media:content` element for each item.
 *
 * @return void
 */
function latent_feed_media_content(): void {
	$post_id = (int) get_the_ID();
	$image   = latent_seo_image( $post_id );

	if ( ! isset( $image['url'], $image['width'], $image['height'], $image['alt'] ) ) {
		return;
	}

	$type = (string) wp_check_filetype( $image['url'] )['type'];

	if ( '' === $type ) {
		$type = 'image/jpeg';
	}

	printf(
		'<media:content url="%s" type="%s" medium="image" width="%d" height="%d"><media:title type="plain">%s</media:title></media:content>' . "\n",
		esc_url( $image['url'] ),
		esc_attr( $type ),
		(int) $image['width'],
		(int) $image['height'],
		esc_html( $image['alt'] )
	);
}
add_action( 'rss2_item', 'latent_feed_media_content' );
