<?php
/**
 * API Keys Admin Table.
 *
 * @package  LifterLMS_REST/Admin/Classes
 *
 * @since 1.0.0-beta.1
 * @version 1.0.0-beta.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_REST_Table_Webhooks class..
 *
 * @since 1.0.0-beta.1
 * @since 1.0.0-beta.3 Output translated status instead of the database value; trim the delivery URL to 40 characters.
 */
class LLMS_REST_Table_Webhooks extends LLMS_Admin_Table {

	/**
	 * Unique ID for the Table
	 *
	 * @var  string
	 */
	protected $id = 'rest-webhooks';

	/**
	 * If true will be a table with a larger font size
	 *
	 * @var bool
	 */
	protected $is_large = true;

	/**
	 * Retrieve information for a the webhook title/description <td>
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param LLMS_REST_API_Key $webhook API Key object.
	 * @return string
	 */
	protected function get_name_cell( $webhook ) {

		$html      = esc_html( $webhook->get( 'name' ) );
		$edit_link = esc_url( $webhook->get_edit_link() );
		$html      = '<a href="' . $edit_link . '">' . $html . '</a>';
		$html     .= '<div class="llms-rest-actions">';
		$html     .= '<small class="llms-action-icon">ID: ' . $webhook->get( 'id' ) . '</small> | ';
		$html     .= '<small><a class="llms-action-icon" href="' . $edit_link . '">' . __( 'View/Edit', 'lifterlms' ) . '</a></small> | ';
		$html     .= '<small><a class="llms-action-icon danger" href="' . esc_url( $webhook->get_delete_link() ) . '">' . __( 'Delete', 'lifterlms' ) . '</a></small>';
		$html     .= '</div>';

		return $html;

	}

	/**
	 * Retrieve data for the columns
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.3 Output translated status instead of the database value; trim the delivery URL to 40 characters.
	 *
	 * @param string            $key the column id / key.
	 * @param LLMS_REST_API_Key $webhook API key object.
	 * @return mixed
	 */
	public function get_data( $key, $webhook ) {

		switch ( $key ) {

			case 'name':
				$value = $this->get_name_cell( $webhook );
				break;

			case 'status':
				$statuses = LLMS_REST_API()->webhooks()->get_statuses();
				$value    = $webhook->get( $key );
				$value    = isset( $statuses[ $value ] ) ? $statuses[ $value ] : $value;
				break;

			case 'delivery_url':
				$value = llms_trim_string( $webhook->get( $key ), 40 );
				break;

			default:
				$value = $webhook->get( $key );

		}

		return $this->filter_get_data( $value, $key, $webhook );

	}

	/**
	 * Execute a query to retrieve results from the table
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param array $args Array of query args.
	 *
	 * @return void
	 */
	public function get_results( $args = array() ) {

		$args = wp_parse_args( $args, $this->set_args() );

		$query            = new LLMS_REST_Webhooks_Query( $args );
		$this->tbody_data = $query->get_webhooks();

	}

	/**
	 * Define the structure of arguments used to pass to the get_results method
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return array
	 */
	public function set_args() {
		return array(
			'per_page' => 999,
		);
	}

	/**
	 * Define the structure of the table
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return   array
	 */
	public function set_columns() {

		return array(
			'name'         => __( 'Name', 'lifterlms' ),
			'status'       => __( 'Status', 'lifterlms' ),
			'topic'        => __( 'Topic', 'lifterlms' ),
			'delivery_url' => __( 'Delivery URL', 'lifterlms' ),
		);

	}

}
