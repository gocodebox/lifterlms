<?php
/**
 * Quiz Metabox
 *
 * @since    1.0.0
 * @version  3.12.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Meta_Box_Quiz extends LLMS_Admin_Metabox {

	/**
	 * Configure the metabox settings
	 * @return void
	 * @since  3.0.0
	 */
	public function configure() {

		$this->id = 'lifterlms-quiz';
		$this->title = __( 'Quiz Settings', 'lifterlms' );
		$this->screens = array(
			'llms_quiz',
		);
		$this->priority = 'high';

	}

	/**
	 * This function is where extending classes can configure all the fields within the metabox
	 * The function must return an array which can be consumed by the "output" function
	 * @return array
	 * @since    3.0.0
	 * @version  3.12.0
	 */
	public function get_fields() {

		return array(
			array(
				'title' 	=> __( 'General', 'lifterlms' ),
				'fields' 	=> array(
					array(
						'type'  	=> 'number',
						'label' 	=> __( 'Allowed Attempts', 'lifterlms' ),
						'desc' 		=> __( 'Number of allowed attempts. Leave blank for unlimited attempts.', 'lifterlms' ),
						'id' 		=> $this->prefix . 'allowed_attempts',
						'class' 	=> 'code input-full',
						'desc_class' => 'd-all',
						'group' 	=> '',
						'value' 	=> '',
					),
					array(
						'type'  	=> 'number',
						'label'  	=> __( 'Passing Percentage', 'lifterlms' ),
						'desc'  	=> __( 'Enter the percent required to pass quiz.', 'lifterlms' ),
						'id'    	=> $this->prefix . 'passing_percent',
						'class' 	=> 'code input-full',
						'min'       => 0,
						'max'       => 100,
						'desc_class' => 'd-all',
						'group' 	=> '',
						'value' 	=> '',
					),
					array(
						'type'  	=> 'number',
						'min'		=> '0',
						'label'  	=> __( 'Time Limit', 'lifterlms' ),
						'desc'  	=> __( 'Enter a time limit for quiz completion in minutes. Leave empty if no time limit.', 'lifterlms' ),
						'id'    	=> $this->prefix . 'time_limit',
						'class' 	=> 'code input-full',
						'desc_class' => 'd-all',
						'group' 	=> '',
						'value' 	=> '',
					),
					array(
						'type'  	=> 'checkbox',
						'label'  	=> __( 'Randomize Questions', 'lifterlms' ),
						'desc'  	=> __( 'Select to randomize the order of questions on each attempt', 'lifterlms' ),
						'id'    	=> $this->prefix . 'random_questions',
						'class' 	=> '',
						'desc_class' => 'd-3of4 t-3of4 m-1of2',
						'group' 	=> '',
						'value' 	=> 'yes',
					),
					array(
						'type'  	=> 'checkbox',
						'label'  	=> __( 'Randomize Answers', 'lifterlms' ),
						'desc'  	=> __( 'Select to randomize quiz answers', 'lifterlms' ),
						'id'    	=> $this->prefix . 'random_answers',
						'class' 	=> '',
						'desc_class' => 'd-3of4 t-3of4 m-1of2',
						'group' 	=> '',
						'value' 	=> 'yes',
					),
				),
			),
			array(
				'title' 	=> __( 'Results', 'lifterlms' ),
				'fields' 	=> array(
					array(
						'type'  	=> 'checkbox',
						'label'  	=> __( 'Show Results', 'lifterlms' ),
						'desc'  	=> __( 'Display Last Quiz Results to User', 'lifterlms' ),
						'id'    	=> $this->prefix . 'show_results',
						'is_controller' => true,
						'class' 	=> '',
						'desc_class' => 'd-3of4 t-3of4 m-1of2',
						'group' 	=> '',
						'value' 	=> 'yes',
					),
					array(
						'controller' => '#' . $this->prefix . 'show_results',
						'controller_value' => 'yes',
						'type'  	=> 'checkbox',
						'label'  	=> __( 'Show Correct Answer', 'lifterlms' ),
						'desc'  	=> __( 'Display Correct Answer on Incorrect Questions', 'lifterlms' ),
						'id'    	=> $this->prefix . 'show_correct_answer',
						'class' 	=> '',
						'desc_class' => 'd-3of4 t-3of4 m-1of2',
						'value' 	=> 'yes',
					),
					array(
						'controller' => '#' . $this->prefix . 'show_results',
						'controller_value' => 'yes',
						'type'  	=> 'checkbox',
						'label'  	=> __( 'Show Description Wrong Answer', 'lifterlms' ),
						'desc'  	=> __( 'Display Picked Option Description on Wrong Questions', 'lifterlms' ),
						'id'    	=> $this->prefix . 'show_options_description_wrong_answer',
						'class' 	=> '',
						'desc_class' => 'd-3of4 t-3of4 m-1of2',
						'value' 	=> 'yes',
					),
					array(
						'controller' => '#' . $this->prefix . 'show_results',
						'controller_value' => 'yes',
						'type'  	=> 'checkbox',
						'label'  	=> __( 'Show Description Right Answer', 'lifterlms' ),
						'desc'  	=> __( 'Display Picked Option Description on Right Questions', 'lifterlms' ),
						'id'    	=> $this->prefix . 'show_options_description_right_answer',
						'class' 	=> '',
						'desc_class' => 'd-3of4 t-3of4 m-1of2',
						'value' 	=> 'yes',
					),
				),
			),
		);

	}
}
