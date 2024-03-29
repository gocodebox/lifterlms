// WordPress dependencies.
import { SVG, Path } from '@wordpress/primitives';

// FontAwesome rectangle-wide regular.
const Icon = () => (
	<SVG className="llms-block-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512">
		<Path
			d="M592 416H48c-26.5 0-48-21.5-48-48V144c0-26.5 21.5-48 48-48h544c26.5 0 48 21.5 48 48v224c0 26.5-21.5 48-48 48z"
		/>
	</SVG>
);

export default Icon;
