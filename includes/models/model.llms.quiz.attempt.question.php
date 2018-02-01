<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Quiz Attempt Answer Question
 * @since   [version]
 * @version [version]
 */
class LLMS_Quiz_Attempt_Question {

	private $data = array();

	/**
	 * Constructor
	 * @param    array      $data   question data array from attempt record
	 * @since    [version]
	 * @version  [version]
	 */
	public function __construct( $data = array() ) {

		$this->data = wp_parse_args( $data, array(
			'id' => null,
			'points' => null,
			'remarks' => null,
			'answer' => null,
			'correct' => null,
		) );

		// var_dump( $this->data );

	}

	/**
	 * Getter
	 * @param    string     $key      data key name
	 * @param    mixed      $default  default fallback value if key is unset
	 * @return   mixed
	 * @since    [version]
	 * @version  [version]
	 */
	public function get( $key, $default = '' ) {
		if ( isset( $this->data[ $key ] ) ) {
			return $this->data[ $key ];
		}
		return $default;
	}

	/**
	 * Retrieve anwser HTML for the question
	 * @return   string
	 * @since    [version]
	 * @version  [version]
	 */
	public function get_answer() {

		$ret = '';
		$question = $this->get_question();
		$answers = $this->get( 'answer' );

		if ( $answers  ) {

			if ( $question->supports( 'choices' ) && $question->supports( 'grading', 'auto' ) ) {

				foreach ( $answers as $aid ) {

					$choice = $question->get_choice( $aid );
					$ret .= $choice->get_choice();

				}

			} else {

				$ret = implode( ', ', array_map( 'wp_kses_post', $answers ) );

			}

		}

		return $ret;

	}

	/**
	 * Retrieve the number of points earned for the question
	 * @return   int
	 * @since    [version]
	 * @version  [version]
	 */
	public function get_earned_points() {
		return $this->is_correct() ? $this->get( 'points' ) : 0;
	}

	/**
	 * Retrieve an instance of the LLMS_Question
	 * @return   obj
	 * @since    [version]
	 * @version  [version]
	 */
	public function get_question() {
		return llms_get_post( $this->get( 'id' ) );
	}

	/**
	 * Retrieve the status icon HTML based on the question's status/answer
	 * @return   string
	 * @since    [version]
	 * @version  [version]
	 */
	public function get_status_icon() {

		$icon = '';

		switch ( $this->get_status() ) {

			case 'graded':
				if ( $this->is_correct() ) {
					$icon = 'check';
					$tip = esc_attr__( 'Correct answer', 'lifterlms' );
				} else {
					$icon = 'times';
					$tip = esc_attr__( 'Incorrect answer', 'lifterlms' );
				}
			break;
			case 'waiting':
				$icon = 'clock-o';
				$tip = esc_attr__( 'Awaiting review', 'lifterlms' );
			break;

		}

		if ( $icon  ) {
			return sprintf( '<span class="llms-status-icon-tip tip--top-left" data-tip="%1$s"><i class="llms-status-icon fa fa-%2$s"></i><span>', $tip, $icon );
		}

		return '';

	}

	/**
	 * Receive the graded status of the question
	 * @return   string      [graded|waiting|none]
	 * @since    [version]
	 * @version  [version]
	 */
	public function get_status() {
		$question = $this->get_question();
		if ( $question->get_auto_grade_type() ) {
			return 'graded';
		} elseif ( $question->supports( 'grading', 'manual' ) ) {
			if ( ! $this->get( 'correct' ) ) {
				return 'waiting';
			} else {
				return 'graded';
			}
		}
		return 'none';
	}

	public function is_correct() {

		if ( 'graded' === $this->get_status() ) {
			return llms_parse_bool( $this->get( 'correct' ) );
		}

		return false;

	}

	/**
	 * Setter
	 * @param    string    $key  data key name
	 * @param    mixed     $val  value
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function set( $key, $val ) {
		$this->data[ $key ] = $val;
	}

}
