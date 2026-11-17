/**
 * WP Data Subscription for the llms/pricing-table block
 *
 * @since 1.3.6
 * @version 1.3.8
 */

// WP Deps.
import { dispatch, select, subscribe } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

// Import jQuery.
import $ from 'jquery';

/**
 * ID of the Post's Last Revision
 *
 * @type {number}
 */
let lastRevision = null;

/**
 * Watch core data to manually save Access Plan data when the post is updated or published.
 *
 * @since 1.3.6
 */
subscribe( () => {
	const {
		getCurrentPostLastRevisionId,
		isCurrentPostPublished,
		isSavingPost,
		isPublishingPost,
	} = select( 'core/editor' );

	// Don't do anything if the post is not published.
	if ( ! isCurrentPostPublished() ) {
		return;
	}

	// Access Plan save button in the metabox.
	const $btn = $( '#llms-save-access-plans' );

	// Don't do anything if there's no button on the page.
	if ( ! $btn.length ) {
		return;
	}

	/**
	 * Determine if the button is disabled (already saving, for example).
	 *
	 * @since 1.3.6
	 *
	 * @return {boolean} Returns `true` if the button is currently disabled.
	 */
	const isBtnDisabled = function () {
		return 'disabled' === $btn.attr( 'disabled' );
	};

	/**
	 * Determine if the post revision ID has changed, if it has we need to update our plans.
	 *
	 * @since 1.3.6
	 *
	 * @return {boolean} Returns `true` if the revision ID has changed.
	 */
	const hasRevisionChanged = function () {
		return lastRevision !== getCurrentPostLastRevisionId();
	};

	// If the revision has changed, the button is not disabled, and the post is saving or publishing.
	if (
		hasRevisionChanged() &&
		! isBtnDisabled() &&
		( isSavingPost() || isPublishingPost() )
	) {
		lastRevision = getCurrentPostLastRevisionId();
		$btn.trigger( 'click' );
	}
} );

// Throw an error mesage when validation issues are encountered.
$( document ).on( 'llms-access-plan-validation-errors', function () {
	dispatch( 'core/notices' ).createErrorNotice(
		__(
			'Validation errors were encountered while attempting to save your access plans.',
			'lifterlms'
		),
		{
			id: 'llms-access-plan-error-notice',
		}
	);
} );
