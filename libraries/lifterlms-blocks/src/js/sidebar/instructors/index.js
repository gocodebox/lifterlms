/**
 * Instructors Sidebar Plugin
 *
 * @since 1.0.0
 * @version 2.1.0
 */

// WP deps.
import { applyFilters } from '@wordpress/hooks';
import { compose } from '@wordpress/compose';
import { withDispatch, withSelect } from '@wordpress/data';
import { Component } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { PanelBody } from '@wordpress/components';

// Internal Deps.
import { SortableList } from '../../components';
import ListItem from './list-item';
import Search from './search';
import './editor.scss';

/**
 * Instructors Sidebar Plugin Component
 *
 * @since 1.0.0
 * @since 1.7.1 Fix WordPress 5.3 issues with JSON data.
 */
class Instructors extends Component {
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @since 1.7.1 Parse instructor data if it's stored as a JSON string.
	 *
	 * @return {void}
	 */
	constructor() {
		super( ...arguments );

		let { instructors } = this.props;
		instructors =
			'string' === typeof instructors
				? JSON.parse( instructors )
				: instructors;

		this.state = {
			instructors: instructors || [],
			search: '',
		};
	}

	/**
	 * Retrieve a list of WordPress roles that are allowed to be listed as an instructor.
	 *
	 * @since 1.0.0
	 *
	 * @return {Array<string>} Array of user roles.
	 */
	getRoles() {
		/**
		 * Filters user roles allowed to be listed as an instructor
		 *
		 * @since 1.0.0
		 *
		 * @param {string[]} roles List of WP User roles.
		 */
		return applyFilters( 'llms_instructor_roles', [
			'administrator',
			'lms_manager',
			'instructor',
			'instructors_assistant',
		] );
	}

	/**
	 * Change event for the search box.
	 *
	 * Updates the "search" state parameter with search results.
	 *
	 * @since 1.0.0
	 *
	 * @param {Array} result Array of instructor data objects.
	 * @return {void}
	 */
	onSearchChange = ( result ) => {
		this.setState( {
			search: result,
		} );
	};

	/**
	 * Retrieve default instructor information.
	 *
	 * @since 1.0.0
	 *
	 * @return {Object} Default instructor information.
	 */
	getInstructorDefaults() {
		/**
		 * Filters default instructor information
		 *
		 * @since 1.0.0
		 *
		 * @param {Object} defaults Default instructor information.
		 */
		return applyFilters( 'llms_instructor_defaults', {
			label: __( 'Author', 'lifterlms' ),
			visibility: 'visible',
		} );
	}

	/**
	 * Update a single instructor by ID
	 *
	 * @since 2.1.0
	 *
	 * @param {number} id   WP_User ID.
	 * @param {Object} data Instructor information to update.
	 * @return {void}
	 */
	updateInstructor = ( id, data ) => {
		const { instructors } = this.state;

		const newInstructors = instructors.map( ( instructor ) => {
			if ( id === instructor.id ) {
				instructor = {
					...instructor,
					...data,
				};
			}

			return instructor;
		} );

		this.updateInstructors( newInstructors );
	};

	/**
	 * Update instructors list in the state and persist to the database via WP core data dispatcher.
	 *
	 * @since 1.0.0
	 *
	 * @param {Array} newInstructors Array of instructor data objects.
	 * @return {void}
	 */
	updateInstructors = ( newInstructors ) => {
		this.setState( {
			instructors: newInstructors,
		} );
		this.props.updateInstructors( newInstructors );
	};

	/**
	 * Add a new instructor from the selected search result.
	 *
	 * @since 1.0.0
	 * @since 2.0.0 Don't use data from removed `_users` property.
	 *
	 * @param {Object} props
	 * @param {number} props.id   WP_User Id.
	 * @param {string} props.name WP_User name.
	 * @return {void}
	 */
	addInstructor = ( { id, name } ) => {
		const { instructors } = this.state;

		instructors.push( {
			...this.getInstructorDefaults(),
			id,
			name,
		} );

		this.updateInstructors( instructors );
	};

	/**
	 * Remove an instructor
	 *
	 * @since 1.0.0
	 *
	 * @param {Object} toRemove Instructor data object of the instructor to remove.
	 * @return {void}
	 */
	removeInstructor = ( toRemove ) => {
		let { instructors } = this.state;

		instructors = instructors.filter( ( { id } ) => {
			return toRemove.id !== id;
		} );

		this.updateInstructors( instructors );
	};

	/**
	 * Render the component.
	 *
	 * @since 1.0.0
	 * @since 2.0.0 Exclude currently selected users from search query.
	 * @since 2.1.0 Reworked to utilize @dndkit in favor of react-sortable-hoc.
	 *
	 * @return {Object} HTML Fragment.
	 */
	render = () => (
		<PanelBody title={ __( 'Instructors', 'lifterlms' ) }>
			<Search
				roles={ this.getRoles() }
				instructors={ this.state.instructors }
				addInstructor={ this.addInstructor }
			/>
			<SortableList
				ListItem={ ListItem }
				items={ this.state.instructors }
				itemClassName="llms-instructor"
				manageState={ {
					createItem: this.addInstructor,
					deleteItem: this.removeInstructor,
					updateItem: this.updateInstructor,
					updateItems: this.updateInstructors,
				} }
			/>
		</PanelBody>
	);
}

/**
 * Add instructors property to the post during data selection.
 *
 * @since 1.0.0
 *
 * @param {Object} select Reference to wp.data.select.
 * @return {Object}
 */
const applyWithSelect = withSelect( ( select ) => {
	const { getEditedPostAttribute } = select( 'core/editor' );
	return {
		instructors: getEditedPostAttribute( 'instructors' ),
	};
} );

/**
 * Add instructors property to the post durinp data selection.
 *
 * @since 1.0.0
 * @since 1.7.1 Dispatch instructors list as a JSON string.
 *
 * @param {Object} dispatch Reference to wp.data.dispatch.
 * @return {Object}
 */
const applyWithDispatch = withDispatch( ( dispatch ) => {
	const { editPost } = dispatch( 'core/editor' );
	return {
		updateInstructors( instructors ) {
			editPost( { instructors: JSON.stringify( instructors ) } );
		},
	};
} );

/**
 * Compose the component with the data select and dispatch properties.
 *
 * @since 1.0.0
 */
export default compose( [ applyWithSelect, applyWithDispatch ] )( Instructors );
