/******/ (function() { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./src/scss/admin-addons.scss":
/*!************************************!*\
  !*** ./src/scss/admin-addons.scss ***!
  \************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "jquery":
/*!*************************!*\
  !*** external "jQuery" ***!
  \*************************/
/***/ (function(module) {

module.exports = window["jQuery"];

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
/******/ 	!function() {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = function(module) {
/******/ 			var getter = module && module.__esModule ?
/******/ 				function() { return module['default']; } :
/******/ 				function() { return module; };
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	!function() {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = function(exports, definition) {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	!function() {
/******/ 		__webpack_require__.o = function(obj, prop) { return Object.prototype.hasOwnProperty.call(obj, prop); }
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	!function() {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = function(exports) {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	}();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be isolated against other modules in the chunk.
!function() {
/*!********************************!*\
  !*** ./src/js/admin-addons.js ***!
  \********************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! jquery */ "jquery");
/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(jquery__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _scss_admin_addons_scss__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../scss/admin-addons.scss */ "./src/scss/admin-addons.scss");
/**
 * UI & UX for the Admin add-ons management screen
 *
 * @package LifterLMS/Scripts
 *
 * @since 3.22.0
 * @version [version]
 */



(function () {
  /**
   * Tracks current # of each bulk action to be run upon form submission
   *
   * @type {Object}
   */
  var actions = {
    update: 0,
    install: 0,
    activate: 0,
    deactivate: 0
  };
  /**
   * When the bulk action modal is closed, clear all existing staged actions
   *
   * @since 3.22.0
   */

  jquery__WEBPACK_IMPORTED_MODULE_0___default()('.llms-bulk-close').on('click', function (e) {
    e.preventDefault();
    jquery__WEBPACK_IMPORTED_MODULE_0___default()('input.llms-bulk-check').filter(':checked').prop('checked', false).trigger('change');
  });
  /**
   * Update the UI and counters when a checkbox action is changed
   *
   * @since 3.22.0
   */

  jquery__WEBPACK_IMPORTED_MODULE_0___default()('input.llms-bulk-check').on('change', function () {
    var action = jquery__WEBPACK_IMPORTED_MODULE_0___default()(this).attr('data-action');

    if (jquery__WEBPACK_IMPORTED_MODULE_0___default()(this).is(':checked')) {
      actions[action]++;
    } else {
      actions[action]--;
    }

    update_ui();
  });
  /**
   * Updates the UI when bulk actions are changed
   * Shows # of each action to be applied & shows the form submission / cancel buttons
   *
   * @since 3.22.0
   *
   * @return void
   */

  function update_ui() {
    var $el = jquery__WEBPACK_IMPORTED_MODULE_0___default()('#llms-addons-bulk-actions');

    if (actions.update || actions.install || actions.activate || actions.deactivate) {
      $el.addClass('active');
    } else {
      $el.removeClass('active');
    }

    jquery__WEBPACK_IMPORTED_MODULE_0___default().each(actions, function (key, count) {
      var html = '',
          $desc = $el.find('.llms-bulk-desc.' + key);

      if (actions[key]) {
        if (actions[key] > 1) {
          html = LLMS.l10n.replace('%d add-ons', {
            '%d': actions[key]
          });
        } else {
          html = LLMS.l10n.translate('1 add-on');
        }

        $desc.show();
      } else {
        $desc.hide();
      }

      $desc.find('span').html(html);
    });
  }
  /**
   * Show the keys management dropdown on click of the "My License Keys" button
   *
   * @since 3.22.0
   */


  jquery__WEBPACK_IMPORTED_MODULE_0___default()('#llms-active-keys-toggle').on('click', function () {
    jquery__WEBPACK_IMPORTED_MODULE_0___default()('#llms-key-field-form').toggle();
  });
})();
}();
/******/ })()
;
//# sourceMappingURL=llms-admin-addons.js.map