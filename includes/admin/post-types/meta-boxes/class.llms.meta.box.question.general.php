<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
* Quiz General Settings
*
* Quesion Builder Metabox
*/
class LLMS_Meta_Box_Question_General {

	/**
	 * Static output class.
	 *
	 * Displays MetaBox
	 * Calls static class metabox_options
	 * Loops through meta-options array and displays appropriate fields based on type.
	 *
	 * @param  object $post [WP post object]
	 *
	 * @return void
	 */
	public static function output( $post ) {
		global $post;

		wp_nonce_field( 'lifterlms_save_data', 'lifterlms_meta_nonce' );

		$question_type = get_post_meta( $post->ID, '_llms_question_type', true );
		$question_options = get_post_meta( $post->ID, '_llms_question_options', true );

		?>

		<div id="llms-question-container">
			<?php
			$label  = '';
			$label .= '<h3>' . __( 'Question Options', 'lifterlms' ) . '</h3> ';
			echo $label;
			?>

			<a href="#" class="button" id="add_new_option"/><?php _e( 'Add a new option', 'lifterlms' ); ?></a>
			<div id="llms-single-options">
				<table class="wp-list-table widefat fixed posts dad-list ui-sortable">
					<tbody>

							<?php
							if ( $question_options ) {
								foreach ( $question_options as $key => $value ) {

									?>
									<tr class="list_item" data-order="<?php echo $key ?>" style="display: table-row;">
									<td>
									<i class="fa fa-bars llms-fa-move-lesson"></i>
									<i data-code="f153" class="dashicons dashicons-dismiss deleteBtn single-option-delete"></i>
									<input type="radio" name="correct_option" value="<?php echo $key; ?>" <?php echo (empty( $value['correct_option'] ) == '1')? '':'checked'; ?> ><label><?php _e( 'Correct Answer', 'lifterlms' ); ?></label>
									<textarea name ="option_text[]" class="option-text"><?php echo esc_textarea( $value['option_text'] ); ?></textarea>
									<br>
									<label><?php _e( 'Explanation Field', 'lifterlms' ); ?></label>
									<textarea name ="option_description[]" class="option-text"><?php echo array_key_exists( 'option_description', $value ) ? esc_textarea( $value['option_description'] ) : ''; ?></textarea>
									</td>
									</tr>
								<?php
								}
							}
							?>


					</tbody>
				</table>
			</div>
		</div>

	<?php
	}

	/**
	 * Static save method
	 *
	 * cleans variables and saves using update_post_meta
	 *
	 * @param  int 		$post_id [id of post object]
	 * @param  object 	$post [WP post object]
	 *
	 * @return void
	 */
	public static function save( $post_id, $post ) {
		global $wpdb;

		$question_options = array();

		//add options to array
		if ( isset( $_POST['option_text'] ) || isset( $_POST['correct_option'] ) ) {
			foreach ( $_POST['option_text'] as $key => $value ) {
				$option_data = array();
				$correct_option = false;

				if ( $_POST['correct_option'] == $key ) {
					$correct_option = true;
				}
				$option_data['option_text'] = $value;
				$option_data['correct_option'] = $correct_option;
				$option_data['option_description'] = $_POST['option_description'][ $key ];
				$question_options[ $key ] = $option_data;
			}

			update_post_meta( $post_id, '_llms_question_type', 'single_choice' );
			//update_post_meta( $post_id, '_llms_question_options', $question_options);
			update_post_meta( $post_id, '_llms_question_options', ( $question_options === '' ) ? '' : $question_options );
		}

	}

}
