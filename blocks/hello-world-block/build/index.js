(()=>{"use strict";const e=window.wp.element,o=window.wp.blocks,l=window.wp.blockEditor;(0,o.registerBlockType)("integlight/hello-world-block",{title:"Hello World Block",icon:"smiley",category:"widgets",edit:()=>{const o=(0,l.useBlockProps)();return(0,e.createElement)("p",o,"Hello, World!")},save:()=>{const o=l.useBlockProps.save();return(0,e.createElement)("p",o,"Hello, World!")}})})();