/**
 * Set the value of a select2 dropdown field
 *
 * This does not actually test whether or not select2 is working,
 * instead it selects the value on the select element and artificially
 * triggers a change event.
 *
 * @since 2.2.0
 *
 * @param {String}  selector Query selector for the select element.
 * @param {String}  value    Option value to select.
 * @param {Boolean} create   If `true`, the value will be added to the select element before being selected.
 *                           This is a useful option for AJAX powered select2 elements that will be empty until interacted with.
 * @return {Void}
 */
export async function setSelect2Option( selector, value, create = true ) {

	await page.$eval( selector, ( el, value, create ) => {
		if ( create ) {
			jQuery( el ).append( '<option value="' + value + '">' + value + '</option>' );
		}
		el.value = value.toString();
		el.dispatchEvent( new Event( 'change' ) );
	}, value, create );

}
