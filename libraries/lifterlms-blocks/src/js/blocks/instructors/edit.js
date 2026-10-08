/**
 * Instructors Block edit
 *
 * @since 1.0.0
 * @since 1.8.0 Use imports in favor of "wp." variables.
 *              Use @wordpress/server-side-render in favor of wp.components.ServerSideRender.
 */

// WP deps.
import { Component, Fragment } from '@wordpress/element';
import { compose } from '@wordpress/compose';
import { withSelect } from '@wordpress/data';
import ServerSideRender from '@wordpress/server-side-render';

/**
 * Instructors Block edit component.
 *
 * @since 1.0.0
 */
class InstructorsEdit extends Component {
	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 *
	 * @return {void}
	 */
	constructor() {
		super( ...arguments );

		this.state = {
			instructors: this.props.instructors,
			// Used to force a remount of <ServerSideRender /> after a save finishes.
			ssrNonce: 0,
		};
	}

	/**
	 * When a save transitions from "saving" -> "not saving", bump the nonce
	 * so the SSR component remounts and refetches fresh HTML.
	 *
	 * @param {Object} prevProps
	 */
	componentDidUpdate( prevProps ) {
		if ( prevProps.isSavingPost && ! this.props.isSavingPost ) {
			this.setState( { ssrNonce: Date.now() } );
		}
	}

	/**
	 * Render component
	 *
	 * @since 1.0.0
	 *
	 * @return {Fragment} Component html fragment.
	 */
	render = () => {
		const { name, attributes, post_id } = this.props; // eslint-disable-line camelcase
		const { ssrNonce } = this.state;

		return (
			<Fragment>
				<ServerSideRender
					key={ ssrNonce } // remount -> refetch on save complete
					block={ name }
					attributes={ attributes }
					urlQueryArgs={ { post_id } }
				/>
			</Fragment>
		);
	};
}

/**
 * Compose the component with data select
 *
 * @since 1.8.0
 *
 * @return {InstructorsEdit}
 */
export default compose( [
	withSelect( ( select ) => {
		const { getEditedPostAttribute, getCurrentPostId, isSavingPost } =
			select( 'core/editor' );

		return {
			post_id: getCurrentPostId(),
			instructors: getEditedPostAttribute( 'instructors' ),
			isSavingPost: isSavingPost(),
		};
	} ),
] )( InstructorsEdit );
