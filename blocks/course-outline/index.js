!function(){"use strict";var e={n:function(l){var t=l&&l.__esModule?function(){return l.default}:function(){return l};return e.d(t,{a:t}),t},d:function(l,t){for(var o in t)e.o(t,o)&&!e.o(l,o)&&Object.defineProperty(l,o,{enumerable:!0,get:t[o]})},o:function(e,l){return Object.prototype.hasOwnProperty.call(e,l)}},l=window.wp.element,t=window.wp.blocks,o=window.wp.components,n=window.wp.blockEditor,r=window.wp.i18n,s=window.wp.serverSideRender,i=e.n(s),a=JSON.parse('{"$schema":"https://schemas.wp.org/trunk/block.json","apiVersion":2,"name":"llms/course-outline","title":"Course Outline","icon":"info-outline","category":"lifterlms","description":"Outputs the course outline as displayed by the widget of the same name. Can show full course outline or just the current section outline. Setting the Outline Type to Current Sections refers to the section that contains the next uncompleted lesson for current student. If the student is not enrolled then the first section in the course will be displayed.","textdomain":"lifterlms","attributes":{"collapse":{"type":"boolean","default":false},"course_id":{"type":"integer"},"outline_type":{"type":"string","default":"full"},"toggles":{"type":"boolean","default":false},"llms_visibility":{"type":"string"},"llms_visibility_in":{"type":"string"},"llms_visibility_posts":{"type":"string"}},"supports":{"align":["wide","full"]},"editorScript":"file:./index.js","render":"file:../../templates/blocks/shortcode.php"}'),u=window.wp.data;const c=["course","lesson","llms_quiz"],d=()=>{const e=(0,u.useSelect)((e=>{var l;return null===(l=e("core"))||void 0===l?void 0:l.getEntityRecords("postType","course")}),[]);return(null==e?void 0:e.map((e=>({label:e.title.rendered,value:e.id}))))||[{label:(0,r.__)("No courses found","lifterlms"),value:null}]},p=e=>{var t,n;let{attributes:s,setAttributes:i}=e;const a=d();return(0,l.createElement)(o.PanelRow,null,(0,l.createElement)(o.SelectControl,{label:(0,r.__)("Course","lifterlms"),help:(0,r.__)("The course to display the author for.","lifterlms"),value:null!==(t=s.course_id)&&void 0!==t?t:null==a||null===(n=a[0])||void 0===n?void 0:n.value,options:a,onChange:e=>i({course_id:e})}))};(0,t.registerBlockType)(a,{edit:e=>{const{attributes:t,setAttributes:s}=e,m=(0,n.useBlockProps)(),f=(()=>{const e=(0,u.useSelect)((e=>{var l;return null===(l=e("core/editor"))||void 0===l?void 0:l.getCurrentPostType()}),[]);return c.includes(e)})(),_=d();var h;return t.course_id||f||s({course_id:null==_||null===(h=_[0])||void 0===h?void 0:h.value}),(0,l.createElement)(l.Fragment,null,(0,l.createElement)(n.InspectorControls,null,(0,l.createElement)(o.PanelBody,{title:(0,r.__)("Course Outline Settings","lifterlms")},(0,l.createElement)(o.PanelRow,null,(0,l.createElement)(o.ToggleControl,{label:(0,r.__)("Collapse","lifterlms"),help:(0,r.__)("If true, will make the outline sections collapsible via click events.","lifterlms"),checked:t.collapse,onChange:e=>s({collapse:e})})),t.collapse&&(0,l.createElement)(o.PanelRow,null,(0,l.createElement)(o.ToggleControl,{label:(0,r.__)("Toggles","lifterlms"),help:(0,r.__)("If true, will display “Collapse All” and “Expand All” toggles at the bottom of the outline. Only functions if “collapse” is true.","lifterlms"),checked:t.toggles,onChange:e=>s({toggles:e})})),!f&&(0,l.createElement)(p,e),(0,l.createElement)(o.PanelRow,null,(0,l.createElement)(o.SelectControl,{label:(0,r.__)("Outline Type","lifterlms"),help:(0,r.__)("Select the type of outline to display.","lifterlms"),value:t.outline_type,options:[{label:(0,r.__)("Full","lifterlms"),value:"full",isDefault:!0},{label:(0,r.__)("Current Section","lifterlms"),value:"current_section"}],onChange:e=>s({outline_type:e})})))),(0,l.createElement)("div",m,(0,l.createElement)(o.Disabled,null,(0,l.createElement)(i(),{block:a.name,attributes:t,LoadingResponsePlaceholder:()=>(0,l.createElement)(o.Spinner,null),ErrorResponsePlaceholder:()=>(0,l.createElement)("p",{className:"llms-block-error"},(0,r.__)("Error loading content. Please check block settings are valid. This block will not be displayed.","lifterlms")),EmptyResponsePlaceholder:()=>(0,l.createElement)("p",{className:"llms-block-empty"},(0,r.__)("No outline information available for this course. This block will not be displayed.","lifterlms"))}))))}})}();