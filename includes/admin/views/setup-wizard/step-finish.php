<?php
/**
 * Setup Wizard step: Finish
 *
 * @package LifterLMS/Views/Admin/SetupWizard
 *
 * @since 4.4.4
 * @since 4.8.0 Unknown.
 * @since 7.4.0 Escape output.
 * @version 7.4.0
 *
 * @property LLMS_Admin_Setup_Wizard $this Setup wizard class instance.
 */

defined( 'ABSPATH' ) || exit;

$courses = LLMS_Export_API::list( 1, 3 );
?>
<h1><?php esc_html_e( 'Setup Complete!', 'lifterlms' ); ?></h1>
<p><?php esc_html_e( 'Here\'s some resources to help you get familiar with LifterLMS:', 'lifterlms' ); ?></p>
<ul>
	<li><span class="dashicons dashicons-format-video"></span> <a href="https://demo.lifterlms.com/course/how-to-build-a-learning-management-system-with-lifterlms/?utm_source=LifterLMS%20Plugin&utm_campaign=Plugin%20to%20Sale&utm_medium=Wizard&utm_content=LifterLMS%Video%20Tutorials" target="_blank"><?php esc_html_e( 'Watch the LifterLMS video tutorials', 'lifterlms' ); ?></a></li>
	<li><span class="dashicons dashicons-admin-page"></span> <a href="https://lifterlms.com/docs/getting-started-guide-with-lifterlms-resources/?utm_source=LifterLMS%20Plugin&utm_campaign=Plugin%20to%20Sale&utm_medium=Wizard&utm_content=LifterLMS%Getting%20Started" target="_blank"><?php esc_html_e( 'Read the LifterLMS Getting Started Guide', 'lifterlms' ); ?></a></li>
</ul>
<br>

<h1><?php esc_html_e( 'Import Sample Courses and Templates!', 'lifterlms' ); ?></h1>
<p><?php esc_html_e( 'Accelerate your progress by installing a quick LifterLMS training course and useful course templates.', 'lifterlms' ); ?></p>

<?php require LLMS_PLUGIN_DIR . 'includes/admin/views/importable-courses.php'; ?>

<div class="llms-importing-msgs">
	<p class="llms-importing-msg single">
		<?php
		printf(
			// Translators: %s = anchor link to LifterLMS.com.
			esc_html__( 'The selected course will be downloaded and imported into this site from %s.', 'lifterlms' ),
			'<a href="https://lifterlms.com/?utm_source=LifterLMS%20Plugin&utm_campaign=Plugin%20to%20Sale&utm_medium=Wizard&utm_content=LifterLMS%20Home" target="_blank">LifterLMS.com</a>'
		);
		?>
	</p>
	<p class="llms-importing-msg multiple">
		<?php
		printf(
			// Translators: %1$s = The number of selected courses; %2$s = anchor link to LifterLMS.com.
			esc_html__( 'The %1$s selected courses will be downloaded and imported into this site from %2$s.', 'lifterlms' ),
			'<span id="llms-importing-number">2</span>',
			'<a href="https://lifterlms.com/?utm_source=LifterLMS%20Plugin&utm_campaign=Plugin%20to%20Sale&utm_medium=Wizard&utm_content=LifterLMS%20Home" target="_blank">LifterLMS.com</a>'
		);
		?>
	</p>
</div>
