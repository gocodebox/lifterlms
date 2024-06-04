<?php
/**
 * List importable courses
 *
 * @package LifterLMS/Admin/Views
 *
 * @since 4.8.0
 * @version 4.8.0
 *
 * @property array[] $courses List of importable course data.
 */

defined( 'ABSPATH' ) || exit;
?>

<?php if ( is_wp_error( $courses ) ) : ?>

	<p class="llms-error">
		<?php
			// Translators: %s = Text of the HTTP Request error message.
			printf( esc_html__( 'There was an error loading importable courses. Please reload the page to try again. [%s]', 'lifterlms' ), wp_kses_post( $courses->get_error_message() ) );
		?>
	</p>

<?php else : ?>
	<ul class="llms-importable-courses">
		<?php
		/**
		 * Action run prior to the output of an importable course list
		 *
		 * @since 4.8.0
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
		 * @since 4.8.0
		 *
		 * @param array[] $courses List of importable course data.
		 */
		do_action( 'llms_after_importable_courses', $courses );
		?>
	</ul>
<?php endif; ?>
