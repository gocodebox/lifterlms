<?php
/**
 * LifterLMS Course reviews
 *
 * This class handles the front end of the reviews. It is responsible
 * for outputting the HTML on the course page (if reviews are activated).
 *
 * @package LifterLMS/Classes
 *
 * @since 1.2.7
 * @version 7.1.3
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Reviews class
 *
 * @since 1.2.7
 */
class LLMS_Reviews {

	/**
	 * This is the constructor for this class.
	 *
	 * It takes care of attaching the functions in this file to the
	 * appropriate actions.
	 * These actions are:
	 * 1) Output after course info.
	 * 2) Output after membership info.
	 * 3 & 4) Add function call to the proper AJAX call.
	 *
	 * @since 3.1.3
	 *
	 * @return void
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
	 *
	 * @since 1.2.7
	 * @since 3.24.0 Unknown.
	 * @since 7.1.3 Improve inline styles, escape output.
	 *
	 * @return void
	 */
	public static function output() {

		/**
		 * Check to see if we are supposed to output the code at all.
		 */
		if ( get_post_meta( get_the_ID(), '_llms_display_reviews', true ) ) {

			/**
			 * Filters the reviews section title.
			 *
			 * @since 1.2.7
			 *
			 * @param string $section_title The section title.
			 */
			$section_title = apply_filters( 'lifterlms_reviews_section_title', __( 'What Others Have Said', 'lifterlms' ) );

			?>
			<div id="old_reviews">
				<h3><?php echo esc_html( $section_title ); ?></h3>
				<?php
				$args = array(
					'posts_per_page'   => get_post_meta( get_the_ID(), '_llms_num_reviews', true ),
					'post_type'        => 'llms_review',
					'post_status'      => 'publish',
					'post_parent'      => get_the_ID(),
					'suppress_filters' => true,
				);

				$posts_array = get_posts( $args );

				/**
				 * Allow review custom styles to be filtered.
				 *
				 * @since 1.2.7
				 *
				 * @param array $styles Array of custom styles.
				 */
				$styles = apply_filters(
					'llms_review_custom_styles',
					array(
						'background-color' => '#efefef',
						'title-color'      => 'inherit',
						'text-color'       => 'inherit',
						'custom-css'       => '',
					)
				);

				$inline_styles = '';

				if ( $styles['background-color'] ?? '' ) {
					$inline_styles .= '.llms_review{background-color:' . $styles['background-color'] . '}';
				}

				if ( $styles['title-color'] ?? '' ) {
					$inline_styles .= '.llms_review h5{color:' . $styles['title-color'] . '}';
				}

				if ( $styles['text-color'] ?? '' ) {
					$inline_styles .= '.llms_review h6,.llms_review p{color:' . $styles['text-color'] . '}';
				}

				if ( $styles['custom-css'] ?? '' ) {

					// Remove style tags in case they were added with the filter.
					$inline_styles .= str_replace( array( '<style>', '</style>' ), '', $styles['custom-css'] );
				}

				if ( $inline_styles ) {
					echo '<style id="llms_review_custom_styles">' . esc_html( $inline_styles ) . '</style>';
				}

				foreach ( $posts_array as $post ) {
					?>
					<div class="llms_review">
						<h5><strong><?php echo esc_html( get_the_title( $post->ID ) ); ?></strong></h5>
						<h6>
							<?php
							// Translators: %s = The author display name.
							echo esc_html( sprintf( __( 'By: %s', 'lifterlms' ), get_the_author_meta( 'display_name', get_post_field( 'post_author', $post->ID ) ) ) );
							?>
						</h6>
						<p><?php echo esc_html( get_post_field( 'post_content', $post->ID ) ); ?></p>
					</div>
					<?php
				}
				?>
				<hr>
			</div>
			<?php
		}

		/**
		 * Check to see if reviews are open.
		 */
		if ( get_post_meta( get_the_ID(), '_llms_reviews_enabled', true ) && is_user_logged_in() ) {

			/**
			 * Filters the thank you text.
			 *
			 * @since 1.2.7
			 *
			 * @param string $thank_you_text The thank you text.
			 */
			$thank_you_text = apply_filters( 'llms_review_thank_you_text', __( 'Thank you for your review!', 'lifterlms' ) );

			if ( ! self::current_user_can_write_review( get_the_ID() ) ) {
				?>
				<div id="thank_you_box">
					<h2><?php echo esc_html( $thank_you_text ); ?></h2>
				</div>
				<?php
			} else {
				?>
				<div class="review_box" id="review_box">
					<h3><?php esc_html_e( 'Write a Review', 'lifterlms' ); ?></h3>
					<!--<form method="post" name="review_form" id="review_form">-->
					<input type="text" name="review_title" placeholder="<?php esc_attr_e( 'Review Title', 'lifterlms' ); ?>" id="review_title">
					<h5 id="review_title_error"><?php esc_html_e( 'Review Title is required.', 'lifterlms' ); ?></h5>
					<textarea name="review_text" placeholder="<?php esc_attr_e( 'Review Text', 'lifterlms' ); ?>" id="review_text"></textarea>
					<h5 id="review_text_error"><?php esc_html_e( 'Review Text is required.', 'lifterlms' ); ?></h5>
					<input name="action" value="submit_review" type="hidden">
					<input name="post_ID" value="<?php echo esc_attr( get_the_ID() ); ?>" type="hidden" id="post_ID">
					<?php wp_nonce_field( 'llms-review', 'llms_review_nonce' ); ?>
					<input type="submit" class="button" value="<?php esc_attr_e( 'Leave Review', 'lifterlms' ); ?>" id="llms_review_submit_button">
					<!--</form>	-->
				</div>
				<div class="thank_you_box" id="thank_you_box">
					<h2><?php echo esc_html( $thank_you_text ); ?></h2>
				</div>
				<?php
			}
		}
	}

	/**
	 * This function adds the review to the database. It is
	 * called by the AJAX handler when the submit review button
	 * is pressed. This function gathers the data from $_POST and
	 * then adds the review with the appropriate content.
	 *
	 * @since 1.2.7
	 * @since 5.9.0 Stop using deprecated `FILTER_SANITIZE_STRING`.
	 * @since 7.5.2 Now checking if the user can write a review.
	 *
	 * @return void
	 */
	public function process_review() {
		// Check the nonce.
		if ( ! isset( $_POST['llms_review_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['llms_review_nonce'] ), 'llms-review' ) ) {
			return;
		}

		$parent_id = llms_filter_input_sanitize_string( INPUT_POST, 'pageID' );

		// Make sure the current user can write reviews yet.
		if ( ! self::current_user_can_write_review( $parent_id ) ) {
			return;
		}

		$post = array(
			'post_content' => llms_filter_input_sanitize_string( INPUT_POST, 'review_text' ), // The full text of the post.
			'post_name'    => llms_filter_input_sanitize_string( INPUT_POST, 'review_title' ), // The name (slug) for your post.
			'post_title'   => llms_filter_input_sanitize_string( INPUT_POST, 'review_title' ), // The title of your post.
			'post_status'  => 'publish',
			'post_type'    => 'llms_review',
			'post_parent'  => $parent_id, // Sets the parent of the new post, if any. Default 0.
			'post_excerpt' => llms_filter_input_sanitize_string( INPUT_POST, 'review_title' ),
		);

		$result = wp_insert_post( $post, true );
	}

	/**
	 * Check to see if we are allowed to write more than one review.
	 * If we are not, check to see if we have written a review already.
	 *
	 * @since 7.5.2
	 *
	 * @param int $parent_id The ID of the parent post.
	 * @return bool True if the user can write a review, false if not.
	 */
	public static function current_user_can_write_review( $parent_id ) {
		// Make sure the user is logged in.
		if ( ! is_user_logged_in() ) {
			return false;
		}

		// Make sure we have a post ID to check.
		if ( empty( $parent_id ) ) {
			return false;
		}

		// Check if reviews are disabled.
		if ( ! get_post_meta( $parent_id, '_llms_reviews_enabled', true ) ) {
			return false;
		}

		// Check if reviews are limited and the user has already written a review.
		$args        = array(
			'posts_per_page'   => 1,
			'post_type'        => 'llms_review',
			'post_status'      => 'publish',
			'post_parent'      => $parent_id,
			'author'           => get_current_user_id(),
			'suppress_filters' => true,
		);
		$posts_array = get_posts( $args );
		if ( get_post_meta( $parent_id, '_llms_multiple_reviews_disabled', true ) && $posts_array ) {
			return false;
		}

		// If we got here, we can write a review.
		return true;
	}
}

return new LLMS_Reviews();
