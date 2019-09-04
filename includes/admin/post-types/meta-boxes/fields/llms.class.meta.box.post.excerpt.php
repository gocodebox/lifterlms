<?php
/**
 * Metabox Field: Post Excerpt
 *
 * @since Unknown
 * @version Unknown
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Metabox_Post_Excerpt_Field
 *
 * @since Unknown
 */
class LLMS_Metabox_Post_Excerpt_Field extends LLMS_Metabox_Field implements Meta_Box_Field_Interface {

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
			'textarea_name'    => 'excerpt',
			'quicktags'        => array(
				'buttons' => 'em,strong,link',
			),
			'tinymce'          => array(
				'theme_advanced_buttons1' => 'bold,italic,strikethrough,separator,bullist,numlist,separator,blockquote,separator,justifyleft,justifycenter,justifyright,separator,link,unlink,separator,undo,redo,separator',
				'theme_advanced_buttons2' => '',
			),
			'editor_class'     => 'llms-post-editor',
			'editor_css'       => '<style>#excerpt_ifr{height:300px}#wp-excerpt-editor-container .wp-editor-area{height:300px; width:100%;}</style>',
			'drag_drop_upload' => true,
		);

		wp_editor( htmlspecialchars_decode( $post->post_excerpt ), 'excerpt', apply_filters( 'lifterlms_course_short_description_editor_settings', $settings ) );

		echo '<div class="clear"></div>';

		parent::close_output();

	}
}

