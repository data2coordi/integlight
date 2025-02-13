/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "react/jsx-runtime":
/*!**********************************!*\
  !*** external "ReactJSXRuntime" ***!
  \**********************************/
/***/ ((module) => {

module.exports = window["ReactJSXRuntime"];

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry needs to be wrapped in an IIFE because it needs to be isolated against other modules in the chunk.
(() => {
/*!**********************!*\
  !*** ./src/index.js ***!
  \**********************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__);

const {
  Fragment,
  useState,
  useEffect
} = wp.element;
const {
  registerFormatType,
  insert
} = wp.richText;
const {
  RichTextToolbarButton
} = wp.blockEditor;
const {
  Modal,
  Button,
  TextControl,
  Spinner
} = wp.components;
const FontAwesomeSearchButton = ({
  value,
  onChange
}) => {
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [searchTerm, setSearchTerm] = useState('');
  const [icons, setIcons] = useState([]);
  const [loading, setLoading] = useState(false);
  useEffect(() => {
    if (isModalOpen && icons.length === 0) {
      setLoading(true);
      fetch('/wp-content/themes/integlight/blocks/gfontawesome/fontawesome-icons.json').then(response => response.json()).then(data => {
        setIcons(data.icons);
        setLoading(false);
      }).catch(error => {
        console.error('アイコン取得エラー:', error);
        setLoading(false);
      });
    }
  }, [isModalOpen]);
  const filteredIcons = icons.filter(icon => icon.toLowerCase().includes(searchTerm.toLowerCase()));

  // 選択されたアイコンをショートコード形式で挿入する（例: [fontawesome icon="fa-home"]）
  const insertIcon = icon => {
    const shortcode = `[fa icon="${icon}"]`;
    const newValue = insert(value, shortcode);
    onChange(newValue);
    setIsModalOpen(false);
  };
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsxs)(Fragment, {
    children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)(RichTextToolbarButton, {
      icon: "search",
      title: "Font Awesome \u30A2\u30A4\u30B3\u30F3\u3092\u691C\u7D22",
      onClick: () => setIsModalOpen(true)
    }), isModalOpen && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsxs)(Modal, {
      title: "Font Awesome \u30A2\u30A4\u30B3\u30F3\u3092\u691C\u7D22",
      onRequestClose: () => setIsModalOpen(false),
      children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)(TextControl, {
        label: "\u691C\u7D22",
        value: searchTerm,
        onChange: newValue => setSearchTerm(newValue),
        placeholder: "\u4F8B: home, user, cog..."
      }), loading ? /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)(Spinner, {}) : /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)("div", {
        className: "fa-icons-grid",
        style: {
          display: 'flex',
          flexWrap: 'wrap',
          gap: '10px',
          marginTop: '10px'
        },
        children: filteredIcons.map((icon, index) => /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)(Button, {
          onClick: () => insertIcon(icon),
          style: {
            padding: '10px'
          },
          children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)("i", {
            className: `fas ${icon}`,
            style: {
              fontSize: '24px'
            }
          })
        }, index))
      })]
    })]
  });
};
registerFormatType('fontawesome/icon', {
  title: 'Font Awesome',
  tagName: 'span',
  // 独自のクラス名を付与することで衝突を回避
  className: 'gfontawesome-shortcode',
  edit: props => /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)(FontAwesomeSearchButton, {
    ...props
  })
});
})();

/******/ })()
;
//# sourceMappingURL=index.js.map