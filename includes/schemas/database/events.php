<?php
/**
 * Table Schema Definition: lifterlms_events
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
	'name'        => 'events',
	'description' => 'Stores user interaction event data.',
	'columns'     => array(
		'id'           => array(
			'description' => 'The event ID.',
			'type'        => 'primary_id',
		),
		'date'         => array(
			'description' => 'A timestamp recorded when the event is created.',
			'type'        => 'datetime',
		),
		'actor_id'     => array(
			'description' => 'The `WP_User` ID of the user.',
			'type'        => 'id',
			'allow_null'  => true,
		),
		'object_type'  => array(
			'description' => 'The type of object the event was made against.',
			'type'        => 'varchar',
			'length'      => 55,
		),
		'object_id'    => array(
			'description' => 'The ID of the object the event was made against.',
			'type'        => 'id',
			'allow_null'  => true,
		),
		'event_type'   => array(
			'description' => 'The type of event.',
			'type'        => 'varchar',
			'length'      => 55,
		),
		'event_action' => array(
			'description' => 'The type of action.',
			'type'        => 'varchar',
			'length'      => 55,
		),
		'meta'         => array(
			'description' => 'Event meta data, stored as a JSON object.',
			'type'        => 'longtext',
		),
	),
	'keys'        => array(
		'id'        => LLMS_Database_Table::KEY_PRIMARY,
		'actor_id'  => LLMS_Database_Table::KEY_DEFAULT,
		'object_id' => LLMS_Database_Table::KEY_DEFAULT,
	),
);
