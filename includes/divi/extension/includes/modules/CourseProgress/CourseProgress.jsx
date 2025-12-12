import React, { Component, Fragment } from "react";
import "./style.css";

class CourseProgress extends Component {
	static slug = "lifterlms_divi_course_progress";

	render() {
		const preview_html = this.props.__preview_html;
		return (
			<div dangerouslySetInnerHTML={{ __html: preview_html }} />
		);
	}
}

export default CourseProgress;
