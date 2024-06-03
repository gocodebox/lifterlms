<?php
/**
 * Template displayed after content on a restricted page
 *
 * @package LifterLMS/Templates
 *
 * @since 1.0.0
 * @since 4.17.0 Removed redundant notice output call and replaced duplicated hook with a new hook.
 * @version 4.17.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Action triggered after the main content of a restricted page is rendered
 *
 * @since 4.17.0
 */
do_action( 'lifterlms_no_access_after' );
