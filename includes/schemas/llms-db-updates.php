<?php
/**
 * LifterLMS database migrations
 *
 * Lists database updates to be run during a plugin upgrade.
 *
 * Each array key should map to the LifterLMS version when the update should be run.
 *
 * Each array should contain a 'type' and 'updates' key
 *
 * The 'type' is either or either 'automatic' or 'manual'. Specifying whether
 * or not the user is prompted to run the upgrade (manual) or if it just runs automatically during
 * an upgrade (automatic).
 *
 * The 'updates' is an array of functions to be called to run the upgrade. They will be run in the order
 * they are listed. A function to upgrade to the version should always be included as the last update in the list.
 * This is important if multiple sets of updates need to be run during the same upgrade. For example a user upgrading from
 * versions less than 3.0.0 would have to run the entire list of upgrades for each version and upgrading the version at the end
 * of the set to the specific version will ensure that the next set of upgrades runs and then the next and so on.
 *
 * @package LifterLMS/Schemas
 *
 * @since 5.2.0
 * @version 7.8.0
 */

defined( 'ABSPATH' ) || exit;

return array(
	'3.0.0'  => array(
		'type'    => 'manual',
		'updates' => array(
			'llms_update_300_create_access_plans',
			'llms_update_300_del_deprecated_options',
			'llms_update_300_migrate_account_field_options',
			'llms_update_300_migrate_coupon_data',
			'llms_update_300_migrate_course_postmeta',
			'llms_update_300_migrate_lesson_postmeta',
			'llms_update_300_migrate_order_data',
			'llms_update_300_migrate_email_postmeta',
			'llms_update_300_update_orders',
			'llms_update_300_update_db_version',
		),
	),
	'3.0.3'  => array(
		'type'    => 'manual',
		'updates' => array(
			'llms_update_303_update_students_role',
			'llms_update_303_update_db_version',
		),
	),
	'3.4.3'  => array(
		'type'    => 'manual',
		'updates' => array(
			'llms_update_343_update_relationships',
			'llms_update_343_update_db_version',
		),
	),
	'3.6.0'  => array(
		'type'    => 'manual',
		'updates' => array(
			'llms_update_360_set_product_visibility',
			'llms_update_360_update_db_version',
		),
	),
	'3.8.0'  => array(
		'type'    => 'manual',
		'updates' => array(
			'llms_update_380_set_access_plan_visibility',
			'llms_update_380_update_db_version',
		),
	),
	'3.12.0' => array(
		'type'    => 'manual',
		'updates' => array(
			'llms_update_3120_update_order_end_dates',
			'llms_update_3120_update_integration_options',
			'llms_update_3120_update_db_version',
		),
	),
	'3.13.0' => array(
		'type'    => 'manual',
		'updates' => array(
			'llms_update_3130_create_default_instructors',
			'llms_update_3130_builder_notice',
			'llms_update_3130_update_db_version',
		),
	),
	'3.16.0' => array(
		'type'    => 'manual',
		'updates' => array(
			'llms_update_3160_update_quiz_settings',
			'llms_update_3160_lesson_to_quiz_relationships_migration',
			'llms_update_3160_attempt_migration',
			'llms_update_3160_ensure_no_dupe_question_rels',
			'llms_update_3160_ensure_no_lesson_dupe_rels',
			'llms_update_3160_update_question_data',
			'llms_update_3160_update_attempt_question_data',
			'llms_update_3160_update_quiz_to_lesson_rels',
			'llms_update_3160_builder_notice',
			'llms_update_3160_update_db_version',
		),
	),
	'3.28.0' => array(
		'type'    => 'manual',
		'updates' => array(
			'llms_update_3280_clear_session_cleanup_cron',
			'llms_update_3280_update_db_version',
		),
	),
	'4.0.0'  => array(
		'type'    => 'manual',
		'updates' => array(
			'llms_update_400_remove_session_options',
			'llms_update_400_clear_session_cron',
			'llms_update_400_update_db_version',
		),
	),
	'4.5.0'  => array(
		'type'    => 'manual',
		'updates' => array(
			'llms_update_450_migrate_events_open_sessions',
			'llms_update_450_update_db_version',
		),
	),
	'4.15.0' => array(
		'type'    => 'manual',
		'updates' => array(
			'llms_update_4150_remove_orphan_access_plans',
			'llms_update_4150_update_db_version',
		),
	),
	'5.0.0'  => array(
		'type'    => 'auto',
		'updates' => array(
			'llms_update_500_legacy_options_autoload_off',
			'llms_update_500_update_db_version',
			'llms_update_500_add_admin_notice',
		),
	),
	'5.2.0'  => array(
		'type'    => 'auto',
		'updates' => array(
			'llms_update_520_upcoming_reminder_notification_backward_compat',
			'llms_update_520_update_db_version',
		),
	),
	'6.0.0'  => array(
		'type'      => 'manual',
		'namespace' => true,
		'updates'   => array(
			'migrate_achievements',
			'migrate_certificates',
			'migrate_award_templates',
			'show_notice',
			'update_db_version',
		),
	),
	'6.3.0'  => array(
		'type'      => 'auto',
		'namespace' => true,
		'updates'   => array(
			'buddypress_profile_endpoints_bc',
			'update_db_version',
		),
	),
	'6.10.0' => array(
		'type'      => 'auto',
		'namespace' => true,
		'updates'   => array(
			'migrate_spanish_users',
			'update_db_version',
		),
	),
	'7.5.0'  => array(
		'type'      => 'auto',
		'namespace' => true,
		'updates'   => array(
			'favorites_feature_bc',
			'update_db_version',
		),
	),
	'7.8.0'  => array(
		'type'      => 'auto',
		'namespace' => true,
		'updates'   => array(
			'maybe_set_option_llms_access_plans_allow_skus',
			'update_db_version',
		),
	),
	'7.8.5'  => array(
		'type'      => 'auto',
		'namespace' => true,
		'updates'   => array(
			'maybe_remove_pwc',
			'update_db_version',
		),
	),
);
