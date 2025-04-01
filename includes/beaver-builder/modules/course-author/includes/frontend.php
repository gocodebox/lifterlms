<?php
/**
 * LifterLMS Course Author Module HTML.
 *
 * @package LifterLMS_Labs/Labs/BeaverBuilder/Modules/CourseAuthor/Templates
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

<div class="llms-lab-course-author">
	[lifterlms_course_author avatar_size="<?php echo esc_attr( $settings->llms_avatar_size ); ?>" bio="<?php echo esc_attr( $settings->llms_show_bio ); ?>" course_id="<?php echo esc_attr( $course_id ); ?>"]
</div>
