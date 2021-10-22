module.exports = {
	command: 'changelog',
	description: "Mange the project's changelog.",
	optionsShared: [
		[ '-d, --dir <directory>', 'Directory where changelog entries are stored.', '.changelogs' ],
	],
	args: [
		[ '<command>', 'The changelog subcommand to execute.' ],
	],
};
