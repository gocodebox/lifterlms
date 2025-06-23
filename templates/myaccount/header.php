<?php
/**
 * Student Dashboard Header
 *
 * @package LifterLMS/Templates
 *
 * @since    3.14.0
 * @since    7.8.0
 * @version  3.14.0
 */

defined( 'ABSPATH' ) || exit;

?>
<header class="llms-sd-header">

	<?php
	/**
	 * @hooked lifterlms_template_student_dashboard_title - 10
	 */
	do_action( 'lifterlms_student_dashboard_header' );
	?>

</header>
