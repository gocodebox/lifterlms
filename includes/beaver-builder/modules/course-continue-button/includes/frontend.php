<?php
/**
 * LifterLMS Course Continue Button Module HTML.
 *
 * @package LifterLMS_Labs/Labs/BeaverBuilder/Modules/CourseContinueButton/Templates
 *
 * @since 1.3.0
 * @since 1.7.0 Escaped attributes.
 * @version 1.7.0
 *
 * @param $settings obj Beaver Builder module settings instance.
 */

defined( 'ABSPATH' ) || exit;

$course_id = ! empty( $settings->llms_course_id ) ? $settings->llms_course_id : get_the_ID();
?>

<div class="llms-lab-course-continue-button">
	[lifterlms_course_continue_button course_id="<?php echo esc_attr( $course_id ); ?>"]
</div>
