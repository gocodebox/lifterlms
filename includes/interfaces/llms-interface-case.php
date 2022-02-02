<?php
/**
 * LLMS_Interface_Case definition
 *
 * @package LifterLMS/Interfaces
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Constants to help with changing the case of strings.
 *
 * @since [version]
 */
interface LLMS_Interface_Case {

	/**
	 * Change all characters in the string to lowercase.
	 *
	 * @var int
	 */
	const CASE_LOWER = 0;

	/**
	 * Do not change the case of the string.
	 *
	 * @var int
	 */
	const CASE_NO_CHANGE = 1;

	/**
	 * Change all characters in the string to uppercase.
	 *
	 * @var int
	 */
	const CASE_UPPER = 2;

	/**
	 * Change the first character in the string to uppercase.
	 *
	 * @var int
	 */
	const CASE_UPPER_FIRST = 3;

	/**
	 * Change the first character of each word in the string to uppercase.
	 *
	 * @var int
	 */
	const CASE_UPPER_WORDS = 4;
}
