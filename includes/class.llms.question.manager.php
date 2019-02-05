<?php
/**
 * LifterLMS Quiz Question Manager
 * Don't instantiate this directly, instead use the wrapper functions
 * found in the LLMS_Quiz and LLMS_Question classes
 * @since    3.16.0
 * @version  3.27.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Question_Manager class.
 */
class LLMS_Question_Manager {

	/**
	 * Constructor
	 * @param    obj     $parent  instance of the parent LLMS_Quiz or LLMS_Question
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	public function __construct( $parent ) {

		$this->parent = $parent;

	}

	/**
	 * Quick access to the parent attribute
	 * @return   [type]
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	private function get_parent() {
		return $this->parent;
	}

	/**
	 * Quick access to parents type property
	 * @return   string    [llms_quiz|llms_question]
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	private function get_parent_type() {
		return $this->parent->get( 'type' );
	}

	/**
	 * Retrieve the related LLMS_Quiz
	 * @return   obj
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	private function get_quiz() {

		if ( 'llms_quiz' === $this->get_parent_type() ) {
			return $this->parent;
		}
		// llms_question
		return $this->parent->get_quiz();

	}

	/**
	 * Create a new question and add it to the quiz
	 * @param    array      $data  array of question data
	 * @return   false|question id
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	public function create_question( $data = array() ) {

		// ensure the question belongs to this quiz
		$data['parent_id'] = $this->get_parent()->get( 'id' );

		$question = new LLMS_Question( 'new', $data );
		if ( $question->get( 'id' ) ) {
			return $question->get( 'id' );
		}

		return false;

	}

	/**
	 * Delete a question associated with this quiz
	 * skips trash and force deletes the question
	 * @param    int     $id  WP Post ID of a question (must be associated with this quiz)
	 * @return   boolean      true = deleted, false = error
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	public function delete_question( $id ) {

		$question = $this->get_question( $id );
		if ( ! $question ) {
			return false;
		}

		// error
		if ( ! wp_delete_post( $id, true ) ) {
			return false;
		}

		// deleted
		return true;

	}

	/**
	 * Retrieve a question associated with this quiz by question ID
	 * @param    int     $id  WP Post ID of the question
	 * @return   boolean
	 * @since    3.16.0
	 * @version  3.27.0
	 */
	public function get_question( $id ) {

		$question = llms_get_post( $id );

		// not valid question, return false
		if ( empty( $question ) || ! is_a( $question, 'LLMS_Question' ) ) {
			return false;
		}

		$parent_id = $question->get( 'parent_id' );

		// when parent id is set, only retrieve questions attached to this parent
		if ( $parent_id && $parent_id !== $this->get_parent()->get( 'id' ) ) {

			if ( 'llms_question' === $this->get_parent_type() && $this->get_quiz()->get( 'id' ) === $question->get_quiz()->get( 'id' ) ) {
				return $question;
			}

			return false;
		}

		// success
		return $question;

	}

	/**
	 * Get questions
	 * @param    string  $return  type of return [ids|posts|questions]
	 * @return   array
	 * @since    3.3.0
	 * @version  3.24.0
	 */
	public function get_questions( $return = 'questions' ) {

		$query = new WP_Query( array(
			'meta_query' => array(
				array(
					'key' => '_llms_parent_id',
					'value' => $this->get_parent()->get( 'id' ),
				),
			),
			'order' => 'ASC',
			'orderby' => 'menu_order',
			'post_status' => 'publish',
			'post_type' => 'llms_question',
			'posts_per_page' => 500,
		) );

		if ( 'ids' === $return ) {
			$ret = wp_list_pluck( $query->posts, 'ID' );
		} elseif ( 'posts' === $return ) {
			$ret = $query->posts;
		} else {
			$ret = array();
			foreach ( $query->posts as $post ) {
				$ret[] = new LLMS_Question( $post );
			}
		}

		return apply_filters( 'llms_quiz_get_questions', $ret, $this, $return );

	}

	/**
	 * Create or update questions
	 * If 'id' passed in $data array will update existing question
	 * Omit 'id' to create a new question
	 * @param    array      $data  array of question data
	 * @return   false|question id
	 * @since    3.16.0
	 * @version  3.17.2
	 */
	public function update_question( $data = array() ) {

		// if there's no ID, we'll add a new question
		if ( ! isset( $data['id'] ) ) {
			return $this->create_question( $data );
		}

		// get the question
		$question = $this->get_question( $data['id'] );
		if ( ! $question ) {
			return false;
		}

		// update all submitted data
		foreach ( $data as $key => $val ) {

			// merge image data into the array
			if ( 'image' === $key ) {
				$val = array_merge( array(
					'enabled' => 'no',
					'id' => '',
					'src' => '',
				), $question->get( $key ), $val );
			}

			$question->set( $key, $val );
		}

		// return question ID
		return $question->get( 'id' );

	}


}
