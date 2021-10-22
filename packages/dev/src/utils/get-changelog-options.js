module.exports = () => {
	return {
		significance: {
			major: 'Backwards incompatible or breaking changes',
			minor: 'New features or backwards-compatible deprecations',
			patch: 'Backwards-compatible bug fixes',
		},
		type: {
			added: 'New features',
			changed: 'Updates to existing features',
			fixed: 'Any bug fixes',
			deprecated: 'Features to be removed',
			removed: 'Features that are being removed',
			dev: 'Developer-related notes or changes',
			performance: 'Performance improvements or fixes',
			security: 'Changes related to security vulnerabilities',
		},
	};
};
