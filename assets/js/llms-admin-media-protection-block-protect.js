/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "@wordpress/element":
/*!*********************************!*\
  !*** external ["wp","element"] ***!
  \*********************************/
/***/ ((module) => {

module.exports = window["wp"]["element"];

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
// This entry need to be wrapped in an IIFE because it need to be isolated against other modules in the chunk.
(() => {
/*!********************************************************!*\
  !*** ./src/js/admin-media-protection-block-protect.js ***!
  \********************************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);

(function (wp) {
  const {
    addFilter
  } = wp.hooks;
  const {
    createHigherOrderComponent
  } = wp.compose;
  const {
    Fragment,
    useState,
    useEffect,
    useRef
  } = wp.element;
  const {
    ToolbarButton,
    Modal,
    Button,
    Flex,
    FlexItem,
    Notice
  } = wp.components;
  const {
    BlockControls
  } = wp.blockEditor;
  const {
    apiFetch
  } = wp;
  const {
    sprintf
  } = wp.i18n;
  const supportedMediaBlocks = ['core/image', 'core/video', 'core/audio', 'core/file'];
  const getUrlAttr = blockName => {
    switch (blockName) {
      case 'core/image':
        return 'url';
      case 'core/audio':
        return 'src';
      case 'core/video':
        return 'src';
      case 'core/file':
        return 'href';
      default:
        return 'url';
    }
  };
  const withProtectImageToolbar = createHigherOrderComponent(BlockEdit => {
    return props => {
      const warningText = sprintf(LLMS.l10n.translate('This media is not protected. If you select a product here, the media will be moved to the protected uploads directory and existing links to the media will no longer work. %1$sLearn More%2$s'), '<a href="https://lifterlms.com/docs/how-protected-media-files-work/" target="_blank">', '</a>');

      // We don't have a media ID if "insert from URL" is used.
      if (!props.attributes || !props.attributes.id) {
        return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(BlockEdit, props);
      }
      if (!supportedMediaBlocks.includes(props.name)) {
        return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(BlockEdit, props);
      }
      const [isModalOpen, setModalOpen] = useState(false);
      const [selectedId, setSelectedId] = useState(null);
      const selectRef = useRef(null);
      useEffect(() => {
        if (isModalOpen && selectRef.current) {
          jQuery(selectRef.current).llmsPostsSelect2();
        }
      }, [isModalOpen]);
      const handleProtectImage = () => {
        const selectedId = jQuery(selectRef.current).val();
        apiFetch({
          path: `/wp/v2/media/${props.attributes.id}`,
          method: 'POST',
          data: {
            _llms_media_protection_product_id: selectedId
          }
        }).then(updatedMedia => {
          const urlAttr = getUrlAttr(props.name);
          props.setAttributes({
            [urlAttr]: updatedMedia.source_url
          });
        }).catch(err => {
          console.error('Error updating media meta:', err);
        });
        setModalOpen(false);
      };
      return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(Fragment, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(BlockEdit, props), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(BlockControls, {
        group: "inline"
      }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(ToolbarButton, {
        icon: "lock",
        label: "Protect Image",
        onClick: () => setModalOpen(true)
      })), isModalOpen && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(Modal, {
        title: LLMS.l10n.translate('Select Course or Membership'),
        onRequestClose: () => setModalOpen(false)
      }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(Flex, {
        direction: "column",
        gap: 4
      }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(FlexItem, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
        htmlFor: "llms-protect-image-select"
      }, LLMS.l10n.translate('Select a Course or Membership to protect this image:'))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(FlexItem, null, !props.attributes[getUrlAttr(props.name)].includes('llms_media_id') && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(Notice, {
        status: "warning",
        isDismissible: false
      }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", {
        dangerouslySetInnerHTML: {
          __html: warningText
        }
      })), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("select", {
        id: "llms-protect-image-select",
        ref: selectRef,
        className: "llms-block-protect llms-posts-select2",
        "data-no-view-button": "true",
        "data-allow_clear": "false",
        "data-post-type": "course,llms_membership"
      })), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(FlexItem, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(Button, {
        isPrimary: true,
        onClick: () => {
          handleProtectImage();
        }
      }, "Protect Image")))));
    };
  }, 'withProtectImageToolbar');
  addFilter('editor.BlockEdit', 'my-plugin/with-protect-image-toolbar', withProtectImageToolbar);
})(window.wp);
})();

/******/ })()
;
//# sourceMappingURL=llms-admin-media-protection-block-protect.js.map