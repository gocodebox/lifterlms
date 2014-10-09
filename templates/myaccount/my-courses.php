<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

$my_orders = get_posts( 
	array(
		'meta_key'    => '_llms_user_id',
		'meta_value'  => get_current_user_id(),
		'post_type'   => 'order',
		'post_status' => 'publish'
	)
);

if ( $my_orders) { 
?>

<div class="llms-my-courses">
<?php echo  '<h3>' .__( 'In-Progress', 'lifterlms' ) . '</h3>'; ?> 
	<ul class="listing-courses">

	<?php
	foreach ( $my_orders as $order ) {

		$order_info = get_post_meta($order->ID);
		
		$course_id = $order_info['_llms_order_product_id'];
		$course = get_post($course_id[0]);

		$author = get_the_author();
		$permalink = get_post_permalink( $course->ID);

		$user = new LLMS_Person;
		$user_postmetas = $user->get_user_postmeta_data( get_current_user_id(), $course->ID );

		$date_formatted = date('M d, Y', 
			strtotime($user_postmetas['_start_date']->updated_date) );

		$course_status = $user_postmetas['_status']->meta_value;

		$course_progress = $user_postmetas['_progress']->meta_value;
		?>

		<li class="course-item">
		    <article class="course">
			    <section class="info">
				    <div class="course-image effect">
				    <?php
				    //course thumbnail
					if ( has_post_thumbnail( $course->ID ) ) {
						echo '<a href="' . get_permalink( $course->ID ) . '" title="' . esc_attr( $course->post_title ) . '">';
						echo get_the_post_thumbnail( $course->ID, 'thumbnail' );
						echo '</a>';
					}
					?>
					</div>
				         
					<hgroup>
					<span class="llms-sts-enrollment">
					    <span class="llms-sts-label">Status: </span>
					    <span class="llms-sts-current"><?php echo $course_status ?></span>
					</span>
					<p class="llms-start-date">Course Started - <?php echo $date_formatted ?></p>
					<h3>
					<?php
					   echo '<a href="' . $permalink  . '">' . $course->post_title . '</a>'
					?>
					</h3>
					<?php printf( __( '<p class="author">Author: <span>%s</span></p>' ), $author ); ?>
					</hgroup>
				</section>

				<div class="clear"></div>
				
				<section class="progress">

				<div class="llms-progress">
					<div class="progress__indicator"><?php echo $course_progress ?>%</div>
						<div class="progress-bar">
						    <div class="progress-bar-complete" style="width:<?php echo $course_progress ?>%"></div>
						</div>
					</div>

					<div class="course-message">
				      <p class="message-text">Your current progress:
				      	<span class="grade-value"><?php echo $course_progress ?>%</span>.
				        	Progress required for a Certificate: <span class="grade-value">
				        	80%</span>.
				      </p>
				</div>

				<div class="course-link">
			 		<?php echo '<a href="' . $permalink  . '" class="button">' . __( 'View Course', 'lifterlms' ) . '</a>'; ?>
			 	</div>

			  	<div class="clear"></div>
			</article>
		</li>

	<?php }; ?>

	</ul>
</div>

<?php } ?>
