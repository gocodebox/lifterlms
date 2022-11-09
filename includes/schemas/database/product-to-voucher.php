<?php
/**
 * Table Schema Definition: lifterlms_product_to_voucher
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
	'name'        => 'product_to_voucher',
	'description' => 'Stores relationship data between products and vouchers.',
	'columns'     => array(
		'product_id' => array(
			'description' => 'The `WP_Post` ID of the product.',
			'type'        => 'id',
		),
		'voucher_id' => array(
			'description' => 'The `WP_Post` ID of the voucher.',
			'type'        => 'id',
		),
	),
	'keys'        => array(
		'product_id' => LLMS_Database_Table::KEY_DEFAULT,
		'voucher_id' => LLMS_Database_Table::KEY_DEFAULT,
	),
);
