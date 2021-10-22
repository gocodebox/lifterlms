module.exports = {
	command: 'version',
	description: "Mange the project's version.",
	optionsShared: [
		[ '-i, --increment <level>', 'Increment the version by the specified level. Accepts: major, minor, patch, premajor, preminor, prepatch, or prerelease.', 'patch' ],
		[ '-p, --preid <identifier>', 'Identifier to be used to prefix premajor, preminor, prepatch or prerelease version increments.', 'beta' ],
	],
};
