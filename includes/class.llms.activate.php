<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class LLMS_Activate {
 
    protected static $instance = null;
 
    private function __construct() {
        add_action( 'lifterlms_update_options', array( $this, 'get_post_response' ) );
    }
 
    public static function get_instance() {
 
        if ( null == self::$instance ) {
            self::$instance = new self;
        }
 
        return self::$instance;
 
    }
 
    public function get_post_response( ) {
    	$is_active = get_option('lifterlms_is_activated', '');

      	$action = 'llms_activate_plugin';
        $authkey = get_option('lifterlms_authkey', '');
        $license = get_option('lifterlms_activation_key', '');
        $site_url = get_bloginfo('url');

        if (($license && $is_active == 'yes') || $license == '') {
        	return;
        }

        $url = 'http://dev.gocodebox.com/llms/wp-admin/admin-ajax.php';

        $response = wp_remote_post(
            $url,
            array(
                'body' => array(
                'action'   => $action,
                'authkey'     => $authkey,
                'license' => $license,
                'url' => $site_url
                )
            )
        );

        if ( is_wp_error( $response ) ) {

        	update_option('lifterlms_activation_message', $activation_response->message);

        }
        else {

			$activation_response = json_decode ($response['body']);
			if ($activation_response->success) {
				update_option('lifterlms_is_activated', 'yes');
				update_option('lifterlms_update_key', $activation_response->update_key);
			}
			else {
				update_option('lifterlms_activation_message', $activation_response->message);
			}

        }
    }
}
