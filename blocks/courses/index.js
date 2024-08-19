(()=>{"use strict";var e={n:l=>{var t=l&&l.__esModule?()=>l.default:()=>l;return e.d(t,{a:t}),t},d:(l,t)=>{for(var r in t)e.o(t,r)&&!e.o(l,r)&&Object.defineProperty(l,r,{enumerable:!0,get:t[r]})},o:(e,l)=>Object.prototype.hasOwnProperty.call(e,l)};const l=window.wp.element,t=window.wp.blocks,r=window.wp.components,s=window.wp.blockEditor,n=window.wp.i18n,o=window.wp.data,a=window.wp.serverSideRender;var i=e.n(a);const c=["course","lesson","llms_quiz"],u=function(e){let l=arguments.length>1&&void 0!==arguments[1]?arguments[1]:"name";const t=null==e?void 0:e.replace("llms_",""),r=t.charAt(0).toUpperCase()+t.slice(1);return"name"===l?t:r},d=JSON.parse('{"$schema":"https://schemas.wp.org/trunk/block.json","apiVersion":2,"name":"llms/courses","title":"Courses","category":"llms-blocks","description":"Displays a loop of LifterLMS Course \\"Tiles\\" as displayed on the default \\"Courses\\" page.","textdomain":"lifterlms","attributes":{"category":{"type":"string"},"hidden":{"type":"boolean","default":true},"id":{"type":"string"},"mine":{"type":"string"},"order":{"type":"string","default":"ASC"},"orderby":{"type":"string","default":"title"},"posts_per_page":{"type":"integer","default":-1},"llms_visibility":{"type":"string"},"llms_visibility_in":{"type":"string"},"llms_visibility_posts":{"type":"string"}},"supports":{"align":["wide","full"]},"editorScript":"file:./index.js"}'),m=window.wp.primitives;(0,t.registerBlockType)(d,{icon:()=>(0,l.createElement)(m.SVG,{className:"llms-block-icon",xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 640 512"},(0,l.createElement)(m.Path,{d:"M320 32c-8.1 0-16.1 1.4-23.7 4.1L15.8 137.4C6.3 140.9 0 149.9 0 160s6.3 19.1 15.8 22.6l57.9 20.9C57.3 229.3 48 259.8 48 291.9v28.1c0 28.4-10.8 57.7-22.3 80.8c-6.5 13-13.9 25.8-22.5 37.6C0 442.7-.9 448.3 .9 453.4s6 8.9 11.2 10.2l64 16c4.2 1.1 8.7 .3 12.4-2s6.3-6.1 7.1-10.4c8.6-42.8 4.3-81.2-2.1-108.7C90.3 344.3 86 329.8 80 316.5V291.9c0-30.2 10.2-58.7 27.9-81.5c12.9-15.5 29.6-28 49.2-35.7l157-61.7c8.2-3.2 17.5 .8 20.7 9s-.8 17.5-9 20.7l-157 61.7c-12.4 4.9-23.3 12.4-32.2 21.6l159.6 57.6c7.6 2.7 15.6 4.1 23.7 4.1s16.1-1.4 23.7-4.1L624.2 182.6c9.5-3.4 15.8-12.5 15.8-22.6s-6.3-19.1-15.8-22.6L343.7 36.1C336.1 33.4 328.1 32 320 32zM128 408c0 35.3 86 72 192 72s192-36.7 192-72L496.7 262.6 354.5 314c-11.1 4-22.8 6-34.5 6s-23.5-2-34.5-6L143.3 262.6 128 408z"})),edit:e=>{const{attributes:t,setAttributes:a}=e,m=(0,s.useBlockProps)(),[p,_]=(0,l.useState)([]),{categories:f}=(0,o.useSelect)((e=>{var l;return{categories:null===(l=e("core"))||void 0===l?void 0:l.getEntityRecords("taxonomy","course_cat")}}),[]),h=null==f?void 0:f.map((e=>({value:e.slug,label:e.name})));null==h||h.unshift({value:"",label:(0,n.__)("- All -","lifterlms")});const b=function(){let e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:"course";const{posts:l,currentPostType:t}=(0,o.useSelect)((l=>{var t;return{posts:l("core").getEntityRecords("postType",e),currentPostType:null===(t=l("core/editor"))||void 0===t?void 0:t.getCurrentPostType()}}),[]),r=(u(e),[]);return c.includes(t)||r.push({label:(0,n.__)("Select course","lifterlms"),value:0}),null!=l&&l.length&&l.forEach((e=>{r.push({label:e.title.rendered+" (ID: "+e.id+")",value:e.id})})),c.includes(t)&&r.unshift({label:(0,n.sprintf)(
// Translators: %s = Post type name.
(0,n.__)("Inherit from current %s","lifterlms"),u(t)),value:0}),null!=r&&r.length||r.push({label:(0,n.__)("Loading","lifterlms"),value:0}),r}(),g={};null==b||b.forEach((e=>{let{value:l,label:t}=e;g[l]=t}));const y=(0,l.useMemo)((()=>(0,l.createElement)(i(),{block:d.name,attributes:t,LoadingResponsePlaceholder:()=>(0,l.createElement)(r.Spinner,null),ErrorResponsePlaceholder:()=>(0,l.createElement)("p",{className:"llms-block-error"},(0,n.__)("Error loading content. Please check block settings are valid. This block will not be displayed.","lifterlms")),EmptyResponsePlaceholder:()=>(0,l.createElement)("p",{className:"llms-block-empty"},(0,n.__)("No courses found matching your selection. This block will not be displayed.","lifterlms"))})),[t]);return(0,l.createElement)(l.Fragment,null,(0,l.createElement)(s.InspectorControls,null,(0,l.createElement)(r.PanelBody,{title:(0,n.__)("Courses Settings","lifterlms")},(null==h?void 0:h.length)>0&&(0,l.createElement)(r.PanelRow,null,(0,l.createElement)(r.SelectControl,{label:(0,n.__)("Category","lifterlms"),help:(0,n.__)("Display courses from a specific Course Category only.","lifterlms"),value:null==t?void 0:t.category,options:h,onChange:e=>a({category:e})})),(0,l.createElement)(r.PanelRow,null,(0,l.createElement)(r.ToggleControl,{label:(0,n.__)("Show hidden courses?","lifterlms"),checked:t.hidden,onChange:e=>a({hidden:e}),help:(0,n.__)('Whether or not courses with a "hidden" visibility should be included. Defaults to "yes" (hidden courses displayed). Switch to "no" to exclude hidden courses.',"lifterlms")})),(0,l.createElement)(r.PanelRow,null,(0,l.createElement)(r.BaseControl,{help:(0,n.__)("Display only specific course(s). You can select multiple courses.","lifterlms")},(0,l.createElement)(r.FormTokenField,{label:(0,n.__)("Courses","lifterlms"),placeholder:(0,n.__)("Search available courses","lifterlms"),suggestions:Object.values(g),value:p,onChange:e=>{_(e),a({id:e.map((e=>Object.keys(g).find((l=>g[l]===e)))).join(",")})},__experimentalShowHowTo:!1}))),(0,l.createElement)(r.PanelRow,null,(0,l.createElement)(r.SelectControl,{label:(0,n.__)("Show only my courses","lifterlms"),options:[{value:"no",label:(0,n.__)("No","lifterlms")},{value:"any",label:(0,n.__)("Any","lifterlms")},{value:"enrolled",label:(0,n.__)("Enrolled","lifterlms")},{value:"expired",label:(0,n.__)("Expired","lifterlms")},{value:"cancelled",label:(0,n.__)("Cancelled","lifterlms")}],checked:t.mine,onChange:e=>a({mine:e}),help:(0,n.__)('Show only courses the current student is enrolled in. By default ("no") shows courses regardless of enrollment.',"lifterlms")})),(0,l.createElement)(r.PanelRow,null,(0,l.createElement)(r.SelectControl,{label:(0,n.__)("Order","lifterlms"),value:t.order,options:[{value:"ASC",label:(0,n.__)("Ascending","lifterlms")},{value:"DESC",label:(0,n.__)("Descending","lifterlms")}],onChange:e=>a({order:e}),help:(0,n.__)("Display courses in ascending or descending order.","lifterlms")})),(0,l.createElement)(r.PanelRow,null,(0,l.createElement)(r.SelectControl,{label:(0,n.__)("Order by","lifterlms"),value:t.orderby,options:[{value:"id",label:(0,n.__)("ID","lifterlms")},{value:"author",label:(0,n.__)("Author","lifterlms")},{value:"title",label:(0,n.__)("Title","lifterlms")},{value:"name",label:(0,n.__)("Name","lifterlms")},{value:"date",label:(0,n.__)("Date","lifterlms")},{value:"modified",label:(0,n.__)("Date modified","lifterlms")},{value:"rand",label:(0,n.__)("Random","lifterlms")},{value:"menu_order",label:(0,n.__)("Menu Order","lifterlms")}],onChange:e=>a({orderby:e}),help:(0,n.__)("Determines which field is used to order courses in the courses list.","lifterlms")})),(0,l.createElement)(r.PanelRow,null,(0,l.createElement)(r.__experimentalNumberControl,{label:(0,n.__)("Per Page","lifterlms"),value:t.posts_per_page,min:-1,max:100,onChange:e=>a({posts_per_page:null!=e?e:-1}),help:(0,n.__)(" Determines the number of results to display. Default returns all available courses.","lifterlms")})))),(0,l.createElement)("div",m,(0,l.createElement)(r.Disabled,null,y)))}})})();