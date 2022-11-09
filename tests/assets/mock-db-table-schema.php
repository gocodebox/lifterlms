<?php

return array(
	'name'    => 'mock_db_table_schema',
	'columns' => array(
		'version' => 20221103.1,
		'columns' => array(
			'id' => array(
				'type'           => 'bigint',
				'length'         => 20,
				'unsigned'       => true,
				'allow_null'     => false,
				'auto_increment' => true,
			),
			'date' => array(
				'type' => 'datetime',
			),
		),
		'keys' => array(
			'primary' => 'id',
		),
	),
);
