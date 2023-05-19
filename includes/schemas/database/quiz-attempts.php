<?php
/**
 * Table Schema Definition: lifterlms_user_postmeta
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
	'name'        => 'quiz_attempts',
	'description' => 'Stores a record for each user quiz attempt.',
	'columns'     => array(
		'id'          => array(
			'description' => 'The quiz attempt ID.',
			'type'        => 'primary_id',
		),
		'student_id'  => array(
			'description' => 'A `WP_User` ID.',
			'type'        => 'id',
			'allow_null'  => true,
		),
		'quiz_id'     => array(
			'description' => 'The `WP_Post` ID of the associated quiz.',
			'type'        => 'id',
			'allow_null'  => true,
		),
		'lesson_id'   => array(
			'description' => 'The `WP_Post` ID of the associated lesson.',
			'type'        => 'id',
			'allow_null'  => true,
		),
		'start_date'  => array(
			'description' => 'Timestamp recorded when the attempt is started.',
			'type'        => 'datetime',
		),
		'update_date' => array(
			'description' => 'Timestamp recorded when the attempt is updated.',
			'type'        => 'datetime',
		),
		'end_date'    => array(
			'description' => 'Timestamp recorded when the attempt is ended.',
			'type'        => 'datetime',
		),
		'status'      => array(
			'description' => 'Attempt status.',
			'type'        => 'varchar',
			'length'      => '15',
			'default'     => '',
		),
		'attempt'     => array(
			'description' => 'Attempt number.',
			'type'        => 'bigint',
			'length'      => '20',
		),
		'grade'       => array(
			'description' => 'Attempt grade.',
			'type'        => 'float',
		),
		'questions'   => array(
			'description' => 'Object representing the quiz at the time when the attempt was started.',
			'type'        => 'longtext',
		),
	),
	'keys'        => array(
		'id'         => LLMS_Database_Table::KEY_PRIMARY,
		'student_id' => LLMS_Database_Table::KEY_DEFAULT,
		'quiz_id'    => LLMS_Database_Table::KEY_DEFAULT,
	),
);
