<?php
/**
 * Course information block.
 *
 * @package  LifterLMS_Blocks/Abstracts
 * @since    1.0.0
 * @version  1.1.0
 *
 * @render_hook llms_course-information-block_render
 */

defined( 'ABSPATH' ) || exit;

/**
 * Course information block class.
 */
class LLMS_Blocks_Course_Information_Block extends LLMS_Blocks_Abstract_Block {

	/**
	 * Block ID.
	 *
	 * @var string
	 */
	protected $id = 'course-information';

	/**
	 * Is block dynamic (rendered in PHP).
	 *
	 * @var bool
	 */
	protected $is_dynamic = true;

	/**
	 * Add actions attached to the render function action.
	 *
	 * @param   array  $attributes Optional. Block attributes. Default empty array.
	 * @param   string $content    Optional. Block content. Default empty string.
	 * @return  void
	 * @since   1.0.0
	 * @version 1.1.0
	 */
	public function add_hooks( $attributes = array(), $content = '' ) {

		$attributes = wp_parse_args(
			$attributes,
			array(
				'title'           => __( 'Course Information', 'lifterlms' ),
				'title_size'      => 'h3',
				'show_length'     => true,
				'show_difficulty' => true,
				'show_tracks'     => true,
				'show_cats'       => true,
				'show_tags'       => true,
			)
		);

		$show_wrappers = false;

		if ( $attributes['show_length'] ) {
			$show_wrappers = true;
			add_action( $this->get_render_hook(), 'lifterlms_template_single_length', 10 );
		}

		if ( $attributes['show_difficulty'] ) {
			$show_wrappers = true;
			add_action( $this->get_render_hook(), 'lifterlms_template_single_difficulty', 20 );
		}

		if ( $attributes['show_tracks'] ) {
			$show_wrappers = true;
			add_action( $this->get_render_hook(), 'lifterlms_template_single_course_tracks', 25 );
		}

		if ( $attributes['show_cats'] ) {
			$show_wrappers = true;
			add_action( $this->get_render_hook(), 'lifterlms_template_single_course_categories', 30 );
		}

		if ( $attributes['show_tags'] ) {
			$show_wrappers = true;
			add_action( $this->get_render_hook(), 'lifterlms_template_single_course_tags', 35 );
		}

		if ( $show_wrappers ) {

			$this->title      = $attributes['title'];
			$this->title_size = $attributes['title_size'];

			add_filter( 'llms_course_meta_info_title', array( $this, 'filter_title' ) );
			add_filter( 'llms_course_meta_info_title_size', array( $this, 'filter_title_size' ) );

			add_action( $this->get_render_hook(), 'lifterlms_template_single_meta_wrapper_start', 5 );
			add_action( $this->get_render_hook(), 'lifterlms_template_single_meta_wrapper_end', 50 );

		}

	}

	/**
	 * Filters the title of the course information headline per block settings.
	 *
	 * @param   string $title default title.
	 * @return  string
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function filter_title( $title ) {
		return $this->title;
	}

	/**
	 * Filters the title headline element size of the course information headline per block settings.
	 *
	 * @param   string $size default size.
	 * @return  string
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function filter_title_size( $size ) {
		return $this->title_size;
	}

	/**
	 * Register meta attributes stub.
	 *
	 * Called after registering the block type.
	 *
	 * @return  void
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function register_meta() {

		register_meta(
			'post',
			'_llms_length',
			array(
				'object_subtype'    => 'course',
				'sanitize_callback' => 'sanitize_text_field',
				'auth_callback'     => array( $this, 'meta_auth_callback' ),
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
			)
		);

	}

	/**
	 * Meta field update authorization callback.
	 *
	 * @param   bool   $allowed   Is the update allowed.
	 * @param   string $meta_key  Meta keyname.
	 * @param   int    $object_id WP Object ID (post,comment,etc)...
	 * @param   int    $user_id   WP User ID.
	 * @param   string $cap       requested capability.
	 * @param   array  $caps      user capabilities.
	 * @return  bool
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function meta_auth_callback( $allowed, $meta_key, $object_id, $user_id, $cap, $caps ) {
		return true;
	}

}

return new LLMS_Blocks_Course_Information_Block();
