<?php
/**
 * Registration Form
 *
 * @author 		LifterLMS
 * @package 	LifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

llms_print_notices();

if ( get_option( 'lifterlms_enable_myaccount_registration' ) === 'yes' ) :

	llms_get_template( 'global/form-registration.php' );

endif;
