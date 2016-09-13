<?php
/**
 * Acheivements Metabox
 *
 * Generates main metabox and builds forms
 *
 * @since  1.0.0
 * @version  3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Meta_Box_Achievement extends LLMS_Admin_Metabox {


	/**
	 * Configure the metabox settings
	 * @return void
	 * @since  3.0.0
	 * @version  3.0.0
	 */
	public function configure() {

		$this->id = 'lifterlms-achievement';
		$this->title = __( 'Achievement Settings', 'lifterlms' );
		$this->screens = array(
			'llms_achievement',
		);
		$this->priority = 'high';

	}

	/**
	 * Builds array of metabox options.
	 * Array is called in output method to display options.
	 * Appropriate fields are generated based on type.
	 *
	 * @return array
	 *
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function get_fields() {

		return array(
			array(
				'title' 	=> 'General',
				'fields' 	=> array(
					array(
						'label' 	=> __( 'Achievement Title', 'lifterlms' ),
						'desc' 		=> __( 'Enter a title for your achievement. IE: Achievement of Completion', 'lifterlms' ),
						'id' 		=> $this->prefix . 'achievement_title',
						'type'  	=> 'text',
						'section' 	=> 'achievement_meta_box',
						'class' 	=> 'code input-full',
						'desc_class' => 'd-all',
						'group' 	=> '',
						'value' 	=> '',
					),
					//Achievment content textarea
					array(
						'label' 	=> __( 'Achievement Content', 'lifterlms' ),
						'desc' 		=> __( 'Enter any information you would like to display on the achievement.', 'lifterlms' ),
						'id' 		=> $this->prefix . 'achievement_content',
						'type'  	=> 'textarea_w_tags',
						'section' 	=> 'achievement_meta_box',
						'class' 	=> 'code input-full',
						'desc_class' => 'd-all',
						'group' 	=> '',
						'value' 	=> '',
					),
					//Achievement background image
					array(
						'label'  	=> __( 'Background Image', 'lifterlms' ),
						'desc'  	=> __( 'Select an Image to use for the achievement.', 'lifterlms' ),
						'id'    	=> $this->prefix . 'achievement_image',
						'type'  	=> 'image',
						'section' 	=> 'achievement_meta_box',
						'class' 	=> 'achievement',
						'desc_class' => 'd-all',
						'group' 	=> '',
						'value' 	=> '',
					),
				),
			),
		);

	}

}
