<?php
/**
 * LifterLMS Quiz Question
 *
 * @since    1.0.0
 * @version  3.12.0
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
	 * @version  3.9.0
	 */
	public function get_correct_option() {
		$options = $this->get_options();
		$key = $this->get_correct_option_key();
		if ( ! is_null( $key ) && isset( $options[ $key ] ) ) {
			return $options[ $key ];
		}
		return null;
	}

	/**
	 * Get the key of the correct option
	 * @return   int|null
	 * @since    3.9.0
	 * @version  3.9.0
	 */
	public function get_correct_option_key() {
		$options = $this->get_options();
		foreach ( $options as $key => $option ) {
			if ( $option['correct_option'] ) {
				return $key;
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
	 * Retrieve quizzes this quiz is assigned to
	 * @return   array              array of WP_Post IDs (quiz post types)
	 * @since    3.12.0
	 * @version  3.12.0
	 */
	public function get_quizzes() {

		$id = absint( $this->get( 'id' ) );
		$len = strlen( strval( $id ) );

		$str_like = '%' . sprintf( 's:2:"id";s:%1$d:"%2$s";', $len, $id ) . '%';
		$int_like = '%' . sprintf( 's:2:"id";i:%1$s;', $id ) . '%';

		global $wpdb;
		$query = $wpdb->get_col(
			"SELECT post_id
			 FROM {$wpdb->postmeta}
			 WHERE meta_key = '_llms_questions'
			   AND (
			   	      meta_value LIKE '{$str_like}'
			   	   OR meta_value LIKE '{$int_like}'
			   );"
		);

		return $query;

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
