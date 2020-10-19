<?php
/**
 * Setup Wizard step: Finish
 *
 * @since 4.4.4
 * @version [version]
 *
 * @property LLMS_Admin_Setup_Wizard $this Setup wizard class instance.
 */

defined( 'ABSPATH' ) || exit;

$courses = llms_get_importable_courses( 1, 3 );
?>
<h1><?php _e( 'Setup Complete!', 'lifterlms' ); ?></h1>
<p><?php _e( 'Here\'s some resources to help you get familiar with LifterLMS:', 'lifterlms' ); ?></p>
<ul>
	<li><span class="dashicons dashicons-format-video"></span> <a href="https://demo.lifterlms.com/course/how-to-build-a-learning-management-system-with-lifterlms/" target="_blank"><?php _e( 'Watch the LifterLMS video tutorials', 'lifterlms' ); ?></a></li>
	<li><span class="dashicons dashicons-admin-page"></span> <a href="https://lifterlms.com/docs/getting-started-with-lifterlms/" target="_blank"><?php _e( 'Read the LifterLMS Getting Started Guide', 'lifterlms' ); ?></a></li>
</ul>
<br>

<h1><?php _e( 'Install Sample Courses!', 'lifterlms' ); ?></h1>
<p><?php _e( 'Accelerate your progress by installing a quick LifterLMS training course and useful course templates.', 'lifterlms' ); ?></p>

<?php include LLMS_PLUGIN_DIR . 'includes/admin/views/importable-courses.php'; ?>
