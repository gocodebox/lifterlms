// Deps.
const
	execSync = require( './exec-sync' ),
	getProjectSlug = require( './get-project-slug' );

/**
 * Commit and push a specified zip file to a git branch.
 *
 * This is used, primarily, to publish the distribution archive of a project
 * to the "release" branch which is used to create and publish installable releases.
 *
 * @since 0.0.1
 * @since 0.0.2 OSX compatibility: don't use `xargs -d`.
 *
 * @param {string}  distFile Distribution file used as the source of the commit.
 * @param {string}  branch   Branch to commit and push to.
 * @param {string}  message  Commit message.
 * @param {boolean} silent   Whether or not to output child process stdout.
 * @return {void}
 */
module.exports = ( distFile, branch, message, silent = true ) => {
	const slug = getProjectSlug();

	execSync( 'mkdir -p ./tmp' );

	const
		cwd = process.cwd() + '/tmp/git',
		url = execSync( 'git config --get remote.origin.url', true );

	// Clone the repo into a temp directory.
	execSync( `git clone ${ url } ${ cwd }`, silent );

	// Checkout to the publication branch.
	execSync( `git checkout -b ${ branch }`, silent, { cwd } );

	// Empty everything except the git directory.
	execSync( `mv .git ../ && cd ../ && rm -rf ./git && mkdir git && mv .git ./git && cd git`, silent, { cwd } );

	// Extract the distribution file.
	execSync( `unzip ${ distFile } -d ./tmp/git/`, silent );

	// Move all the contents into the publication branch.
	execSync( `mv ./${ slug }/* ./ && rm -rf ${ slug }/`, silent, { cwd } );

	// Add all files.
	execSync( `git add -A`, silent, { cwd } );

	// Commit.
	execSync( `git commit --allow-empty -m "${ message }"`, silent, { cwd } );

	// Force push.
	execSync( `git push origin ${ branch } -f`, silent, { cwd } );

	// Remove temp repo dir.
	execSync( `rm -rf ./tmp/git`, silent );
};
