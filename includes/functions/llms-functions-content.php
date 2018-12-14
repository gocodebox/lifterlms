<?php
/**
 * (Post) Content functions
 *
 * @package   LifterLMS/Functions/Content
 * @since     3.25.1
 * @version   3.25.2
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'llms_get_post_content' ) ) {

	/**
	 * Post Template Include
	 *
	 * Appends LLMS content above and below post content.
	 *
	 * @param    string  $content  WP_Post post_content.
	 * @return   string
	 * @since    1.0.0
	 * @version  3.25.2
	 */
	function llms_get_post_content( $content ) {

		global $post;
		if ( ! $post instanceof WP_Post ) {
			return $content;
		}

		$page_restricted = llms_page_restricted( $post->ID );
		$before          = '';
		$template_before = '';
		$after           = '';
		$template_after  = '';

		if ( 'course' === $post->post_type || 'llms_membership' === $post->post_type ) {

			$sales_page = get_post_meta( $post->ID, '_llms_sales_page_content_type', true );

			if ( $page_restricted['is_restricted'] && ( '' === $sales_page || 'content' === $sales_page ) ) {

				add_filter( 'the_excerpt', array( $GLOBALS['wp_embed'], 'autoembed' ), 9 );
				if ( $post->post_excerpt ) {
					$content = llms_get_excerpt( $post->ID );
				}
			}

			$template_name   = str_replace( 'llms_', '', $post->post_type );
			$template_before = llms_get_template_part_contents( 'content', 'single-' . $template_name . '-before' );
			$template_after  = llms_get_template_part_contents( 'content', 'single-' . $template_name . '-after' );

		} elseif ( 'lesson' === $post->post_type ) {

			if ( $page_restricted['is_restricted'] ) {
				$content         = '';
				$template_before = llms_get_template_part_contents( 'content', 'no-access-before' );
				$template_after  = llms_get_template_part_contents( 'content', 'no-access-after' );
			} else {
				$template_before = llms_get_template_part_contents( 'content', 'single-lesson-before' );
				$template_after  = llms_get_template_part_contents( 'content', 'single-lesson-after' );
			}
		} elseif ( 'llms_quiz' === $post->post_type ) {

			$template_before = llms_get_template_part_contents( 'content', 'single-quiz-before' );
			$template_after  = llms_get_template_part_contents( 'content', 'single-quiz-after' );

		}

		if ( $template_before ) {
			ob_start();
			load_template( $template_before, false );
			$before = ob_get_clean();
		}

		if ( $template_after ) {
			ob_start();
			load_template( $template_after, false );
			$after = ob_get_clean();
		}

		return apply_filters( 'llms_get_post_content', do_shortcode( $before . $content . $after ), $post, $page_restricted );

	}
}// End if().
add_filter( 'the_content', 'llms_get_post_content' );
