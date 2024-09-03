<?php
/**
 * LifterLMS Eearned Engagements (Certificate/Achievement) Reporting Table trait.
 *
 * @package LifterLMS/Traits
 *
 * @since 6.0.0
 * @version 6.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LifterLMS Eearned Engagements (Certificate/Achievement) Reporting Table trait.
 *
 * This trait should only be used by classes that extend from the {@see LLMS_Admin_Table} class.
 *
 * @since 6.0.0
 */
trait LLMS_Trait_Earned_Engagement_Reporting_Table {

	/**
	 * Add award engagement button above the table.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function output_table_html() {

		$post_type = null;
		if ( 'certificates' === $this->id ) {
			$post_type = 'llms_my_certificate';
		} elseif ( 'achievements' === $this->id ) {
			$post_type = 'llms_my_achievement';
		}
		if ( empty( $post_type ) ) {
			parent::output_table_html();

			return;
		}

		$post_type_object = get_post_type_object( $post_type );

		if ( ! current_user_can( $post_type_object->cap->edit_post ) ) {
			parent::output_table_html();

			return;
		}

		$student = false;
		if ( ! empty( $this->student ) ) {
			$student = $this->student->get_id();
		} elseif ( ! empty( $_GET['student_id'] ) ) { //phpcs:ignore -- Nonce verification not needed.
			$student = llms_filter_input( INPUT_GET, 'student_id', FILTER_SANITIZE_NUMBER_INT );
		}

		$post_new_file = "post-new.php?post_type=$post_type";
		?>
		<a id="llms-new-award-button" style="display:inline-block;margin-bottom:20px" href="<?php echo esc_url( add_query_arg( 'sid', $student, admin_url( $post_new_file ) ) ); ?>" class="llms-button-secondary small"><?php echo esc_html( $post_type_object->labels->add_new ); ?></a>
		<?php
		parent::output_table_html();
	}
}
