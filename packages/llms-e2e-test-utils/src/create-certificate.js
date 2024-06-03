// Internal deps.
import { createEngagement } from './create-engagement';
import { createPost } from './create-post';
import { updatePost } from './update-post';

/**
 * Retrieve default block editor content.
 *
 * @since 3.3.0
 *
 * @return {string} Block markup.
 */
function getDefaultContent() {
	return `<!-- wp:llms/certificate-title {"placeholder":"Certificate of Achievement"} -->
<h1 class="has-text-align-center has-default-font-family"></h1>
<!-- /wp:llms/certificate-title -->

<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">Awarded to {first_name} {last_name}</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">On {current_date}</p>
<!-- /wp:paragraph -->`;
}

/**
 * Create and publish a new certificate
 *
 * @since 2.1.2
 * @since 3.3.0 Updated to utilize the block editor in favor of classic.
 *
 * @param {Object} args            Optional creation arguments.
 * @param {string} args.title      Certificate title.
 * @param {string} args.content    HTML content of the certificate.
 * @param {string} args.adminTitle Admin title.
 * @param {string} args.engagement If supplied, also creates an engagement trigger. This should be the ID of a trigger
 * @return {Object} {
 *    Object containing information about the created post(s).
 *    @type {number} certificateId WP Post ID of the created certificate post.
 *    @type {number} engagementId  WP Post ID of the created engagement post.
 * }
 */
export async function createCertificate( {
	title = 'Test Certificate',
	content = null,
	adminTitle = null,
	engagement = '',
} = {} ) {
	let engagementId;

	adminTitle = adminTitle || `${ title } Admin Title`;
	content = content || getDefaultContent( title );

	const certificateId = await createPost( 'llms_certificate', adminTitle, content ),
		certUrl = await page.url();

	// If we programmatically set the post content without physically entering the title we'll end up with an empty title later.
	if ( content.includes( '<!-- wp:llms/certificate-title' ) ) {
		const TITLE_SELECTOR = '.is-root-container.block-editor-block-list__layout .wp-block-llms-certificate-title';
		await page.waitForSelector( TITLE_SELECTOR );
		await page.click( TITLE_SELECTOR );
		await page.keyboard.type( title );
		await page.waitForTimeout( 500 );
		await updatePost();
	}

	if ( engagement ) {
		engagementId = await createEngagement( certificateId, {
			trigger: engagement,
			type: 'certificate',
			title: `Engagement for ${ title } (ID #${ certificateId })`,
		} );

		// Return to the certificate.
		await page.goto( certUrl );
	}

	return {
		certificateId,
		engagementId,
	};
}
