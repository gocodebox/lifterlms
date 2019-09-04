<?php
/**
 * @author      LifterLMS
 * @package     LifterLMS/Templates
 */

defined( 'ABSPATH' ) || exit;

global $post;

$course = new LLMS_Course( $post );

if ( ! $course->get_video() ) {
	return; }

?>

<div class="llms-video-wrapper">
	<div class="center-video">
		<?php echo $course->get_video(); ?>
	</div>
</div>
