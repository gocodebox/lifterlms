<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LLMS_Course {

	// Post Id
	public $id;

	/** @var object The actual post object. */
	public $post;

	
	public function __construct( $course ) {
		if ( is_numeric( $course ) ) {
			$this->id   = absint( $course );
			$this->post = get_post( $this->id );
		} elseif ( $course instanceof LLMS_Course ) {
			$this->id   = absint( $course->id );
			$this->post = $course;
		} elseif ( $course instanceof LLMS_Post || isset( $course->ID ) ) {
			$this->id   = absint( $course->ID );
			$this->post = $course;
		}
	}

	/**
	 * Get SKU
	 *
	 * @return string
	 */
	public function get_sku() {
		return $this->sku;
	}

	/**
	 * Returns the price in html format.
	 *
	 * @access public
	 * @param string $price (default: '')
	 * @return string
	 */
	public function get_price_html( $price = '' ) {

		$tax_display_mode      = ''; //TODO
		$display_price         = $tax_display_mode == 'incl' ? $this->get_price_including_tax() : $this->get_price_excluding_tax();
		$display_regular_price = $tax_display_mode == 'incl' ? $this->get_price_including_tax( 1, $this->get_regular_price() ) : $this->get_price_excluding_tax( 1, $this->get_regular_price() );
		$display_sale_price    = $tax_display_mode == 'incl' ? $this->get_price_including_tax( 1, $this->get_sale_price() ) : $this->get_price_excluding_tax( 1, $this->get_sale_price() );

		if ( $this->get_price() > 0 ) {

			if ( $this->is_on_sale() && $this->get_regular_price() ) {

				$price .= $this->get_price_html_from_to( $display_regular_price, $display_price ) . $this->get_price_suffix();

				$price = apply_filters( 'lifterlms_sale_price_html', $price, $this );

			} else {

				$price .= llms_price( $display_price ) . $this->get_price_suffix();

				$price = apply_filters( 'lifterlms_price_html', $price, $this );

			}

		} elseif ( $this->get_price() === '' ) {

			$price = apply_filters( 'lifterlms_empty_price_html', '', $this );

		} elseif ( $this->get_price() == 0 ) {

			if ( $this->is_on_sale() && $this->get_regular_price() ) {

				$price .= $this->get_price_html_from_to( $display_regular_price, __( 'Free!', 'lifterlms' ) );

				$price = apply_filters( 'lifterlms_free_sale_price_html', $price, $this );

			} else {

				$price = __( 'Free!', 'lifterlms' );

				$price = apply_filters( 'lifterlms_free_price_html', $price, $this );

			}
		}

		return apply_filters( 'lifterlms_get_price_html', $price, $this );
	}

}