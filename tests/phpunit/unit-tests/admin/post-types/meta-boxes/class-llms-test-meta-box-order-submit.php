<?php
/**
 * Tests for LifterLMS Order Metabox
 *
 * @package LifterLMS/Tests
 *
 * @group admin
 * @group metaboxes
 * @group order_submit
 * @group metaboxes_post_type
 *
 * @since [version]
 */
class LLMS_Test_Meta_Box_Order_Submit extends LLMS_PostTypeMetaboxTestCase {

	/**
	 * Setup test.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		$this->main = new LLMS_Meta_Box_Order_Submit();

	}

	/**
	 * Test save() method checking all the editable date fields can be saved.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_save_all_editable_dates_success() {

		$order = $this->get_mock_order();

		$originals = array(
			'_llms_date_trial_end'      => $order->get( 'date_trial_end' ),
			'_llms_date_next_payment'   => $order->get( 'date_next_payment' ),
			'_llms_date_access_expires' => $order->get( 'date_access_expires' ),
		);

		$post_values = array(
			'_llms_date_trial_end'      => array(
				'date'   => '2022-06-20',
				'hour'   => '10',
				'minute' => '00',
			),
			'_llms_date_next_payment'   => array(
				'date'   => '2022-06-21',
				'hour'   => '10',
				'minute' => '00',
			),
			'_llms_date_access_expires' => array(
				'date'   => '2022-06-22',
				'hour'   => '10',
				'minute' => '00',
			),
		);

		$this->mockPostRequest(
			$this->add_nonce_to_array(
				$post_values
			)
		);

		$this->main->save( $order->get( 'id' ) );

		foreach ( $originals as $key => $value ) {
			$this->assertEquals(
				$post_values[ $key ]['date'] . ' ' . sprintf( '%02d', $post_values[ $key ]['hour'] ) . ':' . sprintf( '%02d', $post_values[ $key ]['minute'] ) . ':00',
				$order->get( str_replace( '_llms_', '', $key ) ),
				$key
			);
		}

	}

	/**
	 * Test save() method checking all the editable date fields can be saved, except recurring payment related.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_save_all_editable_dates_success_except_recurring_payment_related() {

		// The order's gateway is not set, so the order does not supports modifying recurring payments.
		$order_id = $this->factory->post->create( array( 'post_type' => 'llms_order' ) );
		$order    = llms_get_post( $order_id );

		$originals = array(
			'_llms_date_trial_end'      => $order->get( 'date_trial_end' ),
			'_llms_date_next_payment'   => $order->get( 'date_next_payment' ),
			'_llms_date_access_expires' => $order->get( 'date_access_expires' ),
		);

		$post_values = array(
			'_llms_date_trial_end'      => array(
				'date'   => '2022-06-20',
				'hour'   => '10',
				'minute' => '00',
			),
			'_llms_date_next_payment'   => array(
				'date'   => '2022-06-21',
				'hour'   => '10',
				'minute' => '00',
			),
			'_llms_date_access_expires' => array(
				'date'   => '2022-06-22',
				'hour'   => '10',
				'minute' => '00',
			),
		);

		$this->mockPostRequest(
			$this->add_nonce_to_array(
				$post_values
			)
		);

		$this->main->save( $order->get( 'id' ) );

		foreach ( $originals as $key => $value ) {
			$this->assertEquals(
				( '_llms_date_access_expires' === $key )
					?
					$post_values[ $key ]['date'] . ' ' . sprintf( '%02d', $post_values[ $key ]['hour'] ) . ':' . sprintf( '%02d', $post_values[ $key ]['minute'] ) . ':00'
					:
					$value, // Dates which are not `_llms_date_access_expires` are not being saved.
				$order->get( str_replace( '_llms_', '', $key ) ),
				$key
			);
		}

	}
}
