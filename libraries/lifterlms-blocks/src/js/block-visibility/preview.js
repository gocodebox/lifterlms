/**
 * Preview area for visibility settings on the block list
 *
 * @since 1.1.0
 * @version 2.0.0
 */

// WP Deps.
import { __, sprintf } from '@wordpress/i18n';
import { Component, Fragment } from '@wordpress/element';
import { Dashicon } from '@wordpress/components';

// Internal Deps.
import './editor.scss';
import { getSetting } from './settings';

/**
 * Preview component.
 *
 * Displays a lock icon on the block editor preview area for each block which indicates whether or not the block
 * is open to all or has visibility settings enabled.
 *
 * @since 1.1.0
 */
export default class Preview extends Component {
	/**
	 * Render component
	 *
	 * @since 1.1.0
	 * @since 1.6.0 Use camelCase `className` in favor of `class`.
	 * @since 2.0.0 Improve the information displayed for a restricted block.
	 *
	 * @return {Fragment} Component HTML Fragment.
	 */
	render() {
		const { llms_visibility } = this.props.attributes,
			{ children } = this.props;

		// Return early for defaults.
		if ( 'all' === llms_visibility ) {
			return children;
		}

		return (
			<div className="llms-block-visibility">
				{ children }
				<div className="llms-block-visibility--indicator">
					<Dashicon icon="visibility" />
					<span className="llms-block-visibility--msg">
						{ sprintf(
							// Translators: %s = visibility setting label.
							__(
								'This block is only visible to %s',
								'lifterlms'
							),
							getSetting( llms_visibility )
						) }
					</span>
				</div>
			</div>
		);
	}
}
