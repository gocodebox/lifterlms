<?php
/**
 * Generate LMS Content from export files or raw arrays of data.
 *
 * @package LifterLMS/Abstracts/Classes
 *
 * @since 4.7.0
 * @version 7.3.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Abstract_Generator_Posts class.
 *
 * Many methods in this class were moved from `LLMS_Generator`. The move has been
 * noted on these methods and their preexisting changelogs have been preserved.
 *
 * @since 4.7.0
 * @since 6.0.0 Removed the deprecated `LLMS_Abstract_Generator_Posts::increment()` method.
 */
abstract class LLMS_Abstract_Generator_Posts {

	/**
	 * Exception code: WP_Post creation error
	 *
	 * @var int
	 */
	const ERROR_CREATE_POST = 1000;

	/**
	 * Exception code: WP_Term creation error
	 *
	 * @var int
	 */
	const ERROR_CREATE_TERM = 1001;

	/**
	 * Exception code: WP_User creation error
	 *
	 * @var int
	 */
	const ERROR_CREATE_USER = 1002;

	/**
	 * Exception code: Requested LLMS_Post_Model subclass does not exist.
	 *
	 * @var int
	 */
	const ERROR_INVALID_POST = 1100;

	/**
	 * Default post status when status isn't set in $raw for a given post
	 *
	 * @var string
	 */
	private $default_post_status = 'draft';

	/**
	 * Array of images that have been sideloaded during generation
	 *
	 * Each array key will be the original source URL and the array value will be the new
	 * attachment post ID of the image that has been sideloaded into the current site.
	 *
	 * This array is checked prior to sideloading an image to ensure that if the same image is
	 * used multiple times throughout an import, the image is only sideloaded a single time.
	 *
	 * @var array
	 */
	protected $images = array();

	/**
	 * Array of reusable blocks that have been imported during generation
	 *
	 * Each array key will be the original block ID and the array value will be the new
	 * block ID.
	 *
	 * This array is checked prior to importing a reusable block to ensure that if the same
	 * block is used multiple times throughout an import, it will only be imported once.
	 *
	 * @var array
	 */
	protected $reusable_blocks = array();

	/**
	 * Associate raw tempids with actual created ids
	 *
	 * @var array
	 */
	protected $tempids = array();

	/**
	 * Construct a new generator instance with data
	 *
	 * @since 4.7.0
	 *
	 * @return void
	 */
	public function __construct() {

		// Load deps.
		$this->load_dependencies();
	}

	/**
	 * Add custom data to a post based on the 'custom' array
	 *
	 * @since 3.16.11
	 * @since 3.28.3 Add extra slashes around JSON strings.
	 * @since 3.30.2 Skip JSON evaluation for non-string values; make publicly accessible.
	 * @since 4.7.0 Moved from `LLMS_Generator`.
	 *
	 * @param int   $post_id WP Post ID.
	 * @param array $raw     Raw data.
	 * @return void
	 */
	public function add_custom_values( $post_id, $raw ) {

		// No custom data, return early.
		if ( empty( $raw['custom'] ) ) {
			return;
		}

		foreach ( $raw['custom'] as $custom_key => $custom_vals ) {
			foreach ( $custom_vals as $val ) {
				$this->add_custom_value( $post_id, $custom_key, $val );
			}
		}
	}

	/**
	 * Add a "custom" post meta data for a given post
	 *
	 * Automatically slashes JSON data when supplied.
	 *
	 * Automatically unserializes serialized data so `add_post_meta()` can re-serialize.
	 *
	 * @since 4.7.0
	 *
	 * @param int    $post_id WP_Post ID.
	 * @param string $key     Meta key.
	 * @param mixed  $val     Meta value.
	 * @return void
	 */
	protected function add_custom_value( $post_id, $key, $val ) {

		// If $val is a JSON string, add slashes before saving.
		if ( is_string( $val ) && null !== json_decode( $val, true ) ) {
			$val = wp_slash( $val );
		}

		add_post_meta( $post_id, $key, maybe_unserialize( $val ) );
	}

	/**
	 * Generate a new LLMS_Post_Model.
	 *
	 * @since 4.7.0
	 * @since 4.7.1 Set the post's excerpt during the initial insert instead of during metadata updates after creation.
	 * @since 7.3.0 Skip adding the `generated_from_id` meta from the original post: this is the case when cloning a cloned post.
	 *              Also skip creating revisions.
	 *
	 * @param string $type      The LLMS_Post_Model post type type. For example "course" for an `LLMS_Course` or `membership` for `LLMS_Membership`.
	 * @param array  $raw       Array of raw, used to create the post.
	 * @param int    $author_id Fallback author ID, used when now author data can be found in `$raw`.
	 * @return LLMS_Post_Model
	 *
	 * @throws Exception When the class identified by `$type` is not found or when an error is encountered during post creation.
	 */
	protected function create_post( $type, $raw = array(), $author_id = null ) {

		$class_name = sprintf( 'LLMS_%s', implode( '_', array_map( 'ucfirst', explode( '_', $type ) ) ) );
		if ( ! class_exists( $class_name ) ) {
			throw new Exception( esc_html( sprintf( __( 'The class "%s" does not exist.', 'lifterlms' ), $class_name ) ), intval( self::ERROR_INVALID_POST ) );
		}

		// Don't create useless revision on "cloning".
		add_filter( 'wp_revisions_to_keep', '__return_zero', 999 );

		// Insert the object.
		$post = new $class_name(
			'new',
			/**
			 * Filter the data used to generate a new post.
			 *
			 * @since 7.4.0
			 *
			 * @param array $new_post_data New post data array.
			 * @param array $raw           Original raw post data array.
			 */
			apply_filters(
				'llms_generator_new_post_data',
				array(
					'post_author'   => $this->get_author_id_from_raw( $raw, $author_id ),
					'post_content'  => isset( $raw['content'] ) ? $raw['content'] : '',
					'post_date'     => isset( $raw['date'] ) ? $this->format_date( $raw['date'] ) : null,
					'post_excerpt'  => isset( $raw['excerpt'] ) ? $raw['excerpt'] : '',
					'post_modified' => isset( $raw['modified'] ) ? $this->format_date( $raw['modified'] ) : null,
					'post_status'   => isset( $raw['status'] ) ? $raw['status'] : $this->get_default_post_status(),
					'post_title'    => $raw['title'],
				),
				$raw
			)
		);

		if ( ! $post->get( 'id' ) ) {
			// Translators: %s = post type name.
			throw new Exception( esc_html( sprintf( __( 'Error creating the %s post object.', 'lifterlms' ), $type ) ), intval( self::ERROR_CREATE_POST ) );
		}

		// Store the temp id if it exists.
		$this->store_temp_id( $raw, $post );

		// Don't set these values again.
		unset( $raw['id'], $raw['author'], $raw['content'], $raw['date'], $raw['excerpt'], $raw['modified'], $raw['name'], $raw['status'], $raw['title'] );
		/**
		 * Skip adding the `generated_from_id` meta from the original post:
		 * this is the case when cloning a cloned post.
		 */
		unset( $raw['custom'][ $post->get( 'meta_prefix' ) . 'generated_from_id' ] );

		$this->set_metadata( $post, $raw );
		$this->set_featured_image( $raw, $post->get( 'id' ) );
		$this->add_custom_values( $post->get( 'id' ), $raw );
		$this->sideload_images( $post, $raw );
		$this->handle_reusable_blocks( $post, $raw );

		// Remove revision prevention.
		remove_filter( 'wp_revisions_to_keep', '__return_zero', 999 );

		return $post;
	}

	/**
	 * Creates a reusable block
	 *
	 * @since 4.7.0
	 *
	 * @param int   $block_id WP_Post ID of the block being imported. This will be the ID as found on the original site.
	 * @param array $block    {
	 *     Array of block data.
	 *
	 *     @type string $title   Title of the reusable block.
	 *     @type string $content Content of the reusable block.
	 * }
	 * @return bool|int The WP_Post ID of the new block on success or `false` on error.
	 */
	protected function create_reusable_block( $block_id, $block ) {

		$block_id = absint( $block_id );

		// Check if the block was previously imported.
		$id = empty( $this->reusable_blocks[ $block_id ] ) ? false : $this->reusable_blocks[ $block_id ];
		if ( ! $id ) {

			// If the block already exists, don't create it again.
			$existing = get_post( $block_id );
			if ( $existing && 'wp_block' === $existing->post_type && $block['title'] === $existing->post_title && $block['content'] === $existing->post_content ) {
				return false;
			}

			$id = $this->insert_resuable_block( $block_id, $block );
		}

		// Don't return 0 if `wp_insert_post()` fails.
		return $id ? $id : false;
	}

	/**
	 * Create a new WP_User from raw data
	 *
	 * @since 4.7.0
	 *
	 * @param array $raw Raw data.
	 * @return int|WP_Error WP_User ID on success or error on failure.
	 */
	protected function create_user( $raw ) {

		/**
		 * Filter the default role used to create a new user during generator imports
		 *
		 * This role is used a role isn't supplied in the raw data.
		 *
		 * @since 4.7.0
		 *
		 * @param string $role WP_User role. Default role is 'administrator'.
		 * @param array  $raw  Original raw author data.
		 */
		$raw['role'] = empty( $raw['role'] ) ? apply_filters( 'llms_generator_new_user_default_role', 'administrator', $raw ) : $raw['role'];

		$data = array(
			'role'       => $raw['role'],
			'user_email' => $raw['email'],
			'user_login' => LLMS_Person_Handler::generate_username( $raw['email'] ),
			'user_pass'  => wp_generate_password(),
		);

		if ( isset( $raw['first_name'] ) && isset( $raw['last_name'] ) ) {
			$data['display_name'] = $raw['first_name'] . ' ' . $raw['last_name'];
			$data['first_name']   = $raw['first_name'];
			$data['last_name']    = $raw['last_name'];
		}

		if ( isset( $raw['description'] ) ) {
			$data['description'] = $raw['description'];
		}

		/**
		 * Filter user data used to create a new user during generator imports
		 *
		 * @since Unknown
		 *
		 * @param array $data Prepared user data to be passed to `wp_insert_user()`.
		 * @param array $raw  Original raw author data.
		 */
		$data      = apply_filters( 'llms_generator_new_author_data', $data, $raw );
		$author_id = wp_insert_user( $data );

		if ( ! is_wp_error( $author_id ) ) {
			/**
			 * Action fired after creation of a new user during generation
			 *
			 *  @since 4.7.0
			 *
			 * @param int   $author_id WP_User ID.
			 * @param array $data      User creation data passed to `wp_insert_user()`.
			 * @param array $raw       Original raw author data.
			 */
			do_action( 'llms_generator_new_user', $author_id, $data, $raw );
		}

		return $author_id;
	}

	/**
	 * Ensure raw dates are correctly formatted to create a post date
	 *
	 * Falls back to current date if no date is supplied.
	 *
	 * @since 3.3.0
	 * @since 3.30.2 Made publicly accessible.
	 * @since 4.7.0 Use `llms_current_time()` in favor of `current_time()`.
	 *
	 * @param string $raw_date Raw date from raw object.
	 * @return string
	 */
	public function format_date( $raw_date = null ) {

		if ( ! $raw_date ) {
			return llms_current_time( 'mysql' );
		}

		return date( 'Y-m-d H:i:s', strtotime( $raw_date ) );
	}

	/**
	 * Accepts raw author data and locates an existing author by email or id or creates one
	 *
	 * @since 3.3.0
	 * @since 4.3.3 Use strict string comparator.
	 * @since 4.7.0 Moved from `LLMS_Generator` and made `protected` instead of `private`.
	 *
	 * @param array $raw Author data.
	 *                   If id and email are provided will use id only if it matches the email for user matching that id in the database.
	 *                   If no id found, attempts to locate by email.
	 *                   If no author found and email provided, creates new user using email.
	 *                   Falls back to current user id.
	 *                   First_name, last_name, and description can be optionally provided.
	 *                   When provided will be used only when creating a new user.
	 * @return int WP_User ID
	 *
	 * @throws Exception When an error is encountered creating a new user.
	 */
	protected function get_author_id( $raw ) {

		$author_id = 0;

		// If raw is missing an ID and Email, use current user id.
		if ( ! isset( $raw['id'] ) && ! isset( $raw['email'] ) ) {
			$author_id = get_current_user_id();
		} else {

			// If id is set, check if the id matches a user in the DB.
			if ( isset( $raw['id'] ) && is_numeric( $raw['id'] ) ) {

				$user = get_user_by( 'ID', $raw['id'] );

				// User exists.
				if ( $user ) {

					// We have a raw email.
					if ( isset( $raw['email'] ) ) {

						// Raw email matches found user's email.
						if ( $user->user_email === $raw['email'] ) {
							$author_id = $user->ID;
						}
					} else {
						$author_id = $user->ID;
					}
				}
			}

			if ( ! $author_id ) {

				if ( isset( $raw['email'] ) ) {

					// See if we have a user that matches by email.
					$user = get_user_by( 'email', $raw['email'] );

					// User exists, use this user.
					if ( $user ) {
						$author_id = $user->ID;
					}
				}
			}

			// No author id, create a new one using the email.
			if ( ! $author_id && isset( $raw['email'] ) ) {

				$author_id = $this->create_user( $raw );

				if ( is_wp_error( $author_id ) ) {
					throw new Exception( esc_html( $author_id->get_error_message() ), intval( self::ERROR_CREATE_USER ) );
				}
			}
		}

		/**
		 * Filter the author ID prior to it being used for the generation of new posts
		 *
		 * @since 4.7.0
		 *
		 * @param int   $author_id WP_User ID of the author.
		 * @param array $raw       Original raw author data.
		 */
		return apply_filters( 'llms_generator_get_author_id', $author_id, $raw );
	}

	/**
	 * Receives a raw array of course, plan, section, lesson, etc data and gets an author id
	 *
	 * Falls back to optionally supplied fallback id.
	 * Falls back to current user id.
	 *
	 * @since 3.3.0
	 * @since 3.30.2 Made publicly accessible.
	 * @since 4.7.0 Moved from `LLMS_Generators`.
	 *
	 * @param array $raw                Raw data.
	 * @param int   $fallback_author_id Optional. WP User ID. Default is `null`.
	 *                                  If not supplied, if no author is set, the current user ID will be used.
	 * @return int|WP_Error
	 */
	public function get_author_id_from_raw( $raw, $fallback_author_id = null ) {

		// If author is set, get the author id.
		if ( isset( $raw['author'] ) ) {
			$author_id = $this->get_author_id( $raw['author'] );
		}

		// Fallback to current user.
		if ( empty( $author_id ) ) {
			$author_id = ! empty( $fallback_author_id ) ? $fallback_author_id : get_current_user_id();
		}

		return $author_id;
	}

	/**
	 * Retrieve the default post status for the generated set of posts
	 *
	 * @since 3.7.3
	 * @since 3.30.2 Made publicly accessible.
	 * @since 4.7.0 Moved from `LLMS_Generators`.
	 *
	 * @return string
	 */
	public function get_default_post_status() {

		/**
		 * Filter the default status used for generating posts
		 *
		 * @since 3.7.3
		 *
		 * @param string         $post_status The default post status.
		 * @param LLMS_Generator $generator   Generator instance.
		 */
		return apply_filters( 'llms_generator_default_post_status', $this->default_post_status, $this );
	}

	/**
	 * Get a WP Term ID for a term by taxonomy and term name
	 *
	 * Attempts to find a given term by name first to prevent duplicates during imports.
	 *
	 * @since 3.3.0
	 * @since 4.7.0 Moved from `LLMS_Generator` and updated method access from `private` to `protected`.
	 *               Throws an exception in favor of returning `null` when an error is encountered.
	 *
	 * @param string $term_name Term name.
	 * @param string $tax       Taxonomy slug.
	 * @return int The created WP_Term `term_id`.
	 *
	 * @throws Exception When an error is encountered during taxonomy term creation.
	 */
	protected function get_term_id( $term_name, $tax ) {

		$term = get_term_by( 'name', $term_name, $tax, ARRAY_A );

		// Not found, create it.
		if ( ! $term ) {

			$term = wp_insert_term( $term_name, $tax );

			if ( is_wp_error( $term ) ) {
				throw new Exception( esc_html( sprintf( __( 'Error creating new term "%s".', 'lifterlms' ), $term_name ) ), intval( self::ERROR_CREATE_TERM ) );
			}

			/**
			 * Triggered when a new term is generated during an import
			 *
			 * @since 4.7.0
			 *
			 * @param array  $term Term information array from `wp_insert_term()`.
			 * @param string $tax  Taxonomy name.
			 */
			do_action( 'llms_generator_new_term', $term, $tax );

		}

		return $term['term_id'];
	}

	/**
	 * Handle importing of reusable blocks stored in post content
	 *
	 * @since 4.7.0
	 *
	 * @param LLMS_Post_Model $post Instance of a post model.
	 * @param array           $raw  Array of raw data.
	 * @return null|bool Returns `null` when importing is disabled, `false` when there are no blocks to import, and `true` on success.
	 */
	protected function handle_reusable_blocks( $post, $raw ) {

		// Importing blocks is disabled.
		if ( ! $this->is_reusable_block_importing_enabled() ) {
			return null;
		}

		// No blocks to import.
		if ( empty( $raw['_extras']['blocks'] ) ) {
			return false;
		}

		$find    = array();
		$replace = array();
		foreach ( $raw['_extras']['blocks'] as $block_id => $block ) {

			$new_id = $this->create_reusable_block( $block_id, $block );
			if ( ! is_wp_error( $new_id ) && is_numeric( $new_id ) ) {
				$find[]    = sprintf( '<!-- wp:block {"ref":%d}', absint( $block_id ) );
				$replace[] = sprintf( '<!-- wp:block {"ref":%d}', $new_id );
			}
		}

		if ( $find && $replace ) {
			$args = array(
				'ID'           => $post->get( 'id' ),
				'post_content' => str_replace( $find, $replace, $post->get( 'content', true ) ),
			);
			return wp_update_post( $args ) ? true : false;
		}

		return false;
	}

	/**
	 * Insert a reusable block into the database
	 *
	 * @since 4.7.0
	 *
	 * @param int   $block_id WP_Post ID of the block being imported. This will be the ID as found on the original site.
	 * @param array $block    {
	 *     Array of block data.
	 *
	 *     @type string $title   Title of the reusable block.
	 *     @type string $content Content of the reusable block.
	 * }
	 * @return int WP_Post ID on success or `0 on error.
	 */
	protected function insert_resuable_block( $block_id, $block ) {

		$id = wp_insert_post(
			array(
				'post_content' => $block['content'],
				'post_title'   => $block['title'],
				'post_type'    => 'wp_block',
				'post_status'  => 'publish',
			)
		);

		if ( $id ) {

			$this->reusable_blocks[ $block_id ] = $id;

			/**
			 * Triggered when a new reusable block is created during an import
			 *
			 * @since 4.7.0
			 *
			 * @param int   $id    WP_Post ID of the block.
			 * @param array $block Array of block information from the import.
			 */
			do_action( 'llms_generator_new_reusable_block', $id, $block );

		}

		return $id;
	}

	/**
	 * Determines if image sideloading is enabled for the generator
	 *
	 * @since 4.7.0
	 *
	 * @return boolean If `true`, sideloading is enabled, otherwise sideloading is disabled.
	 */
	public function is_image_sideloading_enabled() {

		/**
		 * Filter the status of image sideloading for the generator.
		 *
		 * @since 4.7.0
		 *
		 * @param boolean        $enabled   Whether or not sideloading is enabled.
		 * @param LLMS_Generator $generator Generator instance.
		 */
		return apply_filters( 'llms_generator_is_image_sideloading_enabled', true, $this );
	}

	/**
	 * Determines if reusable block importing is enabled generator
	 *
	 * @since 4.7.0
	 *
	 * @return boolean If `true`, importing is enabled, otherwise importing is disabled.
	 */
	public function is_reusable_block_importing_enabled() {

		/**
		 * Filter the status of reusable block importing for the generator.
		 *
		 * @since 4.7.0
		 *
		 * @param boolean        $enabled   Whether or not block importing is enabled.
		 * @param LLMS_Generator $generator Generator instance.
		 */
		return apply_filters( 'llms_generator_is_reusable_block_importing_enabled', true, $this );
	}

	/**
	 * Load additional generator classes and other dependencies
	 *
	 * @since 4.7.0
	 *
	 * @return void
	 */
	protected function load_dependencies() {

		// For featured image creation via `media_sideload_image()`.
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
	}

	/**
	 * Saves an image (from URL) to the media library and sets it as the featured image for a given post
	 *
	 * @since 3.3.0
	 * @since 4.7.0 Moved from `LLMS_Generator` and made `protected` instead of `private`.
	 *               Add a return instead of `void`; Don't import if sideloading is disabled; Use `$this->sideload_image()` sideloading.
	 *
	 * @param string $url_or_raw Array of raw data or URL to an image.
	 * @param int    $post_id    WP Post ID.
	 * @return null|false|int Returns `null` if sideloading is disabled, WP Post ID of the attachment on success, `false` on error.
	 */
	protected function set_featured_image( $url_or_raw, $post_id ) {

		// Sideloading is disabled.
		if ( ! $this->is_image_sideloading_enabled() ) {
			return null;
		}

		$image_url = ( is_array( $url_or_raw ) && ! empty( $url_or_raw['featured_image'] ) ) ? $url_or_raw['featured_image'] : $url_or_raw;

		if ( $image_url && is_string( $image_url ) ) {

			$id = $this->sideload_image( $post_id, $image_url, 'id' );
			if ( ! is_wp_error( $id ) ) {
				set_post_thumbnail( $post_id, $id );
				return $id;
			}
		}

		return false;
	}

	/**
	 * Configure the default post status for generated posts at runtime
	 *
	 * @since 3.7.3
	 *
	 * @param string $status Any valid WP Post Status.
	 * @return void
	 */
	public function set_default_post_status( $status ) {
		$this->default_post_status = $status;
	}

	/**
	 * Set all metadata for a given post object
	 *
	 * This method will only set metadata for registered LLMS_Post_Model properties.
	 *
	 * @since 4.7.0
	 *
	 * @param LLMS_Post_Model $post An LLMS post object.
	 * @param array           $raw  Array of raw data.
	 * @return void
	 */
	protected function set_metadata( $post, $raw ) {

		// Set all metadata.
		foreach ( array_keys( $post->get_properties() ) as $key ) {
			if ( isset( $raw[ $key ] ) ) {
				$post->set( $key, $raw[ $key ] );
			}
		}
	}

	/**
	 * Sideload an image from a url
	 *
	 * @since 4.7.0
	 *
	 * @link https://developer.wordpress.org/reference/hooks/http_request_host_is_external/ If exporting from a local site and importing into another local site, images *will not* be side loaded as a result of this condition in the WP Core
	 *
	 * @param int    $post_id WP_Post ID of the post where the image will be attached.
	 * @param string $url     The image's URL.
	 * @return string|int|WP_Error Returns a WP_Error on failure, the image's new URL when `$return` is "src", otherwise returns the image's attachment ID.
	 */
	protected function sideload_image( $post_id, $url, $return = 'src' ) {

		// Check if the image was previously sideloaded.
		$id = empty( $this->images[ $url ] ) ? false : $this->images[ $url ];

		// Image was not previously sideloaded.
		if ( ! $id ) {

			$id = media_sideload_image( $url, $post_id, null, 'id' );
			if ( is_wp_error( $id ) ) {
				return $id;
			}

			// Store the ID for future usage.
			$this->images[ $url ] = $id;

		}

		return 'src' === $return ? wp_get_attachment_url( $id ) : $id;
	}

	/**
	 * Sideload images found in a given post
	 *
	 * This attempts to sideload the `src` attribute of every <img> element
	 * found in the `post_content` of the supplied post.
	 *
	 * @since 4.7.0
	 *
	 * @param LLMS_Post_Model $post Post object.
	 * @param array           $raw  Array of raw data.
	 * @return null|boolean Returns `true` on success, `false` if there were no images to update, or `null` if sideloading is disabled.
	 */
	protected function sideload_images( $post, $raw ) {

		// Sideloading is disabled.
		if ( ! $this->is_image_sideloading_enabled() ) {
			return null;
		}

		// No images to sideload.
		if ( empty( $raw['_extras']['images'] ) ) {
			return false;
		}

		/**
		 * List of hostnames from which sideloading is explicitly disabled
		 *
		 * If the source url of an image is from a host in this list, the image will not be sideloaded
		 * during generation.
		 *
		 * By default the current site is included in the blocklist ensuring that images aren't
		 * sideloaded into the same site.
		 *
		 * @since 4.7.0
		 *
		 * @param string[] $blocked_hosts Array of hostnames.
		 */
		$blocked_hosts = apply_filters(
			'llms_generator_sideload_hosts_blocklist',
			array(
				parse_url( get_site_url(), PHP_URL_HOST ),
			)
		);

		$post_id = $post->get( 'id' );
		$find    = array();
		$replace = array();
		foreach ( $raw['_extras']['images'] as $src ) {

			// Don't sideload images from blocked hosts.
			if ( in_array( parse_url( $src, PHP_URL_HOST ), $blocked_hosts, true ) ) {
				continue;
			}

			$new_src = $this->sideload_image( $post_id, $src );
			if ( ! is_wp_error( $new_src ) ) {
				$find[]    = $src;
				$replace[] = $new_src;
			}
		}

		if ( $find && $replace ) {
			$content = str_replace( $find, $replace, $post->get( 'content', true ) );
			return $post->set( 'content', $content );
		}

		return false;
	}

	/**
	 * Accepts a raw object, finds the raw id and stores it
	 *
	 * @since 3.3.0
	 *
	 * @param array           $raw Array of raw data.
	 * @param LLMS_Post_Model $obj The LLMS Post Object generated from the raw data.
	 * @return int|false Raw id when present or `false` if no raw id was found.
	 */
	protected function store_temp_id( $raw, $obj ) {

		if ( empty( $raw['id'] ) ) {
			return false;
		}

		// Ensure the object post type array exists.
		if ( ! isset( $this->tempids[ $obj->get( 'type' ) ] ) ) {
			$this->tempids[ $obj->get( 'type' ) ] = array();
		}

		// Store the id on the meta table.
		$obj->set( 'generated_from_id', $raw['id'] );

		// Store it in the object for prereq handling later.
		$this->tempids[ $obj->get( 'type' ) ][ $raw['id'] ] = $obj->get( 'id' );

		return $raw['id'];
	}
}
