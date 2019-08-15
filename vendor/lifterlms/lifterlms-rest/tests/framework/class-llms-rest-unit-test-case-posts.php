<?php
/**
 * LifterLMS REST API witServer Unit Test Case Bootstrap
 *
 * @package LifterLMS_REST_API/Tests
 *
 * @since 1.0.0-beta.1
 * @version 1.0.0-beta.1
 */

require_once 'class-llms-rest-unit-test-case-server.php';

class LLMS_REST_Unit_Test_Case_Posts extends LLMS_REST_Unit_Test_Case_Server {

    /**
	 * db post type of the model being tested
	 * @var  string
	 */
    protected $post_type = '';


	/**
	 * Utility to compare an LLMS_Post with an array of data, tipically coming from a rest response.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param LLMS_Post_Model $llms_post       An LLMS_Post_Model.
	 * @param array           $llms_post_data  An array of llms post data.
	 * @param string          $context    Optional. Default 'view'.
	 * @return void
	 */
	protected function llms_posts_fields_match( $llms_post, $llms_post_data, $context = 'view' ) {

		$password_required = post_password_required( $llms_post->get( 'id' ) );

		$expected = array(
			'id'               => $llms_post->get( 'id' ),
			'title'            => array(
				'raw'      => $llms_post->get( 'title', 'raw' ),
				'rendered' => $llms_post->get( 'title' ),
			),
			'status'           => $llms_post->get( 'status' ),
			'content'          => array(
				'raw'      => $llms_post->get( 'content', 'raw' ),
				'rendered' => $password_required ? '' : apply_filters( 'the_content', $llms_post->get( 'content', 'raw' ) ),
			),
			'excerpt'          => array(
				'raw'      => $llms_post->get( 'excerpt', 'raw' ),
				'rendered' => $password_required ? '' : apply_filters( 'the_excerpt', $llms_post->get( 'content', 'raw' ) ),
			),
			'date_created'     => $llms_post->get( 'date', 'Y-m-d H:i:s' ),
			'date_created_gmt' => $llms_post->get( 'date_gmt', 'Y-m-d H:i:s' ),
			'date_updated'     => $llms_post->get( 'modified', 'Y-m-d H:i:s' ),
			'date_updated_gmt' => $llms_post->get( 'modified_gmt', 'Y-m-d H:i:s' ),
		);

		if ( 'edit' !== $context ) {
			unset(
				$expected['content']['raw'],
				$expected['excerpt']['raw'],
				$expected['title']['raw']
			);
		}

		$expected = $this->filter_expected_fields( $expected, $llms_post );

		/**
		 * The rtrim below is not ideal but at the moment we have templates printed after the course summary (e.g. prerequisites) that,
		 * even when printing no data they still print "\n". Let's pretend we're not interested in testing the trailing "\n" presence.
		 */
		foreach ( $expected as $key => $value ) {
			if ( ! isset( $llms_post_data[ $key ] ) ) {
				continue;
			}
			if ( is_array( $value ) ) {
				foreach ( $value as $k => $v ) {
					if ( 'content' === $key ) {
						if ( ! isset( $llms_post_data[ $key ][ $k ] ) ) {
							continue;
						}
						$this->assertEquals( rtrim( $v, "\n" ), rtrim( $llms_post_data[ $key ][ $k ], "\n" ) );
					}
				}
			} else {
				if ( 'content' === $key ) {
					$this->assertEquals( rtrim( $value, "\n" ), rtrim( $llms_post_data[ $key ], "\n" ) );
				}
			}
		}

	}

	protected function filter_expected_fields( $expected, $llms_post ) {
		return $expected;
	}

}
