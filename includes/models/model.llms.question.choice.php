<?php
/**
 * LifterLMS Quiz Question Model
 *
 * @since    3.16.0
 * @version  3.16.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Question_Choice model.
 */
class LLMS_Question_Choice {

	protected $prefix = '_llms_choice_';

	private $id = null;

	private $data = array();

	private $question    = null;
	private $question_id = null;

	/**
	 * Constructor
	 *
	 * @param    int          $question_id  WP Post ID of the choice's parent LLMS_Question
	 * @param    array|string $data_or_id   array of choice data or the choice ID string
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	public function __construct( $question_id, $data_or_id = array() ) {

		// ensure the question is valid
		if ( $this->set_question( $question_id ) ) {

			// if an ID is passed in, load the question data from post meta
			if ( ! is_array( $data_or_id ) ) {
				$data_or_id = str_replace( $this->prefix, '', $data_or_id );
				$data_or_id = get_post_meta( $this->question_id, $this->prefix . $data_or_id, true );
			}

			// hydrate with postmeta data or array of data passed in
			if ( is_array( $data_or_id ) && isset( $data_or_id['id'] ) ) {
				$this->hydrate( $data_or_id );
			}
		}

	}

	/**
	 * Creates a new question
	 *
	 * @param    array $data  question data array
	 * @return   self
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	public function create( $data ) {

		$this->id = uniqid();
		return $this->update( $data )->save();

	}

	/**
	 * Delete a choice
	 *
	 * @return   boolean
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	public function delete() {
		return delete_post_meta( $this->question_id, $this->prefix . $this->id );
	}

	/**
	 * Determine if the choice that's been requested actually exists
	 *
	 * @return   boolean
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	public function exists() {
		return ( $this->id );
	}

	/**
	 * Retrieve a piece of choice data by key
	 *
	 * @param    string $key      name of the data to be retrieved
	 * @param    mixed  $default  default value if key isn't set
	 * @return   mixed
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	public function get( $key, $default = '' ) {

		if ( isset( $this->data[ $key ] ) ) {
			return $this->data[ $key ];
		}

		return $default;

	}

	/**
	 * Generic choice getter which automatically uses correct functions based on choice type
	 *
	 * @return   string
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	public function get_choice() {
		if ( 'image' === $this->get( 'choice_type' ) ) {
			return $this->get_image();
		}
		return $this->get( 'choice' );
	}

	/**
	 * Retrieve an image for picture choices
	 *
	 * @return   [type]
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	public function get_image() {
		if ( 'image' !== $this->get( 'choice_type' ) ) {
			return '';
		}
		$img = $this->get( 'choice' );
		if ( is_array( $img ) && isset( $img['id'] ) ) {
			return wp_get_attachment_image( $img['id'], 'full' );
		}
		return '';
	}

	/**
	 * Retrieve all of the choice data as an array
	 *
	 * @return   array
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	public function get_data() {
		return $this->data;
	}

	/**
	 * Retrieve an instance of an LLMS_Question for questions parent
	 *
	 * @return   obj
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	public function get_question() {
		return $this->question;
	}

	/**
	 * Retrieve the question ID for the given choice
	 *
	 * @return   int
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	public function get_question_id() {
		return $this->question_id;
	}

	/**
	 * Setup the id and data variables
	 *
	 * @param    array $data  array of question data
	 * @return   void
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	private function hydrate( $data ) {
		$this->id         = $data['id'];
		$this->data['id'] = $this->id;
		$this->update( $data );
	}

	/**
	 * Determine if the choice is correct
	 *
	 * @return   bool
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	public function is_correct() {
		return filter_var( $this->get( 'correct' ), FILTER_VALIDATE_BOOLEAN );
	}

	/**
	 * Save $this->data to the postmeta table
	 *
	 * @return   void
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	public function save() {

		$this->data['id'] = $this->id; // always ensure the ID is set when saving data
		$update           = update_post_meta( $this->question_id, $this->prefix . $this->id, $this->data );

		return ( $update );

	}

	/**
	 * Set a piece of data by key
	 *
	 * @param    string $key  name of the key to set
	 * @param    mixed  $val  value to set
	 * @return   self
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	public function set( $key, $val ) {

		// dont set the ID
		if ( 'id' === $key ) {
			return $this;
		}

		switch ( $key ) {

			case 'choice_type':
				if ( ! in_array( $val, array( 'text', 'image' ) ) ) {
					$val = 'text';
				}
				break;

			case 'correct':
				$val = filter_var( $val, FILTER_VALIDATE_BOOLEAN );
				break;

			case 'marker':
				$type    = $this->get_question()->get_question_type();
				$markers = $type['choices']['markers'];
				if ( ! in_array( $val, $markers ) ) {
					$val = $markers[0];
				}
				break;

			case 'choice':
			default:
				if ( is_array( $val ) ) {
					$val = array_map( 'sanitize_text_field', $val );
				} else {
					$val = wp_kses_post( $val );
				}
				break;

		}

		$this->data[ $key ] = $val;
		return $this;

	}

	/**
	 * Sets question-related data from constructor
	 *
	 * @param    int $id  WP Post ID of the question's parent question
	 * @return   boolean
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	public function set_question( $id ) {
		$question = llms_get_post( $id );
		if ( $question && is_a( $question, 'LLMS_Question' ) ) {
			$this->question    = $question;
			$this->question_id = $id;
			return true;
		}

		return false;

	}

	/**
	 * Update multiple data by key=>val pairs
	 *
	 * @param    array $data  array of data to set
	 * @return   self
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	public function update( $data = array() ) {

		foreach ( $data as $key => $val ) {
			$this->set( $key, $val );
		}
		return $this;

	}

}
