(()=>{var e={184:(e,t)=>{var r;!function(){"use strict";var n={}.hasOwnProperty;function a(){for(var e=[],t=0;t<arguments.length;t++){var r=arguments[t];if(r){var i=typeof r;if("string"===i||"number"===i)e.push(r);else if(Array.isArray(r)){if(r.length){var o=a.apply(null,r);o&&e.push(o)}}else if("object"===i){if(r.toString!==Object.prototype.toString&&!r.toString.toString().includes("[native code]")){e.push(r.toString());continue}for(var s in r)n.call(r,s)&&r[s]&&e.push(s)}}}return e.join(" ")}e.exports?(a.default=a,e.exports=a):void 0===(r=function(){return a}.apply(t,[]))||(e.exports=r)}()}},t={};function r(n){var a=t[n];if(void 0!==a)return a.exports;var i=t[n]={exports:{}};return e[n](i,i.exports,r),i.exports}r.n=e=>{var t=e&&e.__esModule?()=>e.default:()=>e;return r.d(t,{a:t}),t},r.d=(e,t)=>{for(var n in t)r.o(t,n)&&!r.o(e,n)&&Object.defineProperty(e,n,{enumerable:!0,get:t[n]})},r.o=(e,t)=>Object.prototype.hasOwnProperty.call(e,t),(()=>{"use strict";const e=window.wp.blocks,t=JSON.parse('{"$schema":"https://schemas.wp.org/trunk/block.json","apiVersion":2,"name":"llms/certificate-title","title":"Certificate Title","category":"text","description":"Displays the title of a certificate.","textdomain":"lifterlms","attributes":{"textAlign":{"type":"string","default":"center"},"content":{"type":"string","source":"html","selector":"h1,h2,h3,h4,h5,h6","default":"","__experimentalRole":"content"},"level":{"type":"number","default":1},"placeholder":{"type":"string"},"fontFamily":{"type":"string","default":"default"}},"supports":{"align":["wide","full"],"anchor":true,"className":false,"color":{"link":true},"spacing":{"margin":true},"typography":{"fontSize":true,"lineHeight":true,"__experimentalFontStyle":true,"__experimentalFontFamily":true,"__experimentalFontWeight":true,"__experimentalLetterSpacing":true,"__experimentalTextTransform":true,"__experimentalDefaultControls":{"fontSize":true,"fontAppearance":true,"textTransform":true}},"__experimentalSelector":"h1,h2,h3,h4,h5,h6","__unstablePasteTextInline":true,"__experimentalSlashInserter":true,"multiple":false,"llms_visibility":false},"editorScript":"file:./index.js"}'),n=window.wp.element,a=window.wp.i18n,i=window.wp.data,o=window.wp.editor,s=window.wp.components;window.wp.compose;const c=window.React;function l(){return l=Object.assign?Object.assign.bind():function(e){for(var t=1;t<arguments.length;t++){var r=arguments[t];for(var n in r)Object.prototype.hasOwnProperty.call(r,n)&&(e[n]=r[n])}return e},l.apply(this,arguments)}window.ReactDOM,window.lodash,window.wp.apiFetch,window.wp.url;const u=function(e){var t=Object.create(null);return function(r){return void 0===t[r]&&(t[r]=e(r)),t[r]}};var d=/^((children|dangerouslySetInnerHTML|key|ref|autoFocus|defaultValue|defaultChecked|innerHTML|suppressContentEditableWarning|suppressHydrationWarning|valueLink|abbr|accept|acceptCharset|accessKey|action|allow|allowUserMedia|allowPaymentRequest|allowFullScreen|allowTransparency|alt|async|autoComplete|autoPlay|capture|cellPadding|cellSpacing|challenge|charSet|checked|cite|classID|className|cols|colSpan|content|contentEditable|contextMenu|controls|controlsList|coords|crossOrigin|data|dateTime|decoding|default|defer|dir|disabled|disablePictureInPicture|download|draggable|encType|enterKeyHint|form|formAction|formEncType|formMethod|formNoValidate|formTarget|frameBorder|headers|height|hidden|high|href|hrefLang|htmlFor|httpEquiv|id|inputMode|integrity|is|keyParams|keyType|kind|label|lang|list|loading|loop|low|marginHeight|marginWidth|max|maxLength|media|mediaGroup|method|min|minLength|multiple|muted|name|nonce|noValidate|open|optimum|pattern|placeholder|playsInline|poster|preload|profile|radioGroup|readOnly|referrerPolicy|rel|required|reversed|role|rows|rowSpan|sandbox|scope|scoped|scrolling|seamless|selected|shape|size|sizes|slot|span|spellCheck|src|srcDoc|srcLang|srcSet|start|step|style|summary|tabIndex|target|title|translate|type|useMap|value|width|wmode|wrap|about|datatype|inlist|prefix|property|resource|typeof|vocab|autoCapitalize|autoCorrect|autoSave|color|incremental|fallback|inert|itemProp|itemScope|itemType|itemID|itemRef|on|option|results|security|unselectable|accentHeight|accumulate|additive|alignmentBaseline|allowReorder|alphabetic|amplitude|arabicForm|ascent|attributeName|attributeType|autoReverse|azimuth|baseFrequency|baselineShift|baseProfile|bbox|begin|bias|by|calcMode|capHeight|clip|clipPathUnits|clipPath|clipRule|colorInterpolation|colorInterpolationFilters|colorProfile|colorRendering|contentScriptType|contentStyleType|cursor|cx|cy|d|decelerate|descent|diffuseConstant|direction|display|divisor|dominantBaseline|dur|dx|dy|edgeMode|elevation|enableBackground|end|exponent|externalResourcesRequired|fill|fillOpacity|fillRule|filter|filterRes|filterUnits|floodColor|floodOpacity|focusable|fontFamily|fontSize|fontSizeAdjust|fontStretch|fontStyle|fontVariant|fontWeight|format|from|fr|fx|fy|g1|g2|glyphName|glyphOrientationHorizontal|glyphOrientationVertical|glyphRef|gradientTransform|gradientUnits|hanging|horizAdvX|horizOriginX|ideographic|imageRendering|in|in2|intercept|k|k1|k2|k3|k4|kernelMatrix|kernelUnitLength|kerning|keyPoints|keySplines|keyTimes|lengthAdjust|letterSpacing|lightingColor|limitingConeAngle|local|markerEnd|markerMid|markerStart|markerHeight|markerUnits|markerWidth|mask|maskContentUnits|maskUnits|mathematical|mode|numOctaves|offset|opacity|operator|order|orient|orientation|origin|overflow|overlinePosition|overlineThickness|panose1|paintOrder|pathLength|patternContentUnits|patternTransform|patternUnits|pointerEvents|points|pointsAtX|pointsAtY|pointsAtZ|preserveAlpha|preserveAspectRatio|primitiveUnits|r|radius|refX|refY|renderingIntent|repeatCount|repeatDur|requiredExtensions|requiredFeatures|restart|result|rotate|rx|ry|scale|seed|shapeRendering|slope|spacing|specularConstant|specularExponent|speed|spreadMethod|startOffset|stdDeviation|stemh|stemv|stitchTiles|stopColor|stopOpacity|strikethroughPosition|strikethroughThickness|string|stroke|strokeDasharray|strokeDashoffset|strokeLinecap|strokeLinejoin|strokeMiterlimit|strokeOpacity|strokeWidth|surfaceScale|systemLanguage|tableValues|targetX|targetY|textAnchor|textDecoration|textRendering|textLength|to|transform|u1|u2|underlinePosition|underlineThickness|unicode|unicodeBidi|unicodeRange|unitsPerEm|vAlphabetic|vHanging|vIdeographic|vMathematical|values|vectorEffect|version|vertAdvY|vertOriginX|vertOriginY|viewBox|viewTarget|visibility|widths|wordSpacing|writingMode|x|xHeight|x1|x2|xChannelSelector|xlinkActuate|xlinkArcrole|xlinkHref|xlinkRole|xlinkShow|xlinkTitle|xlinkType|xmlBase|xmlns|xmlnsXlink|xmlLang|xmlSpace|y|y1|y2|yChannelSelector|z|zoomAndPan|for|class|autofocus)|(([Dd][Aa][Tt][Aa]|[Aa][Rr][Ii][Aa]|x)-.*))$/;const f=u((function(e){return d.test(e)||111===e.charCodeAt(0)&&110===e.charCodeAt(1)&&e.charCodeAt(2)<91}));var p=function(){function e(e){var t=this;this._insertTag=function(e){var r;r=0===t.tags.length?t.insertionPoint?t.insertionPoint.nextSibling:t.prepend?t.container.firstChild:t.before:t.tags[t.tags.length-1].nextSibling,t.container.insertBefore(e,r),t.tags.push(e)},this.isSpeedy=void 0===e.speedy||e.speedy,this.tags=[],this.ctr=0,this.nonce=e.nonce,this.key=e.key,this.container=e.container,this.prepend=e.prepend,this.insertionPoint=e.insertionPoint,this.before=null}var t=e.prototype;return t.hydrate=function(e){e.forEach(this._insertTag)},t.insert=function(e){this.ctr%(this.isSpeedy?65e3:1)==0&&this._insertTag(function(e){var t=document.createElement("style");return t.setAttribute("data-emotion",e.key),void 0!==e.nonce&&t.setAttribute("nonce",e.nonce),t.appendChild(document.createTextNode("")),t.setAttribute("data-s",""),t}(this));var t=this.tags[this.tags.length-1];if(this.isSpeedy){var r=function(e){if(e.sheet)return e.sheet;for(var t=0;t<document.styleSheets.length;t++)if(document.styleSheets[t].ownerNode===e)return document.styleSheets[t]}(t);try{r.insertRule(e,r.cssRules.length)}catch(e){}}else t.appendChild(document.createTextNode(e));this.ctr++},t.flush=function(){this.tags.forEach((function(e){return e.parentNode&&e.parentNode.removeChild(e)})),this.tags=[],this.ctr=0},e}(),h=Math.abs,m=String.fromCharCode,g=Object.assign;function y(e){return e.trim()}function v(e,t,r){return e.replace(t,r)}function b(e,t){return e.indexOf(t)}function w(e,t){return 0|e.charCodeAt(t)}function k(e,t,r){return e.slice(t,r)}function x(e){return e.length}function _(e){return e.length}function C(e,t){return t.push(e),e}var S=1,A=1,P=0,T=0,O=0,$="";function E(e,t,r,n,a,i,o){return{value:e,root:t,parent:r,type:n,props:a,children:i,line:S,column:A,length:o,return:""}}function L(e,t){return g(E("",null,null,"",null,null,0),e,{length:-e.length},t)}function R(){return O=T>0?w($,--T):0,A--,10===O&&(A=1,S--),O}function z(){return O=T<P?w($,T++):0,A++,10===O&&(A=1,S++),O}function M(){return w($,T)}function j(){return T}function I(e,t){return k($,e,t)}function N(e){switch(e){case 0:case 9:case 10:case 13:case 32:return 5;case 33:case 43:case 44:case 47:case 62:case 64:case 126:case 59:case 123:case 125:return 4;case 58:return 3;case 34:case 39:case 40:case 91:return 2;case 41:case 93:return 1}return 0}function F(e){return S=A=1,P=x($=e),T=0,[]}function B(e){return $="",e}function D(e){return y(I(T-1,G(91===e?e+2:40===e?e+1:e)))}function H(e){for(;(O=M())&&O<33;)z();return N(e)>2||N(O)>3?"":" "}function q(e,t){for(;--t&&z()&&!(O<48||O>102||O>57&&O<65||O>70&&O<97););return I(e,j()+(t<6&&32==M()&&32==z()))}function G(e){for(;z();)switch(O){case e:return T;case 34:case 39:34!==e&&39!==e&&G(O);break;case 40:41===e&&G(e);break;case 92:z()}return T}function W(e,t){for(;z()&&e+O!==57&&(e+O!==84||47!==M()););return"/*"+I(t,T-1)+"*"+m(47===e?e:z())}function U(e){for(;!N(M());)z();return I(e,T)}var V="-ms-",X="-moz-",Y="-webkit-",K="comm",Z="rule",J="decl",Q="@keyframes";function ee(e,t){for(var r="",n=_(e),a=0;a<n;a++)r+=t(e[a],a,e,t)||"";return r}function te(e,t,r,n){switch(e.type){case"@import":case J:return e.return=e.return||e.value;case K:return"";case Q:return e.return=e.value+"{"+ee(e.children,n)+"}";case Z:e.value=e.props.join(",")}return x(r=ee(e.children,n))?e.return=e.value+"{"+r+"}":""}function re(e,t){switch(function(e,t){return(((t<<2^w(e,0))<<2^w(e,1))<<2^w(e,2))<<2^w(e,3)}(e,t)){case 5103:return Y+"print-"+e+e;case 5737:case 4201:case 3177:case 3433:case 1641:case 4457:case 2921:case 5572:case 6356:case 5844:case 3191:case 6645:case 3005:case 6391:case 5879:case 5623:case 6135:case 4599:case 4855:case 4215:case 6389:case 5109:case 5365:case 5621:case 3829:return Y+e+e;case 5349:case 4246:case 4810:case 6968:case 2756:return Y+e+X+e+V+e+e;case 6828:case 4268:return Y+e+V+e+e;case 6165:return Y+e+V+"flex-"+e+e;case 5187:return Y+e+v(e,/(\w+).+(:[^]+)/,"-webkit-box-$1$2-ms-flex-$1$2")+e;case 5443:return Y+e+V+"flex-item-"+v(e,/flex-|-self/,"")+e;case 4675:return Y+e+V+"flex-line-pack"+v(e,/align-content|flex-|-self/,"")+e;case 5548:return Y+e+V+v(e,"shrink","negative")+e;case 5292:return Y+e+V+v(e,"basis","preferred-size")+e;case 6060:return Y+"box-"+v(e,"-grow","")+Y+e+V+v(e,"grow","positive")+e;case 4554:return Y+v(e,/([^-])(transform)/g,"$1-webkit-$2")+e;case 6187:return v(v(v(e,/(zoom-|grab)/,Y+"$1"),/(image-set)/,Y+"$1"),e,"")+e;case 5495:case 3959:return v(e,/(image-set\([^]*)/,Y+"$1$`$1");case 4968:return v(v(e,/(.+:)(flex-)?(.*)/,"-webkit-box-pack:$3-ms-flex-pack:$3"),/s.+-b[^;]+/,"justify")+Y+e+e;case 4095:case 3583:case 4068:case 2532:return v(e,/(.+)-inline(.+)/,Y+"$1$2")+e;case 8116:case 7059:case 5753:case 5535:case 5445:case 5701:case 4933:case 4677:case 5533:case 5789:case 5021:case 4765:if(x(e)-1-t>6)switch(w(e,t+1)){case 109:if(45!==w(e,t+4))break;case 102:return v(e,/(.+:)(.+)-([^]+)/,"$1-webkit-$2-$3$1"+X+(108==w(e,t+3)?"$3":"$2-$3"))+e;case 115:return~b(e,"stretch")?re(v(e,"stretch","fill-available"),t)+e:e}break;case 4949:if(115!==w(e,t+1))break;case 6444:switch(w(e,x(e)-3-(~b(e,"!important")&&10))){case 107:return v(e,":",":"+Y)+e;case 101:return v(e,/(.+:)([^;!]+)(;|!.+)?/,"$1"+Y+(45===w(e,14)?"inline-":"")+"box$3$1"+Y+"$2$3$1"+V+"$2box$3")+e}break;case 5936:switch(w(e,t+11)){case 114:return Y+e+V+v(e,/[svh]\w+-[tblr]{2}/,"tb")+e;case 108:return Y+e+V+v(e,/[svh]\w+-[tblr]{2}/,"tb-rl")+e;case 45:return Y+e+V+v(e,/[svh]\w+-[tblr]{2}/,"lr")+e}return Y+e+V+e+e}return e}function ne(e){return B(ae("",null,null,null,[""],e=F(e),0,[0],e))}function ae(e,t,r,n,a,i,o,s,c){for(var l=0,u=0,d=o,f=0,p=0,h=0,g=1,y=1,w=1,k=0,_="",S=a,A=i,P=n,T=_;y;)switch(h=k,k=z()){case 40:if(108!=h&&58==T.charCodeAt(d-1)){-1!=b(T+=v(D(k),"&","&\f"),"&\f")&&(w=-1);break}case 34:case 39:case 91:T+=D(k);break;case 9:case 10:case 13:case 32:T+=H(h);break;case 92:T+=q(j()-1,7);continue;case 47:switch(M()){case 42:case 47:C(oe(W(z(),j()),t,r),c);break;default:T+="/"}break;case 123*g:s[l++]=x(T)*w;case 125*g:case 59:case 0:switch(k){case 0:case 125:y=0;case 59+u:p>0&&x(T)-d&&C(p>32?se(T+";",n,r,d-1):se(v(T," ","")+";",n,r,d-2),c);break;case 59:T+=";";default:if(C(P=ie(T,t,r,l,u,a,s,_,S=[],A=[],d),i),123===k)if(0===u)ae(T,t,P,P,S,i,d,s,A);else switch(f){case 100:case 109:case 115:ae(e,P,P,n&&C(ie(e,P,P,0,0,a,s,_,a,S=[],d),A),a,A,d,s,n?S:A);break;default:ae(T,P,P,P,[""],A,0,s,A)}}l=u=p=0,g=w=1,_=T="",d=o;break;case 58:d=1+x(T),p=h;default:if(g<1)if(123==k)--g;else if(125==k&&0==g++&&125==R())continue;switch(T+=m(k),k*g){case 38:w=u>0?1:(T+="\f",-1);break;case 44:s[l++]=(x(T)-1)*w,w=1;break;case 64:45===M()&&(T+=D(z())),f=M(),u=d=x(_=T+=U(j())),k++;break;case 45:45===h&&2==x(T)&&(g=0)}}return i}function ie(e,t,r,n,a,i,o,s,c,l,u){for(var d=a-1,f=0===a?i:[""],p=_(f),m=0,g=0,b=0;m<n;++m)for(var w=0,x=k(e,d+1,d=h(g=o[m])),C=e;w<p;++w)(C=y(g>0?f[w]+" "+x:v(x,/&\f/g,f[w])))&&(c[b++]=C);return E(e,t,r,0===a?Z:s,c,l,u)}function oe(e,t,r){return E(e,t,r,K,m(O),k(e,2,-2),0)}function se(e,t,r,n){return E(e,t,r,J,k(e,0,n),k(e,n+1,-1),n)}var ce=function(e,t,r){for(var n=0,a=0;n=a,a=M(),38===n&&12===a&&(t[r]=1),!N(a);)z();return I(e,T)},le=new WeakMap,ue=function(e){if("rule"===e.type&&e.parent&&!(e.length<1)){for(var t=e.value,r=e.parent,n=e.column===r.column&&e.line===r.line;"rule"!==r.type;)if(!(r=r.parent))return;if((1!==e.props.length||58===t.charCodeAt(0)||le.get(r))&&!n){le.set(e,!0);for(var a=[],i=function(e,t){return B(function(e,t){var r=-1,n=44;do{switch(N(n)){case 0:38===n&&12===M()&&(t[r]=1),e[r]+=ce(T-1,t,r);break;case 2:e[r]+=D(n);break;case 4:if(44===n){e[++r]=58===M()?"&\f":"",t[r]=e[r].length;break}default:e[r]+=m(n)}}while(n=z());return e}(F(e),t))}(t,a),o=r.props,s=0,c=0;s<i.length;s++)for(var l=0;l<o.length;l++,c++)e.props[c]=a[s]?i[s].replace(/&\f/g,o[l]):o[l]+" "+i[s]}}},de=function(e){if("decl"===e.type){var t=e.value;108===t.charCodeAt(0)&&98===t.charCodeAt(2)&&(e.return="",e.value="")}},fe=[function(e,t,r,n){if(e.length>-1&&!e.return)switch(e.type){case J:e.return=re(e.value,e.length);break;case Q:return ee([L(e,{value:v(e.value,"@","@"+Y)})],n);case Z:if(e.length)return function(e,t){return e.map(t).join("")}(e.props,(function(t){switch(function(e,t){return(e=/(::plac\w+|:read-\w+)/.exec(e))?e[0]:e}(t)){case":read-only":case":read-write":return ee([L(e,{props:[v(t,/:(read-\w+)/,":-moz-$1")]})],n);case"::placeholder":return ee([L(e,{props:[v(t,/:(plac\w+)/,":-webkit-input-$1")]}),L(e,{props:[v(t,/:(plac\w+)/,":-moz-$1")]}),L(e,{props:[v(t,/:(plac\w+)/,V+"input-$1")]})],n)}return""}))}}];const pe=function(e){var t=e.key;if("css"===t){var r=document.querySelectorAll("style[data-emotion]:not([data-s])");Array.prototype.forEach.call(r,(function(e){-1!==e.getAttribute("data-emotion").indexOf(" ")&&(document.head.appendChild(e),e.setAttribute("data-s",""))}))}var n,a,i=e.stylisPlugins||fe,o={},s=[];n=e.container||document.head,Array.prototype.forEach.call(document.querySelectorAll('style[data-emotion^="'+t+' "]'),(function(e){for(var t=e.getAttribute("data-emotion").split(" "),r=1;r<t.length;r++)o[t[r]]=!0;s.push(e)}));var c,l,u,d,f=[te,(d=function(e){c.insert(e)},function(e){e.root||(e=e.return)&&d(e)})],h=(l=[ue,de].concat(i,f),u=_(l),function(e,t,r,n){for(var a="",i=0;i<u;i++)a+=l[i](e,t,r,n)||"";return a});a=function(e,t,r,n){c=r,ee(ne(e?e+"{"+t.styles+"}":t.styles),h),n&&(m.inserted[t.name]=!0)};var m={key:t,sheet:new p({key:t,container:n,nonce:e.nonce,speedy:e.speedy,prepend:e.prepend,insertionPoint:e.insertionPoint}),nonce:e.nonce,inserted:o,registered:{},insert:a};return m.sheet.hydrate(s),m},he=function(e){for(var t,r=0,n=0,a=e.length;a>=4;++n,a-=4)t=1540483477*(65535&(t=255&e.charCodeAt(n)|(255&e.charCodeAt(++n))<<8|(255&e.charCodeAt(++n))<<16|(255&e.charCodeAt(++n))<<24))+(59797*(t>>>16)<<16),r=1540483477*(65535&(t^=t>>>24))+(59797*(t>>>16)<<16)^1540483477*(65535&r)+(59797*(r>>>16)<<16);switch(a){case 3:r^=(255&e.charCodeAt(n+2))<<16;case 2:r^=(255&e.charCodeAt(n+1))<<8;case 1:r=1540483477*(65535&(r^=255&e.charCodeAt(n)))+(59797*(r>>>16)<<16)}return(((r=1540483477*(65535&(r^=r>>>13))+(59797*(r>>>16)<<16))^r>>>15)>>>0).toString(36)},me={animationIterationCount:1,borderImageOutset:1,borderImageSlice:1,borderImageWidth:1,boxFlex:1,boxFlexGroup:1,boxOrdinalGroup:1,columnCount:1,columns:1,flex:1,flexGrow:1,flexPositive:1,flexShrink:1,flexNegative:1,flexOrder:1,gridRow:1,gridRowEnd:1,gridRowSpan:1,gridRowStart:1,gridColumn:1,gridColumnEnd:1,gridColumnSpan:1,gridColumnStart:1,msGridRow:1,msGridRowSpan:1,msGridColumn:1,msGridColumnSpan:1,fontWeight:1,lineHeight:1,opacity:1,order:1,orphans:1,tabSize:1,widows:1,zIndex:1,zoom:1,WebkitLineClamp:1,fillOpacity:1,floodOpacity:1,stopOpacity:1,strokeDasharray:1,strokeDashoffset:1,strokeMiterlimit:1,strokeOpacity:1,strokeWidth:1};var ge=/[A-Z]|^ms/g,ye=/_EMO_([^_]+?)_([^]*?)_EMO_/g,ve=function(e){return 45===e.charCodeAt(1)},be=function(e){return null!=e&&"boolean"!=typeof e},we=u((function(e){return ve(e)?e:e.replace(ge,"-$&").toLowerCase()})),ke=function(e,t){switch(e){case"animation":case"animationName":if("string"==typeof t)return t.replace(ye,(function(e,t,r){return _e={name:t,styles:r,next:_e},t}))}return 1===me[e]||ve(e)||"number"!=typeof t||0===t?t:t+"px"};function xe(e,t,r){if(null==r)return"";if(void 0!==r.__emotion_styles)return r;switch(typeof r){case"boolean":return"";case"object":if(1===r.anim)return _e={name:r.name,styles:r.styles,next:_e},r.name;if(void 0!==r.styles){var n=r.next;if(void 0!==n)for(;void 0!==n;)_e={name:n.name,styles:n.styles,next:_e},n=n.next;return r.styles+";"}return function(e,t,r){var n="";if(Array.isArray(r))for(var a=0;a<r.length;a++)n+=xe(e,t,r[a])+";";else for(var i in r){var o=r[i];if("object"!=typeof o)null!=t&&void 0!==t[o]?n+=i+"{"+t[o]+"}":be(o)&&(n+=we(i)+":"+ke(i,o)+";");else if(!Array.isArray(o)||"string"!=typeof o[0]||null!=t&&void 0!==t[o[0]]){var s=xe(e,t,o);switch(i){case"animation":case"animationName":n+=we(i)+":"+s+";";break;default:n+=i+"{"+s+"}"}}else for(var c=0;c<o.length;c++)be(o[c])&&(n+=we(i)+":"+ke(i,o[c])+";")}return n}(e,t,r);case"function":if(void 0!==e){var a=_e,i=r(e);return _e=a,xe(e,t,i)}}if(null==t)return r;var o=t[r];return void 0!==o?o:r}var _e,Ce=/label:\s*([^\s;\n{]+)\s*(;|$)/g,Se=function(e,t,r){if(1===e.length&&"object"==typeof e[0]&&null!==e[0]&&void 0!==e[0].styles)return e[0];var n=!0,a="";_e=void 0;var i=e[0];null==i||void 0===i.raw?(n=!1,a+=xe(r,t,i)):a+=i[0];for(var o=1;o<e.length;o++)a+=xe(r,t,e[o]),n&&(a+=i[o]);Ce.lastIndex=0;for(var s,c="";null!==(s=Ce.exec(a));)c+="-"+s[1];return{name:he(a)+c,styles:a,next:_e}},Ae=!!c.useInsertionEffect&&c.useInsertionEffect,Pe=Ae||function(e){return e()},Te=(Ae||c.useLayoutEffect,(0,c.createContext)("undefined"!=typeof HTMLElement?pe({key:"css"}):null));Te.Provider;var Oe=function(e){return(0,c.forwardRef)((function(t,r){var n=(0,c.useContext)(Te);return e(t,n,r)}))},$e=(0,c.createContext)({});function Ee(e,t,r){var n="";return r.split(" ").forEach((function(r){void 0!==e[r]?t.push(e[r]+";"):n+=r+" "})),n}var Le=function(e,t,r){var n=e.key+"-"+t.name;!1===r&&void 0===e.registered[n]&&(e.registered[n]=t.styles)},Re=f,ze=function(e){return"theme"!==e},Me=function(e){return"string"==typeof e&&e.charCodeAt(0)>96?Re:ze},je=function(e,t,r){var n;if(t){var a=t.shouldForwardProp;n=e.__emotion_forwardProp&&a?function(t){return e.__emotion_forwardProp(t)&&a(t)}:a}return"function"!=typeof n&&r&&(n=e.__emotion_forwardProp),n},Ie=function(e){var t=e.cache,r=e.serialized,n=e.isStringTag;return Le(t,r,n),Pe((function(){return function(e,t,r){Le(e,t,r);var n=e.key+"-"+t.name;if(void 0===e.inserted[t.name]){var a=t;do{e.insert(t===a?"."+n:"",a,e.sheet,!0),a=a.next}while(void 0!==a)}}(t,r,n)})),null};var Ne=function e(t,r){var n,a,i=t.__emotion_real===t,o=i&&t.__emotion_base||t;void 0!==r&&(n=r.label,a=r.target);var s=je(t,r,i),u=s||Me(o),d=!u("as");return function(){var f=arguments,p=i&&void 0!==t.__emotion_styles?t.__emotion_styles.slice(0):[];if(void 0!==n&&p.push("label:"+n+";"),null==f[0]||void 0===f[0].raw)p.push.apply(p,f);else{p.push(f[0][0]);for(var h=f.length,m=1;m<h;m++)p.push(f[m],f[0][m])}var g=Oe((function(e,t,r){var n=d&&e.as||o,i="",l=[],f=e;if(null==e.theme){for(var h in f={},e)f[h]=e[h];f.theme=(0,c.useContext)($e)}"string"==typeof e.className?i=Ee(t.registered,l,e.className):null!=e.className&&(i=e.className+" ");var m=Se(p.concat(l),t.registered,f);i+=t.key+"-"+m.name,void 0!==a&&(i+=" "+a);var g=d&&void 0===s?Me(n):u,y={};for(var v in e)d&&"as"===v||g(v)&&(y[v]=e[v]);return y.className=i,y.ref=r,(0,c.createElement)(c.Fragment,null,(0,c.createElement)(Ie,{cache:t,serialized:m,isStringTag:"string"==typeof n}),(0,c.createElement)(n,y))}));return g.displayName=void 0!==n?n:"Styled("+("string"==typeof o?o:o.displayName||o.name||"Component")+")",g.defaultProps=t.defaultProps,g.__emotion_real=g,g.__emotion_base=o,g.__emotion_styles=p,g.__emotion_forwardProp=s,Object.defineProperty(g,"toString",{value:function(){return"."+a}}),g.withComponent=function(t,n){return e(t,l({},r,n,{shouldForwardProp:je(g,n,!0)})).apply(void 0,p)},g}}.bind();["a","abbr","address","area","article","aside","audio","b","base","bdi","bdo","big","blockquote","body","br","button","canvas","caption","cite","code","col","colgroup","data","datalist","dd","del","details","dfn","dialog","div","dl","dt","em","embed","fieldset","figcaption","figure","footer","form","h1","h2","h3","h4","h5","h6","head","header","hgroup","hr","html","i","iframe","img","input","ins","kbd","keygen","label","legend","li","link","main","map","mark","marquee","menu","menuitem","meta","meter","nav","noscript","object","ol","optgroup","option","output","p","param","picture","pre","progress","q","rp","rt","ruby","s","samp","script","section","select","small","source","span","strong","style","sub","summary","sup","table","tbody","td","textarea","tfoot","th","thead","time","title","tr","track","u","ul","var","video","wbr","circle","clipPath","defs","ellipse","foreignObject","g","image","line","linearGradient","mask","path","pattern","polygon","polyline","radialGradient","rect","stop","svg","text","tspan"].forEach((function(e){Ne[e]=Ne(e)})),Ne(s.BaseControl)`
	width: 100%;
	& .llms-search-control__input:focus {
		box-shadow: none;
	}
	& .llms-search-control__menu {
		background: #fff !important;
		z-index: 9999999 !important;
	}
	& .llms-search-control__value-container {
		width: 100%;
	}
`,window.wp.coreData;var Fe=r(184),Be=r.n(Fe);const De=window.wp.blockEditor,He=window.wp.primitives;(0,e.registerBlockType)(t,{icon:()=>(0,n.createElement)(He.SVG,{className:"llms-block-icon",xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 384 512"},(0,n.createElement)(He.Path,{d:"M173.8 5.5c11-7.3 25.4-7.3 36.4 0L228 17.2c6 3.9 13 5.8 20.1 5.4l21.3-1.3c13.2-.8 25.6 6.4 31.5 18.2l9.6 19.1c3.2 6.4 8.4 11.5 14.7 14.7L344.5 83c11.8 5.9 19 18.3 18.2 31.5l-1.3 21.3c-.4 7.1 1.5 14.2 5.4 20.1l11.8 17.8c7.3 11 7.3 25.4 0 36.4L366.8 228c-3.9 6-5.8 13-5.4 20.1l1.3 21.3c.8 13.2-6.4 25.6-18.2 31.5l-19.1 9.6c-6.4 3.2-11.5 8.4-14.7 14.7L301 344.5c-5.9 11.8-18.3 19-31.5 18.2l-21.3-1.3c-7.1-.4-14.2 1.5-20.1 5.4l-17.8 11.8c-11 7.3-25.4 7.3-36.4 0L156 366.8c-6-3.9-13-5.8-20.1-5.4l-21.3 1.3c-13.2 .8-25.6-6.4-31.5-18.2l-9.6-19.1c-3.2-6.4-8.4-11.5-14.7-14.7L39.5 301c-11.8-5.9-19-18.3-18.2-31.5l1.3-21.3c.4-7.1-1.5-14.2-5.4-20.1L5.5 210.2c-7.3-11-7.3-25.4 0-36.4L17.2 156c3.9-6 5.8-13 5.4-20.1l-1.3-21.3c-.8-13.2 6.4-25.6 18.2-31.5l19.1-9.6C65 70.2 70.2 65 73.4 58.6L83 39.5c5.9-11.8 18.3-19 31.5-18.2l21.3 1.3c7.1 .4 14.2-1.5 20.1-5.4L173.8 5.5zM272 192a80 80 0 1 0 -160 0 80 80 0 1 0 160 0zM1.3 441.8L44.4 339.3c.2 .1 .3 .2 .4 .4l9.6 19.1c11.7 23.2 36 37.3 62 35.8l21.3-1.3c.2 0 .5 0 .7 .2l17.8 11.8c5.1 3.3 10.5 5.9 16.1 7.7l-37.6 89.3c-2.3 5.5-7.4 9.2-13.3 9.7s-11.6-2.2-14.8-7.2L74.4 455.5l-56.1 8.3c-5.7 .8-11.4-1.5-15-6s-4.3-10.7-2.1-16zm248 60.4L211.7 413c5.6-1.8 11-4.3 16.1-7.7l17.8-11.8c.2-.1 .4-.2 .7-.2l21.3 1.3c26 1.5 50.3-12.6 62-35.8l9.6-19.1c.1-.2 .2-.3 .4-.4l43.2 102.5c2.2 5.3 1.4 11.4-2.1 16s-9.3 6.9-15 6l-56.1-8.3-32.2 49.2c-3.2 5-8.9 7.7-14.8 7.2s-11-4.3-13.3-9.7z"})),edit:function(t){let{attributes:r,setAttributes:s,mergeBlocks:c,onReplace:l,style:u,clientId:d}=t;const{getBlockType:f}=(0,i.useSelect)(e.store),{getEditedPostAttribute:p,getCurrentPostType:h}=(0,i.useSelect)(o.store),{edit:m}=f("core/heading"),g="llms_certificate"===h()?"certificate_title":"title";return r.placeholder=r.placeholder||(0,a.__)("Certificate of Achievement","lifterlms"),r.content=r.content||p(g),(0,n.createElement)(n.Fragment,null,(0,n.createElement)(m,{attributes:r,setAttributes:e=>{const{content:t}=e;return void 0!==t&&function(e){let t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:null;if(!t){const{getCurrentPostType:e}=(0,i.select)(o.store);t=e()}const{editPost:r}=(0,i.dispatch)(o.store),n={};"llms_certificate"===t?n.certificate_title=e:"llms_my_certificate"===t&&(n.title=e),r(n)}(t),s(e)},mergeBlocks:c,onReplace:l,style:u,clientId:d,context:[]}))},save:function(e){let{attributes:t}=e;const{textAlign:r,content:a,level:i}=t,o="h"+i,s=Be()({[`has-text-align-${r}`]:r});return(0,n.createElement)(o,De.useBlockProps.save({className:s}),(0,n.createElement)(De.RichText.Content,{value:a}))}})})()})();