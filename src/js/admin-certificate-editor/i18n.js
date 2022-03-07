import { addFilter } from '@wordpress/hooks';
import { __ } from '@wordpress/i18n';
import { subscribe, select } from '@wordpress/data';
import { store as editorStore } from '@wordpress/editor';

/**
 * Wait for the current post type to load and unsubscribe as soon as we have a post type.
 *
 * This subscription "translates" the "Move to trash" button to "Delete permanently".
 *
 * @since 6.0.0
 */
const unsubscribe = subscribe( () => {
	const { getCurrentPostType } = select( editorStore ),
		postType = getCurrentPostType();

	if ( null !== postType ) {
		doUnsubscribe( 'llms_my_certificate' === postType );
	}
} );

/**
 * Performs unsubscribe on the subscription and optionally applies the desired translation.
 *
 * @since 6.0.0
 *
 * @param {boolean} withFilter Whether or not the addFilter call should be applied.
 * @return {void}
 */
function doUnsubscribe( withFilter ) {
	unsubscribe();

	if ( ! withFilter ) {
		return;
	}

	addFilter( 'i18n.gettext_default', 'llms/certificates', function( text ) {
		if ( 'Move to trash' === text ) {
			return __( 'Delete permanently', 'lifterlms' );
		}

		return text;
	} );
}

