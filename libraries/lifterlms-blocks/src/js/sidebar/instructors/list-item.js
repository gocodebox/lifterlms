/**
 * Single instructor compontent
 *
 * @since 2.1.0
 * @version 2.1.0
 */

// WP Deps.
import {
	Button,
	Dashicon,
	TextControl,
	ToggleControl,
	Tooltip,
} from '@wordpress/components';
import { addQueryArgs } from '@wordpress/url';
import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';

import { SortableDragHandle } from '../../components';

/**
 * Instructor list item component
 *
 * @since 2.1.0
 *
 * @param {Object} props Component properties object.
 * @return {Object} Component fragment.
 */
export default function ( props ) {
	const {
			id,
			item: instructor,
			index,
			setNodeRef,
			listeners,
			manageState,
		} = props,
		{ visibility, name, label } = instructor,
		{
			updateItem: updateInstructor,
			deleteItem: removeInstructor,
		} = manageState,
		visible = 'visible' === visibility,
		isPrimary = 0 === index,
		[ isEditing, setIsEditing ] = useState( false );

	return (
		<>
			<div className="llms-instructor--header">
				<section>
					<strong>{ name }</strong>
					<small>(#{ id })</small>
				</section>
				<aside>
					{ isPrimary && (
						<Tooltip
							text={ __( 'Primary Instructor', 'lifterlms' ) }
						>
							<Dashicon icon="star-filled" />
						</Tooltip>
					) }
					<SortableDragHandle
						label={ __( 'Reorder instructor', 'lifterlms' ) }
						setNodeRef={ setNodeRef }
						listeners={ listeners }
					/>
					<Button
						isSmall
						showTooltip
						label={ __( 'Edit instructor', 'lifterlms' ) }
						icon={ isEditing ? 'arrow-up-alt2' : 'arrow-down-alt2' }
						onClick={ () => setIsEditing( ! isEditing ) }
					/>
				</aside>
			</div>
			{ isEditing && (
				<div className="llms-instructor--settings">
					<ToggleControl
						label={ __( 'Visibility', 'lifterlms' ) }
						help={
							visible
								? __(
										'Instructor is visible on frontend',
										'lifterlms'
								  )
								: __(
										'Instructor is hidden on frontend',
										'lifterlms'
								  )
						}
						checked={ visible }
						onChange={ ( val ) =>
							updateInstructor( id, {
								visibility: val ? 'visible' : 'hidden',
							} )
						}
					/>
					{ visible && (
						<TextControl
							label={ __( 'Label', 'lifterlms' ) }
							value={ label }
							onChange={ ( label ) =>
								updateInstructor( id, { label } )
							}
						/>
					) }
					<Button
						isSecondary
						iconPosition="right"
						href={ addQueryArgs( '/wp-admin/user-edit.php', {
							user_id: id,
						} ) }
						target="_blank"
						rel="noreferrer"
						style={ { marginRight: '5px' } }
					>
						{ __( 'Edit', 'lifterlms' ) }
						<Dashicon icon="external" />
					</Button>
					{ ! isPrimary && (
						<Button
							isDestructive
							onClick={ () => removeInstructor( instructor ) }
						>
							{ __( 'Remove', 'lifterlms' ) }
						</Button>
					) }
				</div>
			) }
		</>
	);
}
