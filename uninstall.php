<?php
/**
 * LifterLMS Uninstall
 *
 * @author 		codeBOX
 * @category 	Core
 * @package 	LifterLMS/Uninstaller
 * @version     0.1
 */

// If uninstall not called from WordPress exit 
if( ! defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit ();

// Delete option from options table 
delete_option( 'lifterlms_options' );

//remove additional options and custom tables