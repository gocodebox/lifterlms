<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
*
*/
class LLMS_Metabox_Button_Field extends LLMS_Metabox_Field implements Meta_Box_Field_Interface
{
	/**
	 * Class constructor
	 * @param array $_field Array containing information about field
	 */
	function __construct( $_field ) {

		$this->field = $_field;
	}

	/**
	 * Outputs the Html for the given field
	 * @return HTML
	 */
	public function Output() {

		global $post;

		parent::Output(); ?>
					
		<button 
 			id="<?php echo esc_attr( $this->field['id'] ); ?>" 
 			class="<?php echo esc_attr( $this->field['class'] ); ?>"
 		>
 			<?php echo esc_attr( $this->field['value'] ); ?>
 		</button>			
		<?php
		parent::CloseOutput();
	}
}

