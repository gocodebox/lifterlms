<?php
/**
 * Student Dashboard Header
 *
 * @package LifterLMS/Templates
 *
 * @since    3.14.0
 * @version  3.14.0
 */

defined( 'ABSPATH' ) || exit;

?>
<header class="llms-sd-header">

	<?php
	/**
	 * @since [version] Added the $show_nav parameter.
	 *
	 * @hooked lifterlms_template_my_account_navigation - 10
	 * @hooked lifterlms_template_student_dashboard_title - 20
	 *
	 * @param bool $show_nav Whether to show the navigation.
	 */
	do_action( 'lifterlms_student_dashboard_header', true );
	?>

</header>
