<?php
/**
 * Quiz Attempt Answer Question
 *
 * @package LifterLMS/Models/Classes
 *
 * @since 3.16.0
 * @version 5.3.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Quiz_Attempt_Question model class
 *
 * @since 3.16.0
 * @since 3.16.15 Unknown.
 */
class LLMS_Quiz_Attempt_Question {

	private $data = array();

	/**
	 * Constructor
	 *
	 * @since 3.16.0
	 *
	 * @param array $data Question data array from attempt record.
	 * @return void
	 */
	public function __construct( $data = array() ) {

		$this->data = wp_parse_args(
			$data,
			array(
				'id'      => null,
				'earned'  => null,
				'points'  => null,
				'remarks' => null,
				'answer'  => null,
				'correct' => null,
			)
		);

	}

	/**
	 * Determine if it's possible to manually grade the question
	 *
	 * @since 3.16.8
	 * @since 3.16.9 Unknown.
	 * @since 5.3.0 Early bail for deleted questions.
	 *
	 * @return boolean
	 */
	public function can_be_manually_graded() {

		$question = $this->get_question();

		if ( $question && $this->get( 'points' ) >= 1 ) {

			// The question is auto-gradable so it cannot be manually graded.
			if ( $question->get_auto_grade_type() ) {
				return false;
			} elseif ( $question->supports( 'grading', 'manual' ) || $question->supports( 'grading', 'conditional' ) ) {
				return true;
			}
		}

		return false;

	}

	/**
	 * Getter
	 *
	 * @since 3.16.0
	 *
	 * @param string $key     Data key name
	 * @param mixed  $default Optional. Default fallback value if key is unset. Default is empty string.
	 * @return mixed
	 */
	public function get( $key, $default = '' ) {
		if ( isset( $this->data[ $key ] ) ) {
			return $this->data[ $key ];
		}
		return $default;
	}

	/**
	 * Retrieve answer HTML for the question answers
	 *
	 * @since 3.16.0
	 * @since 3.16.15 Unknown.
	 *
	 * @return string
	 */
	public function get_answer() {

		$question = $this->get_question();
		$answers  = $this->get_answer_array();
		$ret      = apply_filters( 'llms_quiz_attempt_question_get_answer_pre', '', $answers, $question, $this );

		if ( ! $ret ) {

			if ( $answers ) {

				$ret = '<ul class="llms-quiz-attempt-answers">';
				foreach ( $answers as $answer ) {
					$ret .= sprintf( '<li class="llms-quiz-attempt-answer">%s</li>', wp_kses_post( $answer ) );
				}
				$ret .= '</ul>';

			}
		}

		return apply_filters( 'llms_quiz_attempt_question_get_answer', $ret, $answers, $question, $this );

	}

	/**
	 * Get answer(s) as an array
	 *
	 * @since 3.16.15
	 * @since 3.27.0 Unknown.
	 *
	 * @return array
	 */
	public function get_answer_array() {

		$ret      = array();
		$question = $this->get_question();
		$answers  = $this->get( 'answer' );

		if ( $answers ) {

			if ( $question->supports( 'choices' ) && $question->supports( 'grading', 'auto' ) ) {

				foreach ( $answers as $aid ) {

					$choice = $question->get_choice( $aid );
					$ret[]  = $choice ? $choice->get_choice() : _x( '[Deleted]', 'Selected quiz choice has been deleted.', 'lifterlms' );

				}
			} else {

				$ret = $answers;

			}
		}

		return apply_filters( 'llms_quiz_attempt_question_get_answer_array', $ret, $answers, $question, $this );

	}

	/**
	 * Retrieve answer HTML for the question correct answers
	 *
	 * @since 3.16.0
	 * @since 3.16.15 Unknown.
	 *
	 * @return string
	 */
	public function get_correct_answer() {

		$ret     = '';
		$answers = $this->get_correct_answer_array();

		if ( $answers ) {

			$ret = '<ul class="llms-quiz-attempt-answers">';
			foreach ( $answers as $answer ) {
				$ret .= sprintf( '<li class="llms-quiz-attempt-answer">%s</li>', wp_kses_post( $answer ) );
			}
			$ret .= '</ul>';

		}

		return apply_filters( 'llms_quiz_attempt_question_get_correct_answer', $ret, $answers, $this->get_question(), $this );

	}

	/**
	 * Get correct answer(s) as an array
	 *
	 * @since 3.16.15
	 *
	 * @return array
	 */
	public function get_correct_answer_array() {

		$ret      = array();
		$question = $this->get_question();
		$type     = $question->get_auto_grade_type();

		if ( 'choices' === $type ) {

			foreach ( $question->get_correct_choice() as $aid ) {
				$choice = $question->get_choice( $aid );
				$ret[]  = $choice->get_choice();
			}
		} elseif ( 'conditional' === $type ) {

			$ret = $question->get_conditional_correct_value();

		}

		return apply_filters( 'llms_quiz_attempt_question_get_correct_answer_array', $ret, $question, $this );

	}

	/**
	 * Retrieve an instance of the LLMS_Question
	 *
	 * @since 3.16.0
	 *
	 * @return LLMS_Question
	 */
	public function get_question() {
		return llms_get_post( $this->get( 'id' ) );
	}

	/**
	 * Retrieve the status icon HTML based on the question's status/answer
	 *
	 * @since 3.16.0
	 *
	 * @return string
	 */
	public function get_status_icon() {

		$icon = '';

		switch ( $this->get_status() ) {

			case 'graded':
				if ( $this->is_correct() ) {
					$icon = 'check';
					$tip  = esc_attr__( 'Correct answer', 'lifterlms' );
				} else {
					$icon = 'times';
					$tip  = esc_attr__( 'Incorrect answer', 'lifterlms' );
				}
				break;
			case 'waiting':
				$icon = 'clock-o';
				$tip  = esc_attr__( 'Awaiting review', 'lifterlms' );
				break;

		}

		if ( $icon ) {
			return sprintf( '<span class="llms-status-icon-tip tip--top-left" data-tip="%1$s"><i class="llms-status-icon fa fa-%2$s"></i><span>', $tip, $icon );
		}

		return '';

	}

	/**
	 * Receive the graded status of the question
	 *
	 * @since 3.16.0
	 * @since 3.16.9 Unknown.
	 * @since 5.3.0 Account for deleted questions.
	 *
	 * @return string Attempt's question status [graded|waiting|none].
	 */
	public function get_status() {

		$question = $this->get_question();

		if ( ! $question ) {
			return 'graded';
		}

		$status = 'none';

		if ( $this->get( 'points' ) >= 1 ) {

			if ( $question->get_auto_grade_type() ) {

				$status = 'graded';

			} elseif ( $question->supports( 'grading', 'manual' ) || $question->supports( 'grading', 'conditional' ) ) {

				if ( ! $this->get( 'correct' ) ) {
					$status = 'waiting';
				} else {
					$status = 'graded';
				}
			}
		}

		return $status;
	}

	/**
	 * Determine if remarks are available for the question
	 *
	 * @since 3.16.0
	 *
	 * @return bool
	 */
	public function has_remarks() {

		return ( $this->get( 'remarks' ) );

	}

	/**
	 * Determine if a question is correct
	 *
	 * @since 3.16.8
	 *
	 * @return bool
	 */
	public function is_correct() {

		if ( 'graded' === $this->get_status() ) {
			return llms_parse_bool( $this->get( 'correct' ) );
		}

		return false;

	}

	/**
	 * Setter
	 *
	 * @since 3.16.0
	 *
	 * @param string $key Data key name.
	 * @param mixed  $val Value.
	 * @return void
	 */
	public function set( $key, $val ) {
		$this->data[ $key ] = $val;
	}

}
