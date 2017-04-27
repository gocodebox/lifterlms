<?php
/**
* LifterLMS Facebook Integration
*
* @since   [version]
* @version [version]
*/

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Integration_Facebook extends LLMS_Abstract_Integration {

	public function __construct() {

		add_action( 'wp_print_footer_scripts', array( $this, 'output_scripts' ) );

	}

	/**
	 * Detemine if the integration had been enabled via checkbox
	 * @return   boolean
	 * @since    [version]
	 * @version  [version]
	 */
	public function is_enabled() {
		return ( 'yes' == get_option( 'lifterlms_facebook_enabled', 'yes' ) );
	}

	/**
	 * Determine if the related plugin, theme, 3rd party is
	 * installed and activated
	 * if this does not apply, this should return true without
	 * doing any checks
	 * @return   boolean
	 * @since    [version]
	 * @version  [version]
	 */
	public function is_installed() {
		return true;
	}

	public function output_scripts() {
		// $app_id = apply_filters( 'llms_integration_facebook_app_id', '1740695496193479' );
		?>
		<script id="llms-facebook-jssdk">
		window.fbAsyncInit = function(){
			FB.init({
				// appId: 'your-app-id',
				xfbml: true,
				version: 'v2.9'
			});
		};
		(function(d, s, id){
			var js, fjs = d.getElementsByTagName(s)[0];
			if (d.getElementById(id)) {return;}
			js = d.createElement(s); js.id = id;
			js.src = "//connect.facebook.net/en_US/sdk.js";
			fjs.parentNode.insertBefore(js, fjs);
		}(document, 'script', 'facebook-jssdk'));</script>
		<?php
	}

}
