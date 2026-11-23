/**
 * "Form Settings" metabox locate in the "PluginDocumentSettingPanel" slot.
 *
 * Displays only on `llms_form` post types.
 *
 * @since 1.6.0
 * @version 2.3.2
 */

// WP Deps.
import { parse } from '@wordpress/blocks';
import {
	Button,
	ExternalLink,
	PanelRow,
	ToggleControl,
	TextControl,
} from '@wordpress/components';
import { compose } from '@wordpress/compose';
import { dispatch, select, withDispatch, withSelect } from '@wordpress/data';
import { store as blockEditorStore } from '@wordpress/block-editor';
import { PluginDocumentSettingPanel } from '@wordpress/edit-post';
import { store as editorStore } from '@wordpress/editor';
import { store as noticesStore } from '@wordpress/notices';
import { Component, Fragment } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

// Internal Deps.
import LLMSFormDocSettings from './slot-fill';
import { store as fieldsStore } from '../../data/fields';

/**
 * Render the "Form Settings" metabox in the "PluginDocumentSettingPanel" slot.
 *
 * @since 1.6.0
 * @since 1.7.0 Return early during renders on WP Core 5.2 and earlier where the `PluginDocumentSettingPanel` doesn't exist.
 */
class FormDocumentSettings extends Component {
	/**
	 * Render the Sidebar.
	 *
	 * @since 1.6.0
	 * @since 1.7.0 Add early return for WP Core 5.2 and earlier where the `PluginDocumentSettingPanel` doesn't exist.
	 *              Add link to form location on frontend if available.
	 * @since 1.12.0 Add a class name to the document sidebar.
	 *               Add default template restoration.
	 * @since 2.0.0 Use default template from location definition in favor of from metadata.
	 * @since 2.3.2 Add control for the checkout's form title for free access plans.
	 *
	 * @return {?Fragment} Component HTML fragment or null when not supported.
	 */
	render = () => {
		// This slot doesn't exist until WordPress 5.3.
		if ( 'undefined' === typeof PluginDocumentSettingPanel ) {
			return null;
		} else if (
			'llms_form' !== select( editorStore ).getCurrentPostType()
		) {
			return null;
		}

		const {
			location,
			link,
			showTitle,
			freeApTitle,
			setFormMetas,
		} = this.props;
		const { formLocations } = window.llms,
			currentLoc = formLocations[ location ];

		// Set default values.
		if ( '' === showTitle ) {
			setFormMetas( { _llms_form_show_title: 'yes' } );
		}

		/**
		 * Replace all blocks in the current form with the blocks from a template string
		 *
		 * @since 1.12.0
		 *
		 * @param {string} template A block template (in the form of the content stored in the `post_content` field).
		 * @return {void}
		 */
		function replaceAllBlocks( template ) {
			dispatch( blockEditorStore ).replaceBlocks(
				select( blockEditorStore )
					.getBlocks()
					.map( ( block ) => block.clientId ),
				parse( template )
			);
		}

		/**
		 * Revert the current form to the default template for the form's location
		 *
		 * This function will temporarily store the current layout (prior to reversion) so that it can
		 * be restored immediately following reversion.
		 *
		 * @since 1.12.0
		 * @since 2.0.0 Use default template from location definition & reset llms/user-info-fields data before replacing blocks.
		 *
		 * @return {void}
		 */
		function revertToDefault() {
			const id = 'llms-form-restore-default',
				// Save temp content for reverting in the notice action.
				tempContent = select( editorStore ).getEditedPostAttribute(
					'content'
				),
				{ createSuccessNotice, removeNotice } = dispatch(
					noticesStore
				),
				{ resetFields } = dispatch( fieldsStore );

			// Reset field data.
			resetFields();

			// Replace blocks.
			replaceAllBlocks( currentLoc.template );

			// Pop a success notice and allow undoing.
			createSuccessNotice(
				__(
					'The form has been restored to the default template.',
					'lifterlms'
				),
				{
					id,
					actions: [
						{
							label: __( 'Undo', 'lifterlms' ),

							/**
							 * Restore the temporary backup and clear the notice.
							 *
							 * @since 1.12.0
							 * @since 2.0.0 Reset llms/user-info-fields data before replacing blocks.
							 *
							 * @return {void}
							 */
							onClick: () => {
								resetFields();
								replaceAllBlocks( tempContent );
								removeNotice( id );
							},
						},
					],
				}
			);
		}

		return (
			<Fragment>
				<PluginDocumentSettingPanel
					className="llms-forms-doc-settings"
					name="llms-forms-doc-settings"
					title={ __( 'Form Settings', 'lifterlms' ) }
					opened={ true }
				>
					<LLMSFormDocSettings.Slot>
						{ ( fills ) => (
							<Fragment>
								<PanelRow>
									<strong>
										{ __( 'Location', 'lifterlms' ) }
									</strong>
									{ ! link && (
										<strong>{ currentLoc.name }</strong>
									) }
									{ link && (
										<ExternalLink href={ link }>
											{ currentLoc.name }
										</ExternalLink>
									) }
								</PanelRow>
								<p style={ { marginTop: '5px' } }>
									<em>{ currentLoc.description }</em>
								</p>
								{ fills }
								<br />
								<ToggleControl
									label={ __(
										'Display Form Title',
										'lifterlms'
									) }
									checked={ 'yes' === showTitle }
									help={
										'yes' === showTitle
											? __(
													'Displaying form title.',
													'lifterlms'
											  )
											: __(
													'Not displaying form title.',
													'lifterlms'
											  )
									}
									onChange={ ( val ) =>
										setFormMetas( {
											_llms_form_show_title: val
												? 'yes'
												: 'no',
										} )
									}
								/>
								{ 'checkout' === location &&
									'yes' === showTitle && (
										<TextControl
											label={ __(
												'Free Access Plan Form Title',
												'lifterlms'
											) }
											value={ freeApTitle }
											onChange={ ( val ) =>
												setFormMetas( {
													_llms_form_title_free_access_plans: val,
												} )
											}
											help={ __(
												'The form title to be shown for free access plans.',
												'lifterlms'
											) }
										/>
									) }
								<br />
								<PanelRow>
									<Button
										isDestructive
										onClick={ revertToDefault }
									>
										{ __(
											'Revert to Default',
											'lifterlms'
										) }
									</Button>
								</PanelRow>
								<p style={ { marginTop: '5px' } }>
									<em>
										{ __(
											'Replace the existing content of the form with the original default content.',
											'lifterlms'
										) }
									</em>
								</p>
							</Fragment>
						) }
					</LLMSFormDocSettings.Slot>
				</PluginDocumentSettingPanel>
			</Fragment>
		);
	};
}

/**
 * Retrieve custom meta information when retrieving posts.
 *
 * @since 1.6.0
 * @since 1.7.0 Retrieve form link attribute.
 * @since 1.7.2 Only modify select when working with an `llms_form` post type.
 * @since 1.12.0 Load the default template meta field.
 * @since 2.0.0 Don't load default template from metadata.
 * @since 2.3.2 Retrieve form title for free access plans meta field.
 */
const applyWithSelect = withSelect( ( select ) => {
	const {
		getCurrentPost,
		getCurrentPostType,
		getEditedPostAttribute,
	} = select( editorStore );

	if ( 'llms_form' !== getCurrentPostType() ) {
		return {};
	}

	const meta = getEditedPostAttribute( 'meta' );

	return {
		link: getCurrentPost().link,
		location: meta._llms_form_location,
		showTitle: meta._llms_form_show_title,
		freeApTitle: meta._llms_form_title_free_access_plans,
	};
} );

/**
 * Save custom meta information when saving posts.
 *
 * @since 1.6.0
 */
const applyWithDispatch = withDispatch( ( dispatch ) => {
	const { editPost } = dispatch( 'core/editor' );
	return {
		setFormMetas( metas ) {
			editPost( { meta: metas } );
		},
	};
} );

// Export.
export default compose( [ applyWithSelect, applyWithDispatch ] )(
	FormDocumentSettings
);
