<?php
/**
 * LLMS_Abstract_Enum abstract class file
 *
 * @package LifterLMS/Abstracts/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Base class for creating enum-like objects.
 *
 * @since [version]
 */
abstract class LLMS_Abstract_Enum {

	/**
	 * Retrieves all cases defined in the enum.
	 *
	 * @since [version]
	 *
	 * @return LLMS_Enum_Case[] An array of enum case objects.
	 */
	public static function cases() {

		$ref = new ReflectionClass( static::class );
		return $ref->getConstants( ReflectionClassConstant::IS_PUBLIC );

	}

}
