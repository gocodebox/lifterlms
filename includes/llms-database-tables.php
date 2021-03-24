<?php
/**
 * LifterLMS Database tables list
 *
 * Returns an array representing LifterLMS database tables.
 *
 * @package LifterLMS/Database
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

return array(

	'lifterlms_user_postmeta' => '
		`meta_id` bigint(20) NOT NULL AUTO_INCREMENT,
		`user_id` bigint(20) NOT NULL,
		`post_id` bigint(20) NOT NULL,
		`meta_key` varchar(255) NULL,
		`meta_value` longtext NULL,
		`updated_date` datetime NOT NULL DEFAULT "0000-00-00 00:00:00",
		PRIMARY KEY (`meta_id`),
		KEY `user_id` (`user_id`),
		KEY `post_id` (`post_id`)
	',
	'lifterlms_quiz_attempts' => '
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		`student_id` bigint(20) DEFAULT NULL,
		`quiz_id` bigint(20) DEFAULT NULL,
		`lesson_id` bigint(20) DEFAULT NULL,
		`start_date` datetime DEFAULT NULL,
		`update_date` datetime DEFAULT NULL,
		`end_date` datetime DEFAULT NULL,
		`status` varchar(15) DEFAULT "",
		`attempt` bigint(20) DEFAULT NULL,
		`grade` float DEFAULT NULL,
		`questions` longtext,
		PRIMARY KEY (`id`),
		KEY `student_id` (`student_id`),
		KEY `quiz_id` (`quiz_id`)
	',
	'lifterlms_product_to_voucher' => '
		`product_id` bigint(20) NOT NULL,
		`voucher_id` bigint(20) NOT NULL,
		KEY `product_id` (`product_id`),
		KEY `voucher_id` (`voucher_id`)
	',
	'lifterlms_voucher_code_redemptions' => '
		`id` int(20) unsigned NOT NULL AUTO_INCREMENT,
		`code_id` bigint(20) NOT NULL,
		`user_id` bigint(20) NOT NULL,
		`redemption_date` datetime DEFAULT NULL,
		PRIMARY KEY (`id`),
		KEY `code_id` (`code_id`),
		KEY `user_id` (`user_id`)
	',
	'lifterlms_vouchers_codes' => '
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		`voucher_id` bigint(20) NOT NULL,
		`code` varchar(20) NOT NULL DEFAULT "",
		`redemption_count` bigint(20) DEFAULT NULL,
		`is_deleted` tinyint(1) NOT NULL DEFAULT "0",
		`created_at` datetime DEFAULT NULL,
		`updated_at` datetime DEFAULT NULL,
		PRIMARY KEY (`id`),
		KEY `code` (`code`),
		KEY `voucher_id` (`voucher_id`)
	',
	'lifterlms_notifications' => '
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		`created` datetime DEFAULT NULL,
		`updated` datetime DEFAULT NULL,
		`status` varchar(11) DEFAULT "0",
		`type` varchar(75) DEFAULT NULL,
		`subscriber` varchar(255) DEFAULT NULL,
		`trigger_id` varchar(75) DEFAULT NULL,
		`user_id` bigint(20) DEFAULT NULL,
		`post_id` bigint(20) DEFAULT NULL,
		PRIMARY KEY (`id`),
		KEY `status` (`status`),
		KEY `type` (`type`),
		KEY `subscriber` (`subscriber`(191))
	',
	'lifterlms_events' => '
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		`date` datetime DEFAULT NULL,
		`actor_id` bigint(20) DEFAULT NULL,
		`object_type` varchar(55) DEFAULT NULL,
		`object_id` bigint(20) DEFAULT NULL,
		`event_type` varchar(55) DEFAULT NULL,
		`event_action` varchar(55) DEFAULT NULL,
		`meta` longtext DEFAULT NULL,
		PRIMARY KEY (`id`),
		KEY actor_id (`actor_id`),
		KEY object_id (`object_id`)
	',
	'lifterlms_events_open_sessions' => '
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		`event_id` bigint(20) unsigned NOT NULL,
		PRIMARY KEY (`id`)
	',
	'lifterlms_sessions' => '
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		`session_key` char(32) NOT NULL,
		`data` longtext NOT NULL,
		`expires` BIGINT unsigned NOT NULL,
		PRIMARY KEY (`id`),
		UNIQUE KEY `session_key` (`session_key`)
	',
	'lifterlms_form_fields' => '
		`id` varchar(255) NOT NULL,
		`name` varchar(255) NOT NULL,
		`field_type` varchar(55) NOT NULL,
		`store` varchar(255) NOT NULL,
		`store_key` varchar(255) NOT NULL,
		`protected` tinyint(1) NOT NULL DEFAULT "0",
		PRIMARY KEY (`id`),
		KEY `name` (`name`)
	',
	'lifterlms_form_fields_meta' => '
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		`field_id` varchar(255) NOT NULL,
		`meta_key` varchar(255) NULL,
		`meta_value` longtext NULL,
		PRIMARY KEY (`id`),
		KEY `field_id` (`field_id`),
		KEY `meta_key` (`meta_key`)
	',
);
