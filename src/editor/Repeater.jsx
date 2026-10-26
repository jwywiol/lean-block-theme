import { Button, Card, CardBody, CardHeader, Flex, FlexItem } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';

/**
 * Editable list of items (FAQ questions, testimonials, logos, stats).
 *
 * One item is expanded at a time, so long lists stay short in the sidebar.
 * Items can be added, removed and moved up or down. Every change produces a new
 * array, so the block attribute is never mutated in place.
 *
 * @param {Object}   props
 * @param {Array}    props.items      Current items.
 * @param {Function} props.onChange   Receives the new array.
 * @param {Function} props.createItem Returns an empty item.
 * @param {Function} props.label      ( item, index ) => title shown in the collapsed row.
 * @param {Function} props.children   ( item, update( patch ), index ) => item fields.
 * @param {string}   props.addLabel   Text of the "add" button.
 */
export function Repeater( { items = [], onChange, createItem, label, children, addLabel } ) {
	const [ open, setOpen ] = useState( items.length ? 0 : -1 );

	const update = ( index, patch ) =>
		onChange( items.map( ( item, i ) => ( i === index ? { ...item, ...patch } : item ) ) );

	const remove = ( index ) => {
		onChange( items.filter( ( _, i ) => i !== index ) );
		setOpen( -1 );
	};

	const move = ( from, to ) => {
		if ( to < 0 || to >= items.length ) {
			return;
		}
		const next = [ ...items ];
		next.splice( to, 0, next.splice( from, 1 )[ 0 ] );
		onChange( next );
		setOpen( to );
	};

	const add = () => {
		onChange( [ ...items, createItem() ] );
		setOpen( items.length );
	};

	return (
		<Flex direction="column" gap={ 2 }>
			{ items.map( ( item, index ) => {
				const isOpen = open === index;
				const title =
					label( item, index ) ||
					sprintf( /* translators: %d: item number. */ __( 'Item %d', 'lean-block-theme' ), index + 1 );

				return (
					<Card key={ index } size="small">
						<CardHeader size="small">
							<FlexItem isBlock>
								<Button
									variant="link"
									aria-expanded={ isOpen }
									onClick={ () => setOpen( isOpen ? -1 : index ) }
									style={ {
										textAlign: 'left',
										textDecoration: 'none',
										color: 'inherit',
										fontWeight: 500,
									} }
								>
									{ title }
								</Button>
							</FlexItem>
							<Flex gap={ 0 } expanded={ false }>
								<Button
									size="small"
									icon="arrow-up-alt2"
									label={ __( 'Move up', 'lean-block-theme' ) }
									disabled={ index === 0 }
									onClick={ () => move( index, index - 1 ) }
								/>
								<Button
									size="small"
									icon="arrow-down-alt2"
									label={ __( 'Move down', 'lean-block-theme' ) }
									disabled={ index === items.length - 1 }
									onClick={ () => move( index, index + 1 ) }
								/>
								<Button
									size="small"
									icon="trash"
									isDestructive
									label={ __( 'Remove', 'lean-block-theme' ) }
									onClick={ () => remove( index ) }
								/>
							</Flex>
						</CardHeader>
						{ isOpen && (
							<CardBody size="small">
								<Flex direction="column" gap={ 4 }>
									{ children( item, ( patch ) => update( index, patch ), index ) }
								</Flex>
							</CardBody>
						) }
					</Card>
				);
			} ) }
			<Button variant="secondary" onClick={ add } style={ { justifyContent: 'center' } }>
				{ addLabel ?? __( 'Add item', 'lean-block-theme' ) }
			</Button>
		</Flex>
	);
}
