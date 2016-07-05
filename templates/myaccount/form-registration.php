<?php
/**
 * Registration Form
 *
 * @author 		LifterLMS
 * @package 	LifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

llms_print_notices();


if ( get_option( 'lifterlms_enable_myaccount_registration' ) === 'yes' || get_query_var( 'product-id' ) ) :

	include( llms_get_template_part_contents( 'global/form', 'registration' ) );

endif;
