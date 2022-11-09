<?php
/**
 * Table Schema Definition: lifterlms_events_open_sessions
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
	'name'        => 'events_open_sessions',
	'description' => 'Records all open user sessions.',
	'columns'     => array(
		'id'       => array(
			'description' => 'The session ID.',
			'type'        => 'primary_id',
		),
		'event_id' => array(
			'description' => 'The ID of the event that started the session.',
			'type'        => 'id',
		),
	),
	'keys'        => array(
		'id' => LLMS_Database_Table::KEY_PRIMARY,
	),
);
