(()=>{"use strict";const e=window.wp.element,{registerBlockType:t}=wp.blocks,{RichText:n,InspectorControls:l,ColorPalette:o,FontSizePicker:a}=wp.blockEditor||wp.editor,{PanelBody:i}=wp.components;t("integlight/text-flow-animation",{title:"【Integlight】テキスト流れるアニメーション",icon:"editor-alignleft",category:"widgets",attributes:{content:{type:"string",source:"html",selector:"p"},color:{type:"string",default:"#000000"},fontSize:{type:"number",default:16}},edit:({attributes:t,setAttributes:r,isSelected:c})=>{const{content:s,color:m,fontSize:u}=t,p=c?"text-flow-animation edit-mode":"text-flow-animation";return(0,e.createElement)(e.Fragment,null,(0,e.createElement)(l,null,(0,e.createElement)(i,{title:"テキスト設定"},(0,e.createElement)("p",null,"テキストカラー"),(0,e.createElement)(o,{value:m,onChange:e=>r({color:e||"#000000"})}),(0,e.createElement)("p",null,"フォントサイズ"),(0,e.createElement)(a,{value:u||16,onChange:e=>{null!==e&&r({fontSize:e})},min:10,max:100}))),(0,e.createElement)(n,{tagName:"p",className:p,style:{color:m,fontSize:u},value:s,onChange:e=>r({content:e}),placeholder:"ここにテキストを入力…"}))},save:({attributes:t})=>(0,e.createElement)("div",{className:"text-flow-animation-container"},(0,e.createElement)("div",{className:"text-flow-animation",style:{color:t.color,fontSize:t.fontSize}},t.content))})})();