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
	'name'        => 'user_postmeta',
	'description' => 'Stores user information related to the specified post. Such as student enrollment and progress data.',
	'columns'     => array(
		'meta_id'      => array(
			'description' => 'The meta data ID.',
			'type'        => 'primary_id',
		),
		'user_id'      => array(
			'description' => 'A `WP_User` ID.',
			'type'        => 'id',
		),
		'post_id'      => array(
			'description' => 'A `WP_Post` ID.',
			'type'        => 'id',
		),
		'meta_key'     => array(
			'description' => 'The meta data key / name.',
			'type'        => 'varchar',
			'length'      => 255,
			'default'     => 'NULL',
		),
		'meta_value'   => array(
			'description' => 'The meta data value.',
			'type'        => 'longtext',
			'default'     => 'NULL',
		),
		'updated_date' => array(
			'description' => 'The timestamp of the last update to the metadata.',
			'type'        => 'datetime',
			'default'     => '0000-00-00 00:00:00',
			'allow_null'  => false,
		),
	),
	'keys'        => array(
		'meta_id' => LLMS_Database_Table::KEY_PRIMARY,
		'user_id' => LLMS_Database_Table::KEY_DEFAULT,
		'post_id' => LLMS_Database_Table::KEY_DEFAULT,
	),
);
