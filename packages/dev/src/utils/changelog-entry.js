/**
 * A changelog entry object.
 *
 * @typedef {Object} ChangelogEntry
 * @property {string}   title        Title of the changelog entry. Used as the filename (excluding the extension) for the changelog file.
 * @property {string}   significance Entry significance.
 * @property {string}   type         Entry type.
 * @property {string}   comment      Internal-use comment accompanying the entry.
 * @property {string[]} links        List of GitHub issues linked to the entry.
 * @property {string[]} attributions List of individuals attributed to the entry.
 * @property {string}   entry        The content of the changelog entry.
 */
module.exports = {
	title: '',
	significance: '',
	type: '',
	comment: '',
	links: [],
	attributions: [],
	entry: '',
};
