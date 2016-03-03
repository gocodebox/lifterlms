<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! defined( 'LLMS_Admin_Metabox' ) ) {
	// Include the file for the parent class
	include_once LLMS_PLUGIN_DIR . '/includes/admin/llms.class.admin.metabox.php';
}

/**
* Meta Box Builder
*
* Generates main metabox and builds forms
*/
class LLMS_Meta_Box_Certificate extends LLMS_Admin_Metabox{

	public static $prefix = '_';

	/**
	 * Function to field WP::output() method call
	 * Passes output instruction to parent
	 *
	 * @param object $post WP global post object
	 * @return void
	 */
	public static function output ( $post ) {
		global $post;
		parent::new_output( $post, self::metabox_options() );
	}

	/**
	 * Builds array of metabox options.
	 * Array is called in output method to display options.
	 * Appropriate fields are generated based on type.
	 *
	 * @return array [md array of metabox fields]
	 */
	public static function metabox_options() {
		global $post;

		$meta_fields_certificate = array(
			array(
				'title' 	=> 'General',
				'fields' 	=> array(
					array(
						'label' 	=> 'Certificate Title',
						'desc' 		=> 'Enter a title for your certificate. EG: Certificate of Completion',
						'id' 		=> self::$prefix . 'llms_certificate_title',
						'type'  	=> 'text',
						'section' 	=> 'certificate_meta_box',
						'class' 	=> 'code input-full',
						'desc_class' => 'd-all',
						'group' 	=> '',
						'value' 	=> '',
					),
					array(
						'label'  	=> 'Background Image',
						'desc'  	=> 'Select an Image to use for the certificate.',
						'id'    	=> self::$prefix . 'llms_certificate_image',
						'type'  	=> 'image',
						'section' 	=> 'certificate_meta_box',
						'class' 	=> 'certificate',
						'desc_class' => 'd-all',
						'group' 	=> '',
						'value' 	=> '',
					),
					array(
						'label'  	=> '',
						'desc'  	=> '',
						'id'    	=> self::$prefix . 'llms_help',
						'type'  	=> 'custom-html',
						'section' 	=> 'certificate_meta_box',
						'class' 	=> 'code input-full',
						'desc_class' => 'd-all',
						'group' 	=> '',
						'value' 	=> '<p>Use the text editor above to add content to your certificate.
										You can include any of the following merge fields. 
										<br>{site_title}
										<br>{user_login}
										<br>{site_url}
										<br>{first_name}
										<br>{last_name}
										<br>{email_address}
										<br>{current_date}
										</p>',
					),
				),
			),
		);

		if (has_filter( 'llms_meta_fields_certificate' )) {
			//Add Fields to the achievement Meta Box
			$meta_fields_certificate = apply_filters( 'llms_meta_fields_certificate', $meta_fields_certificate );
		}

		return $meta_fields_certificate;
	}

	/**
	 * Static save method
	 *
	 * cleans variables and saves using update_post_meta
	 *
	 * @param  int 		$post_id [id of post object]
	 * @param  object 	$post [WP post object]
	 *
	 * @return void
	 */
	public static function save( $post_id, $post ) {
		global $wpdb;

	}

}
