<?php
/**
 * LLMS_Interface_User_Engagement_Type definition
 *
 * @package LifterLMS/Interfaces
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Constants to help get a user engagement type label name.
 *
 * @since [version]
 */
interface LLMS_Interface_User_Engagement_Type {

	/**
	 * An awarded user engagement.
	 *
	 * @var int
	 */
	const AWARDED = 0;

	/**
	 * The plural version of the user engagement name.
	 *
	 * @var int
	 */
	const PLURAL = 1;

	/**
	 * The singular version of the user engagement name.
	 *
	 * @var int
	 */
	const SINGULAR = 2;

	/**
	 * A user engagement template.
	 *
	 * @var int
	 */
	const TEMPLATE = 3;
}
