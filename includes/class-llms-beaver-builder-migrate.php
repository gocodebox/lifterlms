<?php
/**
 * Handle post migration to the Beaver Builder modules.
 *
 * @package LifterLMS/Classes
 *
 * @since [version]
 */

defined( 'ABSPATH' ) || exit;

class LLMS_Beaver_Builder_Migrate {

	public function __construct() {

		add_action( 'wp', array( $this, 'maybe_migrate_post' ) );
		add_action( 'wp', array( $this, 'remove_template_hooks' ) );
	}

	/**
	 * Migrate posts created prior to the elementor updates to have default LifterLMS widgets.
	 *
	 * @since [version]
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

		// TODO: Also migrate lessons?
		if ( 'course' !== get_post_type( $post->ID ) ) {
			return;
		}

		if ( ! $this->should_migrate_post( $post->ID ) ) {
			return;
		}

		$this->add_template_to_post();
	}

	public function add_template_to_post() {
		// Get the existing layout data.
		$data = FLBuilderModel::get_layout_data();

		if ( ! $data ) {
			$draft_data = FLBuilderModel::get_layout_data( 'draft' );

			// We don't want to update if there's already a draft.
			if ( ! empty( $draft_data ) ) {
				return;
			}
		}

		$path      = LLMS_PLUGIN_DIR . 'includes/beaver-builder/templates/default-course-template.dat';
		$templates = maybe_unserialize( file_get_contents( $path ) );
		$template  = $templates['layout'][0];

		if ( ! $data ) {
			FLBuilderModel::update_layout_data( $template->nodes, 'draft' );

			// TODO: Check if we want to do this now?
			$this->update_migration_status( get_the_ID() );

			return;
		}

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

		$this->update_migration_status( get_the_ID() );
	}

	/**
	 * Removes core template action hooks from posts which have been migrated to beaver builder widgets.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function remove_template_hooks() {
		if ( ! function_exists( 'llms_is_beaver_builder_post' ) ||
			! llms_is_beaver_builder_post() ||
			( get_the_ID() && ! llms_parse_bool( get_post_meta( get_the_ID(), '_llms_beaver_builder_migrated', true ) ) ) ) {
			return;
		}

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
	 * @since [version]
	 *
	 * @param int $post_id WP_Post ID.
	 * @return bool
	 */
	public function should_migrate_post( $post_id ) {

		$ret = ! llms_parse_bool( get_post_meta( $post_id, '_llms_beaver_builder_migrated', true ) );

		/**
		 * Filters whether or not a post should be migrated
		 *
		 * @since [version]
		 *
		 * @param bool $migrate Whether or not a post should be migrated.
		 * @param int  $post_id WP_Post ID.
		 */
		return apply_filters( 'llms_beaver_builder_should_migrate_post', $ret, $post_id );
	}

	/**
	 * Update post meta data to signal status of the editor migration.
	 *
	 * @since [version]
	 *
	 * @param int    $post_id WP_Post ID.
	 * @param string $status  Yes or no.
	 * @return void
	 */
	public function update_migration_status( $post_id, $status = 'yes' ) {
		update_post_meta( $post_id, '_llms_beaver_builder_migrated', $status );
	}
}

global $llms_beaver_builder_migrate;
$llms_beaver_builder_migrate = new LLMS_Beaver_Builder_Migrate();
return $llms_beaver_builder_migrate;
