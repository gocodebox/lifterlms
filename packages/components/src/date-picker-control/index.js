import {
	BaseControl,
	Button,
	DateTimePicker,
	Dropdown,
} from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { format } from '@wordpress/date';

/**
 * A datepicker control.
 *
 * @since [version]
 *
 * @param {Object}      props               Component properties.
 * @param {string}      props.label         BaseControl label.
 * @param {string}      props.className     BaseControl CSS class names.
 * @param {string}      props.id            BaseControl id.
 * @param {Function}    props.onChange      Callback function when the date changes.
 * @param {Function}    props.isInvalidDate Callback function which receives a Date object and should return a boolean to signify whether or not the supplied date is valid.
 * @param {Date|string} props.currentDate   The currently selected date as a Date object or a datetime string that can be passed into the Date constructor.
 * @param {string}      props.dateFormat    The date display format.
 * @param {boolean}     props.is12Hour      Whether or not a 12 hour time clock should be used which allows selecting AM/PM.
 * @param {boolean}     props.allowEmpty    Whether or not to allow an empty value to be selected.
 * @param {string}      props.emptyValue    The text string displayed when an empty value is selected.
 * @return {BaseControl} The component.
 */
export default function ( {
	label,
	className,
	id,
	onChange,
	isInvalidDate,
	currentDate = new Date(),
	dateFormat = 'Y-m-d h:i a',
	is12Hour = true,
	allowEmpty = false,
	emptyValue = __( 'Select a date', 'lifterlms' ),
} ) {
	currentDate =
		currentDate && 'string' === typeof currentDate
			? new Date( currentDate )
			: currentDate;

	const [ , setPreviewedMonth ] = useState( currentDate ),
		dateFormatted = currentDate ? format( dateFormat, currentDate ) : null,
		buttonText = allowEmpty && ! currentDate ? emptyValue : dateFormatted;

	return (
		<BaseControl label={ label } className={ className } id={ id }>
			<br />
			<Dropdown
				contentClassName={ `${ className }__dialog` }
				renderToggle={ ( { onToggle, isOpen } ) => (
					<Button
						isSecondary
						className={ `${ className }__toggle` }
						onClick={ onToggle }
						aria-expanded={ isOpen }
					>
						{ buttonText }
					</Button>
				) }
				renderContent={ ( { onClose } ) => (
					<>
						<DateTimePicker
							is12Hour={ is12Hour }
							currentDate={ dateFormatted }
							onChange={ ( newDate ) => {
								onClose();
								onChange( newDate );
							} }
							onMonthPreviewed={ setPreviewedMonth }
							isInvalidDate={ isInvalidDate }
						/>
						<footer className={ `${ className }__footer` }>
							<Button isTertiary onClick={ onClose }>
								{ __( 'Close', 'lifterlms' ) }
							</Button>
						</footer>
					</>
				) }
			/>
		</BaseControl>
	);
}
