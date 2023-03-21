<?php

/**
 * Template for favorite button.
 *
 * @author LifterLMS
 * @package LifterLMS/Templates
 *
 * @since [version]
 *
 * @var LLMS_Lesson     $lesson        The lesson object.
 * @var LLMS_Student    $student       A LLMS_Student object.
 */
defined( 'ABSPATH' ) || exit;

global $post;

if ( ! is_user_logged_in() ) {
	return;
}

$lesson          = new LLMS_Lesson( $post->ID );
$student         = llms_get_student( get_current_user_id() );
$total_favorites = get_total_favorites( $lesson->get( 'id' ) );
?>

<div class="llms-favorite-wrapper">

	<?php do_action( 'llms_before_favorite_button', $lesson, $student ); ?>

	<?php if ( $student->is_favorite( $lesson->get( 'id' ), 'lesson' ) ) : ?>

		<!-- TODO: Dynamic data-type [Lesson, Course, Instructor] value. -->
		<i data-action="unfavorite" data-type="lesson" data-id="<?php echo esc_attr( $lesson->get( 'id' ) ); ?>" class="fa fa-heart llms-unfavorite-btn llms-heart-btn"></i>

	<?php else : ?>
		
		<!-- TODO: Dynamic data-type [Lesson, Course, Instructor] value. -->
		<i data-action="favorite" data-type="lesson" data-id="<?php echo esc_attr( $lesson->get( 'id' ) ); ?>" class="fa fa-heart-o llms-favorite-btn llms-heart-btn"></i>

	<?php endif; ?>

	<span class="llms-favorites-count">
		<?php echo $total_favorites; ?>
	</span>

	<?php do_action( 'llms_after_favorite_button', $lesson, $student ); ?>

</div>
