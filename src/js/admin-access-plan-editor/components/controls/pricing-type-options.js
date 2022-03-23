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
		label: __( 'Free', 'lifterlms' ),
		value: 'free',
		icon: 'unlock',
		planOptions: {
			price: 0,
			frequency: 0,
		},
	},
	{
		label: __( 'One-Time', 'lifterlms' ),
		value: 'single',
		icon: 'money-alt',
		planOptions: {
			price: 9.99,
			frequency: 0,
		},
	},
	{
		label: __( 'Recurring', 'lifterlms' ),
		value: 'recurring',
		icon: 'update',
		planOptions: {
			price: 9.99,
			frequency: 1,
			length: 0,
			period: 'year',
		},
	},
];

/**
 * Retrieve an object of access plan updates based on the selected pricing type value
 *
 * Locates the newly selected option in the options array and returns the planOptions
 * object from the options list.
 *
 * @since [version]
 *
 * @param {string} value Newly selected option value.
 * @return {Object} Updates object which can be used to update the access plan.
 */
function getPlanOptions( value ) {
	const { planOptions } = options.find( ( { value: optionValue } ) => {
		return value === optionValue;
	} );
	return planOptions;
};

/**
 * Button Group Control for selecting access plan pricing type option
 *
 * @since [version]
 *
 * @param {Object} props              Component properties object.
 * @param {string} props.selected     The current access plan pricing type setting.
 * @param {Function} props.updatePlan Function used to update the pricing type value on the access plan state.
 * @return {ButtonGroupControl} The rendered component.
 */
export default function( { selected, updatePlan } ) {

	return (
		<ButtonGroupControl
			label={ __( 'Plan type', 'lifterlms' ) }
			className="llms-access-plan--billing-type"
			id="llms-access-plan--billing-type"
			selected={ selected }
			options={ options }
			onClick={ ( value ) => updatePlan( getPlanOptions( value ) ) }
		/>
	);

}
