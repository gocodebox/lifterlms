/**
 * Default eslint config for LifterLMS projects
 *
 * @package
 *
 * @since Unknown
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
		],
		'import/resolver': __dirname + '/import-resolver.js',
	},
	/**
	 * Add overrides for test files.
	 *
	 * @see {@link https://github.com/WordPress/gutenberg/blob/1749166b9f5d7cb536d82e82a94ccffae53300eb/packages/eslint-plugin/configs/recommended-with-formatting.js#L53-L63}
	 */
	overrides: [
		{
			// Unit test files and their helpers only.
			files: [ '**/@(test|__tests__)/**/*.js', '**/?(*.)test.js' ],
			extends: [ 'plugin:@wordpress/eslint-plugin/test-unit' ],
		},
		{
			// End-to-end test files and their helpers only.
			files: [ 'tests/e2e/**/?(*.)test.js', '**/specs/**/*.js', '**/?(*.)spec.js' ],
			extends: [ 'plugin:@wordpress/eslint-plugin/test-e2e' ],
		},
	],
};

module.exports = eslintConfig;
