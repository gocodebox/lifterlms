<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
$person = new LLMS_Person;
$my_courses = $person->get_user_postmetas_by_key( get_current_user_id(), '_status' );
LLMS_log($my_courses);

$my_orders = get_posts(
	array(
		'meta_key'    => '_llms_user_id',
		'meta_value'  => get_current_user_id(),
		'post_type'   => 'order',
		'post_status' => 'publish'
	)
);

?>

<div class="llms-my-courses">
<?php echo  '<h3>' .__( 'Courses In-Progress', 'lifterlms' ) . '</h3>'; 
	if ( $my_orders) {?>
	<ul class="listing-courses">

	<?php
	foreach ( $my_courses as $course_item ) {
		if ( $course_item->meta_value == 'Enrolled' ) {

			//$course_data = get_post($course->post_id);
			//$course_meta = get_post_meta($course->post_id);
			$course = new LLMS_Course_Basic( $course_item->post_id );
			//$course_id = $order_info['_llms_order_product_id'];

			//$course_object = new LLMS_Course_Basic( $course_id[0] );
			$course_progress = $course->get_percent_complete();
			$author = get_the_author();
			$permalink = get_post_permalink( $course->id );

			//$user = new LLMS_Person;
			//$user_postmetas = $user->get_user_postmeta_data( get_current_user_id(), $course->ID );

			$date_formatted = date('M d, Y', strtotime($course_item->updated_date) );
			$course_status = $course_item->meta_value;

			if (get_option('lifterlms_course_display_author') == 'yes') {
				$course_author = sprintf( __( '<p class="author">Author: <span>%s</span></p>' ), $author ); 
			}

			?>

			<li class="course-item">
			    <article class="course">
				    <section class="info">
					    <div class="course-image llms-image-thumb effect">
					    <?php
					   // lifterlms_template_single_featured_image();
					    //get_the_post_thumbnail( $course->id );
					    //the_post_thumbnail();
					 //    if ( has_post_thumbnail() ) {
						// 	the_post_thumbnail();
						// } 
		LLMS_log('about to look for an image');
		LLMS_log(llms_placeholder_img_src());
		 LLMS_log(get_the_post_thumbnail( $course->id));
						// if ( has_post_thumbnail( $course->id ) ) {
						// 	LLMS_log('post has thumbnial');
						// 	echo '<a href="' . get_permalink( $course->id ) . '" title="' . esc_attr( $course->post->post_title ) . '">';
						// 	echo get_the_post_thumbnail( $course->id );
						// 	echo '</a>';
						// }
						//else {
							echo lifterlms_get_featured_image( $course->id );
						//}
					//	}
						//lifterlms_get_featured_image( $course->id );
						?>
						</div>

						<hgroup>
						<span class="llms-sts-enrollment">
						    <span class="llms-sts-label">Status: </span>
						    <span class="llms-sts-current"><?php echo $course_status ?></span>
						</span>
						<p class="llms-start-date">Course Started - <?php echo $date_formatted ?></p>
						<h3>
						<?php echo '<a href="' . $permalink  . '">' . $course->post->post_title . '</a>' ?>
						</h3>
						<?php echo $course_author ?>
						</hgroup>
					</section>

					<div class="clear"></div>

					<section class="progress">

					<div class="llms-progress">
						<div class="progress__indicator"><?php printf( __( '%s%%', 'lifterlms' ), $course_progress ); ?></div>
							<div class="progress-bar">
							    <div class="progress-bar-complete" style="width:<?php echo $course_progress ?>%"></div>
							</div>
						</div>

					<div class="course-link">
				 		<?php echo '<a href="' . $permalink  . '" class="button llms-button">' . __( 'View Course', 'lifterlms' ) . '</a>'; ?>
				 	</div>

				  	<div class="clear"></div>
				</article>
			</li>

	<?php } }; ?>

	</ul>
	<?php 
	}
	else {
		echo  '<p>' .__( 'You are not enrolled in any courses.', 'lifterlms' ) . '</p>'; 
	}
	?>
</div>


