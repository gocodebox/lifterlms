<?php
/**
 * My Favorites
 *
 * @package LifterLMS/Templates/MyAccount
 *
 * @since 7.5.0
 * @version 7.5.0
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="llms-sd-section llms-my-favorites">
	<?php
	// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in templates.
	echo $content;
	?>
</div>

