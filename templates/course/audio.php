<?php
/**
 * @author      codeBOX
 * @package     lifterLMS/Templates
 */

defined( 'ABSPATH' ) || exit;

global $post;

$course = new LLMS_Course( $post );

if ( ! $course->get_audio() ) {
	return; }
?>

<div class="llms-audio-wrapper">
	<div class="center-audio">
		<?php
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $course->get_audio();
		?>
	</div>
</div>
