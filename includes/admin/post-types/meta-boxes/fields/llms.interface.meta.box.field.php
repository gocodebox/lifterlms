<?php
/**
 * Meta box Field interface
 *
 * @package LifterLMS/Admin/PostTypes/MetaBoxes/Fields/Interfaces
 *
 * @since unknown
 * @version unknown
 */

defined( 'ABSPATH' ) || exit;

/**
 * Meta_Box_Field_Interface interface
 *
 * @since Unknown
 */
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Legacy interface, backward compatibility.
interface Meta_Box_Field_Interface {

	public function output();
}
