<?php
/**
 * LLMS_Media_Protector class
 *
 * @package LifterLMS/Classes
 *
 * @since 7.7.0
 * @version 7.7.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Media_Protector class.
 *
 * Allows uploaded media files to be protected from unauthorized downloading.
 *
 * WordPress uses the terms "media" and "attachment" interchangeably to describe uploaded files.
 * When a file is uploaded to WordPress, a post is created with type = 'attachment' and the file name and path relative
 * to the upload directory, normally `WP_CONTENT_DIR . '/uploads'`, is saved as '_wp_attached_file' metadata.
 *
 * Example of uploading a file:
 *
 *     $protector = new LLMS_Media_Protector( '/social-learning' );
 *     $id        = $protector->handle_upload( 'image', 0, 'llms_sl_authorize_media_view', $post_data );
 *
 * Example of protecting a file:
 *
 *     add_filter( 'llms_sl_authorize_media_view', array( $this, 'authorize_media_view' ), 10, 3 );
 *
 *     public function authorize_media_view( $is_authorized, $media_id, $url ) {
 *         $is_authorized = current_user_can( 'view_others_students' );
 *         return $is_authorized;
 *     }
 *
 * @since 7.7.0
 *
 * @todo Add handling of HTTP range requests. See {@see https://datatracker.ietf.org/doc/html/rfc7233} and
 *       {@see https://developer.mozilla.org/en-US/docs/Web/HTTP/Range_requests}.
 * @todo Add WordPress multi-site capability.
 */
class LLMS_Media_Protector {

	/**
	 * The meta key used to specify the filter hook name that authorizes viewing of a media file.
	 *
	 * The key is protected by prefixing it with an underscore '_', which causes WordPress to not display it in
	 * a custom fields interface. {@see is_protected_meta()}.
	 *
	 * @since 7.7.0
	 *
	 * @var string
	 */
	public const AUTHORIZATION_FILTER_KEY = '_llms_media_authorization_filter';

	/**
	 * Serve the media file by reading and outputting it with the readfile() function.
	 *
	 * This is the least efficient way to serve a file because it uses a PHP process instead of a HTTP server thread.
	 * For small files or a small number of protected files on a page, this may not be noticeable. However, the server's
	 * configuration may need to be changed to allow more PHP processes to run, which will use more memory.
	 *
	 * @since 7.7.0
	 *
	 * @var int
	 */
	public const SERVE_READ_FILE = 1;

	/**
	 * Serve the media file by redirecting the HTTP client with a "Location" header.
	 *
	 * This is the least secure way to serve a file because an unprotected URL is given to the HTTP client.
	 * It is unlikely, yet possible, that the URL could then be used by an unauthorized user to view the file.
	 *
	 * @since 7.7.0
	 *
	 * @var int
	 */
	public const SERVE_REDIRECT = 2;

	/**
	 * Serve the media file by sending an "X-Sendfile" style header and let the HTTP server serve the file.
	 *
	 * This is the most efficient and most secure way to serve a file. It requires one of the following HTTP servers.
	 * - {@see https://httpd.apache.org/ Apache httpd} with {@see https://tn123.org/mod_xsendfile/ mod_xsendfile}
	 * - {@see http://cherokee-project.com/doc/other_goodies.html Cherokee}
	 * - {@see https://redmine.lighttpd.net/projects/lighttpd/wiki/X-LIGHTTPD-send-file lighttpd}
	 * - {@see https://www.nginx.com/resources/wiki/start/topics/examples/x-accel/ NGINX}
	 *
	 * @since 7.7.0
	 *
	 * @var int
	 */
	public const SERVE_SEND_FILE = 3;

	/**
	 * The name of the URL parameter for whether the media image should be treated as an icon.
	 *
	 * @since 7.7.0
	 *
	 * @var string
	 */
	public const URL_PARAMETER_ICON = 'llms_media_icon';

	/**
	 * The name of the URL parameter for the media post ID.
	 *
	 * @since 7.7.0
	 *
	 * @var string
	 */
	public const URL_PARAMETER_ID = 'llms_media_id';

	/**
	 * The name of the URL parameter for when the LifterLMS rewrite rule changes a URL that directly accesses the
	 * 'llms-uploads' directory into '/index.php?llms_protected_url=llms-uploads/PATH_TO_FILE'.
	 *
	 * @since 7.7.0
	 *
	 * @var string
	 */
	public const URL_PARAMETER_PROTECTED_URL = 'llms_protected_url';

	/**
	 * The name of the URL parameter for the requested media image size.
	 *
	 * @since 7.7.0
	 *
	 * @var string
	 */
	public const URL_PARAMETER_SIZE = 'llms_media_image_size';

	/**
	 * An optional path added to the base upload path.
	 *
	 * If it is not empty, it will have a leading slash and will not have a trailing slash.
	 * Normally, the full path is `WP_CONTENT_DIR . "/uploads/$base/$additional/$year/$month/$file_name"`.
	 *
	 * @since 7.7.0
	 *
	 * @var string
	 */
	protected $additional_upload_path = '';

	/**
	 * A base path for uploaded LifterLMS files.
	 *
	 * If it is not empty, it will have a leading slash and will not have a trailing slash.
	 * Normally, the full path is `WP_CONTENT_DIR . "/uploads/$base/$additional/$year/$month/$file_name"`.
	 *
	 * @since 7.7.0
	 *
	 * @var string
	 */
	protected $base_upload_path = '';

	/**
	 * Set up this class.
	 *
	 * @since 7.7.0
	 *
	 * @param string $additional_upload_path This path is added to the base upload path.
	 * @param string $base_upload_path       This path is appended to the WordPress upload path, which defaults to
	 *                                       `WP_CONTENT_DIR . '/uploads'` in {@see _wp_upload_dir()}.
	 * @return void
	 */
	public function __construct( $additional_upload_path = '', $base_upload_path = '/lifterlms' ) {

		$this->set_base_upload_path( $base_upload_path );
		$this->set_additional_upload_path( $additional_upload_path );
	}

	/**
	 * Adds query parameters to a protected media URL.
	 *
	 * Hooked to the {@see 'wp_get_attachment_image_src'} filter in {@see wp_get_attachment_image_src()}
	 * by {@see LLMS_Media_Protector::register_callbacks()}.
	 *
	 * @since 7.7.0
	 *
	 * @param array|false  $image    {
	 *     Array of image data, or boolean false if no image is available.
	 *
	 *     @type string $0 Image source URL.
	 *     @type int    $1 Image width in pixels.
	 *     @type int    $2 Image height in pixels.
	 *     @type bool   $3 Whether the image is a resized image.
	 * }
	 * @param int          $media_id The post ID of the image.
	 * @param string|int[] $size     Requested image size. Can be any registered image size name,
	 *                               or an array of width and height values in pixels (in that order).
	 * @param bool         $icon     Whether the image should be treated as an icon.
	 * @return array|false
	 */
	public function authorize_media_image_src( $image, $media_id, $size, $icon ) {

		if ( ! is_numeric( $media_id ) || ! intval( $media_id ) ) {
			// Nothing to verify.
			return $image;
		}

		$is_authorized = $this->is_authorized_to_view( get_current_user_id(), $media_id );
		if ( is_null( $is_authorized ) ) {
			// The media file is not protected.
			return $image;
		} elseif ( false === $is_authorized ) {
			// Return the same thing that wp_get_attachment_image_src would return if no image found.
			return false;
		}

		$image[0] = add_query_arg(
			array(
				self::URL_PARAMETER_ID   => $media_id,
				self::URL_PARAMETER_SIZE => rawurlencode( is_array( $size ) ? wp_json_encode( $size ) : $size ),
				self::URL_PARAMETER_ICON => $icon ? 1 : 0,
			),
			trailingslashit( home_url() )
		);

		return $image;
	}

	/**
	 * Returns the unchanged URL if the media file is not protected,
	 * else if the user is authorized, returns a URL that triggers {@see LLMS_Media_Protector::serve_file()} when requested,
	 * else returns a URL to a placeholder file.
	 *
	 * The result of this filter is cached for the duration of the current HTTP request.
	 *
	 * Hooked to the {@see 'wp_get_attachment_url'} filter in {@see wp_get_attachment_url()}
	 * by {@see LLMS_Media_Protector::register_callbacks()}.
	 *
	 * @since 7.7.0
	 *
	 * @param string $url      URL for the given media file.
	 * @param int    $media_id The post ID of the media file.
	 * @return string
	 */
	public function authorize_media_url( $url, $media_id ) {

		$is_authorized = $this->is_authorized_to_view( get_current_user_id(), $media_id );
		if ( true === $is_authorized ) {
			$url = add_query_arg(
				array( self::URL_PARAMETER_ID => $media_id ),
				trailingslashit( home_url() )
			);
		} elseif ( false === $is_authorized ) {
			$url = '';
		}
		// If $is_authorized is null, do not change $url because it is unprotected.

		return $url;
	}

	/**
	 * Modify the media upload directory if this is a LifterLMS request.
	 *
	 * @param $params
	 *
	 * @return array
	 */
	public function change_media_upload_directory( $params ) {
		if ( isset( $_REQUEST['llms'] ) && '1' === $_REQUEST['llms'] ) {
			$params = $this->upload_dir( $params );
		}

		return $params;
	}

	/**
	 * Adds authorization meta after an attachment is added.
	 *
	 * @param $media_id
	 *
	 * @return void
	 */
	public function add_authorization_meta_after_attachment_added( $media_id ) {
		$attachment = get_post( $media_id );
		if ( $attachment && 'attachment' === $attachment->post_type && isset( $_REQUEST['llms'] ) && '1' === $_REQUEST['llms'] ) {
			$this->add_authorization_meta_to_media_post( $media_id );
		}
	}

	/**
	 * Returns a path path with a leading slash and without a trailing slash, or if the given path is empty, an empty string.
	 *
	 * @since 7.7.0
	 *
	 * @param string $path The path to be formatted.
	 * @return string An empty string or a path with a leading slash and without a trailing slash.
	 */
	protected function format_path( $path ) {

		if ( '' === $path ) {
			return $path;
		}

		// Add leading slash.
		if ( strpos( $path, '/' ) !== 0 ) {
			$path = '/' . $path;
		}

		// Strip trailing slash.
		$path = untrailingslashit( $path );

		return $path;
	}

	/**
	 * Returns the additional path that is added onto the base path.
	 *
	 * @since 7.7.0
	 *
	 * @return string
	 */
	public function get_additional_upload_path() {

		return $this->additional_upload_path;
	}

	/**
	 * Returns the base upload path.
	 *
	 * @since 7.7.0
	 *
	 * @return string
	 */
	public function get_base_upload_path() {

		return $this->base_upload_path;
	}

	/**
	 * Returns the absolute path to the media file in the upload directory.
	 *
	 * @since 7.7.0
	 *
	 * @param int $media_id The media post ID.
	 * @return string
	 */
	public function get_media_path( $media_id ) {

		$upload_dir = wp_upload_dir();
		$file_name  = get_post_meta( $media_id, '_wp_attached_file', true );

		return $upload_dir['basedir'] . DIRECTORY_SEPARATOR . $file_name;
	}

	/**
	 * Gets the size from the URL query parameter.
	 *
	 * @see wp_create_image_subsizes()
	 * @since 7.7.0
	 *
	 * @return string|int[]|null
	 */
	protected function get_size() {

		$size = ( isset( $_GET[ self::URL_PARAMETER_SIZE ] ) ) ? sanitize_text_field( $_GET[ self::URL_PARAMETER_SIZE ] ) : null;
		if ( false === $size ) {
			$size = null;
		} elseif ( is_string( $size ) && '[' === $size[0] ) {
			$size = json_decode( $size );
			// Sanitize untrusted external input.
			if ( isset( $size[0] ) ) {
				$size[0] = (int) $size[0];
			}
			if ( isset( $size[1] ) ) {
				$size[1] = (int) $size[1];
			}
		}

		return $size;
	}

	/**
	 * Saves a file submitted from a POST request and creates an attachment post for it.
	 *
	 * @since 7.7.0
	 *
	 * @param string $file_id   Index of the `$_FILES` array that the file was sent. Required.
	 * @param int    $post_id   The post ID of a post to attach the media item to. Required, but can
	 *                          be set to 0, creating a media item that has no relationship to a post.
	 * @param string $hook_name The name of the filter that will be applied by {@see LLMS_Media_Protector::is_authorized_to_view()}.
	 * @param array  $post_data Optional. Set attachment elements that are sent to {@see wp_insert_post()}.
	 *                          The defaults are set in {@see media_handle_upload()}.
	 * @param array  $overrides Optional. Override the {@see wp_handle_upload()} behavior.
	 * @return int|WP_Error Post ID of the media file or a WP_Error object on failure.
	 */
	public function handle_upload(
		$file_id,
		$post_id,
		$hook_name,
		$post_data = array(),
		$overrides = array( 'test_form' => false )
	) {

		$post_data['meta_input'][ self::AUTHORIZATION_FILTER_KEY ] = $hook_name;
		add_filter( 'upload_dir', array( $this, 'upload_dir' ), 10, 1 );
		$media_id = media_handle_upload( $file_id, $post_id, $post_data, $overrides );
		remove_filter( 'upload_dir', array( $this, 'upload_dir' ), 10 );
		$this->add_authorization_meta_to_media_post( $media_id );

		return $media_id;
	}

	/**
	 * Returns true if the user is authorized to view the requested media file, false if not authorized,
	 * or null if the media file is not protected.
	 *
	 * Authorization is handled by the callback added to the filter hook name given to {@see LLMS_Media_Protector::handle_upload()}.
	 *
	 * @since 7.7.0
	 *
	 * @param int $user_id  The user ID.
	 * @param int $media_id The post ID of the media file.
	 * @return bool|null
	 */
	public function is_authorized_to_view( $user_id, $media_id ): ?bool {
		if ( ! is_numeric( $media_id ) || ! intval( $media_id ) ) {
			return null;
		}

		$cache_key     = 'llms-media-authorization-' . $media_id . '-' . $user_id;
		$authorization = wp_cache_get( $cache_key, 'llms_media_authorization', false, $found );
		if ( $found ) {
			return ( ( $authorization === 'null' ) ? null : $authorization );
		}

		$authorization_filter = get_post_meta( $media_id, self::AUTHORIZATION_FILTER_KEY, true );
		if ( ! $authorization_filter ) {
			// We need to use string of 'null' since on some hosting like wordpress.com the value of null comes back as bool false.
			wp_cache_add( $cache_key, 'null', 'llms_media_authorization' );

			return null;
		}

		// The default is to allow WordPress super admins and LifterLMS managers to view all protected media files.
		// @todo Consider allowing users with the some of the 'students' capabilities.
		if ( is_super_admin( $user_id ) ) {
			$is_authorized = true;
		} else {
			$user          = wp_get_current_user();
			$is_authorized = in_array( 'llms_manager', $user->roles, true ) || intval( get_post_field( 'post_author', $media_id ) ) === $user_id;
		}

		// Allow student to view if they have an incomplete attempt for a quiz this media is for.
		if ( ! $is_authorized && llms_get_student() ) {
			$authorized_quiz_ids = (array) get_post_meta( $media_id, '_llms_quiz_id', true );

			if ( $authorized_quiz_ids ) {
				$student_quizzes = llms_get_student()->quizzes()->get_all( $authorized_quiz_ids );
				foreach ( $student_quizzes as $student_quiz_attempt ) {
					$quiz_id = $student_quiz_attempt->get( 'quiz_id' );
					if ( ! ( new LLMS_Quiz( $quiz_id ) )->is_open() ) {
						continue;
					}
					if ( 'incomplete' === $student_quiz_attempt->get( 'status' ) ) {
						$is_authorized = true;
						break;
					}
				}
			}
		}

		/**
		 * Allow the plugin that is protecting the file to authorize access to it.
		 *
		 * The default is to allow the user to view the file in case there is a not a callback for the authorization hook.
		 *
		 * @since 7.7.0
		 *
		 * @param bool|null $is_authorized True if the user is authorized to view the media file, false if not authorized,
		 *                                 or null if the file is not protected.
		 * @param int       $media_id      The post ID of the media file.
		 * @param int       $user_id       The ID of the user wanting to view the media file.
		 */
		$is_authorized = apply_filters( $authorization_filter, $is_authorized, $media_id, $user_id );

		// Sanitize value.
		if ( ! is_bool( $is_authorized ) && ! is_null( $is_authorized ) ) {
			$is_authorized = (bool) $is_authorized;
		}

		/**
		 * Determine how long the media authorization is valid for.
		 *
		 * @since 7.7.0
		 *
		 * @param int   $cache_expiration    Time in seconds to cache the authorization for this media file and user.
		 * @param int   $media_id            The post ID of the media file.
		 * @param int   $user_id             The ID of the user wanting to view the media file.
		 */
		$cache_expiration = apply_filters( 'llms_media_protection_cache_expiration_time', MINUTE_IN_SECONDS * 1, $media_id, $user_id );

		wp_cache_add( $cache_key, $is_authorized, 'llms_media_authorization', $cache_expiration );

		return $is_authorized;
	}

	/**
	 * Returns true if the current request has a different modification date or entity tag than the requested file.
	 *
	 * @since 7.7.0
	 *
	 * @param string $file_name The complete path and file name that the request is for.
	 * @param string $entity_tag {@see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/ETag}.
	 * @return bool
	 */
	protected function is_requested_file_modified( $file_name, $entity_tag ): bool {

		$is_modified = true;

		$file_modified     = filemtime( $file_name );
		$if_modified_since = ( isset( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) ) ? sanitize_text_field( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) : '';
		if ( strtotime( $if_modified_since ) === $file_modified ) {
			$is_modified = false;
		}

		$if_match = llms_filter_input( INPUT_SERVER, 'HTTP_IF_MATCH', FILTER_SANITIZE_URL );
		if ( $if_match === $entity_tag ) {
			$is_modified = false;
		}

		return $is_modified;
	}

	/**
	 * Changes the URLs for image attachments prepared for JavaScript.
	 *
	 * Hooked to the {@see 'wp_prepare_attachment_for_js'} filter in {@see wp_prepare_attachment_for_js()}
	 * by {@see LLMS_Media_Protector::register_callbacks()}.
	 *
	 * @since 7.7.0
	 *
	 * @param array       $response   Array of prepared attachment data.
	 * @param WP_Post     $attachment Attachment object.
	 * @param array|false $meta       Array of attachment meta data, or false if there is none.
	 * @return array
	 */
	public function prepare_attachment_for_js( $response, $attachment, $meta ) {

		$is_authorized = $this->is_authorized_to_view( get_current_user_id(), $attachment->ID );
		if ( is_null( $is_authorized ) || ! array_key_exists( 'sizes', $response ) ) {
			return $response;
		}

		foreach ( $response['sizes'] as $size => &$size_meta ) {
			$size_meta['url'] = add_query_arg(
				array(
					self::URL_PARAMETER_ID   => $attachment->ID,
					self::URL_PARAMETER_SIZE => $size,
				),
				trailingslashit( home_url() )
			);
		}

		return $response;
	}

	/**
	 * Reads and outputs the file.
	 *
	 * This method sends the entire file and does not handle
	 * {@see https://developer.mozilla.org/en-US/docs/Web/HTTP/Range_requests HTTP range requests}.
	 *
	 * @since 7.7.0
	 *
	 * @param string $file_name The file path and name.
	 * @return void
	 */
	protected function read_file( $file_name ): void {

		// @todo What about the web server time limit?
		set_time_limit( 0 );

		// Tell the HTTP client that we do not handle HTTP range requests.
		header( 'Accept-Ranges: none' );

		// Turn off all output buffers to avoid running out of memory with large files.
		// @see https://www.php.net/readfile#refsect1-function.readfile-notes.
		wp_ob_end_flush_all();

		$result = readfile( $file_name ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_readfile
		if ( false === $result ) {
			// Tell the HTTP client that something unspecific went wrong. readfile() outputs warnings to the PHP error log.
			header( 'HTTP/1.1 500 Internal Server Error' );
		}
	}

	/**
	 * Registers the callback functions for action and filter hooks that allow this class to protect uploaded media files.
	 *
	 * @since 7.7.0
	 *
	 * @return self
	 */
	public function register_callbacks() {

		if (
			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			array_key_exists( self::URL_PARAMETER_ID, $_GET ) ||
			array_key_exists( self::URL_PARAMETER_PROTECTED_URL, $_GET )
			// phpcs:enable WordPress.Security.NonceVerification.Recommended
		) {
			add_action( 'init', array( $this, 'serve_file' ), 10 );
		} else {
			add_filter( 'admin_init', array( $this, 'save_mod_rewrite_rules' ), 10, 1 );
			add_filter( 'wp_prepare_attachment_for_js', array( $this, 'prepare_attachment_for_js' ), 99, 3 );
			add_filter( 'wp_get_attachment_image_src', array( $this, 'authorize_media_image_src' ), 10, 4 );
			add_filter( 'wp_get_attachment_url', array( $this, 'authorize_media_url' ), 10, 2 );
			add_filter( 'upload_dir', array( $this, 'change_media_upload_directory' ), 10, 1 );
			add_action( 'add_attachment', array( $this, 'add_authorization_meta_after_attachment_added' ), 10, 1 );
		}

		return $this;
	}

	/**
	 * Adds .htaccess and blank index.php/html files to the upload directory to protect the files from being listed.
	 *
	 * Hooked to the {@see 'flush_rewrite_rules_hard'} filter in {@see WP_Rewrite::flush_rules()}
	 * by {@see LLMS_Media_Protector::register_callbacks()}.
	 *
	 * @since 7.7.0
	 *
	 * @return bool
	 */
	public function save_mod_rewrite_rules() {
		// TODO: Different for multi-site?

		if ( false === get_transient( 'lifterlms_check_media_protection_files' ) ) {
			global $wp_filesystem;
			/** @var WP_Filesystem_Base $wp_filesystem */

			/** Load files that define {@see WP_Filesystem()}, {@see media_handle_sideload()}, and many image functions. */
			require_once ABSPATH . 'wp-admin/includes/file.php';

			WP_Filesystem();

			$uploads = wp_get_upload_dir();

			$upload_path   = $uploads['basedir'] . $this->get_base_upload_path();
			$htaccess_file = $upload_path . '/.htaccess';

			$upload_path_writeable = $wp_filesystem->is_writable( $upload_path );

			$rules  = "Options -Indexes\n";
			$rules .= "deny from all\n";

			if ( $upload_path_writeable && ! $wp_filesystem->exists( $htaccess_file ) ) {
				$wp_filesystem->put_contents( $htaccess_file, $rules, 0644 );
			} elseif ( $upload_path_writeable ) {
				$contents = $wp_filesystem->get_contents( $htaccess_file );
				if ( $contents !== $rules ) {
					$wp_filesystem->put_contents( $htaccess_file, $rules, 0644 );
				}
			}

			if ( $upload_path_writeable && ! $wp_filesystem->exists( $upload_path . '/index.php' ) ) {
				$wp_filesystem->put_contents( $upload_path . '/index.php', '<?php' . PHP_EOL . '// Silence is golden.' );
			}

			if ( $upload_path_writeable && ! $wp_filesystem->exists( $upload_path . '/index.html' ) ) {
				$wp_filesystem->put_contents( $upload_path . '/index.html', '' );
			}

			// Get the main directories in the root of the directory we're scanning.
			$upload_root_dirs = glob( $upload_path . '/*', GLOB_ONLYDIR | GLOB_NOSORT | GLOB_MARK );

			// Now get all the recursive directories.
			$upload_sub_dirs = glob( $upload_path . '/*/**', GLOB_ONLYDIR | GLOB_NOSORT | GLOB_MARK );

			// Merge the two arrays together, and avoid any possible duplicates.
			foreach ( array_unique( array_merge( $upload_root_dirs, $upload_sub_dirs ) ) as $dir ) {
				if ( ! wp_is_writable( $dir ) ) {
					continue;
				}

				// Create index.php, if it doesn't exist.
				if ( ! $wp_filesystem->exists( $dir . 'index.php' ) ) {
					$wp_filesystem->put_contents( $dir . 'index.php', '<?php' . PHP_EOL . '// Silence is golden.' );
				}

				if ( ! $wp_filesystem->exists( $dir . 'index.html' ) ) {
					$wp_filesystem->put_contents( $dir . 'index.html', '' );
				}
			}

			set_transient( 'lifterlms_check_media_protection_files', true, DAY_IN_SECONDS );
		}
	}

	/**
	 * Outputs an X-Sendfile or X-Accel-Redirect HTTP header which will instruct the HTTP server
	 * to send the file so that PHP doesn't have to.
	 *
	 * If none of the following HTTP servers are detected, {@see LLMS_Media_Protector::read_file()} is called.
	 * - {@see https://tn123.org/mod_xsendfile/ Apache mod_xsendfile}
	 * - {@see https://redmine.lighttpd.net/projects/lighttpd/wiki/Docs_ModCGI Lighttpd}
	 * - {@see https://www.nginx.com/resources/wiki/start/topics/examples/xsendfile/ NGINX}
	 * - {@see https://cherokee-project.com/doc/other_goodies.html#x-sendfile Cherokee}
	 *
	 * Add `$_SERVER['MOD_X_SENDFILE_ENABLED'] = '1';` in `wp-config.php` if web server auto-detection isn't working.
	 *
	 * IIS administrators may want to use {@see https://github.com/stakach/IIS-X-Sendfile-plugin}.
	 *
	 * @since 7.7.0
	 *
	 * @param string $file_name The file path and name.
	 * @param int    $media_id  The post ID of the media file. Not used in this implementation, but here for consistency
	 *                          with the other "serve" methods and may be useful in an overriding this method.
	 * @return void
	 */
	protected function send_file( $file_name, $media_id ) {
		$server_software = ( isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( $_SERVER['SERVER_SOFTWARE'] ) : '' );

		if (
			( array_key_exists( 'MOD_X_SENDFILE_ENABLED', $_SERVER ) && '1' === $_SERVER['MOD_X_SENDFILE_ENABLED'] ) ||
			( function_exists( 'apache_get_modules' ) && in_array( 'mod_xsendfile', apache_get_modules(), true ) ) ||
			stristr( $server_software, 'cherokee' ) ||
			stristr( $server_software, 'lighttpd' )
		) {
			header( "X-Sendfile: $file_name" );

		} elseif ( stristr( $server_software, 'nginx' ) ) {
			/**
			 * @see https://www.nginx.com/resources/wiki/start/topics/examples/xsendfile/
			 * @see https://woocommerce.com/document/digital-downloadable-product-handling/#nginx-setting
			 */
			// NGINX requires a URI without the server's root path.
			$wp_root = $this->find_wp_root();

			if ( $wp_root ) {
				$nginx_file_name = substr( $file_name, strlen( $this->find_wp_root() ) );
				header( 'X-Accel-Redirect: ' . urlencode( $nginx_file_name ) );
			} else {
				$this->read_file( $file_name );
			}
		} else {
			$this->read_file( $file_name );
		}
	}

	protected function find_wp_root() {
		$dir = dirname( WP_CONTENT_DIR );
		while ( $dir ) {
			if ( file_exists( $dir . '/wp-load.php' ) || file_exists( $dir . '/wp-config.php' ) ) {
				return $dir;
			}

			$parent_dir = dirname( $dir );
			if ( $parent_dir === $dir ) {
				// We have reached the root directory
				break;
			}
			$dir = $parent_dir;
		}
		return false; // Root directory not found
	}


	/**
	 * Send headers for the download.
	 *
	 * @since 7.7.0
	 *
	 * @param string $file_name The file path and name.
	 * @param int    $media_id  The post ID of the media file.
	 * @return void
	 */
	protected function send_headers( $file_name, $media_id ) {

		$file_size = @filesize( $file_name ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		if ( ! $file_size ) {
			return;
		}

		$media_file   = get_post( $media_id );
		$content_type = $media_file->post_mime_type;

		header( "Content-Type: $content_type" );
		header( "Content-Length: $file_size" );
		header( 'X-Robots-Tag: noindex, nofollow', true );
	}

	/**
	 * Sends a header that redirects the HTTP client to the media file's URL.
	 *
	 * @since 7.7.0
	 *
	 * @param int               $media_id The post ID of the media file.
	 * @param string|int[]|null $size     A registered image size name, or an array of width and height values in pixels.
	 * @param bool|null         $icon     Whether the image should fall back to a mime type icon.
	 * @return void
	 */
	protected function send_redirect( $media_id, $size, $icon ): void {

		if ( is_null( $size ) && is_null( $icon ) ) {
			$url = wp_get_attachment_url( $media_id );
		} else {
			$url = wp_get_attachment_image_url( $media_id, $size, $icon );
		}

		header( "Location: $url" );
	}

	protected function strip_query_params( $file_name ) {
		$parsed_url = parse_url( $file_name );
		$path       = isset( $parsed_url['path'] ) ? $parsed_url['path'] : $file_name;
		return $path;
	}

	/**
	 * Serves the requested media file to the HTTP client.
	 *
	 * This method calls the {@see llms_exit()} function and does not return.
	 *
	 * Hooked to the {@see 'init'} filter by {@see LLMS_Media_Protector::register_callbacks()}.
	 *
	 * @since 7.7.0
	 *
	 * @return void
	 * @throws LLMS_Unit_Test_Exception_Exit Thrown during unit testing instead of exiting.
	 */
	public function serve_file() {

		$media_id = llms_filter_input( INPUT_GET, self::URL_PARAMETER_ID, FILTER_SANITIZE_NUMBER_INT );

		// Handle a rewritten URL.
		// e.g. `/wp-content/uploads/llms-uploads/2022/01/image.png` is changed by the LifterLMS mod_rewrite rule
		// into `/index.php?llms-uploads=llms_protected_url/2022/01/image.png`.
		if ( empty( $media_id ) ) {
			$attached_file = llms_filter_input( INPUT_GET, self::URL_PARAMETER_PROTECTED_URL, FILTER_SANITIZE_URL );

			/** Extract the optional size. {@see WP_Image_Editor::get_suffix()} and {@see WP_Image_Editor::generate_filename()} */
			$result = preg_match( '/^(.+?)-(\d+x\d+)(.+)$/', $attached_file, $matches );
			if ( $result ) {
				$attached_file = $matches[1] . $matches[3];
				$size          = explode( 'x', $matches[2] );
				$size          = array_map( 'intval', $size );
			}

			$query    = new WP_Query(
				array(
					'fields'      => 'ids',
					'meta_key'    => '_wp_attached_file',
					'meta_value'  => $attached_file,
					'post_status' => 'any',
					'post_type'   => 'attachment',
				)
			);
			$media_id = reset( $query->posts );
		}

		$media_file = get_post( $media_id );

		// Validate that the attachment post exists.
		if ( is_null( $media_file ) ) {
			header( 'HTTP/1.1 404 Not Found' );
			llms_exit();
		}

		$file_name = $this->get_media_path( $media_id );

		// Optionally, use an alternate image size.
		if ( ! isset( $size ) ) {
			$size = $this->get_size();
		}
		$icon = (bool) ( isset( $_GET[ self::URL_PARAMETER_ICON ] ) ? sanitize_text_field( $_GET[ self::URL_PARAMETER_ICON ] ) : null );
		if ( ! is_null( $size ) || ! is_null( $icon ) ) {
			$image     = wp_get_attachment_image_src( $media_id, $size, $icon );
			$file_name = dirname( $file_name ) . '/' . basename( $image[0] );
		}

		$file_name = $this->strip_query_params( $file_name );

		// Validate that the media file exists.
		if ( false === file_exists( $file_name ) ) {
			header( 'HTTP/1.1 404 Not Found' );
			llms_exit();
		}

		// Is the user authorized to view the file?
		$is_authorized = $this->is_authorized_to_view( get_current_user_id(), $media_id );
		if ( false === $is_authorized ) {
			status_header( 404 );
			nocache_headers();
			die( 'File not found.' );
		}

		// An HTTP client, but not a proxy, is allowed to cache the file, but must check with the server before reuse.
		$entity_tag = '"' . md5_file( $file_name ) . '"';
		if ( false === $this->is_requested_file_modified( $file_name, $entity_tag ) ) {
			header( 'HTTP/1.1 304 Not Modified' );
			llms_exit();
		}
		header( 'Cache-Control: private, no-cache' );
		header( "Etag: $entity_tag" );

		/**
		 * Determine how the media file should be served.
		 *
		 * @since 7.7.0
		 *
		 * @param string    $serve_method  One of the LLMS_Media_Protector::SERVE_X constants, {@see LLMS_Media_Protector::SERVE_SEND_FILE}.
		 * @param int       $media_id      The post ID of the media file.
		 * @param bool|null $is_authorized True if the user is authorized to view the requested media file,
		 *                                 false if not authorized, or null if the media file is not protected.
		 */
		$serve_method = apply_filters( 'llms_media_serve_method', self::SERVE_SEND_FILE, $media_id, $is_authorized );

		// Don't use 'llms-uploads=' rewrite + send_redirect() at the same time. Otherwise there will be an infinite loop
		// of HTTP requests for the file and HTTP responses with a '302 Found' redirect back to the same file.
		if ( self::SERVE_REDIRECT === $serve_method && isset( $attached_file ) ) {
			$serve_method = self::SERVE_READ_FILE;
		}

		switch ( $serve_method ) {
			case self::SERVE_READ_FILE:
				$this->send_headers( $file_name, $media_id );
				$this->read_file( $file_name );
				break;
			case self::SERVE_SEND_FILE:
				$this->send_headers( $file_name, $media_id );
				$this->send_file( $file_name, $media_id );
				break;
			case self::SERVE_REDIRECT:
			default:
				$this->send_redirect( $media_id, $size, $icon );
				break;
		}

		llms_exit();
	}

	/**
	 * Sanitizes and sets the additional upload path that is appended to the base upload path.
	 *
	 * @since 7.7.0
	 *
	 * @param string $additional_upload_path
	 * @return self
	 */
	public function set_additional_upload_path( $additional_upload_path ): self {

		$this->additional_upload_path = $this->format_path( $additional_upload_path );

		return $this;
	}

	/**
	 * Sanitizes and sets the base upload path relative to `WP_CONTENT_DIR . '/uploads'`.
	 *
	 * @since 7.7.0
	 *
	 * @param string $base_upload_path
	 * @return self
	 */
	public function set_base_upload_path( $base_upload_path ): self {

		$this->base_upload_path = $this->format_path( $base_upload_path );

		return $this;
	}

	/**
	 * Removes the authorization filter on the media file.
	 *
	 * @since 7.7.0
	 *
	 * @param int    $media_id             The post ID of the media file.
	 * @param string $authorization_filter The hook name of the filter that authorizes users to view media files.
	 * @return bool True on success, false on failure.
	 */
	public function unprotect( $media_id, $authorization_filter ): bool {

		return delete_post_meta( $media_id, self::AUTHORIZATION_FILTER_KEY, $authorization_filter );
	}

	/**
	 * Filters the 'uploads' directory data.
	 *
	 * @since 7.7.0
	 *
	 * @param array $uploads {
	 *     Array of information about the upload directory.
	 *
	 *     @type string       $path    Base directory and subdirectory or full path to upload directory.
	 *     @type string       $url     Base URL and subdirectory or absolute URL to upload directory.
	 *     @type string       $subdir  Subdirectory if uploads use year/month folders option is on.
	 *     @type string       $basedir Path without subdirectory.
	 *     @type string       $baseurl URL path without subdirectory.
	 *     @type string|false $error   False or error message.
	 * }
	 * @return array
	 */
	public function upload_dir( $uploads ) {
		$uploads['subdir'] = trailingslashit( $this->base_upload_path . $this->additional_upload_path ) . date( 'Y/m' );
		$uploads['path']   = $uploads['basedir'] . $uploads['subdir'];
		$uploads['url']    = $uploads['baseurl'] . $uploads['subdir'];

		return $uploads;
	}

	/**
	 * Add authorization meta to the post.
	 *
	 * @param $post_id
	 *
	 * @return void
	 */
	private function add_authorization_meta_to_media_post( $post_id ): void {
		if ( ! is_numeric( $post_id ) ) {
			return;
		}

		update_post_meta( $post_id, self::AUTHORIZATION_FILTER_KEY, 'llms_attachment_is_access_allowed' );
	}
}
