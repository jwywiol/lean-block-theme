import { useSettings } from '@wordpress/block-editor';

/**
 * Whether white text has more contrast on this colour than black text.
 * Same formula as lean_is_dark_color() in inc/template-tags.php.
 *
 * @param {string} hex Colour as #rgb or #rrggbb.
 */
export function isDarkColor( hex = '' ) {
	let value = hex.trim().replace( /^#/, '' );
	if ( value.length === 3 ) {
		value = value.replace( /./g, '$&$&' );
	}
	if ( ! /^[0-9a-f]{6}$/i.test( value ) ) {
		return false;
	}

	const channel = ( i ) => {
		const c = parseInt( value.slice( i, i + 2 ), 16 ) / 255;
		return c <= 0.03928 ? c / 12.92 : ( ( c + 0.055 ) / 1.055 ) ** 2.4;
	};
	const luminance = 0.2126 * channel( 0 ) + 0.7152 * channel( 2 ) + 0.0722 * channel( 4 );

	return 1.05 / ( luminance + 0.05 ) >= ( luminance + 0.05 ) / 0.05;
}

/**
 * Whether the selected palette background is dark.
 *
 * @param {string|undefined} slug backgroundColor attribute.
 */
export function useIsDarkBackground( slug ) {
	const [ palette = [] ] = useSettings( 'color.palette.theme' );
	const color = slug && palette.find( ( entry ) => entry.slug === slug );
	return color ? isDarkColor( color.color ) : false;
}
