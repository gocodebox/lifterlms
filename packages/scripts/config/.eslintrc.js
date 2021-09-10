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
	extends: [ 'plugin:@wordpress/eslint-plugin/recommended' ],
	rules: {
		"jsdoc/tag-lines": [ 0 ]
	}
};

module.exports = eslintConfig;
