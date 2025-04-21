<?php
/**
 * LifterLMS Quiz Question Model
 *
 * @package LifterLMS/Models/Classes
 *
 * @since 1.0.0
 * @version 7.8.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS Quiz Question Model class
 *
 * @property string $question_type Type of question.
 *
 * @since 1.0.0
 * @since 3.30.1 Fixed choice sorting issues.
 * @since 3.35.0 Escape `LIKE` clause when retrieving choices.
 * @since 3.38.2 When getting the 'not raw' question_type, made sure to always return a valid value.
 * @since 4.0.0 Remove deprecated class methods.
 */
class LLMS_Question extends LLMS_Post_Model {

	/**
	 * Database post type name
	 *
	 * @var string
	 */
	protected $db_post_type = 'llms_question';

	/**
	 * Modefl post type name
	 *
	 * @var string
	 */
	protected $model_post_type = 'question';

	/**
	 * Map of Model properties to property type
	 *
	 * @var array
	 */
	protected $properties = array(
		'content'                => 'html',
		'clarifications'         => 'html',
		'clarifications_enabled' => 'yesno',
		'description_enabled'    => 'yesno',
		'image'                  => 'array',
		'multi_choices'          => 'yesno',
		'parent_id'              => 'absint',
		'points'                 => 'absint',
		'question_type'          => 'string',
		'question'               => 'html',
		'title'                  => 'html',
		'video_enabled'          => 'yesno',
		'video_src'              => 'string',
	);

	/**
	 * Create a new question choice
	 *
	 * @since 3.16.0
	 *
	 * @param array $data Array of question choice data.
	 * @return string|boolean
	 */
	public function create_choice( $data ) {

		$data = wp_parse_args(
			$data,
			array(
				'choice'      => '',
				'choice_type' => 'text',
				'correct'     => false,
				'marker'      => $this->get_next_choice_marker(),
				'question_id' => $this->get( 'id' ),
			)
		);

		$choice = new LLMS_Question_Choice( $this->get( 'id' ) );
		if ( $choice->create( $data ) ) {
			return $choice->get( 'id' );
		}

		return false;
	}

	/**
	 * Delete a choice by ID
	 *
	 * @since 3.16.0
	 *
	 * @param string $id Choice ID.
	 * @return boolean
	 */
	public function delete_choice( $id ) {

		$choice = $this->get_choice( $id );
		if ( ! $choice ) {
			return false;
		}
		return $choice->delete();
	}

	/**
	 * Retrieve the type of automatic grading that can be performed on the question
	 *
	 * @since 3.16.0
	 *
	 * @return string|false
	 */
	public function get_auto_grade_type() {

		if ( $this->supports( 'choices' ) && $this->supports( 'grading', 'auto' ) ) {
			return 'choices';
		} elseif ( $this->supports( 'grading', 'conditional' ) && llms_parse_bool( $this->get( 'auto_grade' ) ) ) {
			return 'conditional';
		}

		return false;
	}


	/**
	 * An array of default arguments to pass to $this->create() when creating a new post
	 *
	 * @since 3.16.0
	 * @since 3.16.12 Unknown.
	 *
	 * @param array $args Args of data to be passed to wp_insert_post.
	 * @return array
	 */
	protected function get_creation_args( $args = null ) {

		// Allow nothing to be passed in.
		if ( empty( $args ) ) {
			$args = array();
		}

		// Backwards compat to original 3.0.0 format when just a title was passed in.
		if ( is_string( $args ) ) {
			$args = array(
				'post_title' => $args,
			);
		}

		if ( isset( $args['title'] ) ) {
			$args['post_title'] = $args['title'];
			unset( $args['title'] );
		}
		if ( isset( $args['content'] ) ) {
			$args['post_content'] = $args['content'];
			unset( $args['content'] );
		}

		$meta = isset( $args['meta_input'] ) ? $args['meta_input'] : array();

		$props = array_diff( array_keys( $this->get_properties() ), array_keys( $this->get_post_properties() ) );

		foreach ( $props as $prop ) {

			if ( isset( $args[ $prop ] ) ) {

				$meta[ $this->meta_prefix . $prop ] = $args[ $prop ];
				unset( $args[ $prop ] );

			}
		}

		$args['meta_input'] = wp_parse_args( $meta, $meta );

		$args = wp_parse_args(
			$args,
			array(
				'comment_status' => 'closed',
				'meta_input'     => array(),
				'menu_order'     => 1,
				'ping_status'    => 'closed',
				'post_author'    => get_current_user_id(),
				'post_content'   => '',
				'post_excerpt'   => '',
				'post_status'    => 'publish',
				'post_title'     => '',
				'post_type'      => $this->get( 'db_post_type' ),
			)
		);

		return apply_filters( "llms_{$this->model_post_type}_get_creation_args", $args, $this );
	}


	/**
	 * Getter
	 *
	 * @since 3.38.2
	 *
	 * @param string  $key The property key.
	 * @param boolean $raw Optional. Whether or not we need to get the raw value. Default false.
	 * @return mixed
	 */
	public function get( $key, $raw = false ) {

		$value = parent::get( $key, $raw );

		// When getting the 'not raw' value, make sure we always return a valid question type.
		if ( ! $raw && ! $value && 'question_type' === $key ) {
			$value = 'choice';
		}

		return $value;
	}

	/**
	 * Retrieve a choice by id
	 *
	 * @since 3.16.0
	 * @since 4.4.0 Use strict comparison.
	 *
	 * @param string $id Choice ID.
	 * @return obj|false
	 */
	public function get_choice( $id ) {
		$choice = new LLMS_Question_Choice( $this->get( 'id' ), $id );
		if ( $choice->exists() && absint( $this->get( 'id' ) ) === absint( $choice->get_question_id() ) ) {
			return $choice;
		}
		return false;
	}

	/**
	 * Retrieve the question's choices
	 *
	 * @since 3.16.0
	 * @since 3.30.1 Improve choice sorting to accommodate numeric markers.
	 * @since 3.35.0 Escape `LIKE` clause.
	 * @since 4.4.0 Don't allow objects when using `unserialize()`.
	 *
	 * @param string $return Optional. Determine how to return the choice data.
	 *                       'choices' (default) returns an array of LLMS_Question_Choice objects.
	 *                       'ids' returns an array of LLMS_Question_Choice ids.
	 * @return array
	 */
	public function get_choices( $return = 'choices' ) {

		global $wpdb;
		$results = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$wpdb->prepare(
				"SELECT meta_key AS id
				  , meta_value AS data
			 FROM {$wpdb->postmeta}
			 WHERE post_id = %d
			   AND meta_key LIKE %s
			;",
				$this->get( 'id' ),
				'_llms_choice_%'
			)
		);

		usort( $results, array( $this, 'sort_choices' ) );

		if ( 'ids' === $return ) {
			return wp_list_pluck( $results, 'id' );
		}

		$ret = array();
		foreach ( $results as $result ) {
			$ret[] = new LLMS_Question_Choice( $this->get( 'id' ), unserialize( $result->data, array( 'allowed_classes' => false ) ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize
		}

		return $ret;
	}

	/**
	 * Retrieve the question description (post_content)
	 *
	 * Add's extra allowed tags to wp_kses_post allowed tags so that async audio shortcodes will work properly
	 *
	 * @since 3.16.6
	 *
	 * @return string
	 */
	public function get_description() {

		global $allowedposttags;
		$allowedposttags['source'] = array(
			'src'  => true,
			'type' => true,
		);
		$desc                      = $this->get( 'content' );
		unset( $allowedposttags['source'] );

		return apply_filters( 'llms_' . $this->get( 'question_type' ) . '_question_get_description', $desc, $this );
	}

	/**
	 * Retrieve the correct values for a conditionally graded question
	 *
	 * @since 3.16.15
	 *
	 * @return array
	 */
	public function get_conditional_correct_value() {

		$correct = explode( '|', $this->get( 'correct_value' ) );
		$correct = array_map( 'trim', $correct );

		return $correct;
	}

	/**
	 * Retrieve correct choices for a given question
	 *
	 * @since 3.16.0
	 *
	 * @return array
	 */
	public function get_correct_choice() {

		$correct = false;

		if ( $this->supports( 'choices' ) && $this->supports( 'grading', 'auto' ) ) {

			$multi   = ( 'yes' === $this->get( 'multi_choices' ) );
			$correct = array();

			foreach ( $this->get_choices() as $choice ) {

				if ( $choice->is_correct() ) {
					$correct[] = $choice->get( 'id' );
					if ( ! $multi ) {
						break;
					}
				}
			}

			// Always sort multi choices for easy auto comparison.
			if ( $multi && $this->supports( 'selectable' ) ) {
				sort( $correct );
			}
		}

		return $correct;
	}

	/**
	 * Get the question text (title).
	 *
	 * @since 3.16.0
	 * @since 7.8.0 Added $attempt param.
	 *
	 * @param string            $format  Optional. Format of the question text. Accepts 'html' or 'plain'.
	 * @param LLMS_Quiz_Attempt $attempt Optional. Quiz Attempt object.
	 * @return string
	 */
	public function get_question( $format = 'html', $attempt = null ) {
		/**
		 * Filter the question text.
		 *
		 * The dynamic portion of this filter, `{$type}`, refers to the question type.
		 *
		 * @since 7.8.0
		 *
		 * @param string            $title    Question title.
		 * @param string            $format   Format of the question text. Accepts 'html' or 'plain'.
		 * @param LLMS_Question     $question Question object.
		 * @param LLMS_Quiz_Attempt $attempt  Attempt object.
		 */
		return apply_filters( "llms_{$this->get( 'question_type' )}_question_get_question", $this->get( 'title' ), $format, $this, $attempt );
	}

	/**
	 * Retrieve child questions (for question group)
	 *
	 * @since 3.16.0
	 *
	 * @todo Need to prevent access for non-group questions.
	 *
	 * @return array
	 */
	public function get_questions() {
		return $this->questions()->get_questions();
	}

	/**
	 * Retrieve URL for an image associated with the question if it's enabled
	 *
	 * @since 3.16.0
	 *
	 * @param string|array $size   Registered image size or a numeric array with width/height.
	 * @param null         $unused Unused parameter.
	 * @return string Source URL or an Eepty string if no image or not supported.
	 */
	public function get_image( $size = 'full', $unused = null ) {

		$url = '';

		if ( $this->has_image() ) {
			$img = $this->get( 'image' );
			if ( isset( $img['id'] ) && is_numeric( $img['id'] ) ) {
				$src = wp_get_attachment_image_src( $img['id'], $size );
				if ( $src ) {
					$url = $src[0];
				} elseif ( isset( $img['src'] ) ) {
					$url = $img['src'];
				}
			}
		}

		return apply_filters( 'llms_' . $this->get( 'question_type' ) . '_question_get_image', $url, $this );
	}

	/**
	 * Retrieve the next marker for question choices.
	 *
	 * @since 3.16.0
	 * @since 3.30.1 Fixed bug which caused the next marker to be 1 index too high.
	 * @since 7.4.1 Check `$type['choices']` is an array before trying to access it as such.
	 *
	 * @return string
	 */
	protected function get_next_choice_marker() {
		$next_index = count( $this->get_choices( 'ids', false ) );
		$type       = $this->get_question_type();
		if ( ! is_array( $type['choices'] ?? false ) ) {
			return false;
		}
		$markers = $type['choices']['markers'];
		return $next_index > count( $markers ) ? false : $markers[ $next_index ];
	}

	/**
	 * Retrieve question type data for the given question
	 *
	 * @since 3.16.0
	 *
	 * @return array
	 */
	public function get_question_type() {
		return llms_get_question_type( $this->get( 'question_type' ) );
	}

	/**
	 * Retrieve an instance of the questions parent LLMS_Quiz
	 *
	 * @since 3.16.0
	 *
	 * @return obj
	 */
	public function get_quiz() {
		return new LLMS_Quiz( $this->get( 'parent_id' ) );
	}

	/**
	 * Retrieve video embed for question featured video
	 *
	 * @since 3.16.0
	 * @since 3.17.0 Unknown.
	 *
	 * @return string
	 */
	public function get_video() {

		$html  = '';
		$embed = $this->get( 'video_src' );

		if ( $embed ) {

			// Get oembed.
			$html = wp_oembed_get( $embed );

			// Fallback to video shortcode.
			if ( ! $html ) {
				$html = do_shortcode( '[video src="' . $embed . '"]' );
			}
		}

		return apply_filters( 'llms_' . $this->get( 'question_type' ) . '_question_get_video', $html, $embed, $this );
	}

	/**
	 * Attempt to grade a question
	 *
	 * @since 3.16.0
	 * @since 3.16.15 Unknown.
	 * @since 4.4.0 Combined nested if statements into a single condition.
	 *
	 * @param array[] $answer Selected answer(s).
	 * @return string|null Returns `null` if the question cannot be automatically graded.
	 *                     Returns `yes` for correct answers and `no` for incorrect answers.
	 */
	public function grade( $answer ) {

		$question_type = $this->get( 'question_type' );

		/**
		 * Use this filter to bypass core grading for a given question type.
		 *
		 * If the filter returns a non-null value core grading is bypassed.
		 *
		 * The dynamic portion of this hook, `$question_type`, refers to the type of question being graded.
		 *
		 * @since 3.16.0
		 *
		 * @param null|string   $grade    Defaults to `null` which signifies that LifterLMS should attempt to grade the answer.
		 *                                Return `yes` (correct) or `no` (incorrect) to bypass core grading methods.
		 * @param string[]      $answer   User-submitted answers.
		 * @param LLMS_Question $question Question object.
		 */
		$grade = apply_filters( "llms_{$question_type}_question_pre_grade", null, $answer, $this );

		if ( is_null( $grade ) && $this->get( 'points' ) >= 1 ) {

			$grading_type = $this->get_auto_grade_type();

			if ( 'choices' === $grading_type ) {

				sort( $answer );
				$grade = ( $answer === $this->get_correct_choice() ) ? 'yes' : 'no';

			} elseif ( 'conditional' === $grading_type ) {

				$correct = $this->get_conditional_correct_value();

				/**
				 * Filter whether or not conditionally graded question answers are treated as a case-sensitive
				 *
				 * By default, case sensitivity is disabled.
				 *
				 * @since 3.16.15
				 *
				 * @param boolean       $case_sensitive Whether or not answers are treated as case-sensitive.
				 * @param string[]      $answer         User-submitted answers.
				 * @param string[]      $correct        Correct answers.
				 * @param LLMS_Question $question       Question object.
				 */
				if ( false === apply_filters( 'llms_quiz_grading_case_sensitive', false, $answer, $correct, $this ) ) {

					$answer  = array_map( 'strtolower', $answer );
					$correct = array_map( 'strtolower', $correct );

				}

				$answer  = array_map( 'trim', $answer );
				$correct = array_map( 'trim', $correct );

				$grade = ( $answer === $correct ) ? 'yes' : 'no';

			}
		}

		/**
		 * Filter the grading result of an answer for a given question type.
		 *
		 * The dynamic portion of this hook, `$question_type`, refers to the type of question being graded.
		 *
		 * @since 3.16.0
		 *
		 * @param null|string   $grade    Defaults to `null` which signifies that LifterLMS should attempt to grade the answer.
		 *                                Return `yes` (correct) or `no` (incorrect) to bypass core grading methods.
		 * @param string[]      $answer   User-submitted answers.
		 * @param LLMS_Question $question Question object.
		 */
		return apply_filters( "llms_{$question_type}_question_grade", $grade, $answer, $this );
	}

	/**
	 * Determine if a description is enabled and not empty
	 *
	 * @since 3.16.0
	 * @since 3.16.12 Unknown.
	 *
	 * @return bool
	 */
	public function has_description() {
		$enabled = $this->get( 'description_enabled' );
		$content = $this->get( 'content' );
		return ( 'yes' === $enabled && $content );
	}

	/**
	 * Determine if a featured image is enabled and not empty
	 *
	 * @since 3.16.0
	 *
	 * @return bool
	 */
	public function has_image() {
		$img = $this->get( 'image' );
		if ( is_array( $img ) ) {
			if ( ! empty( $img['enabled'] ) && ( ! empty( $img['id'] ) || ! empty( $img['src'] ) ) ) {
				return ( 'yes' === $img['enabled'] );
			}
		}
		return false;
	}

	/**
	 * Determine if a featured video is enabled & not empty
	 *
	 * @since 3.16.0
	 * @since 3.16.12 Unknown.
	 *
	 * @return bool
	 */
	public function has_video() {
		$enabled = $this->get( 'video_enabled' );
		$src     = $this->get( 'video_src' );
		return ( 'yes' === $enabled && $src );
	}

	/**
	 * Determine if the question is an orphan
	 *
	 * @since 3.27.0
	 *
	 * @return bool
	 */
	public function is_orphan() {

		$statuses  = array( 'publish', 'draft' );
		$parent_id = $this->get( 'parent_id' );

		if ( ! $parent_id ) {
			return true;
		} elseif ( ! in_array( get_post_status( $parent_id ), $statuses, true ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Access question manager (used for question groups)
	 *
	 * @since 3.16.0
	 *
	 * @todo Need to prevent access for non-group questions.
	 *
	 * @return obj
	 */
	public function questions() {
		return new LLMS_Question_Manager( $this );
	}

	/**
	 * Sort choices by marker.
	 *
	 * @since 3.30.1
	 * @since 4.4.0 Don't allow objects when using `unserialize()`.
	 *
	 * @param string $choice_a Serialized choice data.
	 * @param string $choice_b Serialized choice data.
	 * @return int
	 */
	private function sort_choices( $choice_a, $choice_b ) {
		$a_data = unserialize( $choice_a->data, array( 'allowed_classes' => false ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize
		$b_data = unserialize( $choice_b->data, array( 'allowed_classes' => false ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize
		return strnatcmp( $a_data['marker'], $b_data['marker'] );
	}

	/**
	 * Determine if the question supports a question feature
	 *
	 * @since 3.16.0
	 * @since 3.16.15 Unknown.
	 *
	 * @param string $feature Name of the feature (eg "choices").
	 * @param mixed  $option  Allow matching feature options.
	 * @return boolean
	 */
	public function supports( $feature, $option = null ) {

		$ret = false;

		$type = $this->get_question_type();
		if ( $type ) {
			if ( 'choices' === $feature ) {
				$ret = ( ! empty( $type['choices'] ) );
			} elseif ( 'grading' === $feature ) {
				$ret = ( $type['grading'] && $option === $type['grading'] );
			} elseif ( 'points' === $feature ) {
				$ret = $type['points'];
			} elseif ( 'random_lock' === $feature ) {
				$ret = $type['random_lock'];
			} elseif ( 'selectable' === $feature ) {
				$ret = empty( $type['choices'] ) ? false : $type['choices']['selectable'];
			}
		}

		/**
		 * Filter supported features of a given question type.
		 *
		 * The dynamic portion of this hook, `$this->get( 'question_type' )`, refers to the type of question
		 * being filtered.
		 *
		 * @since 3.16.0
		 *
		 * @param boolean       $ret      Return value.
		 * @param string        $string   Name of the feature being checked.
		 * @param string        $option   Name of the option being checked.
		 * @param LLMS_Question $question Instance of the LLMS_Question.
		 */
		return apply_filters( "llms_{$this->get( 'question_type' )}_question_supports", $ret, $feature, $option, $this );
	}

	/**
	 * Called before data is sorted and returned by $this->toArray()
	 *
	 * Extending classes should override this data if custom data should
	 * be added when object is converted to an array or json.
	 *
	 * @since 3.3.0
	 * @since 3.16.0 Unknown.
	 *
	 * @param array $arr Array of data to be serialized.
	 * @return array
	 */
	protected function toArrayAfter( $arr ) {

		unset( $arr['author'] );
		unset( $arr['date'] );
		unset( $arr['excerpt'] );
		unset( $arr['modified'] );
		unset( $arr['status'] );

		$choices = array();
		foreach ( $this->get_choices() as $choice ) {
			$choices[] = $choice->get_data();
		}
		$arr['choices'] = $choices;

		if ( 'group' === $this->get( 'question_type' ) ) {
			$arr['questions'] = array();
			foreach ( $this->get_questions() as $question ) {
				$arr['questions'][] = $question->toArray();
			}
		}

		return $arr;
	}

	/**
	 * Update a question choice
	 *
	 * If no id is supplied will create a new choice.
	 *
	 * @since 3.16.0
	 *
	 * @param array $data Array of choice data.
	 * @return string|boolean
	 */
	public function update_choice( $data ) {

		// If there's no ID, we'll add a new choice.
		if ( ! isset( $data['id'] ) ) {
			return $this->create_choice( $data );
		}

		// Get the question.
		$choice = $this->get_choice( $data['id'] );
		if ( ! $choice ) {
			return false;
		}

		$choice->update( $data )->save();

		// Return choice ID.
		return $choice->get( 'id' );
	}

	/**
	 * Retrieve quizzes this quiz is assigned to
	 *
	 * @since 3.12.0
	 *
	 * @return array Array of WP_Post IDs (quiz post types).
	 */
	public function get_quizzes() {

		$id  = absint( $this->get( 'id' ) );
		$len = strlen( strval( $id ) );

		$str_like = '%' . sprintf( 's:2:"id";s:%1$d:"%2$s";', $len, $id ) . '%';
		$int_like = '%' . sprintf( 's:2:"id";i:%1$s;', $id ) . '%';

		global $wpdb;
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$query = $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			"SELECT post_id
			 FROM {$wpdb->postmeta}
			 WHERE meta_key = '_llms_questions'
			   AND (
			   	      meta_value LIKE '{$str_like}'
			   	   OR meta_value LIKE '{$int_like}'
			   );"
		);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		return $query;
	}

	/**
	 * Don't add custom fields during toArray()
	 *
	 * @since 3.16.11
	 *
	 * @param array $arr Post model array.
	 * @return array
	 */
	protected function toArrayCustom( $arr ) {
		return $arr;
	}
}
