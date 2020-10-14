<?php
/**
 * Update functions for version 3.16.0
 *
 * @package LifterLMS/Functions/Updates
 *
 * @since 3.39.0
 * @version 3.39.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Add yes/no vals for quiz new quiz settings
 *
 * @since 3.16.0
 *
 * @return void
 */
function llms_update_3160_update_quiz_settings() {

	global $wpdb;
	$ids = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'llms_quiz'" );

	foreach ( $ids as $id ) {

		$quiz = llms_get_post( $id );

		if ( $quiz->get( 'time_limit' ) > 0 ) {
			$quiz->set( 'limit_time', 'yes' );
		}

		if ( $quiz->get( 'allowed_attempts' ) > 0 ) {
			$quiz->set( 'limit_attempts', 'yes' );
		}
	}

}

/**
 * Rename meta keys for lesson -> quiz relationship
 *
 * @since 3.16.0
 *
 * @return void
 */
function llms_update_3160_lesson_to_quiz_relationships_migration() {

	global $wpdb;
	$wpdb->update(
		$wpdb->postmeta,
		array(
			'meta_key' => '_llms_quiz',
		),
		array(
			'meta_key' => '_llms_assigned_quiz',
		)
	); // db call ok; no-cache ok.

}

/**
 * Migrate attempt data from the former location on the wp_usermeta table
 *
 * @since 3.16.0
 * @since 3.24.1 Unknown.
 *
 * @return void
 */
function llms_update_3160_attempt_migration() {

	global $wpdb;
	$query = $wpdb->get_results( "SELECT user_id, meta_value FROM {$wpdb->usermeta} WHERE meta_key = 'llms_quiz_data' LIMIT 100;" ); // db call ok; no-cache ok.

	// Finished.
	if ( ! $query ) {
		set_transient( 'llms_update_3160_attempt_migration', 'complete', DAY_IN_SECONDS );
		return false;
	}

	foreach ( $query as $record ) {

		if ( ! empty( $record->meta_value ) ) {

			foreach ( unserialize( $record->meta_value ) as $attempt ) {

				if ( ! is_array( $attempt ) ) {
					continue;
				}

				$to_insert = array();
				$format    = array();

				$start = $attempt['start_date'];
				$end   = $attempt['end_date'];

				if ( $end ) {
					$to_insert['update_date'] = $end;
					$format[]                 = '%s';
				} elseif ( $start ) {
					$to_insert['update_date'] = $start;
					$format[]                 = '%s';
				} else {
					continue;
				}

				foreach ( $attempt as $key => $val ) {

					$insert_key = $key;
					$insert_val = $val;

					if ( 'assoc_lesson' === $key ) {
						$insert_key = 'lesson_id';
					} elseif ( 'id' === $key ) {
						$insert_key = 'quiz_id';
					} elseif ( 'user_id' === $key ) {
						$insert_key = 'student_id';
					} elseif ( 'wpnonce' === $key ) {
						continue;
					} elseif ( 'current' === $key ) {
						continue;
					} elseif ( 'questions' === $key ) {
						$insert_val = serialize( $val );
					} elseif ( 'passed' === $key ) {
						$insert_key = 'status';
						if ( $val ) {
							$insert_val = 'pass';
						} else {
							// Quiz has been initialized but hasn't been started yet,
							// we don't need to migrate these.
							if ( ! $start && ! $end ) {
								// $insert_val = 'new';
								continue;
							} elseif ( $start && ! $end ) {
								// sSill taking the quiz.
								if ( isset( $attempt['current'] ) && $attempt['current'] ) {
									$insert_val = 'current';
								}
								// Quiz was abandoned.
								$insert_val = 'incomplete';
								// Actual failure.
							} else {
								$insert_val = 'fail';
							}
						}
					}

					switch ( $insert_key ) {

						case 'lesson_id':
						case 'quiz_id':
						case 'student_id':
						case 'attempt':
							$insert_format = '%d';
							break;

						case 'grade':
							$insert_format = '%f';
							break;

						default:
							$insert_format = '%s';

					}

					$to_insert[ $insert_key ] = $insert_val;
					$format[]                 = $insert_format;

				}

				$wpdb->insert( $wpdb->prefix . 'lifterlms_quiz_attempts', $to_insert, $format ); // db call ok; no-cache ok.

			}
		}

		// Backup original.
		update_user_meta( $record->user_id, 'llms_legacy_quiz_data', $record->meta_value );

		// Selete the original so it's not there on the next run.
		delete_user_meta( $record->user_id, 'llms_quiz_data' );

	}

	// Needs to run again.
	return true;

}

/**
 * Create duplicate questions for each question attached to multiple quizzes
 *
 * @since 3.16.0
 *
 * @return void
 */
function llms_update_3160_ensure_no_dupe_question_rels() {

	if ( 'complete' !== get_transient( 'llms_update_3160_attempt_migration' ) ) {
		return true;
	}

	$skip = get_transient( 'llms_3160_skipper_dupe_q' );
	if ( ! $skip ) {
		$skip = 0;
	}
	set_transient( 'llms_3160_skipper_dupe_q', $skip + 20, DAY_IN_SECONDS );

	global $wpdb;
	$question_ids = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT ID
		 FROM {$wpdb->posts}
		 WHERE post_type = 'llms_question'
		 ORDER BY ID ASC
		 LIMIT %d, 20;",
			$skip
		)
	); // db call ok; no-cache ok.

	if ( ! $question_ids ) {
		set_transient( 'llms_update_3160_ensure_no_dupe_question_rels_status', 'complete', DAY_IN_SECONDS );
		return false;
	}

	foreach ( $question_ids as $qid ) {

		$parts = array(
			serialize(
				array(
					'id' => $qid,
				)
			),
			serialize(
				array(
					'id' => absint( $qid ),
				)
			),
		);

		foreach ( $parts as &$part ) {
			$part = substr( $part, 5, -1 );
		}

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$quiz_ids = $wpdb->get_col(
			"
			SELECT post_id
			FROM {$wpdb->postmeta}
			WHERE meta_key = '_llms_questions'
			  AND ( meta_value LIKE '%{$parts[0]}%' OR meta_value LIKE '%{$parts[1]}%' );"
		); // db call ok; no-cache ok.
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		// Question is attached to 2 or more quizzes.
		if ( count( $quiz_ids ) >= 2 ) {

			// Remove the first quiz and duplicate questions for the remaining quizzes.
			array_shift( $quiz_ids );

			foreach ( $quiz_ids as $quiz_id ) {

				// Copy the question and add update the reference on the quiz.
				$question_copy_id = llms_update_util_post_duplicator( $qid );
				$questions        = get_post_meta( $quiz_id, '_llms_questions', true );
				foreach ( $questions as &$qdata ) {
					if ( $qdata['id'] == $qid ) {
						$qdata['id'] = $question_copy_id;
					}
				}
				update_post_meta( $quiz_id, '_llms_questions', $questions );

				// Update references to the quiz in quiz attempts.
				// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$attempt_ids = $wpdb->get_col(
					"
					SELECT id
					FROM {$wpdb->prefix}lifterlms_quiz_attempts
					WHERE quiz_id = {$quiz_id}
					  AND ( questions LIKE '%{$parts[0]}%' OR questions LIKE '%{$parts[1]}%' );"
				); // db call ok; no-cache ok.
				// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

				foreach ( $attempt_ids as $aid ) {

					$attempt    = new LLMS_Quiz_Attempt( $aid );
					$attempt_qs = $attempt->get_questions();
					foreach ( $attempt_qs as &$answer ) {
						if ( $answer['id'] == $qid ) {
							$answer['id'] = $question_copy_id;
						}
					}
					$attempt->set_questions( $attempt_qs, true );

				}
			}
		}
	}

	// Need to run again.
	return true;

}

/**
 * Create duplicates for any quiz attached to multiple lessons
 *
 * @since 3.16.0
 *
 * @return void
 */
function llms_update_3160_ensure_no_lesson_dupe_rels() {

	if ( 'complete' !== get_transient( 'llms_update_3160_ensure_no_dupe_question_rels_status' ) ) {
		return true;
	}

	$skip = get_transient( 'llms_3160_skipper_dupe_l' );
	if ( ! $skip ) {
		$skip = 0;
	}
	set_transient( 'llms_3160_skipper_dupe_l', $skip + 100, DAY_IN_SECONDS );

	global $wpdb;
	$res = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT post_id AS lesson_id, meta_value AS quiz_id
		 FROM {$wpdb->postmeta}
		 WHERE meta_key = '_llms_quiz'
		   AND meta_value != 0
		 ORDER BY lesson_id ASC
		 LIMIT %d, 100
		;",
			$skip
		)
	); // db call ok; no-cache ok.

	if ( ! $res ) {
		set_transient( 'llms_update_3160_ensure_no_lesson_dupe_rels', 'complete', DAY_IN_SECONDS );
		return false;
	}

	$quizzes_set = array();

	foreach ( $res as $data ) {

		$lesson = llms_get_post( $data->lesson_id );
		if ( ! $lesson ) {
			continue;
		}

		// Quiz no longer exists, unset the data from the lesson.
		$quiz = llms_get_post( $data->quiz_id );
		if ( ! $quiz ) {
			$lesson->set( 'quiz', 0 );
			$lesson->set( 'quiz_enabled', 'no' );
			continue;
		}

		/**
		 * Quiz already attached to a lesson
		 * + duplicate it
		 * + assign lesson/quiz relationships off new quiz
		 * + find quiz attempts by old quiz / lesson
		 * + update attempt quiz id
		 * + update attempt question ids
		 */
		if ( in_array( $data->quiz_id, $quizzes_set ) ) {

			$orig_questions = get_post_meta( $data->quiz_id, '_llms_questions', true );
			$qid_map        = array();
			$dupe_quiz_id   = llms_update_util_post_duplicator( $data->quiz_id );
			foreach ( $orig_questions as &$oqdata ) {
				$dupe_q                   = llms_update_util_post_duplicator( $oqdata['id'] );
				$qid_map[ $oqdata['id'] ] = $dupe_q;
				$oqdata['id']             = $dupe_q;
			}
			update_post_meta( $dupe_quiz_id, '_llms_questions', $orig_questions );
			update_post_meta( $dupe_quiz_id, '_llms_lesson_id', $data->lesson_id );

			$lesson->set( 'quiz', $dupe_quiz_id );

			// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$attempt_ids = $wpdb->get_col(
				"
				SELECT id
				FROM {$wpdb->prefix}lifterlms_quiz_attempts
				WHERE quiz_id = {$data->quiz_id} AND lesson_id = {$data->lesson_id}"
			); // db call ok; no-cache ok.
			// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

			foreach ( $attempt_ids as $aid ) {
				$attempt   = new LLMS_Quiz_Attempt( $aid );
				$questions = $attempt->get_questions();
				foreach ( $questions as &$aqd ) {

					if ( isset( $qid_map[ $aqd['id'] ] ) ) {
						$aqd['id'] = $qid_map[ $aqd['id'] ];
					}
				}
				$attempt->set_questions( $questions, true );
				$attempt->set( 'quiz_id', $dupe_quiz_id );
				$attempt->save();

			}
		}

		$quizzes_set[] = $data->quiz_id;
		$lesson->set( 'quiz_enabled', 'yes' ); // Ensure the new quiz enabled key is set.

	}

	// Run it again.
	return true;

}

/**
 * Update question & choice data to new structure
 *
 * @since 3.16.0
 *
 * @return void
 */
function llms_update_3160_update_question_data() {

	if ( 'complete' !== get_transient( 'llms_update_3160_ensure_no_lesson_dupe_rels' ) ) {
		return true;
	}

	$skip = get_transient( 'llms_3160_skipper_qdata' );
	if ( ! $skip ) {
		$skip = 0;
	}
	set_transient( 'llms_3160_skipper_qdata', $skip + 100, DAY_IN_SECONDS );

	global $wpdb;
	$res = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT post_id AS quiz_id, meta_value AS questions
		 FROM {$wpdb->postmeta}
		 WHERE meta_key = '_llms_questions'
		 ORDER BY post_id ASC
		 LIMIT %d, 100;",
			$skip
		)
	); // db call ok; no-cache ok.

	// Finished.
	if ( ! $res ) {
		set_transient( 'llms_update_3160_update_question_data', 'complete', DAY_IN_SECONDS );
		return false;
	}

	foreach ( $res as $data ) {
		$questions = maybe_unserialize( $data->questions );
		if ( is_array( $questions ) ) {
			foreach ( $questions as $raw_question ) {

				$points = isset( $raw_question['points'] ) ? $raw_question['points'] : 1;

				$question = llms_get_post( $raw_question['id'] );

				if ( ! $question ) {
					continue;
				}

				$question->set( 'parent_id', $data->quiz_id );
				$question->set( 'question_type', 'choice' );
				$question->set( 'points', $points );
				update_post_meta( $question->get( 'id' ), '_llms_legacy_question_title', $question->get( 'title' ) );
				$question->set( 'title', strip_tags( str_replace( array( '<p>', '</p>' ), '', $question->get( 'content' ) ), '<b><em><u><strong><i>' ) );

				$options = get_post_meta( $question->get( 'id' ), '_llms_question_options', true );

				update_post_meta( $question->get( 'id' ), '_llms_legacy_question_options', $options );
				delete_post_meta( $question->get( 'id' ), '_llms_question_options' );

				if ( ! $options ) {
					continue;
				}
				$clarify = '';

				$markers = range( 'A', 'Z' );

				foreach ( (array) $options as $index => $option ) {

					if ( ! isset( $option['option_text'] ) ) {
						continue;
					}

					$correct = false;
					// No correct_option set for the choice, set it to false.
					if ( ! isset( $option['correct_option'] ) ) {
						$correct = false;
						/**
						 * Handle bool strings like "on" "off" "yes" "no"
						 * and questions imported from a 3rd party Excel to LifterLMS plugin
						 * that doesn't save options in the expected format...
						 *  dev if you're reading this I love you but you caused me a pretty large headache
						 * trying to figure out where in our codebase we went wrong...
						 */
					} elseif ( is_string( $option['correct_option'] ) && '' !== $option['correct_option'] ) {
						$correct = true;
						// Catch everything else and filter var it.
					} else {

						$correct = filter_var( $option['correct_option'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE );

						// Nothing should get here but I'm tired...
						if ( is_null( $correct ) ) {
							$correct = true;
						}
					}

					$question->create_choice(
						array(
							'choice'  => $option['option_text'],
							'correct' => $correct,
							'marker'  => $markers[ $index ],
						)
					);

					// If an option desc is set.
					if ( ! empty( $option['option_description'] ) ) {
						// If the description hasn't already been added to the new clarification.
						if ( false === strpos( $clarify, $option['option_description'] ) ) {
							$clarify .= $option['option_description'] . '<br><br>';
						}
					}
				}

				if ( $clarify ) {
					$question->set( 'clarifications', trim( rtrim( $clarify, '<br><br>' ) ) );
					$question->set( 'clarifications_enabled', 'yes' );
				}
			}
		}
	}

	// Run it again.
	return true;

}

/**
 * Update question data to new formats & match question choice indexes to new choice IDs
 *
 * @since 3.16.0
 *
 * @return void
 */
function llms_update_3160_update_attempt_question_data() {

	if ( 'complete' !== get_transient( 'llms_update_3160_update_question_data' ) ) {
		return true;
	}

	$skip = get_transient( 'llms_update_3160_skipper' );
	if ( ! $skip ) {
		$skip = 0;
	}
	set_transient( 'llms_update_3160_skipper', $skip + 500, DAY_IN_SECONDS );

	global $wpdb;
	$res = $wpdb->get_col( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}lifterlms_quiz_attempts ORDER BY id ASC LIMIT %d, 500", $skip ) ); // db call ok; no-cache ok.

	// Finished.
	if ( ! $res ) {
		set_transient( 'llms_update_3160_update_attempt_question_data', 'complete', DAY_IN_SECONDS );
		return false;
	}

	foreach ( $res as $att_id ) {

		$attempt   = new LLMS_Quiz_Attempt( $att_id );
		$questions = $attempt->get_questions();
		foreach ( $questions as &$question ) {

			$question['earned'] = empty( $question['correct'] ) ? 0 : $question['points'];
			if ( ! isset( $question['answer'] ) ) {
				$question['answer'] = array();
			} elseif ( ! is_array( $question['answer'] ) && is_numeric( $question['answer'] ) ) {
				$obj = llms_get_post( $question['id'] );
				if ( $obj ) {
					$choices = $obj->get_choices();
					if ( isset( $choices[ $question['answer'] ] ) ) {
						$question['answer'] = array( $choices[ $question['answer'] ]->get( 'id' ) );
					}
				}
			}
		}

		$attempt->set_questions( $questions, true );

	}

	return true;

}

/**
 * Ensure quizzes backreference their parent lessons
 *
 * @since 3.16.0
 *
 * @return void
 */
function llms_update_3160_update_quiz_to_lesson_rels() {

	if ( 'complete' !== get_transient( 'llms_update_3160_update_attempt_question_data' ) ) {
		return true;
	}

	global $wpdb;
	$ids = $wpdb->get_col( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_llms_quiz_enabled' AND meta_value = 'yes'" );

	foreach ( $ids as $id ) {

		$lesson = llms_get_post( $id );
		if ( $lesson ) {
			$quiz_id = $lesson->get( 'quiz' );
			if ( $quiz_id ) {
				$quiz = llms_get_post( $quiz_id );
				$quiz->set( 'lesson_id', $id );
			}
		}
	}

}

/**
 * Add an admin notice about new quiz things
 *
 * @since 3.16.0
 *
 * @return void
 */
function llms_update_3160_builder_notice() {

	if ( 'complete' !== get_transient( 'llms_update_3160_update_attempt_question_data' ) ) {
		return true;
	}

	require_once LLMS_PLUGIN_DIR . 'includes/admin/class.llms.admin.notices.php';

	LLMS_Admin_Notices::add_notice(
		'update-3160',
		array(
			'html'        => sprintf(
				__( 'Welcome to LifterLMS 3.16.0! This update adds significant improvements to the quiz-building experience. Notice quizzes and questions are no longer found under "Courses" on the sidebar? Your quizzes have not been deleted but they have been moved! Read more about the all new %1$squiz builder%2$s.', 'lifterlms' ),
				'<a href="http://blog.lifterlms.com/hello-quizzes/" target="_blank">',
				'</a>'
			),
			'type'        => 'info',
			'dismissible' => true,
			'remindable'  => false,
		)
	);

}

/**
 * Update db version at conclusion of 3.16.0 updates
 *
 * @since 3.16.0
 *
 * @return void
 */
function llms_update_3160_update_db_version() {

	if ( 'complete' !== get_transient( 'llms_update_3160_update_attempt_question_data' ) ) {
		return true;
	}

	LLMS_Install::update_db_version( '3.16.0' );

}
