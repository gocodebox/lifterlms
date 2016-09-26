<?php
/**
 * Lesson Settings Metabox
 *
 * @since    1.0.0
 * @version  3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Meta_Box_Lesson extends LLMS_Admin_Metabox {

	/**
	 * This function allows extending classes to configure required class properties
	 * $this->id, $this->title, and $this->screens should be configured in this function
	 *
	 * @return  void
	 * @since   3.0.0
	 * @version 3.0.0
	 */
	public function configure() {

		$this->id = 'lifterlms-lesson';
		$this->title = __( 'Lesson Settings', 'lifterlms' );
		$this->screens = array(
			'lesson',
		);
		$this->priority = 'high';

	}

	/**
	 * This function is where extending classes can configure all the fields within the metabox
	 * The function must return an array which can be consumed by the "output" function
	 *
	 * @return array
	 * @since   3.0.0
	 * @version 3.0.0
	 */
	public function get_fields() {

		return array(
			array(
				'title' 	=> __( 'General', 'lifterlms' ),
				'fields' 	=> array(
					array(
						'class' 	=> 'code input-full',
						'desc' 		=> sprintf( __( 'Paste the url for a Wistia, Vimeo or Youtube video or a hosted video file. For a full list of supported providers see %s.', 'lifterlms' ), '<a href="https://codex.wordpress.org/Embeds#Okay.2C_So_What_Sites_Can_I_Embed_From.3F" target="_blank">WordPress oEmbeds</a>' ),
						'desc_class' => 'd-all',
						'id' 		=> $this->prefix . 'video_embed',
						'label'		=> __( 'Video Embed Url', 'lifterlms' ),
						'type'		=> 'text',
					),
					array(
						'class' 	=> 'code input-full',
						'desc' 		=> sprintf( __( 'Paste the url for a SoundCloud or Spotify song or a hosted audio file. For a full list of supported providers see %s.', 'lifterlms' ), '<a href="https://codex.wordpress.org/Embeds#Okay.2C_So_What_Sites_Can_I_Embed_From.3F" target="_blank">WordPress oEmbeds</a>' ),
						'desc_class' => 'd-all',
						'id' 		=> $this->prefix . 'audio_embed',
						'type'		=> 'text',
						'label'		=> __( 'Audio Embed Url', 'lifterlms' ),
					),
					array(
						'class' 	=> '',
						'desc' 		=> __( 'Checking this box will allow guests to view the content of this lesson without registering or signing up for the course.', 'lifterlms' ),
						'desc_class' => 'd-3of4 t-3of4 m-1of2',
						'id' 		=> $this->prefix . 'free_lesson',
						'is_controller' => true,
						'label'		=> __( 'Free Lesson', 'lifterlms' ),
						'type'		=> 'checkbox',
						'value' 	=> 'yes',
					),
				),
			),
			array(
				'title' 	=> __( 'Prerequisites', 'lifterlms' ),
				'fields' 	=> array(

					array(
						'class' 	=> '',
						'controls' => '#' . $this->prefix . 'prerequisite',
						'desc' 		=> __( 'Enable to choose a prerequisite Lesson', 'lifterlms' ),
						'desc_class' => 'd-3of4 t-3of4 m-1of2',
						'id' 		=> $this->prefix . 'has_prerequisite',
						'label'		=> __( 'Enable Prerequisite', 'lifterlms' ),
						'type'		=> 'checkbox',
						'value' 	=> 'yes',
					),
					array(
						'class' 	 => 'llms-select2-post',
						'data_attributes' => array(
							'allow-clear' => true,
							'placeholder' => __( 'Select a Prerequisite Lesson', 'lifterlms' ),
							'post-type' => 'lesson',
						),
						'desc' 		 => __( 'Select the prerequisite lesson', 'lifterlms' ),
						'desc_class' => 'd-all',
						'id' 		 => $this->prefix . 'prerequisite',
						'label'		 => __( 'Choose Prerequisite', 'lifterlms' ),
						'type'		 => 'select',
						'value' 	=> llms_make_select2_post_array( array( get_post_meta( $this->post->ID, $this->prefix . 'prerequisite', true ) ) ),
					),
				),
			),

			array(
				'title' 	=> __( 'Drip Settings', 'lifterlms' ),
				'fields' 	=> array(
					array(
						'class' 	=> 'llms-select2',
						'desc' 		=> __( 'Choose a method to determine how to drip this lesson to enrolled students', 'lifterlms' ),
						'desc_class' => 'd-all',
						'id' 		=> $this->prefix . 'drip_method',
						'is_controller' => true,
						'label'		=> __( 'Drip Method', 'lifterlms' ),
						'type'		=> 'select',
						'value'     => array(
							array(
								'key' => 'date',
								'title' => __( 'Available on a specific date', 'lifterlms' ),
							),
							array(
								'key' => 'enrollment',
								'title' => __( 'Available # of days after after enrollment in the course', 'lifterlms' ),
							),
							array(
								'key' => 'start',
								'title' => __( 'Available # of days after course start date', 'lifterlms' ),
							),
						),
					),
					array(
						'controller' => '#' . $this->prefix . 'drip_method',
						'controller_value' => 'lesson,enrollment,start',
						'class' 	=> 'input-full',
						'id' 		=> $this->prefix . 'days_before_available',
						'label'		=> __( 'Number of days ', 'lifterlms' ),
						'type'		=> 'number',
					),
					array(
						'controller' => '#' . $this->prefix . 'drip_method',
						'controller_value' => 'date',
						'class' 	=> 'llms-datepicker',
						'id' 		=> $this->prefix . 'date_available',
						'label'		=> __( 'Date Available', 'lifterlms' ),
						'type'		=> 'date',
					),
					array(
						'controller' => '#' . $this->prefix . 'drip_method',
						'controller_value' => 'date',
						'class' 	=> '',
						'desc'      => __( 'Optionally enter a time when the lesson should become available. If no time supplied, leson will be available at 12:00 AM on the date supplied above. Format must be HH:MM AM', 'lifterlms' ),
						'id' 		=> $this->prefix . 'time_available',
						'label'		=> __( 'Time Available', 'lifterlms' ),
						'type'		=> 'text',
					),
				),
			),
			array(
				'title' 	=> __( 'Quiz', 'lifterlms' ),
				'fields' 	=> array(
					array(
						'controller' => '#' . $this->prefix . 'free_lesson',
						'controller_value' => 'yes',
						'id' => 'free-lesson-quiz-error',
						'label' => '',
						'type' => 'custom-html',
						'value' => __( 'Quizzes cannot be assigned to free lessons.', 'lifterlms' ),
					),
					array(
						'class' 	=> 'llms-select2-post',
						'controller' => '#' . $this->prefix . 'free_lesson',
						'controller_value' => 'false',
						'data_attributes' => array(
							'allow-clear' => true,
							'placeholder' => __( 'Select a Quiz', 'lifterlms' ),
							'post-type' => 'llms_quiz',
						),
						'desc' 		=> __( 'Quiz will be required to complete lesson.', 'lifterlms' ),
						'desc_class' => 'd-all',
						'id' 		=> $this->prefix . 'assigned_quiz',
						'label'		=> __( 'Assigned Quiz', 'lifterlms' ),
						'type'		=> 'select',
						'value' 	=> llms_make_select2_post_array( array( get_post_meta( $this->post->ID, $this->prefix . 'assigned_quiz', true ) ) ),
					),
					array(
						'controller' => '#' . $this->prefix . 'free_lesson',
						'controller_value' => 'false',
						'desc' 		=> __( 'Checking this box will require students to get a passing score on the above quiz to complete the lesson.', 'lifterlms' ),
						'desc_class' => 'd-3of4 t-3of4 m-1of2',
						'id' 		=> $this->prefix . 'require_passing_grade',
						'label'		=> __( 'Require Passing Grade', 'lifterlms' ),
						'type'		=> 'checkbox',
						'value' 	=> 'yes',
					),
				),
			),
		);
	}

}
