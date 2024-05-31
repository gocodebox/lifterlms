<?php
/**
 * LifterLMS Loop Author Info
 *
 * @package LifterLMS/Templates
 *
 * @since   3.0.0
 * @version 3.0.0
 */

defined( 'ABSPATH' ) || exit;

echo llms_get_author(
	array(
		'avatar_size' => 28,
	)
);

