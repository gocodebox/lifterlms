<?php
/**
 * LifterLMS Quiz Question
 *
 * @since    1.0.0
 * @version  3.3.0
 *
 * @property  $question_type  (string)  type of question
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Question extends LLMS_Post_Model {

	protected $db_post_type = 'llms_question';
	protected $model_post_type = 'question';

	protected $properties = array(
		'question_type' => 'string',
	);

	/**
	 * Get the correct option for the question
	 * @return   array|null
	 * @since    1.0.0
	 * @version  3.3.0
	 */
	public function get_correct_option() {
		$options = $this->get_options();
		foreach ( $options as $option ) {
			if ( $option['correct_option'] ) {
				return $option;
			}
		}
		return null;
	}

	/**
	 * Get the options for the question
	 * @return   array
	 * @since    1.0.0
	 * @version  3.3.0
	 */
	public function get_options() {
		$options = get_post_meta( $this->get( 'id' ), $this->meta_prefix . 'question_options', true );
		return $options ? $options : array();
	}

	/**
	 * Called before data is sorted and returned by $this->toArray()
	 * Extending classes should override this data if custom data should
	 * be added when object is converted to an array or json
	 * @param    array     $arr   array of data to be serialized
	 * @return   array
	 * @since    3.3.0
	 * @version  3.3.0
	 */
	protected function toArrayAfter( $arr ) {

		unset( $arr['author'] );
		unset( $arr['date'] );
		unset( $arr['excerpt'] );
		unset( $arr['menu_order'] );
		unset( $arr['modified'] );
		unset( $arr['status'] );

		$arr['options'] = $this->get_options();

		return $arr;

	}

}
