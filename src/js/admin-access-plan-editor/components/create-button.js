import { Button, Dashicon, Tooltip } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useDispatch, useSelect } from '@wordpress/data';
import { store as editorStore } from '@wordpress/editor';
import { useState } from '@wordpress/element';
import { applyFilters } from '@wordpress/hooks';

// LLMS deps.
import { accessPlanStore } from '@lifterlms/data';

import { withAccessPlans } from '../hooks';
import Modal from './modal';

function CreateButton( { plans = [], order = null, arePlansLoading = false, hasPlanLimitBeenReached = false } ) {

	const { getCurrentPostId } = useSelect( editorStore ),
		{ savePlan, deletePlan, editPlan } = useDispatch( accessPlanStore ),
		[ isCreating, setIsCreating ] = useState( false ),
		[ isOpen, setIsOpen ] = useState( false ),
		[ createdPlan, setCreatedPlan ] = useState( null ),
		{ menu_order: lastOrder } = plans.length ? plans.at( -1 ) : { menu_order: -1 };

	return (
		<>
			<Button
				isSecondary
				showTooltip
				isBusy={ isCreating }
				disabled={ arePlansLoading || hasPlanLimitBeenReached }
				style={ { marginTop: '10px' } }
				onClick={ async () => {
					setIsCreating( true );
					const newPlan = applyFilters(
						'llms.newAccessPlanDefaults',
						{
							title: __( 'New Access Plan', 'lifterlms' ),
							price: 0,
							post_id: getCurrentPostId(),
							menu_order: lastOrder + 1,
							visibility: 'hidden',
						}
					);
					setCreatedPlan( await savePlan( newPlan ) );
					setIsCreating( false );
					setIsOpen( true );
				} }
			>
				{ __( 'Add New Access Plan', 'lifterlms' ) }
			</Button>
			{ hasPlanLimitBeenReached && (
				<Tooltip text={ __( 'The maximum number of allowed access plans has been reached.', 'lifterlms' ) }>
					<Dashicon icon="editor-help" style={ { verticalAlign: 'middle', color: '#828282' } } />
				</Tooltip>
			) }

			<Modal
				accessPlan={ createdPlan }
				updatePlan={ ( edits ) => editPlan( createdPlan.id, { ...edits } ) }
				deletePlan={ async () => await deletePlan( createdPlan.id ) }
				setIsOpen={ setIsOpen }
				isOpen={ isOpen }
			/>
		</>
	);

}

export default withAccessPlans( CreateButton );
