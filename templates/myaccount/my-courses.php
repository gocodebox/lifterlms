<?php
/**
 * My Courses List
 * Used in My Account and My Courses shortcodes
 *
 * @since    3.0.0
 * @version  3.6.0
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
global $wp_query;
?>

<div class="llms-sd-section llms-my-courses">
	<h3 class="llms-sd-section-title"><?php echo apply_filters( 'lifterlms_my_courses_title', __( 'Courses In-Progress', 'lifterlms' ) ); ?></h3>

	<?php if ( ! $courses['results'] ) : ?>
		<p><?php _e( 'You are not enrolled in any courses.', 'lifterlms' ); ?></p>
	<?php else : ?>
		<ul class="listing-courses">
			<?php foreach ( $courses['results'] as $c ) : $c = new LLMS_Course( $c ); ?>

				<li class="course-item">
				    <article class="course">
					    <section class="info">
						    <div class="course-image llms-image-thumb effect">
						    	<?php echo lifterlms_get_featured_image( $c->get_id() ); ?>
							</div>

							<div class="meta">
								<?php echo apply_filters( 'lifterlms_my_courses_enrollment_status_html',
									'<span class="llms-sts-enrollment">
										<span class="llms-sts-label">' . __( 'Status:','lifterlms' ) . '</span>
										<span class="llms-sts-current">' . llms_get_enrollment_status_name( $student->get_enrollment_status( $c->get_id() ) ) . '</span>
									</span>'
								); ?>

								<?php echo apply_filters('lifterlms_my_courses_start_date_html',
									'<p class="llms-start-date">' . __( 'Course Started','lifterlms' ) . ' - ' . $student->get_enrollment_date( $c->get_id(), 'enrolled' ) . '</p>'
								); ?>

								<h3><a href="<?php echo $c->get_permalink(); ?>"><?php echo $c->get_title(); ?></a></h3>

								<?php if ( 'yes' === get_option( 'lifterlms_course_display_author' ) ) : ?>
									<p class="author"><?php printf( __( 'Author: %s', 'lifterlms' ), '<span>' . $c->get_author_name() . '</span>' ); ?></p>
								<?php endif; ?>
							</div>
						</section>

						<div class="clear"></div>

						<div class="llms-progress">
							<?php $progress = $c->get_percent_complete( $student->get_id() ); ?>
							<div class="progress__indicator"><?php echo $progress; ?>%</div>
							<div class="llms-progress-bar">
							    <div class="progress-bar-complete" style="width:<?php echo $progress ?>%"></div>
							</div>
						</div>

						<div class="course-link">
							<a href="<?php echo $c->get_permalink(); ?>" class="button llms-button-primary"><?php echo apply_filters( 'lifterlms_my_courses_course_button_text', __( 'View Course', 'lifterlms' ) ); ?></a>
					 	</div>

					  	<div class="clear"></div>
					</article>
				</li>

			<?php endforeach; ?>
		</ul>

		<footer class="llms-sd-pagination llms-my-courses-pagination">
			<?php if ( isset( $wp_query->query_vars['view-courses'] ) ) : ?>
				<?php if ( $courses['skip'] > 0 ) : ?>
					<a class="llms-button-secondary" href="<?php echo esc_url( add_query_arg( array(
						'limit' => $courses['limit'],
						'skip' => $courses['skip'] - $courses['limit'],
					), llms_get_endpoint_url( 'view-courses', '', llms_get_page_url( 'myaccount' ) ) ) ); ?>">&lt; <?php _e( 'Back', 'lifterlms' ); ?></a>
				<?php endif; ?>

				<?php if ( $courses['more'] ) : ?>
					<a class="llms-button-secondary" href="<?php echo esc_url( add_query_arg( array(
						'limit' => $courses['limit'],
						'skip' => $courses['skip'] + $courses['limit'],
					), llms_get_endpoint_url( 'view-courses', '', llms_get_page_url( 'myaccount' ) ) ) ); ?>"><?php _e( 'Next', 'lifterlms' ); ?> &gt;</a>
				<?php endif; ?>
			<?php else : ?>

				<?php if ( count( $courses['results'] ) ) : ?>
					<a class="llms-button-text" href="<?php echo esc_url( llms_get_endpoint_url( 'view-courses', '', llms_get_page_url( 'myaccount' ) ) ); ?>"><?php _e( 'View All My Courses', 'lifterlms' ); ?></a>
				<?php endif; ?>

			<?php endif; ?>
		</footer>

	<?php endif; ?>
</div>
