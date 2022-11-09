<?php
/**
 * Table Schema Definition: lifterlms_voucher_code_redemptions
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
	'name'        => 'voucher_code_redemptions',
	'description' => 'Records user voucher redemptions.',
	'columns'     => array(
		'id'              => array(
			'description'    => 'The redemption ID.',
			'type'           => 'int', // @todo The table *should* be altered to bigint.
			'length'         => 20,
			'allow_null'     => false,
			'auto_increment' => true,
			'unsigned'       => true,
		),
		'code_id'         => array(
			'description' => 'The `WP_Post` ID of the voucher.',
			'type'        => 'id',
		),
		'user_id'         => array(
			'description' => 'The `WP_User` ID.',
			'type'        => 'id',
		),
		'redemption_date' => array(
			'description' => 'The timestamp of the last update to the metadata.',
			'type'        => 'datetime',
		),
	),
	'keys'        => array(
		'id'      => LLMS_Database_Table::KEY_PRIMARY,
		'code_id' => LLMS_Database_Table::KEY_DEFAULT,
		'user_id' => LLMS_Database_Table::KEY_DEFAULT,
	),
);
