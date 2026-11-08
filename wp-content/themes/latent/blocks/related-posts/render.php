<?php
/**
 * Server-side render for the latent/related-posts block.
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

$latent_post_id     = (int) ( $block->context['postId'] ?? get_the_ID() );
$latent_related     = latent_related_get_posts( $latent_post_id, 3 );
$latent_heading_id  = wp_unique_id( 'latent-related-title-' );

if ( empty( $latent_related ) ) {
	return;
}
?>
<section class="latent-related" aria-labelledby="<?php echo esc_attr( $latent_heading_id ); ?>">
	<h2 class="latent-related__title" id="<?php echo esc_attr( $latent_heading_id ); ?>"><?php esc_html_e( 'Bài liên quan', 'latent' ); ?></h2>

	<div class="latent-related__grid latent-grid latent-grid--3">
		<?php foreach ( $latent_related as $latent_related_post ) : ?>
			<?php
			$latent_permalink  = (string) get_permalink( $latent_related_post );
			$latent_categories = get_the_category( $latent_related_post->ID );
			$latent_category   = $latent_categories[0] ?? null;
			$latent_excerpt    = wp_trim_words(
				wp_strip_all_tags( get_the_excerpt( $latent_related_post ) ),
				22,
				'…'
			);
			?>
			<article class="latent-related__card latent-card">
				<?php if ( has_post_thumbnail( $latent_related_post ) ) : ?>
					<a
						class="latent-card__media latent-related__media"
						href="<?php echo esc_url( $latent_permalink ); ?>"
						tabindex="-1"
						aria-hidden="true"
					>
						<?php
						echo get_the_post_thumbnail(
							$latent_related_post,
							'medium_large',
							array(
								'loading'  => 'lazy',
								'decoding' => 'async',
							)
						);
						?>
					</a>
				<?php endif; ?>

				<div class="latent-card__body">
					<div class="latent-card__meta">
						<?php if ( $latent_category instanceof WP_Term ) : ?>
							<span class="latent-card__category">
								<a href="<?php echo esc_url( (string) get_category_link( $latent_category ) ); ?>">
									<?php echo esc_html( $latent_category->name ); ?>
								</a>
							</span>
						<?php endif; ?>
						<time class="latent-card__date" datetime="<?php echo esc_attr( (string) get_the_date( 'c', $latent_related_post ) ); ?>">
							<?php echo esc_html( (string) get_the_date( get_option( 'date_format' ), $latent_related_post ) ); ?>
						</time>
					</div>

					<h3 class="latent-card__title">
						<a href="<?php echo esc_url( $latent_permalink ); ?>">
							<?php echo esc_html( get_the_title( $latent_related_post ) ); ?>
						</a>
					</h3>

					<?php if ( '' !== $latent_excerpt ) : ?>
						<p class="latent-card__excerpt"><?php echo esc_html( $latent_excerpt ); ?></p>
					<?php endif; ?>
				</div>
			</article>
		<?php endforeach; ?>
	</div>
</section>
