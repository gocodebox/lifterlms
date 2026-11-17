/**
 * Webpack config
 *
 * @package LifterLMS/Scripts/Dev
 *
 * @since 5.5.0
 * @version 6.10.0
 */

const { resolve } = require( 'path' ),
	blocksConfig = require( '@lifterlms/scripts/config/blocks-webpack.config' ),
	generate = require( '@lifterlms/scripts/config/webpack.config' ),
	config = generate( {
		js: [
			'admin-addons',
			'admin-addon-promo',
			'admin-award-certificate',
			'admin-certificate-editor',
			'admin-media-protection-block-protect',
			'admin-elementor-editor',
			'lesson-timer',
			'quill-wordcount',

			// Module packages.
			'components',
			'icons',
			'spinner',
			'utils',
		],
		css: [
			'admin-addons',
			'lesson-timer',
		],
	} );

// config.entry.fontawesome = resolve( './src/scss/fontawesome.scss' );

module.exports = [
	blocksConfig,
	config
];
