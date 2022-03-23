/**
 * Single instructor compontent
 *
 * @since 2.1.0
 * @version 2.1.0
 */

// WP Deps.
import {
	Button,
	Dashicon,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';

import Modal from './modal';

import { getTitle, getVisibilities } from '../utils';

const { SortableDragHandle } = window.llms.components;

function VisibilityIcon( { visibility, updatePlan } ) {

	const opts = getVisibilities(),
		{ icon, title, help, color } = opts[ visibility ],
		nextVisibility = () => {
			const keys = Object.keys( opts ),
				currIndex = keys.indexOf( visibility );
			return keys[ currIndex + 1 ] ?? keys[0];
		};

	return (
		<Button
			showTooltip
			isSmall
			style={ { padding: 0 } }
			onClick={ () => updatePlan( { visibility: nextVisibility() } ) }
			label={ `${ title }: ${ help }` }
		>
			<Dashicon style={ { color } } icon={ icon } />
		</Button>
	);

}

/**
 * Instructor list item component
 *
 * @since 2.1.0
 *
 * @param {Object} props Component properties object.
 * @return {Object} Component fragment.
 */
export default function ( props ) {

	const {
			id,
			item: accessPlan,
			index,
			setNodeRef,
			listeners,
			manageState,
		} = props,
		{ title, visibility } = accessPlan,
		{
			updateItem,
			deleteItem,
		} = manageState,
		[ isOpen, setIsOpen ] = useState( false ),
		updatePlan = ( edits ) => updateItem( { ...edits, id } );

	return (
		<>
			<div className="llms-sortable-list--item-header">
				<section>
					<strong>{ getTitle( accessPlan ) }</strong>
					<small>(#{ id })</small>
				</section>
				<aside>
					<VisibilityIcon visibility={ visibility } updatePlan={ updatePlan } />
					<Button
						isSmall
						showTooltip
						label={ __( 'Edit Access Plan', 'lifterlms' ) }
						icon="edit"
						onClick={ () => setIsOpen( true ) }
					/>
					<SortableDragHandle
						label={ __( 'Reorder Access Plan', 'lifterlms' ) }
						setNodeRef={ setNodeRef }
						listeners={ listeners }
					/>
				</aside>
			</div>

			<Modal
				accessPlan={ accessPlan }
				updatePlan={ updatePlan }
				deletePlan={ () => deleteItem( id ) }
				setIsOpen={ setIsOpen }
				isOpen={ isOpen }
				closeOnDelete={ false }
			/>
		</>
	);
}

