// WP deps.
import { __ } from '@wordpress/i18n';

// Internal deps.
import { ButtonGroupControl } from '@lifterlms/components';

/**
 * Options List
 *
 * @type {Object[]}
 */
const options = [
	{
		label: __( 'No expiration', 'lifterlms' ),
		value: 'lifetime',
	},
	{
		label: __( 'Expire by period', 'lifterlms' ),
		value: 'limited-period',
	},
	{
		label: __( 'Expire on date', 'lifterlms' ),
		value: 'limited-date',
	},
];

/**
 * Button Group Control for selecting access plan access_expiration option
 *
 * @since [version]
 *
 * @param {Object}   props            Component properties object.
 * @param {string}   props.selected   The current access plan access_expiration setting.
 * @param {Function} props.updatePlan Function used to update the access_expiration value on the access plan state.
 * @return {ButtonGroupControl} The rendered component.
 */
export default function( { selected, updatePlan } ) {

	return (
		<ButtonGroupControl
			label={__( 'Content Access Expiration', 'lifterlms' ) }
			className="llms-access-plan--access-expiration"
			id="llms-access-plan--access-expiration"
			selected={ selected }
			options={ options }
			onClick={ ( access_expiration ) => updatePlan( { access_expiration } ) }
		/>
	);

}
