<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

$person = new LLMS_Person;
$my_courses = $person->get_user_postmetas_by_key( get_current_user_id(), '_status' );
?>

<div class="llms-my-courses">
<?php echo  '<h3>' .__( 'Courses In-Progress', 'lifterlms' ) . '</h3>'; 
	if ( $my_courses ) {?>
	<ul class="listing-courses">

	<?php
	foreach ( $my_courses as $course_item ) {
		if ( $course_item->meta_value == 'Enrolled' ) {
			$course = new LLMS_Course_Basic( $course_item->post_id );
	
			if ( is_object( $course->post ) && $course->post->post_type == 'course') {
				$course_progress = $course->get_percent_complete();
				$author = get_userdata( $course->post->post_author );
				$author_name = $author->first_name . ' ' . $author->last_name;

				$permalink = get_post_permalink( $course->id );

				$date_formatted = date('M d, Y', strtotime($course_item->updated_date) );
				$course_status = $course_item->meta_value;

				$course_author = '';
				if (get_option('lifterlms_course_display_author') == 'yes') {
					$course_author = sprintf( __( '<p class="author">Author: <span>%s</span></p>' ), $author_name ); 
				}
				?>

				<li class="course-item">
				    <article class="course">
					    <section class="info">
						    <div class="course-image llms-image-thumb effect">
						    	<?php echo lifterlms_get_featured_image( $course->id ); ?>
							</div>

							<hgroup>
							<span class="llms-sts-enrollment">
							    <span class="llms-sts-label"><?php _e('Status:','lifterlms'); ?></span>
							    <span class="llms-sts-current"><?php echo $course_status ?></span>
							</span>
							<p class="llms-start-date"><?php echo __('Course Started','lifterlms') . ' - ' . $date_formatted ?></p>
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
					 		<?php echo '<a href="' . $permalink  . '" class="button llms-button">' . __( 'View Course', 'lifterlms' ) . '</a>'; ?>
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
	}
	else {
		echo  '<p>' .__( 'You are not enrolled in any courses.', 'lifterlms' ) . '</p>'; 
	}
	?>
</div>


