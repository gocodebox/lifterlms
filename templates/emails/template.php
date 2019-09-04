<?php
/**
 * LifterLMS Emails Template
 *
 * @since    1.0.0
 * @version  3.8.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * lifterlms_email_header hook
 *
 * @hooked llms_email_header - 10
 */
do_action( 'lifterlms_email_header', $email_heading );

/**
 * lifterlms_email_body hook
 *
 * @hooked llms_email_body
 */
do_action( 'lifterlms_email_body', $email_message );

/**
 * lifterlms_email_footer hook
 *
 * @hooked llms_email_footer - 10
 */
do_action( 'lifterlms_email_footer' );
