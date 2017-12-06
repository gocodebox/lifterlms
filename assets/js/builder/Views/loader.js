define( [
		'Views/Course',
		'Views/Lesson',
		'Views/LessonList',
		'Views/LessonSearchPopover',
		'Views/Main',
		'Views/Tools',
		'Views/Tutorial',
		'Views/Section',
		'Views/SectionList',
	], function(
		Course,
		Lesson,
		LessonList,
		LessonSearchPopover,
		Main,
		Tools,
		Tutorial,
		Section,
		SectionList
	) {

	return {
		Course: Course,
		Lesson: Lesson,
		LessonList: LessonList,
		LessonSearchPopover: LessonSearchPopover,
		Main: Main,
		Tools: Tools,
		Tutorial: Tutorial,
		Section: Section,
		SectionList: SectionList,
	};

} );
