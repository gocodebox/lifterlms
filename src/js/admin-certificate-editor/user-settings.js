// External deps.
import styled from '@emotion/styled';

// WP deps.
import { __ } from '@wordpress/i18n';
import { PanelRow, ExternalLink } from '@wordpress/components';
import { compose } from '@wordpress/compose';
import { withSelect, dispatch, useSelect } from '@wordpress/data';
import { PluginPostStatusInfo } from '@wordpress/edit-post';
import { store as editorStore } from '@wordpress/editor';
import { store as coreStore } from '@wordpress/core-data';
import { addQueryArgs, getQueryArg } from '@wordpress/url';

// Internal deps.
import { UserSearchControl } from '@lifterlms/components';

/**
 * Force the PanelRow to full-width.
 *
 * @since 6.0.0
 */
export const StyledPanelRow = styled( PanelRow )`
	width: 100%;
`;

/**
 * Outputs information about the selected user.
 *
 * This component is displayed for published certificates in place of the <UserSearchControl>.
 *
 * @since 6.0.0
 *
 * @param {Object} args        Component arguments.
 * @param {Object} args.userId WP_User ID of the selected user.
 * @return {WPElement|ExternalLink} Returns a generic element with loading text or an ExternalLink component linking to the
 *                                  the WP admin user edit screen for the selected user.
 */
function SelectedUser( { userId } ) {
	const name = useSelect(
		( select ) => {
			const { getEntityRecord } = select( coreStore ),
				user = getEntityRecord( 'root', 'user', userId );
			return user?.name;
		},
		[ userId ]
	);

	if ( ! name ) {
		return (
			<span>{ __( 'Loading…', 'lifterlms' ) }</span>
		);
	}

	return ( <ExternalLink href={ addQueryArgs(
		'admin.php',
		{
			page: 'llms-reporting',
			tab: 'students',
			stab: 'certificates',
			student_id: userId,
		}
	) }>{ name }</ExternalLink> );
}

/**
 * Render the certificate settings editor panel.
 *
 * @since 6.0.0
 *
 * @param {Object}  args        Component arguments.
 * @param {string}  args.type   Current post type.
 * @param {number}  args.userId WP User Id.
 * @param {boolean} args.isNew  Whether the current post has never been saved.
 * @return {?PluginPostStatusInfo} The component or `null` when rendered in an invalid context.
 */
function CertificateUserSettings( { type, userId, isNew } ) {
	// Only load for the right post type.
	if ( 'llms_my_certificate' !== type ) {
		return null;
	}

	// Retrieve the user ID from the URL.
	const forceId = getQueryArg( window.location.href, 'sid' );
	userId = forceId ? forceId : userId;

	return (

		<PluginPostStatusInfo>
			<StyledPanelRow>
				<span style={ { display: 'block', width: '45%' } }>{ __( 'Student', 'lifterlms' ) }</span>

				{ ( ! isNew || forceId ) && (
					<SelectedUser userId={ userId } />
				) }
				{ ( isNew && ! forceId ) && (
					<UserSearchControl
						selectedValue={ userId }
						onUpdate={ ( { id } ) => {
							const { editPost } = dispatch( editorStore );
							editPost(
								{
									author: id, // Update the post author.
								}
							);
						} }
					/>
				) }
			</StyledPanelRow>
		</PluginPostStatusInfo>

	);
}

const applyWithSelect = withSelect( ( select ) => {
	const { getEditedPostAttribute, isEditedPostNew } = select( editorStore );
	return {
		isNew: isEditedPostNew(),
		type: getEditedPostAttribute( 'type' ),
		userId: getEditedPostAttribute( 'author' ),
	};
} );

export default compose( [ applyWithSelect ] )(
	CertificateUserSettings
);
