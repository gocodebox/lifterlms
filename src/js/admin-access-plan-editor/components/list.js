// WP deps.
import { useDispatch } from '@wordpress/data';
import { useState } from '@wordpress/element';
import { Spinner, Button } from '@wordpress/components';
import { Component } from '@wordpress/element';
import { __ } from '@wordpress/i18n';


import { SortableList } from '@lifterlms/components';
import { accessPlanStore } from '@lifterlms/data';

import { withAccessPlans } from '../hooks';

// Internal Deps.
import ListItem from './list-item';

function Placeholder( { children } ) {

	return (
		<div style={ { border: '1px solid #ddd', textAlign: 'center', padding: '24px' } }>
			{ children }
		</div>
	);

}

function List( { plans, arePlansLoading } ) {

	if ( arePlansLoading ) {
		return (
			<Placeholder>
				<Spinner />
				{ __( 'Loading…', 'lifterlms' ) }
			</Placeholder>
		);
	}

	if ( ! plans.length ) {
		return (
			<Placeholder>
				{ __( 'No access plans found…', 'lifterlms' ) }
			</Placeholder>
		);
	}

	const { savePlan, deletePlan, editPlan } = useDispatch( accessPlanStore );

	return (

		<>
			<SortableList
				ListItem={ ListItem }
				items={ plans }
				themeId="default"
				itemClassName="llms-access-plan--list-item"
				manageState={ {
					createItem: savePlan,
					deleteItem: deletePlan,
					updateItem: ( { id, ...edits } ) => editPlan( id, edits ),
					updateItems: ( items ) => items.forEach( ( { id }, menu_order ) => editPlan( id, { menu_order } ) ),
				} }
			/>
		</>

	);

}

export default withAccessPlans( List );

