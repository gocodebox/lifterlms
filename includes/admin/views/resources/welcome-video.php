<?php
/**
 * Welcome video meta box HTML.
 *
 * @package LifterLMS/Admin/Views/Resources
 *
 * @since 7.4.1
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="llms-welcome-video">
	<p><?php esc_html_e( 'Thank you for choosing LifterLMS! This page is your command center, where you can find all of the basic information you need to start building your courses. Use the links on this page to quickly access support or any additional documentation you may need along the way.', 'lifterlms' ); ?></p>
	<div class="llms-welcome-video-container">
		<iframe width="762" height="428" src="https://www.youtube.com/embed/SWJKl4hs99g" title="How to Build an LMS Website - Getting Started with LifterLMS" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
	</div>
</div>
