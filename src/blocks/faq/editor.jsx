import { InspectorControls } from '@wordpress/block-editor';
import { registerBlockType } from '@wordpress/blocks';
import { PanelBody } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import {
	binder,
	Fields,
	HeadingTagControl,
	Repeater,
	SectionHeaderPanel,
	ServerPreview,
	Text,
	Textarea,
	Toggle,
} from '../../editor';
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
				/>
				<PanelBody title={ __( 'Questions', 'lean-block-theme' ) + ` (${ attributes.items.length })` }>
					<Repeater
						items={ attributes.items }
						onChange={ bind( 'items' ) }
						createItem={ () => ( { question: '', answer: '' } ) }
						label={ ( item ) => item.question }
						addLabel={ __( 'Add question', 'lean-block-theme' ) }
					>
						{ ( item, update ) => (
							<>
								<Text
									label={ __( 'Question', 'lean-block-theme' ) }
									value={ item.question }
									onChange={ ( question ) => update( { question } ) }
								/>
								<Textarea
									label={ __( 'Answer', 'lean-block-theme' ) }
									help={ __(
										'Line breaks become paragraphs. Links are allowed.',
										'lean-block-theme'
									) }
									rows={ 5 }
									value={ item.answer }
									onChange={ ( answer ) => update( { answer } ) }
								/>
							</>
						) }
					</Repeater>
				</PanelBody>
				<PanelBody title={ __( 'Behaviour', 'lean-block-theme' ) } initialOpen={ false }>
					<Fields>
						<Toggle
							label={ __( 'One open at a time', 'lean-block-theme' ) }
							checked={ attributes.exclusive }
							onChange={ bind( 'exclusive' ) }
						/>
						<Toggle
							label={ __( 'Open the first question', 'lean-block-theme' ) }
							checked={ attributes.openFirst }
							onChange={ bind( 'openFirst' ) }
						/>
						<Toggle
							label={ __( 'FAQPage structured data', 'lean-block-theme' ) }
							help={ __(
								'Adds the questions to the page JSON-LD (merged into Yoast SEO when it is active).',
								'lean-block-theme'
							) }
							checked={ attributes.schema }
							onChange={ bind( 'schema' ) }
						/>
						<HeadingTagControl
							label={ __( 'Question heading', 'lean-block-theme' ) }
							value={ attributes.questionTag }
							tags={ [ 'none', 'h3', 'h4' ] }
							onChange={ bind( 'questionTag' ) }
						/>
					</Fields>
				</PanelBody>
			</InspectorControls>
			<ServerPreview name={ name } attributes={ attributes } />
		</>
	);
}

registerBlockType( name, { edit: Edit, save: () => null } );
