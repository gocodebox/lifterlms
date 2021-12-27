export function getFonts() {
	return window.llms.certificates.fonts;
}

export function getFont( id ) {
	const fonts = getFonts();
	return fonts[ id ] || null;
}
