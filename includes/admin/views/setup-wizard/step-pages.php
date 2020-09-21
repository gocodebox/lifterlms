<?php
/**
 * Setup Wizard step: Page Setup
 *
 * @since 4.4.4
 * @version 4.4.4
 *
 * @property LLMS_Admin_Setup_Wizard $this Setup wizard class instance.
 */

defined( 'ABSPATH' ) || exit;
?>
<h1><?php _e( 'Page Setup', 'lifterlms' ); ?></h1>

<p><?php _e( 'LifterLMS has a few essential pages. The following will be created automatically if they don\'t already exist.', 'lifterlms' ); ?>

<table>
	<tr>
		<td><a href="https://lifterlms.com/docs/course-catalog/" target="_blank"><?php _e( 'Course Catalog', 'lifterlms' ); ?></a></td>
		<td><p><?php _e( 'This page is where your visitors will find a list of all your available courses.', 'lifterlms' ); ?></p></td>
	</tr>
	<tr>
		<td><a href="https://lifterlms.com/docs/membership-catalog/" target="_blank"><?php _e( 'Membership Catalog', 'lifterlms' ); ?></a></td>
		<td><p><?php _e( 'This page is where your visitors will find a list of all your available memberships.', 'lifterlms' ); ?></p></td>
	</tr>
	<tr>
		<td><a href=" https://lifterlms.com/docs/checkout-page/" target="_blank"><?php _e( 'Checkout', 'lifterlms' ); ?></a></td>
		<td><p><?php _e( 'This is the page where visitors will be directed in order to pay for courses and memberships.', 'lifterlms' ); ?></p></td>
	</tr>
	<tr>
		<td><a href="https://lifterlms.com/docs/student-dashboard/" target="_blank"><?php _e( 'Student Dashboard', 'lifterlms' ); ?></a></td>
		<td><p><?php _e( 'Page where students can view and manage their current enrollments, earned certificates and achievements, account information, and purchase history.', 'lifterlms' ); ?></p></td>
	</tr>
</table>

<p><?php printf( __( 'After setup, you can manage these pages from the admin dashboard on the %1$sPages screen%2$s and you can control which pages display on your menu(s) via %3$sAppearance > Menus%4$s.', 'lifterlms' ), '<a href="' . esc_url( admin_url( 'edit.php?post_type=page' ) ) . '" target="_blank">', '</a>', '<a href="' . esc_url( admin_url( 'nav-menus.php' ) ) . '" target="_blank">', '</a>' ); ?></p>
