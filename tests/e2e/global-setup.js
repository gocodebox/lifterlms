/**
 * Playwright Global Setup
 *
 * Bootstraps the WordPress test environment with required users, posts, and settings.
 * Replaces the old tests/bin/setup-e2e.sh used with the Puppeteer/Docker stack.
 *
 * @since [version]
 */

import { execSync } from 'node:child_process';
import { request } from '@playwright/test';
import { RequestUtils } from '@wordpress/e2e-test-utils-playwright';

function wpEnvRun( command ) {
	execSync( `npx wp-env run tests-cli -- ${ command }`, {
		stdio: 'pipe',
		encoding: 'utf-8',
	} );
}

async function globalSetup() {
	// 1. Activate LifterLMS plugin.
	wpEnvRun( 'wp plugin activate lifterlms' );

	// 2. Bootstrap user accounts.
	wpEnvRun( 'wp user meta update 1 first_name Chad' );
	wpEnvRun( 'wp user meta update 1 last_name Feldheimer' );

	const users = [
		{ login: 'voucher', email: 'voucher@email.tld', role: 'student', pass: 'password' },
		{ login: 'validcreds', email: 'validcreds@email.tld', role: 'student', pass: 'password' },
		{ login: 'restrictionstester', email: 'restrictions@email.tld', role: 'student', pass: 'password' },
		{ login: 'hasacert', email: 'hasacert@email.tld', role: 'student', pass: 'password' },
	];

	for ( const user of users ) {
		try {
			wpEnvRun(
				`wp user create ${ user.login } ${ user.email } --role=${ user.role } --user_pass=${ user.pass }`
			);
		} catch {
			// User may already exist from a previous run.
		}
	}

	// 3. Set options.
	wpEnvRun( 'wp option update can_compress_scripts 1' );

	// 4. Bootstrap posts.
	try {
		wpEnvRun(
			'wp post create --post_type=page --post_title="Integrity-Test" --post_status=publish'
		);
	} catch {
		// Post may already exist.
	}

	// 5. Set up admin authentication state for Playwright.
	const requestContext = await request.newContext( {
		baseURL: process.env.WP_BASE_URL || 'http://localhost:8889',
	} );

	const requestUtils = new RequestUtils( requestContext, {
		storageStatePath:
			process.env.STORAGE_STATE_PATH ||
			'artifacts/storage-states/admin.json',
		user: {
			username: 'admin',
			password: 'password',
		},
	} );

	await requestUtils.setupRest();
	await requestContext.dispose();
}

export default globalSetup;
