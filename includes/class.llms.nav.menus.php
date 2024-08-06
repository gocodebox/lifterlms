<?php
/**
 * LifterLMS Navigation Menus
 *
 * @package LifterLMS/Classes
 *
 * @since 3.14.7
 * @version 7.3.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LifterLMS Navigation Menus class.
 *
 * @since 3.14.7
 * @since 3.24.0 Unknown.
 * @since 3.37.12 Fixed possible access to undefined index.
 *                Excluded endpoints with an empty url.
 *                Made sure to use strict comparisons.
 */
class LLMS_Nav_Menus {

	/**
	 * Constructor.
	 *
	 * @since 3.14.7
	 * @since 3.22.0 Unknown.
	 * @since 7.1.0 Postpone the LifterLMS menu meta box addition to `admin_head-nav-menus.php`
	 *               rather than `load-nav-menus.php` it's not initially hidden (for new users).
	 * @since 7.2.0 Add navigation link block and enqueue block editor assets.
	 * @since 7.3.0 Change `render_block_llms/navigation-link` to `render_block` for compatibility with LLMS block visibility.
	 *
	 * @return void
	 */
	public function __construct() {

		// Filter menu items on frontend to add real URLs to menu items.
		add_filter( 'wp_nav_menu_objects', array( $this, 'filter_nav_items' ) );

		// Add meta box to the Appearance -> Menus screen on admin panel.
		add_action( 'admin_head-nav-menus.php', array( $this, 'add_metabox' ) );

		// Add LifterLMS menu item type section to customizer.
		add_filter( 'customize_nav_menu_available_item_types', array( $this, 'customize_add_type' ) );

		// Add LifterLMS menu items links to the customizer.
		add_filter( 'customize_nav_menu_available_items', array( $this, 'customize_add_items' ), 10, 4 );

		// Add active classes for nav items for catalog pages.
		add_filter( 'wp_nav_menu_objects', array( $this, 'menu_item_classes' ) );

		// Register block.
		add_action( 'init', array( $this, 'register_block' ) );

		// Render block.
		add_filter( 'render_block', array( $this, 'render_block' ), 10, 2 );

		// Load menu items data in block editor.
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ) );
	}

	/**
	 * Add nav menu metabox.
	 *
	 * @since 3.14.7
	 *
	 * @return void
	 */
	public function add_metabox() {

		add_meta_box( 'llms-nav-menu', __( 'LifterLMS', 'lifterlms' ), array( $this, 'output' ), 'nav-menus', 'side', 'default' );
		add_action( 'admin_print_footer_scripts', array( $this, 'output_scripts' ) );
	}

	/**
	 * Adds LifterLMS menu items to the customizer.
	 *
	 * @since 3.14.7
	 *
	 * @param array   $items  Optional. Menu items. Default empty array.
	 * @param string  $type   Optional. Requested menu item type. Default empty string.
	 * @param string  $object Optional. Requested menu item object. Default empty string.
	 * @param integer $page   Optional. Requested page number. Default `0`.
	 * @return array
	 */
	public function customize_add_items( $items = array(), $type = '', $object = '', $page = 0 ) {

		if ( 'llms_nav' !== $object ) {
			return $items;
		}

		foreach ( $this->get_nav_items() as $id => $data ) {

			$items[] = array(
				'classes'    => 'llms-nav-item-' . $id,
				'id'         => $id,
				'title'      => $data['title'],
				'type_label' => __( 'Custom Link', 'lifterlms' ),
				'url'        => esc_url_raw( $data['url'] ),
			);

		}

		return array_slice( $items, 10 * $page, 10 );
	}

	/**
	 * Add the LifterLMS menu item section to the customizer.
	 *
	 * @since 3.14.7
	 *
	 * @param array $types Existing menu item types.
	 * @return array
	 */
	public function customize_add_type( $types ) {

		$types['llms_nav_menu_items'] = array(
			'title'  => _x( 'LifterLMS', 'customizer menu section title', 'lifterlms' ),
			'type'   => 'llms_nav',
			'object' => 'llms_nav',
		);

		return $types;
	}

	/**
	 * Filters Nav Menu Items to convert #llms- urls into actual URLs.
	 *
	 * Also hides URLs that should only be available to logged-in users.
	 *
	 * @since 3.14.7
	 * @since 3.37.12 Use `in_array` with strict types comparison.
	 * @since 7.2.0 Remove passing item data by reference and improve URL checks.
	 *
	 * @param array $items Nav menu items.
	 * @return array
	 */
	public function filter_nav_items( $items ) {

		$urls = array(
			'#llms-signout',
			'#llms-signin',
		);

		foreach ( $items as $i => $data ) {
			$is_object = is_object( $data ) && property_exists( $data, 'url' );
			$url       = $is_object ? $data->url : $data['url'] ?? '';

			if ( ! in_array( $url, $urls, true ) ) {
				continue;
			}

			$data      = (object) $data;
			$logged_in = is_user_logged_in();

			if ( '#llms-signin' === $url && ! $logged_in ) {
				$data->url = llms_get_page_url( 'myaccount' );
			} elseif ( '#llms-signout' === $url && $logged_in ) {
				$data->url = wp_logout_url( llms_get_page_url( 'myaccount' ) );
			} else {
				unset( $items[ $i ] );
				continue;
			}

			$items[ $i ] = $is_object ? $data : (array) $data;
		}

		return $items;
	}

	/**
	 * Retrieve a filtered array of custom LifterLMS nav menu items.
	 *
	 * @since 3.14.7
	 * @since 3.37.12 Fixed possible access to undefined index.
	 *                Excluded endpoints with an empty url.
	 *
	 * @return array
	 */
	private function get_nav_items() {

		$items = array();

		foreach ( LLMS_Student_Dashboard::get_tabs() as $id => $data ) {

			if ( ! empty( $data['nav_item'] ) ) {

				$url = ! empty( $data['endpoint'] ) ? llms_get_endpoint_url( $data['endpoint'], '', llms_get_page_url( 'myaccount' ) ) : '';

				// No URL no nav item.
				if ( empty( $url ) ) {
					if ( empty( $data['url'] ) ) {
						continue;
					} else {
						$url = $data['url'];
					}
				}

				$title = empty( $data['title'] ) ? '' : $data['title'];

				$items[ $id ] = array(
					'url'   => $url,
					'label' => $title,
					'title' => $title,
				);

			}
		}

		$items['signin']  = array(
			'url'   => '#llms-signin',
			'label' => __( 'Sign In', 'lifterlms' ),
			'title' => __( 'Sign In', 'lifterlms' ),
		);
		$items['signout'] = array(
			'url'   => '#llms-signout',
			'label' => __( 'Sign Out', 'lifterlms' ),
			'title' => __( 'Sign Out', 'lifterlms' ),
		);

		/**
		 * Filters array of custom LifterLMS nav menu items
		 *
		 * @since 3.14.7
		 *
		 * @param array $items Array of custom LifterLMS nav menu items.
		 */
		return apply_filters( 'llms_nav_menu_items', $items );
	}

	/**
	 * Add "active" classes to menu items for LLMS catalog pages.
	 *
	 * @since 3.22.0
	 * @since 3.37.12 Use strict comparisons.
	 *                Cast `page_for_posts` option to int in order to use strict comparisons.
	 * @since 4.12.0 Make sure `is_lifterlms()` exists before calling it.
	 *
	 * @param array $menu_items Menu items.
	 * @return array
	 */
	public function menu_item_classes( $menu_items ) {

		if ( ! function_exists( 'is_lifterlms' ) || ! is_lifterlms() ) {
			return $menu_items;
		}

		$courses_id     = llms_get_page_id( 'courses' );
		$memberships_id = llms_get_page_id( 'memberships' );
		$blog_id        = absint( get_option( 'page_for_posts', 0 ) );

		foreach ( $menu_items as $key => $item ) {

			$classes   = $item->classes;
			$object_id = absint( $item->object_id );

			// Remove active class from blog archive.
			if ( $blog_id === $object_id ) {

				$menu_items[ $key ]->current = false;
				foreach ( array( 'current_page_parent', 'current-menu-item' ) as $class ) {
					if ( in_array( $class, $classes, true ) ) {
						unset( $classes[ array_search( $class, $classes, true ) ] );
					}
				}
			} elseif ( 'page' === $item->object && ( ( is_courses() && $courses_id === $object_id ) || ( is_memberships() && $memberships_id === $object_id ) ) ) {

				$menu_items[ $key ]->current = true;
				$classes[]                   = 'current-menu-item';
				$classes[]                   = 'current_page_item';

				// Set parent links for courses & memberships.
			} elseif ( ( $courses_id === $object_id && ( is_singular( 'course' ) || is_course_taxonomy() ) ) || ( $memberships_id === $object_id && ( is_singular( 'llms_membership' ) || is_membership_taxonomy() ) ) ) {

				$classes[] = 'current_page_parent';

			}

			$menu_items[ $key ]->classes = array_unique( $classes );

		}

		return $menu_items;
	}

	/**
	 * Output the metabox.
	 *
	 * @since 3.14.7
	 * @since 3.24.0 Unknown.
	 *
	 * @return void
	 */
	public function output() {

		?>
		<div id="posttype-llms-nav-items" class="posttypediv">
			<div id="tabs-panel-llms-nav-items" class="tabs-panel tabs-panel-active">
				<ul id="llms-nav-items-checklist" class="categorychecklist form-no-clear">
					<?php
					$i = -1;
					foreach ( $this->get_nav_items() as $key => $data ) :
						?>
						<li>
							<label class="menu-item-title">
								<input type="checkbox" class="menu-item-checkbox" name="menu-item[<?php echo esc_attr( $i ); ?>][menu-item-object-id]" value="<?php echo esc_attr( $i ); ?>" /> <?php echo esc_html( $data['label'] ); ?>
							</label>
							<input type="hidden" class="menu-item-type" name="menu-item[<?php echo esc_attr( $i ); ?>][menu-item-type]" value="custom" />
							<input type="hidden" class="menu-item-title" name="menu-item[<?php echo esc_attr( $i ); ?>][menu-item-title]" value="<?php echo esc_html( $data['title'] ); ?>" />
							<input type="hidden" class="menu-item-url" name="menu-item[<?php echo esc_attr( $i ); ?>][menu-item-url]" value="<?php echo esc_url( $data['url'] ); ?>" />
							<input type="hidden" class="menu-item-classes" name="menu-item[<?php echo esc_attr( $i ); ?>][menu-item-classes]" value="<?php echo esc_attr( 'llms-nav-item-' . $key ); ?>" />
						</li>
						<?php
						--$i;
					endforeach;
					?>
				</ul>
			</div>
			<p class="button-controls">
				<span class="list-controls">
					<a href="<?php echo esc_url( admin_url( 'nav-menus.php?page-tab=all&selectall=1#posttype-llms-nav-items' ) ); ?>" class="select-all"><?php esc_html_e( 'Select all', 'lifterlms' ); ?></a>
				</span>
				<span class="add-to-menu">
					<input type="submit" class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e( 'Add to menu', 'lifterlms' ); ?>" name="add-post-type-menu-item" id="submit-posttype-llms-nav-items">
					<span class="spinner"></span>
				</span>
			</p>
		</div>
		<?php
	}

	/**
	 * Output JS to ensure that users don't edit the #llms-signout URL that's replaced dynamically with an actual signout link.
	 *
	 * @since 3.14.7
	 *
	 * @return void
	 */
	public function output_scripts() {
		?>
		<script type="text/javascript">
		jQuery( '#menu-to-edit' ).on( 'click', 'a.item-edit', function() {
			var $settings = jQuery(this).closest( '.menu-item-bar' ).next( '.menu-item-settings' ),
				$url = $settings.find( '.edit-menu-item-url' );

			if ( 0 === $url.val().indexOf( '#llms-sign' ) ) {
				$url.closest( 'p.field-url' ).css( 'display', 'none' );
			}
		} );
		</script>
		<?php
	}

	/**
	 * Register navigation link block.
	 *
	 * @since 7.2.0
	 *
	 * @return void
	 */
	public function register_block() {
		$block_dir = LLMS_PLUGIN_DIR . 'blocks/navigation-link';

		if ( file_exists( "$block_dir/block.json" ) ) {
			register_block_type( $block_dir );
		}
	}

	/**
	 * Render the navigation link block.
	 *
	 * @since 7.2.0
	 * @since 7.3.0 Add block name check since filter changed.
	 *
	 * @param string $block_content Block content.
	 * @param array  $block Block data.
	 * @return string
	 */
	public function render_block( string $block_content, array $block ): string {

		if ( 'llms/navigation-link' !== $block['blockName'] ) {
			return $block_content;
		}

		$items = $this->filter_nav_items( $this->get_nav_items() );
		$page  = $block['attrs']['page'] ?? 'dashboard';

		if ( ! $page ) {
			return '';
		}

		$url = $items[ $page ]['url'] ?? '';

		// Support conditional URLs, e.g. when user logged in or not.
		if ( ! $url ) {
			return '';
		}

		$label = $block['attrs']['label'] ?? $items[ $page ]['label'] ?? '';

		$html  = '<li class="wp-block-navigation-item">';
		$html .= '<a href="' . esc_url( $url ) . '" class="wp-block-navigation-item__content">';
		$html .= '<span class="wp-block-navigation-item__label">';
		$html .= esc_html( $label );
		$html .= '</span></a></li>';

		return $html;
	}

	/**
	 * Add LifterLMS nav menu item data to block editor.
	 *
	 * @since 7.2.0
	 *
	 * @return void
	 */
	public function enqueue_block_editor_assets() {
		$links = array();

		foreach ( $this->get_nav_items() as $key => $data ) {
			$links[ $key ] = $data['label'];
		}

		wp_localize_script(
			'llms-navigation-link-editor-script',
			'llmsNavMenuItems',
			$links
		);
	}
}

return new LLMS_Nav_Menus();
