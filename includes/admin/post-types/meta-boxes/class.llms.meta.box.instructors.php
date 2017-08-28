<?php
/**
* Automation Creation Interface
* @since   1.0.0
* @version 1.0.1
*/

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Metabox_Instructors extends LLMS_Admin_Metabox {

	/**
	 * Configure the metabox
	 * @return  void
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function configure() {

		$this->id = 'llms-instructors';
		$this->title = __( 'Instructors', 'lifterlms' );
		$this->screens = array( 'course', 'llms_membership' );
		// $this->context = 'normal';
		// $this->priority = 'high';

		// add_action( 'admin_head', array( $this, 'output_styles' ) );
		// add_action( 'admin_print_footer_scripts', array( $this, 'output_scripts') );

	}

	/**
	 * Define metabox fields
	 * @return  array
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function get_fields() {

		// $primary = llms_get_instructor( $this->post->post_author );

		return array();

		return array(
			array(
				'title' => __( 'Instructors', 'lifterlms' ),
				'fields' => array(
					array(
						'button' => array(
							'text' => __( 'Add Instructor', 'lifterlms' ),
						),
						'handler' => 'llms_pa_automation_store',
						'header' => array(
							'default' => __( 'New Instructor', 'lifterlms' ),
						),
						'id' => $this->prefix . 'instructors',
						'label' => '',
						'type' => 'repeater',
						'fields' => array(
							array(
								'allow_null' => false,
								'class' => 'llms-select2',
								'id' => $this->prefix . 'instructor',
								'type' => 'select',
								'label' => __( 'Publication Schedule', 'lifterlms' ),
								'value' => array(
									'instant' => __( 'Instant (when automation begins)', 'lifterlms' ),
									'start' => __( '# of days after automation begins', 'lifterlms' ),
									'date' => __( 'On specific date', 'lifterlms' ),
								),
							),
						),
					),
				),
			),
		);

	}


	public function output() {

		$roles = array(
			'author' => esc_html__( 'Author', 'lifterlms' ),
			'instructor' => esc_html__( 'Instructor', 'lifterlms' ),
		);

		$author = llms_make_select2_student_array( array( $this->post->post_author ) );
		$author = array_shift( $author );
		?>

		<div class="llms-metabox" id="llms-instructors">

			<div class="llms-metabox-section d-all">

				<div class="d-1of3">
					<label for="post_author_override">
						<?php _e( 'Primary Instructor', 'lifterlms' ); ?>
					</label>
					<select class="llms-select2-student" name="post_author_override" type="select" data-roles="administrator,lms_manager,instructor,instructors_assistant">
						<option value="<?php echo $author['key']; ?>" selected="selected"><?php echo $author['title']; ?></option>
					</select>
				</div>

				<div class="d-1of3">
					<label for="post_author_role">
						<?php _e( 'Instructor Role', 'lifterlms' ); ?>
					</label><br>
					<select name="post_author_role">
						<?php foreach ( $roles as $key => $val ) : ?>
							<option value="<?php echo $key; ?>"><?php echo $val; ?></option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="d-1of3">
					<label>
						<?php _e( 'Visibility', 'lifterlms' ); ?>
					</label><br>
					<select name="post_author_visibility">
						<option value="visible"><?php esc_html_e( 'Visible', 'lifterlms' ); ?></option>
						<option value="visible"><?php esc_html_e( 'Hidden', 'lifterlms' ); ?></option>
					</select>
				</div>

			</div>
		</div>
		<?php
	}

}

return new LLMS_Metabox_Instructors();
