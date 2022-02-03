<?php
/**
 * LLMS_Case definition
 *
 * @package LifterLMS/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Methods to help with changing the case of strings.
 *
 * @since [version]
 */
class LLMS_Case {

	/**
	 * Change all characters in the string to lowercase.
	 *
	 * @var int
	 */
	const LOWER = 0;

	/**
	 * Do not change the case of the string.
	 *
	 * @var int
	 */
	const NO_CHANGE = 1;

	/**
	 * Change all characters in the string to uppercase.
	 *
	 * @var int
	 */
	const UPPER = 2;

	/**
	 * Change the first character in the string to uppercase.
	 *
	 * @var int
	 */
	const UPPER_FIRST = 3;

	/**
	 * Change the first character of each word in the string to uppercase.
	 *
	 * @var int
	 */
	const UPPER_WORDS = 4;

	/**
	 * Changes the case of a string.
	 *
	 * @since [version]
	 *
	 * @param string $string The string to change the case of.
	 * @param string $case   One of the CASE_ constants from {@see LLMS_Case}.
	 * @return string
	 */
	public static function change( $string, $case ) {

		if ( function_exists( 'mb_convert_case' ) ) {
			return self::change_with_multibyte( $string, $case );
		} else {
			return self::change_without_multibyte( $string, $case );
		}
	}

	/**
	 * Changes the case of a string using multibyte string functions.
	 *
	 * @since [version]
	 *
	 * @param string $string The string to change the case of.
	 * @param string $case   One of the CASE_ constants from {@see LLMS_Case}.
	 * @return string
	 */
	private static function change_with_multibyte( $string, $case ) {

		switch ( $case ) {
			case self::LOWER:
				return mb_convert_case( $string, MB_CASE_LOWER );
			case self::UPPER:
				return mb_convert_case( $string, MB_CASE_UPPER );
			case self::UPPER_FIRST:
				if ( function_exists( 'mb_substr' ) ) {
					return mb_convert_case( mb_substr( $string, 0, 1 ), MB_CASE_UPPER ) . mb_substr( $string, 1 );
				} else {
					return ucfirst( $string );
				}
			case self::UPPER_WORDS:
				return mb_convert_case( $string, MB_CASE_TITLE );
			case self::NO_CHANGE:
			default:
				return $string;
		}
	}

	/**
	 * Changes the case of a string using non-multibyte PHP functions.
	 *
	 * @since [version]
	 *
	 * @param string $string The string to change the case of.
	 * @param string $case   One of the CASE_ constants from {@see LLMS_Case}.
	 * @return string
	 */
	private static function change_without_multibyte( $string, $case ) {

		switch ( $case ) {
			case self::LOWER:
				return strtolower( $string );
			case self::UPPER:
				return strtoupper( $string );
			case self::UPPER_FIRST:
				return ucfirst( $string );
			case self::UPPER_WORDS:
				return ucwords( $string );
			case self::NO_CHANGE:
			default:
				return $string;
		}
	}
}
