<?php

/**
 * Template for favorite button.
 *
 * @author LifterLMS
 * @package LifterLMS/Templates
 *
 * @since [version]
 *
 * @var int    $object_id   WP Post ID of the Lesson, Section, Track, or Course.
 * @var string $object_type Object type [lesson|section|course|track].
 */
defined( 'ABSPATH' ) || exit;

global $post;

$lesson          = new LLMS_Lesson( ( null === $object_id || '' === $object_id ) ? $post->ID : $object_id );
$student         = llms_get_student( get_current_user_id() );
$total_favorites = get_total_favorites( $lesson->get( 'id' ) );
?>

<div class="llms-favorite-wrapper">

	<?php do_action( 'llms_before_favorite_button', $lesson, $student ); ?>

	<?php if ( ! is_user_logged_in() ) { ?>

		<i class="fa fa-heart-o llms-favorite-btn llms-heart-btn"></i>

	<?php } else { ?>

		<?php if ( $student->is_favorite( $lesson->get( 'id' ), 'lesson' ) ) : ?>

			<!-- TODO: Dynamic data-type [Lesson, Course, Instructor] value. -->
			<i data-action="unfavorite" data-type="lesson" data-id="<?php echo esc_attr( $lesson->get( 'id' ) ); ?>" class="fa fa-heart llms-unfavorite-btn llms-heart-btn"></i>

		<?php else : ?>

			<!-- TODO: Dynamic data-type [Lesson, Course, Instructor] value. -->
			<i data-action="favorite" data-type="lesson" data-id="<?php echo esc_attr( $lesson->get( 'id' ) ); ?>" class="fa fa-heart-o llms-favorite-btn llms-heart-btn"></i>

		<?php endif; ?>

	<?php } ?>

	<span class="llms-favorites-count">
		<?php echo $total_favorites; ?>
	</span>

	<?php do_action( 'llms_after_favorite_button', $lesson, $student ); ?>

</div>
