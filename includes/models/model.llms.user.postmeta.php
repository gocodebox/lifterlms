<?php
/**
 * LLMS_User_Postmeta data model
 * @since    3.15.0
 * @version  3.15.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_User_Postmeta model.
 */
class LLMS_User_Postmeta extends LLMS_Abstract_Database_Store {

	protected $date_created = null;
	protected $date_updated = 'updated_date';

	/**
	 * Array of table column name => format
	 * @var  array
	 */
	protected $columns = array(
		'user_id' => '%d',
		'post_id' => '%d',
		'meta_key' => '%s',
		'meta_value' => '%s',
		'updated_date' => '%s',
	);

	/**
	 * Primary Key column name => format
	 * @var  array
	 */
	protected $primary_key = array(
		'meta_id' => '%d',
	);

	/**
	 * Database Table Name
	 * @var  string
	 */
	protected $table = 'user_postmeta';

	/**
	 * Constructor
	 * @param    mixed      $item     meta_id of a user postmeta item or an object with at least an "id"
	 * @param    bool       $hydrate  if true, hydrates the object on instantiation (if an ID was found via $item)
	 * @since    3.15.0
	 * @version  3.21.0
	 */
	public function __construct( $item = null, $hydrate = true ) {

		if ( is_numeric( $item ) ) {

			$this->id = $item;

		} elseif ( is_object( $item ) && isset( $item->id ) ) {

			$this->id = $item->id;

		}

		parent::__construct();

		if ( $this->id && $hydrate ) {
			$this->hydrate();
		}

	}

	/**
	 * Get a string used to describe the postmeta item
	 * @param    string     $context  display context [course|student]
	 * @return   string
	 * @since    3.15.0
	 * @version  3.15.0
	 */
	public function get_description( $context = 'course' ) {

		$key = $this->get( 'meta_key' );

		$student = $this->get_student();
		$name = $student ? $student->get( 'display_name' ) : __( '[Deleted]', 'lifterlms' );

		$post = llms_get_post( $this->get( 'post_id' ) );
		$label = is_a( $post, 'LLMS_Post_Model' ) ? strtolower( $post->get_post_type_label() ) : __( 'quiz', 'lifterlms' );
		$post_name = ( 'course' === $context ) ? $label : sprintf( '%1$s "%2$s"', $label, get_the_title( $this->get( 'post_id' ) ) );

		$string = '';

		switch ( $key ) {

			case '_achievement_earned':

				$string = sprintf( __( '%1$s earned the achievement "%2$s"', 'lifterlms' ), $name, get_the_title( $this->get( 'meta_value' ) ) );

			break;

			case '_certificate_earned':

				$string = sprintf( __( '%1$s earned the certificate "%2$s"', 'lifterlms' ), $name, get_the_title( $this->get( 'meta_value' ) ) );

			break;

			case '_email_sent':

				$string = sprintf( __( 'Email "%1$s" was sent to %2$s', 'lifterlms' ), get_the_title( $this->get( 'meta_value' ) ), $name );

			break;

			case '_enrollment_trigger':

				$string = sprintf( __( '%1$s purchased the %2$s', 'lifterlms' ), $name, $post_name );

			break;

			case '_status':

				if ( 'enrolled' === $this->get( 'meta_value' ) ) {
					$string = sprintf( __( '%1$s enrolled into the %2$s', 'lifterlms' ), $name, $post_name );
				} else {
					$string = sprintf( __( '%1$s unenrolled from the %2$s', 'lifterlms' ), $name, $post_name );
				}

			break;

			case '_is_complete':

				$string = sprintf( __( '%1$s completed the %2$s', 'lifterlms' ), $name, $post_name );

			break;

		}// End switch().

		return $string;

	}

	/**
	 * Retrieve a link for the item on the admin panel
	 * @param    string     $context  display context [course|student]
	 * @return   string
	 * @since    3.15.0
	 * @version  3.15.0
	 */
	public function get_link( $context = 'course' ) {

		switch ( $this->get( 'meta_key' ) ) {

			case '_achievement_earned':
				$achievement = new LLMS_User_Achievement( $this->get( 'meta_value' ) );
				$url = get_edit_post_link( $achievement->get( 'achievement_template' ) );
			break;

			case '_certificate_earned':
				$certificate = new LLMS_User_Certificate( $this->get( 'meta_value' ) );
				$url = get_edit_post_link( $certificate->get( 'certificate_template' ) );
			break;

			case '_email_sent':
				$url = get_edit_post_link( $this->get( 'meta_value' ) );
			break;

			case '_enrollment_trigger':
				$url = get_edit_post_link( str_replace( 'order_', '', $this->get( 'meta_value' ) ) );
			break;

			default:

				$student = $this->get_student();
				if ( ! $student ) {
					return '';
				}

				$course = false;
				if ( 'course' === get_post_type( $this->get( 'post_id' ) ) ) {
					$course = llms_get_post( $this->get( 'post_id' ) );
				} else {
					$course = llms_get_post_parent_course( $this->get( 'post_id' ) );
				}

				if ( $course ) {
					$url = LLMS_Admin_Reporting::get_current_tab_url( array(
						'course_id' => $course->get( 'id' ),
						'stab' => 'courses',
						'student_id' => $student->get_id(),
						'tab' => 'students',
					) );
				}
		}// End switch().

	}

	/**
	 * Retrieve a student obj for the meta item
	 * @return   obj LLMS_Student
	 * @since    3.15.0
	 * @version  3.15.0
	 */
	public function get_student() {
		return llms_get_student( $this->get( 'user_id' ) );
	}

}
