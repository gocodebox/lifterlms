import React, { Component } from "react";
import "./style.css";

class HelloWorld extends Component {
	static slug = "lifterlms_hello_world";

	render() {
		const preview_html = this.props.__preview_html;
		return (
			<div className="lifterlms-hello-world">
				{preview_html ? (
					<div dangerouslySetInnerHTML={{ __html: preview_html }} />
				) : (
					<div>Loading…</div>
				)}
			</div>
		);
	}
}

export default HelloWorld;
