/**
 * Users search component
 *
 * @since 2.1.0
 * @version 2.1.0
 */

// WP deps.
import { __ } from '@wordpress/i18n';

// Internal Deps.
import { SearchUser } from '../../components';

/**
 * User search component for searching instructors.
 *
 * @since 2.1.0
 *
 * @param {Object}   props
 * @param {Object[]} props.instructors   Array of instructor objects.
 * @param {string[]} props.roles         Array of WP User roles to include in search results.
 * @param {Function} props.addInstructor Callback function to add a selected instructor to the parent component's state.
 * @return {SearchUser} User search component.
 */
export default function ( { instructors, roles, addInstructor } ) {
	return (
		<SearchUser
			roles={ roles }
			placeholder={ __( 'Search…', 'lifterlms' ) }
			searchArgs={ {
				exclude: instructors.map( ( { id } ) => id ),
			} }
			onChange={ addInstructor }
		/>
	);
}
