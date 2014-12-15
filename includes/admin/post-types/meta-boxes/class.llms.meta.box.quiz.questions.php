<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* Quiz Questions
*
* Allows users to assign questions to a quiz.
*/
class LLMS_Meta_Box_Quiz_Questions {

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

		// $question_type = get_post_meta( $post->ID, '_llms_question_type', true );
		 $questions_selected = get_post_meta( $post->ID, '_llms_questions', true );
		// 
		$question_args = array(
			'posts_per_page'   => 1000,
			'post_status'      => 'publish',
			'orderby'          => 'title',
			'order'            => 'ASC',
			'post_type'        => 'llms_question',
			'suppress_filters' => true 
		); 
		$questions = get_posts($question_args);
		?>

		<div id="llms-question-container">
			<?php
			$label  = '';
			$label .= '<h3>' . __( 'Question Options', 'lifterlms' ) . '</h3> ';
			echo $label;
			?>

			<a href="#" class="button" id="add_new_question"/><?php _e('Add a new question', 'lifterlms'); ?></a>
			<div id="llms-single-options">
				<table class="wp-list-table widefat fixed posts question-list ui-sortable">
					
					<thead>
						<tr>
							<th class="llms-table-select">Name</th>
							<th class="llms-table-points">Points</th>
							<th class="llms-table-options"></th>
						</tr>
					</thead>
					<tbody>
						
						<?php
						if ($questions_selected) {
							foreach ($questions_selected as $key => $value) { ?>
								<tr class="list_item" id="question_<?php echo $key; ?>" data-order="<?php echo $key; ?>" style="display: table-row;">
									<td class="llms-table-select">
										<select id="question_select_<?php echo $key; ?>" name="_llms_question[]" class="chosen-select question-select">
											<?php
											if ($questions) {
												foreach ($questions as $pkey => $pvalue) { 
										
													$selected = ($pvalue->ID == $value['id'] ? 'selected' : ''); ?>
													<option <?php echo $selected ?> value="<?php echo $pvalue->ID; ?>"><?php echo $pvalue->post_title; ?></option>
												<?php }
											} ?>
										</select>
									</td>
									<td class="llms-table-points">
										<input type="text" class="llms-points" name="_llms_points[]" id="llms_points_<?php echo $key; ?>" value="<?php echo $value['points']; ?>"/>
									</td>
									<td class="llms-table-options">
									<a href="<?php echo get_edit_post_link($value['id']); ?>"><i class="fa fa-pencil-square-o llms-fa-edit"></i></a>
										<i class="fa fa-bars llms-fa-move"></i>
										<i data-code="f153" class="dashicons dashicons-dismiss deleteBtn single-option-delete"></i> 
									</td>
								</tr>
		
							<?php
							}
						}
						?>
						</tbody>
							<tfoot>
								<tr>
								<td>
									<?php _e('Total Points: ', 'lifterlms') ?> <span id="llms_points_total"></span>
								</td>
							</tr>
						</tfoot>
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
		LLMS_log($_POST);
		$questions = array();

		if ( isset($_POST['_llms_question']) ) {
			foreach ($_POST['_llms_question'] as $key => $value) {
				$question_id = llms_clean($value);
				$question_data = array();

				if ( !empty($question_id) ) {
					$question_data['id'] = $question_id;
					$question_data['points'] = ($_POST['_llms_points'][$key] == '' ? 0 : $_POST['_llms_points'][$key]);

					$questions[$key] = $question_data;
				}

				if($questions) {
					update_post_meta( $post_id, '_llms_questions', $questions);	
				}
			}
		}

	}

}