"use strict";(self.webpackChunklifterlms=self.webpackChunklifterlms||[]).push([[365],{365:function(e,l,t){t.r(l),t.d(l,{default:function(){return m}});var r=t(307),a=t(609),s=t(736),n=t(818),i=t(238),c=t(606),o=t(702);function m(e){let{accessPlan:l,updatePlan:t}=e;const{availability_restrictions:m,redirect_page:p,redirect_type:u,redirect_url:d,redirect_forced:_,sku:h}=l,{getCurrentPostType:f}=(0,n.useSelect)(i.store),b=m.length>0;return(0,r.createElement)(r.Fragment,null,(0,r.createElement)(a.TextControl,{label:(0,s.__)("SKU","lifterlms"),value:h,onChange:e=>t({sku:e})}),(0,r.createElement)("hr",null),"course"===f()&&(0,r.createElement)(r.Fragment,null,(0,r.createElement)(c.PostSearchControl,{isClearable:!0,isMulti:!0,postType:"llms_membership",label:(0,s.__)("Membership Restrictions","lifterlms"),placeholder:(0,s.__)("Search for a membership…","lifterlms"),selectedValue:m,onUpdate:e=>{t({availability_restrictions:e.map((e=>{let{id:l}=e;return l}))})},help:b?(0,s.__)("The access plan is only available to members active in at least one of the selected memberhips.","lifterlms"):(0,s.__)("The access plan is available to everyone.","lifterlms")}),(0,r.createElement)("hr",null)),b&&(0,r.createElement)(a.BaseControl,{id:"llms-access-plan--trial-status",label:(0,s.__)("Override Membership Redirects","lifterlms")},(0,r.createElement)(a.ToggleControl,{label:_?(0,s.__)("Overriding membership redirects in favor of the plan settings below","lifterlms"):(0,s.__)("Using the default membership redirect settings","lifterlms"),className:"llms-access-plan--trial-enabled",id:"llms-access-plan--trial-enabled",checked:_,onChange:e=>t({redirect_forced:e})})),(!b||_)&&(0,r.createElement)(a.Flex,null,(0,r.createElement)(a.FlexItem,null,(0,r.createElement)(a.SelectControl,{label:(0,s.__)("Checkout Redirect","lifterlms"),value:u,onChange:e=>t({redirect_type:e}),options:(0,o.pY)()})),"self"!==u&&(0,r.createElement)(a.FlexItem,{style:{flex:1}},"page"===u&&(0,r.createElement)(c.PostSearchControl,{isClearable:!0,postType:"pages",label:(0,s.__)("Redirect Page","lifterlms"),placeholder:(0,s.__)("Search for a page…","lifterlms"),selectedValue:p?[p]:[],onUpdate:e=>{const l=(null==e?void 0:e.id)||null;t({redirect_page:l})}}),"url"===u&&(0,r.createElement)(a.TextControl,{label:(0,s.__)("Redirect URL","lifterlms"),type:"url",value:d,onChange:e=>t({redirect_url:e})}))))}}}]);