/**
 * DOM Ready Events for LifterLMS Forms (llms_forms) Post Type
 *
 * @since 1.7.0
 * @version 1.12.0
 */

/* eslint camelcase: [ "error", { allow: [ "_llms_form_*" ] } ] */

// WP Deps.
import { createBlock } from '@wordpress/blocks';
import { dispatch, subscribe, select } from '@wordpress/data';
import { store as blockEditorStore } from '@wordpress/block-editor';
import { store as editorStore } from '@wordpress/editor';
import { addFilter } from '@wordpress/hooks';
import { __ } from '@wordpress/i18n';

// External deps.
import { debounce, some } from 'lodash';

// Internal Deps.
import { getBlocksFlat } from '../../util/';

/**
 * Hides WP Core UI elements that we cannot disable with filters or proper APIs
 *
 * + Disables the "Draft" button / UI, we never want our forms to be "drafts".
 * + Disables the "Status & Visibility" sidebar, we don't want to make our forms private, password
 *   protected, or published in the future.
 *
 * @since 1.12.0
 * @since 2.0.0 Only prevent drafts on core forms.
 *
 * @return {void}
 */
function hideCoreUI() {
	const { _llms_form_is_core } = select( editorStore ).getEditedPostAttribute(
		'meta'
	);

	// Hide Status & Visibility.
	const selectors = [
		'.edit-post-layout .components-panel__body.edit-post-post-status',
	];

	// Core forms cannot be drafted.
	if ( 'yes' === _llms_form_is_core ) {
		selectors.push(
			'.edit-post-layout button.editor-post-switch-to-draft'
		);
	}

	subscribe( () => {
		setTimeout( () => {
			const els = document.querySelectorAll( selectors.join( ',' ) );
			els.forEach( ( el ) => {
				el.style.display = 'none';
			} );
		}, 1 );
	} );
}

/**
 * Disable visibility settings when viewing registration and account forms.
 *
 * @since 1.12.0
 *
 * @return {void}
 */
function maybeDisableVisibility() {
	const { _llms_form_location } = select(
		editorStore
	).getEditedPostAttribute( 'meta' );

	if ( [ 'registration', 'account' ].includes( _llms_form_location ) ) {
		addFilter(
			'llms_block_supports_visibility',
			'llms/form-block-visibility',
			() => {
				return false;
			}
		);
	}
}

/**
 * Modify the list of available block-level visibility options
 *
 * Certain form fields (user email, password, and login) are required to
 * create a WordPress account. Therefore we cannot allow end users to restrict
 * visibility of these fields otherwise we could end up with forms that cannot
 * function.
 *
 * For example, if a user email field were to be created with a visibility setting
 * of "logged_in" it would be impossible for the user to create a new account.
 *
 * However, displaying an email field only to logged out users is acceptable because
 * once the account is created (by a logged out user) it isn't required on future enrollments.
 *
 * This function reduces the list to only allow visibility settings of "all" and "logged out"
 * to be used.
 *
 * @since 2.0.0
 *
 * @return {void}
 */
function modifyVisibilityForBlocks() {
	const visibilityOptsMap = {
		'llms/form-field-user-email': [ 'all', 'logged_out' ],
		'llms/form-field-user-password': [ 'all', 'logged_out' ],
		'llms/form-field-user-login': [ 'logged_out' ],
	};

	const blocksList = Object.keys( visibilityOptsMap );

	/**
	 * Retrieve a list of options for a given block
	 *
	 * @since 2.0.0
	 *
	 * @param {Object}   options             A WP_Block object.
	 * @param {string}   options.name        The block's name.
	 * @param {Object[]} options.innerBlocks The block's inner blocks list.
	 * @return {string[]} Array of block options.
	 */
	const getOptsForBlock = ( { name, innerBlocks } ) => {
		let mapKey = name;

		if ( 'llms/form-field-confirm-group' === name ) {
			const inner = innerBlocks.find( ( innerBlock ) =>
				blocksList.includes( innerBlock.name )
			);
			mapKey = inner ? inner.name : mapKey;
		}

		return visibilityOptsMap[ mapKey ] || [];
	};

	/**
	 * Determines whether or not a given block should have it's visibility options modified
	 *
	 * @since 2.0.0
	 *
	 * @param {Object}   options             A WP_Block object.
	 * @param {string}   options.name        The block's name.
	 * @param {Object[]} options.innerBlocks The block's inner blocks list.
	 * @return {boolean} Whether or not the settings list should be modified.
	 */
	const shouldModify = ( { name, innerBlocks } ) => {
		if ( 'llms/form-field-confirm-group' === name ) {
			return some( innerBlocks, ( innerBlock ) =>
				blocksList.includes( innerBlock.name )
			);
		}

		return blocksList.includes( name );
	};

	addFilter(
		'llms_block_visibility_settings_options',
		'llms/form-block-visibility-options',
		( opts ) => {
			const { getSelectedBlock } = select( blockEditorStore ),
				selectedBlock = getSelectedBlock();

			if ( selectedBlock && shouldModify( selectedBlock ) ) {
				return opts.filter( ( { value } ) =>
					getOptsForBlock( selectedBlock ).includes( value )
				);
			}

			return opts;
		}
	);
}

/**
 * All forms must have *at least* an email field.
 *
 * Watches editor data and if the field is removed throw an error notice.
 *
 * @since 1.7.0
 * @since 1.12.0 Wait for the editor to be fully initialized before dispatching a notice.
 *
 * @return {void}
 */
function ensureEmailFieldExists() {
	const emailBlockName = 'llms/form-field-user-email',
		noticeId = 'llms-forms-no-email-error-notice',
		{ getNotices } = select( 'core/notices' ),
		{ createErrorNotice, removeNotice } = dispatch( 'core/notices' );

	subscribe(
		debounce( () => {
			// Skip this check if we're editing a pattern.
			if ( 'wp_block' === select( 'core/editor' ).getCurrentPostType() ) {
				return;
			}

			const post = select( 'core/editor' ).getCurrentPost(),
				blocks = getBlocksFlat().map( ( block ) => block.name );

			/**
			 * The block editor appears to call domReady() prior to the block editor data being set
			 * which results in getBlocksFlat() to respond with an empty array even though there are blocks
			 * in the post content.
			 *
			 * To combat this we can determine that the editor "is ready" when there is either at least a single
			 * block in the blocks array or the post content *does not* include a block comment string.
			 *
			 * If the post content doesn't include any block comment string then the empty array is not an
			 * incorrect result.
			 */
			if ( ! post.content.includes( '<!-- wp:' ) || ! blocks.length ) {
				return;
			}

			const hasNotice = getNotices()
					.map( ( notice ) => notice.id )
					.includes( noticeId ),
				updateBtn = document.querySelector(
					'button.editor-post-publish-button'
				);

			// Check if a user email field exists & the notice hasn't been displayed yet.
			if ( ! blocks.includes( emailBlockName ) && ! hasNotice ) {
				createErrorNotice(
					__( 'User Email is a required field.', 'lifterlms' ),
					{
						id: noticeId,
						isDismissible: false,
						actions: [
							{
								label: __(
									'Restore user email field?',
									'lifterlms'
								),

								/**
								 * Restore the field, remove the notice, and re-enable the post update button.
								 *
								 * Inserts the user email field as the first block in the form.
								 *
								 * @since 1.12.0
								 *
								 * @return {void}
								 */
								onClick: () => {
									// Add the field.
									const blockEd =
										dispatch( 'core/block-editor' ) ||
										dispatch( 'core/editor' );
									blockEd.insertBlock(
										createBlock( emailBlockName ),
										0
									);
								},
							},
						],
					}
				);

				// Disable the "Update" button so the form cannot be saved in a messed up state.
				updateBtn.disabled = true;
			} else if ( blocks.includes( emailBlockName ) && hasNotice ) {
				// Remove the notice.
				removeNotice( noticeId );

				// Re-enable updating.
				updateBtn.disabled = false;
			}
		}, 500 )
	);
}

/**
 * Default Function, runs all methods and events.
 *
 * @since 1.7.0
 * @since 1.7.1 Disable block visibility on registration & account forms to prevent a potentially confusing form creation experience.
 * @since 1.7.3 Move forms ready event to block registration file to ensure blocks are registered during editor init.
 * @since 1.12.0 Move UI disabling functionality into it's own function.
 *
 * @return {void}
 */
export default () => {
	maybeDisableVisibility();
	modifyVisibilityForBlocks();
	hideCoreUI();
	ensureEmailFieldExists();
};
