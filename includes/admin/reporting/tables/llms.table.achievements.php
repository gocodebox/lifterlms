<?php
/**
 * Admin Achievements Table
 *
 * @since   3.2.0
 * @version 3.35.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Table_Achievements
 *
 * @since   3.2.0
 * @since 3.35.0 Get student ID more reliably.
 */
class LLMS_Table_Achievements extends LLMS_Admin_Table {

	/**
	 * Unique ID for the Table
	 *
	 * @var  string
	 */
	protected $id = 'achievements';

	/**
	 * Instance of LLMS_Student
	 *
	 * @var  null
	 */
	protected $student = null;

	/**
	 * Get HTML for buttons in the actions cell of the table
	 *
	 * @param    int $certificate_id  WP Post ID of the llms_my_certificate
	 * @return   void
	 * @since    3.18.0
	 * @version  3.18.0
	 */
	private function get_actions_html( $achievement_id ) {
		ob_start();
		?>
		<form action="" method="POST" style="display:inline;">

			<button type="submit" class="llms-button-danger small" id="llms_delete_achievement" name="llms_delete_achievement">
				<?php _e( 'Delete', 'lifterlms' ); ?>
				<i class="fa fa-trash" aria-hidden="true"></i>
			</button>

			<input type="hidden" name="achievement_id" value="<?php echo absint( $achievement_id ); ?>">
			<?php wp_nonce_field( 'llms-achievement-actions', '_llms_achievement_actions_nonce' ); ?>

		</form>

		<script>document.getElementById( 'llms_delete_achievement' ).onclick = function( e ) {
			return window.confirm( '<?php esc_attr_e( 'Are you sure you want to delete this achievement? This action cannot be undone!', 'lifterlms' ); ?>' );
		};</script>
		<?php
		return ob_get_clean();
	}


	/**
	 * Retrieve data for the columns
	 *
	 * @param    string $key   the column id / key
	 * @param    mixed  $data  object of achievement data
	 * @return   mixed
	 * @since    3.2.0
	 * @version  3.18.0
	 */
	public function get_data( $key, $data ) {

		switch ( $key ) {

			case 'actions':
				$value = $this->get_actions_html( $data->achievement_id );
				break;

			case 'related':
				if ( $data->post_id && 'llms_achievement' !== get_post_type( $data->post_id ) ) {
					if ( is_numeric( $data->post_id ) ) {
						$value = $this->get_post_link( $data->post_id, get_the_title( $data->post_id ) );
					} else {
						$value = $data->post_id;
					}
				} else {
					$value = '&ndash;';
				}
				break;

			case 'earned':
				$value = date_i18n( 'F j, Y', strtotime( $data->earned_date ) );
				break;

			case 'id':
				$value = $data->achievement_id;
				break;

			case 'image':
				$value = wp_get_attachment_image( get_post_meta( $data->achievement_id, '_llms_achievement_image', true ), array( 64, 64 ) );
				break;

			case 'template_id':
				// prior to 3.2 this data wasn't recorded
				$template = get_post_meta( $data->achievement_id, '_llms_achievement_template', true );
				if ( $template ) {
					$value = $this->get_post_link( $template );
				} else {
					$value = '&ndash;';
				}
				break;

			case 'name':
				$value = get_post_meta( $data->achievement_id, '_llms_achievement_title', true );
				break;

			default:
				$value = $key;

		}// End switch().

		return $this->filter_get_data( $value, $key, $data );

	}

	public function get_results( $args = array() ) {

		$args = $this->clean_args( $args );

		if ( is_numeric( $args['student'] ) ) {
			$args['student'] = new LLMS_Student( $args['student'] );
		}

		$this->student = $args['student'];

		$this->tbody_data = $this->student->get_achievements();

	}

	/**
	 * Define the structure of arguments used to pass to the get_results method
	 *
	 * @since 2.3.0
	 * @since 3.35.0 Get student ID more reliably.
	 *
	 * @return   array
	 */
	public function set_args() {

		$student = false;
		if ( ! empty( $this->student ) ) {
			$student = $this->student->get_id();
		} elseif ( ! empty( $_GET['student_id'] ) ) {
			$student = llms_filter_input( INPUT_GET, 'student_id', FILTER_SANITIZE_NUMBER_INT );
		}

		return array(
			'student' => $student,
		);
	}

	/**
	 * Define the structure of the table
	 *
	 * @return   array
	 * @since    3.2.0
	 * @version  3.18.0
	 */
	protected function set_columns() {
		return array(
			'id'          => __( 'ID', 'lifterlms' ),
			'template_id' => __( 'Template ID', 'lifterlms' ),
			'name'        => __( 'Achievement Title', 'lifterlms' ),
			'image'       => __( 'Image', 'lifterlms' ),
			'earned'      => __( 'Earned Date', 'lifterlms' ),
			'related'     => __( 'Related Post', 'lifterlms' ),
			'actions'     => '',
		);
	}

	/**
	 * Empty message displayed when no results are found
	 *
	 * @return   string
	 * @since    3.2.0
	 * @version  3.2.0
	 */
	protected function set_empty_message() {
		return __( 'This student has not yet earned any achievements.', 'lifterlms' );
	}

}
