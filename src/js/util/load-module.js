/**
 * Loads a module into the global `window.llms` object at the specified key.
 *
 * @since [version]
 *
 * @param {string}  key    Key where the module will be loaded.
 * @param {Module}  Module The module to load into the global object.
 * @param {boolean} extend When `true`, will load the module into the specified key if it already exists.
 *                         When `false` it will replace the existing key.
 * @return {void}
 */
export default function( key, Module, extend = false ) {
	window.llms = window.llms || {};

	let base = {};

	if ( extend && window.llms[ key ] ) {
		base = window.llms[ key ];
	}

	window.llms[ key ] = {
		...base,
		...Module,
	};
}
