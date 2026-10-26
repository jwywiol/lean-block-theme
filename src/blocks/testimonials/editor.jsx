import { InspectorControls } from '@wordpress/block-editor';
import { registerBlockType } from '@wordpress/blocks';
import { PanelBody } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { binder, ImageControl, Repeater, SectionHeaderPanel, ServerPreview, Text, Textarea } from '../../editor';
import { name } from './block.json';

function Edit( { attributes, setAttributes } ) {
	const bind = binder( setAttributes );

	return (
		<>
			<InspectorControls>
				<SectionHeaderPanel
					attributes={ attributes }
					setAttributes={ setAttributes }
					tags={ [ 'h2', 'h3', 'h4' ] }
					initialOpen={ false }
				/>
				<PanelBody title={ __( 'Testimonials', 'lean-block-theme' ) + ` (${ attributes.items.length })` }>
					<Repeater
						items={ attributes.items }
						onChange={ bind( 'items' ) }
						createItem={ () => ( { quote: '', name: '', role: '', imageId: 0 } ) }
						label={ ( item ) => item.name }
						addLabel={ __( 'Add testimonial', 'lean-block-theme' ) }
					>
						{ ( item, update ) => (
							<>
								<Textarea
									label={ __( 'Quote', 'lean-block-theme' ) }
									rows={ 4 }
									value={ item.quote }
									onChange={ ( quote ) => update( { quote } ) }
								/>
								<Text
									label={ __( 'Name', 'lean-block-theme' ) }
									value={ item.name }
									onChange={ ( value ) => update( { name: value } ) }
								/>
								<Text
									label={ __( 'Role and company', 'lean-block-theme' ) }
									value={ item.role }
									onChange={ ( role ) => update( { role } ) }
								/>
								<ImageControl
									label={ __( 'Photo (optional, initials are shown otherwise)', 'lean-block-theme' ) }
									value={ item.imageId }
									onChange={ ( imageId ) => update( { imageId } ) }
								/>
							</>
						) }
					</Repeater>
				</PanelBody>
			</InspectorControls>
			<ServerPreview name={ name } attributes={ attributes } />
		</>
	);
}

registerBlockType( name, { edit: Edit, save: () => null } );
