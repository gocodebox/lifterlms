!function(){"use strict";var e={n:function(t){var n=t&&t.__esModule?function(){return t.default}:function(){return t};return e.d(n,{a:n}),n},d:function(t,n){for(var r in n)e.o(n,r)&&!e.o(t,r)&&Object.defineProperty(t,r,{enumerable:!0,get:n[r]})},o:function(e,t){return Object.prototype.hasOwnProperty.call(e,t)}},t=window.wp.element,n=window.wp.blocks,r=window.wp.components,l=window.wp.blockEditor,i=window.wp.i18n,o=window.wp.serverSideRender,s=e.n(o),a=JSON.parse('{"$schema":"https://schemas.wp.org/trunk/block.json","apiVersion":2,"name":"llms/my-account","title":"My Account","icon":"admin-users","category":"lifterlms","description":"Outputs the login, registration, dashboard, profile and reset password templates.","textdomain":"lifterlms","attributes":{"login_redirect":{"type":"string"},"llms_visibility":{"type":"string"},"llms_visibility_in":{"type":"string"},"llms_visibility_posts":{"type":"string"}},"supports":{"align":["wide","full"]},"editorScript":"file:./index.js","render":"file:../../templates/blocks/shortcode.php"}');(0,n.registerBlockType)(a,{edit:e=>{const{attributes:n,setAttributes:o}=e,c=(0,l.useBlockProps)();return(0,t.createElement)(t.Fragment,null,(0,t.createElement)(l.InspectorControls,null,(0,t.createElement)(r.PanelBody,{title:(0,i.__)("My Account Settings","lifterlms")},(0,t.createElement)(r.PanelRow,null,(0,t.createElement)(r.TextControl,{label:(0,i.__)("Login redirect URL","lifterlms"),value:n.login_redirect,onChange:e=>o({login_redirect:e})})))),(0,t.createElement)("div",c,(0,t.createElement)(r.Disabled,null,(0,t.createElement)(s(),{block:a.name,attributes:n,LoadingResponsePlaceholder:()=>(0,t.createElement)("p",null,(0,i.__)("Loading...","lifterlms")),ErrorResponsePlaceholder:()=>(0,t.createElement)("p",null,(0,i.__)("Error loading content. Please check block settings are valid.","lifterlms"))}))))}})}();