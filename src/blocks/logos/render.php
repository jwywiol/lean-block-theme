<?php
/**
 * Logo strip block.
 *
 * Marquee mode renders the list twice and animates both copies with CSS. The
 * copy is `inert` and aria-hidden, so screen readers and the Tab key see each
 * logo once. With prefers-reduced-motion the animation stops and the logos
 * wrap into rows.
 *
 * Images come from the media library with width/height attributes, so the
 * strip does not shift the layout while logos load.
 *
 * @package Lean_Block_Theme
 *
 * @var array<string, mixed> $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$lean_items = array_values(
	array_filter(
		lean_list( $attributes['items'] ?? array() ),
		static fn ( $item ) => ! empty( $item['imageId'] ) || '' !== trim( (string) ( $item['name'] ?? '' ) )
	)
);

if ( ! $lean_items ) {
	return;
}

$lean_marquee = 'marquee' === ( $attributes['layout'] ?? 'grid' );
$lean_classes = 'lean-logos' . ( $lean_marquee ? ' lean-logos--marquee' : '' ) . ( ! empty( $attributes['grayscale'] ) ? ' lean-logos--grayscale' : '' );
$lean_title   = trim( (string) ( $attributes['title'] ?? '' ) );
$lean_tag     = lean_heading_tag( $attributes['titleTag'] ?? 'h2', 'h2', array( 'h2', 'h3', 'h4', 'none' ) );

// Logo list markup, reused for the marquee copy.
ob_start();
foreach ( $lean_items as $lean_item ) {
	$lean_name     = trim( (string) ( $lean_item['name'] ?? '' ) );
	$lean_url      = trim( (string) ( $lean_item['url'] ?? '' ) );
	$lean_image_id = (int) ( $lean_item['imageId'] ?? 0 );
	$lean_alt      = $lean_image_id ? trim( (string) get_post_meta( $lean_image_id, '_wp_attachment_image_alt', true ) ) : '';

	$lean_logo = $lean_image_id ? wp_get_attachment_image(
		$lean_image_id,
		'medium',
		false,
		array(
			'class'   => 'lean-logos__image',
			'alt'     => '' !== $lean_alt ? $lean_alt : $lean_name,
			'loading' => 'lazy',
		)
	) : '';

	if ( ! $lean_logo ) {
		$lean_logo = '<span class="lean-logos__wordmark">' . esc_html( $lean_name ) . '</span>';
	}

	// A link needs an accessible name: the image alt or the logo name.
	if ( '' !== $lean_url && ( '' !== $lean_alt || '' !== $lean_name ) ) {
		$lean_logo = sprintf( '<a class="lean-logos__link" href="%s">%s</a>', esc_url( $lean_url ), $lean_logo );
	}

	echo '<li class="lean-logos__item">' . $lean_logo . '</li>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from core markup and escaped values.
}
$lean_list_items = (string) ob_get_clean();
?>
<section <?php echo get_block_wrapper_attributes( array( 'class' => lean_section_classes( $attributes, $lean_classes ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by core. ?>>
	<div class="lean-container">
		<?php if ( '' !== $lean_title ) : ?>
			<?php
			echo 'none' === $lean_tag
				? '<p class="lean-logos__title">' . lean_inline( $lean_title ) . '</p>' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by the helper.
				: sprintf( '<%1$s class="lean-logos__title">%2$s</%1$s>', $lean_tag, lean_inline( $lean_title ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- allow-listed tag, escaped text.
			?>
		<?php endif; ?>

		<?php if ( $lean_marquee ) : ?>
			<?php // Persistent pause control (WCAG 2.2.2). A checkbox and :has() in CSS, no JavaScript. ?>
			<label class="lean-logos__pause">
				<input type="checkbox" class="lean-sr-only">
				<span class="lean-sr-only"><?php esc_html_e( 'Pause logo animation', 'lean-block-theme' ); ?></span>
				<span class="lean-logos__pause-icon" aria-hidden="true"></span>
			</label>
			<div class="lean-logos__marquee">
				<ul class="lean-logos__list" role="list"><?php echo $lean_list_items; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built above. ?></ul>
				<ul class="lean-logos__list" role="list" aria-hidden="true" inert><?php echo $lean_list_items; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built above. ?></ul>
			</div>
		<?php else : ?>
			<ul class="lean-logos__list grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6" role="list"><?php echo $lean_list_items; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built above. ?></ul>
		<?php endif; ?>
	</div>
</section>
