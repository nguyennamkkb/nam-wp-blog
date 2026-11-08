<?php
/**
 * Server-side render for the latent/series-nav block.
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

$latent_post_id = (int) ( $block->context['postId'] ?? get_the_ID() );
$latent_term    = latent_series_get_primary_term( $latent_post_id );

if ( ! $latent_term instanceof WP_Term ) {
	return;
}

$latent_archive = latent_series_get_archive_url( $latent_term );

if ( '' === $latent_archive ) {
	return;
}

$latent_parts       = latent_series_get_parts( (int) $latent_term->term_id );
$latent_part_count  = count( $latent_parts );
$latent_part_index  = latent_series_get_part_index( $latent_parts, $latent_post_id );
$latent_series_name = $latent_term->name;

/* translators: %s: series name. */
$latent_aria_label = sprintf( __( 'Chuỗi bài: %s', 'latent' ), $latent_series_name );

if ( $latent_part_count <= 1 ) :
	?>
	<nav class="latent-series-nav latent-series-nav--compact" aria-label="<?php echo esc_attr( $latent_aria_label ); ?>">
		<p class="latent-series-nav__eyebrow"><?php esc_html_e( 'Chuỗi bài', 'latent' ); ?></p>
		<p class="latent-series-nav__compact">
			<a class="latent-series-nav__compact-link" href="<?php echo esc_url( $latent_archive ); ?>"><?php echo esc_html( $latent_series_name ); ?></a>
			<span class="latent-series-nav__compact-count"><?php esc_html_e( '1 phần', 'latent' ); ?></span>
		</p>
	</nav>
	<?php
	return;
endif;
?>
<nav class="latent-series-nav" aria-label="<?php echo esc_attr( $latent_aria_label ); ?>">
	<div class="latent-series-nav__head">
		<p class="latent-series-nav__eyebrow"><?php esc_html_e( 'Chuỗi bài', 'latent' ); ?></p>
		<h2 class="latent-series-nav__title">
			<a href="<?php echo esc_url( $latent_archive ); ?>"><?php echo esc_html( $latent_series_name ); ?></a>
		</h2>
		<p class="latent-series-nav__count">
			<?php
			if ( $latent_part_index > 0 ) {
				printf(
					/* translators: 1: current part number, 2: total number of parts. */
					esc_html__( 'Phần %1$d/%2$d', 'latent' ),
					(int) $latent_part_index,
					(int) $latent_part_count
				);
			} else {
				printf(
					/* translators: %d: total number of parts. */
					esc_html__( '%d phần', 'latent' ),
					(int) $latent_part_count
				);
			}
			?>
		</p>
	</div>
	<ol class="latent-series-nav__list">
		<?php foreach ( $latent_parts as $latent_index => $latent_part ) : ?>
			<?php
			$latent_is_current = (int) $latent_part->ID === $latent_post_id;
			$latent_number     = $latent_index + 1;
			?>
			<li class="latent-series-nav__item<?php echo $latent_is_current ? ' is-current' : ''; ?>">
				<a
					class="latent-series-nav__link"
					href="<?php echo esc_url( (string) get_permalink( $latent_part ) ); ?>"
					<?php if ( $latent_is_current ) : ?>
						aria-current="page"
					<?php endif; ?>
				>
					<span class="latent-series-nav__number" aria-hidden="true"><?php echo esc_html( str_pad( (string) $latent_number, 2, '0', STR_PAD_LEFT ) ); ?></span>
					<span class="latent-series-nav__label"><?php echo esc_html( get_the_title( $latent_part ) ); ?></span>
				</a>
			</li>
		<?php endforeach; ?>
	</ol>
</nav>
