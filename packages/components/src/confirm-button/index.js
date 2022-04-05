import { Button } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * A Button component that requires user confirmation before performing the onClick callback function.
 *
 * When clicked the button's state will change and will display confirmation language. When clicked again
 * the onClick callback will be executed.
 *
 * Optionally, after the initial click an "Cancel" button can be displayed adjacent to the initial button. When clicked
 * the primary button will return to its initial state.
 *
 * @since [version]
 *
 * @param {Object}   props                      Component properties object.
 * @param {string}   props.confirmText          Button text displayed to confirm the action.
 * @param {string}   props.processingText       Button text displayed while performing the onClick callback.
 * @param {string}   props.cancelText           Button text displayed in the cancel button.
 * @param {boolean}  props.allowCancel          Whether or not cancellation is allowed in the confirmation state.
 * @param {boolean}  props.confirmIsDescructive If `true`, the <Button> component will be passed `isDestructive` in the confirmation state.
 * @param {boolean}  props.processingIsBusy     If `true`, the <Button> component will be passed `isBusy` in the processing state.
 * @param {Function} props.onClick              The onClick callback function executed when the button is clicked in the confirmation state.
 * @param {Object[]} props.children             Child components to render within the button. Only displayed in the default state.
 * @param {Object}   props.btnProps             Properties passed to the <Button> component.
 * @return {WPElement} The component.
 */
export default function ( {
	confirmText = __( 'Are you sure?', 'lifterlms' ),
	processingText = __( 'Processing…', 'lifterlms' ),
	cancelText = __( 'Cancel', 'lifterlms' ),
	allowCancel = true,
	confirmIsDescructive = true,
	processingIsBusy = true,
	onClick = () => {},
	children,
	...btnProps
} ) {
	// 0 = unclicked; 1 = unconfirmed; 2 confirmed & processing.
	const [ status, setStatus ] = useState( 0 ),
		nextStatus = status + 1 > 2 ? 0 : status + 1,
		done = () => setStatus( 0 );

	if ( 1 === status && confirmIsDescructive ) {
		btnProps.isDestructive = true;
	} else if ( 2 === status && processingIsBusy ) {
		btnProps.isBusy = true;
	}

	return (
		<>
			<Button
				{ ...btnProps }
				onClick={ () => {
					if ( 1 === status ) {
						onClick( done );
					}
					setStatus( nextStatus );
				} }
			>
				{ ! status && children }
				{ 1 === status && confirmText }
				{ 2 === status && processingText }
			</Button>
			{ 1 === status && allowCancel && (
				<Button
					isSecondary
					style={ { marginLeft: '5px' } }
					onClick={ () => setStatus( 0 ) }
				>
					{ cancelText }
				</Button>
			) }
		</>
	);
}
