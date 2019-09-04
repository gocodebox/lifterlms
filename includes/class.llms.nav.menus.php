<?php
defined( 'ABSPATH' ) || exit;

/**
 * LifterLMS Navigation Menus
 *
 * @since    3.14.7
 * @version  3.24.0
 */
class LLMS_Nav_Menus {

	/**
	 * Constructor
	 *
	 * @since    3.14.7
	 * @version  3.22.0
	 */
	public function __construct() {

		// filter menu items on frontend to add real URLs to menu items
		add_filter( 'wp_nav_menu_objects', array( $this, 'filter_nav_items' ) );

		// add meta box to the Appearance -> Menus screen on admin panel
		add_action( 'load-nav-menus.php', array( $this, 'add_metabox' ) );

		// add LifterLMS menu item type section to customizer
		add_filter( 'customize_nav_menu_available_item_types', array( $this, 'customize_add_type' ) );

		// add LifterLMS menu items links to the customizer
		add_filter( 'customize_nav_menu_available_items', array( $this, 'customize_add_items' ), 10, 4 );

		// add active classes for nav items for catalog pages
		add_filter( 'wp_nav_menu_objects', array( $this, 'menu_item_classes' ) );

	}

	/**
	 * Add nav menu metabox
	 *
	 * @return   void
	 * @since    3.14.7
	 * @version  3.14.7
	 */
	public function add_metabox() {

		add_meta_box( 'llms-nav-menu', __( 'LifterLMS', 'lifterlms' ), array( $this, 'output' ), 'nav-menus', 'side', 'default' );
		add_action( 'admin_print_footer_scripts', array( $this, 'output_scripts' ) );

	}

	/**
	 * Adds LifterLMS menu items to the customizer
	 *
	 * @param    array   $items   menu items
	 * @param    string  $type    requested menu item type
	 * @param    string  $object  requested menu item object
	 * @param    integer $page    requested page number
	 * @return   array
	 * @since    3.14.7
	 * @version  3.14.7
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
	 * Add the LifterLMS menu item section to the customizer
	 *
	 * @param    array $types  existing menu item types
	 * @return   array
	 * @since    3.14.7
	 * @version  3.14.7
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
	 * Filters Nav Menu Items to convert #llms- urls into actual URLs
	 * Also hides URLs that should only be available to logged in users
	 *
	 * @param    array $items  nav menu items
	 * @return   array
	 * @since    3.14.7
	 * @version  3.14.7
	 */
	public function filter_nav_items( $items ) {

		$urls = array(
			'#llms-signout',
			'#llms-signin',
		);

		foreach ( $items as $i => &$data ) {

			if ( in_array( $data->url, $urls ) ) {

				if ( '#llms-signin' === $data->url ) {
					if ( is_user_logged_in() ) {
						unset( $items[ $i ] );
					} else {
						$data->url = llms_get_page_url( 'myaccount' );
					}
				} elseif ( '#llms-signout' === $data->url ) {
					if ( is_user_logged_in() ) {
						$data->url = wp_logout_url( llms_get_page_url( 'myaccount' ) );
					} else {
						unset( $items[ $i ] );
					}
				}
			}
		}

		return $items;
	}

	/**
	 * Retrieve a filtered array of custom LifterLMS nav menu items
	 *
	 * @return   array
	 * @since    3.14.7
	 * @version  3.14.7
	 */
	private function get_nav_items() {

		$items = array();

		foreach ( LLMS_Student_Dashboard::get_tabs() as $id => $data ) {

			if ( ! empty( $data['nav_item'] ) ) {

				$items[ $id ] = array(
					'url'   => $data['endpoint'] ? llms_get_endpoint_url( $data['endpoint'], '', llms_get_page_url( 'myaccount' ) ) : $data['url'],
					'label' => $data['title'],
					'title' => $data['title'],
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

		return apply_filters( 'llms_nav_menu_items', $items );

	}

	/**
	 * Add "active" classes to menu items for LLMS catalog pages
	 *
	 * @param    array $menu_items  menu items
	 * @return   array
	 * @since    3.22.0
	 * @version  3.22.0
	 */
	public function menu_item_classes( $menu_items ) {

		if ( ! is_lifterlms() ) {
			return $menu_items;
		}

		$courses_id     = llms_get_page_id( 'courses' );
		$memberships_id = llms_get_page_id( 'memberships' );
		$blog_id        = get_option( 'page_for_posts' );

		foreach ( $menu_items as $key => $item ) {

			$classes = $item->classes;

			// remove active class from blog archive
			if ( $blog_id == $item->object_id ) {

				$menu_items[ $key ]->current = false;
				foreach ( array( 'current_page_parent', 'current-menu-item' ) as $class ) {
					if ( in_array( $class, $classes ) ) {
						unset( $classes[ array_search( $class, $classes ) ] );
					}
				}
			} elseif ( 'page' === $item->object && ( ( is_courses() && $courses_id == $item->object_id ) || ( is_memberships() && $memberships_id == $item->object_id ) ) ) {

				$menu_items[ $key ]->current = true;
				$classes[]                   = 'current-menu-item';
				$classes[]                   = 'current_page_item';

				// set parent links for courses & memberships
			} elseif ( ( $courses_id == $item->object_id && ( is_singular( 'course' ) || is_course_taxonomy() ) ) || ( $memberships_id == $item->object_id && ( is_singular( 'llms_membership' ) || is_membership_taxonomy() ) ) ) {

				$classes[] = 'current_page_parent';

			}

			$menu_items[ $key ]->classes = array_unique( $classes );

		}

		return $menu_items;
	}

	/**
	 * Output the metabox
	 *
	 * @return   void
	 * @since    3.14.7
	 * @version  3.24.0
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
						$i--;
					endforeach;
					?>
				</ul>
			</div>
			<p class="button-controls">
				<span class="list-controls">
					<a href="<?php echo admin_url( 'nav-menus.php?page-tab=all&selectall=1#posttype-llms-nav-items' ); ?>" class="select-all"><?php _e( 'Select all', 'lifterlms' ); ?></a>
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
	 * Output JS to ensure that users don't edit the #llms-signout URL that's replaced dynamically with an actual signout link
	 *
	 * @return   void
	 * @since    3.14.7
	 * @version  3.14.7
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

}

return new LLMS_Nav_Menus();
