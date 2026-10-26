/**
 * Counts numbers up from zero when they scroll into view.
 *
 * The server already printed the final value, so this script only adds
 * motion. It skips users who prefer reduced motion and numbers that are
 * already on screen when the page loads, which would otherwise flash from
 * the final value to zero.
 */
const DURATION = 1400;

const easeOutCubic = ( t ) => 1 - ( 1 - t ) ** 3;

function animate( el ) {
	const target = parseFloat( el.dataset.leanCount );
	const decimals = parseInt( el.dataset.leanDecimals, 10 ) || 0;
	const final = el.dataset.leanFinal;
	const format = new Intl.NumberFormat( document.documentElement.lang || undefined, {
		minimumFractionDigits: decimals,
		maximumFractionDigits: decimals,
	} );
	const start = performance.now();

	const tick = ( now ) => {
		const progress = Math.min( ( now - start ) / DURATION, 1 );
		if ( progress < 1 ) {
			el.textContent = format.format( target * easeOutCubic( progress ) );
			requestAnimationFrame( tick );
		} else {
			// The server's own formatting (WordPress locale), not Intl's.
			el.textContent = final;
		}
	};

	requestAnimationFrame( tick );
}

const numbers = [ ...document.querySelectorAll( '[data-lean-count]' ) ];
const reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

if ( numbers.length && ! reduceMotion && 'IntersectionObserver' in window ) {
	// Read all positions first, then write, to avoid forced layouts.
	const offscreen = numbers.filter( ( el ) => {
		const rect = el.getBoundingClientRect();
		return rect.top >= window.innerHeight || rect.bottom <= 0;
	} );

	const observer = new IntersectionObserver(
		( entries ) => {
			for ( const entry of entries ) {
				if ( entry.isIntersecting ) {
					observer.unobserve( entry.target );
					animate( entry.target );
				}
			}
		},
		{ threshold: 0.6 }
	);

	for ( const el of offscreen ) {
		el.dataset.leanFinal = el.textContent;
		el.textContent = '0';
		observer.observe( el );
	}
}
