<?php
/**
 * Create a focus-mode course whose lesson contains a core/tabs block.
 *
 * Idempotent. Run from tests/e2e/bin/setup-env.sh via `wp eval-file`.
 *
 * @package LifterLMS/Tests/E2E
 *
 * @since [version]
 */

defined( 'ABSPATH' ) || exit;

$tabs_content = <<<'HTML'
<!-- wp:tabs -->
<div class="wp-block-tabs"><!-- wp:tab-list -->
<div class="wp-block-tab-list" role="tablist"><button type="button" role="tab">Overview</button><button type="button" role="tab">Details</button></div>
<!-- /wp:tab-list -->

<!-- wp:tab-panels -->
<div class="wp-block-tab-panels"><!-- wp:tab-panel {"anchor":"overview","label":"Overview"} -->
<section class="wp-block-tab-panel" id="overview" role="tabpanel" tabindex="0"><!-- wp:paragraph -->
<p>Overview tab content.</p>
<!-- /wp:paragraph --></section>
<!-- /wp:tab-panel -->

<!-- wp:tab-panel {"anchor":"details","label":"Details"} -->
<section class="wp-block-tab-panel" id="details" role="tabpanel" tabindex="0"><!-- wp:paragraph -->
<p>Details tab content.</p>
<!-- /wp:paragraph --></section>
<!-- /wp:tab-panel --></div>
<!-- /wp:tab-panels --></div>
<!-- /wp:tabs -->
HTML;

$course_query = new WP_Query(
	array(
		'name'           => 'focus-mode-tabs-course',
		'post_type'      => 'course',
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'no_found_rows'  => true,
	)
);
$course_id    = $course_query->posts ? (int) $course_query->posts[0] : 0;
if ( ! $course_id ) {
	$course_id = wp_insert_post(
		array(
			'post_type'    => 'course',
			'post_title'   => 'Focus Mode Tabs Course',
			'post_name'    => 'focus-mode-tabs-course',
			'post_status'  => 'publish',
			'post_content' => 'Focus mode course used to test the core Tabs block.',
		)
	);
}

update_post_meta( $course_id, '_llms_focus_mode', 'enable' );

$section_query = new WP_Query(
	array(
		'name'           => 'focus-mode-tabs-section',
		'post_type'      => 'section',
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'no_found_rows'  => true,
	)
);
$section_id    = $section_query->posts ? (int) $section_query->posts[0] : 0;
if ( ! $section_id ) {
	$section_id = wp_insert_post(
		array(
			'post_type'   => 'section',
			'post_title'  => 'Focus Mode Tabs Section',
			'post_name'   => 'focus-mode-tabs-section',
			'post_status' => 'publish',
		)
	);
}
update_post_meta( $section_id, '_llms_parent_course', $course_id );
update_post_meta( $section_id, '_llms_order', 1 );

$lesson_query = new WP_Query(
	array(
		'name'           => 'focus-mode-tabs-lesson',
		'post_type'      => 'lesson',
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'no_found_rows'  => true,
	)
);
$lesson_id    = $lesson_query->posts ? (int) $lesson_query->posts[0] : 0;
if ( ! $lesson_id ) {
	$lesson_id = wp_insert_post(
		array(
			'post_type'    => 'lesson',
			'post_title'   => 'Focus Mode Tabs Lesson',
			'post_name'    => 'focus-mode-tabs-lesson',
			'post_status'  => 'publish',
			'post_content' => $tabs_content,
		)
	);
} else {
	wp_update_post(
		array(
			'ID'           => $lesson_id,
			'post_content' => $tabs_content,
		)
	);
}
update_post_meta( $lesson_id, '_llms_parent_course', $course_id );
update_post_meta( $lesson_id, '_llms_parent_section', $section_id );
update_post_meta( $lesson_id, '_llms_order', 1 );

$plan_query = new WP_Query(
	array(
		'name'           => 'focus-mode-tabs-plan',
		'post_type'      => 'llms_access_plan',
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'no_found_rows'  => true,
	)
);
$plan_id    = $plan_query->posts ? (int) $plan_query->posts[0] : 0;
if ( ! $plan_id ) {
	$plan_id = wp_insert_post(
		array(
			'post_type'   => 'llms_access_plan',
			'post_title'  => 'Free Access',
			'post_name'   => 'focus-mode-tabs-plan',
			'post_status' => 'publish',
		)
	);
}
update_post_meta( $plan_id, '_llms_product_id', $course_id );
update_post_meta( $plan_id, '_llms_is_free', 'yes' );
update_post_meta( $plan_id, '_llms_price', 0 );
update_post_meta( $plan_id, '_llms_frequency', 0 );
update_post_meta( $plan_id, '_llms_availability', 'open' );
update_post_meta( $plan_id, '_llms_access_expiration', 'lifetime' );
update_post_meta( $plan_id, '_llms_enroll_text', 'Enroll' );

$student = get_user_by( 'login', 'validcreds' );
if ( $student ) {
	llms_enroll_student( $student->ID, $course_id, 'e2e_setup' );
}
