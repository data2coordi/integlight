(()=>{"use strict";const e=window.ReactJSXRuntime,{Fragment:t,useState:s,useEffect:o}=wp.element,{registerFormatType:n,insert:a}=wp.richText,{RichTextToolbarButton:c}=wp.blockEditor,{Modal:l,Button:r,TextControl:i,Spinner:m}=wp.components,h=({value:n,onChange:h})=>{const[p,x]=s(!1),[d,g]=s(""),[w,f]=s(null),[j,u]=s(!1);return o((()=>{p&&!w&&(u(!0),fetch("/wp-content/themes/integlight/blocks/gfontawesome/fontawesome-icons.json").then((e=>e.json())).then((e=>{f(e),u(!1)})).catch((e=>{console.error("アイコン取得エラー:",e),u(!1)})))}),[p,w]),(0,e.jsxs)(t,{children:[(0,e.jsx)(c,{icon:"search",title:"Font Awesome Icon Search",onClick:()=>x(!0)}),p&&(0,e.jsxs)(l,{title:"Font Awesome Icon Search",onRequestClose:()=>x(!1),className:"gfontawesome-modal",children:[(0,e.jsx)(i,{label:"Search",value:d,onChange:e=>g(e),placeholder:"ex): home, user, cog..."}),j?(0,e.jsx)(m,{}):(0,e.jsx)("div",{className:"gfontawesome-categories",children:w?Object.entries(w).map((([t,s])=>{const o=s.filter((e=>e.toLowerCase().includes(d.toLowerCase())));return(0,e.jsxs)("div",{className:"gfontawesome-category",children:[(0,e.jsx)("h3",{style:{marginTop:"20px",textTransform:"capitalize"},children:t.replace(/_/g," ")}),o.length>0?(0,e.jsx)("div",{className:"fa-icons-grid",style:{display:"flex",flexWrap:"wrap",gap:"10px",marginBottom:"20px"},children:o.map((t=>(0,e.jsx)(r,{onClick:()=>(e=>{const t=a(n,`[fontawesome icon="${e}"]`);h(t),x(!1)})(t),style:{padding:"10px"},children:(0,e.jsx)("i",{className:`fas ${t}`,style:{fontSize:"24px"}})},t)))}):(0,e.jsx)("p",{style:{marginLeft:"10px"},children:"アイコンが見つかりません"})]},t)})):(0,e.jsx)("p",{children:"アイコンデータがありません"})})]})]})};n("fontawesome/icon",{title:"Font Awesome",tagName:"span",className:"gfontawesome-shortcode",edit:t=>(0,e.jsx)(h,{...t})})})();