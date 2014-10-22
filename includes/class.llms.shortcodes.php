<?php

/**
* Shortcode base class
*
* Shortcode logic
*
* @version 1.0
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Shortcodes {

	/**
	* init shortcodes array
	*
	* @return void
	*/
	public static function init() {

		$shortcodes = array(
			'lifterlms_my_account' => __CLASS__ . '::my_account',
			'lifterlms_checkout' => __CLASS__ . '::checkout',
			'courses' => __CLASS__ . '::courses',
		);

		foreach ( $shortcodes as $shortcode => $function ) {

			add_shortcode( apply_filters( "{$shortcode}_shortcode_tag", $shortcode ), $function );

		}

	}

	/**
	* Creates a wrapper for shortcode.
	*
	* @return void
	*/
	public static function shortcode_wrapper(

		$function,
		$atts    = array(),
		$wrapper = array(
			'class'  => 'lifterlms',
			'before' => null,
			'after'  => null
	) ) {

		ob_start();

		$before 	= empty( $wrapper['before'] ) ? '<div class="' . esc_attr( $wrapper['class'] ) . '">' : $wrapper['before'];
		$after 		= empty( $wrapper['after'] ) ? '</div>' : $wrapper['after'];

		echo $before;
		call_user_func( $function, $atts );
		echo $after;

		return ob_get_clean();
	}

	/**
	* My account shortcode.
	*
	* Used for displaying account.
	*
	* @return self::shortcode_wrapper
	*/
	public static function my_account( $atts ) {

		return self::shortcode_wrapper( array( 'LLMS_Shortcode_My_Account', 'output' ), $atts );

	}

	/**
	* Checkout shortcode.
	*
	* Used for displaying checkout form
	*
	* @return self::shortcode_wrapper
	*/
	public static function checkout( $atts ) {

		return self::shortcode_wrapper( array( 'LLMS_Shortcode_Checkout', 'output' ), $atts );

	}

	/**
	* courses shortcode
	*
	* Used for courses [courses]
	*
	* @return array
	*/
	public static function courses( $atts ) {

	    ob_start();

	    $query = new WP_Query( array(
	        'post_type' => 'course',
	        'post_status' => 'publish',
	        'posts_per_page' => isset($atts['per_page']) ? $atts['per_page'] : -1,
	        'order' => isset($atts['order']) ? $atts['order'] : 'ASC',
	        'orderby' => isset($atts['orderby']) ? $atts['orderby'] : 'title',
	    ) );

	    if ( $query->have_posts() ) { ?>

	       <?php lifterlms_course_loop_start(); ?>

					<?php while ( $query->have_posts() ) : $query->the_post(); ?>

						<?php llms_get_template_part( 'content', 'course' ); ?>

					<?php endwhile; ?>

				<?php lifterlms_course_loop_end(); ?>

	    <?php $courses = ob_get_clean();
	    wp_reset_postdata();
	    return $courses;
	    }
	    wp_reset_postdata();

	}


}

