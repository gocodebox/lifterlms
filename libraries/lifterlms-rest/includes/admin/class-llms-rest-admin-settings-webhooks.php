<?php
/**
 * Admin Settings Page: REST API: Webhooks
 *
 * @package LifterLMS_REST/Admin/Classes
 *
 * @since 1.0.0-beta.1
 * @version 1.0.0-beta.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin Settings Page: REST API: Webhooks
 *
 * @since 1.0.0-beta.1
 */
class LLMS_Rest_Admin_Settings_Webhooks {


	/**
	 * Get settings fields for the Keys tab.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return array
	 */
	public static function get_fields() {

		require_once 'tables/class-llms-rest-table-webhooks.php';

		$add_hook = '1' === llms_filter_input( INPUT_GET, 'add-webhook', FILTER_SANITIZE_NUMBER_INT );
		$hook_id  = llms_filter_input( INPUT_GET, 'edit-webhook', FILTER_SANITIZE_NUMBER_INT );

		$settings = array();

		$settings[] = array(
			'class' => 'top',
			'id'    => 'rest_hooks_options_start',
			'type'  => 'sectionstart',
		);

		$settings[] = array(
			'title' => $hook_id || $add_hook ? __( 'Webhook Details', 'lifterlms' ) : __( 'Webhooks', 'lifterlms' ),
			'type'  => 'title-with-html',
			'id'    => 'rest_hooks_options_title',
			'html'  => $hook_id || $add_hook ? '' : '<a href="' . esc_url( admin_url( 'admin.php?page=llms-settings&tab=rest-api&section=webhooks&add-webhook=1' ) ) . '" class="llms-button-primary small" type="submit" style="top:-2px;">' . __( 'Add Webhook', 'lifterlms' ) . '</a>',
		);

		if ( $add_hook || $hook_id ) {

			$hook = $add_hook ? false : LLMS_REST_API()->webhooks()->get( $hook_id );
			if ( $add_hook || $hook->exists() ) {

				add_action( 'admin_print_footer_scripts', array( __CLASS__, 'output_scripts' ) );

				$user_id = $hook ? $hook->get( 'user_id' ) : get_current_user_id();

				$settings[] = array(
					'title' => __( 'Name', 'lifterlms' ),
					'desc'  => '<br>' . __( 'A friendly, human-readable, name used to identify the webhook.', 'lifterlms' ),
					'id'    => 'llms_rest_webhook_name',
					'type'  => 'text',
					'css'   => 'width:480px',
					'value' => $hook ? $hook->get( 'name' ) : '',
				);

				$settings[] = array(
					'title'   => __( 'Status', 'lifterlms' ),
					'id'      => 'llms_rest_webhook_status',
					'type'    => 'select',
					'options' => LLMS_REST_API()->webhooks()->get_statuses(),
					'value'   => $hook ? $hook->get( 'status' ) : '',
				);

				$topic = '';
				if ( $hook && 'action' === $hook->get_resource() ) {
					$topic = 'action';
				} elseif ( $hook ) {
					$topic = $hook->get( 'topic' );
				}
				$settings[] = array(
					'title'   => __( 'Topic', 'lifterlms' ),
					'id'      => 'llms_rest_webhook_topic',
					'type'    => 'select',
					'class'   => 'llms-select2',
					'options' => LLMS_REST_API()->webhooks()->get_topics(),
					'value'   => $topic,
				);

				$settings[] = array(
					'title' => __( 'Action', 'lifterlms' ),
					'id'    => 'llms_rest_webhook_action',
					'desc'  => '<br>' . __( 'Any registered WordPress, plugin, or theme action hook.', 'lifterlms' ),
					'type'  => 'text',
					'value' => $hook ? $hook->get_event() : '',
				);

				$settings[] = array(
					'title'             => __( 'Delivery URL', 'lifterlms' ),
					'id'                => 'llms_rest_webhook_delivery_url',
					'desc'              => '<br>' . __( 'URL where the webhook payload will be delivered.', 'lifterlms' ),
					'type'              => 'text',
					'css'               => 'width:480px',
					'class'             => 'code widefat',
					'value'             => $hook ? $hook->get( 'delivery_url' ) : '',
					'custom_attributes' => array(
						'required' => 'required',
					),
				);

				$settings[] = array(
					'title' => __( 'Secret Key', 'lifterlms' ),
					'id'    => 'llms_rest_webhook_secret',
					'desc'  => '<br>' . __( 'The secret key can be used to verify received payloads originated from this website.', 'lifterlms' ),
					'type'  => 'text',
					'css'   => 'width:480px',
					'class' => 'code widefat',
					'value' => $hook ? $hook->get( 'secret' ) : '',
				);

				$buttons = '<br><br><button class="llms-button-primary" type="submit" value="llms-rest-save-webhook">' . __( 'Save', 'lifterlms' ) . '</button>';
				if ( $hook ) {
					$buttons .= $buttons ? '&nbsp;&nbsp;&nbsp;' : '<br><br>';
					$buttons .= '<a class="llms-button-danger" href="' . esc_url( $hook->get_delete_link() ) . '">' . __( 'Delete', 'lifterlms' ) . '</a>';
				}
				$buttons .= wp_nonce_field( 'lifterlms-settings', '_wpnonce', true, false );

				$settings[] = array(
					'type'  => 'custom-html',
					'id'    => 'llms_rest_webhook_buttons',
					'value' => $buttons,
				);

				$settings[] = array(
					'type'  => 'hidden',
					'id'    => 'llms_rest_webhook_id',
					'value' => $hook ? $hook->get( 'id' ) : '',
				);

				$settings[] = array(
					'type'  => 'hidden',
					'id'    => 'llms_rest_webhook_nonce',
					'value' => wp_create_nonce( 'create-update-webhook' ),
				);

			} else {

				$settings[] = array(
					'id'    => 'rest_hooks_options_invalid_error',
					'type'  => 'custom-html',
					'value' => __( 'Invalid webhook.', 'lifterlms' ),
				);

			}
		} else {

			$settings[] = array(
				'id'    => 'llms_webhooks_table',
				'table' => new LLMS_REST_Table_Webhooks(),
				'type'  => 'table',
			);

		}

		$settings[] = array(
			'id'   => 'rest_hooks_options_end',
			'type' => 'sectionend',
		);

		return $settings;

	}

	/**
	 * Quick and dirty output of JS to power the UI.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public static function output_scripts() {
		?>
		<script>
			( function( $ ) {
				$( '#llms_rest_webhook_topic' ).on( 'change', function() {
					var $action = $( '#llms_rest_webhook_action' ).closest( 'tr' );
					if ( 'action' === $( this ).val() ) {
						$action.show();
					} else {
						$action.hide();
					}
				} ).trigger( 'change' );
			} )( jQuery );
		</script>
		<?php
	}

}

