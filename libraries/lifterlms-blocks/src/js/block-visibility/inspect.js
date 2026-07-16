/**
 * Add visibility attribute inspect and preview interfaces to qualifying blocks
 *
 * @since 1.0.0
 * @version 2.1.1
 */

// WP Deps.
import { __ } from '@wordpress/i18n';
import { createHigherOrderComponent } from '@wordpress/compose';
import { Fragment } from '@wordpress/element';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl } from '@wordpress/components';
import { applyFilters } from '@wordpress/hooks';

// Internal Deps.
import check from './check';
import Preview from './preview';
import SearchPost from '../components/search-post';
import { options as visibilityOptions } from './settings';

/**
 * Block edit inspector controls for visibility settings
 *
 * @since 1.0.0
 * @since 1.1.0 Updated.
 * @since 1.5.1 Exits early for non LifterLMS dynamic blocks.
 * @since 1.6.0 Use `check()` helper to determine if the block supports visibility.
 *              Add "logged in" and "logged out" block visibility options.
 * @since 1.8.0 Fix issue causing visibility attributes to render on blocks that don't support them.
 * @since 2.1.1 Fixed issue causing visibility controls shown for blocks which have no visibility attributes defined.
 */
export default createHigherOrderComponent( ( BlockEdit ) => {
	return ( props ) => {
		// Exit early if the block doesn't support visibility.
		if ( ! check( wp.blocks.getBlockType( props.name ), props.name ) ) {
			return <BlockEdit { ...props } />;
		}

		const {
			attributes: { llms_visibility, llms_visibility_in },
			setAttributes,
		} = props;

		// Visibility is disabled via block properties, or attribute not defined for this block.
		if ( ! llms_visibility || 'off' === llms_visibility ) {
			return <BlockEdit { ...props } />;
		}

		let { llms_visibility_posts } = props.attributes; // eslint-disable-line camelcase

		if ( undefined === llms_visibility_posts ) {
			llms_visibility_posts = '[]';
		}

		llms_visibility_posts = JSON.parse( llms_visibility_posts );

		/**
		 * Retrieve a filtered object of options for the "visibility" select control
		 *
		 * @since 1.0.0
		 *
		 * @return {Object} Options object.
		 */
		const getVisibilityInOptions = () => {
			const currentPost = wp.data
				.select( 'core/editor' )
				.getCurrentPost();

			const options = [];

			if ( -1 !== [ 'course', 'lesson' ].indexOf( currentPost.type ) ) {
				options.push( {
					value: 'this',
					label: __( 'in this course', 'lifterlms' ),
				} );
			}

			options.push( {
				value: 'any_course',
				label: __( 'in any course', 'lifterlms' ),
			} );

			if ( -1 !== [ 'llms_membership' ].indexOf( currentPost.type ) ) {
				options.push( {
					value: 'this',
					label: __( 'in this membership', 'lifterlms' ),
				} );
			}

			options.push(
				{
					value: 'any_membership',
					label: __( 'in any membership', 'lifterlms' ),
				},
				{
					value: 'any',
					label: __( 'in any course or membership', 'lifterlms' ),
				},
				{
					value: 'list_all',
					label: __(
						'in all of the selected courses or memberships',
						'lifterlms'
					),
				},
				{
					value: 'list_any',
					label: __(
						'in any of the selected courses or memberships',
						'lifterlms'
					),
				}
			);

			return applyFilters(
				'llms_blocks_block_visibility_in_options',
				options,
				currentPost
			);
		};

		/**
		 * Retrieve label text for the visibility "in" control.
		 *
		 * @since 1.0.0
		 *
		 * @param {string} visibility Value of the "visibility" control.
		 * @return {string} Translated label.
		 */
		const getVisibilityInLabel = ( visibility ) => {
			if ( 'enrolled' === visibility ) {
				return __( 'Enrolled In', 'lifterlms' );
			}
			return __( 'Not Enrolled In', 'lifterlms' );
		};

		/**
		 * On change event callback for seaching specific posts.
		 *
		 * @since 1.0.0
		 *
		 * @param {Object} post  WP_Post object.
		 * @param {Object} event JS event obj.
		 * @return {void}
		 */
		const onChange = ( post, event ) => {
			if ( 'select-option' === event.action ) {
				addPost( event.option );
			} else if ( 'remove-value' === event.action ) {
				delPost( event.removedValue );
			}
		};

		/**
		 * On Change event callback for visibility select control
		 *
		 * Additionally updates the valued of "visibility in" to be the default value.
		 * Resolves an issue that causes the `in` value to not be stored because no change event is triggerd on the control.
		 *
		 * @since 1.1.0
		 *
		 * @param {string} value Setting value.
		 * @return {void}
		 */
		const onChangeVisibility = ( value ) => {
			setAttributes( {
				llms_visibility: value,
				llms_visibility_in: getVisibilityInOptions()[ 0 ].value,
			} );
		};

		/**
		 * Adds a post to the posts visibility attribute & saves.
		 *
		 * @since 1.0.0
		 *
		 * @param {Object} add WP_Post.
		 * @return {void}
		 */
		const addPost = ( add ) => {
			if (
				! llms_visibility_posts
					.map( ( { id } ) => id )
					.includes( add.id )
			) {
				llms_visibility_posts.push( add );
			}
			savePosts();
		};

		/**
		 * Deletes a post from the posts visibility attribute & saves.
		 *
		 * @since 1.0.0
		 *
		 * @param {Object} del WP_Post.
		 * @return {void}
		 */
		const delPost = ( del ) => {
			llms_visibility_posts.splice(
				llms_visibility_posts.map( ( { id } ) => id ).indexOf( del.id ),
				1
			);
			savePosts();
		};

		/**
		 * Save the current posts attribute state.
		 *
		 * @since 1.0.0
		 * @since 2.0.0 The post object stored in the block attribute was reduced to
		 *               include only the minimum required properties.
		 *
		 * @return {void}
		 */
		const savePosts = () => {
			// Reduce the post objects to only what we require to save.
			const toSave = llms_visibility_posts.map( ( post ) => {
				const { id, title, type } = post,
					stored = { id, title, type };

				/**
				 * Filters the reduced WP_Post object stored as a block attribute
				 *
				 * By default, the `id`, `title`, and `type` properties are stored, should
				 * additional object properties be required they may be added here.
				 *
				 * @since 2.0.0
				 *
				 * @param {Object} stored Reduced WP_Post object.
				 * @param {Object} pot    The original WP_Post object.
				 */
				return applyFilters(
					'llms_block_visibility_stored_post_props',
					stored,
					post
				);
			} );

			setAttributes( {
				llms_visibility_posts: JSON.stringify( toSave ),
			} );
		};

		return (
			<Fragment>
				<Preview { ...props }>
					<BlockEdit { ...props } />
				</Preview>
				<InspectorControls>
					<PanelBody
						title={ __( 'Enrollment Visibility', 'lifterlms' ) }
					>
						<SelectControl
							className="llms-visibility-select"
							label={ __( 'Display to', 'lifterlms' ) }
							value={ llms_visibility }
							onChange={ onChangeVisibility }
							options={ applyFilters(
								'llms_block_visibility_settings_options',
								visibilityOptions
							) }
						/>

						{ -1 ===
							[ 'all', 'logged_in', 'logged_out' ].indexOf(
								llms_visibility
							) && (
							<Fragment>
								<SelectControl
									className="llms-visibility-select--in"
									label={ getVisibilityInLabel(
										llms_visibility
									) }
									value={ llms_visibility_in }
									onChange={ ( value ) =>
										setAttributes( {
											llms_visibility_in: value,
										} )
									}
									options={ getVisibilityInOptions() }
								/>

								{ ( 'list_all' === llms_visibility_in ||
									'list_any' === llms_visibility_in ) && (
									<div>
										<SearchPost
											isMulti
											postType="course"
											label={ __(
												'Courses',
												'lifterlms'
											) }
											placeholder={ __(
												'Search by course title…',
												'lifterlms'
											) }
											onChange={ onChange }
											selected={ llms_visibility_posts.filter(
												( post ) =>
													'course' === post.type
											) }
										/>
										<SearchPost
											isMulti
											postType="llms_membership"
											label={ __(
												'Memberships',
												'lifterlms'
											) }
											placeholder={ __(
												'Search by membership title…',
												'lifterlms'
											) }
											onChange={ onChange }
											selected={ llms_visibility_posts.filter(
												( post ) =>
													'llms_membership' ===
													post.type
											) }
										/>
									</div>
								) }
							</Fragment>
						) }
					</PanelBody>
				</InspectorControls>
			</Fragment>
		);
	};
}, 'withInspectorControl' );
