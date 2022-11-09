<?php
/**
 * Table Schema Definition: lifterlms_vouchers_codes
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
	'name'        => 'vouchers_codes',
	'description' => 'Stores voucher codes which can be used to redeem a voucher.',
	'columns'     => array(
		'id'               => array(
			'description' => 'The redemption ID.',
			'type'        => 'primary_id',
		),
		'voucher_id'       => array(
			'description' => 'The `WP_Post` ID of the voucher.',
			'type'        => 'id',
		),
		'code'             => array(
			'description' => 'The voucher code.',
			'type'        => 'varchar',
			'length'      => 20,
			'default'     => '',
			'allow_null'  => false,
		),
		'redemption_count' => array(
			'description' => 'The number of times the code can be redeemed.',
			'type'        => 'id',
		),
		'is_deleted'       => array(
			'description' => 'Whether the code has been deleted.',
			'type'        => 'tinyint',
			'length'      => 1,
			'default'     => 0,
			'allow_null'  => false,
		),
		'created_at'       => array(
			'description' => 'The timestamp recorded when the code is created.',
			'type'        => 'datetime',
		),
		'updated_at'       => array(
			'description' => 'The timestamp recorded when the code is updated.',
			'type'        => 'datetime',
		),
	),
	'keys'        => array(
		'id'         => LLMS_Database_Table::KEY_PRIMARY,
		'code'       => LLMS_Database_Table::KEY_DEFAULT,
		'voucher_id' => LLMS_Database_Table::KEY_DEFAULT,
	),
);
