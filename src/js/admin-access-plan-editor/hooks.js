// External deps.
import { orderBy } from 'lodash';

// WP Deps.
import { withSelect } from '@wordpress/data';
import { store as editorStore } from '@wordpress/editor';

// LLMS Deps.
import { accessPlanStore } from '@lifterlms/data';

// Internal deps.
import { getPlanLimit } from './utils';

export function withAccessPlans( Component ) {

	return withSelect( ( select, ownProps ) => {

		const { getCurrentPostId } = select( editorStore ),
			{ getEditedPlans, isLoading } = select( accessPlanStore ),
			query = { post_id: getCurrentPostId() },
			editedPlans = getEditedPlans( query ),
			arePlansLoading = isLoading( query ),
			planLimit = getPlanLimit(),
			plans = editedPlans ? orderBy( editedPlans, 'menu_order', 'asc' ) : [];

		return {
			hasPlanLimitBeenReached: plans.length >= planLimit,
			plans,
			arePlansLoading,
			planLimit,
		};

	} )( Component );

}
