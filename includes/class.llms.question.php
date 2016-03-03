<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
* Base Question Class
*
*/
class LLMS_Question {

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
	* initializes the question object based on post data
	*/
	public function __construct( $question ) {

		if ( is_numeric( $question ) ) {

			$this->id   = absint( $question );
			$this->post = get_post( $this->id );

		} elseif ( $question instanceof LLMS_Question ) {

			$this->id   = absint( $question->id );
			$this->post = $question;

		} elseif ( isset( $question->ID ) ) {

			$this->id   = absint( $question->ID );
			$this->post = $question;

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
	* initializes the question object based on post data
	*
	* @param string $item
	* @return string $value
	*/
	public function __get( $item ) {

		$value = get_post_meta( $this->id, '_' . $item, true );

		return $value;
	}

	/**
	 * Get question type
	 * @return string [type id]
	 */
	public function get_type() {
		return $this->llms_question_type;
	}

	/**
	 * Get question options
	 * @return array [question options]
	 */
	public function get_options() {

		return $this->llms_question_options;
	}

	public function get_correct_option() {
		$options = $this->get_options();
		foreach ($options as $option) {
			if ($option['correct_option']) {
				return $option;
			}
		}
	}

}
