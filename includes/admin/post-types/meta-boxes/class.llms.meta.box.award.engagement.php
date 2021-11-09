<?php
/**
 * Award engagement meta box.
 *
 * @package LifterLMS/Admin/PostTypes/MetaBoxes/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Award engagement meta box class
 *
 * Generates main meta box and builds forms.
 *
 * @since [version]
 */
class LLMS_Meta_Box_Award_Engagement extends LLMS_Admin_Metabox {

	/**
	 * ID of the student who earned (is about to earn) the engagement.
	 *
	 * @since [version]
	 *
	 * @var int
	 */
	private $student_id;

	/**
	 * Allowed post types.
	 *
	 * @since [version]
	 *
	 * @var string[]
	 */
	private $allowed_post_types = array(
		'llms_my_achievement' => array(
			'model'          => 'LLMS_User_Achievement',
			'reporting_stab' => 'achievements',
		),
		'llms_my_certificate' => array(
			'model'          => 'LLMS_User_Certificate',
			'reporting_stab' => 'certificates',
		),
	);

	/**
	 * Constructor.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function __construct() {

		parent::__construct();

		add_action( 'llms_metabox_before_content', array( $this, 'maybe_print_current_user_information' ) );

	}

	/**
	 * Configure the metabox settings.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function configure() {

		$this->id       = 'lifterlms-award-engagement';
		$this->title    = __( 'Student Information', 'lifterlms' );
		$this->screens  = array(
			'llms_my_certificate',
			'llms_my_achievement',
		);
		$this->priority = 'high';

	}

	/**
	 * Builds array of metabox options.
	 *
	 * Array is called in output method to display options.
	 * Appropriate fields are generated based on type.
	 *
	 * @since [version]
	 *
	 * @return array
	 */
	public function get_fields() {

		// Bail if not creating.
		if ( 'add' !== get_current_screen()->action ) {
			return array();
		}

		$student_id = $this->current_student_id( true );

		// The `post_author_override` is the same used in WP core for the author selector.
		$field_id = 'post_author_override';

		$field = array(
			'id'        => $field_id,
			'type'      => 'hidden',
			'value'     => $student_id,
			'skip_save' => true,
			'required'  => true,
		);

		if ( empty( $student_id ) ) {
			$field = array(
				'allow_null'      => false,
				'class'           => 'llms-select2-student',
				'data_attributes' => array(
					'allow_clear' => false,
					'placeholder' => __( 'Select a Student', 'lifterlms' ),
				),
				'id'              => $field_id,
				'label'           => __( 'Select a Student', 'lifterlms' ),
				'type'            => 'select',
				'skip_save'       => true,
				'required'        => true,
			);
		}

		return array(
			array(
				'title'  => __( 'General', 'lifterlms' ),
				'fields' => array(
					$field,
				),
			),
		);

	}

	/**
	 * Maybe print current user information.
	 *
	 * Executed after the fields have been processed.
	 *
	 * @since [version]
	 *
	 * @param string $metabox_id Metabox identifier.
	 * @return void
	 */
	public function maybe_print_current_user_information( $metabox_id ) {

		$post_type = get_post_type();

		// Bail if not the right metabox.
		if ( $this->id !== $metabox_id ) {
			return;
		}

		$student_id = $this->current_student_id();
		$student    = $student_id ? llms_get_student( $student_id ) : $student_id;

		// Bail if no student.
		if ( empty( $student ) ) {
			return;
		}

		$first = $student->get( 'first_name' );
		$last  = $student->get( 'last_name' );

		if ( ! $first || ! $last ) {
			$name = $student->get( 'display_name' );
		} else {
			$name = $last . ', ' . $first;
		}

		$url = add_query_arg(
			array(
				'page'       => 'llms-reporting',
				'tab'        => 'students',
				'student_id' => $student_id,
				'stab'       => $this->allowed_post_types[ $post_type ]['reporting_stab'],
			),
			admin_url( 'admin.php' )
		);

		printf(
			'<div style="font-size:1.2em;padding:15px 15px 0"><b>%1$s:</b>&nbsp;<span>%2$s</span></div>',
			__( 'Student', 'lifterlms' ),
			sprintf(
				'<a href="%1$s" target="_blank">%2$s &lt;%3$s&gt;</a>',
				esc_url( $url ),
				$name,
				$student->get( 'user_email' )
			)
		);
	}

	/**
	 * Retrieve the current student id.
	 *
	 * @since [version]
	 *
	 * @param null|bool $creating Whether or not we're awarding an engagement.
	 *                            If not provided, it'll be dynamically retrieved if the current screen's action is 'add'. See WordPress' `get_current_screen()`.
	 * @return int
	 */
	private function current_student_id( $creating = null ) {

		if ( isset( $this->student_id ) ) {
			return $this->student_id;
		}

		$creating  = $creating ?? ( 'add' === get_current_screen()->action );
		$post_type = get_post_type();
		// If creating, take into account passed GET variable.
		$student          = $creating && ! empty( $_GET['sid'] ) ? llms_filter_input( INPUT_GET, 'sid', FILTER_SANITIZE_NUMBER_INT ) : 0; // phpcs:ignore
		// If not creating, retrieve the earned engagement user id.
		$student          = ! $creating ? ( new $this->allowed_post_types[ $post_type ]['model']( $this->post->ID ) )->get_user_id() : $student;
		$this->student_id = $student;

		return $this->student_id;

	}

}
