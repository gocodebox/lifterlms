<?php
/**
 * Defines base methods and properties for querying data about LifterLMS Custom Post Types.
 *
 * @package LifterLMS/Abstracts
 *
 * @since 3.31.0
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Post_Data abstract.
 *
 * @since 3.31.0
 * @since [version] Move various methods into the LLMS_Abstract_Object_Data class.
 */
abstract class LLMS_Abstract_Post_Data extends LLMS_Abstract_Object_Data {

	/**
	 * LLMS Post instance.
	 *
	 * @since 3.31.0
	 *
	 * @var LLMS_Post_Model
	 */
	protected $post;

	/**
	 * LLMS Post ID.
	 *
	 * @since 3.31.0
	 *
	 * @var int
	 */
	protected $post_id;

	/**
	 * Constructor.
	 *
	 * @since 3.31.0
	 * @version [version]
	 *
	 * @param int $post_id WP Post ID of the LLMS Post.
	 *
	 * @return void
	 */
	public function __construct( $post_id ) {

		parent::__construct( $post_id );

		$this->post_id = $this->object_id;
		$this->post    = $this->object;

	}

	/**
	 * Retrieve post object from the post id passed to the constructor
	 *
	 * @since [version]
	 *
	 * @param int $object_id Object ID.
	 *
	 * @return LLMS_Post_Model
	 */
	protected function set_object( $object_id ) {
		return llms_get_post( $object_id );
	}

	/**
	 * Retrieve the instance of the LLMS_Post_Model.
	 *
	 * @since 3.31.0
	 * @since [version] Use getters from LLMS_Abstract_Object_Data
	 *
	 * @return LLMS_Post_Model
	 */
	public function get_post() {
		return $this->get_object();
	}

	/**
	 * Retrieve the LLMS_Post_Model ID.
	 *
	 * @since 3.31.0
	 * @since [version] Use getters from LLMS_Abstract_Object_Data
	 *
	 * @return int
	 */
	public function get_post_id() {
		return $this->get_object_id();
	}

	/**
	 * Retrieve recent LLMS_User_Postmeta for the quiz
	 *
	 * @since 3.31.0
	 *
	 * @param array $args {
	 *     Optional. An array of arguments to feed the LLMS_Query_User_Postmeta with.
	 *
	 *     @type int          $per_page The number of posts to query for. Default 10.
	 *     @type array|string $types    Array of strings for the type of events to fetch, or a string to fetch them all. Default 'all'.
	 *                                  @see LLMS_Query_User_Postmeta::parse_args()
	 * }
	 * @return array Array of LLMS_User_Postmetas.
	 */
	public function recent_events( $args = array() ) {

		$query_args = wp_parse_args(
			$args,
			array(
				'per_page' => 10,
				'types'    => 'all',
			)
		);

		$query_args['post_id'] = $this->post_id;

		$query = new LLMS_Query_User_Postmeta( $query_args );

		return $query->get_metas();

	}

}
