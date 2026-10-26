<?php
/**
 * Hero block.
 *
 * Without an image the content is centred. With an image the section switches
 * to two columns, and the image loads with fetchpriority="high", because it is
 * usually the Largest Contentful Paint element.
 *
 * @package Lean_Block_Theme
 *
 * @var array<string, mixed> $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$lean_image_id = (int) ( $attributes['imageId'] ?? 0 );
$lean_image    = $lean_image_id ? wp_get_attachment_image(
	$lean_image_id,
	'large',
	false,
	array(
		'class'         => 'lean-hero__image',
		'loading'       => 'eager',
		'fetchpriority' => 'high',
		'decoding'      => 'async',
		'sizes'         => '(min-width: 64rem) 36rem, 100vw',
	)
) : '';

$lean_classes = lean_section_classes( $attributes, $lean_image ? 'lean-hero lean-hero--split' : 'lean-hero' );
?>
<section <?php echo get_block_wrapper_attributes( array( 'class' => $lean_classes ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by core. ?>>
	<div class="lean-container grid items-center gap-12 <?php echo $lean_image ? 'lg:grid-cols-[1.1fr_1fr] lg:gap-16' : ''; ?>">
		<div class="lean-hero__content">
			<?php
			echo lean_section_header( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by the helper.
				array(
					'eyebrow' => (string) ( $attributes['eyebrow'] ?? '' ),
					'title'   => (string) ( $attributes['title'] ?? '' ),
					'tag'     => lean_heading_tag( $attributes['titleTag'] ?? 'h1', 'h1', array( 'h1', 'h2' ) ),
					'intro'   => (string) ( $attributes['intro'] ?? '' ),
					'class'   => $lean_image ? 'lean-hero__header' : 'lean-hero__header lean-section-header--center',
				)
			);
			echo lean_button_row( $attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by the helper.
			?>
		</div>
		<?php if ( $lean_image ) : ?>
			<figure class="lean-hero__media"><?php echo $lean_image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core markup. ?></figure>
		<?php endif; ?>
	</div>
</section>
