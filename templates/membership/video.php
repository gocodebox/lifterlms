<?php
/**
 * @author      LifterLMS
 * @package     LifterLMS/Templates
 */

defined( 'ABSPATH' ) || exit;

global $post;

$membership = new LLMS_Membership( $post );

if ( ! $membership->get_video() ) {
	return; }

?>

<div class="llms-video-wrapper">
	<div class="center-video">
		<?php
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $membership->get_video();
		?>
	</div>
</div>
