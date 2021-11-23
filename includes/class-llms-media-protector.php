<?php
/**
 * LLMS_Media_Protector class
 *
 * @package LifterLMS/Classes
 *
 * @since   [version]
 * @version [version]
 */

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
 *     $media = new LLMS_Media_Protector();
 *     $media->set_upload_subdirectory( $media->get_upload_subdirectory() . '/social-learning' );
 *     $post_data['meta_input'] = array( LLMS_Media_Protector::AUTHORIZATION_FILTER_KEY => 'llms_sl_authorize_media_view' );
 *     $id = $media->handle_upload( 'image', 0, $post_data );
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
 * @since [version]
 *
 * @todo Add handling of HTTP range requests. See {@see https://datatracker.ietf.org/doc/html/rfc7233} and
 *       {@see https://developer.mozilla.org/en-US/docs/Web/HTTP/Range_requests}.
 */
class LLMS_Media_Protector {

	/**
	 * The meta key used to specify the filter hook name that authorizes viewing of a media file.
	 *
	 * @TODO Should the key be prefixed with an underscore '_' to denote private?
	 *
	 * @since [version]
	 *
	 * @var string
	 */
	public const AUTHORIZATION_FILTER_KEY = 'llms_media_authorization_filter';

	/**
	 * The name of the query parameter for whether the media image should be treated as an icon.
	 *
	 * @since [version]
	 *
	 * @var string
	 */
	public const QUERY_PARAMETER_ICON = 'llms_media_icon';

	/**
	 * The name of the query parameter for the media post ID.
	 *
	 * @since [version]
	 *
	 * @var string
	 */
	public const QUERY_PARAMETER_ID = 'llms_media_id';

	/**
	 * The name of the query parameter for the requested media image size.
	 *
	 * @since [version]
	 *
	 * @var string
	 */
	public const QUERY_PARAMETER_SIZE = 'llms_media_image_size';

	/**
	 * Serve the media file by reading and outputting it with the readfile() function.
	 *
	 * @since [version]
	 *
	 * @var int
	 */
	public const SERVE_READ_FILE = 1;

	/**
	 * Serve the media file by redirecting the HTTP client with a "Location" header.
	 *
	 * @since [version]
	 *
	 * @var int
	 */
	public const SERVE_REDIRECT = 2;

	/**
	 * Serve the media file by sending an "X-Sendfile" style header and let the HTTP server serve the file.
	 *
	 * @since [version]
	 *
	 * @var int
	 */
	public const SERVE_SEND_FILE = 3;

	/**
	 * The root path component for uploaded LifterLMS files with a leading slash '/' and without a trailing slash.
	 *
	 * @since [version]
	 *
	 * @var string
	 */
	protected $upload_subdirectory = '/llms-uploads';

	/**
	 * Set up this class.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function __construct() {

		add_filter( 'mod_rewrite_rules', array( $this, 'add_mod_xsendfile_directives' ) );

		if ( isset( $_GET[ self::QUERY_PARAMETER_ID ] ) ) {
			add_action( 'init', array( $this, 'serve_file' ), 10 );
		} else {
			add_filter( 'wp_get_attachment_image_src', array( $this, 'authorize_media_image_src' ), 10, 4 );
			add_filter( 'wp_get_attachment_url', array( $this, 'authorize_media_url' ), 10, 2 );
		}
	}

	/**
	 * Adds directives to .htaccess that allows Apache mod_xsendfile to be enabled and detected.
	 *
	 * @since [version]
	 *
	 * @param string $rules mod_rewrite Rewrite rules formatted for .htaccess.
	 * @return string
	 */
	public function add_mod_xsendfile_directives( string $rules ) {

		$directives = <<<'NOWDOC'

# BEGIN LifterLMS mod_xsendfile
<IfModule mod_xsendfile.c>
  <Files *.php>
    XSendFile On
    SetEnv MOD_X_SENDFILE_ENABLED 1
  </Files>
</IfModule>
# END LifterLMS mod_xsendfile


NOWDOC;

		return $directives . $rules;
	}

	/**
	 * Adds query parameters to a protected media URL.
	 *
	 * @since [version]
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
	 * @return array
	 */
	public function authorize_media_image_src( array $image, int $media_id, $size, bool $icon ) {

		// Is the media file protected?
		$authorization_filter = get_post_meta( $media_id, self::AUTHORIZATION_FILTER_KEY, true );
		if ( ! $authorization_filter ) {
			return $image;
		}

		$image[0] = add_query_arg(
			array(
				self::QUERY_PARAMETER_ID   => $media_id,
				self::QUERY_PARAMETER_SIZE => rawurlencode( is_array( $size ) ? json_encode( $size ) : $size ),
				self::QUERY_PARAMETER_ICON => $icon ? 1 : 0,
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
	 * @since [version]
	 *
	 * @param string $url      URL for the given media file.
	 * @param int    $media_id The post ID of the media file.
	 * @return string
	 */
	public function authorize_media_url( string $url, int $media_id ) {

		$authorized_url = wp_cache_get( $media_id, 'llms_authorized_media_url' );
		if ( $authorized_url ) {
			return $authorized_url;
		}

		$is_authorized = $this->is_authorized_to_view( get_current_user_id(), $media_id );
		if ( true === $is_authorized ) {
			$url = add_query_arg(
				array(
					self::QUERY_PARAMETER_ID => $media_id
				),
				trailingslashit( home_url() )
			);
		} elseif ( false === $is_authorized ) {
			// @TODO Finish handling the placeholder.
			$placeholder = $this->get_placeholder();
			$url         = 'placeholder.jpg';
//		} elseif ( is_null( $is_authorized ) ) {
		}

		wp_cache_add( $media_id, $url, 'llms_authorized_media_url' );

		return $url;
	}

	/**
	 * Returns the absolute path to the media file in the upload directory.
	 *
	 * @since [version]
	 *
	 * @param int $media_id The media post ID.
	 * @return string
	 */
	public function get_media_path( int $media_id ) {

		$upload_dir = wp_upload_dir();
		$file_name  = get_post_meta( $media_id, '_wp_attached_file', true );

		return $upload_dir['basedir'] . DIRECTORY_SEPARATOR . $file_name;
	}

	/**
	 * Returns an absolute URL to the media file in the upload directory.
	 *
	 * @since [version]
	 *
	 * @param int $media_id The media post ID.
	 * @return string
	 */
	public function get_media_url( int $media_id ) {

		$upload_dir = wp_upload_dir();
		$file_name  = get_post_meta( $media_id, '_wp_attached_file', true );

		return $upload_dir['baseurl'] . '/' . $file_name;
	}

	/**
	 * Returns a placeholder.
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	protected function get_placeholder() {

		//@TODO Finish writing this method.

		return 'placeholder.jpg';
	}

	/**
	 * Returns the upload subdirectory.
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	public function get_upload_subdirectory() {

		return $this->upload_subdirectory;
	}

	/**
	 * Saves a file submitted from a POST request and creates an attachment post for it.
	 *
	 * To protect the file from unauthorized viewing,
	 * set `$post_data['meta_input'][{@see LLMS_Media_Protector::AUTHORIZATION_FILTER_KEY}]` to the hook name of an
	 * authorization method that is triggered in {@see LLMS_Media_Protector::is_authorized_to_view()}.
	 *
	 * @since [version]
	 *
	 * @param string $file_id   Index of the `$_FILES` array that the file was sent. Required.
	 * @param int    $post_id   The post ID of a post to attach the media item to. Required, but can
	 *                          be set to 0, creating a media item that has no relationship to a post.
	 * @param array  $post_data Optional. Set attachment elements that are sent to {@see wp_insert_post()}.
	 *                          The defaults are set in {@see media_handle_upload()}.
	 * @param array  $overrides Optional. Override the {@see wp_handle_upload()} behavior.
	 * @return int|WP_Error Post ID of the media file or a WP_Error object on failure.
	 */
	public function handle_upload(
		string $file_id,
		int $post_id,
		array $post_data = array(),
		array $overrides = array( 'test_form' => false )
	) {

		add_filter( 'upload_dir', array( $this, 'upload_dir' ), 10, 1 );
		$media_id = media_handle_upload( $file_id, $post_id, $post_data, $overrides );
		remove_filter( 'upload_dir', array( $this, 'upload_dir' ), 10 );

		return $media_id;
	}

	/**
	 * Returns true if the user is authorized to view the requested media file, false if not authorized,
	 * or null if the media file is not protected.
	 *
	 * @since [version]
	 *
	 * @param int $user_id  The user ID.
	 * @param int $media_id The post ID of the media file.
	 * @return bool|null
	 */
	public function is_authorized_to_view( int $user_id, int $media_id ) {

		$authorization_filter = get_post_meta( $media_id, self::AUTHORIZATION_FILTER_KEY, true );
		if ( ! $authorization_filter ) {
			return null;
		}

		/**
		 * Allow the plugin that is protecting the file to authorize access to it.
		 *
		 * @since [version]
		 *
		 * @param bool $is_authorized True if the user is authorized to view the media file, false if not authorized,
		 *                            or null if file is not protected.
		 * @param int  $media_id      The post ID of the media file.
		 * @param int  $user_id       The ID of the user wanting to view the media file.
		 */
		$is_authorized = apply_filters( $authorization_filter, null, $media_id, $user_id );

		return $is_authorized;
	}

	/**
	 * Reads and outputs the file.
	 *
	 * This method sends the entire file and does not handle
	 * {@see https://developer.mozilla.org/en-US/docs/Web/HTTP/Range_requests HTTP range requests}.
	 *
	 * This method calls the {@see https://www.php.net/manual/function.exit.php `exit()`} function and does not return.
	 *
	 * @since [version]
	 *
	 * @param string $file_name The file path and name.
	 * @return void
	 */
	protected function read_file( $file_name ) {

		// @TODO What about the web server time limit?
		ini_set( 'max_execution_time', '0' );

		// Tell the HTTP client that we do not handle HTTP range requests.
		header( 'Accept-Ranges: none' );

		// Turn off all output buffers to avoid running out of memory with large files.
		// @see https://www.php.net/readfile#refsect1-function.readfile-notes
		wp_ob_end_flush_all();

		$result = readfile( $file_name );
		if ( false === $result  ) {
			// Tell the HTTP client that something unspecific went wrong. readfile() outputs warnings to the PHP error log.
			header( 'HTTP/1.1 500 Internal Server Error' );
		}

		exit();
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
	 * This method calls the {@see https://www.php.net/manual/function.exit.php `exit()`} function and does not return.
	 *
	 * @since [version]
	 *
	 * @param string $file_name The file path and name.
	 * @param int    $media_id  The post ID of the media file.
	 * @return void
	 */
	protected function send_file( string $file_name, int $media_id ) {

		$server_software = $_SERVER['SERVER_SOFTWARE'];
		$this->send_headers( $file_name, $media_id );

		if (
			'1' === $_SERVER['MOD_X_SENDFILE_ENABLED'] ||
			( function_exists( 'apache_get_modules' ) && in_array( 'mod_xsendfile', apache_get_modules(), true ) ) ||
			stristr( $server_software, 'cherokee' ) ||
			stristr( $server_software, 'lighttpd' )
		) {
			header( "X-Sendfile: $file_name" );

		} elseif ( stristr( $server_software, 'nginx' ) ) {
			/**
			 * @TODO Test NGINX.
			 * @see https://www.nginx.com/resources/wiki/start/topics/examples/xsendfile/
			 * @see https://woocommerce.com/document/digital-downloadable-product-handling/#nginx-setting
			 */
			// NGINX requires a URI without the server's root path.
			$nginx_file_name = substr( $file_name, strlen( ABSPATH ) - 1 );
			header( "X-Accel-Redirect: $nginx_file_name" );

		} else {
			$this->read_file( $file_name );
		}

		exit();
	}

	/**
	 * Send headers for the download.
	 *
	 * @since [version]
	 *
	 * @param string $file_name The file path and name.
	 * @param int    $media_id  The post ID of the media file.
	 * @return void
	 */
	protected function send_headers( string $file_name, int $media_id ) {

		$file_size = @filesize( $file_name ); // phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged
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
	 * This method calls the {@see https://www.php.net/manual/function.exit.php `exit()`} function and does not return.
	 *
	 * @since [version]
	 *
	 * @param int $media_id The post ID of the media file.
	 * @return void
	 */
	protected function send_redirect( int $media_id ) {

		$url = $this->get_media_url( $media_id );
		header( "Location: $url" );
		exit();
	}

	/**
	 * Serves the requested media file to the HTTP client.
	 *
	 * This method calls the {@see https://www.php.net/manual/function.exit.php `exit()`} function and does not return.
	 *
	 * @todo Maybe respond with 404 Not Found?
	 * @todo Maybe respond with 401 Unauthorized?
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function serve_file() {

		$media_id = $_GET[ self::QUERY_PARAMETER_ID ];
		$media_file = get_post( $media_id );


		// Validate that the attachment post exists.
		if ( is_null( $media_file ) ) {
			return;
		}

		$file_name = $this->get_media_path( $media_id );

		$size = null;
		if ( isset( $_GET[ self::QUERY_PARAMETER_SIZE ] ) ) {
			if ( '[' === $_GET[ self::QUERY_PARAMETER_SIZE ][0] ) {
				$size = json_decode( $_GET[ self::QUERY_PARAMETER_SIZE ] );
			} else {
				$size = $_GET[ self::QUERY_PARAMETER_SIZE ];
			}
		}

		$icon = null;
		if ( isset( $_GET[ self::QUERY_PARAMETER_ICON ] ) ) {
			$icon = (bool) $_GET[ self::QUERY_PARAMETER_ICON ];
		}

		// Optionally, use an alternate image size.
		if ( ! is_null( $size ) || ! is_null( $icon ) ) {
			$image     = wp_get_attachment_image_src( $media_id, $size, $icon );
			$base_name = basename( $image[0] );
			$file_name = dirname( $file_name ) . "/$base_name";
		}

		// Validate that the media file exists.
		if ( false === file_exists( $file_name ) ) {
			return;
		}

		// Is the user authorized to view the file?
		$is_authorized = $this->is_authorized_to_view( get_current_user_id(), $media_id );
		if ( false === $is_authorized ) {
			$content_type = $media_file->post_mime_type;
			if ( 0 === stripos( $content_type, 'image/' ) ) {
				// @todo Find or create an image to denote unauthorized access to an image file.
				$file_name = LLMS_PLUGIN_DIR . 'assets/images/circle-with-a-slash.png';
			} else {
				header( 'HTTP/1.1 403 Forbidden' );
				exit();
			}
		}

		/**
		 * Determine how the media file should be served.
		 *
		 * @since [version]
		 *
		 * @param string $serve_method One of the {@see LLMS_Media_Protector::SERVE_REDIRECT LLMS_Media_Protector::SERVE_X} constants.
		 * @param int    $media_id     The post ID of the media file.
		 */
		$serve_method = apply_filters( 'llms_media_serve_method', self::SERVE_SEND_FILE, $media_id );

		switch ( $serve_method ) {
			case self::SERVE_READ_FILE:
				$this->send_headers( $file_name, $media_id );
				$this->read_file( $file_name );
				break;
			case self::SERVE_SEND_FILE:
				$this->send_file( $file_name, $media_id );
				break;
			case self::SERVE_REDIRECT:
			default:
				$this->send_redirect( $media_id );
				break;
		}
	}

	/**
	 * Sets the upload subdirectory.
	 *
	 * @since [version]
	 *
	 * @param string $upload_subdirectory
	 * @return $this
	 */
	public function set_upload_subdirectory( string $upload_subdirectory ) {

		// Add leading slash.
		if ( strpos( $upload_subdirectory, '/' ) !== 0 ) {
			$upload_subdirectory = '/' . $upload_subdirectory;
		}

		// Strip trailing slash.
		$upload_subdirectory = rtrim( $upload_subdirectory, '/' );

		$this->upload_subdirectory = $upload_subdirectory;

		return $this;
	}

	/**
	 * Removes the authorization filter on the media file.
	 *
	 * @since [version]
	 *
	 * @param int    $media_id             The post ID of the media file.
	 * @param string $authorization_filter The hook name of the filter that authorizes users to view media files.
	 * @return bool True on success, false on failure.
	 */
	public function unprotect( int $media_id, string $authorization_filter ) {

		return delete_post_meta( $media_id, self::AUTHORIZATION_FILTER_KEY, $authorization_filter );
	}

	/**
	 * Filters the 'uploads' directory data.
	 *
	 * @since [version]
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
	public function upload_dir( array $uploads ) {

		$uploads['path'] = $uploads['basedir'] . $this->upload_subdirectory . $uploads['subdir'];
		$uploads['url']  = $uploads['baseurl'] . $this->upload_subdirectory . $uploads['subdir'];

		return $uploads;
	}
}
