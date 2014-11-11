<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* Quiz General Settings
*
* Quesion Builder Metabox
*
* @version 1.0
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Meta_Box_Question_General {

	/**
	 * Set up metabox
	 *
	 * @return string
	 * @param string $post
	 */
	public static function output( $post ) {
		global $post;
		LLMS_log('output works');
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

					<a href="#" class="button" id="add_new_option"/><?php _e('Add a new option', 'lifterlms'); ?></a>
					<div id="llms-single-options">
						<table class="wp-list-table widefat fixed posts dad-list ui-sortable">
							<tbody>
								
									<?php
									if ($question_options) {
										foreach ($question_options as $key => $value) {
											LLMS_log($question_options);
											LLMS_log($value['correct_option']);
											?>
											<tr class="list_item" data-order="<?php echo $key ?>" style="display: table-row;">
											<td>
											<i class="fa fa-bars llms-fa-move-lesson"></i>
											<i data-code="f153" class="dashicons dashicons-dismiss deleteBtn single-option-delete"></i>
											<input type="radio" name="correct_option" value="<?php echo $key ?>" <?php echo (empty($value['correct_option'] == '1')?'':'checked'); ?> ><label><?php _e('Correct Answer', 'lifterlms') ?></label>
											<textarea name ="option_text[]" class="option-text"><?php echo $value['option_text']; ?></textarea>
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

	public static function save( $post_id, $post ) {
		global $wpdb;
		LLMS_log('it is saving?');


		LLMS_log($_POST);

		$question_options = array();

		//add options to array
		if ( isset($_POST['option_text']) || isset($_POST['correct_option'])) {
		foreach ($_POST['option_text'] as $key => $value) {
			$option_data = array();
			$correct_option = false;

			$option_text = llms_clean( $value );

			if($_POST['correct_option'] == $key) {
				$correct_option = true;
			}
			$option_data['option_text'] = $option_text;
			$option_data['correct_option'] = $correct_option;
			$question_options[$key] = $option_data;
		}
		LLMS_log($question_options);

		update_post_meta( $post_id, '_llms_question_type', 'single_choice');	
		//update_post_meta( $post_id, '_llms_question_options', $question_options);
			update_post_meta( $post_id, '_llms_question_options', ( $question_options === '' ) ? '' : $question_options );	
		}
		//LLMS_log($question_options);
		//if ( isset( $_POST['_llms_allowed_attempts'] ) ) {
			//$allowed_attempts = ( llms_clean( $_POST['_llms_allowed_attempts']  ) );
			//update_post_meta( $post_id, '_llms_question_type', 'single_choice');		
		//}
		// if ( isset( $_POST['_llms_passing_percent'] ) ) {
		// 	$passing_percent = ( llms_clean( $_POST['_llms_passing_percent']  ) );
		// 	update_post_meta( $post_id, '_llms_passing_percent', ( $passing_percent === '' ) ? '0' : $passing_percent );		
		// }
	}

}