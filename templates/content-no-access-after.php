<?php
/**
 * Template displayed after content on a restricted page
 *
 * @package LifterLMS/Templates
 *
 * @since 1.0.0
 * @since [version] Removed redundant notice output call and replaced duplicated hook with a new hook.
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Action triggered after the main content of a restricted page is rendered
 *
 * @since [version]
 */
do_action( 'lifterlms_no_access_after' );
