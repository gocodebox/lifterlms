/**
 * Instructors added after one is removed are all saved.
 *
 * @since [version]
 */

import { test, expect } from '@wordpress/e2e-test-utils-playwright';

test.describe( 'Admin/InstructorsRepeater', () => {
	const ids = [];
	let courseId;

	test.beforeAll( async ( { requestUtils } ) => {
		const suffix = Date.now();
		for ( const name of [ 'first', 'second', 'third', 'fourth' ] ) {
			const user = await requestUtils.createUser( {
				username: `repeater${ name }${ suffix }`,
				email: `repeater${ name }${ suffix }@example.com`,
				firstName: name,
				lastName: 'Repeater',
				password: 'password',
				roles: [ 'author' ],
			} );
			ids.push( user.id );
		}

		const course = await requestUtils.rest( {
			method: 'POST',
			path: '/llms/v1/courses',
			data: {
				title: `Instructors Repeater ${ suffix }`,
				content:
					'<!-- wp:paragraph --><p>Course content.</p><!-- /wp:paragraph -->',
				status: 'publish',
				instructors: ids.slice( 0, 3 ),
			},
		} );
		courseId = course.id;
	} );

	test.afterAll( async ( { requestUtils } ) => {
		for ( const id of ids ) {
			await requestUtils.rest( {
				method: 'DELETE',
				path: `/wp/v2/users/${ id }`,
				params: { force: true, reassign: 1 },
			} );
		}
	} );

	test( 'removing an instructor and adding one saves every instructor', async ( {
		admin,
		page,
		requestUtils,
	} ) => {
		await admin.editPost( courseId );

		const metabox = page.locator( '#lifterlms-course-options' );
		await metabox.waitFor( { state: 'attached' } );
		await page.evaluate( () => {
			window.wp.data
				.dispatch( 'core/preferences' )
				.set( 'core/edit-post', 'metaBoxesMainIsOpen', true );
		} );
		await metabox
			.locator( '.llms-nav-item', { hasText: 'Instructors' } )
			.locator( 'a, span' )
			.first()
			.click();

		const rows = metabox.locator(
			'._llms_instructors_data .llms-repeater-rows .llms-repeater-row'
		);
		await expect( rows ).toHaveCount( 3 );

		// Remove the middle instructor.
		page.once( 'dialog', ( dialog ) => dialog.accept() );
		await rows.nth( 1 ).locator( '.llms-repeater-remove' ).click();
		await expect( rows ).toHaveCount( 2 );
		await expect(
			page.locator( '._llms_instructors_data.processing' )
		).toHaveCount( 0 );

		// Add the fourth user with the + button.
		await metabox
			.locator( '._llms_instructors_data .llms-repeater-new-btn' )
			.click();
		await expect( rows ).toHaveCount( 3 );
		await page.evaluate( ( id ) => {
			const row = [
				...document.querySelectorAll(
					'._llms_instructors_data .llms-repeater-rows .llms-repeater-row'
				),
			].pop();
			const select = window
				.jQuery( row )
				.find( 'select[name^=_llms_id]' );
			select
				.append(
					new window.Option( 'fourth Repeater', id, true, true )
				)
				.trigger( 'change' );
		}, String( ids[ 3 ] ) );

		const names = await rows
			.locator( 'select[name^=_llms_id]' )
			.evaluateAll( ( els ) => els.map( ( el ) => el.name ) );
		expect( new Set( names ).size ).toBe( names.length );

		await page.evaluate( () =>
			window.wp.data
				.dispatch( 'core/editor' )
				.editPost( { excerpt: 'Instructors changed.' } )
		);
		const topBar = page.getByRole( 'region', { name: 'Editor top bar' } );
		const saveButton = topBar.getByRole( 'button', {
			name: 'Save',
			exact: true,
		} );
		await ( ( await saveButton.isVisible() )
			? saveButton
			: topBar.getByRole( 'button', { name: 'Update', exact: true } )
		).click();
		await page.waitForFunction( () => {
			const { select } = window.wp.data;
			return (
				! select( 'core/editor' ).isSavingPost() &&
				! select( 'core/edit-post' ).isSavingMetaBoxes() &&
				! select( 'core/editor' ).isEditedPostDirty()
			);
		} );

		const course = await requestUtils.rest( {
			path: `/llms/v1/courses/${ courseId }`,
		} );
		expect( course.instructors ).toEqual( [
			ids[ 0 ],
			ids[ 2 ],
			ids[ 3 ],
		] );
	} );
} );
