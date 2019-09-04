<?php
/**
 * Admin Achievements Table
 *
 * @since   3.2.0
 * @version 3.18.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Table_Student_Certificates
 *
 * @since   3.2.0
 * @since 3.35.0 Get student ID more reliably.
 */
class LLMS_Table_Student_Certificates extends LLMS_Admin_Table {

	/**
	 * Unique ID for the Table
	 *
	 * @var  string
	 */
	protected $id = 'certificates';

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
	private function get_actions_html( $certificate_id ) {
		ob_start();
		?>
		<a class="llms-button-secondary small" href="<?php echo esc_url( get_permalink( $certificate_id ) ); ?>" target="_blank">
			<?php _e( 'View', 'lifterlms' ); ?>
			<i class="fa fa-external-link" aria-hidden="true"></i>
		</a>

		<form action="" method="POST" style="display:inline;">

			<button type="submit" class="llms-button-secondary small" name="llms_generate_cert">
				<?php _e( 'Download', 'lifterlms' ); ?>
				<i class="fa fa-cloud-download" aria-hidden="true"></i>
			</button>

			<button type="submit" class="llms-button-danger small" id="llms_delete_cert" name="llms_delete_cert">
				<?php _e( 'Delete', 'lifterlms' ); ?>
				<i class="fa fa-trash" aria-hidden="true"></i>
			</button>

			<input type="hidden" name="certificate_id" value="<?php echo absint( $certificate_id ); ?>">
			<?php wp_nonce_field( 'llms-cert-actions', '_llms_cert_actions_nonce' ); ?>

		</form>

		<script>document.getElementById( 'llms_delete_cert' ).onclick = function( e ) {
			return window.confirm( '<?php esc_attr_e( 'Are you sure you want to delete this certificate? This action cannot be undone!', 'lifterlms' ); ?>' );
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
				$value = $this->get_actions_html( $data->certificate_id );
				break;

			case 'related':
				if ( $data->post_id && 'llms_certificate' !== get_post_type( $data->post_id ) ) {
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
				$value = $data->certificate_id;
				break;

			case 'name':
				$value = get_post_meta( $data->certificate_id, '_llms_certificate_title', true );
				break;

			// prior to 3.2 this data wasn't recorded
			case 'template_id':
				$template = get_post_meta( $data->certificate_id, '_llms_certificate_template', true );
				if ( $template ) {
					$value = $this->get_post_link( $template );
				} else {
					$value = '&ndash;';
				}
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

		$this->tbody_data = $this->student->get_certificates();

	}

	/**
	 * Define the structure of arguments used to pass to the get_results method
	 *
	 * @since    2.3.0
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
			'name'        => __( 'Certificate Title', 'lifterlms' ),
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
		return __( 'This student has not yet earned any certificates.', 'lifterlms' );
	}

}
