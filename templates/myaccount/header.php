<?php
/**
 * Student Dashboard Header
 * @since    [version]
 * @version  [version]
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

?>
<header class="llms-sd-header">

	<?php
	/**
	 * @hooked lifterlms_template_my_account_navigation - 10
	 * @hooked lifterlms_template_student_dashboard_title - 20
	 */
	do_action( 'lifterlms_student_dashboard_header' ); ?>

</header>
