const { existsSync, readFileSync } = require( 'fs' );
const { basename, join } = require( 'path' );

/**
 * Retrieve the package's "slug".
 *
 * Matches the directory name when that name is also the plugin file (`lifterlms` → `lifterlms.php`).
 * If the checkout directory differs, uses the `name` from package.json when that file exists.
 * The private security mirror is checked out as `lifterlms-security` but the plugin file is still `lifterlms.php`.
 *
 * @since 0.0.1
 *
 * @return {string} The project's slug.
 */
module.exports = () => {
	const directory = basename( process.cwd() );

	if ( existsSync( join( process.cwd(), `${ directory }.php` ) ) ) {
		return directory;
	}

	let packageName = '';
	try {
		packageName = JSON.parse( readFileSync( join( process.cwd(), 'package.json' ), 'utf8' ) ).name || '';
	} catch ( err ) {
		packageName = '';
	}

	if ( packageName && existsSync( join( process.cwd(), `${ packageName }.php` ) ) ) {
		return packageName;
	}

	return directory;
};
