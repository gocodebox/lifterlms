<?php
/**
 * LifterLMS Quiz Model
 * @since    3.3.0
 * @version  3.12.0
 *
 * @property  $allowed_attempts  (int)  Number of times a student is allowed to take the quiz before being locked out of it
 * @property  $passing_percent  (float)  Grade required for a student to "pass" the quiz
 * @property  $random_answers  (yesno)  Whether or not to randomize the order of answers to the quiz questions
 * @property  $random_questions  (yesno)  Whether or not to randomize the order of questions for each attempt
 * @property  $show_correct_answer  (yesno)  Whether or not to show the correct answer(s) to students on the quiz results screen
 * @property  $show_options_description_right_answer  (yesno)  If yes, displays the question description when the student chooses the correct answer
 * @property  $show_options_description_wrong_answer  (yesno)  If yes, displays the question description when the student chooses the wrong answer
 * @property  $show_results  (yesno)  If yes, results will be shown to the student at the conclusion of the quiz
 * @property  $time_limit  (int)  Quiz time limit (in minutes), empty denotes unlimited (untimed) quiz
 */
class LLMS_QQuiz extends LLMS_Post_Model {

	protected $db_post_type = 'llms_quiz';
	protected $model_post_type = 'quiz';

	protected $properties = array(
		'allowed_attempts' => 'int',
		'passing_percent' => 'float',
		'random_answers' => 'yesno',
		'random_questions' => 'yesno',
		'show_correct_answer' => 'yesno',
		'show_options_description_right_answer' => 'yesno',
		'show_options_description_wrong_answer' => 'yesno',
		'show_results' => 'yesno',
		'time_limit' => 'int',
	);

	/**
	 * Retrieve lessons this quiz is assigned to
	 * @param    string    $return  format of the return [ids|lessons]
	 * @return   array              array of WP_Post IDs (lesson post types)
	 * @since    3.12.0
	 * @version  3.12.0
	 */
	public function get_lessons( $return = 'ids' ) {

		global $wpdb;
		$query = $wpdb->get_col( $wpdb->prepare(
			"SELECT post_id
			 FROM {$wpdb->postmeta}
			 WHERE meta_key = '_llms_assigned_quiz'
			   AND meta_value = %d;",
			$this->get( 'id' )
		) );

		// return just the ids
		if ( 'ids' === $return ) {
			return $query;
		}

		// setup lesson objects
		$ret = array();
		foreach ( $query as $id ) {
			$ret[] = llms_get_post( $id );
		}
		return $ret;

	}

	/**
	 * Get the (points) value of a question
	 * @param    int     $question_id  WP Post ID of the LLMS_Question
	 * @return   int
	 * @since    3.3.0
	 * @version  3.3.0
	 */
	public function get_question_value( $question_id ) {

		foreach ( $this->get_questions_raw() as $q ) {
			if ( $question_id == $q['id'] ) {
				return absint( $q['points'] );
			}
		}

		return 0;

	}

	/**
	 * Get questions
	 * @param    string  $return  type of return [ids|posts|questions]
	 * @return   array
	 * @since    3.3.0
	 * @version  3.3.0
	 */
	public function get_questions( $return = 'questions' ) {

		$questions = $this->get_questions_raw();

		if ( $return === 'ids' ) {
			$r = wp_list_pluck( $questions, 'id' );
		} else {
			$r = array();
			foreach ( $questions as $q ) {
				if ( $return === 'posts' ) {
					$r[] = new WP_Post( $q['id'] );
				} else {
					$r[] = new LLMS_Question( $q['id'] );
				}
			}
		}

		return $r;

	}

	/**
	 * Retrieve the array of raw question data from the postmeta table
	 * @return   array
	 * @since    3.3.0
	 * @version  3.3.0
	 */
	private function get_questions_raw() {

		$q = get_post_meta( $this->get( 'id' ), $this->meta_prefix . 'questions', true );
		return $q ? $q : array();

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

		$arr['questions'] = array();
		foreach ( $this->get_questions() as $q ) {
			$qdata = $q->toArray();
			$qdata['value'] = $this->get_question_value( $q->get( 'id' ) );
			$arr['questions'][] = $qdata;
		}

		return $arr;

	}

}
