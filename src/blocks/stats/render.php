<?php
/**
 * Stats block.
 *
 * Each number is rendered twice in one grid cell: an invisible copy of the
 * final value sets the width, and the visible copy is what view.js animates.
 * The counter therefore never changes the layout (no CLS). Values that are
 * not plain numbers, such as "24/7", are shown as typed and not animated.
 *
 * @package Lean_Block_Theme
 *
 * @var array<string, mixed> $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$lean_items = array_values(
	array_filter(
		lean_list( $attributes['items'] ?? array() ),
		static fn ( $item ) => '' !== trim( (string) ( $item['value'] ?? '' ) )
	)
);

if ( ! $lean_items ) {
	return;
}

$lean_animate = ! empty( $attributes['animate'] );
$lean_columns = 0 === count( $lean_items ) % 3 ? 'sm:grid-cols-3' : 'sm:grid-cols-2';
?>
<section <?php echo get_block_wrapper_attributes( array( 'class' => lean_section_classes( $attributes, 'lean-stats' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by core. ?>>
	<div class="lean-container grid gap-12 lg:grid-cols-[1fr_2fr] lg:gap-16">
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
		<dl class="lean-stats__list grid grid-cols-1 gap-x-8 gap-y-10 <?php echo esc_attr( $lean_columns ); ?>">
			<?php
			foreach ( $lean_items as $lean_item ) :
				$lean_raw     = trim( (string) $lean_item['value'] );
				$lean_numeric = (bool) preg_match( '/^\d+(?:\.(\d+))?$/', $lean_raw, $lean_match );
				$lean_digits  = isset( $lean_match[1] ) ? strlen( $lean_match[1] ) : 0;
				$lean_display = $lean_numeric ? number_format_i18n( (float) $lean_raw, $lean_digits ) : $lean_raw;
				?>
				<div class="lean-stats__item">
					<dt class="lean-stats__label"><?php echo esc_html( (string) ( $lean_item['label'] ?? '' ) ); ?></dt>
					<?php
					// Built as one string: whitespace between prefix, number and suffix would show up as spaces.
					$lean_number = $lean_numeric && $lean_animate
						? sprintf(
							'<span class="lean-stats__number"><span class="lean-stats__ghost" aria-hidden="true">%1$s</span><span data-lean-count="%2$s" data-lean-decimals="%3$d">%1$s</span></span>',
							esc_html( $lean_display ),
							esc_attr( $lean_raw ),
							$lean_digits
						)
						: '<span class="lean-stats__number">' . esc_html( $lean_display ) . '</span>';

					printf(
						'<dd class="lean-stats__value">%s%s%s</dd>',
						esc_html( (string) ( $lean_item['prefix'] ?? '' ) ),
						$lean_number, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
						esc_html( (string) ( $lean_item['suffix'] ?? '' ) )
					);
					?>
				</div>
			<?php endforeach; ?>
		</dl>
	</div>
</section>
