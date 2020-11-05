<?php
/**
 * List importable courses
 *
 * @package LifterLMS/Admin/Views
 *
 * @since [version]
 * @version [version]
 *
 * @property array[] $courses List of importable course data.
 */

defined( 'ABSPATH' ) || exit;
?>
<ul class="llms-importable-courses">
<?php
	/**
	 * Action run prior to the output of an importable course list
	 *
	 * @since [version]
	 *
	 * @param array[] $courses List of importable course data.
	 */
	do_action( 'llms_before_importable_courses', $courses );

foreach ( $courses as $course ) {
	include LLMS_PLUGIN_DIR . 'includes/admin/views/importable-course.php';
}

	/**
	 * Action run after the output of an importable course list
	 *
	 * @since [version]
	 *
	 * @param array[] $courses List of importable course data.
	 */
	do_action( 'llms_after_importable_courses', $courses );
?>
</ul>
