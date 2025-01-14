<?php
/**
 * Admin Student Certificates Table
 *
 * @package LifterLMS/Admin/Reporting/Tables/Classes
 *
 * @since 3.2.0
 * @version 6.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Table_Student_Certificates class.
 *
 * @since 3.2.0
 * @since 3.35.0 Get student ID more reliably.
 * @since 6.0.0 Allow pagination.
 */
class LLMS_Table_Student_Certificates extends LLMS_Admin_Table {

	use LLMS_Trait_Earned_Engagement_Reporting_Table;

	/**
	 * Unique ID for the Table.
	 *
	 * @var string
	 */
	protected $id = 'certificates';

	/**
	 * Instance of LLMS_Student.
	 *
	 * @var null
	 */
	protected $student = null;

	/**
	 * If true, tfoot will add ajax pagination links.
	 *
	 * @var boolean
	 */
	protected $is_paginated = true;

	/**
	 * Get HTML for buttons in the actions cell of the table.
	 *
	 * @since 3.18.0
	 * @since 6.0.0 Show a button to edit earned certificates.
	 *
	 * @param int $certificate_id  WP Post ID of the llms_my_certificate
	 * @return void
	 */
	private function get_actions_html( $certificate_id ) {
		ob_start();
		?>
		<a class="llms-button-secondary small" href="<?php echo esc_url( get_permalink( $certificate_id ) ); ?>" target="_blank">
			<?php esc_html_e( 'View', 'lifterlms' ); ?>
			<i class="fa fa-external-link" aria-hidden="true"></i>
		</a>
		<?php if ( get_edit_post_link( $certificate_id ) ) : ?>
		<a class="llms-button-secondary small" href="<?php echo esc_url( get_edit_post_link( $certificate_id ) ); ?>">
			<?php esc_html_e( 'Edit', 'lifterlms' ); ?>
			<i class="fa fa-pencil" aria-hidden="true"></i>
		</a>
		<?php endif; ?>
		<form action="" method="POST" style="display:inline;">

			<button type="submit" class="llms-button-secondary small" name="llms_generate_cert">
				<?php esc_html_e( 'Download', 'lifterlms' ); ?>
				<i class="fa fa-cloud-download" aria-hidden="true"></i>
			</button>

			<button type="submit" class="llms-button-danger small" id="llms_delete_cert" name="llms_delete_cert">
				<?php esc_html_e( 'Delete', 'lifterlms' ); ?>
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
	 * Retrieve data for the columns.
	 *
	 * @since 3.2.0
	 * @since 3.18.0 Unknown.
	 * @since 6.0.0 Retrieve date using the LLMS_User_Certificate model.
	 *
	 * @param  string                $key         The column id / key.
	 * @param  LLMS_User_Certificate $certificate Object of certificate data.
	 * @return mixed
	 */
	public function get_data( $key, $certificate ) {

		// Handle old object being passed in.
		if ( ! is_a( $certificate, 'LLMS_User_Certificate' ) && property_exists( $certificate, 'certificate_id' ) ) {
			$certificate = llms_get_certificate( $certificate->certificate_id );
		}

		switch ( $key ) {

			case 'actions':
				$value = $this->get_actions_html( $certificate->get( 'id' ) );
				break;

			case 'related':
				$related = $certificate->get( 'related' );
				if ( $related && 'llms_certificate' !== get_post_type( $related ) ) {
					if ( is_numeric( $related ) ) {
						$value = $this->get_post_link( $related, get_the_title( $related ) );
					} else {
						$value = $related;
					}
				} else {
					$value = '&ndash;';
				}
				break;

			case 'earned':
				$value = $certificate->get_earned_date();
				$value = 'future' === get_post_status( $certificate->get( 'id' ) ) ? $value . ' ' . __( '(scheduled)', 'lifterlms' ) : $value;
				break;

			case 'id':
				$value = $certificate->get( 'id' );
				break;

			case 'name':
				$value = $certificate->get( 'title' );
				break;

			case 'template_id':
				$template = $certificate->get( 'parent' );
				if ( $template ) {
					$value = $this->get_post_link( $template );
				} else {
					$value = '&ndash;';
				}
				break;

			default:
				$value = $key;

		}

		// Pass the "legacy" object to the filter.
		$backwards_compat_obj = array(
			'post_id'        => $certificate->get( 'related' ),
			'certificate_id' => $certificate->get( 'id' ),
			'earned_date'    => $certificate->get_earned_date(),
		);

		return $this->filter_get_data( $value, $key, $backwards_compat_obj );
	}

	/**
	 * Get table results.
	 *
	 * @since Unknown
	 * @since 6.0.0 Paginate results.
	 *
	 * @param array $args
	 * @return void
	 */
	public function get_results( $args = array() ) {

		$args = $this->clean_args( $args );

		if ( is_numeric( $args['student'] ) ) {
			$args['student'] = new LLMS_Student( $args['student'] );
		}

		$this->student = $args['student'];

		if ( isset( $args['page'] ) ) {
			$this->current_page = absint( $args['page'] );
		}

		$query = $this->student->get_certificates(
			array(
				'per_page' => 10,
				'status'   => array( 'publish', 'future' ),
				'paged'    => $this->current_page,
				'sort'     => array(
					'date' => 'ASC',
					'ID'   => 'ASC',
				),
			)
		);

		$this->max_pages = $query->get_max_pages();

		if ( $this->max_pages > $this->current_page ) {
			$this->is_last_page = false;
		}

		$this->tbody_data = $query->get_awards();
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
