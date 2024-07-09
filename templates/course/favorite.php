<?php
/**
 * Template for favorite button.
 *
 * @author LifterLMS
 * @package LifterLMS/Templates
 *
 * @since 7.5.0
 *
 * @param int    $object_id   WP Post ID of the object to mark/unmark as favorite.
 * @param string $object_type The object type, currently only 'lesson'.
 */
defined( 'ABSPATH' ) || exit;

$lesson = null;
if ( 'lesson' === $object_type ) {
	global $post;
	$lesson = llms_get_post( empty( $object_id ) ? $post : $object_id );
}

if ( ! $lesson || ! is_a( $lesson, 'LLMS_Lesson' ) ) {
	return;
}

$total_favorites   = llms_get_object_total_favorites( $lesson->get( 'id' ) );
$student           = llms_get_student( get_current_user_id() );
$is_favorite       = $student && $student->is_favorite( $lesson->get( 'id' ), 'lesson' );
$can_mark_favorite = $lesson && ( ( $student && $student->is_enrolled( $lesson->get( 'id' ) ) ) || $lesson->is_free() );
?>

<div class="llms-favorite-wrapper">

	<?php
	/**
	 * Action fired before Favorite Button Hook.
	 *
	 * @since 7.5.0
	 *
	 * @param LLMS_Lesson  $lesson  Lesson object.
	 * @param LLMS_Student $student Student object.
	 */
	do_action( 'llms_before_favorite_button', $lesson, $student );
	?>

	<?php if ( ! is_user_logged_in() || ( ! $can_mark_favorite && ! $is_favorite ) ) { ?>

		<i class="fa fa-heart-o llms-favorite-btn llms-heart-btn"></i>

	<?php } else { ?>

		<?php if ( $is_favorite ) : ?>

			<i title="<?php esc_attr_e( 'Remove favorite for this lesson', 'lifterlms' ); ?>" data-action="unfavorite" data-type="lesson" data-id="<?php echo esc_attr( $lesson->get( 'id' ) ); ?>" class="fa fa-heart llms-unfavorite-btn llms-heart-btn"></i>

		<?php else : ?>

			<i title="<?php esc_attr_e( 'Add favorite for this lesson', 'lifterlms' ); ?>" data-action="favorite" data-type="lesson" data-id="<?php echo esc_attr( $lesson->get( 'id' ) ); ?>" class="fa fa-heart-o llms-favorite-btn llms-heart-btn"></i>

		<?php endif; ?>

	<?php } ?>

	<span class="llms-favorites-count" aria-label="<?php esc_attr_e( 'Total favorites for this lesson', 'lifterlms' ); ?>">
		<?php echo esc_html( $total_favorites ); ?>
	</span>

	<?php
	/**
	 * Action fired after Favorite Button Hook.
	 *
	 * @since 7.5.0
	 *
	 * @param LLMS_Lesson  $lesson  Lesson object.
	 * @param LLMS_Student $student Student object.
	 */
	do_action( 'llms_after_favorite_button', $lesson, $student );
	?>

</div>
