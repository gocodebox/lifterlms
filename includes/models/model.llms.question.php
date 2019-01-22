<?php
/**
 * LifterLMS Quiz Question
 *
 * @package  LifterLMS/Models
 * @since    1.0.0
 * @version  3.27.0
 *
 * @property  $question_type  (string)  type of question
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Question model.
 */
class LLMS_Question extends LLMS_Post_Model {

	protected $db_post_type = 'llms_question';
	protected $model_post_type = 'question';

	protected $properties = array(
		'content' => 'html',
		'clarifications' => 'html',
		'clarifications_enabled' => 'yesno',
		'description_enabled' => 'yesno',
		'image' => 'array',
		'multi_choices' => 'yesno',
		'parent_id' => 'absint',
		'points' => 'absint',
		'question_type' => 'string',
		'question' => 'html',
		'title' => 'html',
		'video_enabled' => 'yesno',
		'video_src' => 'string',
	);

	/**
	 * Create a new question choice
	 * @param    array     $data  array of question choice data
	 * @return   string|boolean
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	public function create_choice( $data ) {

		$data = wp_parse_args( $data, array(
			'choice' => '',
			'choice_type' => 'text',
			'correct' => false,
			'marker' => $this->get_next_choice_marker(),
			'question_id' => $this->get( 'id' ),
		) );

		$choice = new LLMS_Question_Choice( $this->get( 'id' ) );
		if ( $choice->create( $data ) ) {
			return $choice->get( 'id' );
		}

		return false;

	}

	/**
	 * Delete a choice by ID
	 * @param    string     $id  choice ID
	 * @return   boolean
	 * @since    3.16.0
	 * @version  3.16.0
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
	 * @return   string|false
	 * @since    3.16.0
	 * @version  3.16.0
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
	 * @param    array  $args   args of data to be passed to wp_insert_post
	 * @return   array
	 * @since    3.16.0
	 * @version  3.16.12
	 */
	protected function get_creation_args( $args = null ) {

		// allow nothing to be passed in
		if ( empty( $args ) ) {
			$args = array();
		}

		// backwards compat to original 3.0.0 format when just a title was passed in
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

		$args = wp_parse_args( $args, array(
			'comment_status' => 'closed',
			'meta_input'     => array(),
			'menu_order'     => 1,
			'ping_status'	 => 'closed',
			'post_author' 	 => get_current_user_id(),
			'post_content'   => '',
			'post_excerpt'   => '',
			'post_status' 	 => 'publish',
			'post_title'     => '',
			'post_type' 	 => $this->get( 'db_post_type' ),
		) );

		return apply_filters( 'llms_' . $this->model_post_type . '_get_creation_args', $args, $this );

	}

	/**
	 * Retrieve a choice by id
	 * @param    string     $id   Choice ID
	 * @return   obj|false
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	public function get_choice( $id ) {
		$choice = new LLMS_Question_Choice( $this->get( 'id' ), $id );
		if ( $choice->exists() && $this->get( 'id' ) == $choice->get_question_id() ) {
			return $choice;
		}
		return false;
	}

	/**
	 * Retrieve the question's choices
	 * @param    string     $return  return type [choices|ids]
	 * @return   array
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	public function get_choices( $return = 'choices' ) {

		// $query = wp_cache_get( $this->get_choice_cache_key(), 'llms' );

		// if ( false === $query ) {

			global $wpdb;
			$query = $wpdb->get_results( $wpdb->prepare(
				"SELECT meta_key AS id
					  , meta_value AS data
				 FROM {$wpdb->postmeta}
				 WHERE post_id = %d
				   AND meta_key LIKE '_llms_choice_%'
				;", $this->get( 'id' )
			) );

			usort( $query, function( $a, $b ) {
				$adata = unserialize( $a->data );
				$bdata = unserialize( $b->data );
				return strcmp( $adata['marker'], $bdata['marker'] );
			} );

			// wp_cache_set( $this->get_choice_cache_key(), $query, 'llms' );

		// }

		if ( 'ids' === $return ) {
			return wp_list_pluck( $query, 'id' );
		}

		$ret = array();
		foreach ( $query as $result ) {
			$ret[] = new LLMS_Question_Choice( $this->get( 'id' ), unserialize( $result->data ) );
		}

		return $ret;

	}

	/**
	 * Retrieve the question description (post_content)
	 * Add's extra allowed tags to wp_kses_post allowed tags so that async audio shortcodes will work properly
	 * @return   string
	 * @since    3.16.6
	 * @version  3.16.6
	 */
	public function get_description() {

		global $allowedposttags;
		$allowedposttags['source'] = array(
			'src' => true,
			'type' => true,
		);
		$desc = $this->get( 'content' );
		unset( $allowedposttags['source'] );

		return apply_filters( 'llms_' . $this->get( 'question_type' ) . '_question_get_description', $desc, $this );

	}

	/**
	 * Retrieve the correct values for a conditionally graded question
	 * @return   array
	 * @since    3.16.15
	 * @version  3.16.15
	 */
	public function get_conditional_correct_value() {

		$correct = explode( '|', $this->get( 'correct_value' ) );
		$correct = array_map( 'trim', $correct );

		return $correct;

	}

	/**
	 * Retrieve correct choices for a given question
	 * @return   array
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	public function get_correct_choice() {

		$correct = false;

		if ( $this->supports( 'choices' ) && $this->supports( 'grading', 'auto' ) ) {

			$multi = ( 'yes' === $this->get( 'multi_choices' ) );
			$correct = array();

			foreach ( $this->get_choices() as $choice ) {

				if ( $choice->is_correct() ) {
					$correct[] = $choice->get( 'id' );
					if ( ! $multi ) {
						break;
					}
				}
			}

			// always sort multi choices for easy auto comparison
			if ( $multi && $this->supports( 'selectable' ) ) {
				sort( $correct );
			}
		}

		return $correct;

	}

	/**
	 * Get the question text (title)
	 * @return   string
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	public function get_question( $format = 'html' ) {
		return apply_filters( 'llms_' . $this->get( 'question_type' ) . '_question_get_question', $this->get( 'title' ), $format, $this );
	}

	/**
	 * Retrieve child questions (for question group)
	 * @todo     need to prevent access for non-group questions...
	 * @return   array
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	public function get_questions() {
		return $this->questions()->get_questions();
	}

	/**
	 * Retrieves an object cache key for the question's choices
	 * @return   string
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	private function get_choice_cache_key() {

		return sprintf( 'question_%d_choices', $this->get( 'id' ) );

	}

	/**
	 * Retrieve URL for an image associated with the question if it's enabled
	 * @param    string|array   $size  registered image size or a numeric array with width/height
	 * @return   string                empty string if no image or not supported
	 * @since    3.16.0
	 * @version  3.16.0
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
	 * Retrieve the next marker for question choices
	 * @return   string
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	protected function get_next_choice_marker() {
		$next_index = count( $this->get_choices( 'ids', false ) ) + 1;
		$type = $this->get_question_type();
		$markers = $type['choices']['markers'];
		return $next_index > count( $markers ) ? false : $markers[ $next_index ];
	}

	/**
	 * Retrieve question type data for the given question
	 * @return   array
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	public function get_question_type() {
		return  llms_get_question_type( $this->get( 'question_type' ) );
	}

	/**
	 * Retrieve an instance of the questions parent LLMS_Quiz
	 * @return   obj
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	public function get_quiz() {
		return new LLMS_Quiz( $this->get( 'parent_id' ) );
	}

	/**
	 * Retrieve video embed for question featured video
	 * @return   string
	 * @since    3.16.0
	 * @version  3.17.0
	 */
	public function get_video() {

		$html = '';
		$embed = $this->get( 'video_src' );

		if ( $embed ) {

			// get oembed
			$html = wp_oembed_get( $embed );

			// fallback to video shortcode
			if ( ! $html ) {
				$html = do_shortcode( '[video src="' . $embed . '"]' );
			}
		}

		return apply_filters( 'llms_' . $this->get( 'question_type' ) . '_question_get_video', $html, $embed, $this );

	}

	/**
	 * Attempt to grade a question
	 * @param    array     $answer  selected answer(s)
	 * @return   mixed     yes = correct
	 *                     no  = incorrect
	 *                     null = not auto gradeable
	 * @since    3.16.0
	 * @version  3.16.15
	 */
	public function grade( $answer ) {

		/**
		 * Allow 3rd parties to do custom grading
		 * If filter returns non-null will bypass core grading
		 */
		$grade = apply_filters( 'llms_' . $this->get( 'question_type' ) . '_question_pre_grade', null, $answer, $this );

		if ( is_null( $grade ) ) {

			if ( $this->get( 'points' ) >= 1 ) {

				$grading_type = $this->get_auto_grade_type();

				if ( 'choices' === $grading_type ) {

					sort( $answer );
					$grade = ( $answer === $this->get_correct_choice() ) ? 'yes' : 'no';

				} elseif ( 'conditional' === $grading_type ) {

					$correct = $this->get_conditional_correct_value();

					// allow case sensitivity to be enabled if required
					if ( false === apply_filters( 'llms_quiz_grading_case_sensitive', false, $answer, $correct, $this ) ) {

						$answer = array_map( 'strtolower', $answer );
						$correct = array_map( 'strtolower', $correct );

					}

					$grade = ( $answer === $correct ) ? 'yes' : 'no';

				}
			}
		}

		return apply_filters( 'llms_' . $this->get( 'question_type' ) . '_question_grade', $grade, $answer, $this );

	}

	/**
	 * Determine if a description is enabled and not empty
	 * @return   bool
	 * @since    3.16.0
	 * @version  3.16.12
	 */
	public function has_description() {
		$enabled = $this->get( 'description_enabled' );
		$content = $this->get( 'content' );
		return ( 'yes' === $enabled && $content );
	}

	/**
	 * Determine if a featured image is enabled and not empty
	 * @return   bool
	 * @since    3.16.0
	 * @version  3.16.0
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
	 * @return   bool
	 * @since    3.16.0
	 * @version  3.16.12
	 */
	public function has_video() {
		$enabled = $this->get( 'video_enabled' );
		$src = $this->get( 'video_src' );
		return ( 'yes' === $enabled && $src );
	}

	/**
	 * Determine if the question is an orphan
	 * @return   bool
	 * @since    3.27.0
	 * @version  3.27.0
	 */
	public function is_orphan() {

		$statuses = array( 'publish', 'draft' );
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
	 * @todo     need to prevent access for non-group questions...
	 * @return   obj
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	public function questions() {
		return new LLMS_Question_Manager( $this );
	}

	/**
	 * Determine if the question supports a question feature
	 * @param    string     $feature  name of the feature (eg "choices")
	 * @param    mixed      $option   allow matching feauture options
	 * @return   boolean
	 * @since    3.16.0
	 * @version  3.16.15
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
		 * @filter   llms_{$question_type}_question_supports
		 * @param    boolean   $ret      return value
		 * @param    string    $string   name of the feature being checked (eg "choices")
		 * @param    obj       $this     instance of the LLMS_Question
		 * @usage    apply_filters( 'llms_choice_question_supports', function( $ret, $feature, $option, $question ) {
		 *           	return $ret;
		 *           }, 10, 4 );
		 */
		return apply_filters( 'llms_' . $this->get( 'question_type' ) . '_question_supports', $ret, $feature, $option, $this );

	}

	/**
	 * Called before data is sorted and returned by $this->toArray()
	 * Extending classes should override this data if custom data should
	 * be added when object is converted to an array or json
	 * @param    array     $arr   array of data to be serialized
	 * @return   array
	 * @since    3.3.0
	 * @version  3.16.0
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
	 * if no id is supplied will create a new choice
	 * @param    array     $data  array of choice data (see $this->create_choice())
	 * @return   string|boolean
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	public function update_choice( $data ) {

		// if there's no ID, we'll add a new choice
		if ( ! isset( $data['id'] ) ) {
			return $this->create_choice( $data );
		}

		// get the question
		$choice = $this->get_choice( $data['id'] );
		if ( ! $choice ) {
			return false;
		}

		$choice->update( $data )->save();

		// return choice ID
		return $choice->get( 'id' );

	}





















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
	 * Don't add custom fields during toArray()
	 * @param    array     $arr  post model array
	 * @return   array
	 * @since    3.16.11
	 * @version  3.16.11
	 */
	protected function toArrayCustom( $arr ) {
		return $arr;
	}

	/*
		       /$$                                                               /$$                     /$$
		      | $$                                                              | $$                    | $$
		  /$$$$$$$  /$$$$$$   /$$$$$$   /$$$$$$   /$$$$$$   /$$$$$$$  /$$$$$$  /$$$$$$    /$$$$$$   /$$$$$$$
		 /$$__  $$ /$$__  $$ /$$__  $$ /$$__  $$ /$$__  $$ /$$_____/ |____  $$|_  $$_/   /$$__  $$ /$$__  $$
		| $$  | $$| $$$$$$$$| $$  \ $$| $$  \__/| $$$$$$$$| $$        /$$$$$$$  | $$    | $$$$$$$$| $$  | $$
		| $$  | $$| $$_____/| $$  | $$| $$      | $$_____/| $$       /$$__  $$  | $$ /$$| $$_____/| $$  | $$
		|  $$$$$$$|  $$$$$$$| $$$$$$$/| $$      |  $$$$$$$|  $$$$$$$|  $$$$$$$  |  $$$$/|  $$$$$$$|  $$$$$$$
		 \_______/ \_______/| $$____/ |__/       \_______/ \_______/ \_______/   \___/   \_______/ \_______/
		                    | $$
		                    | $$
		                    |__/
	*/

	/**
	 * Get the options for the question
	 * @return     array
	 * @since      1.0.0
	 * @version    3.16.0
	 * @deprecated 3.16.0
	 */
	public function get_options() {

		llms_deprecated_function( 'LLMS_Question::get_options()', '3.16.0', 'LLMS_Question::get_choices()' );
		return $this->get_choices();

	}


}
