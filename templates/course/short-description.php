<?php
/**
 * @author      codeBOX
 * @package     lifterLMS/Templates
 */

defined( 'ABSPATH' ) || exit;

global $post;
// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo the_excerpt();
