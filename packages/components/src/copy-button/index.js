import { __ } from '@wordpress/i18n';
import { ClipboardButton, Button, Tooltip } from '@wordpress/components';
import { useCopyToClipboard } from '@wordpress/compose';

/**
 * A "click to copy" button.
 *
 * Uses the `useCopyToClipboard()` hook with a <Button> on WP 5.8 & later, otherwise falls back
 * to the deprecated <ClipboardButton>.
 *
 * @since [version]
 *
 * @param {Object}   args             Component arguments.
 * @param {string}   args.buttonText  Text to to display within the button.
 * @param {string}   args.copyText    Text to copy to the clipboard.
 * @param {string}   args.tooltipText Text to use in the tooltip wrapper around the button.
 * @param {Function} args.onCopy      Copy success callback function.
 * @param {...*}     args.buttonProps Remaining properties passed to the underlying <Button> component.
 * @return {Object} The copy button fragment.
 */
export default function ( {
	buttonText,
	copyText,
	onCopy,
	tooltipText = null,
	...buttonProps
} ) {
	tooltipText = tooltipText || __( 'Click to copy.', 'lifterlms' );

	const canUseHook = 'undefined' !== typeof useCopyToClipboard;

	// WP 5.8+.
	const HookButton = () => {
		const ref = useCopyToClipboard( copyText, onCopy );
		return (
			<Button { ...buttonProps } ref={ ref }>
				{ buttonText }
			</Button>
		);
	};

	// WP < 5.8.
	const BackwardsButton = () => {
		return (
			<ClipboardButton
				{ ...buttonProps }
				text={ copyText }
				onCopy={ onCopy }
			>
				{ buttonText }
			</ClipboardButton>
		);
	};

	return (
		<Tooltip text={ tooltipText }>
			{ canUseHook && <HookButton /> }
			{ ! canUseHook && <BackwardsButton /> }
		</Tooltip>
	);
}
