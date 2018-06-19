<?php
/**
 * AJAX Callbacks for admin addons screen
 * @since    [version]
 * @version  [version]
 */
defined( 'ABSPATH' ) || exit;

add_action( 'wp_ajax_llms_addon_toggle_activation', 'llms_ajax_addon_toggle_activation' );
function llms_ajax_addon_toggle_activation() {

	check_ajax_referer( 'llms-ajax', '_ajax_nonce' );

	if ( ! current_user_can( 'activate_plugins' ) ) {
		wp_send_json( array(
			'error' => __( 'You do not have the permissions required to perform this action.', 'lifterlms' ),
		) );
	}

	$upgrader = LLMS_AddOn_Upgrader::instance();
	$addon = $upgrader->get_product_data_by( 'id', $_REQUEST['addon'] );
	if ( ! $addon ) {
		wp_send_json( array(
			'error' => __( 'Invalid addon.', 'lifterlms' ),
		) );
	}

	if ( 'activate' === $_REQUEST['status'] ) {
		$toggle = activate_plugin( $addon['update_file'] );
	} elseif ( 'deactivate' === $_REQUEST['status'] ) {
		$toggle = deactivate_plugin( $addon['update_file'] );
	} else {
		wp_send_json( array(
			'error' => __( 'Invalid action.', 'lifterlms' )
		) );
	}

	if ( is_wp_erro( $toggle ) ) {
		wp_send_json( array(
			'error' => $toggle->get_error_message(),
		) );
	}

	wp_send_json( true );

}
