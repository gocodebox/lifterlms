<?php
/**
 * Table Schema Definition: lifterlms_notifications
 *
 * @package LifterLMS/Schemas/Database
 *
 * @since [version]
 * @version [version]
 *
 * @see LLMS_Database_Table
 */

defined( 'ABSPATH' ) || exit;

return array(
	'name'        => 'notifications',
	'description' => 'Stores user notification data.',
	'columns'     => array(
		'id'         => array(
			'description' => 'The notification ID.',
			'type'        => 'primary_id',
		),
		'created'    => array(
			'description' => 'A timestamp recorded when the notification is created.',
			'type'        => 'datetime',
		),
		'updated'    => array(
			'description' => 'A timestamp recorded when the notification is updated.',
			'type'        => 'datetime',
		),
		'status'     => array(
			'description' => 'The notification status.',
			'type'        => 'varchar',
			'length'      => 11,
			'default'     => '0',
		),
		'type'       => array(
			'description' => 'The notification type.',
			'type'        => 'varchar',
			'length'      => 75,
		),
		'subscriber' => array(
			'description' => 'The notification subscriber.',
			'type'        => 'varchar',
			'length'      => 255,
		),
		'trigger_id' => array(
			'description' => 'The ID of the notification trigger.',
			'type'        => 'varchar',
			'length'      => 75,
		),
		'user_id'    => array(
			'description' => 'The `WP_User` ID of the user who triggered the notification.',
			'type'        => 'id',
			'allow_null'  => true,
		),
		'post_id'    => array(
			'description' => 'The `WP_Post` ID of a related post.',
			'type'        => 'id',
			'allow_null'  => true,
		),
	),
	'keys'        => array(
		'id'         => LLMS_Database_Table::KEY_PRIMARY,
		'status'     => LLMS_Database_Table::KEY_DEFAULT,
		'type'       => LLMS_Database_Table::KEY_DEFAULT,
		'subscriber' => array( 'length' => 191 ),
	),
);
