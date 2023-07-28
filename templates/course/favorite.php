<?php

/**
 * Template for favorite button.
 *
 * @author LifterLMS
 * @package LifterLMS/Templates
 *
 * @since [version]
 *
 * @var int    $object_id   WP Post ID of the Lesson.
 * @var string $object_type Type, 'Lesson'.
 */
defined( 'ABSPATH' ) || exit;

global $post;

$lesson          = new LLMS_Lesson(  empty( $object_id )  ? $post->ID : $object_id );
$student         = llms_get_student( get_current_user_id() );
$total_favorites = llms_get_object_total_favorites( $lesson->get( 'id' ) );
?>

<div class="llms-favorite-wrapper">

	<?php
	/**
	 * Action fired before Favorite Button Hook.
	 *
	 * @since [version]
	 *
	 * @param LLMS_Lesson  $lesson  Lesson object.
	 * @param LLMS_Student $student Student object.
	 */
	do_action( 'llms_before_favorite_button', $lesson, $student );
	?>

	<?php if ( ! is_user_logged_in() ) { ?>

		<i class="fa fa-heart-o llms-favorite-btn llms-heart-btn"></i>

	<?php } else { ?>

		<?php if ( $student->is_favorite( $lesson->get( 'id' ), 'lesson' ) ) : ?>

			<i data-action="unfavorite" data-type="lesson" data-id="<?php echo esc_attr( $lesson->get( 'id' ) ); ?>" class="fa fa-heart llms-unfavorite-btn llms-heart-btn"></i>

		<?php else : ?>

			<i data-action="favorite" data-type="lesson" data-id="<?php echo esc_attr( $lesson->get( 'id' ) ); ?>" class="fa fa-heart-o llms-favorite-btn llms-heart-btn"></i>

		<?php endif; ?>

	<?php } ?>

	<span class="llms-favorites-count">
		<?php echo $total_favorites; ?>
	</span>

	<?php
	/**
	 * Action fired after Favorite Button Hook.
	 *
	 * @since [version]
	 *
	 * @param LLMS_Lesson  $lesson  Lesson object.
	 * @param LLMS_Student $student Student object.
	 */
	do_action( 'llms_after_favorite_button', $lesson, $student );
	?>

</div>
