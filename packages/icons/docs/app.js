/**
 * React App that automatically renders all icons in the library into a table.
 *
 * This app is rendered as a static HTML file and generate.js pulls the HTML of the table
 * out of the file and stores it in the README.md file between the appropriate comment
 * tokens.
 */

import { render } from 'react-dom';
import * as Icons from '../src';

const { Icon, ...icons } = Icons;

function App() {
	return (
		<table>
			<thead>
				<tr>
					<th>Icon</th>
					<th>ID</th>
					<th>Usage</th>
				</tr>
			</thead>
			<tbody>
				{ Object.entries( icons ).map( ( [ id, icon ] ) => {
					return (
						<tr key={ icon }>
							<td><Icon icon={ icons[ id ] } size="48" /></td>
							<td>{ id }</td>
							<td><code>{ `<Icon icon={ ${ id } } />` }</code></td>
						</tr>
					);
				} ) }
			</tbody>

		</table>
	);
}
render( <App />, document.getElementById( 'app' ) );
