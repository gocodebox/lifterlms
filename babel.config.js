/**
 * Babel config
 *
 * @package LifterLMS/Dev/Scripts
 *
 * @since Unknown
 * @version Unknown
 */

const presets = [
	[
		'@babel/preset-env',
		{
			modules: 'amd',
		}
	]
];

module.exports = { presets };
