(()=>{"use strict";var e,t={751:()=>{const e=window.wp.blocks,t=window.wp.blockEditor,s=window.wp.components,i=window.wp.element,n=window.ReactJSXRuntime;(0,e.registerBlockType)("integlight/tab",{title:__("Tab","integlight"),parent:["integlight/tab-block"],icon:"screenoptions",category:"layout",attributes:{tabTitle:{type:"string",source:"html",selector:".tab-title h4",default:""}},edit:e=>{const{attributes:{tabTitle:s},setAttributes:i,className:l}=e,a=(0,t.useBlockProps)({className:"tab"});return(0,n.jsxs)("div",{...a,children:[(0,n.jsx)("div",{className:"tab-title",children:(0,n.jsx)(t.RichText,{tagName:"h4",placeholder:__("Tab title...","integlight"),value:s,onChange:e=>i({tabTitle:e})})}),(0,n.jsx)("div",{className:"tab-content",children:(0,n.jsx)(t.InnerBlocks,{})})]})},save:e=>{const{attributes:{tabTitle:s}}=e,i=t.useBlockProps.save({className:"wp-block-integlight-tab tab"});return(0,n.jsxs)("div",{...i,children:[(0,n.jsx)("div",{className:"tab-title",children:(0,n.jsx)(t.RichText.Content,{tagName:"h4",value:s})}),(0,n.jsx)("div",{className:"tab-content",children:(0,n.jsx)(t.InnerBlocks.Content,{})})]})}}),(0,e.registerBlockType)("integlight/tab-block",{edit:e=>{const l=(0,t.useBlockProps)({className:"tabs-block"});return(0,n.jsxs)(i.Fragment,{children:[(0,n.jsx)(t.InspectorControls,{children:(0,n.jsx)(s.PanelBody,{title:__("Tab setting","integlight"),initialOpen:!0})}),(0,n.jsxs)("div",{...l,children:[(0,n.jsx)("div",{className:"tabs-navigation-editor",children:(0,n.jsx)("p",{children:__("Tab switching is reflected when the website is displayed.","integlight")})}),(0,n.jsx)("div",{className:"tabs-content-editor",children:(0,n.jsx)(t.InnerBlocks,{allowedBlocks:["integlight/tab"],template:[["integlight/tab",{}]],templateLock:!1,renderAppender:t.InnerBlocks.ButtonBlockAppender})})]})]})},save:()=>{const e=t.useBlockProps.save({className:"tabs"});return(0,n.jsx)("div",{...e,children:(0,n.jsx)("div",{className:"tabs-content",children:(0,n.jsx)(t.InnerBlocks.Content,{})})})}})}},s={};function i(e){var n=s[e];if(void 0!==n)return n.exports;var l=s[e]={exports:{}};return t[e](l,l.exports,i),l.exports}i.m=t,e=[],i.O=(t,s,n,l)=>{if(!s){var a=1/0;for(d=0;d<e.length;d++){for(var[s,n,l]=e[d],r=!0,o=0;o<s.length;o++)(!1&l||a>=l)&&Object.keys(i.O).every((e=>i.O[e](s[o])))?s.splice(o--,1):(r=!1,l<a&&(a=l));if(r){e.splice(d--,1);var c=n();void 0!==c&&(t=c)}}return t}l=l||0;for(var d=e.length;d>0&&e[d-1][2]>l;d--)e[d]=e[d-1];e[d]=[s,n,l]},i.o=(e,t)=>Object.prototype.hasOwnProperty.call(e,t),(()=>{var e={57:0,350:0};i.O.j=t=>0===e[t];var t=(t,s)=>{var n,l,[a,r,o]=s,c=0;if(a.some((t=>0!==e[t]))){for(n in r)i.o(r,n)&&(i.m[n]=r[n]);if(o)var d=o(i)}for(t&&t(s);c<a.length;c++)l=a[c],i.o(e,l)&&e[l]&&e[l][0](),e[l]=0;return i.O(d)},s=globalThis.webpackChunk=globalThis.webpackChunk||[];s.forEach(t.bind(null,0)),s.push=t.bind(null,s.push.bind(s))})();var n=i.O(void 0,[350],(()=>i(751)));n=i.O(n)})();