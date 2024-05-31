<?php
/**
 * Quizzes Tab on Reporting Screen
 *
 * @package LifterLMS/Admin/Reporting/Tabs/Classes
 *
 * @since 3.16.0
 * @version 5.9.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Admin_Reporting_Tab_Quizzes
 *
 * @since 3.16.0
 * @since 3.35.0 Sanitize input data.
 */
class LLMS_Admin_Reporting_Tab_Quizzes {

	/**
	 * Constructor
	 *
	 * @return   void
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	public function __construct() {

		add_action( 'llms_reporting_content_quizzes', array( $this, 'output' ) );
		add_action( 'llms_reporting_quiz_tab_breadcrumbs', array( $this, 'breadcrumbs' ) );

	}

	/**
	 * Add breadcrumb links to the tab depending on current view
	 *
	 * @return   void
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	public function breadcrumbs() {

		$links = array();

		// Single quiz.
		if ( isset( $_GET['quiz_id'] ) ) {
			$quiz = llms_get_post( absint( $_GET['quiz_id'] ) );
			$links[ LLMS_Admin_Reporting::get_stab_url( 'overview' ) ] = $quiz->get( 'title' );
		}

		if ( isset( $_GET['attempt_id'] ) ) {

			$attempt = new LLMS_Quiz_Attempt( absint( $_GET['attempt_id'] ) );
			$links[ LLMS_Admin_Reporting::get_stab_url( 'attempts' ) ] = $attempt->get_title();

		}

		foreach ( $links as $url => $title ) {

			echo '<a href="' . esc_url( $url ) . '">' . $title . '</a>';

		}

	}

	/**
	 * Output tab content
	 *
	 * @since 3.16.0
	 * @since 3.35.0 Sanitize input data.
	 * @since 5.9.0 Stop using deprecated `FILTER_SANITIZE_STRING`.
	 *
	 * @return void
	 */
	public function output() {

		// Single quiz.
		if ( isset( $_GET['quiz_id'] ) ) {

			if ( ! current_user_can( 'edit_post', llms_filter_input( INPUT_GET, 'quiz_id', FILTER_SANITIZE_NUMBER_INT ) ) ) {
				wp_die( __( 'You do not have permission to access this content.', 'lifterlms' ) );
			}

			$tabs = apply_filters(
				'llms_reporting_tab_quiz_tabs',
				array(
					'overview' => __( 'Overview', 'lifterlms' ),
					'attempts' => __( 'Attempts', 'lifterlms' ),
				)
			);

			llms_get_template(
				'admin/reporting/tabs/quizzes/quiz.php',
				array(
					'current_tab' => isset( $_GET['stab'] ) ? esc_attr( llms_filter_input_sanitize_string( INPUT_GET, 'stab' ) ) : 'overview',
					'tabs'        => $tabs,
					'quiz'        => llms_get_post( intval( $_GET['quiz_id'] ) ),
				)
			);

			// Quiz table.
		} else {

			$table = new LLMS_Table_Quizzes();
			$table->get_results();
			echo $table->get_table_html();

		}

	}

}
return new LLMS_Admin_Reporting_Tab_Quizzes();
