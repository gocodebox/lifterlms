<?php
/**
 * Button to view a course or membership.
 *
 * @package LifterLMS/Templates
 *
 * @since Unknown
 * @version Unknown
 */

defined( 'ABSPATH' ) || exit;

global $post;

$course = new LLMS_Course( $post );
?>

<footer class="llms-course-footer">

<?php
if ( 'course' === $post->post_type ) :
	if ( ! llms_is_user_enrolled( get_current_user_id(), $course->id ) ) :
		?>
			<span class="llms-button-primary llms-purchase-button"><?php _e( 'View Course', 'lifterlms' ); ?></span>

		<?php
		else :
			$course_progress = $course->get_percent_complete();
			$user            = new LLMS_Person();
			$user_postmetas  = $user->get_user_postmeta_data( get_current_user_id(), $course->id );

			lifterlms_course_progress_bar( $course_progress );

		endif;
	endif;

if ( 'llms_membership' === $post->post_type ) :
	?>

<span class="llms-button-primary llms-purchase-button"><?php _e( 'Learn More', 'lifterlms' ); ?></span>
<?php endif; ?>

</footer>
