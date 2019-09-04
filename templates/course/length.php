<?php
/**
 * LifterLMS Course Length Meta Info
 *
 * @author      LifterLMS
 * @package     LifterLMS/Templates
 */

defined( 'ABSPATH' ) || exit;

global $post;

$course = new LLMS_Course( $post );

if ( ! $course->get( 'length' ) ) {
	return; }
?>

<div class="llms-meta llms-course-length">
	<p><?php printf( __( 'Estimated Time: <span class="length">%s</span>', 'lifterlms' ), $course->get( 'length' ) ); ?></p>
</div>

