<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
* Meta Box Lesson Options
*
* diplays misc lesson options.
*/
class LLMS_Meta_Box_Lesson_Options {

	/**
	 * Static output class.
	 *
	 * Displays MetaBox
	 *
	 * @param  object $post [WP post object]
	 * @return void
	 */
	public static function output( $post ) {

		$days_before_avalailable = get_post_meta( $post->ID, '_days_before_avalailable', true );
		$assigned_quiz = get_post_meta( $post->ID, '_llms_assigned_quiz', true );
		$require_passing_grade = get_post_meta( $post->ID, '_llms_require_passing_grade', true );

		$quiz_args = array(
			'posts_per_page'   => 1000,
			'post_status'      => 'publish',
			'orderby'          => 'title',
			'order'            => 'ASC',
			'post_type'        => 'llms_quiz',
			'suppress_filters' => true,
		);
		$quizzes = get_posts( $quiz_args );
		?>

		<table class="form-table">
			<tbody>
				<tr>
					<th><label for="'_days_before_avalailable'"><?php _e( 'Drip Content (in days)', 'lifterlms' ); ?></label></th>
					<td>
						<input type="text" name="_days_before_avalailable" id="_days_before_avalailable" value="<?php echo $days_before_avalailable; ?>"/>
						<br /><span class="description"><?php _e( 'Number of days before lesson is available after course begins (date of purchase or set start date)', 'lifterlms' ); ?></span>
					</td>
				</tr>

				<tr>
					<th><label for="'_llms_assigned_quiz'"><?php _e( 'Assigned Quiz', 'lifterlms' ); ?></label></th>
					<td>
						<select id="_llms_assigned_quiz" name="_llms_assigned_quiz" class="chosen-select question-select">
						<option value="" >None</option>
							<?php
							foreach ($quizzes as $key => $value) {
								$selected = ($value->ID == $assigned_quiz ? 'selected' : ''); ?>
								<option <?php echo $selected ?> value="<?php echo $value->ID; ?>"><?php echo $value->post_title; ?></option>
							<?php } ?>
						</select>
						<br /><span class="description"><?php _e( 'Quiz will be required to complete lesson.', 'lifterms' ); ?></span>
					</td>
				</tr>

				<tr>
					<th><label for="'_llms_require_passing_grade'"><?php _e( 'Require Passing Grade', 'lifterlms' ); ?></label></th>
					<td>
						<input type="checkbox" name="_llms_require_passing_grade" <?php if ( $require_passing_grade == true ) { ?>checked="checked"<?php } ?> />
						<br /><span class="description"><?php _e( 'Checking this box will require students to get a passing score on the above quiz to complete the lesson.', 'lifterms' ); ?></span>
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

		$days = ( llms_clean( $_POST['_days_before_avalailable'] ) );
		update_post_meta( $post_id, '_days_before_avalailable', ( $days === '' ) ? '' : $days );

		// Free lesson checkbox
		$free_lesson = ( isset( $_POST['_llms_free_lesson'] ) ? true : false );
		update_post_meta( $post_id, '_llms_free_lesson', ( $free_lesson === '' ) ? '' : $free_lesson );

		if ( isset( $_POST['_llms_assigned_quiz'] ) ) {
			//update assigned quiz select
			$assigned_quiz = ( llms_clean( $_POST['_llms_assigned_quiz'] ) );
			update_post_meta( $post_id, '_llms_assigned_quiz', ( $assigned_quiz === '' ) ? '' : $assigned_quiz );
		}

		//update passing grade checkbox
		$require_passing_grade = ( isset( $_POST['_llms_require_passing_grade'] ) ? true : false );
		update_post_meta( $post_id, '_llms_require_passing_grade', ( $require_passing_grade === '' ) ? '' : $require_passing_grade );
	}

}
