<?php
// @codingStandardsIgnoreFile
/**
 * @deprecated 3.30.3
 */
namespace LLMS\Users;

class User {

	/**
	 * @var string
	 * @since 1.3.0
	 */
	public $first_name;

	/**
	 * @var int
	 * @since 1.3.0
	 */
	private $id;

	/**
	 * @var string
	 * @since 1.3.0
	 */
	public $last_name;

	/**
	 * @var array
	 * @since 1.3.0
	 */
	public $quiz_data;

	/**
	 * Constructor
	 * If ID is passed then creates object by id else gets current user id.
	 * @param [type] $user_id [description]
	 */
	public function __construct( $user_id = '' ) {

		if ( is_numeric( $user_id ) ) {

			$this->id = absint( $user_id );

		} else {

			$this->id = get_current_user_id();

		}

	}

	/**
	* __isset function
	*
	* checks if metadata exists
	*
	* @param string $item
	*/
	public function __isset( $item ) {

		return metadata_exists( 'user', $this->id, 'llms_' . $item );
	}

	/**
	* __get function
	*
	* initializes the user object based on user data
	*
	* @param string $item
	* @return string $value
	*/
	public function __get( $item ) {

		$value = get_user_meta( $this->id, 'llms_' . $item, true );

		if ( ! $value) {
			$value = get_user_meta( $this->id, $item, true );
		}

		return $value;
	}

	/**
	 * set function
	 * @param string $key meta key
	 * @param mixed  $value  [description]
	 */
	public function set( $key, $value ) {

		$this->$key = $value;
		return update_user_meta( $this->id, 'llms_' . $key, $value );

	}

	/**
	 * Public get Id method
	 * @return int
	 */
	public function get_id() {

		return $this->id;
	}

	/**
	 * Get quiz serialized array
	 * @return array
	 */
	public function get_quiz_data() {

		return $this->quiz_data;
	}

	public function get_first_name() {

		return $this->first_name;
	}

	public function get_last_name() {

		return $this->last_name;
	}

	public function get_full_name() {

		return $this->first_name . ' ' . $this->last_name;
	}

}
