<?php
/**
 * Coupon Metabox
 *
 * @since 1.0.0
 * @version 3.35.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Coupon Metabox class.
 *
 * @since 1.0.0
 * @since 3.32.0 Coupons can now be restricted also to a draft or scheduled Course/Membership.
 * @since 3.35.0 Sanitize `$_POST` data and verify nonce.
 */
class LLMS_Meta_Box_Coupon extends LLMS_Admin_Metabox {

	/**
	 * Configure the metabox settings
	 *
	 * @return void
	 * @since  3.0.0
	 */
	public function configure() {

		$this->id       = 'lifterlms-coupon';
		$this->title    = __( 'Coupon Settings', 'lifterlms' );
		$this->screens  = array(
			'llms_coupon',
		);
		$this->priority = 'high';

	}

	/**
	 * This function is where extending classes can configure all the fields within the metabox.
	 * The function must return an array which can be consumed by the "output" function.
	 *
	 * @since 3.0.0
	 * @since 3.32.0 Coupons can now be restricted also to a draft or scheduled Course/Membership
	 *                  via the `<select />` data attribute 'post-statuses' (data-post-status).
	 *
	 * @return array
	 */
	public function get_fields() {

		$courses     = array();
		$memberships = array();

		if ( isset( $this->post ) ) {

			$c = new LLMS_Coupon( $this->post );

			foreach ( $c->get_array( 'coupon_courses' ) as $course_id ) {
				$courses[] = array(
					'key'   => $course_id,
					'title' => get_the_title( $course_id ) . ' (' . __( 'ID#', 'lifterlms' ) . ' ' . $course_id . ')',
				);
			}
			foreach ( $c->get_array( 'coupon_membership' ) as $membership_id ) {
				$memberships[] = array(
					'key'   => $membership_id,
					'title' => get_the_title( $membership_id ) . ' (' . __( 'ID#', 'lifterlms' ) . ' ' . $membership_id . ')',
				);
			}
		} else {

			$c = false;

		}

		return array(

			array(
				'title'  => 'General',
				'fields' => array(
					array(
						'allow_null'      => false,
						'class'           => 'llms-select2',
						'data_attributes' => array(
							'minimum-results-for-search' => 5,
						),
						'desc'            => __( 'Select a dollar or percentage discount.', 'lifterlms' ),
						'desc_class'      => 'd-all',
						'id'              => $this->prefix . 'discount_type',
						'label'           => __( 'Discount Type', 'lifterlms' ),
						'type'            => 'select',
						'value'           => array(
							array(
								'key'   => 'percent',
								'title' => __( 'Percentage Discount', 'lifterlms' ),
							),
							array(
								'key'   => 'dollar',
								'title' => sprintf( __( '%s Discount', 'lifterlms' ), get_lifterlms_currency_symbol() ),
							),
						),
					),
					array(
						'type'            => 'select',
						'label'           => __( 'Access Plan Types', 'lifterlms' ),
						'desc'            => __( 'Select which type of access plans this coupon can be used with.', 'lifterlms' ),
						'id'              => $this->prefix . 'plan_type',
						'class'           => 'llms-select2',
						'value'           => array(
							array(
								'key'   => 'any',
								'title' => __( 'Any Access Plan', 'lifterlms' ),
							),
							array(
								'key'   => 'one-time',
								'title' => __( 'Only One-time Payment Access Plans', 'lifterlms' ),
							),
							array(
								'key'   => 'recurring',
								'title' => sprintf( __( 'Only Recurring Access Plans', 'lifterlms' ), get_lifterlms_currency_symbol() ),
							),
						),
						'desc_class'      => 'd-all',
						'allow_null'      => false,
						'data_attributes' => array(
							'minimum-results-for-search' => 5,
						),
					),
					array(
						'type'       => 'number',
						'label'      => __( 'Discount Amount', 'lifterlms' ),
						'desc'       => sprintf( __( 'The amount to be subtracted from the "Price" of an applicable access plan. Do not include symbols such as %1$s.', 'lifterlms' ), get_lifterlms_currency_symbol() ),
						'id'         => $this->prefix . 'coupon_amount',
						'class'      => 'code input-full',
						'desc_class' => 'd-all',
						'required'   => true,
					),
					array(
						'type'       => 'checkbox',
						'label'      => __( 'Enable Trial Pricing Discount', 'lifterlms' ),
						'desc'       => 'When checked, the coupon can apply a discount to an access plan\'s "Trial Price"',
						'id'         => $this->prefix . 'enable_trial_discount',
						'value'      => 'yes',
						'desc_class' => 'd-3of4 t-3of4 m-1of2',
						'controls'   => '#' . $this->prefix . 'trial_amount',
					),
					array(
						'type'       => 'number',
						'label'      => __( 'Trial Discount Amount', 'lifterlms' ),
						'desc'       => sprintf( __( 'The amount to be subtracted from the "Trial Price" of an applicable access plan. Do not include symbols such as %1$s.', 'lifterlms' ), get_lifterlms_currency_symbol() ),
						'id'         => $this->prefix . 'trial_amount',
						'class'      => 'code input-full',
						'desc_class' => 'd-all',
						'group'      => '',
						'value'      => '',
					),
				),
			),

			array(
				'title'  => __( 'Restrictions', 'lifterlms' ),
				'fields' => array(
					array(
						'type'            => 'select',
						'label'           => __( 'Courses', 'lifterlms' ),
						'desc'            => __( 'Limit coupon to the following courses.', 'lifterlms' ),
						'id'              => $this->prefix . 'coupon_courses',
						'class'           => 'input-full llms-select2-post',
						'value'           => $courses,
						'multi'           => true,
						'selected'        => $c ? $c->get_array( 'coupon_courses' ) : array(),
						'data_attributes' => array(
							'post-type'     => 'course',
							'post-statuses' => 'publish,draft,future',
						),
					),
					array(
						'type'            => 'select',
						'label'           => __( 'Membership', 'lifterlms' ),
						'desc'            => __( 'Limit coupon to the following memberships.', 'lifterlms' ),
						'id'              => $this->prefix . 'coupon_membership',
						'class'           => 'input-full llms-select2-post',
						'value'           => $memberships,
						'multi'           => true,
						'selected'        => $c ? $c->get_array( 'coupon_membership' ) : array(),
						'data_attributes' => array(
							'post-type'     => 'llms_membership',
							'post-statuses' => 'publish,draft,future',
						),
					),
					array(
						'type'       => 'date',
						'label'      => __( 'Coupon Expiration Date', 'lifterlms' ),
						'desc'       => __( 'Coupon will no longer be usable after this date. Leave blank for no expiration.', 'lifterlms' ),
						'id'         => $this->prefix . 'expiration_date',
						'class'      => 'llms-datepicker input-full',
						'value'      => '',
						'desc_class' => 'd-all',
						'group'      => '',
					),
					array(
						'type'       => 'number',
						'label'      => __( 'Usage Limit', 'lifterlms' ),
						'desc'       => __( 'The amount of times this coupon can be used. Leave empty or enter 0 for unlimited uses.', 'lifterlms' ),
						'id'         => $this->prefix . 'usage_limit',
						'class'      => 'code input-full',
						'desc_class' => 'd-all',
						'group'      => '',
						'value'      => '',
					),
				),
			),

			array(
				'title'  => __( 'Description', 'lifterlms' ),
				'fields' => array(
					array(
						'type'       => 'textarea',
						'label'      => __( 'Description', 'lifterlms' ),
						'desc'       => __( 'Optional description for internal notes. This is never displayed to your students.', 'lifterlms' ),
						'id'         => $this->prefix . 'description',
						'desc_class' => 'd-all',
						'group'      => '',
						'value'      => '',
						'required'   => false,
					),
				),
			),
		);

	}

	/**
	 * Save all metadata
	 *
	 * @since 3.0.0
	 * @since 3.35.0 Sanitize `$_POST` data and verify nonce.
	 *
	 * @param int $post_id WP Post ID.
	 * @return void
	 */
	protected function save( $post_id ) {

		if ( ! llms_verify_nonce( 'lifterlms_meta_nonce', 'lifterlms_save_data' ) ) {
			return;
		}

		$coupon = new LLMS_Coupon( $post_id );

		// dupcheck the title
		$exists = llms_find_coupon( $coupon->get( 'title' ), $post_id );
		if ( $exists ) {
			$this->add_error( __( 'Coupon code already exists. Customers will use the most recently created coupon with this code.', 'lifterlms' ) );
		}

		// trial validation
		$trial_discount = llms_filter_input( INPUT_POST, $this->prefix . 'enable_trial_discount', FILTER_SANITIZE_STRING );
		$trial_amount   = llms_filter_input( INPUT_POST, $this->prefix . 'trial_amount', FILTER_SANITIZE_NUMBER_INT );
		if ( ! $trial_discount ) {
			$trial_discount = 'no';
		} elseif ( 'yes' === $trial_discount && empty( $trial_amount ) ) {
			$this->add_error( __( 'A Trial Discount Amount was not supplied. Trial Pricing Discount has automatically been disabled. Please re-enable Trial Pricing Discount and enter a Trial Discount Amount, then save this coupon again.', 'lifterlms' ) );
			$trial_discount = 'no';
		}

		$coupon->set( 'enable_trial_discount', $trial_discount );
		$coupon->set( 'trial_amount', $trial_amount );

		$courses = llms_filter_input( INPUT_POST, $this->prefix . 'coupon_courses', FILTER_SANITIZE_NUMBER_INT, FILTER_REQUIRE_ARRAY );
		if ( empty( $courses ) ) {
			$courses = array();
		}

		$coupon->set( 'coupon_courses', $courses );

		$memberships = llms_filter_input( INPUT_POST, $this->prefix . 'coupon_membership', FILTER_SANITIZE_NUMBER_INT, FILTER_REQUIRE_ARRAY );
		if ( empty( $memberships ) ) {
			$memberships = array();
		}

		$coupon->set( 'coupon_membership', $memberships );

		// save all the fields
		$fields = array(
			'coupon_amount',
			'usage_limit',
			'discount_type',
			'description',
			'expiration_date',
			'plan_type',
		);
		foreach ( $fields as $field ) {
			if ( isset( $_POST[ $this->prefix . $field ] ) ) {
				$coupon->set( $field, llms_filter_input( INPUT_POST, $this->prefix . $field, FILTER_SANITIZE_STRING ) );
			}
		}

	}

}
