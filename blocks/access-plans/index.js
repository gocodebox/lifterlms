!function(){var e={184:function(e,t){var n;!function(){"use strict";var r={}.hasOwnProperty;function o(){for(var e=[],t=0;t<arguments.length;t++){var n=arguments[t];if(n){var l=typeof n;if("string"===l||"number"===l)e.push(n);else if(Array.isArray(n)){if(n.length){var a=o.apply(null,n);a&&e.push(a)}}else if("object"===l)if(n.toString===Object.prototype.toString)for(var s in n)r.call(n,s)&&n[s]&&e.push(s);else e.push(n.toString())}}return e.join(" ")}e.exports?(o.default=o,e.exports=o):void 0===(n=function(){return o}.apply(t,[]))||(e.exports=n)}()}},t={};function n(r){var o=t[r];if(void 0!==o)return o.exports;var l=t[r]={exports:{}};return e[r](l,l.exports,n),l.exports}n.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return n.d(t,{a:t}),t},n.d=function(e,t){for(var r in t)n.o(t,r)&&!n.o(e,r)&&Object.defineProperty(e,r,{enumerable:!0,get:t[r]})},n.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},function(){"use strict";var e=window.wp.blocks,t=JSON.parse('{"$schema":"https://schemas.wp.org/trunk/block.json","apiVersion":2,"name":"llms/access-plans","title":"Access Plans","category":"llms-blocks","description":"List course or membership access plans.","textdomain":"lifterlms","attributes":{"postId":{"type":"integer"},"useCurrentPost":{"type":"boolean","default":true},"planIds":{"type":"array"},"accentColor":{"type":"string"},"customAccentColor":{"type":"string"},"accentTextColor":{"type":"string"},"customAccentTextColor":{"type":"string"},"plansPerRow":{"type":"integer","default":3}},"providesContext":{"llms/access-plans/planIds":"planIds"},"supports":{"align":["wide","full"],"anchor":true,"className":true,"llms_visibility":false},"editorScript":"file:./index.js","editorStyle":"file:./editor.css"}');function r(){return r=Object.assign||function(e){for(var t=1;t<arguments.length;t++){var n=arguments[t];for(var r in n)Object.prototype.hasOwnProperty.call(n,r)&&(e[r]=n[r])}return e},r.apply(this,arguments)}var o=window.wp.element,l=n(184),a=n.n(l),s=window.wp.i18n,c=window.wp.blockEditor,i=window.wp.data,u=window.wp.editor,p=window.wp.components,d=window.lodash,m=window.llms.data;function f(e,t){let{accentColor:n,accentTextColor:r,style:o={}}=t;return{className:a()(e,{[(0,c.getColorClassName)("background-color",n.slug)]:n.slug,[r.class]:r.class}),style:{...o,backgroundColor:n.slug?null:n.color,color:r.slug?null:r.color}}}function g(e){let{plan:t,setAttributes:n,plansPerRow:l,accentColor:u,accentTextColor:d}=e;const{id:g,title:w,price:C,visibility:x,enroll_text:b,featured_text:v=(0,s.__)("Featured","lifterlms")}=t,{editPlan:y}=(0,i.useDispatch)(m.accessPlanStore),h="featured"===x;if("hidden"===x)return null;const _=+(100/l).toFixed(4)+"%",P=h?1.5:1;return(0,o.createElement)(p.FlexItem,{className:a()("llms-ap--wrap",{"is-featured-plan":h}),style:{border:h?`1px solid ${u.color}`:null,flexShrink:0,flexGrow:P,flexBasis:_}},h&&(0,o.createElement)(c.RichText,r({tagName:"strong"},f("llms-ap--featured",{accentColor:u,accentTextColor:d}),{allowedFormats:[],value:v,onChange:e=>y(g,{featured_text:e})})),(0,o.createElement)("div",null,C.toFixed(2)),(0,o.createElement)(c.RichText,{tagName:"strong",className:"llms-ap--title",allowedFormats:[],value:w,onChange:e=>y(g,{title:e})}),(0,o.createElement)(c.RichText,r({tagName:"div"},f("llms-ap--button wp-block-button__link",{accentColor:u,accentTextColor:d}),{allowedFormats:[],value:b,onChange:e=>y(g,{enroll_text:e})})))}function w(e){let{attributes:t,setAttributes:n,accentColor:r,setAccentColor:l,accentTextColor:a,setAccentTextColor:i}=e;const{plansPerRow:u}=t;return(0,o.createElement)(c.InspectorControls,null,(0,o.createElement)(p.PanelBody,{title:(0,s.__)("Layout & Display","lifterlms"),initialOpen:!0},(0,o.createElement)(p.RangeControl,{label:(0,s.__)("Plans per row"),help:(0,s.__)("The maximum number of plans displayed on each row."),value:u,onChange:e=>n({plansPerRow:e}),min:1,max:6})),(0,o.createElement)(c.PanelColorSettings,{__experimentalHasMultipleOrigins:!0,__experimentalIsRenderedInSidebar:!0,title:(0,s.__)("Color"),initialOpen:!1,colorSettings:[{value:r.color,label:(0,s.__)("Accent Color"),onChange:l},{value:a.color,label:(0,s.__)("Accent Text Color"),onChange:i}]}))}window.wp.compose;const{title:C}=t;var x=(0,c.withColors)({accentColor:"color"},{accentTextColor:"color"})((function(e){let{attributes:t,setAttributes:n,clientId:l,accentColor:a,setAccentColor:s,accentTextColor:f,setAccentTextColor:C}=e;const x=(0,c.useBlockProps)(),{plansPerRow:b}=t,{arePlansLoading:v,plans:y}=(0,i.useSelect)((e=>{const{getCurrentPostId:t}=e(u.store),{getEditedPlans:n,isLoading:r}=e(m.accessPlanStore),o={post_id:t()},l=n(o);return{plans:l?(0,d.orderBy)(l,"menu_order","asc"):[],arePlansLoading:r(o)}}));return(0,o.createElement)(o.Fragment,null,v&&(0,o.createElement)("div",r({},x,{style:{textAlign:"center",padding:"20px 0"}}),(0,o.createElement)(p.Spinner,null)),!v&&(0,o.createElement)(o.Fragment,null,(0,o.createElement)(w,{attributes:t,setAttributes:n,accentColor:a,setAccentColor:s,accentTextColor:f,setAccentTextColor:C}),(0,o.createElement)("div",x,(0,o.createElement)(p.Flex,{className:"llms-ap-list--wrap"},y.map((e=>(0,o.createElement)(g,{plan:e,plansPerRow:b,accentColor:a,accentTextColor:f})))))))}));const{name:b}=t;(0,e.registerBlockType)(b,{icon:{foreground:"#466dd8",src:"editor-table"},edit:x,save:function(e){let{attributes:t}=e;return(0,o.createElement)("div",c.useBlockProps.save(),(0,o.createElement)(c.InnerBlocks.Content,null))}})}()}();