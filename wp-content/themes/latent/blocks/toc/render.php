<?php
/**
 * Server-side render for the latent/toc block.
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

$latent_post_id  = (int) ( $block->context['postId'] ?? get_the_ID() );
$latent_headings = latent_toc_get_headings( $latent_post_id );

if ( count( $latent_headings ) < 2 ) {
	return;
}

$latent_instance = wp_unique_id( 'latent-toc-' );
$latent_panel_id = $latent_instance . '-panel';
?>
<nav class="latent-toc" aria-label="<?php esc_attr_e( 'Mục lục', 'latent' ); ?>" data-latent-toc>
	<div class="latent-toc__head">
		<h2 class="latent-toc__title"><?php esc_html_e( 'Mục lục', 'latent' ); ?></h2>
		<button
			type="button"
			class="latent-toc__toggle"
			data-latent-toc-toggle
			aria-expanded="false"
			aria-controls="<?php echo esc_attr( $latent_panel_id ); ?>"
		>
			<span class="latent-toc__toggle-text"><?php esc_html_e( 'Mục lục', 'latent' ); ?></span>
			<span class="latent-toc__toggle-icon" aria-hidden="true"></span>
		</button>
	</div>
	<div class="latent-toc__panel" id="<?php echo esc_attr( $latent_panel_id ); ?>">
		<ol class="latent-toc__list">
			<?php foreach ( $latent_headings as $latent_heading ) : ?>
				<li class="latent-toc__item latent-toc__item--h<?php echo esc_attr( (string) $latent_heading['level'] ); ?>">
					<a href="#<?php echo esc_attr( $latent_heading['anchor'] ); ?>">
						<?php echo esc_html( $latent_heading['text'] ); ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ol>
	</div>
</nav>
