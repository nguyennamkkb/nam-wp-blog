<?php
/**
 * Server-side render for the latent/series-list block.
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

$latent_terms = get_terms(
	array(
		'taxonomy'   => 'series',
		'hide_empty' => true,
		'orderby'    => 'name',
		'order'      => 'ASC',
	)
);

if ( is_wp_error( $latent_terms ) || empty( $latent_terms ) ) {
	return;
}
?>
<div class="latent-series-list">
	<ul class="latent-series-list__items">
		<?php foreach ( $latent_terms as $latent_term ) : ?>
			<?php
			$latent_archive = latent_series_get_archive_url( $latent_term );

			if ( '' === $latent_archive ) {
				continue;
			}

			$latent_parts  = latent_series_get_parts( (int) $latent_term->term_id );
			$latent_count  = (int) $latent_term->count;
			$latent_latest = ! empty( $latent_parts ) ? end( $latent_parts ) : null;
			?>
			<li class="latent-series-list__item latent-card">
				<div class="latent-card__body latent-series-list__body">
					<p class="latent-series-list__meta">
						<?php
						printf(
							/* translators: %d: number of posts in the series. */
							esc_html( _n( '%d phần', '%d phần', $latent_count, 'latent' ) ),
							(int) $latent_count
						);
						?>
					</p>
					<h2 class="latent-card__title latent-series-list__title">
						<a href="<?php echo esc_url( $latent_archive ); ?>"><?php echo esc_html( $latent_term->name ); ?></a>
					</h2>
					<?php if ( '' !== $latent_term->description ) : ?>
						<p class="latent-series-list__description"><?php echo esc_html( $latent_term->description ); ?></p>
					<?php endif; ?>
					<?php if ( $latent_latest instanceof WP_Post ) : ?>
						<p class="latent-series-list__latest">
							<span class="latent-series-list__latest-label"><?php esc_html_e( 'Mới nhất:', 'latent' ); ?></span>
							<a href="<?php echo esc_url( (string) get_permalink( $latent_latest ) ); ?>"><?php echo esc_html( get_the_title( $latent_latest ) ); ?></a>
						</p>
					<?php endif; ?>
				</div>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
