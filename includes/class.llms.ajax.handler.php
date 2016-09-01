<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
* lifterLMS AJAX Event Handler
*
* Handles server side ajax communication.
*
* @author codeBOX
* @project lifterLMS
*/
class LLMS_AJAX_Handler {

	/**
	 * Move a Product Access Plan to the trash
	 * @since  3.0.0
	 * @version  3.0.0
	 * @param  array $request $_REQUEST object
	 * @return mixed      WP_Error on error, true if successful
	 */
	public static function delete_access_plan( $request ) {

		// shouldn't be possible
		if ( empty( $request['plan_id'] ) ) {

			die();

		}

		if ( ! wp_trash_post( $request['plan_id'] ) ) {

			$err = new WP_Error();
			$err->add( 'error', __( 'There was a problem deleting your access plan, please try again.', 'lifterlms' ) );
			return $err;

		}

		return true;

	}

	/**
	 * Remove a coupon from an order during checkout
	 * @return  string/json
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public static function remove_coupon_code( $request ) {

		LLMS()->session->set( 'llms_coupon', false );

		$plan = new LLMS_Access_Plan( $request['plan_id'] );

		ob_start();
		llms_get_template( 'checkout/form-coupon.php' );
		$coupon_html = ob_get_clean();

		ob_start();
		llms_get_template( 'checkout/form-gateways.php', array(
			'coupon' => false,
			'gateways' => LLMS()->payment_gateways()->get_enabled_payment_gateways(),
			'selected_gateway' => LLMS()->payment_gateways()->get_default_gateway(),
			'plan' => $plan,
		) );
		$gateways_html = ob_get_clean();

		ob_start();
		llms_get_template( 'checkout/form-summary.php', array(
			'coupon' => false,
			'plan' => $plan,
			'product' => $plan->get_product(),
		) );
		$summary_html = ob_get_clean();

		return array(
			'coupon_html' => $coupon_html,
			'gateways_html' => $gateways_html,
			'summary_html' => $summary_html,
		);

	}

	/**
	 * Handle Select2 Search boxes for WordPress Posts by Post Type
	 * @since 3.0.0
	 * @version 3.0.0
	 * @return  string/json
	 */
	public static function select2_query_posts() {

		// grab the search term if it exists
		$term = array_key_exists( 'term', $_REQUEST ) ? $_REQUEST['term'] : '';

		$page = array_key_exists( 'page', $_REQUEST ) ? $_REQUEST['page'] : 0;

		global $wpdb;

		$limit = 30;
		$start = $limit * $page;

		if ( $term ) {
			$like = " AND post_title LIKE '%s'";
			$vars = array( $_REQUEST['post_type'], '%' . $term . '%', $start, $limit );
		} else {
			$like = '';
			$vars = array( $_REQUEST['post_type'], $start, $limit );
		}

		$posts = $wpdb->get_results( $wpdb->prepare(
			"SELECT ID, post_title
			 FROM $wpdb->posts
			 WHERE
			 	    post_type = %s
			 	AND post_status = 'publish'
			 	$like
			 ORDER BY post_title
			 LIMIT %d, %d
			",
			$vars
		) );

		$r = array();

		foreach ( $posts as $p ) {

			$r[] = array(
				'id' => $p->ID,
				'name' => $p->post_title . ' (' . __( 'ID#', 'lifterlms' ) . ' ' . $p->ID . ')',
			);

		}

		echo json_encode( array(
			'items' => $r,
			'more' => count( $r ) === $limit,
			'success' => true,
		) );

		wp_die();
	}

	/**
	 * Validate a Coupon via the Checkout Form
	 * @return  string/json
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public static function validate_coupon_code( $request ) {

		$error = new WP_Error();

		// validate for required fields
		if ( empty( $request['code'] ) ) {

			$error->add( 'error', __( 'Please enter a coupon code.', 'lifterlms' ) );

			// this shouldn't be possible...
		} elseif ( empty( $request['plan_id'] ) ) {

			$error->add( 'error', __( 'Please enter a plan ID.', 'lifterlms' ) );

		} // all required fields found
		else {

			$cid = llms_find_coupon( $request['code'] );

			if ( ! $cid ) {

				$error->add( 'error', sprintf( __( 'Coupon code "%s" not found.', 'lifterlms' ), $request['code'] ) );

			} else {

				$c = new LLMS_Coupon( $cid );

				$valid = $c->is_valid( $request['plan_id'] );

				if ( is_wp_error( $valid ) ) {

					$error = $valid;

				} else {

					LLMS()->session->set( 'llms_coupon', array(
						'plan_id' => $request['plan_id'],
						'coupon_id' => $c->get( 'id' ),
					) );

					$plan = new LLMS_Access_Plan( $request['plan_id'] );

					ob_start();
					llms_get_template( 'checkout/form-coupon.php', array(
						'coupon' => $c,
					) );
					$coupon_html = ob_get_clean();


					ob_start();
					llms_get_template( 'checkout/form-gateways.php', array(
						'coupon' => $c,
						'gateways' => LLMS()->payment_gateways()->get_enabled_payment_gateways(),
						'selected_gateway' => LLMS()->payment_gateways()->get_default_gateway(),
						'plan' => $plan,
					) );
					$gateways_html = ob_get_clean();


					ob_start();
					llms_get_template( 'checkout/form-summary.php', array(
						'coupon' => $c,
						'plan' => $plan,
						'product' => $plan->get_product(),
					) );
					$summary_html = ob_get_clean();

					$success = array(
						'code' => $c->get( 'title' ),
						'coupon_html' => $coupon_html,
						'gateways_html' => $gateways_html,
						'summary_html' => $summary_html,
					);

				}

			}

		}

		// if there are errors, return them
		if ( $error->get_error_messages() ) {

			return $error;

		} else {

			return $success;

		}

	}














	/**
	 * @todo organize and docblock remaining class functions
	 */

	public static function create_section( $request ) {

		$section_id = LLMS_Post_Handler::create_section( $request['post_id'], $request['title'] );

		$html = LLMS_Meta_Box_Course_Outline::section_tile( $section_id );

		return $html;

	}

	public static function get_course_sections( $request ) {

		$course = new LLMS_Course( $request['post_id'] );
		$sections = $course->get_children_sections();

		return $sections;
	}

	public static function create_lesson( $request ) {

		$lesson_id = LLMS_Post_Handler::create_lesson(
			$request['post_id'],
			$request['section_id'],
			$request['title'],
			$request['excerpt']
		);

		$html = LLMS_Meta_Box_Course_Outline::lesson_tile( $lesson_id, $request['section_id'] );

		return $html;

	}

	public static function get_lesson_options_for_select( $request ) {

		return LLMS_Post_Handler::get_lesson_options_for_select_list();

	}

	public static function add_lesson_to_course( $request ) {

		$lesson_id = LLMS_Lesson_Handler::assign_to_course( $request['post_id'], $request['section_id'], $request['lesson_id'] );

		$html = LLMS_Meta_Box_Course_Outline::lesson_tile( $lesson_id, $request['section_id'] );

		return $html;

	}

	public static function get_course_section( $request ) {

		return new LLMS_Section( $request['section_id'] );
	}

	public static function update_course_section( $request ) {

		$section = new LLMS_Section( $request['section_id'] );
		return $section->set_title( $request['title'] );

	}

	public static function get_course_lesson( $request ) {

		return new LLMS_Lesson( $request['lesson_id'] );

	}

	public static function update_course_lesson( $request ) {

		$post_data = array(
			'title' => $request['title'],
			'excerpt' => $request['excerpt'],
		);

		$lesson = new LLMS_Lesson( $request['lesson_id'] );

		return $lesson->update( $post_data );

	}

	public static function remove_course_lesson( $request ) {

		$post_data = array(
			'parent_course' => '',
			'parent_section' => '',
			'order'	=> '',
		);

		$lesson = new LLMS_Lesson( $request['lesson_id'] );

		return $lesson->update( $post_data );

	}

	public static function delete_course_section( $request ) {

		$section = new LLMS_Section( $request['section_id'] );
		return $section->delete();
	}

	public static function update_section_order( $request ) {

		$updated_data;

		foreach ( $request['sections'] as $key => $value ) {

			$section = new LLMS_Section( $key );
			$updated_data[ $key ] = $section->update( array( 'order' => $value ) );

		}

		return $updated_data;

	}

	public static function update_lesson_order( $request ) {

		$updated_data;

		foreach ( $request['lessons'] as $key => $value ) {

			$lesson = new LLMS_Lesson( $key );
			$updated_data[ $key ] = $lesson->update(
				array(
					'parent_section' => $value['parent_section'],
					'order' => $value['order'],
				)
			);

		}

		return $updated_data;

	}

}

new LLMS_AJAX_Handler();
