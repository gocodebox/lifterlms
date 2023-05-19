<?php
/**
 * Table Schema Definition: lifterlms_sessions
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
	'name'        => 'sessions',
	'description' => 'Records user sessions.',
	'columns'     => array(
		'id'          => array(
			'description' => 'The session ID.',
			'type'        => 'primary_id',
		),
		'session_key' => array(
			'description' => 'The unique session key.',
			'type'        => 'char',
			'length'      => 32,
			'allow_null'  => false,
		),
		'data'        => array(
			'description' => 'The session data, stored as a JSON object.',
			'type'        => 'longtext',
			'allow_null'  => false,
		),
		'expires'     => array(
			'description' => 'The session data, stored as a JSON object.',
			'type'        => 'id',
		),
	),
	'keys'        => array(
		'id'          => LLMS_Database_Table::KEY_PRIMARY,
		'session_key' => LLMS_Database_Table::KEY_UNIQUE,
	),
);
