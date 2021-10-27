/**
 * Set the value of a select2 dropdown field
 *
 * This does not actually test whether or not select2 is working,
 * instead it selects the value on the select element and artificially
 * triggers a change event.
 *
 * @since 2.2.0
 *
 * @param {string}  selector Query selector for the select element.
 * @param {string}  value    Option value to select.
 * @param {boolean} create   If `true`, the value will be added to the select element before being selected.
 *                           This is a useful option for AJAX powered select2 elements that will be empty until interacted with.
 * @return {void}
 */
export async function setSelect2Option( selector, value, create = true ) {
	await page.$eval(
		selector,
		( el, _value, _create ) => {
			if ( _create ) {
				jQuery( el ).append(
					'<option value="' + _value + '">' + _value + '</option>'
				);
			}
			el.value = _value.toString();
			el.dispatchEvent( new Event( 'change' ) );
		},
		value,
		create
	);
}
