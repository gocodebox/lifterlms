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
	extends: [ 'plugin:@wordpress/eslint-plugin/recommended-with-formatting' ],
	rules: {
		'jsdoc/tag-lines': [ 0 ]
	},
	settings: {
		// Ensure that WordPress core dependencies don't throw errors when importing them.
		'import/internal-regex': '^@wordpress/',
		'import/core-modules': [
			// @todo: This list needs to be expanded to include other WP Core included modules.
			'jquery',
		]
	}
};

module.exports = eslintConfig;
