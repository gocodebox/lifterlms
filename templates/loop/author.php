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

// Generated HTML is escaped inside the function.
// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo llms_get_author(
	array(
		'avatar_size' => 28,
	)
);

