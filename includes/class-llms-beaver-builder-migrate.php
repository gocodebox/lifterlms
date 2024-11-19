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

		add_action( 'current_screen', array( $this, 'migrate_post' ) );
		add_action( 'wp', array( $this, 'remove_template_hooks' ) );
	}

	public function get_beaver_builder_data_template() {
		$content = array();

		return $content;
	}

	/**
	 * Migrate posts created prior to the elementor updates to have default LifterLMS widgets.
	 *
	 * @since 7.7.0
	 *
	 * @return  void
	 */
	public function migrate_post() {

		global $pagenow;

		if ( 'post.php' !== $pagenow ) {
			return;
		}

		$post_id = llms_filter_input( INPUT_GET, 'post', FILTER_SANITIZE_NUMBER_INT );
		$post    = $post_id ? get_post( $post_id ) : false;

		if ( ! $post || ! isset( $_REQUEST['action'] ) || 'elementor' !== $_REQUEST['action'] || ! $this->should_migrate_post( $post_id ) || 'course' !== get_post_type( $post_id ) ) {
			return;
		}

		$this->ensure_elementor_data_present( $post_id );
		$this->add_template_to_post( $post_id );
	}

	public function add_template_to_post( $post_id ) {
		$content = get_post_meta( $post_id, '_elementor_data', true );
		if ( ! $content ) {
			return;
		}

		$decoded_content = json_decode( $content, true );

		if ( ! is_array( $decoded_content ) ) {
			return;
		}

		$decoded_content = array_merge( $decoded_content, $this->get_elementor_data_template() );

		$this->update_elementor_data( $post_id, $decoded_content );
		$this->update_migration_status( $post_id );
	}

	/**
	 * Removes core template action hooks from posts which have been migrated to elementor widgets.
	 *
	 * @since 7.7.0
	 *
	 * @return void
	 */
	public function remove_template_hooks() {
		// TODO: Refactor this so it's not duplicated between Elementor and Beaver Builder.
		if ( ! function_exists( 'llms_is_beaver_builder_post' ) ||
			! llms_is_beaver_builder_post() ||
			( get_the_ID() && ! llms_parse_bool( get_post_meta( get_the_ID(), '_llms_beaver_builder_migrated', true ) ) ) ) {
			return;
		}

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
	 * @since 7.7.0
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
	 * @since 7.7.0
	 *
	 * @param int    $post_id WP_Post ID.
	 * @param string $status  Yes or no.
	 * @return void
	 */
	public function update_migration_status( $post_id, $status = 'yes' ) {
		update_post_meta( $post_id, '_llms_beaver_builder_migrated', $status );
	}

	private function ensure_elementor_data_present( $post_id ): void {
		$content = json_decode( get_post_meta( $post_id, '_elementor_data', true ) );

		if ( ! is_array( $content ) && ( $post = get_post( $post_id ) ) ) {
			$content   = array();
			$content[] = array(
				'id'       => uniqid(),
				'elType'   => 'container',
				'settings' => array(),
				'elements' => array(
					array(
						'id'         => uniqid(),
						'elType'     => 'widget',
						'settings'   => array(
							'editor' => $post->post_content,
						),
						'elements'   => array(),
						'widgetType' => 'text-editor',
					),
				),
				'isInner'  => false,
			);
			$this->update_elementor_data( $post_id, $content );
		}
	}

	private function update_elementor_data( $post_id, $content ): void {
		// The trim and wp json encode are important. It doesn't seem to work with just json_encode, for example.
		update_post_meta( $post_id, '_elementor_data', trim( wp_slash( wp_json_encode( $content ) ), '"' ) );
	}
}

global $llms_beaver_builder_migrate;
$llms_beaver_builder_migrate = new LLMS_Beaver_Builder_Migrate();
return $llms_beaver_builder_migrate;
