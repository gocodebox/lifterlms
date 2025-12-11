import React, { Component, Fragment } from "react";
import "./style.css";

class CourseContinueButton extends Component {
	static slug = "lifterlms_divi_course_continue_button";

	render() {
		const preview_html = this.props.__preview_html;
		return (
			<Fragment dangerouslySetInnerHTML={{ __html: preview_html }} />
		);
	}
}

export default CourseContinueButton;
