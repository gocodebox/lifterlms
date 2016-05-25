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

	public static function test_ajax_call() {

		return $_REQUEST;

	}

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


	public static function remove_coupon_code( $request ) {

		ob_start();
		llms_get_template( 'checkout/form-coupon.php' );
		$coupon_html = ob_get_clean();

		ob_start();
		llms_get_template( 'checkout/form-pricing.php', array(
			'product' => new LLMS_Product( $request['product_id'] ),
		) );
		$pricing_html = ob_get_clean();

		return array(
			'coupon_html' => $coupon_html,
			'pricing_html' => $pricing_html,
		);

	}

	/**
	 * Validate a Coupon via the Checkout Form
	 *
	 * @since  3.0.0
	 */
	public static function validate_coupon_code( $request ) {

		$error = new WP_Error();

		// validate for required fields
		if ( empty( $request['code'] ) ) {

			$error->add( 'error', __( 'Please enter a coupon code.', 'lifterlms' ) );

		// this shouldn't be possible...
		} elseif ( empty( $request['product_id'] ) ) {

			$error->add( 'error', __( 'Please enter a product ID.', 'lifterlms' ) );

		}
		// all required fields found
		else {

			$c = new LLMS_Coupon( $request['code'] );
			$valid = $c->is_valid( $request['product_id'] );

			if( is_wp_error( $valid ) ) {

				$error = $valid;

			} else {

				$coupon = array(
					'code' => $request['code'],
					'discounts' => array(
						'single'    => $c->get_single_amount(),
						'first'     => $c->get_recurring_first_payment_amount(),
						'recurring' => $c->get_recurring_payments_amount(),
					),
					'type'      => $c->get_discount_type(),
				);

				ob_start();
				llms_get_template( 'checkout/form-coupon.php', array(
					'coupon' => $c,
				) );
				$coupon_html = ob_get_clean();

				ob_start();
				llms_get_template( 'checkout/form-pricing.php', array(
					'coupon' => $c,
					'product' => new LLMS_Product( $request['product_id'] ),
				) );
				$pricing_html = ob_get_clean();

				$success = array_merge( $coupon, array(
					'coupon_html' => $coupon_html,
					'pricing_html' => $pricing_html,
				) );

			}

		}

		// if there are errors, return them
		if ( $error->get_error_messages() ) {

			return $error;

		} else {

			return $success;

		}

	}


}

new LLMS_AJAX_Handler();
