<?php
/**
 * Call to action block.
 *
 * The background is WordPress' own colour support (palette only, see
 * theme.json). When the chosen colour is dark, lean_section_classes() adds
 * .lean-is-dark, which flips text and button colours.
 *
 * @package Lean_Block_Theme
 *
 * @var array<string, mixed> $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$lean_split   = 'split' === ( $attributes['layout'] ?? 'center' );
$lean_classes = lean_section_classes( $attributes, $lean_split ? 'lean-cta lean-cta--split' : 'lean-cta' );
?>
<section <?php echo get_block_wrapper_attributes( array( 'class' => $lean_classes ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by core. ?>>
	<div class="lean-container flex flex-col gap-8 <?php echo $lean_split ? 'lg:flex-row lg:items-center lg:justify-between' : 'items-center text-center'; ?>">
		<?php
		echo lean_section_header( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by the helper.
			array(
				'eyebrow' => (string) ( $attributes['eyebrow'] ?? '' ),
				'title'   => (string) ( $attributes['title'] ?? '' ),
				'tag'     => lean_heading_tag( $attributes['titleTag'] ?? 'h2', 'h2', array( 'h2', 'h3', 'h4' ) ),
				'intro'   => (string) ( $attributes['intro'] ?? '' ),
				'class'   => $lean_split ? '' : 'lean-section-header--center',
			)
		);
		echo lean_button_row( $attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by the helper.
		?>
	</div>
</section>
