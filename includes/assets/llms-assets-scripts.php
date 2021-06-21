<?php
/**
 * The main LifterLMS Script Asset Definition list
 *
 * This file returns an array of script asset definition arrays.
 *
 * The array key of each definition is the asset's "handle" which
 * is used by both LifterLMS and WordPress to identify the asset
 * during registration and enqueue.
 *
 * The remaining items in each definition are optional and will be
 * automatically populated with default values. See `LLMS_Assets::get_defaults()`
 * for information on the default values of the asset.
 *
 * See `LLMS_Assets::get()` for full documentation on the properties
 * of an asset definition.
 *
 * @package LifterLMS/Assets
 *
 * @since 4.4.0
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Stylesheets assets list
 *
 * @since 4.4.0
 * @since 4.8.0 Added llms-admin-setup.
 * @since 5.0.0 Added llms-select2.
 */
return array(

	// Core.
	'llms'                    => array(
		'dependencies' => array( 'jquery' ),
	),
	'llms-form-checkout'      => array(
		'dependencies' => array( 'jquery' ),
	),
	'llms-notifications'      => array(
		'dependencies' => array( 'jquery' ),
	),
	'llms-quiz'               => array(
		'dependencies' => array( 'jquery', 'llms', 'wp-mediaelement' ),
	),

	// Admin.
	'llms-admin-setup'        => array(
		'dependencies' => array( 'jquery' ),
	),
	'llms-admin-forms'        => array(
		'dependencies' => array( 'wp-i18n' ),
	),

	// Vendor.
	'llms-iziModal'           => array(
		'file_name' => 'iziModal',
		'path'      => 'assets/vendor/izimodal',
		'version'   => '1.5.1',
	),
	'llms-jquery-matchheight' => array(
		'file_name'    => 'jquery.matchHeight',
		'path'         => 'assets/js/vendor/',
		'suffix'       => '',
		'version'      => '0.7.0',
		'dependencies' => array( 'jquery' ),
	),
	'llms-select2'            => array(
		'file_name'    => 'select2',
		'path'         => 'assets/vendor/select2/js',
		'version'      => '4.0.3',
		'dependencies' => array( 'jquery' ),
	),
	'webui-popover'           => array(
		'file_name'    => 'jquery.webui-popover',
		'path'         => 'assets/vendor/webui-popover',
		'version'      => '1.2.15',
		'dependencies' => array( 'jquery' ),
	),

);
