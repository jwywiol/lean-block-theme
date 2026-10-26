import { InspectorControls } from '@wordpress/block-editor';
import { registerBlockType } from '@wordpress/blocks';
import { PanelBody } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { ButtonsPanel, SectionHeaderPanel, Select, ServerPreview } from '../../editor';
import { name } from './block.json';

function Edit( { attributes, setAttributes } ) {
	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Layout', 'lean-block-theme' ) }>
					<Select
						label={ __( 'Alignment', 'lean-block-theme' ) }
						value={ attributes.layout }
						options={ [
							{ value: 'center', label: __( 'Centred', 'lean-block-theme' ) },
							{ value: 'split', label: __( 'Text left, buttons right', 'lean-block-theme' ) },
						] }
						onChange={ ( layout ) => setAttributes( { layout } ) }
					/>
				</PanelBody>
				<SectionHeaderPanel
					attributes={ attributes }
					setAttributes={ setAttributes }
					tags={ [ 'h2', 'h3', 'h4' ] }
				/>
				<ButtonsPanel attributes={ attributes } setAttributes={ setAttributes } initialOpen />
			</InspectorControls>
			<ServerPreview name={ name } attributes={ attributes } />
		</>
	);
}

registerBlockType( name, { edit: Edit, save: () => null } );
