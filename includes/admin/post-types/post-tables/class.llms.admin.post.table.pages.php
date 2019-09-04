<?php
/**
 * Customize display of the "Page" post tables
 *
 * @since    3.0.0
 * @version  3.7.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

class LLMS_Admin_Post_Table_Pages {

	public $pages = array();

	/**
	 * Constructor
	 *
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function __construct() {

		if ( isset( $_GET['post_type'] ) && 'page' === $_GET['post_type'] ) {

			$pages = array(
				'checkout'    => __( 'LifterLMS Checkout', 'lifterlms' ),
				'courses'     => __( 'LifterLMS Course Catalog', 'lifterlms' ),
				'memberships' => __( 'LifterLMS Memberships Catalog', 'lifterlms' ),
				'myaccount'   => __( 'LifterLMS Student Dashboard', 'lifterlms' ),
			);

			foreach ( $pages as $key => $name ) {
				$id = llms_get_page_id( $key );
				if ( $id ) {

					$this->pages[ $id ] = $name;

				}
			}

			add_filter( 'display_post_states', array( $this, 'post_states' ), 10, 2 );

		}

	}

	/**
	 * Add state information to pages that are set as LifterLMD pages
	 *
	 * @param    array $states  array of post states
	 * @param    obj   $post    WP_Post object
	 * @return   array
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function post_states( $states, $post ) {

		if ( isset( $this->pages[ $post->ID ] ) ) {

			$states[] = $this->pages[ $post->ID ];

		}

		return $states;

	}


}

return new LLMS_Admin_Post_Table_Pages();
