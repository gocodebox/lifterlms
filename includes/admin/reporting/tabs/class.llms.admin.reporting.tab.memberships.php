<?php
/**
 * Memberships Tab on Reporting Screen
 * @since 3.32.0
 * @version 3.32.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Memberships Tab on Reporting Screen class.
 *
 * @since 3.32.0
 */
class LLMS_Admin_Reporting_Tab_Memberships {

	/**
	 * Constructor.
	 *
	 * @since 3.32.0
	 *
	 * @return void
	 */
	public function __construct() {

		add_action( 'llms_reporting_content_memberships', array( $this, 'output' ) );
		add_action( 'llms_reporting_membership_tab_breadcrumbs', array( $this, 'breadcrumbs' ) );

	}

	/**
	 * Add breadcrumb links to the tab depending on current view.
	 *
	 * @since 3.32.0
	 *
	 * @return void
	 */
	public function breadcrumbs() {

		$links = array();

		// single student
		if ( isset( $_GET['membership_id'] ) ) {
			$membership = llms_get_post( absint( $_GET['membership_id'] ) );
			$links[ LLMS_Admin_Reporting::get_stab_url( 'overview' ) ] = $membership->get( 'title' );
		}

		foreach ( $links as $url => $title ) {

			echo '<a href="' . esc_url( $url ) . '">' . $title . '</a>';

		}

	}

	/**
	 * Output tab content.
	 *
	 * @since 3.32.0
	 *
	 * @return void
	 */
	public function output() {

		// single membership
		if ( isset( $_GET['membership_id'] ) ) {

			if ( ! current_user_can( 'edit_post', $_GET['membership_id'] ) ) {
				wp_die( __( 'You do not have permission to access this content.', 'lifterlms' ) );
			}

			$tabs = apply_filters( 'llms_reporting_tab_membership_tabs', array(
				'overview' => __( 'Overview', 'lifterlms' ),
				'students' => __( 'Students', 'lifterlms' ),
			) );

			llms_get_template( 'admin/reporting/tabs/memberships/membership.php', array(
				'current_tab' => isset( $_GET['stab'] ) ? esc_attr( $_GET['stab'] ) : 'overview',
				'tabs'        => $tabs,
				'membership'  => llms_get_post( intval( $_GET['membership_id'] ) ),
			) );

		} // End if().
		else {

			$table = new LLMS_Table_Memberships();
			$table->get_results();
			echo $table->get_table_html();

		}

	}

}

return new LLMS_Admin_Reporting_Tab_Memberships();
