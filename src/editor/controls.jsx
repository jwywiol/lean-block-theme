import { MediaUpload, MediaUploadCheck, URLInput } from '@wordpress/block-editor';
import {
	BaseControl,
	Button,
	Flex,
	PanelBody,
	SelectControl,
	TextareaControl,
	TextControl,
	ToggleControl,
} from '@wordpress/components';
import { store as coreStore } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

// Current component sizing, which also silences the deprecation notices for
// the legacy 36px controls and bottom margins.
const modern = { __next40pxDefaultSize: true, __nextHasNoMarginBottom: true };

export const Text = ( props ) => <TextControl { ...modern } { ...props } />;
export const Textarea = ( props ) => <TextareaControl __nextHasNoMarginBottom rows={ 3 } { ...props } />;
export const Toggle = ( props ) => <ToggleControl __nextHasNoMarginBottom { ...props } />;
export const Select = ( props ) => <SelectControl { ...modern } { ...props } />;

/** Vertical stack with consistent spacing for the fields inside a panel. */
export const Fields = ( { children } ) => (
	<Flex direction="column" gap={ 4 }>
		{ children }
	</Flex>
);

/**
 * Binds an attribute name to a change handler: onChange={ bind( 'title' ) }.
 *
 * @param {Function} setAttributes
 */
export const binder = ( setAttributes ) => ( key ) => ( value ) => setAttributes( { [ key ]: value } );

export function HeadingTagControl( { label, value, onChange, tags = [ 'h2', 'h3', 'h4' ] } ) {
	return (
		<Select
			label={ label ?? __( 'Heading level', 'lean-block-theme' ) }
			help={ __( 'Pick the level that fits the page outline. It does not change the look.', 'lean-block-theme' ) }
			value={ value }
			options={ tags.map( ( tag ) => ( {
				value: tag,
				label: tag === 'none' ? __( 'No heading (plain text)', 'lean-block-theme' ) : tag.toUpperCase(),
			} ) ) }
			onChange={ onChange }
		/>
	);
}

/**
 * Eyebrow, title, heading level and intro: the header every section block has.
 */
export function SectionHeaderPanel( { attributes, setAttributes, tags, intro = true, initialOpen = true } ) {
	const bind = binder( setAttributes );
	return (
		<PanelBody title={ __( 'Section header', 'lean-block-theme' ) } initialOpen={ initialOpen }>
			<Fields>
				<Text
					label={ __( 'Eyebrow', 'lean-block-theme' ) }
					value={ attributes.eyebrow }
					onChange={ bind( 'eyebrow' ) }
				/>
				<Textarea
					label={ __( 'Title', 'lean-block-theme' ) }
					help={ __( 'Allowed: <em>, <strong>, <mark> (highlight), <br>.', 'lean-block-theme' ) }
					rows={ 2 }
					value={ attributes.title }
					onChange={ bind( 'title' ) }
				/>
				<HeadingTagControl value={ attributes.titleTag } onChange={ bind( 'titleTag' ) } tags={ tags } />
				{ intro && (
					<Textarea
						label={ __( 'Intro', 'lean-block-theme' ) }
						value={ attributes.intro }
						onChange={ bind( 'intro' ) }
					/>
				) }
			</Fields>
		</PanelBody>
	);
}

/** URL field with suggestions for posts and pages. */
export function UrlField( { label, value, onChange } ) {
	return (
		<BaseControl __nextHasNoMarginBottom label={ label }>
			<URLInput __nextHasNoMarginBottom value={ value } onChange={ ( url ) => onChange( url ) } />
		</BaseControl>
	);
}

/** Primary and secondary button: label + URL each. */
export function ButtonsPanel( { attributes, setAttributes, initialOpen = false } ) {
	const bind = binder( setAttributes );
	return (
		<PanelBody title={ __( 'Buttons', 'lean-block-theme' ) } initialOpen={ initialOpen }>
			<Fields>
				<Text
					label={ __( 'Primary button label', 'lean-block-theme' ) }
					value={ attributes.primaryText }
					onChange={ bind( 'primaryText' ) }
				/>
				<UrlField
					label={ __( 'Primary button link', 'lean-block-theme' ) }
					value={ attributes.primaryUrl }
					onChange={ bind( 'primaryUrl' ) }
				/>
				<Text
					label={ __( 'Secondary button label', 'lean-block-theme' ) }
					value={ attributes.secondaryText }
					onChange={ bind( 'secondaryText' ) }
				/>
				<UrlField
					label={ __( 'Secondary button link', 'lean-block-theme' ) }
					value={ attributes.secondaryUrl }
					onChange={ bind( 'secondaryUrl' ) }
				/>
				<p className="components-base-control__help">
					{ __( 'Leave a label empty to hide that button.', 'lean-block-theme' ) }
				</p>
			</Fields>
		</PanelBody>
	);
}

/**
 * Media library picker storing only the attachment ID. The markup (srcset,
 * width/height, alt text) is generated server-side by wp_get_attachment_image().
 */
export function ImageControl( { label, value = 0, onChange } ) {
	const media = useSelect(
		( select ) => ( value ? select( coreStore ).getMedia( value, { context: 'view' } ) : null ),
		[ value ]
	);
	const preview = media?.media_details?.sizes?.medium?.source_url ?? media?.source_url;

	return (
		<BaseControl __nextHasNoMarginBottom label={ label }>
			{ preview && (
				<img
					src={ preview }
					alt=""
					style={ { display: 'block', width: '100%', height: 'auto', marginBottom: 8, borderRadius: 4 } }
				/>
			) }
			<MediaUploadCheck>
				<Flex justify="flex-start" gap={ 2 }>
					<MediaUpload
						allowedTypes={ [ 'image' ] }
						value={ value }
						onSelect={ ( item ) => onChange( item?.id ?? 0 ) }
						render={ ( { open } ) => (
							<Button variant="secondary" size="compact" onClick={ open }>
								{ value
									? __( 'Replace', 'lean-block-theme' )
									: __( 'Choose image', 'lean-block-theme' ) }
							</Button>
						) }
					/>
					{ !! value && (
						<Button variant="tertiary" size="compact" isDestructive onClick={ () => onChange( 0 ) }>
							{ __( 'Remove', 'lean-block-theme' ) }
						</Button>
					) }
				</Flex>
			</MediaUploadCheck>
		</BaseControl>
	);
}
