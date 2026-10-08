<?php
/**
 * Lesson Minimum Time Timer Display
 *
 * @package LifterLMS/Templates
 *
 * @since 10.1.0
 * @version 10.1.0
 *
 * @var string $display_text The formatted timer text.
 * @var int    $required     Required time in seconds (0 if no minimum).
 * @var int    $accumulated  Accumulated time in seconds.
 * @var bool   $met          Whether the time requirement has been met.
 */

defined( 'ABSPATH' ) || exit;
?>
<div id="llms-lesson-timer" class="llms-lesson-timer">
	<span id="llms-lesson-timer-display"<?php echo $met ? ' class="llms-time-met"' : ''; ?>><?php echo esc_html( $display_text ); ?></span>
</div>
