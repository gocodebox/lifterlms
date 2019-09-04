<?php
defined( 'ABSPATH' ) || exit;

/**
 * Metabox_Field Parent Class
 * Contains base code for each of the Metabox Fields
 *
 * @since    ??
 * @version  3.24.0
 */
abstract class LLMS_Metabox_Field {

	/**
	 * Global array used in class instance to store field information
	 *
	 * @var array
	 */
	public $field;

	/**
	 * Global variable to contain meta information about $field
	 *
	 * @var object
	 */
	public $meta;

	/**
	 * outputs the head for each of the field types
	 *
	 * @todo  all the unset variables here should be defaulted somewhere else probably
	 * @since    ??
	 * @version  3.11.0
	 */
	public function output() {

		global $post;
		if ( ( ! metadata_exists( 'post', $post->ID, $this->field['id'] ) || 'auto-draft' === $post->post_status ) && ! empty( $this->field['default'] ) ) {
			$this->meta = $this->field['default'];
		} else {
			$this->meta = self::get_post_meta( $post->ID, $this->field['id'] );
		}

		$controller       = isset( $this->field['controller'] ) ? ' data-controller="' . $this->field['controller'] . '"' : '';
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

		$wrapper_classes   = array( 'llms-mb-list' );
		$wrapper_classes[] = $this->field['id'];
		$wrapper_classes[] = $this->field['type'];
		$wrapper_classes   = array_merge( $wrapper_classes, explode( ' ', $this->field['group'] ) );

		?>
		<li class="<?php echo implode( ' ', $wrapper_classes ); ?>"<?php echo $controller . $controller_value; ?>>
			<div class="description <?php echo $this->field['desc_class']; ?>">
				<label for="<?php echo $this->field['id']; ?>"><?php echo $this->field['label']; ?></label>
				<?php echo $this->field['desc']; ?>
				<?php
				if ( isset( $this->field['required'] ) && $this->field['required'] ) :
					?>
					<em>(required)</em><?php endif; ?>
			</div> 
			<?php
	}

	/**
	 * outputs the tail for each of the field types
	 */
	public function close_output() {

		echo '<div class="clear"></div></li>';

	}

	/**
	 * Set the default meta value of a field
	 *
	 * @param    int    $post_id   WP Post ID
	 * @param    string $field_id  ID/name of the field
	 * @return   mixed
	 * @since    1.0.0
	 * @version  3.24.0
	 */
	public static function get_post_meta( $post_id, $field_id ) {

		if ( '_post_course_difficulty' === $field_id ) {
			$difficulties = wp_get_object_terms( $post_id, 'course_difficulty' );

			if ( $difficulties ) {
				return $difficulties[0]->slug;
			}
		} else {
			return get_post_meta( $post_id, $field_id, true );
		}

	}
}
