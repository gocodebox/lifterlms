<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* Base Course Class
*
* Class used for instantiating course object
*
* @version 1.0
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Course {

	/**
	* ID
	* @access public
	* @var int
	*/
	public $id;

	/**
	* Post Object
	* @access public
	* @var array
	*/
	public $post;

	/**
	* Constructor
	*
	* initializes the course object based on post data
	*/
	public function __construct( $course ) {

		if ( is_numeric( $course ) ) {

			$this->id   = absint( $course );
			$this->post = get_post( $this->id );

		}

		elseif ( $course instanceof LLMS_Course ) {

			$this->id   = absint( $course->id );
			$this->post = $course;

		}

		elseif ( isset( $course->ID ) ) {

			$this->id   = absint( $course->ID );
			$this->post = $course;

		}

	}

	/**
	* __isset function
	*
	* checks if metadata exists
	*
	* @param string $item
	*/
	public function __isset( $item ) {

		return metadata_exists( 'post', $this->id, '_' . $item );

	}

	/**
	* __get function
	*
	* initializes the course object based on post data
	*
	* @param string $item
	* @return string $value
	*/
	public function __get( $item ) {

		$value = get_post_meta( $this->id, '_' . $item, true );

		return $value;
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
	 * Get Lesson Length
	 *
	 * @return string
	 */
	public function get_lesson_length() {

		$enabled = get_option('lifterlms_course_display_length');
		if ( 'yes' != $enabled ) {
			return false;
		} 

		elseif ($this->lesson_length == '') {
			return false;
		}

		return $this->lesson_length;

	}

	/**
	 * Return array of objects containing user meta data for a single post.
	 *
	 * @return  array
	 */
	public function get_user_postmeta_data( $post_id ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'lifterlms_user_postmeta';

		$results = $wpdb->get_results( $wpdb->prepare(
			'SELECT * FROM '.$table_name.' WHERE post_id = %d', $user_id, $post_id) );

		for ($i=0; $i < count($results); $i++) {
			$results[$results[$i]->meta_key] = $results[$i];
			unset($results[$i]);
		}

		return $results;
	}

	/**
	 * Return array of objects containing user meta data for a single post.
	 *
	 * @return  array
	 */
	public function get_user_postmetas_by_key( $post_id, $meta_key ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'lifterlms_user_postmeta';

		$results = $wpdb->get_results( $wpdb->prepare(
			'SELECT * FROM '.$table_name.' WHERE post_id = %s and meta_key = "%s" ORDER BY updated_date DESC', $post_id, $meta_key ) );

		for ($i=0; $i < count($results); $i++) {
			$results[$results[$i]->post_id] = $results[$i];
			unset($results[$i]);
		}

		return $results;
	}

	/**
	 * Get checkout url
	 *
	 * @return string
	 */
	public function get_checkout_url() {

		$checkout_page_id = llms_get_page_id( 'checkout' );
		$checkout_url =  apply_filters( 'lifterlms_get_checkout_url', $checkout_page_id ? get_permalink( $checkout_page_id ) : '' );
		
		return add_query_arg( 'course-id', $this->id, $checkout_url );

	}

	/**
	 * Get Video (oembed)
	 *
	 * @return mixed (default: '')
	 */
	public function get_video() {

		if ( ! isset( $this->video_embed ) ) {

			return '';

		}

		else {

			return wp_oembed_get($this->video_embed);

		}

	}

	/**
	 * Get Audio (wp shortcode)
	 *
	 * @return mixed (default: '')
	 */
	public function get_audio() {

		if ( ! isset( $this->audio_embed ) ) {

			return '';

		}

		else {

			return do_shortcode('[audio src="'. $this->audio_embed . '"]');

		}

	}

	/**
	 * Get Difficulty
	 *
	 * @return string
	 */
	public function get_difficulty() {

		$enabled = get_option('lifterlms_course_display_difficulty');

		if ( 'yes' != $enabled ) {
			return false;
		} 

		$terms = get_the_terms($this->id, 'course_difficulty');

		if ( $terms === false ) {

			return '';

		}

		else {

			foreach ( $terms as $term ) {

        		return $term->name;
        	}

		}

	}

	public function get_prerequisite() {

		if ( $this->has_prerequisite == 'on' ) {

			return $this->prerequisite;
		}
		else {
			return false;
		}
	}


	public function get_next_uncompleted_lesson() {
		$lessons_not_completed = array();
		$lesson_ids = array();

		$lessons = $this->get_lesson_ids();

		$user = new LLMS_Person;

		foreach( $lessons as $key => $value ) {

			$user_postmetas = $user->get_user_postmeta_data( get_current_user_id(), $value['lesson_id'] );

				if ( ! isset($user_postmetas['_is_complete']) ) {

					array_push($lessons_not_completed, $value['lesson_id']);
			}
		}

		return $lessons_not_completed[0];

	}

	public function get_lesson_ids() {
		$array  = array ();

		$syllabus = $this->get_syllabus();
if($syllabus) {
		foreach($syllabus as $key => $value ) :
			if($syllabus[$key]['lessons']) {
			foreach ($syllabus[$key]['lessons'] as $keys) :

				array_push($array, $keys);

			endforeach;
}
		endforeach;
}
		return $array;

	}

	public function get_percent_complete() {
		$lesson_ids = $this->get_lesson_ids();
		$array = array();
		$i = 0;

		$user = new LLMS_Person;

		foreach( $lesson_ids as $key => $value ) {
			array_push($array, $value['lesson_id']);
		}

		foreach( $array as $key => $value ) {
			$user_postmetas = $user->get_user_postmeta_data( get_current_user_id(), $value );
			if ( isset($user_postmetas['_is_complete']) ) {
				if ( $user_postmetas['_is_complete']->meta_value === 'yes' ) {
					$i++;
				}
			}
		}

		$percent_complete = ($i != 0) ? round(100 / ( ( count($lesson_ids) / $i ) ), 0 ) : 0;

		return $percent_complete;

	}

	public function get_section_percent_complete($lesson_id) {

		$syllabus = $this->get_syllabus();
		$sections = array();
		$section;

		foreach ($syllabus as $key => $value) {

			$sections[$value['section_id']] = $value['lessons'];
			foreach($value['lessons'] as $keys => $values) {
				if ($values['lesson_id'] == $lesson_id) {
					$section = $value['section_id'];
				}
			}
		}


		$total_lessons_in_section = count($sections[$section]);
		$total_completed_lessons = 0;
		foreach($sections[$section] as $key => $value) {

			$user = new LLMS_Person;
			$user_postmetas = $user->get_user_postmeta_data( get_current_user_id(), $value['lesson_id'] );
			if ( $user_postmetas['_is_complete']->meta_value === 'yes' ) {
				$total_completed_lessons++;

			}
		}

		$percent_complete = ($total_lessons_in_section != 0) ? round(100 / ( ( $total_lessons_in_section / $total_completed_lessons ) ), 0 ) : 0;

		return $percent_complete;
	}

	/**
	 * Get the course short description
	 *
	 * @return string (html)
	 */
	public function get_short_description() {

		$short_description = wpautop($this->post->post_excerpt);

		return $short_description;

	}


	/**
	 * Get the Course Section and Lesson information
	 *
	 * @return string
	 */
	public function get_syllabus() {

		$syllabus = $this->sections;

		return $syllabus;

	}

	/**
	 * Get price in html format
	 *
	 * @return string
	 */
	public function get_price_html( $price = '' ) {

		$suffix 				= $this->get_price_suffix_html();
		$currency_symbol 		= get_lifterlms_currency_symbol() != '' ? get_lifterlms_currency_symbol() : '';
		$display_price 			= $this->get_price();
		$display_base_price 	= $this->get_base_price();
		$display_sale_price    	= $this->get_sale_price();

		if ( $this->get_price() > 0 ) {
			$price = $this->set_price_html_as_value($suffix, $currency_symbol, $display_price, $display_base_price, $display_sale_price);

		}

		elseif ( $this->get_price() === '' ) {

			$price = apply_filters( 'lifterlms_empty_price_html', '', $this );

		}

		elseif ( $this->get_price() == 0 ) {

			$price = $this->set_price_html_as_free();

		}

		return apply_filters( 'lifterlms_get_price_html', $price, $this );
	}


	/**
	 * Set price html to a decimal value with currency and suffix.
	 *
	 * @return string
	 */
	public function set_price_html_as_value($suffix, $currency_symbol, $display_price, $display_base_price, $display_sale_price) {


		// Check if price is on sale and base price exists
		if ( $this->is_on_sale() && $this->get_base_price() ) {

			//generate price with formatting and suffix
			$price = $currency_symbol;

			$price .= $this->get_price_variations_html( $display_base_price, $display_price ) . $suffix;

			$price = apply_filters( 'lifterlms_sale_price_html', $price, $this );

		}

		else {

			//generate price with formatting and suffix
			$price = $currency_symbol;

			$price .= llms_price( $display_price ) . $suffix;

			$price = apply_filters( 'lifterlms_price_html', $price, $this );

		}

		return $price;

	}

	/**
	 * Set price html to Free is ocurse is 0
	 *
	 * @return string
	 */
	public function set_price_html_as_free() {

		if ( $this->is_on_sale() && $this->get_base_price() ) {

			$price .= $this->get_price_variations_html( $display_base_price, __( 'Free!', 'lifterlms' ) );

			$price .= apply_filters( 'lifterlms_free_sale_price_html', $price, $this );

		}

		else {

			$price = __( 'Free!', 'lifterlms' );

			$price = apply_filters( 'lifterlms_free_price_html', $price, $this );

		}

		return $price;

	}

	/**
	 * Check: Is the sale price different than the base price and is the sale price equal to the price returned from get_price().
	 *
	 * @return bool
	 */
	public function is_on_sale() {

		return ( $this->get_sale_price() != $this->get_base_price() && $this->get_sale_price() == $this->get_price() );

	}

	/**
	 * Get function for price value.
	 *
	 * @return void
	 */
	public function get_price() {

		return apply_filters( 'lifterlms_get_price', $this->price, $this );

	}

	/**
	 * Set function for price value.
	 *
	 * @return void
	 */
	public function set_price( $price ) {

		$this->price = $price;

	}

	/**
	 * get the base price value.
	 *
	 * @return void
	 */
	public function get_base_price( $price = '' ) {

		$price = $price;

	}

	/**
	 * get the base price value.
	 *
	 * @return void
	 */
	public function get_sale_price( $price = '' ) {

		$price = $price;

	}

	/**
	 * creates the price suffix html
	 *
	 * @return void
	 */
	public function get_price_suffix_html() {


		$price_display_suffix  = get_option( 'lifterlms_price_display_suffix' );

		if ( $price_display_suffix ) {

			$price_display_suffix = ' <small class="lifterlms-price-suffix">' . $price_display_suffix . '</small>';

			$price_display_suffix = str_replace( $find, $replace, $price_display_suffix );

		}

		return apply_filters( 'lifterlms_get_price_suffix_html', $price_display_suffix, $this );
	}

	/**
	 * Returns base price and sale price in html format.
	 *
	 * @return string
	 */
	public function get_price_variations_html( $base, $sale ) {

		return '<del>' . ( ( is_numeric( $base ) ) ? llms_price( $base ) : $base ) . '</del> <ins>' . ( ( is_numeric( $sale ) ) ? llms_price( $sale ) : $sale ) . '</ins>';

	}


	/**
	 * checks if course is visible
	 *
	 * @return bool
	 */
	public function is_visible() {

		$visible = true;


		// visibility setting
		if ( $this->visibility === 'hidden' ) {

			$visible = false;

		}

		elseif ( $this->visibility === 'visible' ) {

			$visible = true;

		// Visibility in loop
		}

		elseif ( $this->visibility === 'search' && is_search() ) {

			$visible = true;

		}

		elseif ( $this->visibility === 'search' && ! is_search() ) {

			$visible = false;

		}

		elseif ( $this->visibility === 'catalog' && is_search() ) {

			$visible = false;

		}

		elseif ( $this->visibility === 'catalog' && ! is_search() ) {

			$visible = true;
		}

		return apply_filters( 'lifterlms_course_is_visible', $visible, $this->id );

	}

}