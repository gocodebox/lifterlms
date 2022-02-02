<?php
/**
 * LLMS_Trait_Case definition
 *
 * @package LifterLMS/Traits
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Methods to help with changing the case of strings.
 *
 * Classes that use this trait MUST implement {@see LLMS_Interface_Case}
 * because traits can not define constants.
 *
 * @since [version]
 */
trait LLMS_Trait_Case {

	/**
	 * Changes the case of a string.
	 *
	 * @since [version]
	 *
	 * @param string $string The string to change the case of.
	 * @param string $case   One of the CASE_ constants from {@see LLMS_Interface_Case}.
	 * @return string
	 */
	protected function change_case( $string, $case ) {

		switch ( $case ) {
			case self::CASE_LOWER:
				$string = strtolower( $string );
				break;
			case self::CASE_UPPER:
				$string = strtoupper( $string );
				break;
			case self::CASE_UPPER_FIRST:
				$string = ucfirst( $string );
				break;
			case self::CASE_UPPER_WORDS:
				$string = ucwords( $string );
				break;
			case self::CASE_NO_CHANGE:
			default:
		}

		return $string;
	}
}
