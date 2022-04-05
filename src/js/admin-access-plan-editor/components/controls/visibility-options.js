// External deps.
import { map } from 'lodash';

// WP deps.
import { __ } from '@wordpress/i18n';

// Internal deps.
import { ButtonGroupControl } from '@lifterlms/components';
import { getVisibilities } from '../../utils';


/**
 * Button Group Control for selecting access plan visibility option
 *
 * @since [version]
 *
 * @param {Object} props            Component properties object.
 * @param {string} props.selected   The current access plan visibility setting.
 * @param {Function} props.updatePlan Function used to update the visibility value on the access plan state.
 * @return {ButtonGroupControl} The rendered component.
 */
export default function( { selected, updatePlan } ) {

	const opts = getVisibilities(),
		{ help } = opts[ selected ];

	return (
		<ButtonGroupControl
			label={ __( 'Visibility', 'lifterlms' ) }
			help={ help }
			className="llms-access-plan--visibility"
			id="llms-access-plan--visibility"
			selected={ selected }
			options={ map( opts, ( { title: label, icon }, value ) => ( { label, value, icon } ) ) }
			onClick={ ( visibility ) => updatePlan( { visibility } ) }
		/>
	);

}
