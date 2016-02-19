<?php
/**
 * Registration Form
 *
 * @author 		lifterLMS
 * @package 	lifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$product_id = get_query_var( 'product-id' );
global $wpdb;
llms_print_notices();
?>

<?php if ( get_option( 'lifterlms_enable_myaccount_registration' ) === 'yes' ) :

	include( llms_get_template_part_contents( 'global/form', 'registration' ) );

	$html = ob_get_clean();

	echo $html;

endif; ?>
