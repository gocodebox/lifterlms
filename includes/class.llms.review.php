<?php
defined( 'ABSPATH' ) || exit;

/**
 * This class handles the front end of the reviews. It is responsible
 * for outputting the HTML on the course page (if reviews are activated)
 * @since    ??
 * @version  3.24.0
 */
class LLMS_Reviews {
	/**
	 * This is the constructor for this class. It takes care of attaching
	 * the functions in this file to the appropriate actions. These actions are:
	 * 1) output after course info
	 * 2) output after membership info
	 * 3 & 4) Add function call to the proper AJAX call
	 *
	 * @return void
	 * @version  3.1.3
	 */
	public function __construct() {
		add_action( 'wp_ajax_LLMSSubmitReview', array( $this, 'process_review' ) );
		add_action( 'wp_ajax_nopriv_LLMSSubmitReview', array( $this, 'process_review' ) );
	}

	/**
	 * This function handles the HTML output of the reviews and review form.
	 * If the option is enabled, the review form will be output,
	 * if not, nothing will happen. This function also checks to
	 * see if a user is allowed to review more than once.
	 * @since    ??
	 * @version  3.24.0
	 */
	public static function output() {

		/**
		 * Check to see if we are supposed to output the code at all
		 */
		if ( get_post_meta( get_the_ID(),'_llms_display_reviews',true ) ) {
		?>
			<div id="old_reviews">
			<h3><?php echo apply_filters( 'lifterlms_reviews_section_title', __( 'What Others Have Said', 'lifterlms' ) ); ?></h3>
			<?php
			$args = array(
				'posts_per_page'   => get_post_meta( get_the_ID(),'_llms_num_reviews',true ),
				'post_type'        => 'llms_review',
				'post_status'      => 'publish',
				'post_parent'	   => get_the_ID(),
				'suppress_filters' => true,
			);
			$posts_array = get_posts( $args );

			$styles = array(
				'background-color' => '#EFEFEF',
				'title-color' => 'inherit',
				'text-color' => 'inherit',
				'custom-css' => '',
			);

			if ( has_filter( 'llms_review_custom_styles' ) ) {
				$styles = apply_filters( 'llms_review_custom_styles', $styles );
			}

			foreach ( $posts_array as $post ) {
				echo $styles['custom-css'];

				?>
				<div class="llms_review" style="margin:20px 0px; background-color:<?php echo $styles['background-color']; ?>; padding:10px">
					<h5 style="font-size:17px; color:<?php echo $styles['title-color']; ?>;" style="margin:3px 0px"><strong><?php echo get_the_title( $post->ID );?></strong></h5>
					<h6 style="font-size:13px; color:<?php echo $styles['text-color']; ?>;"><?php echo sprintf( __( 'By: %s', 'lifterlms' ), get_the_author_meta( 'display_name', get_post_field( 'post_author', $post->ID ) ) ); ?></h6>
					<p style="font-size:15px; color:<?php echo $styles['text-color']; ?>;"><?php echo get_post_field( 'post_content', $post->ID );?></p>
				</div>
				<?php
			}
			?>
			<hr>
			</div>
			<?php
		}// End if().

		/**
		 * Check to see if reviews are open
		 */
		if ( get_post_meta( get_the_ID(),'_llms_reviews_enabled',true ) && is_user_logged_in() ) {
			/**
			 * Look for previous reviews that we have written on this course.
			 * @var array
			 */
			$args = array(
				'posts_per_page'   => 1,
				'post_type'        => 'llms_review',
				'post_status'      => 'publish',
				'post_parent'	   => get_the_ID(),
				'author'		   => get_current_user_id(),
				'suppress_filters' => true,
			);
			$posts_array = get_posts( $args );

			/**
			 * Check to see if we are allowed to write more than one review.
			 * If we are not, check to see if we have written a review already.
			 */
			if ( get_post_meta( get_the_ID(),'_llms_multiple_reviews_disabled',true ) && $posts_array ) {
			?>
				<div id="thank_you_box">
					<h2><?php echo apply_filters( 'llms_review_thank_you_text', __( 'Thank you for your review!','lifterlms' ) ); ?></h2>
				</div>
				<?php
			} else {
				?>
				<div class="review_box" id="review_box">
				<h3><?php _e( 'Write a Review', 'lifterlms' ); ?></h3>
				<!--<form method="post" name="review_form" id="review_form">-->
					<input style="margin:10px 0px" type="text" name="review_title" placeholder="<?php _e( 'Review Title', 'lifterlms' ); ?>" id="review_title">
					<h5 style="color:red; display:none" id="review_title_error"><?php _e( 'Review Title is required.', 'lifterlms' ); ?></h5>
					<textarea name="review_text" placeholder="<?php _e( 'Review Text', 'lifterlms' ); ?>" id="review_text"></textarea>
					<h5 style="color:red; display:none" id="review_text_error"><?php _e( 'Review Text is required.', 'lifterlms' ); ?></h5>
					<?php wp_nonce_field( 'submit_review','submit_review_nonce_code' ); ?>
					<input name="action" value="submit_review" type="hidden">
					<input name="post_ID" value="<?php echo get_the_ID() ?>" type="hidden" id="post_ID">
					<input type="submit" class="button" value="<?php _e( 'Leave Review', 'lifterlms' ); ?>" id="llms_review_submit_button">
				<!--</form>	-->
				</div>
				<div id="thank_you_box" style="display:none;">
					<h2><?php echo apply_filters( 'llms_review_thank_you_text', __( 'Thank you for your review!','lifterlms' ) ); ?></h2>
				</div>
				<?php
			}
		}// End if().
	}

	/**
	 * This function adds the review to the database. It is
	 * called by the AJAX handler when the submit review button
	 * is pressed. This function gathers the data from $_POST and
	 * then adds the review with the appropriate content.
	 *
	 * @return void
	 */
	public function process_review() {

		$post = array(
		  'post_content'   => $_POST['review_text'], // The full text of the post.
		  'post_name'      => $_POST['review_title'], // The name (slug) for your post
		  'post_title'     => $_POST['review_title'], // The title of your post.
		  'post_status'    => 'publish',
		  'post_type'      => 'llms_review',
		  'post_parent'    => $_POST['pageID'], // Sets the parent of the new post, if any. Default 0.
		  'post_excerpt'   => $_POST['review_title'],
		);

		$result = wp_insert_post( $post, true );
	}
}

return new LLMS_Reviews;
