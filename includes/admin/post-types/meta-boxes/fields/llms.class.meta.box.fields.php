<?php
/**
 * Abstract Metabox_Field.
 *
 * @package LifterLMS/Admin/PostTypes/MetaBoxes/Fields/Classes
 *
 * @since unknown
 * @version 6.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Metabox_Field parent class.
 *
 * Contains base code for each of the Metabox Fields.
 *
 * @since unknown
 * @since 3.24.0 Unknown.
 */
abstract class LLMS_Metabox_Field {

	/**
	 * Global array used in class instance to store field information.
	 *
	 * @var array
	 */
	public $field;

	/**
	 * Global variable to contain meta information about $field.
	 *
	 * @var object
	 */
	public $meta;

	/**
	 * Outputs the head for each of the field types.
	 *
	 * @todo All the unset variables here should be defaulted somewhere else probably.
	 *
	 * @since unknown
	 * @since 3.11.0 Unknown.
	 * @since 6.0.0 Do not print empty labels; do not print the description block if both 'desc' and 'label' are empty.
	 *               Avoid retrieving the meta from the db if passed.
	 * @return void
	 */
	public function output() {

		global $post;

		if ( isset( $this->field['meta'] ) ) {
			$this->meta = $this->field['meta'];
		} elseif ( ( ! metadata_exists( 'post', $post->ID, $this->field['id'] ) || 'auto-draft' === $post->post_status ) && ! empty( $this->field['default'] ) ) {
			$this->meta = $this->field['default'];
		} else {
			$this->meta = self::get_post_meta( $post->ID, $this->field['id'] );
		}

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
		<li
			class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>"
								<?php
								echo isset( $this->field['controller'] ) ? ' data-controller="' . esc_attr( $this->field['controller'] ) . '"' : '';
								echo isset( $this->field['controller_value'] ) ? ' data-controller-value="' . esc_attr( $this->field['controller_value'] ) . '"' : '';
								?>
		>
		<?php if ( ! empty( $this->field['desc'] ) || ! empty( $this->field['label'] ) ) : ?>
			<div class="description <?php echo esc_attr( $this->field['desc_class'] ); ?>">
			<?php if ( ! empty( $this->field['label'] ) ) : ?>
				<label for="<?php echo esc_attr( $this->field['id'] ); ?>"><?php echo esc_html( $this->field['label'] ); ?></label>
			<?php endif; ?>
				<?php echo wp_kses_post( $this->field['desc'] ); ?>
				<?php
				if ( isset( $this->field['required'] ) && $this->field['required'] ) :
					?>
					<em>(required)</em><?php endif; ?>
			</div>
			<?php
			endif;
	}

	/**
	 * Outputs the tail for each of the field types.
	 *
	 * @since unknown.
	 *
	 * @return void
	 */
	public function close_output() {

		echo '<div class="clear"></div></li>';
	}

	/**
	 * Set the default meta value of a field.
	 *
	 * @since 1.0.0
	 * @since 3.24.0 Unknown.
	 *
	 * @param int    $post_id  WP Post ID.
	 * @param string $field_id ID/name of the field.
	 * @return mixed
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
