"use strict";(self.webpackChunklifterlms=self.webpackChunklifterlms||[]).push([[418],{418:function(e,l,t){t.r(l),t.d(l,{default:function(){return m}});var n=t(307),a=t(609),i=t(736),r=t(819),s=t(606),c=t(702);function u(e){let{selected:l,updatePlan:t}=e;const a=(0,c.MQ)(),{help:u}=a[l];return(0,n.createElement)(s.ButtonGroupControl,{label:(0,i.__)("Visibility","lifterlms"),help:u,className:"llms-access-plan--visibility",id:"llms-access-plan--visibility",selected:l,options:(0,r.map)(a,((e,l)=>{let{title:t,icon:n}=e;return{label:t,value:l,icon:n}})),onClick:e=>t({visibility:e})})}function m(e){let{accessPlan:l,updatePlan:t}=e;const{visibility:r,enroll_text:s,sku:m}=l;return(0,n.createElement)(n.Fragment,null,(0,n.createElement)(a.Flex,null,(0,n.createElement)(a.FlexItem,{style:{flex:2}},(0,n.createElement)(a.TextControl,{label:(0,i.__)("Title","lifterlms"),value:(0,c.YQ)(l),onChange:e=>t({title:e})})),(0,n.createElement)(a.FlexItem,{style:{flex:1}},(0,n.createElement)(a.TextControl,{label:(0,i.__)("Button Text","lifterlms"),value:s,onChange:e=>t({enroll_text:e})}))),(0,n.createElement)("hr",null),(0,n.createElement)(a.Flex,null,(0,n.createElement)(a.FlexItem,null,(0,n.createElement)(u,{selected:r,updatePlan:t}))))}}}]);