<?php
/**
 * LLMS_Blocks_Reusable class file
 *
 * @package LifterLMS_Blocks/Classes
 *
 * @since 2.0.0
 * @version 2.3.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * Manage customizations to reusable blocks
 *
 * @since 2.0.0
 */
class LLMS_Blocks_Reusable {

	/**
	 * Constructor
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function __construct() {

		add_action( 'rest_api_init', array( $this, 'rest_register_fields' ) );
		add_filter( 'rest_wp_block_query', array( $this, 'mod_wp_block_query' ), 20, 2 );

	}

	/**
	 * Read rest field read callback
	 *
	 * @since 2.0.0
	 *
	 * @param array           $obj     Associative array representing the `wp_block` post.
	 * @param WP_REST_Request $request Request object.
	 * @return WP_Error|array Error when current user isn't authorized to read the data or the post association array on success.
	 */
	public function rest_callback_get( $obj, $request ) {
		return llms_parse_bool( get_post_meta( $obj['id'], '_is_llms_field', true ) ) ? 'yes' : 'no';
	}

	/**
	 * Rest field update callback
	 *
	 * @since 2.0.0
	 *
	 * @param array   $value Post association array.
	 * @param WP_Post $obj   Post object for the `wp_block` post.
	 * @param string  $key   Field key.
	 * @return WP_Error|boolean Returns an error object when current user lacks permission to update the form or `true` on success.
	 */
	public function rest_callback_update( $value, $obj, $key ) {
		$value = llms_parse_bool( $value ) ? 'yes' : 'no';
		return update_post_meta( $obj->ID, '_is_llms_field', $value ) ? true : false;
	}

	/**
	 * Register custom rest fields
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function rest_register_fields() {

		register_rest_field(
			'wp_block',
			'is_llms_field',
			array(
				'get_callback'    => array( $this, 'rest_callback_get' ),
				'update_callback' => array( $this, 'rest_callback_update' ),
			)
		);

	}

	/**
	 * Modify the rest request query used to list reusable blocks within the block editor
	 *
	 * Ensures that reusable blocks containing LifterLMS Form Fields can only be inserted/viewed
	 * in the context that we allow them to be used within.
	 *
	 * + When viewing a `wp_block` post, all reusable blocks should be displayed.
	 * + When viewing an `llms_form` post, only blocks that specify `is_llms_field` as 'yes' can be displayed.
	 * + When viewing any other post, any post with `is_llms_field` of 'yes' is excluded.
	 *
	 * @since 2.0.0
	 *
	 * @see [Reference]
	 * @link [URL]
	 *
	 * @param arrays          $args    WP_Query arguments.
	 * @param WP_REST_Request $request Request object.
	 * @return array
	 */
	public function mod_wp_block_query( $args, $request ) {

		$referer = $request->get_header( 'referer' );
		$screen  = empty( $referer ) ? false : $this->get_screen_from_referer( $referer );

		// We don't care about the screen or it's a reusable block screen.
		if ( empty( $screen ) || 'wp_block' === $screen ) {
			return $args;
		}

		// Add a meta query if it doesn't already exist.
		if ( empty( $args['meta_query'] ) ) {
			$args['meta_query'] = array(
				'relation' => 'AND',
			);
		}

		// Forms should show only blocks with forms and everything else should exclude blocks with forms.
		$include_fields       = 'llms_form' === $screen;
		$args['meta_query'][] = $this->get_meta_query( $include_fields );

		return $args;

	}

	/**
	 * Retrieve a meta query array depending on the post type of the referring rest request
	 *
	 * @since 2.0.0
	 *
	 * @param boolean $include_fields Whether or not to include form fields.
	 * @return array
	 */
	private function get_meta_query( $include_fields ) {

		// Default 	query when including fields.
		$meta_query = array(
			'key'   => '_is_llms_field',
			'value' => 'yes',
		);

		// Excluding fields.
		if ( ! $include_fields ) {

			$meta_query = array(
				'relation' => 'OR',
				wp_parse_args(
					array(
						'compare' => '!=',
					),
					$meta_query
				),
				array(
					'key'     => '_is_llms_field',
					'compare' => 'NOT EXISTS',
				),
			);
		}

		return $meta_query;

	}

	/**
	 * Determine the screen where a reusable blocks rest query originated
	 *
	 * The screen name will either be "widgets" or the WP_Post name of a registered WP_Post type.
	 *
	 * For any other screen we return `false` because we don't care about it.
	 *
	 * @since 2.0.0
	 * @since 2.3.1 Don't pass `null` to `basename()`.
	 *
	 * @param string $referer Referring URL for the REST request.
	 * @return string|boolean Returns the screen name or `false` if we don't care about the screen.
	 */
	private function get_screen_from_referer( $referer ) {

		// Blockified widgets screen.
		$url_path = wp_parse_url( $referer, PHP_URL_PATH );
		if ( $url_path && 'widgets.php' === basename( $url_path ) ) {
			return 'widgets';
		}

		$query_args = array();
		wp_parse_str( wp_parse_url( $referer, PHP_URL_QUERY ), $query_args );

		// Something else.
		if ( empty( $query_args['post'] ) ) {
			return false;
		}

		// Block editor for a WP_Post.
		return get_post_type( $query_args['post'] );

	}

}

return new LLMS_Blocks_Reusable();
