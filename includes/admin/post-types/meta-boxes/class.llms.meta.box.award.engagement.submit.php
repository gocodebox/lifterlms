<?php
/**
 * Award engagement submit meta box.
 *
 * @package LifterLMS/Admin/PostTypes/MetaBoxes/Classes
 *
 * @since 6.0.0
 * @version 6.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Award engagement submit meta box class.
 *
 * @since 6.0.0
 */
class LLMS_Meta_Box_Award_Engagement_Submit extends LLMS_Admin_Metabox {

	/**
	 * ID of the student who earned (is about to earn) the engagement.
	 *
	 * @since 6.0.0
	 *
	 * @var int
	 */
	private $student_id;

	/**
	 * Allowed post types.
	 *
	 * @since 6.0.0
	 *
	 * @var string[]
	 */
	private $post_types = array(
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
	 * Configure the metabox settings.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function configure() {

		$this->id       = 'submitdiv'; // Overrides the WordPress core one.
		$this->title    = __( 'Award', 'lifterlms' );
		$this->screens  = array_keys( $this->post_types );
		$this->context  = 'side';
		$this->priority = 'high';

		$this->callback_args = function () {
			return 'llms_my_certificate' === get_post_type() ?
				array(
					'__back_compat_meta_box' => true,
				)
				:
				array();
		};
	}

	/**
	 * Not used because our metabox doesn't use the standard fields api.
	 *
	 * @since 6.0.0
	 *
	 * @return array
	 */
	public function get_fields() {
		return array();
	}

	/**
	 * Function to field WP::output() method call.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function output() {

		global $action;

		add_action( 'admin_print_footer_scripts', array( __CLASS__, 'metabox_scripts' ), PHP_INT_MAX );

		$engagement             = $this->post;
		$engagement_id          = (int) $this->post->ID;
		$engagement_type_object = get_post_type_object( $this->post->post_type );
		$can_publish            = current_user_can( $engagement_type_object->cap->publish_posts );
		$fields                 = $this->student_fields();

		include LLMS_PLUGIN_DIR . 'includes/admin/views/metaboxes/view-award-engagement-submit.php';
	}

	/**
	 * Student fields.
	 *
	 * @since 6.0.0
	 *
	 * @return string
	 */
	private function student_fields() {

		$fields = '';

		// Creating.
		if ( 'add' === get_current_screen()->action ) {
			$fields = $this->student_fields_on_creation();
		}

		$fields .= $this->student_information();

		return $fields;
	}

	/**
	 * Student fields on creation html.
	 *
	 * @since 6.0.0
	 *
	 * @return string
	 */
	private function student_fields_on_creation() {

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
					'placeholder' => esc_attr__( 'Select a Student', 'lifterlms' ),
				),
				'id'              => $field_id,
				'label'           => ' ' . esc_html__( 'Select a Student', 'lifterlms' ),
				'type'            => 'select',
				'skip_save'       => true,
				'required'        => true,
			);
		}

		return $this->process_field( $field );
	}

	/**
	 * Current student information html.
	 *
	 * @since 6.0.0
	 *
	 * @return string
	 */
	private function student_information() {

		$post_type  = get_post_type( $this->post->ID );
		$student_id = $this->current_student_id();
		$student    = $student_id ? llms_get_student( $student_id ) : $student_id;

		// Bail if no student.
		if ( empty( $student ) ) {
			return '';
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
				'stab'       => $this->post_types[ $post_type ]['reporting_stab'],
			),
			admin_url( 'admin.php' )
		);

		return sprintf(
			'<li class="llms-mb-list student-info"> <b>%1$s:</b>&nbsp;<span>%2$s</span></li>',
			esc_html__( 'Student', 'lifterlms' ),
			sprintf(
				'<a href="%1$s" target="_blank">%2$s &lt;%3$s&gt;</a>',
				esc_url( $url ),
				esc_html( $name ),
				esc_html( $student->get( 'user_email' ) )
			)
		);
	}

	/**
	 * Retrieve the current student id.
	 *
	 * @since 6.0.0
	 *
	 * @param null|bool $creating Whether or not we're awarding an engagement.
	 *                            If not provided, it'll be dynamically retrieved if the current screen's action is 'add'.
	 *                            See WordPress' `get_current_screen()`.
	 * @return int
	 */
	private function current_student_id( $creating = null ) {

		if ( isset( $this->student_id ) ) {
			return $this->student_id;
		}

		$creating  = $creating ?? ( 'add' === get_current_screen()->action );
		$post_type = get_post_type( $this->post->ID );
		// If creating, take into account passed GET variable.
		$student = $creating && ! empty( $_GET['sid'] ) ? llms_filter_input( INPUT_GET, 'sid', FILTER_SANITIZE_NUMBER_INT ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- no need to verify the nonce here.
		// If not creating, retrieve the earned engagement user id.
		$student          = ! $creating ? ( new $this->post_types[ $post_type ]['model']( $this->post->ID ) )->get_user_id() : $student;
		$this->student_id = $student;

		return $this->student_id;
	}

	/**
	 * Metabox specific scripts.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public static function metabox_scripts() {
		?>
<script>
	document.addEventListener(
		"DOMContentLoaded",
		() => {
			// Localization.
			const __  = window.wp.i18n.__,
				_i18n = {
				'Publish on:'  : __( 'Award on:', 'lifterlms' ),
				'Publish'      : __( 'Award', 'lifterlms' ),
				'Published'    : __( 'Awarded', 'lifterlms' ),
				'Published on:': __( 'Awarded on:', 'lifterlms' ),
			};

			window.wp.hooks.addFilter(
				'i18n.gettext',
				'llms.awardEngagement.submitbox',
				( translation, text ) => {
					return text in _i18n ? _i18n[text] : translation;
				}
			);
		}
	);
</script>
		<?php
	}
}
