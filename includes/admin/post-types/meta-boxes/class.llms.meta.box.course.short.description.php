<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * Meta Box Short Description
 *
 * Overrides WP short description
 */
class LLMS_Meta_Box_Course_Short_Description {

	/**
	 * outputs tinymce
	 *
	 * @return mixed (wp_editor)
	 * @param string $post
	 */
	public static function output( $post ) {
		$settings = array(
			'textarea_name' => 'excerpt',
			'quicktags'     => array(
				'buttons' => 'em,strong,link',
			),
			'tinymce'       => array(
				'theme_advanced_buttons1' => 'bold,italic,strikethrough,separator,bullist,numlist,separator,blockquote,separator,justifyleft,justifycenter,justifyright,separator,link,unlink,separator,undo,redo,separator',
				'theme_advanced_buttons2' => '',
			),
			'editor_css'    => '<style>#wp-excerpt-editor-container .wp-editor-area{height:175px; width:100%;}</style>',
		);

		wp_editor( htmlspecialchars_decode( $post->post_excerpt ), 'excerpt', apply_filters( 'lifterlms_course_short_description_editor_settings', $settings ) );
	}

}
