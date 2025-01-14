<?php
/**
 * Display a single importable course
 *
 * @package LifterLMS/Admin/Views
 *
 * @since 4.8.0
 * @version 4.8.0
 *
 * @property array $course A hash of importable course data.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Filters whether or not an action button should be displayed for an importable course.
 *
 * @since 4.8.0
 *
 * @param boolean $show_button Whether or not to show the button.
 * @param array   $course      Hash of the importable course data.
 */
$show_button = apply_filters( 'llms_importable_course_show_action', true, $course );
?>
<li class="llms-importable-course<?php echo $show_button ? ' has-action-button' : ''; ?>">

	<?php
		/**
		 * Action run prior to the output of an importable course item
		 *
		 * @since 4.8.0
		 *
		 * @param array $course Hash of the importable course data.
		 */
		do_action( 'llms_before_importable_course', $course );
	?>

	<img alt="<?php printf( esc_attr__( '%s featured image', 'lifterlms' ), esc_attr( $course['title'] ) ); ?>" src="<?php echo esc_url( $course['image'] ); ?>">

	<h3><?php echo esc_html( $course['title'] ); ?></h3>
	<p><?php echo esc_html( $course['description'] ); ?></p>

	<?php
		/**
		 * Action run after the output of an importable course item
		 *
		 * This runs after the item's content but before the item's action button.
		 *
		 * @since 4.8.0
		 *
		 * @param array $course Hash of the importable course data.
		 */
		do_action( 'llms_after_importable_course', $course );
	?>

	<?php if ( $show_button ) : ?>
		<p><button class="llms-button-secondary" name="llms_cloud_import_course_id" type="submit" value="<?php echo absint( $course['id'] ); ?>"><?php esc_html_e( 'Download & Import', 'lifterlms' ); ?></button></p>
	<?php endif; ?>

</li>
