"use strict";(self.webpackChunklifterlms=self.webpackChunklifterlms||[]).push([[577],{577:function(e,l,t){t.r(l),t.d(l,{default:function(){return p}});var a=t(307),s=t(609),n=t(771),c=t(736),i=t(606);const r=[{label:(0,c.__)("No expiration","lifterlms"),value:"lifetime"},{label:(0,c.__)("Expire by period","lifterlms"),value:"limited-period"},{label:(0,c.__)("Expire on date","lifterlms"),value:"limited-date"}];function o(e){let{selected:l,updatePlan:t}=e;return(0,a.createElement)(i.ButtonGroupControl,{label:(0,c.__)("Content Access Expiration","lifterlms"),className:"llms-access-plan--access-expiration",id:"llms-access-plan--access-expiration",selected:l,options:r,onClick:e=>t({access_expiration:e})})}var m=t(702);function p(e){let{accessPlan:l,updatePlan:t}=e;const{access_expiration:r,access_length:p,access_period:u,access_expires:d}=l,x=d?new Date(d):new Date((new Date).setHours(23,59,59,999));return(0,a.createElement)(a.Fragment,null,(0,a.createElement)(s.Flex,null,(0,a.createElement)(s.FlexItem,null,(0,a.createElement)(o,{selected:r,updatePlan:t})),"limited-period"===r&&(0,a.createElement)(s.FlexItem,null,(0,a.createElement)(s.BaseControl,{label:(0,c.__)("Access Expires After","lifterlms"),className:"llms-access-plan--access-expiration-period",id:"llms-access-plan--access-expiration-period"},(0,a.createElement)(s.Flex,null,(0,a.createElement)(s.FlexItem,null,(0,a.createElement)(s.TextControl,{id:"llms-access-plan--access-expiration-period",hideLabelFromVision:!0,label:(0,c.__)("Access expiration length","lifterlms"),type:"number",step:"1",min:"1",value:p,onChange:e=>t({access_length:e})})),(0,a.createElement)(s.FlexItem,null,(0,a.createElement)(s.SelectControl,{hideLabelFromVision:!0,label:(0,c.__)("Access expiration period","lifterlms"),value:u,onChange:e=>t({access_period:e}),options:(0,m.ug)(p>=2)}))))),"limited-date"===r&&(0,a.createElement)(s.FlexItem,null,(0,a.createElement)(i.DatePickerControl,{label:(0,c.__)("Access Expiration Date","lifterlms"),className:"llms-access-plan--access-expiration-date",id:"llms-access-plan--access-expiration-date",onChange:e=>t({access_expires:e}),isInvalidDate:e=>!(0,n.isInTheFuture)(e),currentDate:x}))))}}}]);