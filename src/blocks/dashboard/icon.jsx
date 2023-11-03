// WordPress dependencies.
import { Path, SVG } from '@wordpress/primitives';

// FontAwesome table-columns solid.
const Icon = () => (
	<SVG className="llms-block-icon" xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512">
		<Path
			d="M0 96C0 60.7 28.7 32 64 32H448c35.3 0 64 28.7 64 64V416c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V96zm64 64V416H224V160H64zm384 0H288V416H448V160z" />
	</SVG>
);

export default Icon;
