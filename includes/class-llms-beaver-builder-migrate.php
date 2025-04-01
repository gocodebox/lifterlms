<?php
/**
 * Handle post migration to the Beaver Builder modules.
 *
 * @package LifterLMS/Classes
 *
 * @since 8.0.0
 */

defined( 'ABSPATH' ) || exit;

class LLMS_Beaver_Builder_Migrate {

	public function __construct() {

		add_action( 'wp', array( $this, 'maybe_migrate_post' ) );
		add_action( 'wp', array( $this, 'remove_template_hooks' ) );
		add_action( 'fl_builder_after_save_layout', array( $this, 'maybe_update_migration_status' ), 10, 2 );
	}

	/**
	 * Migrate posts created prior to the elementor updates to have default LifterLMS widgets.
	 *
	 * @since 8.0.0
	 *
	 * @return  void
	 */
	public function maybe_migrate_post() {
		global $post;

		if ( ! class_exists( 'FLBuilderModel' ) || ! method_exists( 'FLBuilderModel', 'is_builder_active' ) || ! FLBuilderModel::is_builder_active() ) {
			return;
		}

		if ( ! $post ) {
			return;
		}

		if ( ! $this->is_migratable_post_type( $post->ID ) ) {
			return;
		}

		if ( ! $this->should_migrate_post( $post->ID ) ) {
			return;
		}

		$this->add_template_to_post();
	}

	protected function is_migratable_post_type( $post_id ) {
		return in_array( get_post_type( $post_id ), array( 'course', 'lesson', 'llms_membership' ) );
	}

	public function add_template_to_post() {
		// Get the existing layout data.
		$data = FLBuilderModel::get_layout_data();

		if ( ! $data ) {
			return;
		}

		$path = LLMS_PLUGIN_DIR . 'includes/beaver-builder/templates/default-' . get_post_type() . '-template.dat';

		if ( ! file_exists( $path ) ) {
			return;
		}

		$templates = maybe_unserialize( file_get_contents( $path ) );
		$template  = $templates['layout'][0];

		// TODO : Check if the data already has the template inserted.

		// Get the next top-level position.
		$position = FLBuilderModel::next_node_position( 'row' );

		// Adjust the position of template nodes.
		foreach ( $template->nodes as $node_id => $node ) {
			if ( ! $node->parent ) {
				$template->nodes[ $node_id ]->position += $position;
			}
		}
		// Merge the template nodes with the existing nodes.
		$data = array_merge( $data, $template->nodes );

		FLBuilderModel::update_layout_data( $data );
	}

	/**
	 * Removes core template action hooks from posts which have been migrated to beaver builder widgets.
	 *
	 * @since 8.0.0
	 *
	 * @return void
	 */
	public function remove_template_hooks() {
		if ( ! function_exists( 'llms_is_beaver_builder_post' ) ||
			! llms_is_beaver_builder_post() ||
			( get_the_ID() && ! llms_parse_bool( get_post_meta( get_the_ID(), '_llms_beaver_builder_migrated', true ) ) ) ) {

			if ( ! $this->is_migratable_post_type( get_the_ID() ) ) {
				return;
			}

			// Remove the bottom actions if the builder is currently active to avoid confusion with uneditable pieces.
			if ( ! class_exists( 'FLBuilderModel' ) || ! method_exists( 'FLBuilderModel', 'is_builder_active' ) || ! FLBuilderModel::is_builder_active() ) {
				return;
			}
		}

		switch ( get_post_type() ) {
			case 'course':
				$this->remove_course_template_hooks();
				break;
			case 'lesson':
				$this->remove_lesson_template_hooks();
				break;
			case 'llms_membership':
				$this->remove_membership_template_hooks();
				break;
		}
	}

	/**
	 * Remove membership template hooks.
	 *
	 * @since 8.0.0
	 */
	public function remove_membership_template_hooks() {
		remove_action( 'lifterlms_single_membership_after_summary', 'lifterlms_template_pricing_table', 10 );
	}

	/**
	 * Remove lesson template hooks.
	 *
	 * @since 8.0.0
	 */
	public function remove_lesson_template_hooks() {
		remove_action( 'lifterlms_single_lesson_after_summary', 'lifterlms_template_complete_lesson_link', 10 );
	}

	/**
	 * Remove course template hooks.
	 *
	 * @since 8.0.0
	 *
	 * @return void
	 */
	public function remove_course_template_hooks() {
		// TODO: Refactor this so it's not duplicated between Elementor and Beaver Builder.
		remove_action( 'lifterlms_single_course_after_summary', 'lifterlms_template_single_meta_wrapper_start', 5 );
		remove_action( 'lifterlms_single_course_after_summary', 'lifterlms_template_single_length', 10 );
		remove_action( 'lifterlms_single_course_after_summary', 'lifterlms_template_single_difficulty', 20 );
		remove_action( 'lifterlms_single_course_after_summary', 'lifterlms_template_single_course_tracks', 25 );
		remove_action( 'lifterlms_single_course_after_summary', 'lifterlms_template_single_course_categories', 30 );
		remove_action( 'lifterlms_single_course_after_summary', 'lifterlms_template_single_course_tags', 35 );
		remove_action( 'lifterlms_single_course_after_summary', 'lifterlms_template_single_meta_wrapper_end', 50 );
		remove_action( 'lifterlms_single_course_after_summary', 'lifterlms_template_single_course_progress', 60 );
		remove_action( 'lifterlms_single_course_after_summary', 'lifterlms_template_single_syllabus', 90 );
		remove_action( 'lifterlms_single_course_after_summary', 'lifterlms_template_course_author', 40 );
		remove_action( 'lifterlms_single_course_after_summary', 'lifterlms_template_pricing_table', 60 );
	}

	/**
	 * Determine if a post should be migrated.
	 *
	 * @since 8.0.0
	 *
	 * @param int $post_id WP_Post ID.
	 * @return bool
	 */
	public function should_migrate_post( $post_id ) {

		$ret = ! llms_parse_bool( get_post_meta( $post_id, '_llms_beaver_builder_migrated', true ) );

		/**
		 * Filters whether or not a post should be migrated
		 *
		 * @since 8.0.0
		 *
		 * @param bool $migrate Whether or not a post should be migrated.
		 * @param int  $post_id WP_Post ID.
		 */
		return apply_filters( 'llms_beaver_builder_should_migrate_post', $ret, $post_id );
	}

	/**
	 * Update post meta data to signal status of the editor migration.
	 *
	 * @since 8.0.0
	 *
	 * @param int  $post_id WP_Post ID.
	 * @param bool $publish Whether or not the post is being published.
	 * @return void
	 */
	public function maybe_update_migration_status( $post_id, $publish ) {
		if ( ! $publish ) {
			return;
		}

		if ( ! $this->is_migratable_post_type( $post_id ) ) {
			return;
		}

		if ( llms_parse_bool( get_post_meta( $post_id, '_llms_beaver_builder_migrated', true ) ) ) {
			return;
		}

		update_post_meta( $post_id, '_llms_beaver_builder_migrated', 'yes' );
	}
}

return new LLMS_Beaver_Builder_Migrate();
