<?php
/**
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

global $post, $lesson;

if ( ! $lesson ) {

	$lesson = new LLMS_Lesson( $post->ID );

}
if ( is_user_logged_in() && llms_is_user_enrolled( get_current_user_id(), $lesson->parent_course ) ) {
	$user = new LLMS_Person;
	$user_postmetas = $user->get_user_postmeta_data( get_current_user_id(), $lesson->id );

	//get associated quiz
	$associated_quiz = get_post_meta( $post->ID, '_llms_assigned_quiz', true );
	?>

	<div class="clear"></div>
	<div class="llms-lesson-button-wrapper">
		<?php
		if ( isset( $user_postmetas['_is_complete'] ) ) {
			if ( 'yes' === $user_postmetas['_is_complete']->meta_value ) {

				echo __( 'Lesson Complete', 'lifterlms' );
			}

		}

		if ( ! isset( $user_postmetas['_is_complete'] ) && ! $associated_quiz ) {

		?>
		<form method="POST" action="" name="mark_complete" enctype="multipart/form-data">
		 	<?php do_action( 'lifterlms_before_mark_complete_lesson' ); ?>

		 	<input type="hidden" name="mark-complete" value="<?php echo esc_attr( $post->ID ); ?>" />

		 	<input type="submit" class="button" name="mark_complete" value="<?php echo $lesson->single_mark_complete_text(); ?>" />
		 	<input type="hidden" name="action" value="mark_complete" />

		 	<?php wp_nonce_field( 'mark_complete' ); ?>
			<?php do_action( 'lifterlms_after_mark_complete_lesson' ); ?>
		</form>

		<?php }

		if ($associated_quiz) {
		?>

		<form method="POST" action="" name="take_quiz" enctype="multipart/form-data">

		 	<input type="hidden" name="associated_lesson" value="<?php echo esc_attr( $post->ID ); ?>" />
		 	<input type="hidden" name="quiz_id" value="<?php echo esc_attr( $associated_quiz ); ?>" />
		 	<input type="submit" class="button" name="take_quiz" value="<?php _e( 'Take Quiz', 'lifterlms' ); ?>" />
		 	<input type="hidden" name="action" value="take_quiz" />

		 	<?php wp_nonce_field( 'take_quiz' ); ?>
		</form>

		<?php } ?>

	</div>
<?php } ?>
