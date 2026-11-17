/**
 * Post Visibility setting component for courses & memberships
 *
 * @since    1.3.0
 * @version  2.5.0
 */

// WP Deps.
import { Button, Dropdown } from '@wordpress/components';
import { compose, ifCondition, withInstanceId } from '@wordpress/compose';
import { withDispatch, withSelect } from '@wordpress/data';
import { Component } from '@wordpress/element';
import { PluginPostStatusInfo } from '@wordpress/edit-post';
import { __ } from '@wordpress/i18n';

// Internal Deps.
import { visibilityOptions } from './options';
import { default as PostVisibilityLabel } from './label';

class PostVisibility extends Component {
	render() {
		const { onUpdateVisibility, instanceId, visibility } = this.props;

		return (
			<PluginPostStatusInfo className="llms-post-visibility">
				<span>
					{ __( 'Catalog & Search Visibility', 'lifterlms' ) }
				</span>
				<div>
					<Dropdown
						className="llms-post-visibility-dropdown"
						contentClassName="llms-post-visibility-content edit-post-post-visibility__dialog"
						renderToggle={ ( { isOpen, onToggle } ) => (
							<Button
								onClick={ onToggle }
								aria-expanded={ isOpen }
								isLink
							>
								<PostVisibilityLabel />
							</Button>
						) }
						renderContent={ () => (
							<fieldset
								key="visibility-selector"
								className="editor-post-visibility__dialog-fieldset"
							>
								<legend className="editor-post-visibility__dialog-legend">
									{ __( 'Catalog Visibility', 'lifterlms' ) }
								</legend>
								{ visibilityOptions.map(
									( { value, label, info } ) => (
										<div
											key={ value }
											className="editor-post-visibility__choice"
										>
											<input
												type="radio"
												name={ `llms-editor-post-visibility__setting-${ instanceId }` }
												value={ value }
												onChange={ () =>
													onUpdateVisibility( value )
												}
												checked={
													value === visibility
												}
												id={ `editor-post-${ value }-${ instanceId }` }
												aria-describedby={ `editor-post-${ value }-${ instanceId }-description` }
												className="editor-post-visibility__dialog-radio"
											/>
											<label
												htmlFor={ `editor-post-${ value }-${ instanceId }` }
												className="editor-post-visibility__dialog-label"
											>
												{ label }
											</label>
											{
												<p
													id={ `llms-editor-post-${ value }-${ instanceId }-description` }
													className="editor-post-visibility__dialog-info"
												>
													{ info }
												</p>
											}
										</div>
									)
								) }
							</fieldset>
						) }
					/>
				</div>
			</PluginPostStatusInfo>
		);
	}
}

export default compose( [
	withSelect( ( select ) => {
		const { getCurrentPostType, getEditedPostAttribute } = select(
			'core/editor'
		);

		return {
			postType: getCurrentPostType(),
			visibility: getEditedPostAttribute( 'visibility' ),
		};
	} ),
	withDispatch( ( dispatch ) => {
		const { editPost } = dispatch( 'core/editor' );

		return {
			onUpdateVisibility( visibility ) {
				editPost( { visibility } );
			},
		};
	} ),
	ifCondition(
		( { postType } ) =>
			-1 !== [ 'course', 'llms_membership' ].indexOf( postType )
	),
	withInstanceId,
] )( PostVisibility );
