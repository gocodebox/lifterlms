<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* Activation base class
*
* Class used for connecting to the codeBOX activation api to activate lifterLMS
*
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Activate {
 
    /**
     * Instance of API connection
     * @var null
     */
    protected static $instance = null;
 
    /**
     * Constructor
     * Adds action to lifterlms_update_options to trigger API call
     */
    private function __construct() {
        add_action( 'lifterlms_update_options', array( $this, 'get_post_response' ) );
    }
 
    /**
     * Gets current instance of API connection
     * @return static string [instance of current API connection]
     */
    public static function get_instance() {
 
        if ( null == self::$instance ) {
            self::$instance = new self;
        }
 
        return self::$instance;
 
    }
 
    /**
     * Interprets response from activation request.
     * If successful updates options and sets plugin to activated.
     * @return void
     */
    public function get_post_response( ) {
        $is_active = get_option('lifterlms_is_activated', '');
        $deactivate = get_option('lifterlms_activation_deactivate', '');

        $action = 'llms_activate_plugin';
        $authkey = get_option('lifterlms_authkey', '');
        $license = get_option('lifterlms_activation_key', '');
        $site_url = get_bloginfo('url');

        //if deactivate option set to yes then deactivate 
        //if ( $deactivate === 'yes' && $is_active === 'yes' ) {
        if ( $deactivate === 'yes'  ) {

            $this->deactivate( $authkey, $license, $site_url );

        } else {

            if (($license && $is_active == 'yes') || ($license == '' && $deactivate !== 'yes' ) ) {
                return;
            }

            $url = 'https://lifterlms.com/wp-admin/admin-ajax.php';

            $request =  array(
                    'action'   => $action,
                    'authkey'     => $authkey,
                    'license' => $license,
                    'url' => $site_url
                    );

            $postdata = http_build_query(
                            array(
                                'action'   => $action,
                                'authkey'     => $authkey,
                                'license' => $license,
                                'url' => $site_url
                                )
                            );

            $opts = array('http' =>
                                array(
                                    'method'  => 'POST',
                                    'header'  => 'Content-type: application/x-www-form-urlencoded',
                                    'content' => $postdata
                                )
                    );
            

            if($this->get_http_response_code($url) != "200")
            {
                update_option('lifterlms_activation_message', 'There was an error contacting the LifterLMS activation server. Please contact support@lifterlms or try again.');
            }
            else
            {

                $context  = stream_context_create($opts);

                $result = file_get_contents($url, false, $context);

                if ($result) {

                    $response = json_decode($result);
                    // var_dump($response);

                    if (!$response->success) {

                        update_option('lifterlms_activation_message', $response->message );
                    
                    } else {
                        update_option('lifterlms_activation_message', 'Activated' );
                        update_option('lifterlms_is_activated', 'yes');
                        update_option('lifterlms_update_key', $response->update_key);

                    }
                }
                else
                {
                     update_option('lifterlms_activation_message', 'There was an error calling file_get_contents(). Please set allow_url_fopen to "1" in your php.ini file. Contact your hosting provider if you are unsure what to do.');
                }

            } 
 
        }
    }

    /**
    * check response code for activation url before proceeding 
    */
    public function get_http_response_code($url) 
    {
        $headers = get_headers($url);
        llms_log($headers);
        return substr($headers[0], 9, 3);
    }

    /**
     * Deactivates the plugin by updating the site options
     * @return void
     */
    public function deactivate( $authkey, $license, $site_url ) {

        $url = 'https://lifterlms.com/wp-admin/admin-ajax.php';
        $action = 'llms_deactivate_site';

        $response = wp_remote_post(
            $url,
            array(
                'body' => array(
                'action'   => $action,
                'key' => $license,
                'url' => $site_url
                ),
                'sslverify' => false
            )
        );
        if ( is_wp_error( $response ) ) { 

        } else {

            update_option('lifterlms_activation_key', '');
            update_option('lifterlms_is_activated', '');
            update_option('lifterlms_activation_deactivate', '');
            update_option('lifterlms_activation_message', '');

        }

    }

}
