<?php
/**
 * Title: Landing page
 * Slug: lean-block-theme/landing
 * Categories: lean
 * Description: All six Lean blocks composed into a landing page for a fictional studio.
 * Keywords: landing, home, demo
 * Post Types: page
 * Block Types: core/post-content
 * Viewport Width: 1400
 *
 * Demo content only: the company, people and numbers are fictional.
 *
 * @package Lean_Block_Theme
 */

defined( 'ABSPATH' ) || exit;

$lean_blocks = array(
	'lean/hero'         => array(
		'eyebrow'       => 'Northwind Studio',
		'title'         => __( 'Fast websites, <mark>built to last</mark>', 'lean-block-theme' ),
		'intro'         => __( 'We design and build WordPress sites with custom blocks instead of page builders: lighter pages, simpler editing and fewer plugins to keep up to date.', 'lean-block-theme' ),
		'primaryText'   => __( 'Start a project', 'lean-block-theme' ),
		'primaryUrl'    => '#contact',
		'secondaryText' => __( 'How we work', 'lean-block-theme' ),
		'secondaryUrl'  => '#faq',
	),
	'lean/logos'        => array(
		'title'    => __( 'Trusted by teams at', 'lean-block-theme' ),
		'titleTag' => 'none',
		'items'    => array(
			array( 'name' => 'Aurora Labs' ),
			array( 'name' => 'Bluepine' ),
			array( 'name' => 'Fieldwork' ),
			array( 'name' => 'Monolith' ),
			array( 'name' => 'Orbitra' ),
			array( 'name' => 'Kestrel & Co' ),
		),
	),
	'lean/stats'        => array(
		'anchor'          => 'results',
		'backgroundColor' => 'contrast',
		'eyebrow'         => __( 'Results', 'lean-block-theme' ),
		'title'           => __( 'Numbers we measure on every launch', 'lean-block-theme' ),
		'intro'           => __( 'Taken from real projects, not from an empty template.', 'lean-block-theme' ),
		'items'           => array(
			array(
				'value'  => '120',
				'suffix' => '+',
				'label'  => __( 'Sites launched', 'lean-block-theme' ),
			),
			array(
				'value' => '98',
				'label' => __( 'Median Lighthouse performance score', 'lean-block-theme' ),
			),
			array(
				'value'  => '1.1',
				'suffix' => ' s',
				'label'  => __( 'Median Largest Contentful Paint', 'lean-block-theme' ),
			),
			array(
				'value'  => '4.9',
				'suffix' => '/5',
				'label'  => __( 'Average client rating', 'lean-block-theme' ),
			),
		),
	),
	'lean/testimonials' => array(
		'anchor'          => 'testimonials',
		'backgroundColor' => 'surface',
		'eyebrow'         => __( 'Testimonials', 'lean-block-theme' ),
		'title'           => __( 'Clients on working with us', 'lean-block-theme' ),
		'items'           => array(
			array(
				'quote' => __( 'Our old site needed eleven plugins to do what this one does with none. Editing is faster, and the pages load in about a second.', 'lean-block-theme' ),
				'name'  => 'Maya Lindqvist',
				'role'  => __( 'Head of Marketing, Aurora Labs', 'lean-block-theme' ),
			),
			array(
				'quote' => __( 'They explained every trade-off in plain language. We always knew what we were paying for and why.', 'lean-block-theme' ),
				'name'  => 'Tom Okafor',
				'role'  => __( 'Founder, Bluepine', 'lean-block-theme' ),
			),
			array(
				'quote' => __( 'The editors on our team learned the new blocks in one afternoon. Nobody has broken a layout since.', 'lean-block-theme' ),
				'name'  => 'Clara Novak',
				'role'  => __( 'Operations Lead, Fieldwork', 'lean-block-theme' ),
			),
			array(
				'quote' => __( 'Security updates used to be a monthly gamble. With so few dependencies, they take minutes now.', 'lean-block-theme' ),
				'name'  => 'Ben Hart',
				'role'  => __( 'CTO, Monolith', 'lean-block-theme' ),
			),
		),
	),
	'lean/faq'          => array(
		'anchor'  => 'faq',
		'eyebrow' => 'FAQ',
		'title'   => __( 'Questions we hear a lot', 'lean-block-theme' ),
		'intro'   => __( 'Still unsure? Send us a message and we reply within one working day.', 'lean-block-theme' ),
		'items'   => array(
			array(
				'question' => __( 'Why custom blocks instead of a page builder?', 'lean-block-theme' ),
				'answer'   => __( 'A page builder ships its own editor, styles and scripts to every visitor. Custom blocks are rendered by your theme, load only the CSS they need and use the editor WordPress already has.', 'lean-block-theme' ),
			),
			array(
				'question' => __( 'Will our team be able to edit the site?', 'lean-block-theme' ),
				'answer'   => __( 'Yes. Every section is a block with clearly labelled fields in the sidebar. Colours and fonts come from the theme, so pages stay on brand whoever edits them.', 'lean-block-theme' ),
			),
			array(
				'question' => __( 'How many plugins will the site need?', 'lean-block-theme' ),
				'answer'   => __( 'Usually only the ones that handle data, such as forms, SEO or a shop. Sliders, accordions, counters and layout tools are part of the theme.', 'lean-block-theme' ),
			),
			array(
				'question' => __( 'What does "fast" mean in numbers?', 'lean-block-theme' ),
				'answer'   => __( 'We aim for a Largest Contentful Paint under 2.5 seconds on a mid-range phone and no layout shift, and we measure it before launch.', 'lean-block-theme' ),
			),
			array(
				'question' => __( 'Do you host the site as well?', 'lean-block-theme' ),
				'answer'   => __( 'We can, but we do not have to. The theme runs on any standard WordPress hosting.', 'lean-block-theme' ),
			),
		),
	),
	'lean/cta'          => array(
		'anchor'        => 'contact',
		'title'         => __( 'Have a project in mind?', 'lean-block-theme' ),
		'intro'         => __( 'Tell us about it. You get an honest estimate within two working days.', 'lean-block-theme' ),
		'primaryText'   => __( 'Book a call', 'lean-block-theme' ),
		'primaryUrl'    => '#contact',
		'secondaryText' => 'hello@example.com',
		'secondaryUrl'  => 'mailto:hello@example.com',
	),
);

foreach ( $lean_blocks as $lean_name => $lean_attributes ) {
	echo get_comment_delimited_block_content( $lean_name, $lean_attributes, '' ) . "\n\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- serialized block markup.
}
