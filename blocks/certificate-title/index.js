!function(){var e={930:function(e,t,r){"use strict";var n=window.wp.blocks,i=JSON.parse('{"$schema":"https://schemas.wp.org/trunk/block.json","apiVersion":2,"name":"llms/certificate-title","title":"Certificate Title","category":"text","description":"Displays the title of a certificate.","textdomain":"lifterlms","attributes":{"textAlign":{"type":"string","default":"center"},"content":{"type":"string","source":"html","selector":"h1,h2,h3,h4,h5,h6","default":"","__experimentalRole":"content"},"level":{"type":"number","default":1},"placeholder":{"type":"string"},"fontFamily":{"type":"string","default":"default"}},"supports":{"align":["wide","full"],"anchor":true,"className":false,"color":{"link":true},"spacing":{"margin":true},"typography":{"fontSize":true,"lineHeight":true,"__experimentalFontStyle":true,"__experimentalFontFamily":true,"__experimentalFontWeight":true,"__experimentalLetterSpacing":true,"__experimentalTextTransform":true,"__experimentalDefaultControls":{"fontSize":true,"fontAppearance":true,"textTransform":true}},"__experimentalSelector":"h1,h2,h3,h4,h5,h6","__unstablePasteTextInline":true,"__experimentalSlashInserter":true,"multiple":false,"llms_visibility":false},"editorScript":"file:./index.js"}'),l=window.wp.element,o=window.wp.i18n,a=window.wp.data,s=window.wp.editor,c=r(184),u=r.n(c),p=window.wp.blockEditor;const{name:f}=i;(0,n.registerBlockType)(f,{icon:{foreground:"#466dd8",src:"awards"},edit:function(e){let{attributes:t,setAttributes:r,mergeBlocks:i,onReplace:c,style:u,clientId:p}=e;const{getBlockType:f}=(0,a.useSelect)(n.store),{getEditedPostAttribute:d,getCurrentPostType:m}=(0,a.useSelect)(s.store),{edit:h}=f("core/heading"),g="llms_certificate"===m()?"certificate_title":"title";return t.placeholder=t.placeholder||(0,o.__)("Certificate of Achievement","lifterlms"),t.content=t.content||d(g),(0,l.createElement)(l.Fragment,null,(0,l.createElement)(h,{attributes:t,setAttributes:e=>{const{content:t}=e;return void 0!==t&&function(e){let t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:null;if(!t){const{getCurrentPostType:e}=(0,a.select)(s.store);t=e()}const{editPost:r}=(0,a.dispatch)(s.store),n={};"llms_certificate"===t?n.certificate_title=e:"llms_my_certificate"===t&&(n.title=e),r(n)}(t),r(e)},mergeBlocks:i,onReplace:c,style:u,clientId:p}))},save:function(e){let{attributes:t}=e;const{textAlign:r,content:n,level:i}=t,o="h"+i,a=u()({[`has-text-align-${r}`]:r});return(0,l.createElement)(o,p.useBlockProps.save({className:a}),(0,l.createElement)(p.RichText.Content,{value:n}))}})},184:function(e,t){var r;!function(){"use strict";var n={}.hasOwnProperty;function i(){for(var e=[],t=0;t<arguments.length;t++){var r=arguments[t];if(r){var l=typeof r;if("string"===l||"number"===l)e.push(r);else if(Array.isArray(r)){if(r.length){var o=i.apply(null,r);o&&e.push(o)}}else if("object"===l)if(r.toString===Object.prototype.toString)for(var a in r)n.call(r,a)&&r[a]&&e.push(a);else e.push(r.toString())}}return e.join(" ")}e.exports?(i.default=i,e.exports=i):void 0===(r=function(){return i}.apply(t,[]))||(e.exports=r)}()}},t={};function r(n){var i=t[n];if(void 0!==i)return i.exports;var l=t[n]={exports:{}};return e[n](l,l.exports,r),l.exports}r.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return r.d(t,{a:t}),t},r.d=function(e,t){for(var n in t)r.o(t,n)&&!r.o(e,n)&&Object.defineProperty(e,n,{enumerable:!0,get:t[n]})},r.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},r(930)}();