<?php
/**
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) exit;

global $post, $course, $lesson;

$user = new LLMS_Person;
$user_postmetas = $user->get_user_postmeta_data( get_current_user_id(), $lesson->id );
?>
<div class="clear"></div>
<div class="llms-lesson-button-wrapper">
	<?php
	if ( isset($user_postmetas['_is_complete']) ) {
		if ( $user_postmetas['_is_complete']->meta_value === 'yes' ) {

			echo __( 'Lesson Complete', 'lifterlms' );
		}
	}

	else {

	?>
	<form method="POST" action="" name="mark_complete" enctype="multipart/form-data"> 
	 	<?php do_action( 'lifterlms_before_mark_complete_lesson' ); ?>

	 	<input type="hidden" name="mark-complete" value="<?php echo esc_attr( $post->ID ); ?>" />

	 	<input type="submit" class="button" name="mark_complete" value="<?php echo $lesson->single_mark_complete_text(); ?>" />
	 	<input type="hidden" name="action" value="mark_complete" />

	 	<?php wp_nonce_field( 'mark_complete' ); ?>
		<?php do_action( 'lifterlms_after_mark_complete_lesson'  ); ?>
	</form>

	<?php } ?>
</div>
