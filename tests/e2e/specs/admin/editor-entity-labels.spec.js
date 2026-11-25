/**
 * Block editor controls display decoded titles and names.
 *
 * REST responses return texturized, HTML-encoded post titles (`&#8211;`, `&#038;`),
 * term names and user names (`&amp;`). Labels in editor controls must show the
 * decoded text instead of the entities.
 *
 * @since 11.1.1
 */

import { test, expect } from '@wordpress/e2e-test-utils-playwright';

const suffix = Date.now();
const dashTitle = `en dash - test ${ suffix }`;
const dashLabel = `en dash – test ${ suffix }`;
const ampName = `Tom & Jerry ${ suffix }`;

/**
 * Insert a block, select it and open the block settings sidebar.
 *
 * @param {import('@wordpress/e2e-test-utils-playwright').Admin}  admin      Admin utils.
 * @param {import('@wordpress/e2e-test-utils-playwright').Editor} editor     Editor utils.
 * @param {string}                                                name       Block name.
 * @param {Object}                                                attributes Block attributes.
 * @return {Promise<void>}
 */
async function insertBlock( admin, editor, name, attributes = {} ) {
	await admin.createNewPost();
	await editor.insertBlock( { name, attributes } );
	await editor.openDocumentSettingsSidebar();
	await editor.page.getByRole( 'tab', { name: 'Block' } ).click();
}

test.describe( 'Admin/EditorEntityLabels', () => {
	let courseId, userId;

	test.beforeAll( async ( { requestUtils } ) => {
		const course = await requestUtils.rest( {
			method: 'POST',
			path: '/llms/v1/courses',
			data: {
				title: dashTitle,
				content: '<!-- wp:paragraph --><p>Course content.</p><!-- /wp:paragraph -->',
				status: 'publish',
			},
		} );
		courseId = course.id;

		await requestUtils.rest( {
			method: 'POST',
			path: '/llms/v1/access-plans',
			data: {
				title: ampName,
				post_id: courseId,
				price: 0,
				frequency: 0,
				access_expiration: 'lifetime',
			},
		} );

		for ( const taxonomy of [ 'course_cat', 'membership_cat' ] ) {
			await requestUtils.rest( {
				method: 'POST',
				path: `/wp/v2/${ taxonomy }`,
				data: { name: ampName },
			} );
		}

		const user = await requestUtils.createUser( {
			username: `tomjerry${ suffix }`,
			email: `tomjerry${ suffix }@example.com`,
			firstName: 'Tom',
			lastName: 'Jerry',
			password: 'password',
			roles: [ 'author' ],
		} );
		userId = user.id;
		await requestUtils.rest( {
			method: 'POST',
			path: `/wp/v2/users/${ userId }`,
			data: { name: ampName },
		} );
	} );

	test( 'Access Plan Button plan options', async ( { admin, editor, page } ) => {
		await insertBlock( admin, editor, 'llms/access-plan-button' );

		const combobox = page.getByRole( 'combobox', { name: 'Access Plan' } );
		await combobox.fill( 'Tom' );

		await expect( page.getByRole( 'option', { name: ampName } ) ).toBeVisible();
	} );

	test( 'Courses and Memberships category options', async ( { admin, editor, page } ) => {
		await insertBlock( admin, editor, 'llms/courses' );
		const courseCategory = page.getByRole( 'combobox', { name: 'Category' } );
		await courseCategory.fill( 'Tom' );
		await expect( page.getByRole( 'option', { name: ampName } ) ).toBeVisible();

		await insertBlock( admin, editor, 'llms/memberships' );
		const membershipCategory = page.locator( '.block-editor-block-inspector select' ).first();
		await expect( membershipCategory.locator( 'option', { hasText: ampName } ) ).toHaveText( ampName );
	} );

	test( 'My Achievements user options', async ( { admin, editor, page } ) => {
		await insertBlock( admin, editor, 'llms/my-achievements' );

		const userSelect = page.locator( '.block-editor-block-inspector select' ).first();
		await expect( userSelect.locator( `option[value="${ userId }"]` ) ).toHaveText( ampName );
	} );

	test( 'Block visibility course search results', async ( { admin, editor, page } ) => {
		await insertBlock( admin, editor, 'core/paragraph', {
			content: 'Visibility test',
			llms_visibility: 'enrolled',
			llms_visibility_in: 'list_any',
		} );

		await page.locator( '.llms-search--course .llms-search__control' ).click();
		await page.keyboard.type( String( suffix ) );

		await expect( page.locator( '.llms-search__option' ).filter( { hasText: `ID# ${ courseId }` } ) )
			.toHaveText( `${ dashLabel } (ID# ${ courseId })` );
	} );

	test( 'Awarded certificate student search and selected student', async ( { admin, page } ) => {
		await admin.visitAdminPage( 'post-new.php', 'post_type=llms_my_certificate' );
		await page.locator( '.llms-search-control__control' ).click();
		await page.keyboard.type( 'Tom' );
		await expect( page.locator( '.llms-search-control__option' ).filter( { hasText: `ID# ${ userId }` } ) )
			.toHaveText( `${ ampName } (ID# ${ userId })` );

		await admin.visitAdminPage( 'post-new.php', `post_type=llms_my_certificate&sid=${ userId }` );
		await expect( page.getByRole( 'link', { name: ampName } ) ).toBeVisible();
	} );
} );
