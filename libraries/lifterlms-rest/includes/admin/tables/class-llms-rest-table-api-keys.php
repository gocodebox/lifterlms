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
 * LLMS_REST_Table_API_Keys class..
 *
 * @since 1.0.0-beta.1
 */
class LLMS_REST_Table_API_Keys extends LLMS_Admin_Table {

	/**
	 * Unique ID for the Table
	 *
	 * @var  string
	 */
	protected $id = 'rest-api-keys';

	/**
	 * If true will be a table with a larger font size
	 *
	 * @var bool
	 */
	protected $is_large = true;

	/**
	 * Retrieve information for a the api key title/description <td>
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param LLMS_REST_API_Key $api_key API Key object.
	 * @return string
	 */
	protected function get_description_cell( $api_key ) {

		$html      = esc_html( $api_key->get( 'description' ) );
		$edit_link = esc_url( $api_key->get_edit_link() );
		$html      = '<a href="' . $edit_link . '">' . $html . '</a>';
		$html     .= '<div class="llms-rest-actions">';
		$html     .= '<small class="llms-action-icon">ID: ' . $api_key->get( 'id' ) . '</small> | ';
		$html     .= '<small><a class="llms-action-icon" href="' . $edit_link . '">' . __( 'View/Edit', 'lifterlms' ) . '</a></small> | ';
		$html     .= '<small><a class="llms-action-icon danger" href="' . esc_url( $api_key->get_delete_link() ) . '">' . __( 'Revoke', 'lifterlms' ) . '</a></small>';
		$html     .= '</div>';

		return $html;

	}

	/**
	 * Retrieve data for the columns
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param string            $key the column id / key.
	 * @param LLMS_REST_API_Key $api_key API key object.
	 * @return mixed
	 */
	public function get_data( $key, $api_key ) {

		switch ( $key ) {

			case 'description':
				$value = $this->get_description_cell( $api_key );
				break;

			case 'last_access':
				$value = $api_key->get_last_access_date();
				break;

			case 'truncated_key':
				$value = '<code>&hellip;' . $api_key->get( $key ) . '</code>';
				break;

			case 'user_id':
				$user = get_user_by( 'id', $api_key->get( $key ) );
				if ( ! $user ) {
					$value = '';
				} elseif ( current_user_can( 'edit_user', $user->ID ) ) {
					$value = '<a href="' . esc_url( get_edit_user_link( $user->ID ) ) . '">' . esc_html( $user->display_name ) . '</a>';
				} else {
					$value = esc_html( $user->display_name );
				}

				break;

			default:
				$value = $api_key->get( $key );

		}

		return $this->filter_get_data( $value, $key, $api_key );

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

		global $wpdb;

		$rows = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}lifterlms_api_keys", ARRAY_A );

		$tbody_data = array();
		foreach ( $rows as $data ) {
			$key          = new LLMS_REST_API_Key( $data['id'], false );
			$tbody_data[] = $key->setup( $data );
		}

		$this->tbody_data = $tbody_data;

	}

	/**
	 * Define the structure of arguments used to pass to the get_results method
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return array
	 */
	public function set_args() {
		return array();
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
			'description'   => __( 'Description', 'lifterlms' ),
			'truncated_key' => __( 'Consumer key', 'lifterlms' ),
			'user_id'       => __( 'User', 'lifterlms' ),
			'permissions'   => __( 'Permissions', 'lifterlms' ),
			'last_access'   => __( 'Last Access', 'lifterlms' ),
		);

	}

}
