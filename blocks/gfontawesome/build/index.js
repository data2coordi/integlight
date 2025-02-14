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
  // JSON 全体（各カテゴリごとのオブジェクト）を保持する変数
  const [iconsData, setIconsData] = useState(null);
  const [loading, setLoading] = useState(false);
  useEffect(() => {
    if (isModalOpen && !iconsData) {
      setLoading(true);
      fetch('/wp-content/themes/integlight/blocks/gfontawesome/fontawesome-icons.json').then(response => response.json()).then(data => {
        setIconsData(data);
        setLoading(false);
      }).catch(error => {
        console.error('アイコン取得エラー:', error);
        setLoading(false);
      });
    }
  }, [isModalOpen, iconsData]);
  const insertIcon = icon => {
    const shortcode = `[fontawesome icon="${icon}"]`;
    const newValue = insert(value, shortcode);
    onChange(newValue);
    setIsModalOpen(false);
  };
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsxs)(Fragment, {
    children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)(RichTextToolbarButton, {
      icon: "search",
      title: "Font Awesome Icon Search",
      onClick: () => setIsModalOpen(true)
    }), isModalOpen && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsxs)(Modal, {
      title: "Font Awesome Icon Search",
      onRequestClose: () => setIsModalOpen(false),
      className: "gfontawesome-modal",
      children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)(TextControl, {
        label: "Search",
        value: searchTerm,
        onChange: newValue => setSearchTerm(newValue),
        placeholder: "ex): home, user, cog..."
      }), loading ? /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)(Spinner, {}) : /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)("div", {
        className: "gfontawesome-categories",
        children: iconsData ? Object.entries(iconsData).map(([category, iconList]) => {
          // 入力された検索語句でフィルタ
          const filteredList = iconList.filter(icon => icon.toLowerCase().includes(searchTerm.toLowerCase()));
          return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsxs)("div", {
            className: "gfontawesome-category",
            children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)("h3", {
              style: {
                marginTop: '20px',
                textTransform: 'capitalize'
              },
              children: category.replace(/_/g, ' ')
            }), filteredList.length > 0 ? /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)("div", {
              className: "fa-icons-grid",
              style: {
                display: 'flex',
                flexWrap: 'wrap',
                gap: '10px',
                marginBottom: '20px'
              },
              children: filteredList.map(icon => /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)(Button, {
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
              }, icon))
            }) : /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)("p", {
              style: {
                marginLeft: '10px'
              },
              children: "\u30A2\u30A4\u30B3\u30F3\u304C\u898B\u3064\u304B\u308A\u307E\u305B\u3093"
            })]
          }, category);
        }) : /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)("p", {
          children: "\u30A2\u30A4\u30B3\u30F3\u30C7\u30FC\u30BF\u304C\u3042\u308A\u307E\u305B\u3093"
        })
      })]
    })]
  });
};
registerFormatType('fontawesome/icon', {
  title: 'Font Awesome',
  tagName: 'span',
  className: 'gfontawesome-shortcode',
  edit: props => /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)(FontAwesomeSearchButton, {
    ...props
  })
});
})();

/******/ })()
;
//# sourceMappingURL=index.js.map