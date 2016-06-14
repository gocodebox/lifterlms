<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

$person = new LLMS_Person;
$my_courses = $person->get_user_postmetas_by_key( get_current_user_id(), '_status' );
?>

<div class="llms-my-courses">
<?php echo  '<h3>' . apply_filters( 'lifterlms_my_courses_title', __( 'Courses In-Progress', 'lifterlms' ) ) . '</h3>';
if ( $my_courses ) {?>
	<ul class="listing-courses">

	<?php
	foreach ( $my_courses as $course_item ) {
		if ( 'Enrolled' == $course_item->meta_value ) {
			$course = new LLMS_Course_Basic( $course_item->post_id );

			if ( is_object( $course->post ) && 'course' == $course->post->post_type ) {
				$course_progress = $course->get_percent_complete();
				$author = get_userdata( $course->post->post_author );
				$author_name = $author->first_name . ' ' . $author->last_name;

				$permalink = get_post_permalink( $course->id );

				$date_formatted = date_i18n( 'M d, Y', strtotime( $course_item->updated_date ) );
				$course_status = $course_item->meta_value;
				/**
				 * @todo  this data needs to be identified via keys rather than storing the string directly to the database
				 *        this is also, i think, the only string we store here currently...
				 */
				if ( 'Enrolled' === $course_status ) {
					$course_status = __( 'Enrolled', 'lifterlms' );
				}

				$course_author = '';
				if (get_option( 'lifterlms_course_display_author' ) == 'yes') {
					$course_author = sprintf( __( '<p class="author">Author: <span>%s</span></p>', 'lifterlms' ), $author_name );
				}
				?>

				<li class="course-item">
				    <article class="course">
					    <section class="info">
						    <div class="course-image llms-image-thumb effect">
						    	<?php echo lifterlms_get_featured_image( $course->id ); ?>
							</div>

							<hgroup>
							<?php echo apply_filters('lifterlms_my_courses_enrollment_status_html', '<span class="llms-sts-enrollment">
							    <span class="llms-sts-label">' . __( 'Status:','lifterlms' ) . '</span>
							    <span class="llms-sts-current">' . $course_status . '</span>
							</span>'); ?>


							<?php echo apply_filters('lifterlms_my_courses_start_date_html',
							'<p class="llms-start-date">' .  __( 'Course Started','lifterlms' ) . ' - ' . $date_formatted . '</p>'); ?>


							<h3>
							<?php echo '<a href="' . $permalink  . '">' . $course->post->post_title . '</a>' ?>
							</h3>
							<?php echo $course_author ?>
							</hgroup>
						</section>

						<div class="clear"></div>

						<div class="llms-progress">
							<div class="progress__indicator"><?php printf( __( '%s%%', 'lifterlms' ), $course_progress ); ?></div>
							<div class="llms-progress-bar">
							    <div class="progress-bar-complete" style="width:<?php echo $course_progress ?>%"></div>
							</div>
						</div>

						<div class="course-link">
					 		<?php echo '<a href="' . $permalink  . '" class="button llms-button">' . apply_filters( 'lifterlms_my_courses_course_button_text', __( 'View Course', 'lifterlms' ) ) . '</a>'; ?>
					 	</div>

					  	<div class="clear"></div>
					</article>
				</li>

	<?php
			}
		}
	}; ?>

	</ul>
	<?php
} else {
	echo  '<p>' .__( 'You are not enrolled in any courses.', 'lifterlms' ) . '</p>';
}
	?>
</div>


