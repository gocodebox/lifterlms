<?php
/**
 * Membership description template.
 *
 * @package LifterLMS/Templates
 *
 * @since Unknown
 * @version 4.0.0
 */

defined( 'ABSPATH' ) || exit;

global $post;

?>
<div class="llms-full-description">

	<?php
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo apply_filters( 'the_content', apply_filters( 'lifterlms_full_description', do_shortcode( $post->post_content ) ) );
	?>

</div>
