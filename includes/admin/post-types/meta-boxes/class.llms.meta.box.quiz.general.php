<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* Quiz General Settings
*
* Handles settings metabox display and update
*/
class LLMS_Meta_Box_Quiz_General {

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
		$allowed_attempts = get_post_meta( $post->ID, '_llms_allowed_attempts', true );
		$passing_percent = get_post_meta( $post->ID, '_llms_passing_percent', true );
		$time_limit = get_post_meta( $post->ID, '_llms_time_limit', true );
		$show_result = get_post_meta( $post->ID, '_llms_show_results', true );
		$show_correct_answer = get_post_meta( $post->ID, '_llms_show_correct_answer', true );
		$show_option_description_wrong_answer = get_post_meta( $post->ID, '_llms_show_options_description_wrong_answer', true );
		$show_option_description_right_answer = get_post_meta( $post->ID, '_llms_show_options_description_right_answer', true );

		?>

		<table class="form-table">
		<tbody>
			<tr>
				<th>
					<?php
					$label  = '';
					$label .= '<label for="_llms_allowed_attempts">' . __( 'Allowed Attempts', 'lifterlms' ) . '</label> ';
					echo $label;
					?>
				</th>
				<td>
					<?php
					$html  = '';
					$html .= '<input type="text" class="code" name="_llms_allowed_attempts" id="_llms_allowed_attempts" value="' . $allowed_attempts . '"/>';
					$html .= '<br /><span class="description">' .  __( 'Number of allowed attempts. Leave blank for unlimited attempts.', 'lifterlms' ) . '</span>';
					echo $html;
					?>
				</td>
			</tr>

			<tr>
				<th>
					<?php
					$label  = '';
					$label .= '<label for="_llms_passing_percent">' . __( 'Passing Percentage', 'lifterlms' ) . '</label> ';
					echo $label;
					?>
				</th>
				<td>
					<?php
					$html  = '';
					$html .= '<input type="text" class="code" name="_llms_passing_percent" id="_llms_passing_percent" value="' . $passing_percent . '"/>';
					$html .= '<br /><span class="description">' .  __( 'Enter the percent required to pass quiz. DO NOT USE % (IE: enter 50 to have a passing requirement of 50%.', 'lifterlms' ) . '</span>';
					echo $html;
					?>
				</td>
			</tr>

			<tr>
				<th>
					<?php
					$label  = '';
					$label .= '<label for="_llms_time_limit">' . __( 'Time Limit', 'lifterlms' ) . '</label> ';
					echo $label;
					?>
				</th>
				<td>
					<?php
					$html  = '';
					$html .= '<input type="number" min="0" class="code" name="_llms_time_limit" id="_llms_time_limit" value="' . $time_limit . '"/>';
					$html .= '<br /><span class="description">' .  __( 'Enter a time limit for quiz completion in minutes. Leave empty if no time limit.', 'lifterlms' ) . '</span>';
					echo $html;
					?>
				</td>
			</tr>
			<tr>
				<th><label for="_llms_show_results"><?php _e('Display last quiz results', 'lifterlms'); ?></label></th>
				<td>
					<input type="checkbox" name="_llms_show_results" <?php if( $show_result == true ) { ?>checked="checked"<?php } ?> />
					<br /><span class="description"><?php _e('Checking this box will show result summary to students.', 'lifterms'); ?></span>
				</td>
			</tr>
			<tr>
				<th><label for="_llms_show_correct_answer"><?php _e('Show Correct Answers', 'lifterlms'); ?></label></th>
				<td>
					<input type="checkbox" name="_llms_show_correct_answer" <?php if( $show_correct_answer == true ) { ?>checked="checked"<?php } ?> />
					<br /><span class="description"><?php _e('Checking this box will show correct answer on incorrect questions.', 'lifterms'); ?></span>
				</td>
			</tr>
			<tr>
				<th><label for="_llms_show_options_description_wrong_answer"><?php _e('Show description on wrong answers', 'lifterlms'); ?></label></th>
				<td>
					<input type="checkbox" name="_llms_show_options_description_wrong_answer" <?php if( $show_option_description_wrong_answer == true ) { ?>checked="checked"<?php } ?> />
					<br /><span class="description"><?php _e('Checking this box will show  description on incorrect questions', 'lifterms'); ?></span>
				</td>
			</tr>
			<tr>
				<th><label for="_llms_show_options_description_right_answer"><?php _e('Show description on right answers', 'lifterlms'); ?></label></th>
				<td>
					<input type="checkbox" name="_llms_show_options_description_right_answer" <?php if( $show_option_description_right_answer == true ) { ?>checked="checked"<?php } ?> />
					<br /><span class="description"><?php _e('Checking this box will show  description on correct questions', 'lifterms'); ?></span>
				</td>
			</tr>
		</tbody>
		</table>

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

		if ( isset( $_POST['_llms_allowed_attempts'] ) ) {
			$allowed_attempts = ( llms_clean( $_POST['_llms_allowed_attempts']  ) );
			update_post_meta( $post_id, '_llms_allowed_attempts', ( $allowed_attempts === '' ) ? '' : $allowed_attempts );
		}
		if ( isset( $_POST['_llms_passing_percent'] ) ) {
			$passing_percent = ( llms_clean( $_POST['_llms_passing_percent']  ) );
			update_post_meta( $post_id, '_llms_passing_percent', ( $passing_percent === '' ) ? '0' : $passing_percent );
		}
		if ( isset( $_POST['_llms_time_limit'] ) ) {
			$time_limit = ( llms_clean( $_POST['_llms_time_limit']  ) );
			update_post_meta( $post_id, '_llms_time_limit', $time_limit );
		}

		$random_answers = ( isset( $_POST['_llms_random_answers'] ) ? true : false );
		update_post_meta( $post_id, '_llms_random_answers', ( $random_answers === '' ) ? '' : $random_answers );
		
		$show_result = ( isset( $_POST['_llms_show_results'] ) ? true : false );
		update_post_meta( $post_id, '_llms_show_results', ( $show_result === '' ) ? '' : $show_result );

		$show_correct_answer = ( isset( $_POST['_llms_show_correct_answer'] ) ? true : false );
		update_post_meta( $post_id, '_llms_show_correct_answer', ( $show_correct_answer === '' )
			? '' : $show_correct_answer );

		$show_option_description_wrong_answer = ( isset( $_POST['_llms_show_options_description_wrong_answer'] )
			? true : false );
		update_post_meta($post_id, '_llms_show_options_description_wrong_answer',
			( $show_option_description_wrong_answer === '') ? '' : $show_option_description_wrong_answer);

		$show_option_description_right_answer = ( isset( $_POST['_llms_show_options_description_right_answer'] )
			? true : false );
		update_post_meta($post_id, '_llms_show_options_description_right_answer',
			( $show_option_description_right_answer === '') ? '' : $show_option_description_right_answer);
	}
}
