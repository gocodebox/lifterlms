<?php
/**
 * My Account page
 *
 * @package LifterLMS/Templates
 *
 * @since    1.0.0
 * @version  3.14.0
 */

defined( 'ABSPATH' ) || exit;

llms_print_notices();
?>

<div class="llms-sd-tab dashboard">

	<?php

		do_action( 'lifterlms_before_student_dashboard_tab' );

		/**
		 * lifterlms_student_dashboard_index
		 *
		 * @hooked lifterlms_template_student_dashboard_my_courses - 10
		 * @hooked lifterlms_template_student_dashboard_my_achievements - 20
		 * @hooked lifterlms_template_student_dashboard_my_certificates - 30
		 * @hooked lifterlms_template_student_dashboard_my_memberships - 40
		 */
		do_action( 'lifterlms_student_dashboard_index', true );

		do_action( 'lifterlms_after_student_dashboard_tab' );

	?>

</div>
