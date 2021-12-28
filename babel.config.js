/**
 * Babel config
 *
 * @package LifterLMS/Dev/Scripts
 *
 * @since Unknown
 * @version [version]
 */

const
	presets = [ '@wordpress/default' ],
	plugins = [ '@babel/plugin-proposal-class-properties' ];

module.exports = { plugins, presets };
