<?php
/**
 * LLMS_Table_Achievements class file
 *
 * @package LifterLMS/Admin/Reporting/Tables/Classes
 *
 * @since 3.2.0
 * @version 6.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Display the student achievements reporting table.
 *
 * @since 3.2.0
 * @since 6.0.0 Allow pagination.
 */
class LLMS_Table_Achievements extends LLMS_Admin_Table {

	use LLMS_Trait_Earned_Engagement_Reporting_Table;

	/**
	 * Unique ID for the Table.
	 *
	 * @var string
	 */
	protected $id = 'achievements';

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
	 * @since 6.0.0 Show a button to edit earned achievements.
	 *
	 * @param int $achievement_id WP Post ID of the achievement post.
	 * @return void
	 */
	private function get_actions_html( $achievement_id ) {
		ob_start();
		?>
		<?php if ( get_edit_post_link( $achievement_id ) ) : ?>
		<a class="llms-button-secondary small" href="<?php echo esc_url( get_edit_post_link( $achievement_id ) ); ?>">
			<?php esc_html_e( 'Edit', 'lifterlms' ); ?>
			<i class="fa fa-pencil" aria-hidden="true"></i>
		</a>
		<?php endif; ?>
		<form action="" method="POST" style="display:inline;">

			<button type="submit" class="llms-button-danger small" id="llms_delete_achievement" name="llms_delete_achievement">
				<?php esc_html_e( 'Delete', 'lifterlms' ); ?>
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
	 * Retrieve data for the columns.
	 *
	 * @since 3.2.0
	 * @since 3.18.0 Unknown.
	 * @since 6.0.0 Retrieve earned date using the LLMS_User_Achievement model.
	 *
	 * @param  string                $key         The column id / key.
	 * @param  LLMS_User_Achievement $achievement Object of achievement data.
	 * @return mixed
	 */
	public function get_data( $key, $achievement ) {

		// Handle old object being passed in.
		if ( ! is_a( $achievement, 'LLMS_User_Achievement' ) && property_exists( $achievement, 'achievement_id' ) ) {
			$achievement = new LLMS_User_Achievement( $achievement->certificate_id );
		}

		switch ( $key ) {

			case 'actions':
				$value = $this->get_actions_html( $achievement->get( 'id' ) );
				break;

			case 'related':
				if ( $achievement->get( 'related' ) && 'llms_achievement' !== get_post_type( $achievement->get( 'related' ) ) ) {
					if ( is_numeric( $achievement->get( 'related' ) ) ) {
						$value = $this->get_post_link( $achievement->get( 'related' ), get_the_title( $achievement->get( 'related' ) ) );
					} else {
						$value = $achievement->get( 'related' );
					}
				} else {
					$value = '&ndash;';
				}
				break;

			case 'earned':
				$value = $achievement->get_earned_date();
				$value = 'future' === $achievement->get( 'status' ) ? $value . ' ' . __( '(scheduled)', 'lifterlms' ) : $value;
				break;

			case 'id':
				$value = $achievement->get( 'id' );
				break;

			case 'image':
				$src   = $achievement->get_image( array( 32, 32 ) );
				$value = '<img src="' . esc_url( $src ) . '" alt="' . $achievement->get( 'title' ) . '" width="32" height="32">';
				break;

			case 'template_id':
				// Prior to 3.2 this data wasn't recorded.
				$template = $achievement->get( 'parent' );
				if ( $template ) {
					$value = $this->get_post_link( $template );
				} else {
					$value = '&ndash;';
				}
				break;

			case 'name':
				$value = $achievement->get( 'title' );
				break;

			default:
				$value = $key;

		}

		// Pass the "legacy" object to the filter.
		$backwards_compat_obj = array(
			'post_id'        => $achievement->get( 'related' ),
			'achievement_id' => $achievement->get( 'id' ),
			'earned_date'    => $achievement->get_earned_date(),
		);

		return $this->filter_get_data( $value, $key, (object) $backwards_compat_obj );
	}

	/**
	 * Get table results.
	 *
	 * @since Unknown
	 * @since 6.0.0 Don't use deprecated signature for retrieving achievements.
	 *              Paginate results.
	 *
	 * @param array $args Table query arguments.
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

		$query = $this->student->get_achievements(
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
	 * @since 2.3.0
	 * @since 3.35.0 Get student ID more reliably.
	 *
	 * @return array
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
