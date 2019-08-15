<?php
/**
 * Admin Settings Page: REST API
 *
 * @package LifterLMS_REST/Admin/Classes
 *
 * @since 1.0.0-beta.1
 * @version 1.0.0-beta.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin Settings Page: REST API
 *
 * @since 1.0.0-beta.1
 */
class LLMS_Rest_Admin_Settings_API_Keys {

	/**
	 * Holds an LLMS_REST_API_Key instance when a new key is generated.
	 *
	 * Used to show consumer key & secret one time immediately following creation.
	 *
	 * @var null
	 */
	private static $generated_key = null;

	/**
	 * Get settings fields for the Keys tab.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return array
	 */
	public static function get_fields() {

		require_once 'tables/class-llms-rest-table-api-keys.php';

		$add_key = '1' === llms_filter_input( INPUT_GET, 'add-key', FILTER_SANITIZE_NUMBER_INT );
		$key_id  = llms_filter_input( INPUT_GET, 'edit-key', FILTER_SANITIZE_NUMBER_INT );

		$settings = array();

		$settings[] = array(
			'class' => 'top',
			'id'    => 'rest_keys_options_start',
			'type'  => 'sectionstart',
		);

		$settings[] = array(
			'title' => $key_id || $add_key ? __( 'API Key Details', 'lifterlms' ) : __( 'API Keys', 'lifterlms' ),
			'type'  => 'title-with-html',
			'id'    => 'rest_keys_options_title',
			'html'  => $key_id || $add_key ? '' : '<a href="' . esc_url( admin_url( 'admin.php?page=llms-settings&tab=rest-api&section=keys&add-key=1' ) ) . '" class="llms-button-primary small" type="submit" style="top:-2px;">' . __( 'Add API Key', 'lifterlms' ) . '</a>',
		);

		if ( $add_key || $key_id ) {

			$key = $add_key ? false : new LLMS_REST_API_Key( $key_id );
			if ( self::$generated_key ) {
				$key = self::$generated_key;
			}
			if ( $add_key || $key->exists() ) {

				$user_id = $key ? $key->get( 'user_id' ) : get_current_user_id();

				$settings[] = array(
					'title' => __( 'Description', 'lifterlms' ),
					'desc'  => '<br>' . __( 'A friendly, human-readable, name used to identify the key.', 'lifterlms' ),
					'id'    => 'llms_rest_key_description',
					'type'  => 'text',
					'value' => $key ? $key->get( 'description' ) : '',
				);

				$settings[] = array(
					'title'             => __( 'User', 'lifterlms' ),
					'class'             => 'llms-select2-student',
					'custom_attributes' => array(
						'data-placeholder' => __( 'Select a user', 'lifterlms' ),
					),
					'id'                => 'llms_rest_key_user_id',
					'options'           => llms_make_select2_student_array( array( $user_id ) ),
					'type'              => 'select',
				);

				$settings[] = array(
					'title'   => __( 'Permissions', 'lifterlms' ),
					'id'      => 'llms_rest_key_permissions',
					'type'    => 'select',
					'options' => LLMS_REST_API()->keys()->get_permissions(),
					'value'   => $key ? $key->get( 'permissions' ) : '',
				);

				if ( $key && ! self::$generated_key ) {

					$settings[] = array(
						'title'             => __( 'Consumer key ending in', 'lifterlms' ),
						'custom_attributes' => array(
							'readonly' => 'readonly',
						),
						'class'             => 'code',
						'id'                => 'llms_rest_key__read_only_key',
						'type'              => 'text',
						'value'             => '&hellip;' . $key->get( 'truncated_key' ),
					);

					$settings[] = array(
						'title'             => __( 'Last accessed at', 'lifterlms' ),
						'custom_attributes' => array(
							'readonly' => 'readonly',
						),
						'id'                => 'llms_rest_key__read_only_date',
						'type'              => 'text',
						'value'             => $key->get_last_access_date(),
					);

				} elseif ( self::$generated_key ) {

					$settings[] = array(
						'title'             => __( 'Consumer key', 'lifterlms' ),
						'custom_attributes' => array(
							'readonly' => 'readonly',
						),
						'css'               => 'width:400px',
						'class'             => 'code widefat',
						'id'                => 'llms_rest_key__read_only_key',
						'type'              => 'text',
						'value'             => $key->get( 'consumer_key_one_time' ),
					);

					$settings[] = array(
						'title'             => __( 'Consumer secret', 'lifterlms' ),
						'custom_attributes' => array(
							'readonly' => 'readonly',
						),
						'css'               => 'width:400px',
						'class'             => 'code widefat',
						'id'                => 'llms_rest_key__read_only_secret',
						'type'              => 'text',
						'value'             => $key->get( 'consumer_secret' ),
					);

				}

				$buttons = self::$generated_key ? '' : '<br><br><button class="llms-button-primary" type="submit" value="llms-rest-save-key">' . __( 'Save', 'lifterlms' ) . '</button>';
				if ( $key ) {
					$buttons .= $buttons ? '&nbsp;&nbsp;&nbsp;' : '<br><br>';
					$buttons .= '<a class="llms-button-danger" href="' . esc_url( $key->get_delete_link() ) . '">' . __( 'Revoke', 'lifterlms' ) . '</a>';
				}
				$buttons .= wp_nonce_field( 'lifterlms-settings', '_wpnonce', true, false );

				$settings[] = array(
					'type'  => 'custom-html',
					'id'    => 'llms_rest_key_buttons',
					'value' => $buttons,
				);

			} else {

				$settings[] = array(
					'id'    => 'rest_keys_options_invalid_error',
					'type'  => 'custom-html',
					'value' => __( 'Invalid api key.', 'lifterlms' ),
				);

			}
		} else {

			$settings[] = array(
				'id'    => 'llms_api_keys_table',
				'table' => new LLMS_REST_Table_API_Keys(),
				'type'  => 'table',
			);

		}

		$settings[] = array(
			'id'   => 'rest_keys_options_end',
			'type' => 'sectionend',
		);

		return $settings;

	}

	/**
	 * Form handler to save Create / Update an API key.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return null|LLMS_REST_API_Key|WP_Error
	 */
	public static function save() {

		$ret = null;

		$key_id = llms_filter_input( INPUT_GET, 'edit-key', FILTER_SANITIZE_NUMBER_INT );
		if ( $key_id ) {
			$ret = self::save_update( $key_id );
		} elseif ( llms_filter_input( INPUT_GET, 'add-key', FILTER_SANITIZE_NUMBER_INT ) ) {
			$ret = self::save_create();
			if ( ! is_wp_error( $ret ) ) {
				LLMS_Admin_Settings::set_message( __( 'API Key generated. Make sure to copy the consumer key and consumer secret. After leaving this page they will not be displayed again.', 'lifterlms' ) );
			}
		}

		if ( is_wp_error( $ret ) ) {
			// Translators: %1$s = Error message; %2$s = Error code.
			LLMS_Admin_Settings::set_error( sprintf( __( 'Error: %1$s [Code: %2$s]', 'lifterlms' ), $ret->get_error_message(), $ret->get_error_code() ) );
		}

		return $ret;

	}

	/**
	 * Form handler to create a new API key.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return LLMS_REST_API_Key|WP_Error
	 */
	protected static function save_create() {

		$create = LLMS_REST_API()->keys()->create(
			array(
				'description' => llms_filter_input( INPUT_POST, 'llms_rest_key_description', FILTER_SANITIZE_STRING ),
				'user_id'     => llms_filter_input( INPUT_POST, 'llms_rest_key_user_id', FILTER_SANITIZE_NUMBER_INT ),
				'permissions' => llms_filter_input( INPUT_POST, 'llms_rest_key_permissions', FILTER_SANITIZE_STRING ),
			)
		);

		if ( ! is_wp_error( $create ) ) {
			self::$generated_key = $create;
		}

		return $create;

	}

	/**
	 * Form handler to save an API key.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param int $key_id API Key ID.
	 * @return LLMS_REST_API_Key|WP_Error
	 */
	protected static function save_update( $key_id ) {

		$key = LLMS_REST_API()->keys()->get( $key_id );
		if ( ! $key ) {
			// Translators: %s = Invalid API Key ID.
			return new WP_Error( 'llms_rest_api_key_not_found', sprintf( __( '"%s" is not a valid API Key.', 'lifterlms' ), $key_id ) );
		}

		$update = LLMS_REST_API()->keys()->update(
			array(
				'id'          => $key_id,
				'description' => llms_filter_input( INPUT_POST, 'llms_rest_key_description', FILTER_SANITIZE_STRING ),
				'user_id'     => llms_filter_input( INPUT_POST, 'llms_rest_key_user_id', FILTER_SANITIZE_NUMBER_INT ),
				'permissions' => llms_filter_input( INPUT_POST, 'llms_rest_key_permissions', FILTER_SANITIZE_STRING ),
			)
		);

		return $update;

	}

}

