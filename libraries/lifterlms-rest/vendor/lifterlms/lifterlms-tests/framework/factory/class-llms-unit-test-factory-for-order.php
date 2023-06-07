<?php

/**
 * Unit test factory for orders.
 *
 * Note: The below `@method` notations are defined solely for the benefit of IDEs,
 * as a way to indicate expected return values from the given factory methods.
 *
 * @method LLMS_Order create_and_get( $args = array(), $generation_definitions = null )
 */
class LLMS_Unit_Test_Factory_For_Order extends WP_UnitTest_Factory_For_Post {

	public function __construct( $factory = null ) {
		$this->factory = $factory;
		parent::__construct( $factory );

		$this->default_generation_definitions = array(
			'customer' => '', // can pass an array of customer data, customer id, or empty to automatically generate a customer.
			'plan_id' => '', // plan ID
			'plan_price' => 99.99,
			'product_id' => '', // If no plan this product will be used for the auto-generated plan
			'agree_to_terms' => 'yes',
			'payment_gateway' => 'manual',
		);

	}

	public function create_object( $args ) {

		if ( class_exists( 'LLMS_Forms' ) ) {
			LLMS_Forms::instance()->install();
		}

		$cust_id = false;
		if ( is_numeric( $args['customer'] ) ) {
			$cust_id = $args['customer'];
		} elseif ( empty( $args['customer'] ) ) {
			$cust_id = $this->factory->student->create();
		}

		if ( $cust_id ) {
			wp_set_current_user( $cust_id );
			$args['customer'] = array(
				'user_id' => $cust_id,
				'first_name' => 'Sally',
				'last_name' => 'Handson',
				'llms_billing_address_1' => '913 Some Street',
				'llms_billing_city' => 'Reseda',
				'llms_billing_state' => 'CA',
				'llms_billing_zip' => '92342',
				'llms_billing_country' => 'US',
			);
		}

		if ( ! $args['plan_id'] ) {

			$plan = llms_insert_access_plan( array(
				'product_id' => ! empty( $args['product_id'] ) ? $args['product_id'] : $this->factory->course->create( array( 'sections' => 0 ) ),
				'price' => $args['plan_price'],
			) );
			$args['plan_id'] = $plan->get( 'id' );

		}

		$setup = llms_setup_pending_order( $args );

		$order = new LLMS_Order( 'new' );
		$order->init( $setup['person'], $setup['plan'], $setup['gateway'], $setup['coupon'] );

		wp_set_current_user( null );

		return $order->get( 'id' );

	}

	public function get_object_by_id( $post_id ) {
		return llms_get_post( $post_id );
	}

	public function create_and_pay( $args = array() ) {

		$order = $this->create_and_get( $args );

		$order->record_transaction( array(
			'amount' => $order->get( 'total' ),
		) );

		return $order;

	}

}
