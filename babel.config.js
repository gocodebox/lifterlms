/**
 * Babel config
 *
 * @package LifterLMS/Dev/Scripts
 *
 * @since Unknown
 * @version 6.0.0
 */

const
	presets = [ '@wordpress/default' ],
	plugins = [ '@babel/plugin-proposal-class-properties' ];

module.exports = { plugins, presets };
