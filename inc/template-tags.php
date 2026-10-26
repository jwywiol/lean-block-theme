<?php
/**
 * Rendering helpers shared by the blocks in src/blocks.
 *
 * Every helper returns escaped HTML, so render.php files can echo the result
 * directly.
 *
 * @package Lean_Block_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Returns a heading tag from an allow-list, never the raw attribute value.
 *
 * @param mixed              $value    Attribute value.
 * @param string             $fallback Tag used when the value is not allowed.
 * @param array<int, string> $allowed  Allowed tags.
 */
function lean_heading_tag( $value, string $fallback = 'h2', array $allowed = array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ) ): string {
	return in_array( $value, $allowed, true ) ? $value : $fallback;
}

/**
 * Inline HTML editors may use in short texts such as titles: emphasis, line
 * breaks and a <mark> highlight. Anything else is stripped.
 *
 * @return array<string, array<string, bool>>
 */
function lean_inline_allowed_html(): array {
	return array(
		'strong' => array(),
		'em'     => array(),
		'mark'   => array(),
		'br'     => array(),
		'span'   => array( 'class' => true ),
	);
}

/**
 * Escapes a short text, keeping the inline tags from lean_inline_allowed_html().
 *
 * @param string $text Text to escape.
 */
function lean_inline( string $text ): string {
	return wp_kses( $text, lean_inline_allowed_html() );
}

/**
 * Renders a link styled as a button.
 *
 * It uses WordPress' .wp-element-button class, so it inherits the button
 * styles defined in theme.json (styles.elements.button), the same as core
 * Button blocks.
 *
 * @param string $text    Label. An empty label renders nothing.
 * @param string $url     Target URL.
 * @param string $variant 'primary' or 'secondary'.
 */
function lean_button( string $text, string $url, string $variant = 'primary' ): string {
	$text = trim( $text );

	if ( '' === $text ) {
		return '';
	}

	$class = 'secondary' === $variant
		? 'wp-element-button lean-button lean-button--secondary'
		: 'wp-element-button lean-button';

	return sprintf(
		'<a class="%1$s" href="%2$s">%3$s</a>',
		esc_attr( $class ),
		esc_url( '' !== trim( $url ) ? $url : '#' ),
		esc_html( $text )
	);
}

/**
 * Renders up to two buttons in a flex row.
 *
 * @param array<string, mixed> $attributes Block attributes with primaryText/primaryUrl and secondaryText/secondaryUrl.
 */
function lean_button_row( array $attributes ): string {
	$buttons = lean_button( (string) ( $attributes['primaryText'] ?? '' ), (string) ( $attributes['primaryUrl'] ?? '' ) )
		. lean_button( (string) ( $attributes['secondaryText'] ?? '' ), (string) ( $attributes['secondaryUrl'] ?? '' ), 'secondary' );

	return '' === $buttons ? '' : '<div class="lean-buttons">' . $buttons . '</div>';
}

/**
 * Renders a section header: optional eyebrow, heading and intro text.
 *
 * @param array{eyebrow?: string, title?: string, tag?: string, intro?: string, class?: string} $args Header parts.
 */
function lean_section_header( array $args ): string {
	$eyebrow = trim( (string) ( $args['eyebrow'] ?? '' ) );
	$title   = trim( (string) ( $args['title'] ?? '' ) );
	$intro   = trim( (string) ( $args['intro'] ?? '' ) );
	$tag     = lean_heading_tag( $args['tag'] ?? 'h2' );
	$class   = trim( 'lean-section-header ' . ( $args['class'] ?? '' ) );

	if ( '' === $eyebrow && '' === $title && '' === $intro ) {
		return '';
	}

	$html = '';

	if ( '' !== $eyebrow ) {
		$html .= '<p class="lean-eyebrow">' . esc_html( $eyebrow ) . '</p>';
	}

	if ( '' !== $title ) {
		$html .= sprintf( '<%1$s class="lean-section-header__title">%2$s</%1$s>', $tag, lean_inline( $title ) );
	}

	if ( '' !== $intro ) {
		$html .= '<p class="lean-section-header__intro">' . lean_inline( $intro ) . '</p>';
	}

	return '<header class="' . esc_attr( $class ) . '">' . $html . '</header>';
}

/**
 * Whether the block's background is a dark palette colour.
 *
 * The answer comes from the colour's relative luminance (WCAG formula), not
 * from a hard-coded list of slugs, so it stays correct when the palette in
 * theme.json changes. src/editor/tone.js does the same calculation in the
 * editor.
 *
 * @param array<string, mixed> $attributes Block attributes.
 */
function lean_is_dark_background( array $attributes ): bool {
	$slug = (string) ( $attributes['backgroundColor'] ?? '' );

	if ( '' === $slug ) {
		return false;
	}

	static $palette = null;

	if ( null === $palette ) {
		$palette = array();
		foreach ( (array) wp_get_global_settings( array( 'color', 'palette', 'theme' ) ) as $color ) {
			if ( isset( $color['slug'], $color['color'] ) ) {
				$palette[ $color['slug'] ] = $color['color'];
			}
		}
	}

	return isset( $palette[ $slug ] ) && lean_is_dark_color( $palette[ $slug ] );
}

/**
 * Whether white text has more contrast on this colour than black text.
 *
 * @param string $hex Colour as #rgb or #rrggbb.
 */
function lean_is_dark_color( string $hex ): bool {
	$hex = ltrim( trim( $hex ), '#' );

	if ( 3 === strlen( $hex ) ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}

	if ( ! preg_match( '/^[0-9a-f]{6}$/i', $hex ) ) {
		return false;
	}

	$channel = static function ( string $pair ): float {
		$value = hexdec( $pair ) / 255;
		return $value <= 0.03928 ? $value / 12.92 : ( ( $value + 0.055 ) / 1.055 ) ** 2.4;
	};

	$luminance = 0.2126 * $channel( substr( $hex, 0, 2 ) )
		+ 0.7152 * $channel( substr( $hex, 2, 2 ) )
		+ 0.0722 * $channel( substr( $hex, 4, 2 ) );

	// Contrast with white vs. contrast with black.
	return ( 1.05 / ( $luminance + 0.05 ) ) >= ( ( $luminance + 0.05 ) / 0.05 );
}

/**
 * Class list for a section wrapper: shared section class, block class and
 * the dark tone modifier when needed.
 *
 * @param array<string, mixed> $attributes Block attributes.
 * @param string               $classes    Block-specific classes.
 */
function lean_section_classes( array $attributes, string $classes ): string {
	return trim( 'lean-section ' . $classes . ( lean_is_dark_background( $attributes ) ? ' lean-is-dark' : '' ) );
}

/**
 * Returns a list attribute as an array of arrays, dropping malformed entries.
 *
 * @param mixed $items Raw attribute value.
 * @return array<int, array<string, mixed>>
 */
function lean_list( $items ): array {
	if ( ! is_array( $items ) ) {
		return array();
	}

	return array_values( array_filter( $items, 'is_array' ) );
}
