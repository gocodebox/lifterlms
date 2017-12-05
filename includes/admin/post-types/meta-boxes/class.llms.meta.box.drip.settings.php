<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Course element drip settings
 * Applies to lessons & quizzes
 *
 * @since    [version]
 * @version  [version]
 */
class LLMS_Meta_Box_Drip_Settings extends LLMS_Admin_Metabox {

	/**
	 * This function allows extending classes to configure required class properties
	 * $this->id, $this->title, and $this->screens should be configured in this function
	 *
	 * @return  void
	 * @since   [version]
	 * @version [version]
	 */
	public function configure() {

		$this->id = 'drip-settings';
		$this->title = __( 'Drip Settings', 'lifterlms' );
		$this->screens = array(
			'lesson',
			'llms_quiz',
		);
		$this->priority = 'default';
		$this->context = 'side';

	}

	/**
	 * This function is where extending classes can configure all the fields within the metabox
	 * The function must return an array which can be consumed by the "output" function
	 *
	 * @return array
	 * @since   [version]
	 * @version [version]
	 */
	public function get_fields() {

		$object = llms_get_post( $this->post );

		$methods = array(
			'date' => __( 'On a specific date', 'lifterlms' ),
			'enrollment' => __( 'After course enrollment', 'lifterlms' ),
			'start' => __( 'After course start date', 'lifterlms' ),
		);

		// if the object isn't first, add previous completion method
		if ( ! $object->is_first() ) {
			$methods['complete'] = __( 'After previous element completion', 'lifterlms' );
		}

		return array(
			array(
				'title' => __( 'Drip Settings', 'lifterlms' ),
				'fields' => array(
					array(
						'class' => 'llms-select2',
						// 'desc' => __( 'Choose a method to determine how to drip this lesson to enrolled students', 'lifterlms' ),
						'desc_class' => 'd-all',
						'id' => $this->prefix . 'drip_method',
						'is_controller' => true,
						'label' => __( 'Method', 'lifterlms' ),
						'type' => 'select',
						'value' => $methods,
					),
					array(
						'controller' => '#' . $this->prefix . 'drip_method',
						'controller_value' => 'lesson,enrollment,start,complete',
						'class' => 'input-full',
						'id' => $this->prefix . 'days_before_available',
						'label' => __( 'Delay (in days) ', 'lifterlms' ),
						'type' => 'number',
						'step' => 1,
						'min' => 1,
					),
					array(
						'controller' => '#' . $this->prefix . 'drip_method',
						'controller_value' => 'date',
						'class' => 'llms-datepicker',
						'id' => $this->prefix . 'date_available',
						'label' => __( 'Date Available', 'lifterlms' ),
						'type' => 'date',
					),
					array(
						'controller' => '#' . $this->prefix . 'drip_method',
						'controller_value' => 'date',
						'class' => '',
						'desc' => __( 'Optionally enter a time when the lesson should become available. If no time supplied, leson will be available at 12:00 AM. Format must be HH:MM AM', 'lifterlms' ),
						'id' => $this->prefix . 'time_available',
						'label' => __( 'Time Available', 'lifterlms' ),
						'type' => 'text',
					),
				),
			),
		);

	}

}

return new LLMS_Meta_Box_Drip_Settings();
