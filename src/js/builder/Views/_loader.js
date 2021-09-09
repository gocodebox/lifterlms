/**
 * Load view mixins
 *
 * @package LifterLMS/Scripts
 *
 * @since    3.17.1
 * @version  3.17.1
 */

define( [
		'Views/_Detachable',
		'Views/_Editable',
		'Views/_Receivable',
		'Views/_Shiftable',
		'Views/_Subview',
		'Views/_Trashable'
	],
	function(
		Detachable,
		Editable,
		Receivable,
		Shiftable,
		Subview,
		Trashable
	) {

		return {
			Detachable: Detachable,
			Editable: Editable,
			Receivable: Receivable,
			Shiftable: Shiftable,
			Subview: Subview,
			Trashable: Trashable,
		};

} );
