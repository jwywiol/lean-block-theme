/**
 * Previous/next buttons for the CSS scroll-snap slider.
 *
 * Scrolling itself is native. This script only shows the buttons when there
 * is something to scroll and disables them at either end.
 */
function initSlider( root ) {
	const track = root.querySelector( '[data-lean-slider-track]' );
	const controls = root.querySelector( '[data-lean-slider-controls]' );
	const prev = root.querySelector( '[data-lean-slider-prev]' );
	const next = root.querySelector( '[data-lean-slider-next]' );

	if ( ! track || ! controls || ! prev || ! next ) {
		return;
	}

	let frame = 0;

	const update = () => {
		frame = 0;
		const max = track.scrollWidth - track.clientWidth;
		controls.hidden = max <= 1;
		// scrollLeft is negative in right-to-left layouts.
		const position = Math.abs( track.scrollLeft );
		prev.disabled = position <= 1;
		next.disabled = position >= max - 1;
	};

	const schedule = () => {
		if ( ! frame ) {
			frame = requestAnimationFrame( update );
		}
	};

	const step = ( direction ) => {
		const slide = track.querySelector( 'li' );
		const gap = parseFloat( getComputedStyle( track.firstElementChild ).columnGap ) || 0;
		const distance = slide ? slide.getBoundingClientRect().width + gap : track.clientWidth;
		const rtl = getComputedStyle( track ).direction === 'rtl' ? -1 : 1;
		track.scrollBy( { left: direction * distance * rtl } );
	};

	prev.addEventListener( 'click', () => step( -1 ) );
	next.addEventListener( 'click', () => step( 1 ) );
	track.addEventListener( 'scroll', schedule, { passive: true } );
	new ResizeObserver( schedule ).observe( track );
}

document.querySelectorAll( '[data-lean-slider]' ).forEach( initSlider );
