/**
 * Default eslint config for LifterLMS projects
 *
 * @package LifterLMS/Scripts/Config
 *
 * @since [version]
 * @version [version]
 */

const eslintConfig = {
	root: true,
	extends: [
		'plugin:@wordpress/eslint-plugin/recommended-with-formatting',
	],
	rules: {
		'jsdoc/tag-lines': [ 0 ],
		'jsdoc/require-jsdoc': 'error',
		'jsdoc/require-param-description': 'error',
		'jsdoc/require-returns': 'error',
	},
	settings: {
		'import/core-modules': [
			// @todo: This list needs to be expanded to include other WP Core included modules.
			'jquery',
		]
	}
};

module.exports = eslintConfig;


