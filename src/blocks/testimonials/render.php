<?php
/**
 * Testimonials slider block.
 *
 * The track is a plain horizontal list with CSS scroll snapping, so it works
 * with touch, trackpad and keyboard before any JavaScript runs. view.js only
 * reveals the previous/next buttons and keeps their disabled state in sync.
 * Without JavaScript the buttons stay hidden and the list still scrolls.
 *
 * @package Lean_Block_Theme
 *
 * @var array<string, mixed> $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$lean_items = array_values(
	array_filter(
		lean_list( $attributes['items'] ?? array() ),
		static fn ( $item ) => '' !== trim( (string) ( $item['quote'] ?? '' ) )
	)
);

if ( ! $lean_items ) {
	return;
}

$lean_track_id = wp_unique_id( 'lean-testimonials-' );
$lean_title    = trim( wp_strip_all_tags( (string) ( $attributes['title'] ?? '' ) ) );
$lean_label    = '' !== $lean_title ? $lean_title : __( 'Testimonials', 'lean-block-theme' );
?>
<section <?php echo get_block_wrapper_attributes( array( 'class' => lean_section_classes( $attributes, 'lean-testimonials' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by core. ?> data-lean-slider>
	<div class="lean-container">
		<div class="flex flex-wrap items-end justify-between gap-6">
			<?php
			echo lean_section_header( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by the helper.
				array(
					'eyebrow' => (string) ( $attributes['eyebrow'] ?? '' ),
					'title'   => (string) ( $attributes['title'] ?? '' ),
					'tag'     => lean_heading_tag( $attributes['titleTag'] ?? 'h2', 'h2', array( 'h2', 'h3', 'h4' ) ),
					'intro'   => (string) ( $attributes['intro'] ?? '' ),
				)
			);
			?>
			<div class="lean-slider__controls" data-lean-slider-controls hidden>
				<button type="button" class="lean-slider__button" data-lean-slider-prev aria-controls="<?php echo esc_attr( $lean_track_id ); ?>">
					<span class="lean-sr-only"><?php esc_html_e( 'Previous testimonials', 'lean-block-theme' ); ?></span>
					<svg aria-hidden="true" focusable="false" width="20" height="20" viewBox="0 0 20 20"><path d="M12.5 4.5 7 10l5.5 5.5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
				</button>
				<button type="button" class="lean-slider__button" data-lean-slider-next aria-controls="<?php echo esc_attr( $lean_track_id ); ?>">
					<span class="lean-sr-only"><?php esc_html_e( 'Next testimonials', 'lean-block-theme' ); ?></span>
					<svg aria-hidden="true" focusable="false" width="20" height="20" viewBox="0 0 20 20"><path d="M7.5 4.5 13 10l-5.5 5.5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
				</button>
			</div>
		</div>

		<?php // A focusable, labelled region: keyboard users can scroll it with the arrow keys. ?>
		<div class="lean-slider__viewport" id="<?php echo esc_attr( $lean_track_id ); ?>" role="region" aria-label="<?php echo esc_attr( $lean_label ); ?>" tabindex="0" data-lean-slider-track>
			<ul class="lean-slider__track" role="list">
				<?php
				foreach ( $lean_items as $lean_item ) :
					$lean_name     = trim( (string) ( $lean_item['name'] ?? '' ) );
					$lean_role     = trim( (string) ( $lean_item['role'] ?? '' ) );
					$lean_image_id = (int) ( $lean_item['imageId'] ?? 0 );
					$lean_avatar   = $lean_image_id ? wp_get_attachment_image(
						$lean_image_id,
						'thumbnail',
						false,
						array(
							'class' => 'lean-testimonials__avatar',
							'alt'   => '',
						)
					) : '';

					// No photo: initials avatar in CSS, no image request.
					if ( ! $lean_avatar && '' !== $lean_name ) {
						$lean_initials = '';
						foreach ( array_slice( (array) preg_split( '/\s+/u', $lean_name ), 0, 2 ) as $lean_word ) {
							$lean_initials .= mb_strtoupper( mb_substr( $lean_word, 0, 1 ) );
						}
						$lean_avatar = '<span class="lean-testimonials__avatar lean-testimonials__avatar--initials" aria-hidden="true">' . esc_html( $lean_initials ) . '</span>';
					}
					?>
					<li class="lean-slider__slide">
						<figure class="lean-testimonials__card">
							<blockquote class="lean-testimonials__quote">
								<p><?php echo esc_html( (string) $lean_item['quote'] ); ?></p>
							</blockquote>
							<?php if ( '' !== $lean_name ) : ?>
								<figcaption class="lean-testimonials__author">
									<?php echo $lean_avatar; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core markup or escaped initials. ?>
									<span>
										<span class="lean-testimonials__name"><?php echo esc_html( $lean_name ); ?></span>
										<?php if ( '' !== $lean_role ) : ?>
											<span class="lean-testimonials__role"><?php echo esc_html( $lean_role ); ?></span>
										<?php endif; ?>
									</span>
								</figcaption>
							<?php endif; ?>
						</figure>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
</section>
