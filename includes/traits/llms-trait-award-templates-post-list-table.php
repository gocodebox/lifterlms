<?php
/**
 * LifterLMS Certificate/Achievement Templates Post List Table trait
 *
 * @package LifterLMS/Traits
 *
 * @since 6.0.0
 * @version 6.4.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LifterLMS Certificate/Achievement Templates Post List Table trait.
 *
 * @since 6.0.0
 */
trait LLMS_Trait_Award_Templates_Post_List_Table {

	/**
	 * Add post row actions filter callback.
	 *
	 * @since 6.0.0
	 * @since 6.4.0 Stop using deprecated `FILTER_SANITIZE_STRING`.
	 *
	 * @return void
	 */
	protected function award_template_row_actions() {

		if ( "llms_{$this->engagement_type}" === llms_filter_input( INPUT_GET, 'post_type' ) ) {
			add_filter( 'post_row_actions', array( $this, 'add_post_actions' ), 20, 2 );
		}

	}

	/**
	 * Add post row actions.
	 *
	 * @since 6.0.0
	 *
	 * @param array   $actions Array of post row actions.
	 * @param WP_Post $post    Post object for the row.
	 * @return array
	 */
	public function add_post_actions( $actions, $post ) {

		if ( ! $post || "llms_{$this->engagement_type}" !== $post->post_type ) {
			return $actions;
		}

		$post_type_object = get_post_type_object( $post->post_type );
		if ( ! $post_type_object->show_ui ) {
			return $actions;
		}

		$award_post_type = str_replace( 'llms_', 'llms_my_', $post->post_type );

		$actions['llms-awards-list'] = sprintf(
			'<a href="%1$s">%2$s</a>',
			add_query_arg(
				array(
					LLMS_Admin_Post_Table_Awards::TEMPLATE_FILTER_QUERY_VAR => $post->ID,
					'post_type' => $award_post_type,
				),
				admin_url( 'edit.php' )
			),
			sprintf(
				// Translators: %1$s the awarded post type name label.
				__( 'View %1$s', 'lifterlms' ),
				get_post_type_labels(
					get_post_type_object( $award_post_type )
				)->name
			)
		);

		$sync_action = "sync_awarded_{$this->engagement_type}s";
		$sync_url    = add_query_arg(
			array(
				'action' => $sync_action,
				"_llms_{$this->engagement_type}_sync_actions_nonce" => wp_create_nonce( "llms-{$this->engagement_type}-sync-actions" ),
			),
			get_edit_post_link( $post, 'raw' )
		);

		$text = sprintf(
			/* translators: %1$s: the plural awarded post type name label */
			__( 'Sync %1$s', 'lifterlms' ),
			get_post_type_labels( get_post_type_object( $award_post_type ) )->name
		);

		$actions[ $sync_action ] = '<a href="' . esc_html( $sync_url ) . '">' . $text . '</a>';

		return $actions;

	}

}
