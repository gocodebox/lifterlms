<?php
/**
 * LifterLMS Course Author Info
 * @since   3.0.0
 * @version [version]
 */
if ( ! defined( 'ABSPATH' ) ) { exit; } // End if().

$course = llms_get_post( get_the_ID() );
$instructors = $course->get_instructors( true );
if ( ! $instructors ) {
	return;
}
?>

<section class="llms-instructor-info">
	<h3 class="llms-meta-title"><?php echo _n( 'Course Instructor', 'Course Instructors', count( $instructors ), 'lifterlms' ); ?></h3>
	<div class="llms-instructors">
		<?php foreach ( array_reverse( $instructors ) as $instructor ) : ?>
			<?php echo llms_get_author( array(
				'avatar_size' => 100,
				'bio' => true,
				'label' => $instructor['label'],
				'user_id' => $instructor['id'],
			) ); ?>
		<?php endforeach ; ?>
	</div>
</section>
