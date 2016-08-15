<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
* Metabox_Field Parent Class
*
* Contains base code for each of the Metabox Fields
*
* @author codeBOX
* @project lifterLMS
*/
abstract class LLMS_Metabox_Field {

	/**
	 * Global array used in class instance to store field information
	 * @var array
	 */
	public $field;

	/**
	 * Global varaible to contain meta information about $field
	 * @var object
	 */
	public $meta;


	/**
	 * outputs the head for each of the field types
	 * @todo  all the unset variables here should be defaulted somewhere else probably
	 */
	public function output() {

		global $post;
		if ( ! metadata_exists( 'post', $post->ID, $this->field['id'] ) && ! empty( $this->field['default'] ) ) {
			$this->meta = $this->field['default'];
		} else {
			$this->meta = self::get_post_meta( $post->ID, $this->field['id'] );
		}

		$controller = isset( $this->field['controller'] ) ? ' data-controller="' . $this->field['controller'] . '"' : '';
		$controller_value = isset( $this->field['controller_value'] ) ? ' data-controller-value="' . $this->field['controller_value'] . '"' : '';

		if ( ! isset( $this->field['group'] ) ) {
			$this->field['group'] = '';
		}

		if ( ! isset( $this->field['desc_class'] ) ) {
			$this->field['desc_class'] = '';
		}

		if ( ! isset( $this->field['desc'] ) ) {
			$this->field['desc'] = '';
		}

		?>
		<li class="llms-mb-list <?php echo $this->field['group']; ?>"<?php echo $controller . $controller_value; ?>>
		<!--label and description-->
		<div class="description <?php echo $this->field['desc_class']; ?>">
			<label for="<?php echo $this->field['id']; ?>"><?php echo $this->field['label']; ?></label>
			<?php echo $this->field['desc'] ?>
			<?php if ( isset( $this->field['required'] ) && $this->field['required'] ) : ?><em>(required)</em><?php endif; ?>
		</div> <?php
	}

	/**
	 * outputs the tail for each of the field types
	 */
	public function close_output() {

		echo '<div class="clear"></div></li>';

	}

	/**
	 * TBH I'm not sure exactly what this does... But removing it makes everything break.
	 * Your best bet is to ask Mark...
	 *
	 * @param  [type]
	 * @param  [type]
	 * @return [type]
	 */
	public static function get_post_meta( $post_id, $field_id ) {

		if ( $field_id === '_post_course_difficulty' ) {
			$difficulties = wp_get_object_terms( $post_id, 'course_difficulty' );

			if ( $difficulties ) {
				return $difficulties[0]->slug;
			}

		} else {
			return get_post_meta( $post_id, $field_id, true );
		}

	}
}
