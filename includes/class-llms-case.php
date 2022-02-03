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
			switch ( $case ) {
				case self::LOWER:
					$string = mb_convert_case( $string, MB_CASE_LOWER );
					break;
				case self::UPPER:
					$string = mb_convert_case( $string, MB_CASE_UPPER );
					break;
				case self::UPPER_FIRST:
					if ( function_exists( 'mb_substr' ) ) {
						$string = mb_convert_case( mb_substr( $string, 0, 1 ), MB_CASE_UPPER ) . mb_substr( $string, 1 );
					} else {
						$string = ucfirst( $string );
					}
					break;
				case self::UPPER_WORDS:
					$string = mb_convert_case( $string, MB_CASE_TITLE );
					break;
				case self::NO_CHANGE:
				default:
			}
		} else {
			switch ( $case ) {
				case self::LOWER:
					$string = strtolower( $string );
					break;
				case self::UPPER:
					$string = strtoupper( $string );
					break;
				case self::UPPER_FIRST:
					$string = ucfirst( $string );
					break;
				case self::UPPER_WORDS:
					$string = ucwords( $string );
					break;
				case self::NO_CHANGE:
				default:
			}
		}

		return $string;
	}
}
