<?php
/**
 * Metabox Field: Content editor
 *
 * @since Unknown
 * @version Unknown
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Metabox_Post_Content_Field
 *
 * @since Unknown
 */
class LLMS_Metabox_Post_Content_Field extends LLMS_Metabox_Field implements Meta_Box_Field_Interface {

	/**
	 * Class constructor
	 *
	 * @param array $_field Array containing information about field
	 */
	public function __construct( $_field ) {

		$this->field = $_field;

	}

	/**
	 * outputs the Html for the given field
	 *
	 * @return void
	 */
	public function output() {

		global $post;

		parent::output();

		$settings = array(
			'textarea_name'    => 'content',
			'quicktags'        => array(
				'buttons' => 'em,strong,link',
			),
			'tinymce'          => array(
				'theme_advanced_buttons1' => 'bold,italic,strikethrough,separator,bullist,numlist,separator,blockquote,separator,justifyleft,justifycenter,justifyright,separator,link,unlink,separator,undo,redo,separator',
				'theme_advanced_buttons2' => '',
			),
			'editor_css'       => '<style>#wp-content-editor-container .wp-editor-area{height:300px; width:100%;}</style>',
			'drag_drop_upload' => true,
		);

		wp_editor( htmlspecialchars_decode( $post->post_content ), 'content', apply_filters( 'lifterlms_course_full_description_editor_settings', $settings ) );

		parent::close_output();
	}
}

