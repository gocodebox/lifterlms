<?php
/**
 * LLMS_Capabilities class file
 *
 * @package LifterLMS/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LifterLMS user capabilities enum.
 *
 * @since [version]
 */
class LLMS_Capabilities extends LLMS_Abstract_Enum {

	/**
	 * Create, read, update, delete, and award earned engagements.
	 */
	public const MANAGE_EARNED_ENGAGEMENT = 'manage_earned_engagement';

	/**
	 * Read and write access to all LifterLMS settings.
	 */
	public const MANAGE_LIFTERLMS = 'manage_lifterlms';

	/**
	 * Utility capability which denotes the user is an instructor.
	 */
	public const INSTRUCTOR = 'lifterlms_instructor';

	/**
	 * View reporting for student accounts which have an instructor relationship
	 * with the user.
	 */
	public const VIEW_REPORTS = 'view_lifterlms_reports';

	/**
	 * View reporting for student accounts which do not have an instructor
	 * relationship with the user.
	 */
	public const VIEW_OTHERS_REPORTS = 'view_others_lifterlms_reports';

	/**
	 * Enroll user accounts.
	 */
	public const ENROLL = 'enroll';

	/**
	 * Unenroll user accounts.
	 */
	public const UNENROLL = 'unenroll';

	/**
	 * Create new student accounts.
	 */
	public const CREATE_STUDENTS = 'create_students';

	/**
	 * View grades for student accounts which have an instructor relationship
	 * with the user.
	 */
	public const VIEW_GRADES = 'view_grades';

	/**
	 * View the account information of student accounts which have an instructor
	 * relationship with the user.
	 */
	public const VIEW_STUDENTS = 'view_students';

	/**
	 * View the account information of student accounts which do not have an
	 * instructor relationship with the user.
	 */
	public const VIEW_OTHERS_STUDENTS = 'view_others_students';

	/**
	 * Edit the account information of student accounts which have an instructor
	 * relationship with the user.
	 */
	public const EDIT_STUDENTS = 'edit_students';

	/**
	 * Edit the account information of student accounts which do not have an
	 * instructor relationship with the user.
	 */
	public const EDIT_OTHERS_STUDENTS = 'edit_others_students';

	/**
	 * Delete student accounts which have an instructor relationship with the
	 * user.
	 */
	public const DELETE_STUDENTS = 'delete_students';

	/**
	 * Delete student accounts which do not have an instructor relationship with
	 * the user.
	 */
	public const DELETE_OTHERS_STUDENTS = 'delete_others_students';

}
