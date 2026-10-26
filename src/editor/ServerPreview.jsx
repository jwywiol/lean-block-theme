import { useBlockProps } from '@wordpress/block-editor';
import ServerSideRender from '@wordpress/server-side-render';
import { useIsDarkBackground } from './tone';

/**
 * Editor preview rendered by the block's own render.php.
 *
 * - Block supports (colours, spacing, custom class) are applied once, by
 *   useBlockProps() on this wrapper, and skipped in the server request, so they
 *   are not applied a second time on the server markup.
 * - POST instead of GET: list attributes (FAQ items, testimonials) would
 *   otherwise produce very long query strings.
 * - Links in the preview would navigate the editor iframe, so clicks on them
 *   only select the block.
 *
 * @param {Object} props
 * @param {string} props.name       Block name.
 * @param {Object} props.attributes Block attributes.
 */
export function ServerPreview( { name, attributes } ) {
	const isDark = useIsDarkBackground( attributes.backgroundColor );
	const blockProps = useBlockProps( { className: isDark ? 'lean-editor-block lean-is-dark' : 'lean-editor-block' } );

	const swallowLinkClick = ( event ) => {
		if ( event.target.closest?.( 'a' ) ) {
			event.preventDefault();
		}
	};

	return (
		<div { ...blockProps } onClickCapture={ swallowLinkClick } onAuxClickCapture={ swallowLinkClick }>
			<ServerSideRender block={ name } attributes={ attributes } httpMethod="POST" skipBlockSupportAttributes />
		</div>
	);
}
