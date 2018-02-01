<?php
/**
 * Single Quiz: Return to Lesson Link
 * @since    1.0.0
 * @version  3.16.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
global $post;

$quiz = llms_get_post( $post );
if ( ! $quiz ) {
	return;
}
?>

<div class="clear"></div>
<div class="llms-return">
	<a href="<?php echo esc_url( get_permalink( $quiz->get( 'lesson_id' ) ) ); ?>"><?php _e( 'Return to Lesson', 'lifterlms' ); ?></a>
</div>
