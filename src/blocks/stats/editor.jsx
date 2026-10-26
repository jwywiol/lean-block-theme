import { InspectorControls } from '@wordpress/block-editor';
import { registerBlockType } from '@wordpress/blocks';
import { Flex, FlexBlock, PanelBody } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { binder, Repeater, SectionHeaderPanel, ServerPreview, Text, Toggle } from '../../editor';
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
				<PanelBody title={ __( 'Numbers', 'lean-block-theme' ) + ` (${ attributes.items.length })` }>
					<Repeater
						items={ attributes.items }
						onChange={ bind( 'items' ) }
						createItem={ () => ( { value: '', prefix: '', suffix: '', label: '' } ) }
						label={ ( item ) =>
							[ item.prefix, item.value, item.suffix ].join( '' ) +
							( item.label ? ` · ${ item.label }` : '' )
						}
						addLabel={ __( 'Add number', 'lean-block-theme' ) }
					>
						{ ( item, update ) => (
							<>
								<Text
									label={ __( 'Value', 'lean-block-theme' ) }
									help={ __(
										'Digits with an optional decimal point, e.g. 1200 or 4.9. Anything else is shown as typed and not animated.',
										'lean-block-theme'
									) }
									value={ item.value }
									onChange={ ( value ) => update( { value } ) }
								/>
								<Flex gap={ 2 }>
									<FlexBlock>
										<Text
											label={ __( 'Prefix', 'lean-block-theme' ) }
											value={ item.prefix }
											onChange={ ( prefix ) => update( { prefix } ) }
										/>
									</FlexBlock>
									<FlexBlock>
										<Text
											label={ __( 'Suffix', 'lean-block-theme' ) }
											value={ item.suffix }
											onChange={ ( suffix ) => update( { suffix } ) }
										/>
									</FlexBlock>
								</Flex>
								<Text
									label={ __( 'Label', 'lean-block-theme' ) }
									value={ item.label }
									onChange={ ( label ) => update( { label } ) }
								/>
							</>
						) }
					</Repeater>
				</PanelBody>
				<PanelBody title={ __( 'Behaviour', 'lean-block-theme' ) } initialOpen={ false }>
					<Toggle
						label={ __( 'Count up on scroll', 'lean-block-theme' ) }
						checked={ attributes.animate }
						onChange={ bind( 'animate' ) }
					/>
				</PanelBody>
			</InspectorControls>
			<ServerPreview name={ name } attributes={ attributes } />
		</>
	);
}

registerBlockType( name, { edit: Edit, save: () => null } );
