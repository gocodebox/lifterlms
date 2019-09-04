<?php
/**
 * Metabox Field: Search
 *
 * @since Unknown
 * @version Unknown
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Metabox_Search_Field
 *
 * @since Unknown
 */
class LLMS_Metabox_Search_Field extends LLMS_Metabox_Field implements Meta_Box_Field_Interface {

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

		parent::output(); ?>

		<select
			id="<?php echo esc_attr( $this->field['id'] ); ?>"
			name="<?php echo esc_attr( $this->field['id'] ); ?>"
			class="<?php echo esc_attr( $this->field['class'] ); ?>"
		>
			<!--<option value="">None</option>-->

			<?php
			foreach ( $this->field['value'] as $option ) :
				if ( $option['key'] == $this->meta ) :
					?>
				<!--<option value="<?php echo $option['key']; ?>" selected="selected"><?php echo $option['title']; ?></option>-->

			<?php else : ?>
				<!--<option value="<?php echo $option['key']; ?>"><?php echo $option['title']; ?></option>-->

			<?php endif; ?>
			<?php endforeach; ?>
			</select>
		<?php
		parent::close_output();
	}
}

