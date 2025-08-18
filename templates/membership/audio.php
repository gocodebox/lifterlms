<?php
/**
 * @author      codeBOX
 * @package     lifterLMS/Templates
 */

defined( 'ABSPATH' ) || exit;

global $post;

$membership = new LLMS_Membership( $post );

if ( ! $membership->get_audio() ) {
	return; }
?>

<div class="llms-audio-wrapper">
	<div class="center-audio">
		<?php
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $membership->get_audio();
		?>
	</div>
</div>
