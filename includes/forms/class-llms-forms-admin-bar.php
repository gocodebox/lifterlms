<?php
/**
 * LLMS_Forms_Admin_Bar calss
 *
 * @package  LifterLMS/Classes
 *
 * @since 5.0.0
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Add WP Admin Bar Nodes to enable editing of the currently-viewed form by a qualifying user
 *
 * @since 5.0.0
 */
class LLMS_Forms_Admin_Bar {

	/**
	 * Constructor
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function __construct() {

		add_action( 'admin_bar_menu', array( $this, 'add_menu_items' ), 999 );

	}

	/**
	 * Add view links to the admin menu bar for qualifying users.
	 *
	 * @since 3.7.0
	 * @since 3.16.0 Unknown.
	 * @since 4.2.0 Updated icon.
	 * @since 4.5.1 Use `should_display()` method to determine if the view manager should be added to the admin bar.
	 * @since 4.16.0 Retrieve nodes to add from `get_menu_items_to_add()`.
	 *
	 * @param WP_Admin_Bar $wp_admin_bar Admin bar class instance.
	 * @return void
	 */
	public function add_menu_items( $wp_admin_bar ) {

		if ( ! $this->should_display() ) {
			return;
		}

		$args = array( $this->get_current_location() );
		$plan = llms_filter_input( INPUT_GET, 'plan', FILTER_SANITIZE_NUMBER_INT );
		if ( $plan ) {
			$args[] = array( 'plan' => llms_get_post( $plan ) );
		}
		$form = llms_get_form( ...$args );

		$wp_admin_bar->add_node(
			array(
				'id'     => 'llms-edit-form',
				'parent' => 'edit',
				'title'  => __( 'Edit Form', 'lifterlms' ),
				'href'   => get_edit_post_link( $form->ID ),
			)
		);

	}

	/**
	 * Retrieve the form location for the current screen
	 *
	 * Must be on a checkout screen, the "edit account" tab of the dashboard,
	 * or be viewing as a visitor on the main dashboard page with open registration enabled.
	 *
	 * @since 5.0.0
	 *
	 * @return string|boolean Returns the location id as a string or `false` if not on a form location screen.
	 */
	private function get_current_location() {

		if ( is_llms_checkout() ) {

			return 'checkout';

		} elseif ( is_llms_account_page() ) {

			$tab = LLMS_Student_Dashboard::get_current_tab( 'tab' );

			if ( 'edit-account' === $tab ) {
				return 'account';
			}

			if ( 'dashboard' === $tab && 'visitor' === llms_filter_input( INPUT_GET, 'llms-view-as' ) && llms_parse_bool( llms_get_open_registration_status() ) ) {
				return 'registration';

			}
		}

		return false;

	}

	/**
	 * Determine whether or an Edit Form node should be added to the admin bar.
	 *
	 * The user must be able to edit forms and be on a screen with a displayed form.
	 *
	 * @return boolean
	 */
	private function should_display() {

		$display = ( current_user_can( LLMS_Forms::instance()->get_capability() ) && $this->get_current_location() );

		/**
		 * Filters whether or not the "Edit Form" WP_Admin_Bar node is displayed
		 *
		 * @since 5.0.0
		 *
		 * @param boolean $display Whether or not to display the node.
		 */
		return apply_filters( 'llms_should_display_wp_admin_bar_nodes_for_forms', $display );

	}

}

return new LLMS_Forms_Admin_Bar();
