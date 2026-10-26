<?php
/**
 * FAQ block.
 *
 * Built on <details>/<summary>, so opening, closing and keyboard support come
 * from the browser and the block has no JavaScript. With "exclusive" enabled,
 * all items share a `name`, which makes the browser keep only one open at a
 * time (exclusive accordion). Older browsers simply allow several open.
 *
 * Question headings are off by default. A heading inside <summary> sits inside
 * an element with the implicit button role, and some screen readers then
 * announce only the button, not the heading. Turn them on when the page
 * outline needs them.
 *
 * @package Lean_Block_Theme
 *
 * @var array<string, mixed> $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$lean_items = array_values(
	array_filter(
		lean_list( $attributes['items'] ?? array() ),
		static fn ( $item ) => '' !== trim( (string) ( $item['question'] ?? '' ) )
	)
);

if ( ! $lean_items ) {
	return;
}

if ( ! empty( $attributes['schema'] ) ) {
	lean_faq_schema_add( $lean_items );
}

$lean_question_tag = lean_heading_tag( $attributes['questionTag'] ?? 'none', 'none', array( 'none', 'h3', 'h4' ) );
$lean_group        = ! empty( $attributes['exclusive'] ) ? wp_unique_id( 'lean-faq-' ) : '';
?>
<section <?php echo get_block_wrapper_attributes( array( 'class' => lean_section_classes( $attributes, 'lean-faq' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by core. ?>>
	<div class="lean-container grid gap-10 lg:grid-cols-[2fr_3fr] lg:gap-16">
		<?php
		echo lean_section_header( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by the helper.
			array(
				'eyebrow' => (string) ( $attributes['eyebrow'] ?? '' ),
				'title'   => (string) ( $attributes['title'] ?? '' ),
				'tag'     => lean_heading_tag( $attributes['titleTag'] ?? 'h2', 'h2', array( 'h2', 'h3', 'h4' ) ),
				'intro'   => (string) ( $attributes['intro'] ?? '' ),
				'class'   => 'lean-faq__header',
			)
		);
		?>
		<div class="lean-faq__list">
			<?php foreach ( $lean_items as $lean_index => $lean_item ) : ?>
				<details class="lean-faq__item"<?php echo $lean_group ? ' name="' . esc_attr( $lean_group ) . '"' : ''; ?><?php echo ( 0 === $lean_index && ! empty( $attributes['openFirst'] ) ) ? ' open' : ''; ?>>
					<summary class="lean-faq__summary">
						<?php
						$lean_question = esc_html( (string) $lean_item['question'] );
						echo 'none' === $lean_question_tag
							? '<span class="lean-faq__question">' . $lean_question . '</span>' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
							: sprintf( '<%1$s class="lean-faq__question">%2$s</%1$s>', $lean_question_tag, $lean_question ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- allow-listed tag, escaped text.
						?>
						<span class="lean-faq__icon" aria-hidden="true"></span>
					</summary>
					<div class="lean-faq__answer">
						<?php echo wp_kses_post( wpautop( (string) ( $lean_item['answer'] ?? '' ) ) ); ?>
					</div>
				</details>
			<?php endforeach; ?>
		</div>
	</div>
</section>
