import { InspectorControls } from '@wordpress/block-editor';
import { registerBlockType } from '@wordpress/blocks';
import { PanelBody } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import {
	binder,
	Fields,
	HeadingTagControl,
	ImageControl,
	Repeater,
	Select,
	ServerPreview,
	Text,
	Textarea,
	Toggle,
	UrlField,
} from '../../editor';
import { name } from './block.json';

function Edit( { attributes, setAttributes } ) {
	const bind = binder( setAttributes );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Settings', 'lean-block-theme' ) }>
					<Fields>
						<Textarea
							label={ __( 'Title', 'lean-block-theme' ) }
							rows={ 2 }
							value={ attributes.title }
							onChange={ bind( 'title' ) }
						/>
						<HeadingTagControl
							value={ attributes.titleTag }
							tags={ [ 'h2', 'h3', 'h4', 'none' ] }
							onChange={ bind( 'titleTag' ) }
						/>
						<Select
							label={ __( 'Layout', 'lean-block-theme' ) }
							value={ attributes.layout }
							options={ [
								{ value: 'grid', label: __( 'Grid', 'lean-block-theme' ) },
								{ value: 'marquee', label: __( 'Marquee (scrolling)', 'lean-block-theme' ) },
							] }
							onChange={ bind( 'layout' ) }
						/>
						<Toggle
							label={ __( 'Grayscale logos', 'lean-block-theme' ) }
							checked={ attributes.grayscale }
							onChange={ bind( 'grayscale' ) }
						/>
					</Fields>
				</PanelBody>
				<PanelBody title={ __( 'Logos', 'lean-block-theme' ) + ` (${ attributes.items.length })` }>
					<Repeater
						items={ attributes.items }
						onChange={ bind( 'items' ) }
						createItem={ () => ( { name: '', url: '', imageId: 0 } ) }
						label={ ( item ) => item.name }
						addLabel={ __( 'Add logo', 'lean-block-theme' ) }
					>
						{ ( item, update ) => (
							<>
								<Text
									label={ __( 'Name', 'lean-block-theme' ) }
									help={ __(
										'Used as alt text when the image has none, and shown as text when there is no image.',
										'lean-block-theme'
									) }
									value={ item.name }
									onChange={ ( value ) => update( { name: value } ) }
								/>
								<ImageControl
									label={ __( 'Logo', 'lean-block-theme' ) }
									value={ item.imageId }
									onChange={ ( imageId ) => update( { imageId } ) }
								/>
								<UrlField
									label={ __( 'Link (optional)', 'lean-block-theme' ) }
									value={ item.url }
									onChange={ ( url ) => update( { url } ) }
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
