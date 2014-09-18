<?php
/**
 * The Template for displaying all single courses.
 *
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit;

?>

<?php

	 //do_action( 'lifterlms_before_single_course' );

	 // if ( post_password_required() ) {
	 // 	echo get_the_password_form();
	 // 	return;
	 // }

?>
<div id="course-<?php the_ID(); ?>" <?php post_class(); ?>>
<?php

	//do_action( 'lifterlms_before_single_course_summary' );

?>

<div class="summary entry-summary">

		<?php

			do_action( 'lifterlms_single_course_summary' );

		?>

	</div>

	<?php
		
		//do_action( 'lifterlms_after_single_course_summary' );

	?>

	<meta itemprop="url" content="<?php the_permalink(); ?>" />

</div>

<?php //do_action( 'lifterlms_after_single_course' ); ?>
</div>


