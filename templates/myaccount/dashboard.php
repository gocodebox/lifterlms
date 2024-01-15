<?php
/**
 * My Account page.
 *
 * @package LifterLMS/Templates
 *
 * @since 1.0.0
 * @since 7.5.0 Hooked my_favorites function.
 * @version 7.5.0
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
		 * @hooked llms_template_student_dashboard_my_favorites - 50
		 */
		do_action( 'lifterlms_student_dashboard_index', true );

		do_action( 'lifterlms_after_student_dashboard_tab' );

	?>

</div>
