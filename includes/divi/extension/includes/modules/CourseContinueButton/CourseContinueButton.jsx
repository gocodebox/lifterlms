import React, { Component, Fragment } from "react";
import "./style.css";

class CourseContinueButton extends Component {
	static slug = "lifterlms_divi_course_continue_button";

	render() {
		const preview_html = this.props.__preview_html;
		return (
			<div className="lifterlms-divi-course-continue-button">
				{preview_html ? (
					<div dangerouslySetInnerHTML={{ __html: preview_html }} />
				) : (
					<div>Loading…</div>
				)}
			</div>
		);
	}
}

export default CourseContinueButton;
