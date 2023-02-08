!function(){"use strict";var e={n:function(l){var t=l&&l.__esModule?function(){return l.default}:function(){return l};return e.d(t,{a:t}),t},d:function(l,t){for(var r in t)e.o(t,r)&&!e.o(l,r)&&Object.defineProperty(l,r,{enumerable:!0,get:t[r]})},o:function(e,l){return Object.prototype.hasOwnProperty.call(e,l)}},l=window.wp.element,t=window.wp.blocks,r=window.wp.components,i=window.wp.blockEditor,n=window.wp.i18n,s=window.wp.data,a=window.wp.serverSideRender,o=e.n(a),m=JSON.parse('{"$schema":"https://schemas.wp.org/trunk/block.json","apiVersion":2,"name":"llms/memberships","title":"Memberships","icon":"groups","category":"lifterlms","description":"Display a loop of LifterLMS Membership “Tiles” as displayed on the default “Memberships” page.","textdomain":"lifterlms","attributes":{"category":{"type":"string"},"hidden":{"type":"boolean","default":true},"id":{"type":"string"},"order":{"type":"string","default":"ASC"},"orderby":{"type":"string","default":"title"},"posts_per_page":{"type":"integer","default":-1},"llms_visibility":{"type":"string"},"llms_visibility_in":{"type":"string"},"llms_visibility_posts":{"type":"string"}},"supports":{"align":["wide","full"]},"editorScript":"file:./index.js","render":"file:../../templates/blocks/shortcode.php"}');(0,t.registerBlockType)(m,{edit:e=>{const{attributes:t,setAttributes:a}=e,p=(0,i.useBlockProps)(),{categories:d,memberships:u}=(0,s.useSelect)((e=>({categories:e("core")?.getEntityRecords("taxonomy","membership_cat"),memberships:e("core")?.getEntityRecords("postType","membership")})),[]);let c=d?.map((e=>({value:e.slug,label:e.name})));c?.unshift({value:"",label:(0,n.__)("All","lifterlms")});let _={};return u?.map((e=>{_[e.id]=e.title.rendered})),(0,l.createElement)(l.Fragment,null,(0,l.createElement)(i.InspectorControls,null,(0,l.createElement)(r.PanelBody,{title:(0,n.__)("Memberships Settings","lifterlms")},(0,l.createElement)(r.PanelRow,null,(0,l.createElement)(r.SelectControl,{label:(0,n.__)("Category","lifterlms"),value:t.category,options:c,onChange:e=>a({category:e}),help:(0,n.__)("Display courses from a specific Membership Category only.","lifterlms")})),(0,l.createElement)(r.PanelRow,null,(0,l.createElement)(r.TextControl,{label:(0,n.__)("Membership ID","lifterlms"),value:t.id,onChange:e=>a({id:e}),help:(0,n.__)("Display only a specific membership. Use the memberships’s post ID. If using this option, all other options are rendered irrelevant.","lifterlms")})),(0,l.createElement)(r.PanelRow,null,(0,l.createElement)(r.SelectControl,{label:(0,n.__)("Order","lifterlms"),value:t.order,options:[{value:"ASC",label:(0,n.__)("Ascending","lifterlms")},{value:"DESC",label:(0,n.__)("Descending","lifterlms")}],onChange:e=>a({order:e}),help:(0,n.__)("Display memberships in ascending or descending order.","lifterlms")})),(0,l.createElement)(r.PanelRow,null,(0,l.createElement)(r.SelectControl,{label:(0,n.__)("Order by","lifterlms"),value:t.orderby,options:[{value:"id",label:(0,n.__)("ID","lifterlms")},{value:"author",label:(0,n.__)("Author","lifterlms")},{value:"title",label:(0,n.__)("Title","lifterlms")},{value:"name",label:(0,n.__)("Name","lifterlms")},{value:"date",label:(0,n.__)("Date","lifterlms")},{value:"modified",label:(0,n.__)("Date modified","lifterlms")},{value:"rand",label:(0,n.__)("Random","lifterlms")},{value:"menu_order",label:(0,n.__)("Menu Order","lifterlms")}],onChange:e=>a({orderby:e}),help:(0,n.__)("Determines which field is used to order memberships in the memberships list.","lifterlms")})),(0,l.createElement)(r.PanelRow,null,(0,l.createElement)(r.__experimentalNumberControl,{label:(0,n.__)("Per Page","lifterlms"),value:t.posts_per_page,min:-1,max:100,onChange:e=>a({posts_per_page:null!=e?e:-1}),help:(0,n.__)(" Determines the number of results to display. Default returns all available memberships.","lifterlms")})))),(0,l.createElement)("div",p,(0,l.createElement)(r.Disabled,null,(0,l.createElement)(o(),{block:m.name,attributes:t,LoadingResponsePlaceholder:()=>(0,l.createElement)("p",null,"Loading..."),ErrorResponsePlaceholder:()=>(0,l.createElement)("p",null,"Error")}))))}})}();