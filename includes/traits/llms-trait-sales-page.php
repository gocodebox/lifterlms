<?php
/**
 * LifterLMS Sales Page trait
 *
 * @package LifterLMS/Traits
 *
 * @since 5.3.0
 * @version 5.3.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LifterLMS Sales Page trait.
 *
 * **This trait should only be used by classes that extend from the {@see LLMS_Post_Model} class.**
 * **Classes that use this trait must call {@see LLMS_Trait_Sales_Page::construct_sales_page()} in their constructor.**
 *
 * @since 5.3.0
 *
 * @property int    $sales_page_content_page_id WP Post ID of the WP page to redirect to when $sales_page_content_type is 'page'.
 * @property string $sales_page_content_type    Sales page behavior [none,content,page,url].
 * @property string $sales_page_content_url     Redirect URL for a sales page, when $sales_page_content_type is 'url'.
 */
trait LLMS_Trait_Sales_Page {
	/**
	 * @inheritdoc
	 */
	abstract protected function add_properties( $props = array() );

	/**
	 * Setup properties used by this trait.
	 *
	 * **Must be called by the constructor of the class that uses this trait.**
	 *
	 * @since 5.3.0
	 */
	protected function construct_sales_page() {

		$this->add_properties(
			array(
				'sales_page_content_page_id' => 'absint',
				'sales_page_content_type'    => 'string',
				'sales_page_content_url'     => 'string',
			)
		);
	}

	/**
	 * @inheritdoc
	 */
	abstract public function get( $key, $raw = false );

	/**
	 * Get the URL to a WP page or custom URL when sales page redirection is enabled.
	 *
	 * **The class that uses this trait must have the {@see LLMS_Post_Model::$model_post_type} property.**
	 *
	 * @since 3.20.0
	 * @since 5.3.0 Check for an empty  URL or ID.
	 *              Refactored from `LLMS_Course` and `LLMS_Membership`.
	 *
	 * @return string
	 */
	public function get_sales_page_url() {

		$type = $this->get( 'sales_page_content_type' );
		switch ( $type ) {
			case 'page':
				$url = get_permalink( $this->get( 'sales_page_content_page_id' ) );
				break;
			case 'url':
				$url = $this->get( 'sales_page_content_url' );
				break;
			default:
				$url = get_permalink( $this->get( 'id' ) );
		}

		/**
		 * Filters the model's sales page URL
		 *
		 * The dynamic portion of the hook name, $this->model_post_type,
		 * refers to the model's post type, e.g. 'course' or 'membership'.
		 *
		 * @since Unknown
		 *
		 * @param string          $url    Sales page URL.
		 * @param LLMS_Post_Model $object The LLMS_Course or LLMS_Membership object.
		 * @param string          $type   The model's $sales_page_content_type property.
		 */
		$url = apply_filters( "llms_{$this->model_post_type}_get_sales_page_url", $url, $this, $type );

		return $url;
	}

	/**
	 * Determine if sales page redirection is enabled.
	 *
	 * **The class that uses this trait must have the {@see LLMS_Post_Model::$model_post_type} property.**
	 *
	 * @since 5.3.0 Refactored from `LLMS_Course` and `LLMS_Membership`.
	 *
	 * @return boolean
	 */
	public function has_sales_page_redirect() {

		$type = $this->get( 'sales_page_content_type' );
		switch ( $type ) {
			case 'page':
				$has_redirect = (bool) $this->get( 'sales_page_content_page_id' );
				break;
			case 'url':
				$has_redirect = (bool) $this->get( 'sales_page_content_url' );
				break;
			default:
				$has_redirect = false;
		}

		/**
		 * Filters whether or not the model has a sales page redirect.
		 *
		 * The dynamic portion of the hook name, $this->model_post_type,
		 * refers to the model's post type, e.g. 'course' or 'membership'.
		 *
		 * @since Unknown
		 *
		 * @param boolean         $has_redirect Whether or not the model has a sales page redirect.
		 * @param LLMS_Post_Model $object       The LLMS_Course or LLMS_Membership object.
		 * @param string          $type         The model's $sales_page_content_type property.
		 */
		$has_redirect = apply_filters(
			"llms_{$this->model_post_type}_has_sales_page_redirect",
			$has_redirect,
			$this,
			$type
		);

		return $has_redirect;
	}
}
