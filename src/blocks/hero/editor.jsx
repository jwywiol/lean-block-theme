import { InspectorControls } from '@wordpress/block-editor';
import { registerBlockType } from '@wordpress/blocks';
import { PanelBody } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { ButtonsPanel, ImageControl, SectionHeaderPanel, ServerPreview } from '../../editor';
import { name } from './block.json';

function Edit( { attributes, setAttributes } ) {
	return (
		<>
			<InspectorControls>
				<SectionHeaderPanel attributes={ attributes } setAttributes={ setAttributes } tags={ [ 'h1', 'h2' ] } />
				<ButtonsPanel attributes={ attributes } setAttributes={ setAttributes } initialOpen />
				<PanelBody title={ __( 'Image', 'lean-block-theme' ) } initialOpen={ false }>
					<ImageControl
						label={ __( 'Side image (optional)', 'lean-block-theme' ) }
						value={ attributes.imageId }
						onChange={ ( imageId ) => setAttributes( { imageId } ) }
					/>
				</PanelBody>
			</InspectorControls>
			<ServerPreview name={ name } attributes={ attributes } />
		</>
	);
}

registerBlockType( name, { edit: Edit, save: () => null } );
