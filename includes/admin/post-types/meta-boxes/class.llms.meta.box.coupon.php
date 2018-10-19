<?php
defined( 'ABSPATH' ) || exit;

/**
 * Coupon Metabox
 *
 * @since    1.0.0
 * @version  3.24.0
 */
class LLMS_Meta_Box_Coupon extends LLMS_Admin_Metabox {

	/**
	 * Configure the metabox settings
	 * @return void
	 * @since  3.0.0
	 */
	public function configure() {

		$this->id = 'lifterlms-coupon';
		$this->title = __( 'Coupon Settings', 'lifterlms' );
		$this->screens = array(
			'llms_coupon',
		);
		$this->priority = 'high';

	}

	/**
	 * This function is where extending classes can configure all the fields within the metabox
	 * The function must return an array which can be consumed by the "output" function
	 *
	 * @return  array
	 * @since   3.0.0
	 * @version 3.24.0
	 */
	public function get_fields() {

		$courses = array();
		$memberships = array();

		if ( isset( $this->post ) ) {

			$c = new LLMS_Coupon( $this->post );

			foreach ( $c->get_array( 'coupon_courses' ) as $course_id ) {
				$courses[] = array(
					'key' => $course_id,
					'title' => get_the_title( $course_id ) . ' (' . __( 'ID#', 'lifterlms' ) . ' ' . $course_id . ')',
				);
			}
			foreach ( $c->get_array( 'coupon_membership' ) as $membership_id ) {
				$memberships[] = array(
					'key' => $membership_id,
					'title' => get_the_title( $membership_id ) . ' (' . __( 'ID#', 'lifterlms' ) . ' ' . $membership_id . ')',
				);
			}
		} else {

			$c = false;

		}

		return array(

			array(
				'title' 	=> 'General',
				'fields' 	=> array(
					array(
						'allow_null' => false,
						'class' 	=> 'llms-select2',
						'data_attributes' => array(
							'minimum-results-for-search' => 5,
						),
						'desc' 		=> __( 'Select a dollar or percentage discount.', 'lifterlms' ),
						'desc_class' => 'd-all',
						'id' 		=> $this->prefix . 'discount_type',
						'label'		=> __( 'Discount Type', 'lifterlms' ),
						'type'		=> 'select',
						'value' 	=> array(
							array(
								'key' 	=> 'percent',
								'title' => __( 'Percentage Discount', 'lifterlms' ),
							),
							array(
								'key' 	=> 'dollar',
								'title' => sprintf( __( '%s Discount', 'lifterlms' ), get_lifterlms_currency_symbol() ),
							),
						),
					),
					array(
						'type'		=> 'select',
						'label'		=> __( 'Access Plan Types', 'lifterlms' ),
						'desc' 		=> __( 'Select which type of access plans this coupon can be used with.', 'lifterlms' ),
						'id' 		=> $this->prefix . 'plan_type',
						'class' 	=> 'llms-select2',
						'value' 	=> array(
							array(
								'key' 	=> 'any',
								'title' => __( 'Any Access Plan', 'lifterlms' ),
							),
							array(
								'key' 	=> 'one-time',
								'title' => __( 'Only One-time Payment Access Plans', 'lifterlms' ),
							),
							array(
								'key' 	=> 'recurring',
								'title' => sprintf( __( 'Only Recurring Access Plans', 'lifterlms' ), get_lifterlms_currency_symbol() ),
							),
						),
						'desc_class' => 'd-all',
						'allow_null' => false,
						'data_attributes' => array(
							'minimum-results-for-search' => 5,
						),
					),
					array(
						'type'  	=> 'number',
						'label'  	=> __( 'Discount Amount', 'lifterlms' ),
						'desc'  	=> sprintf( __( 'The amount to be subtracted from the "Price" of an applicable access plan. Do not include symbols such as %1$s.', 'lifterlms' ), get_lifterlms_currency_symbol() ),
						'id'    	=> $this->prefix . 'coupon_amount',
						'class' 	=> 'code input-full',
						'desc_class' => 'd-all',
						'required' => true,
					),
					array(
						'type'		=> 'checkbox',
						'label'		=> __( 'Enable Trial Pricing Discount', 'lifterlms' ),
						'desc' 		=> 'When checked, the coupon can apply a discount to an access plan\'s "Trial Price"',
						'id' 		=> $this->prefix . 'enable_trial_discount',
						'value' 	=> 'yes',
						'desc_class' => 'd-3of4 t-3of4 m-1of2',
						'controls'  => '#' . $this->prefix . 'trial_amount',
					),
					array(
						'type'  	=> 'number',
						'label'  	=> __( 'Trial Discount Amount', 'lifterlms', 'lifterlms' ),
						'desc'  	=> sprintf( __( 'The amount to be subtracted from the "Trial Price" of an applicable access plan. Do not include symbols such as %1$s.', 'lifterlms' ), get_lifterlms_currency_symbol() ),
						'id'    	=> $this->prefix . 'trial_amount',
						'class' 	=> 'code input-full',
						'desc_class' => 'd-all',
						'group' 	=> '',
						'value' 	=> '',
					),
				),
			),

			array(
				'title'  => __( 'Restrictions', 'lifterlms' ),
				'fields' => array(
					array(
						'type'  => 'select',
						'label' => __( 'Courses', 'lifterlms' ),
						'desc'  => __( 'Limit coupon to the following courses.', 'lifterlms' ),
						'id'    => $this->prefix . 'coupon_courses',
						'class' => 'input-full llms-select2-post',
						'value' => $courses,
						'multi' => true,
						'selected' => $c ? $c->get_array( 'coupon_courses' ) : array(),
						'data_attributes' => array(
							'post-type' => 'course',
						),
					),
					array(
						'type'  => 'select',
						'label' => __( 'Membership', 'lifterlms' ),
						'desc'  => __( 'Limit coupon to the following memberships.', 'lifterlms' ),
						'id'    => $this->prefix . 'coupon_membership',
						'class' => 'input-full llms-select2-post',
						'value' => $memberships,
						'multi' => true,
						'selected' => $c ? $c->get_array( 'coupon_membership' ) : array(),
						'data_attributes' => array(
							'post-type' => 'llms_membership',
						),
					),
					array(
						'type'		=> 'date',
						'label'		=> __( 'Coupon Expiration Date', 'lifterlms' ),
						'desc' 		=> __( 'Coupon will no longer be usable after this date. Leave blank for no expiration.', 'lifterlms' ),
						'id' 		=> $this->prefix . 'expiration_date',
						'class' 	=> 'llms-datepicker input-full',
						'value' 	=> '',
						'desc_class' => 'd-all',
						'group' 	=> '',
					),
					array(
						'type'  	=> 'number',
						'label'  	=> __( 'Usage Limit', 'lifterlms' ),
						'desc'  	=> __( 'The amount of times this coupon can be used. Leave empty or enter 0 for unlimited uses.', 'lifterlms' ),
						'id'    	=> $this->prefix . 'usage_limit',
						'class' 	=> 'code input-full',
						'desc_class' => 'd-all',
						'group' 	=> '',
						'value' 	=> '',
					),
				),
			),

			array(
				'title' => __( 'Description', 'lifterlms' ),
				'fields' => array(
					array(
						'type'  	=> 'textarea',
						'label' 	=> __( 'Description', 'lifterlms' ),
						'desc' 		=> __( 'Optional description for internal notes. This is never displayed to your students.', 'lifterlms' ),
						'id' 		=> $this->prefix . 'description',
						'desc_class' => 'd-all',
						'group' 	=> '',
						'value' 	=> '',
						'required'	=> false,
					),
				),
			),
		);

	}

	/**
	 * Save all metadata
	 *
	 * @param  int 		$post_id    post_id of the post we're editing
	 * @return void
	 * @version  3.0.0
	 */
	protected function save( $post_id ) {

		$c = new LLMS_Coupon( $post_id );

		// dupcheck the title
		$exists = llms_find_coupon( $c->get( 'title' ), $post_id );
		if ( $exists ) {
			$this->add_error( __( 'Coupon code already exists. Customers will use the most recently created coupon with this code.', 'lifterlms' ) );
		}

		// trial validation
		$trial = isset( $_POST[ $this->prefix . 'enable_trial_discount' ] ) ? $_POST[ $this->prefix . 'enable_trial_discount' ] : false;
		if ( ! $trial ) {
			$_POST[ $this->prefix . 'enable_trial_discount' ] = 'no';
		} elseif ( 'yes' === $trial && empty( $_POST[ $this->prefix . 'trial_amount' ] ) ) {

			$this->add_error( __( 'A Trial Discount Amount was not supplied. Trial Pricing Discount has automatically been disabled. Please re-enable Trial Pricing Discount and enter a Trial Discount Amount, then save this coupon again.', 'lifterlms' ) );
			$_POST[ $this->prefix . 'enable_trial_discount' ] = 'no';

		}

		if ( ! isset( $_POST[ $this->prefix . 'coupon_courses' ] ) ) {
			$_POST[ $this->prefix . 'coupon_courses' ] = array();
		}

		if ( ! isset( $_POST[ $this->prefix . 'coupon_membership' ] ) ) {
			$_POST[ $this->prefix . 'coupon_membership' ] = array();
		}

		// save all the fields
		$fields = array(
			'coupon_amount',
			'trial_amount',
			'usage_limit',
			'coupon_courses',
			'coupon_membership',
			'enable_trial_discount',
			'discount_type',
			'description',
			'expiration_date',
			'plan_type',
		);
		foreach ( $fields as $field ) {

			if ( isset( $_POST[ $this->prefix . $field ] ) ) {

				$c->set( $field, $_POST[ $this->prefix . $field ] );

			}
		}

	}

}
