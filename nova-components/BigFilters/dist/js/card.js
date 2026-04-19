/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, {
/******/ 				configurable: false,
/******/ 				enumerable: true,
/******/ 				get: getter
/******/ 			});
/******/ 		}
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 6);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
/***/ (function(module, exports) {

/* globals __VUE_SSR_CONTEXT__ */

// IMPORTANT: Do NOT use ES2015 features in this file.
// This module is a runtime utility for cleaner component module output and will
// be included in the final webpack user bundle.

module.exports = function normalizeComponent (
  rawScriptExports,
  compiledTemplate,
  functionalTemplate,
  injectStyles,
  scopeId,
  moduleIdentifier /* server only */
) {
  var esModule
  var scriptExports = rawScriptExports = rawScriptExports || {}

  // ES6 modules interop
  var type = typeof rawScriptExports.default
  if (type === 'object' || type === 'function') {
    esModule = rawScriptExports
    scriptExports = rawScriptExports.default
  }

  // Vue.extend constructor export interop
  var options = typeof scriptExports === 'function'
    ? scriptExports.options
    : scriptExports

  // render functions
  if (compiledTemplate) {
    options.render = compiledTemplate.render
    options.staticRenderFns = compiledTemplate.staticRenderFns
    options._compiled = true
  }

  // functional template
  if (functionalTemplate) {
    options.functional = true
  }

  // scopedId
  if (scopeId) {
    options._scopeId = scopeId
  }

  var hook
  if (moduleIdentifier) { // server build
    hook = function (context) {
      // 2.3 injection
      context =
        context || // cached call
        (this.$vnode && this.$vnode.ssrContext) || // stateful
        (this.parent && this.parent.$vnode && this.parent.$vnode.ssrContext) // functional
      // 2.2 with runInNewContext: true
      if (!context && typeof __VUE_SSR_CONTEXT__ !== 'undefined') {
        context = __VUE_SSR_CONTEXT__
      }
      // inject component styles
      if (injectStyles) {
        injectStyles.call(this, context)
      }
      // register component module identifier for async chunk inferrence
      if (context && context._registeredComponents) {
        context._registeredComponents.add(moduleIdentifier)
      }
    }
    // used by ssr in case component is cached and beforeCreate
    // never gets called
    options._ssrRegister = hook
  } else if (injectStyles) {
    hook = injectStyles
  }

  if (hook) {
    var functional = options.functional
    var existing = functional
      ? options.render
      : options.beforeCreate

    if (!functional) {
      // inject component registration as beforeCreate hook
      options.beforeCreate = existing
        ? [].concat(existing, hook)
        : [hook]
    } else {
      // for template-only hot-reload because in that case the render fn doesn't
      // go through the normalizer
      options._injectStyles = hook
      // register for functioal component in vue file
      options.render = function renderWithStyleInjection (h, context) {
        hook.call(context)
        return existing(h, context)
      }
    }
  }

  return {
    esModule: esModule,
    exports: scriptExports,
    options: options
  }
}


/***/ }),
/* 1 */
/***/ (function(module, exports) {

/*
	MIT License http://www.opensource.org/licenses/mit-license.php
	Author Tobias Koppers @sokra
*/
// css base code, injected by the css-loader
module.exports = function(useSourceMap) {
	var list = [];

	// return the list of modules as css string
	list.toString = function toString() {
		return this.map(function (item) {
			var content = cssWithMappingToString(item, useSourceMap);
			if(item[2]) {
				return "@media " + item[2] + "{" + content + "}";
			} else {
				return content;
			}
		}).join("");
	};

	// import a list of modules into the list
	list.i = function(modules, mediaQuery) {
		if(typeof modules === "string")
			modules = [[null, modules, ""]];
		var alreadyImportedModules = {};
		for(var i = 0; i < this.length; i++) {
			var id = this[i][0];
			if(typeof id === "number")
				alreadyImportedModules[id] = true;
		}
		for(i = 0; i < modules.length; i++) {
			var item = modules[i];
			// skip already imported module
			// this implementation is not 100% perfect for weird media query combinations
			//  when a module is imported multiple times with different media queries.
			//  I hope this will never occur (Hey this way we have smaller bundles)
			if(typeof item[0] !== "number" || !alreadyImportedModules[item[0]]) {
				if(mediaQuery && !item[2]) {
					item[2] = mediaQuery;
				} else if(mediaQuery) {
					item[2] = "(" + item[2] + ") and (" + mediaQuery + ")";
				}
				list.push(item);
			}
		}
	};
	return list;
};

function cssWithMappingToString(item, useSourceMap) {
	var content = item[1] || '';
	var cssMapping = item[3];
	if (!cssMapping) {
		return content;
	}

	if (useSourceMap && typeof btoa === 'function') {
		var sourceMapping = toComment(cssMapping);
		var sourceURLs = cssMapping.sources.map(function (source) {
			return '/*# sourceURL=' + cssMapping.sourceRoot + source + ' */'
		});

		return [content].concat(sourceURLs).concat([sourceMapping]).join('\n');
	}

	return [content].join('\n');
}

// Adapted from convert-source-map (MIT)
function toComment(sourceMap) {
	// eslint-disable-next-line no-undef
	var base64 = btoa(unescape(encodeURIComponent(JSON.stringify(sourceMap))));
	var data = 'sourceMappingURL=data:application/json;charset=utf-8;base64,' + base64;

	return '/*# ' + data + ' */';
}


/***/ }),
/* 2 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "a", function() { return HOOKS; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "b", function() { return defaults; });
var HOOKS = [
    "onChange",
    "onClose",
    "onDayCreate",
    "onDestroy",
    "onKeyDown",
    "onMonthChange",
    "onOpen",
    "onParseConfig",
    "onReady",
    "onValueUpdate",
    "onYearChange",
    "onPreCalendarPosition",
];
var defaults = {
    _disable: [],
    allowInput: false,
    allowInvalidPreload: false,
    altFormat: "F j, Y",
    altInput: false,
    altInputClass: "form-control input",
    animate: typeof window === "object" &&
        window.navigator.userAgent.indexOf("MSIE") === -1,
    ariaDateFormat: "F j, Y",
    autoFillDefaultTime: true,
    clickOpens: true,
    closeOnSelect: true,
    conjunction: ", ",
    dateFormat: "Y-m-d",
    defaultHour: 12,
    defaultMinute: 0,
    defaultSeconds: 0,
    disable: [],
    disableMobile: false,
    enableSeconds: false,
    enableTime: false,
    errorHandler: function (err) {
        return typeof console !== "undefined" && console.warn(err);
    },
    getWeek: function (givenDate) {
        var date = new Date(givenDate.getTime());
        date.setHours(0, 0, 0, 0);
        date.setDate(date.getDate() + 3 - ((date.getDay() + 6) % 7));
        var week1 = new Date(date.getFullYear(), 0, 4);
        return (1 +
            Math.round(((date.getTime() - week1.getTime()) / 86400000 -
                3 +
                ((week1.getDay() + 6) % 7)) /
                7));
    },
    hourIncrement: 1,
    ignoredFocusElements: [],
    inline: false,
    locale: "default",
    minuteIncrement: 5,
    mode: "single",
    monthSelectorType: "dropdown",
    nextArrow: "<svg version='1.1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' viewBox='0 0 17 17'><g></g><path d='M13.207 8.472l-7.854 7.854-0.707-0.707 7.146-7.146-7.146-7.148 0.707-0.707 7.854 7.854z' /></svg>",
    noCalendar: false,
    now: new Date(),
    onChange: [],
    onClose: [],
    onDayCreate: [],
    onDestroy: [],
    onKeyDown: [],
    onMonthChange: [],
    onOpen: [],
    onParseConfig: [],
    onReady: [],
    onValueUpdate: [],
    onYearChange: [],
    onPreCalendarPosition: [],
    plugins: [],
    position: "auto",
    positionElement: undefined,
    prevArrow: "<svg version='1.1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' viewBox='0 0 17 17'><g></g><path d='M5.207 8.471l7.146 7.147-0.707 0.707-7.853-7.854 7.854-7.853 0.707 0.707-7.147 7.146z' /></svg>",
    shorthandCurrentMonth: false,
    showMonths: 1,
    static: false,
    time_24hr: false,
    weekNumbers: false,
    wrap: false,
};


/***/ }),
/* 3 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "b", function() { return english; });
var english = {
    weekdays: {
        shorthand: ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"],
        longhand: [
            "Sunday",
            "Monday",
            "Tuesday",
            "Wednesday",
            "Thursday",
            "Friday",
            "Saturday",
        ],
    },
    months: {
        shorthand: [
            "Jan",
            "Feb",
            "Mar",
            "Apr",
            "May",
            "Jun",
            "Jul",
            "Aug",
            "Sep",
            "Oct",
            "Nov",
            "Dec",
        ],
        longhand: [
            "January",
            "February",
            "March",
            "April",
            "May",
            "June",
            "July",
            "August",
            "September",
            "October",
            "November",
            "December",
        ],
    },
    daysInMonth: [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31],
    firstDayOfWeek: 0,
    ordinal: function (nth) {
        var s = nth % 100;
        if (s > 3 && s < 21)
            return "th";
        switch (s % 10) {
            case 1:
                return "st";
            case 2:
                return "nd";
            case 3:
                return "rd";
            default:
                return "th";
        }
    },
    rangeSeparator: " to ",
    weekAbbreviation: "Wk",
    scrollTitle: "Scroll to increment",
    toggleTitle: "Click to toggle",
    amPM: ["AM", "PM"],
    yearAriaLabel: "Year",
    monthAriaLabel: "Month",
    hourAriaLabel: "Hour",
    minuteAriaLabel: "Minute",
    time_24hr: false,
};
/* harmony default export */ __webpack_exports__["a"] = (english);


/***/ }),
/* 4 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "d", function() { return pad; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "c", function() { return int; });
/* harmony export (immutable) */ __webpack_exports__["b"] = debounce;
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "a", function() { return arrayify; });
var pad = function (number, length) {
    if (length === void 0) { length = 2; }
    return ("000" + number).slice(length * -1);
};
var int = function (bool) { return (bool === true ? 1 : 0); };
function debounce(fn, wait) {
    var t;
    return function () {
        var _this = this;
        var args = arguments;
        clearTimeout(t);
        t = setTimeout(function () { return fn.apply(_this, args); }, wait);
    };
}
var arrayify = function (obj) {
    return obj instanceof Array ? obj : [obj];
};


/***/ }),
/* 5 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "b", function() { return monthToStr; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "c", function() { return revFormat; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "d", function() { return tokenRegex; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "a", function() { return formats; });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__utils__ = __webpack_require__(4);

var doNothing = function () { return undefined; };
var monthToStr = function (monthNumber, shorthand, locale) { return locale.months[shorthand ? "shorthand" : "longhand"][monthNumber]; };
var revFormat = {
    D: doNothing,
    F: function (dateObj, monthName, locale) {
        dateObj.setMonth(locale.months.longhand.indexOf(monthName));
    },
    G: function (dateObj, hour) {
        dateObj.setHours((dateObj.getHours() >= 12 ? 12 : 0) + parseFloat(hour));
    },
    H: function (dateObj, hour) {
        dateObj.setHours(parseFloat(hour));
    },
    J: function (dateObj, day) {
        dateObj.setDate(parseFloat(day));
    },
    K: function (dateObj, amPM, locale) {
        dateObj.setHours((dateObj.getHours() % 12) +
            12 * Object(__WEBPACK_IMPORTED_MODULE_0__utils__["c" /* int */])(new RegExp(locale.amPM[1], "i").test(amPM)));
    },
    M: function (dateObj, shortMonth, locale) {
        dateObj.setMonth(locale.months.shorthand.indexOf(shortMonth));
    },
    S: function (dateObj, seconds) {
        dateObj.setSeconds(parseFloat(seconds));
    },
    U: function (_, unixSeconds) { return new Date(parseFloat(unixSeconds) * 1000); },
    W: function (dateObj, weekNum, locale) {
        var weekNumber = parseInt(weekNum);
        var date = new Date(dateObj.getFullYear(), 0, 2 + (weekNumber - 1) * 7, 0, 0, 0, 0);
        date.setDate(date.getDate() - date.getDay() + locale.firstDayOfWeek);
        return date;
    },
    Y: function (dateObj, year) {
        dateObj.setFullYear(parseFloat(year));
    },
    Z: function (_, ISODate) { return new Date(ISODate); },
    d: function (dateObj, day) {
        dateObj.setDate(parseFloat(day));
    },
    h: function (dateObj, hour) {
        dateObj.setHours((dateObj.getHours() >= 12 ? 12 : 0) + parseFloat(hour));
    },
    i: function (dateObj, minutes) {
        dateObj.setMinutes(parseFloat(minutes));
    },
    j: function (dateObj, day) {
        dateObj.setDate(parseFloat(day));
    },
    l: doNothing,
    m: function (dateObj, month) {
        dateObj.setMonth(parseFloat(month) - 1);
    },
    n: function (dateObj, month) {
        dateObj.setMonth(parseFloat(month) - 1);
    },
    s: function (dateObj, seconds) {
        dateObj.setSeconds(parseFloat(seconds));
    },
    u: function (_, unixMillSeconds) {
        return new Date(parseFloat(unixMillSeconds));
    },
    w: doNothing,
    y: function (dateObj, year) {
        dateObj.setFullYear(2000 + parseFloat(year));
    },
};
var tokenRegex = {
    D: "",
    F: "",
    G: "(\\d\\d|\\d)",
    H: "(\\d\\d|\\d)",
    J: "(\\d\\d|\\d)\\w+",
    K: "",
    M: "",
    S: "(\\d\\d|\\d)",
    U: "(.+)",
    W: "(\\d\\d|\\d)",
    Y: "(\\d{4})",
    Z: "(.+)",
    d: "(\\d\\d|\\d)",
    h: "(\\d\\d|\\d)",
    i: "(\\d\\d|\\d)",
    j: "(\\d\\d|\\d)",
    l: "",
    m: "(\\d\\d|\\d)",
    n: "(\\d\\d|\\d)",
    s: "(\\d\\d|\\d)",
    u: "(.+)",
    w: "(\\d\\d|\\d)",
    y: "(\\d{2})",
};
var formats = {
    Z: function (date) { return date.toISOString(); },
    D: function (date, locale, options) {
        return locale.weekdays.shorthand[formats.w(date, locale, options)];
    },
    F: function (date, locale, options) {
        return monthToStr(formats.n(date, locale, options) - 1, false, locale);
    },
    G: function (date, locale, options) {
        return Object(__WEBPACK_IMPORTED_MODULE_0__utils__["d" /* pad */])(formats.h(date, locale, options));
    },
    H: function (date) { return Object(__WEBPACK_IMPORTED_MODULE_0__utils__["d" /* pad */])(date.getHours()); },
    J: function (date, locale) {
        return locale.ordinal !== undefined
            ? date.getDate() + locale.ordinal(date.getDate())
            : date.getDate();
    },
    K: function (date, locale) { return locale.amPM[Object(__WEBPACK_IMPORTED_MODULE_0__utils__["c" /* int */])(date.getHours() > 11)]; },
    M: function (date, locale) {
        return monthToStr(date.getMonth(), true, locale);
    },
    S: function (date) { return Object(__WEBPACK_IMPORTED_MODULE_0__utils__["d" /* pad */])(date.getSeconds()); },
    U: function (date) { return date.getTime() / 1000; },
    W: function (date, _, options) {
        return options.getWeek(date);
    },
    Y: function (date) { return Object(__WEBPACK_IMPORTED_MODULE_0__utils__["d" /* pad */])(date.getFullYear(), 4); },
    d: function (date) { return Object(__WEBPACK_IMPORTED_MODULE_0__utils__["d" /* pad */])(date.getDate()); },
    h: function (date) { return (date.getHours() % 12 ? date.getHours() % 12 : 12); },
    i: function (date) { return Object(__WEBPACK_IMPORTED_MODULE_0__utils__["d" /* pad */])(date.getMinutes()); },
    j: function (date) { return date.getDate(); },
    l: function (date, locale) {
        return locale.weekdays.longhand[date.getDay()];
    },
    m: function (date) { return Object(__WEBPACK_IMPORTED_MODULE_0__utils__["d" /* pad */])(date.getMonth() + 1); },
    n: function (date) { return date.getMonth() + 1; },
    s: function (date) { return date.getSeconds(); },
    u: function (date) { return date.getTime(); },
    w: function (date) { return date.getDay(); },
    y: function (date) { return String(date.getFullYear()).substring(2); },
};


/***/ }),
/* 6 */
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(7);
module.exports = __webpack_require__(26);


/***/ }),
/* 7 */
/***/ (function(module, exports, __webpack_require__) {

Nova.booting(function (Vue, router, store) {
    Vue.component('big-filters', __webpack_require__(8));
});

/***/ }),
/* 8 */
/***/ (function(module, exports, __webpack_require__) {

var disposed = false
var normalizeComponent = __webpack_require__(0)
/* script */
var __vue_script__ = __webpack_require__(9)
/* template */
var __vue_template__ = __webpack_require__(25)
/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = null
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __vue_script__,
  __vue_template__,
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "resources/js/components/Card.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-b9bc2c0a", Component.options)
  } else {
    hotAPI.reload("data-v-b9bc2c0a", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

module.exports = Component.exports


/***/ }),
/* 9 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__custom_DateRangeFilter__ = __webpack_require__(10);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__custom_DateRangeFilter___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0__custom_DateRangeFilter__);
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//


/* harmony default export */ __webpack_exports__["default"] = ({
    components: {
        DateRangeFilter: __WEBPACK_IMPORTED_MODULE_0__custom_DateRangeFilter___default.a
    },
    props: {
        card: {
            filterMenuTitle: String,
            filterMaxHeight: Number,
            filterHideTitle: {
                type: Boolean,
                default: false
            }
        },
        resourceName: String,
        softDeletes: Boolean,
        viaResource: String,
        viaHasOne: Boolean,
        trashed: {
            type: String,
            validator: function validator(value) {
                return ['', 'with', 'only'].indexOf(value) != -1;
            }
        },
        perPage: [String, Number],
        showTrashedOption: {
            type: Boolean,
            default: true
        }
    },
    methods: {
        trashedChanged: function trashedChanged(event) {
            this.$emit('trashed-changed', event.target.value);
        },
        perPageChanged: function perPageChanged(event) {
            this.$emit('per-page-changed', event.target.value);
        },
        resetFilters: function resetFilters() {
            Nova.$emit('occupied-reset-filter');
            // Emit Bus Event to handle custom filters
            _.map(this.filters, function (filter) {
                return filter.currentValue = '';
            });
        }
    },

    computed: {
        /**
         * Return the filters from state
         */
        filters: function filters() {
            return this.$store.getters[this.resourceName + '/filters'];
        },


        /**
         * Determine via state whether filters are applied
         */
        filtersAreApplied: function filtersAreApplied() {
            return this.$store.getters[this.resourceName + '/filtersAreApplied'];
        },


        /**
         * Return the number of active filters
         */
        activeFilterCount: function activeFilterCount() {
            return this.$store.getters[this.resourceName + '/activeFilterCount'];
        },
        encodedFilters: function encodedFilters() {
            return this.$store.getters[this.resourceName + '/currentEncodedFilters'];
        },
        filterRows: function filterRows() {
            if (this.filters.length > 3) {
                return _.chunk(this.filters, 3);
            } else {
                return [this.filters];
            }
        }
    },
    mounted: function mounted() {
        var _this = this;

        this.$on('filter-changed', function () {
            _this.$refs.totalOccupied.getTotal(_this.encodedFilters);
        });
    }
});

/***/ }),
/* 10 */
/***/ (function(module, exports, __webpack_require__) {

var disposed = false
function injectStyle (ssrContext) {
  if (disposed) return
  __webpack_require__(11)
}
var normalizeComponent = __webpack_require__(0)
/* script */
var __vue_script__ = __webpack_require__(15)
/* template */
var __vue_template__ = __webpack_require__(24)
/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = "data-v-129c8b54"
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __vue_script__,
  __vue_template__,
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)
Component.options.__file = "resources/js/components/custom/DateRangeFilter.vue"

/* hot reload */
if (false) {(function () {
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), false)
  if (!hotAPI.compatible) return
  module.hot.accept()
  if (!module.hot.data) {
    hotAPI.createRecord("data-v-129c8b54", Component.options)
  } else {
    hotAPI.reload("data-v-129c8b54", Component.options)
  }
  module.hot.dispose(function (data) {
    disposed = true
  })
})()}

module.exports = Component.exports


/***/ }),
/* 11 */
/***/ (function(module, exports, __webpack_require__) {

// style-loader: Adds some css to the DOM by adding a <style> tag

// load the styles
var content = __webpack_require__(12);
if(typeof content === 'string') content = [[module.i, content, '']];
if(content.locals) module.exports = content.locals;
// add the styles to the DOM
var update = __webpack_require__(13)("fd386c54", content, false, {});
// Hot Module Replacement
if(false) {
 // When the styles change, update the <style> tags
 if(!content.locals) {
   module.hot.accept("!!../../../../node_modules/css-loader/index.js!../../../../node_modules/vue-loader/lib/style-compiler/index.js?{\"vue\":true,\"id\":\"data-v-129c8b54\",\"scoped\":true,\"hasInlineConfig\":true}!../../../../node_modules/vue-loader/lib/selector.js?type=styles&index=0!./DateRangeFilter.vue", function() {
     var newContent = require("!!../../../../node_modules/css-loader/index.js!../../../../node_modules/vue-loader/lib/style-compiler/index.js?{\"vue\":true,\"id\":\"data-v-129c8b54\",\"scoped\":true,\"hasInlineConfig\":true}!../../../../node_modules/vue-loader/lib/selector.js?type=styles&index=0!./DateRangeFilter.vue");
     if(typeof newContent === 'string') newContent = [[module.id, newContent, '']];
     update(newContent);
   });
 }
 // When the module is disposed, remove the <style> tags
 module.hot.dispose(function() { update(); });
}

/***/ }),
/* 12 */
/***/ (function(module, exports, __webpack_require__) {

exports = module.exports = __webpack_require__(1)(false);
// imports


// module
exports.push([module.i, "\n.\\!cursor-not-allowed[data-v-129c8b54] {\n    cursor: not-allowed !important;\n}\n", ""]);

// exports


/***/ }),
/* 13 */
/***/ (function(module, exports, __webpack_require__) {

/*
  MIT License http://www.opensource.org/licenses/mit-license.php
  Author Tobias Koppers @sokra
  Modified by Evan You @yyx990803
*/

var hasDocument = typeof document !== 'undefined'

if (typeof DEBUG !== 'undefined' && DEBUG) {
  if (!hasDocument) {
    throw new Error(
    'vue-style-loader cannot be used in a non-browser environment. ' +
    "Use { target: 'node' } in your Webpack config to indicate a server-rendering environment."
  ) }
}

var listToStyles = __webpack_require__(14)

/*
type StyleObject = {
  id: number;
  parts: Array<StyleObjectPart>
}

type StyleObjectPart = {
  css: string;
  media: string;
  sourceMap: ?string
}
*/

var stylesInDom = {/*
  [id: number]: {
    id: number,
    refs: number,
    parts: Array<(obj?: StyleObjectPart) => void>
  }
*/}

var head = hasDocument && (document.head || document.getElementsByTagName('head')[0])
var singletonElement = null
var singletonCounter = 0
var isProduction = false
var noop = function () {}
var options = null
var ssrIdKey = 'data-vue-ssr-id'

// Force single-tag solution on IE6-9, which has a hard limit on the # of <style>
// tags it will allow on a page
var isOldIE = typeof navigator !== 'undefined' && /msie [6-9]\b/.test(navigator.userAgent.toLowerCase())

module.exports = function (parentId, list, _isProduction, _options) {
  isProduction = _isProduction

  options = _options || {}

  var styles = listToStyles(parentId, list)
  addStylesToDom(styles)

  return function update (newList) {
    var mayRemove = []
    for (var i = 0; i < styles.length; i++) {
      var item = styles[i]
      var domStyle = stylesInDom[item.id]
      domStyle.refs--
      mayRemove.push(domStyle)
    }
    if (newList) {
      styles = listToStyles(parentId, newList)
      addStylesToDom(styles)
    } else {
      styles = []
    }
    for (var i = 0; i < mayRemove.length; i++) {
      var domStyle = mayRemove[i]
      if (domStyle.refs === 0) {
        for (var j = 0; j < domStyle.parts.length; j++) {
          domStyle.parts[j]()
        }
        delete stylesInDom[domStyle.id]
      }
    }
  }
}

function addStylesToDom (styles /* Array<StyleObject> */) {
  for (var i = 0; i < styles.length; i++) {
    var item = styles[i]
    var domStyle = stylesInDom[item.id]
    if (domStyle) {
      domStyle.refs++
      for (var j = 0; j < domStyle.parts.length; j++) {
        domStyle.parts[j](item.parts[j])
      }
      for (; j < item.parts.length; j++) {
        domStyle.parts.push(addStyle(item.parts[j]))
      }
      if (domStyle.parts.length > item.parts.length) {
        domStyle.parts.length = item.parts.length
      }
    } else {
      var parts = []
      for (var j = 0; j < item.parts.length; j++) {
        parts.push(addStyle(item.parts[j]))
      }
      stylesInDom[item.id] = { id: item.id, refs: 1, parts: parts }
    }
  }
}

function createStyleElement () {
  var styleElement = document.createElement('style')
  styleElement.type = 'text/css'
  head.appendChild(styleElement)
  return styleElement
}

function addStyle (obj /* StyleObjectPart */) {
  var update, remove
  var styleElement = document.querySelector('style[' + ssrIdKey + '~="' + obj.id + '"]')

  if (styleElement) {
    if (isProduction) {
      // has SSR styles and in production mode.
      // simply do nothing.
      return noop
    } else {
      // has SSR styles but in dev mode.
      // for some reason Chrome can't handle source map in server-rendered
      // style tags - source maps in <style> only works if the style tag is
      // created and inserted dynamically. So we remove the server rendered
      // styles and inject new ones.
      styleElement.parentNode.removeChild(styleElement)
    }
  }

  if (isOldIE) {
    // use singleton mode for IE9.
    var styleIndex = singletonCounter++
    styleElement = singletonElement || (singletonElement = createStyleElement())
    update = applyToSingletonTag.bind(null, styleElement, styleIndex, false)
    remove = applyToSingletonTag.bind(null, styleElement, styleIndex, true)
  } else {
    // use multi-style-tag mode in all other cases
    styleElement = createStyleElement()
    update = applyToTag.bind(null, styleElement)
    remove = function () {
      styleElement.parentNode.removeChild(styleElement)
    }
  }

  update(obj)

  return function updateStyle (newObj /* StyleObjectPart */) {
    if (newObj) {
      if (newObj.css === obj.css &&
          newObj.media === obj.media &&
          newObj.sourceMap === obj.sourceMap) {
        return
      }
      update(obj = newObj)
    } else {
      remove()
    }
  }
}

var replaceText = (function () {
  var textStore = []

  return function (index, replacement) {
    textStore[index] = replacement
    return textStore.filter(Boolean).join('\n')
  }
})()

function applyToSingletonTag (styleElement, index, remove, obj) {
  var css = remove ? '' : obj.css

  if (styleElement.styleSheet) {
    styleElement.styleSheet.cssText = replaceText(index, css)
  } else {
    var cssNode = document.createTextNode(css)
    var childNodes = styleElement.childNodes
    if (childNodes[index]) styleElement.removeChild(childNodes[index])
    if (childNodes.length) {
      styleElement.insertBefore(cssNode, childNodes[index])
    } else {
      styleElement.appendChild(cssNode)
    }
  }
}

function applyToTag (styleElement, obj) {
  var css = obj.css
  var media = obj.media
  var sourceMap = obj.sourceMap

  if (media) {
    styleElement.setAttribute('media', media)
  }
  if (options.ssrId) {
    styleElement.setAttribute(ssrIdKey, obj.id)
  }

  if (sourceMap) {
    // https://developer.chrome.com/devtools/docs/javascript-debugging
    // this makes source maps inside style tags work properly in Chrome
    css += '\n/*# sourceURL=' + sourceMap.sources[0] + ' */'
    // http://stackoverflow.com/a/26603875
    css += '\n/*# sourceMappingURL=data:application/json;base64,' + btoa(unescape(encodeURIComponent(JSON.stringify(sourceMap)))) + ' */'
  }

  if (styleElement.styleSheet) {
    styleElement.styleSheet.cssText = css
  } else {
    while (styleElement.firstChild) {
      styleElement.removeChild(styleElement.firstChild)
    }
    styleElement.appendChild(document.createTextNode(css))
  }
}


/***/ }),
/* 14 */
/***/ (function(module, exports) {

/**
 * Translates the list format produced by css-loader into something
 * easier to manipulate.
 */
module.exports = function listToStyles (parentId, list) {
  var styles = []
  var newStyles = {}
  for (var i = 0; i < list.length; i++) {
    var item = list[i]
    var id = item[0]
    var css = item[1]
    var media = item[2]
    var sourceMap = item[3]
    var part = {
      id: parentId + ':' + i,
      css: css,
      media: media,
      sourceMap: sourceMap
    }
    if (!newStyles[id]) {
      styles.push(newStyles[id] = { id: id, parts: [part] })
    } else {
      newStyles[id].parts.push(part)
    }
  }
  return styles
}


/***/ }),
/* 15 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_flatpickr__ = __webpack_require__(16);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__airbnb_modified_css__ = __webpack_require__(20);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__airbnb_modified_css___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_1__airbnb_modified_css__);
var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//




/* harmony default export */ __webpack_exports__["default"] = ({
    name: 'date-range-filter',
    props: {
        resourceName: {
            type: String,
            required: true
        },
        filterKey: {
            type: String,
            required: true
        }

    },

    data: function data() {
        return { flatpickr: null };
    },

    computed: {
        placeholder: function placeholder() {
            return this.filter.placeholder || this.__('Pick a date range');
        },
        value: function value() {
            if (_typeof(this.filter.currentValue) === 'object' && this.filter.currentValue.length >= 2) {
                return moment(this.filter.currentValue[0]).format(this.toMomentsFormat(this.dateFormat)) + ' ' + this.separator + ' ' + moment(this.filter.currentValue[1]).format(this.toMomentsFormat(this.dateFormat));
            }
            return this.filter.currentValue || null;
        },
        filter: function filter() {
            return this.$store.getters[this.resourceName + '/getFilter'](this.filterKey);
        },
        disabled: function disabled() {
            return this.filter.disabled;
        },
        separator: function separator() {
            return this.filter.separator || '-';
        },
        dateFormat: function dateFormat() {
            return this.filter.dateFormat || 'Y-m-d';
        },
        twelveHourTime: function twelveHourTime() {
            return this.filter.twelveHourTime;
        },
        enableTime: function enableTime() {
            return this.filter.enableTime;
        },
        enableSeconds: function enableSeconds() {
            return this.filter.enableSeconds;
        }
    },

    mounted: function mounted() {
        var _this = this;

        var self = this;
        this.$nextTick(function () {
            _this.flatpickr = Object(__WEBPACK_IMPORTED_MODULE_0_flatpickr__["a" /* default */])(_this.$refs.datePicker, {
                enableTime: _this.enableTime,
                enableSeconds: _this.enableSeconds,
                onClose: _this.handleChange,
                dateFormat: _this.dateFormat,
                allowInput: true,
                // static: true,
                mode: 'range',
                time_24hr: !_this.twelveHourTime,
                onReady: function onReady() {
                    self.$refs.datePicker.parentNode.classList.add('date-filter');
                },

                locale: {
                    rangeSeparator: ' ' + _this.separator + ' '
                }
            });
            var wrapper = document.querySelector('.dropdown-menu div');
            if (wrapper) {
                wrapper.classList.remove('overflow-hidden');
            }
        });
    },


    methods: {
        handleChange: function handleChange(value) {
            var _this2 = this;

            value = value.map(function (value) {
                return moment(value).format(_this2.toMomentsFormat(_this2.dateFormat));
            });
            this.$store.commit(this.resourceName + '/updateFilterState', {
                filterClass: this.filterKey,
                value: value
            });
            this.$emit('change');
        },
        toMomentsFormat: function toMomentsFormat(format) {
            var res = format;
            res = res.replace('Y', 'YYYY');
            res = res.replace('m', 'MM');
            res = res.replace('d', 'DD');
            return res;
        }
    }
});

/***/ }),
/* 16 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__types_options__ = __webpack_require__(2);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__l10n_default__ = __webpack_require__(3);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__utils__ = __webpack_require__(4);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3__utils_dom__ = __webpack_require__(17);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_4__utils_dates__ = __webpack_require__(18);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_5__utils_formatting__ = __webpack_require__(5);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_6__utils_polyfills__ = __webpack_require__(19);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_6__utils_polyfills___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_6__utils_polyfills__);
var __assign = (this && this.__assign) || function () {
    __assign = Object.assign || function(t) {
        for (var s, i = 1, n = arguments.length; i < n; i++) {
            s = arguments[i];
            for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p))
                t[p] = s[p];
        }
        return t;
    };
    return __assign.apply(this, arguments);
};
var __spreadArrays = (this && this.__spreadArrays) || function () {
    for (var s = 0, i = 0, il = arguments.length; i < il; i++) s += arguments[i].length;
    for (var r = Array(s), k = 0, i = 0; i < il; i++)
        for (var a = arguments[i], j = 0, jl = a.length; j < jl; j++, k++)
            r[k] = a[j];
    return r;
};







var DEBOUNCED_CHANGE_MS = 300;
function FlatpickrInstance(element, instanceConfig) {
    var self = {
        config: __assign(__assign({}, __WEBPACK_IMPORTED_MODULE_0__types_options__["b" /* defaults */]), flatpickr.defaultConfig),
        l10n: __WEBPACK_IMPORTED_MODULE_1__l10n_default__["a" /* default */],
    };
    self.parseDate = Object(__WEBPACK_IMPORTED_MODULE_4__utils_dates__["d" /* createDateParser */])({ config: self.config, l10n: self.l10n });
    self._handlers = [];
    self.pluginElements = [];
    self.loadedPlugins = [];
    self._bind = bind;
    self._setHoursFromDate = setHoursFromDate;
    self._positionCalendar = positionCalendar;
    self.changeMonth = changeMonth;
    self.changeYear = changeYear;
    self.clear = clear;
    self.close = close;
    self.onMouseOver = onMouseOver;
    self._createElement = __WEBPACK_IMPORTED_MODULE_3__utils_dom__["b" /* createElement */];
    self.createDay = createDay;
    self.destroy = destroy;
    self.isEnabled = isEnabled;
    self.jumpToDate = jumpToDate;
    self.updateValue = updateValue;
    self.open = open;
    self.redraw = redraw;
    self.set = set;
    self.setDate = setDate;
    self.toggle = toggle;
    function setupHelperFunctions() {
        self.utils = {
            getDaysInMonth: function (month, yr) {
                if (month === void 0) { month = self.currentMonth; }
                if (yr === void 0) { yr = self.currentYear; }
                if (month === 1 && ((yr % 4 === 0 && yr % 100 !== 0) || yr % 400 === 0))
                    return 29;
                return self.l10n.daysInMonth[month];
            },
        };
    }
    function init() {
        self.element = self.input = element;
        self.isOpen = false;
        parseConfig();
        setupLocale();
        setupInputs();
        setupDates();
        setupHelperFunctions();
        if (!self.isMobile)
            build();
        bindEvents();
        if (self.selectedDates.length || self.config.noCalendar) {
            if (self.config.enableTime) {
                setHoursFromDate(self.config.noCalendar ? self.latestSelectedDateObj : undefined);
            }
            updateValue(false);
        }
        setCalendarWidth();
        var isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
        if (!self.isMobile && isSafari) {
            positionCalendar();
        }
        triggerEvent("onReady");
    }
    function getClosestActiveElement() {
        var _a;
        return (((_a = self.calendarContainer) === null || _a === void 0 ? void 0 : _a.getRootNode())
            .activeElement || document.activeElement);
    }
    function bindToInstance(fn) {
        return fn.bind(self);
    }
    function setCalendarWidth() {
        var config = self.config;
        if (config.weekNumbers === false && config.showMonths === 1) {
            return;
        }
        else if (config.noCalendar !== true) {
            window.requestAnimationFrame(function () {
                if (self.calendarContainer !== undefined) {
                    self.calendarContainer.style.visibility = "hidden";
                    self.calendarContainer.style.display = "block";
                }
                if (self.daysContainer !== undefined) {
                    var daysWidth = (self.days.offsetWidth + 1) * config.showMonths;
                    self.daysContainer.style.width = daysWidth + "px";
                    self.calendarContainer.style.width =
                        daysWidth +
                            (self.weekWrapper !== undefined
                                ? self.weekWrapper.offsetWidth
                                : 0) +
                            "px";
                    self.calendarContainer.style.removeProperty("visibility");
                    self.calendarContainer.style.removeProperty("display");
                }
            });
        }
    }
    function updateTime(e) {
        if (self.selectedDates.length === 0) {
            var defaultDate = self.config.minDate === undefined ||
                Object(__WEBPACK_IMPORTED_MODULE_4__utils_dates__["b" /* compareDates */])(new Date(), self.config.minDate) >= 0
                ? new Date()
                : new Date(self.config.minDate.getTime());
            var defaults = Object(__WEBPACK_IMPORTED_MODULE_4__utils_dates__["f" /* getDefaultHours */])(self.config);
            defaultDate.setHours(defaults.hours, defaults.minutes, defaults.seconds, defaultDate.getMilliseconds());
            self.selectedDates = [defaultDate];
            self.latestSelectedDateObj = defaultDate;
        }
        if (e !== undefined && e.type !== "blur") {
            timeWrapper(e);
        }
        var prevValue = self._input.value;
        setHoursFromInputs();
        updateValue();
        if (self._input.value !== prevValue) {
            self._debouncedChange();
        }
    }
    function ampm2military(hour, amPM) {
        return (hour % 12) + 12 * Object(__WEBPACK_IMPORTED_MODULE_2__utils__["c" /* int */])(amPM === self.l10n.amPM[1]);
    }
    function military2ampm(hour) {
        switch (hour % 24) {
            case 0:
            case 12:
                return 12;
            default:
                return hour % 12;
        }
    }
    function setHoursFromInputs() {
        if (self.hourElement === undefined || self.minuteElement === undefined)
            return;
        var hours = (parseInt(self.hourElement.value.slice(-2), 10) || 0) % 24, minutes = (parseInt(self.minuteElement.value, 10) || 0) % 60, seconds = self.secondElement !== undefined
            ? (parseInt(self.secondElement.value, 10) || 0) % 60
            : 0;
        if (self.amPM !== undefined) {
            hours = ampm2military(hours, self.amPM.textContent);
        }
        var limitMinHours = self.config.minTime !== undefined ||
            (self.config.minDate &&
                self.minDateHasTime &&
                self.latestSelectedDateObj &&
                Object(__WEBPACK_IMPORTED_MODULE_4__utils_dates__["b" /* compareDates */])(self.latestSelectedDateObj, self.config.minDate, true) ===
                    0);
        var limitMaxHours = self.config.maxTime !== undefined ||
            (self.config.maxDate &&
                self.maxDateHasTime &&
                self.latestSelectedDateObj &&
                Object(__WEBPACK_IMPORTED_MODULE_4__utils_dates__["b" /* compareDates */])(self.latestSelectedDateObj, self.config.maxDate, true) ===
                    0);
        if (self.config.maxTime !== undefined &&
            self.config.minTime !== undefined &&
            self.config.minTime > self.config.maxTime) {
            var minBound = Object(__WEBPACK_IMPORTED_MODULE_4__utils_dates__["a" /* calculateSecondsSinceMidnight */])(self.config.minTime.getHours(), self.config.minTime.getMinutes(), self.config.minTime.getSeconds());
            var maxBound = Object(__WEBPACK_IMPORTED_MODULE_4__utils_dates__["a" /* calculateSecondsSinceMidnight */])(self.config.maxTime.getHours(), self.config.maxTime.getMinutes(), self.config.maxTime.getSeconds());
            var currentTime = Object(__WEBPACK_IMPORTED_MODULE_4__utils_dates__["a" /* calculateSecondsSinceMidnight */])(hours, minutes, seconds);
            if (currentTime > maxBound && currentTime < minBound) {
                var result = Object(__WEBPACK_IMPORTED_MODULE_4__utils_dates__["h" /* parseSeconds */])(minBound);
                hours = result[0];
                minutes = result[1];
                seconds = result[2];
            }
        }
        else {
            if (limitMaxHours) {
                var maxTime = self.config.maxTime !== undefined
                    ? self.config.maxTime
                    : self.config.maxDate;
                hours = Math.min(hours, maxTime.getHours());
                if (hours === maxTime.getHours())
                    minutes = Math.min(minutes, maxTime.getMinutes());
                if (minutes === maxTime.getMinutes())
                    seconds = Math.min(seconds, maxTime.getSeconds());
            }
            if (limitMinHours) {
                var minTime = self.config.minTime !== undefined
                    ? self.config.minTime
                    : self.config.minDate;
                hours = Math.max(hours, minTime.getHours());
                if (hours === minTime.getHours() && minutes < minTime.getMinutes())
                    minutes = minTime.getMinutes();
                if (minutes === minTime.getMinutes())
                    seconds = Math.max(seconds, minTime.getSeconds());
            }
        }
        setHours(hours, minutes, seconds);
    }
    function setHoursFromDate(dateObj) {
        var date = dateObj || self.latestSelectedDateObj;
        if (date && date instanceof Date) {
            setHours(date.getHours(), date.getMinutes(), date.getSeconds());
        }
    }
    function setHours(hours, minutes, seconds) {
        if (self.latestSelectedDateObj !== undefined) {
            self.latestSelectedDateObj.setHours(hours % 24, minutes, seconds || 0, 0);
        }
        if (!self.hourElement || !self.minuteElement || self.isMobile)
            return;
        self.hourElement.value = Object(__WEBPACK_IMPORTED_MODULE_2__utils__["d" /* pad */])(!self.config.time_24hr
            ? ((12 + hours) % 12) + 12 * Object(__WEBPACK_IMPORTED_MODULE_2__utils__["c" /* int */])(hours % 12 === 0)
            : hours);
        self.minuteElement.value = Object(__WEBPACK_IMPORTED_MODULE_2__utils__["d" /* pad */])(minutes);
        if (self.amPM !== undefined)
            self.amPM.textContent = self.l10n.amPM[Object(__WEBPACK_IMPORTED_MODULE_2__utils__["c" /* int */])(hours >= 12)];
        if (self.secondElement !== undefined)
            self.secondElement.value = Object(__WEBPACK_IMPORTED_MODULE_2__utils__["d" /* pad */])(seconds);
    }
    function onYearInput(event) {
        var eventTarget = Object(__WEBPACK_IMPORTED_MODULE_3__utils_dom__["e" /* getEventTarget */])(event);
        var year = parseInt(eventTarget.value) + (event.delta || 0);
        if (year / 1000 > 1 ||
            (event.key === "Enter" && !/[^\d]/.test(year.toString()))) {
            changeYear(year);
        }
    }
    function bind(element, event, handler, options) {
        if (event instanceof Array)
            return event.forEach(function (ev) { return bind(element, ev, handler, options); });
        if (element instanceof Array)
            return element.forEach(function (el) { return bind(el, event, handler, options); });
        element.addEventListener(event, handler, options);
        self._handlers.push({
            remove: function () { return element.removeEventListener(event, handler, options); },
        });
    }
    function triggerChange() {
        triggerEvent("onChange");
    }
    function bindEvents() {
        if (self.config.wrap) {
            ["open", "close", "toggle", "clear"].forEach(function (evt) {
                Array.prototype.forEach.call(self.element.querySelectorAll("[data-" + evt + "]"), function (el) {
                    return bind(el, "click", self[evt]);
                });
            });
        }
        if (self.isMobile) {
            setupMobile();
            return;
        }
        var debouncedResize = Object(__WEBPACK_IMPORTED_MODULE_2__utils__["b" /* debounce */])(onResize, 50);
        self._debouncedChange = Object(__WEBPACK_IMPORTED_MODULE_2__utils__["b" /* debounce */])(triggerChange, DEBOUNCED_CHANGE_MS);
        if (self.daysContainer && !/iPhone|iPad|iPod/i.test(navigator.userAgent))
            bind(self.daysContainer, "mouseover", function (e) {
                if (self.config.mode === "range")
                    onMouseOver(Object(__WEBPACK_IMPORTED_MODULE_3__utils_dom__["e" /* getEventTarget */])(e));
            });
        bind(self._input, "keydown", onKeyDown);
        if (self.calendarContainer !== undefined) {
            bind(self.calendarContainer, "keydown", onKeyDown);
        }
        if (!self.config.inline && !self.config.static)
            bind(window, "resize", debouncedResize);
        if (window.ontouchstart !== undefined)
            bind(window.document, "touchstart", documentClick);
        else
            bind(window.document, "mousedown", documentClick);
        bind(window.document, "focus", documentClick, { capture: true });
        if (self.config.clickOpens === true) {
            bind(self._input, "focus", self.open);
            bind(self._input, "click", self.open);
        }
        if (self.daysContainer !== undefined) {
            bind(self.monthNav, "click", onMonthNavClick);
            bind(self.monthNav, ["keyup", "increment"], onYearInput);
            bind(self.daysContainer, "click", selectDate);
        }
        if (self.timeContainer !== undefined &&
            self.minuteElement !== undefined &&
            self.hourElement !== undefined) {
            var selText = function (e) {
                return Object(__WEBPACK_IMPORTED_MODULE_3__utils_dom__["e" /* getEventTarget */])(e).select();
            };
            bind(self.timeContainer, ["increment"], updateTime);
            bind(self.timeContainer, "blur", updateTime, { capture: true });
            bind(self.timeContainer, "click", timeIncrement);
            bind([self.hourElement, self.minuteElement], ["focus", "click"], selText);
            if (self.secondElement !== undefined)
                bind(self.secondElement, "focus", function () { return self.secondElement && self.secondElement.select(); });
            if (self.amPM !== undefined) {
                bind(self.amPM, "click", function (e) {
                    updateTime(e);
                });
            }
        }
        if (self.config.allowInput) {
            bind(self._input, "blur", onBlur);
        }
    }
    function jumpToDate(jumpDate, triggerChange) {
        var jumpTo = jumpDate !== undefined
            ? self.parseDate(jumpDate)
            : self.latestSelectedDateObj ||
                (self.config.minDate && self.config.minDate > self.now
                    ? self.config.minDate
                    : self.config.maxDate && self.config.maxDate < self.now
                        ? self.config.maxDate
                        : self.now);
        var oldYear = self.currentYear;
        var oldMonth = self.currentMonth;
        try {
            if (jumpTo !== undefined) {
                self.currentYear = jumpTo.getFullYear();
                self.currentMonth = jumpTo.getMonth();
            }
        }
        catch (e) {
            e.message = "Invalid date supplied: " + jumpTo;
            self.config.errorHandler(e);
        }
        if (triggerChange && self.currentYear !== oldYear) {
            triggerEvent("onYearChange");
            buildMonthSwitch();
        }
        if (triggerChange &&
            (self.currentYear !== oldYear || self.currentMonth !== oldMonth)) {
            triggerEvent("onMonthChange");
        }
        self.redraw();
    }
    function timeIncrement(e) {
        var eventTarget = Object(__WEBPACK_IMPORTED_MODULE_3__utils_dom__["e" /* getEventTarget */])(e);
        if (~eventTarget.className.indexOf("arrow"))
            incrementNumInput(e, eventTarget.classList.contains("arrowUp") ? 1 : -1);
    }
    function incrementNumInput(e, delta, inputElem) {
        var target = e && Object(__WEBPACK_IMPORTED_MODULE_3__utils_dom__["e" /* getEventTarget */])(e);
        var input = inputElem ||
            (target && target.parentNode && target.parentNode.firstChild);
        var event = createEvent("increment");
        event.delta = delta;
        input && input.dispatchEvent(event);
    }
    function build() {
        var fragment = window.document.createDocumentFragment();
        self.calendarContainer = Object(__WEBPACK_IMPORTED_MODULE_3__utils_dom__["b" /* createElement */])("div", "flatpickr-calendar");
        self.calendarContainer.tabIndex = -1;
        if (!self.config.noCalendar) {
            fragment.appendChild(buildMonthNav());
            self.innerContainer = Object(__WEBPACK_IMPORTED_MODULE_3__utils_dom__["b" /* createElement */])("div", "flatpickr-innerContainer");
            if (self.config.weekNumbers) {
                var _a = buildWeeks(), weekWrapper = _a.weekWrapper, weekNumbers = _a.weekNumbers;
                self.innerContainer.appendChild(weekWrapper);
                self.weekNumbers = weekNumbers;
                self.weekWrapper = weekWrapper;
            }
            self.rContainer = Object(__WEBPACK_IMPORTED_MODULE_3__utils_dom__["b" /* createElement */])("div", "flatpickr-rContainer");
            self.rContainer.appendChild(buildWeekdays());
            if (!self.daysContainer) {
                self.daysContainer = Object(__WEBPACK_IMPORTED_MODULE_3__utils_dom__["b" /* createElement */])("div", "flatpickr-days");
                self.daysContainer.tabIndex = -1;
            }
            buildDays();
            self.rContainer.appendChild(self.daysContainer);
            self.innerContainer.appendChild(self.rContainer);
            fragment.appendChild(self.innerContainer);
        }
        if (self.config.enableTime) {
            fragment.appendChild(buildTime());
        }
        Object(__WEBPACK_IMPORTED_MODULE_3__utils_dom__["f" /* toggleClass */])(self.calendarContainer, "rangeMode", self.config.mode === "range");
        Object(__WEBPACK_IMPORTED_MODULE_3__utils_dom__["f" /* toggleClass */])(self.calendarContainer, "animate", self.config.animate === true);
        Object(__WEBPACK_IMPORTED_MODULE_3__utils_dom__["f" /* toggleClass */])(self.calendarContainer, "multiMonth", self.config.showMonths > 1);
        self.calendarContainer.appendChild(fragment);
        var customAppend = self.config.appendTo !== undefined &&
            self.config.appendTo.nodeType !== undefined;
        if (self.config.inline || self.config.static) {
            self.calendarContainer.classList.add(self.config.inline ? "inline" : "static");
            if (self.config.inline) {
                if (!customAppend && self.element.parentNode)
                    self.element.parentNode.insertBefore(self.calendarContainer, self._input.nextSibling);
                else if (self.config.appendTo !== undefined)
                    self.config.appendTo.appendChild(self.calendarContainer);
            }
            if (self.config.static) {
                var wrapper = Object(__WEBPACK_IMPORTED_MODULE_3__utils_dom__["b" /* createElement */])("div", "flatpickr-wrapper");
                if (self.element.parentNode)
                    self.element.parentNode.insertBefore(wrapper, self.element);
                wrapper.appendChild(self.element);
                if (self.altInput)
                    wrapper.appendChild(self.altInput);
                wrapper.appendChild(self.calendarContainer);
            }
        }
        if (!self.config.static && !self.config.inline)
            (self.config.appendTo !== undefined
                ? self.config.appendTo
                : window.document.body).appendChild(self.calendarContainer);
    }
    function createDay(className, date, _dayNumber, i) {
        var dateIsEnabled = isEnabled(date, true), dayElement = Object(__WEBPACK_IMPORTED_MODULE_3__utils_dom__["b" /* createElement */])("span", className, date.getDate().toString());
        dayElement.dateObj = date;
        dayElement.$i = i;
        dayElement.setAttribute("aria-label", self.formatDate(date, self.config.ariaDateFormat));
        if (className.indexOf("hidden") === -1 &&
            Object(__WEBPACK_IMPORTED_MODULE_4__utils_dates__["b" /* compareDates */])(date, self.now) === 0) {
            self.todayDateElem = dayElement;
            dayElement.classList.add("today");
            dayElement.setAttribute("aria-current", "date");
        }
        if (dateIsEnabled) {
            dayElement.tabIndex = -1;
            if (isDateSelected(date)) {
                dayElement.classList.add("selected");
                self.selectedDateElem = dayElement;
                if (self.config.mode === "range") {
                    Object(__WEBPACK_IMPORTED_MODULE_3__utils_dom__["f" /* toggleClass */])(dayElement, "startRange", self.selectedDates[0] &&
                        Object(__WEBPACK_IMPORTED_MODULE_4__utils_dates__["b" /* compareDates */])(date, self.selectedDates[0], true) === 0);
                    Object(__WEBPACK_IMPORTED_MODULE_3__utils_dom__["f" /* toggleClass */])(dayElement, "endRange", self.selectedDates[1] &&
                        Object(__WEBPACK_IMPORTED_MODULE_4__utils_dates__["b" /* compareDates */])(date, self.selectedDates[1], true) === 0);
                    if (className === "nextMonthDay")
                        dayElement.classList.add("inRange");
                }
            }
        }
        else {
            dayElement.classList.add("flatpickr-disabled");
        }
        if (self.config.mode === "range") {
            if (isDateInRange(date) && !isDateSelected(date))
                dayElement.classList.add("inRange");
        }
        if (self.weekNumbers &&
            self.config.showMonths === 1 &&
            className !== "prevMonthDay" &&
            i % 7 === 6) {
            self.weekNumbers.insertAdjacentHTML("beforeend", "<span class='flatpickr-day'>" + self.config.getWeek(date) + "</span>");
        }
        triggerEvent("onDayCreate", dayElement);
        return dayElement;
    }
    function focusOnDayElem(targetNode) {
        targetNode.focus();
        if (self.config.mode === "range")
            onMouseOver(targetNode);
    }
    function getFirstAvailableDay(delta) {
        var startMonth = delta > 0 ? 0 : self.config.showMonths - 1;
        var endMonth = delta > 0 ? self.config.showMonths : -1;
        for (var m = startMonth; m != endMonth; m += delta) {
            var month = self.daysContainer.children[m];
            var startIndex = delta > 0 ? 0 : month.children.length - 1;
            var endIndex = delta > 0 ? month.children.length : -1;
            for (var i = startIndex; i != endIndex; i += delta) {
                var c = month.children[i];
                if (c.className.indexOf("hidden") === -1 && isEnabled(c.dateObj))
                    return c;
            }
        }
        return undefined;
    }
    function getNextAvailableDay(current, delta) {
        var givenMonth = current.className.indexOf("Month") === -1
            ? current.dateObj.getMonth()
            : self.currentMonth;
        var endMonth = delta > 0 ? self.config.showMonths : -1;
        var loopDelta = delta > 0 ? 1 : -1;
        for (var m = givenMonth - self.currentMonth; m != endMonth; m += loopDelta) {
            var month = self.daysContainer.children[m];
            var startIndex = givenMonth - self.currentMonth === m
                ? current.$i + delta
                : delta < 0
                    ? month.children.length - 1
                    : 0;
            var numMonthDays = month.children.length;
            for (var i = startIndex; i >= 0 && i < numMonthDays && i != (delta > 0 ? numMonthDays : -1); i += loopDelta) {
                var c = month.children[i];
                if (c.className.indexOf("hidden") === -1 &&
                    isEnabled(c.dateObj) &&
                    Math.abs(current.$i - i) >= Math.abs(delta))
                    return focusOnDayElem(c);
            }
        }
        self.changeMonth(loopDelta);
        focusOnDay(getFirstAvailableDay(loopDelta), 0);
        return undefined;
    }
    function focusOnDay(current, offset) {
        var activeElement = getClosestActiveElement();
        var dayFocused = isInView(activeElement || document.body);
        var startElem = current !== undefined
            ? current
            : dayFocused
                ? activeElement
                : self.selectedDateElem !== undefined && isInView(self.selectedDateElem)
                    ? self.selectedDateElem
                    : self.todayDateElem !== undefined && isInView(self.todayDateElem)
                        ? self.todayDateElem
                        : getFirstAvailableDay(offset > 0 ? 1 : -1);
        if (startElem === undefined) {
            self._input.focus();
        }
        else if (!dayFocused) {
            focusOnDayElem(startElem);
        }
        else {
            getNextAvailableDay(startElem, offset);
        }
    }
    function buildMonthDays(year, month) {
        var firstOfMonth = (new Date(year, month, 1).getDay() - self.l10n.firstDayOfWeek + 7) % 7;
        var prevMonthDays = self.utils.getDaysInMonth((month - 1 + 12) % 12, year);
        var daysInMonth = self.utils.getDaysInMonth(month, year), days = window.document.createDocumentFragment(), isMultiMonth = self.config.showMonths > 1, prevMonthDayClass = isMultiMonth ? "prevMonthDay hidden" : "prevMonthDay", nextMonthDayClass = isMultiMonth ? "nextMonthDay hidden" : "nextMonthDay";
        var dayNumber = prevMonthDays + 1 - firstOfMonth, dayIndex = 0;
        for (; dayNumber <= prevMonthDays; dayNumber++, dayIndex++) {
            days.appendChild(createDay("flatpickr-day " + prevMonthDayClass, new Date(year, month - 1, dayNumber), dayNumber, dayIndex));
        }
        for (dayNumber = 1; dayNumber <= daysInMonth; dayNumber++, dayIndex++) {
            days.appendChild(createDay("flatpickr-day", new Date(year, month, dayNumber), dayNumber, dayIndex));
        }
        for (var dayNum = daysInMonth + 1; dayNum <= 42 - firstOfMonth &&
            (self.config.showMonths === 1 || dayIndex % 7 !== 0); dayNum++, dayIndex++) {
            days.appendChild(createDay("flatpickr-day " + nextMonthDayClass, new Date(year, month + 1, dayNum % daysInMonth), dayNum, dayIndex));
        }
        var dayContainer = Object(__WEBPACK_IMPORTED_MODULE_3__utils_dom__["b" /* createElement */])("div", "dayContainer");
        dayContainer.appendChild(days);
        return dayContainer;
    }
    function buildDays() {
        if (self.daysContainer === undefined) {
            return;
        }
        Object(__WEBPACK_IMPORTED_MODULE_3__utils_dom__["a" /* clearNode */])(self.daysContainer);
        if (self.weekNumbers)
            Object(__WEBPACK_IMPORTED_MODULE_3__utils_dom__["a" /* clearNode */])(self.weekNumbers);
        var frag = document.createDocumentFragment();
        for (var i = 0; i < self.config.showMonths; i++) {
            var d = new Date(self.currentYear, self.currentMonth, 1);
            d.setMonth(self.currentMonth + i);
            frag.appendChild(buildMonthDays(d.getFullYear(), d.getMonth()));
        }
        self.daysContainer.appendChild(frag);
        self.days = self.daysContainer.firstChild;
        if (self.config.mode === "range" && self.selectedDates.length === 1) {
            onMouseOver();
        }
    }
    function buildMonthSwitch() {
        if (self.config.showMonths > 1 ||
            self.config.monthSelectorType !== "dropdown")
            return;
        var shouldBuildMonth = function (month) {
            if (self.config.minDate !== undefined &&
                self.currentYear === self.config.minDate.getFullYear() &&
                month < self.config.minDate.getMonth()) {
                return false;
            }
            return !(self.config.maxDate !== undefined &&
                self.currentYear === self.config.maxDate.getFullYear() &&
                month > self.config.maxDate.getMonth());
        };
        self.monthsDropdownContainer.tabIndex = -1;
        self.monthsDropdownContainer.innerHTML = "";
        for (var i = 0; i < 12; i++) {
            if (!shouldBuildMonth(i))
                continue;
            var month = Object(__WEBPACK_IMPORTED_MODULE_3__utils_dom__["b" /* createElement */])("option", "flatpickr-monthDropdown-month");
            month.value = new Date(self.currentYear, i).getMonth().toString();
            month.textContent = Object(__WEBPACK_IMPORTED_MODULE_5__utils_formatting__["b" /* monthToStr */])(i, self.config.shorthandCurrentMonth, self.l10n);
            month.tabIndex = -1;
            if (self.currentMonth === i) {
                month.selected = true;
            }
            self.monthsDropdownContainer.appendChild(month);
        }
    }
    function buildMonth() {
        var container = Object(__WEBPACK_IMPORTED_MODULE_3__utils_dom__["b" /* createElement */])("div", "flatpickr-month");
        var monthNavFragment = window.document.createDocumentFragment();
        var monthElement;
        if (self.config.showMonths > 1 ||
            self.config.monthSelectorType === "static") {
            monthElement = Object(__WEBPACK_IMPORTED_MODULE_3__utils_dom__["b" /* createElement */])("span", "cur-month");
        }
        else {
            self.monthsDropdownContainer = Object(__WEBPACK_IMPORTED_MODULE_3__utils_dom__["b" /* createElement */])("select", "flatpickr-monthDropdown-months");
            self.monthsDropdownContainer.setAttribute("aria-label", self.l10n.monthAriaLabel);
            bind(self.monthsDropdownContainer, "change", function (e) {
                var target = Object(__WEBPACK_IMPORTED_MODULE_3__utils_dom__["e" /* getEventTarget */])(e);
                var selectedMonth = parseInt(target.value, 10);
                self.changeMonth(selectedMonth - self.currentMonth);
                triggerEvent("onMonthChange");
            });
            buildMonthSwitch();
            monthElement = self.monthsDropdownContainer;
        }
        var yearInput = Object(__WEBPACK_IMPORTED_MODULE_3__utils_dom__["c" /* createNumberInput */])("cur-year", { tabindex: "-1" });
        var yearElement = yearInput.getElementsByTagName("input")[0];
        yearElement.setAttribute("aria-label", self.l10n.yearAriaLabel);
        if (self.config.minDate) {
            yearElement.setAttribute("min", self.config.minDate.getFullYear().toString());
        }
        if (self.config.maxDate) {
            yearElement.setAttribute("max", self.config.maxDate.getFullYear().toString());
            yearElement.disabled =
                !!self.config.minDate &&
                    self.config.minDate.getFullYear() === self.config.maxDate.getFullYear();
        }
        var currentMonth = Object(__WEBPACK_IMPORTED_MODULE_3__utils_dom__["b" /* createElement */])("div", "flatpickr-current-month");
        currentMonth.appendChild(monthElement);
        currentMonth.appendChild(yearInput);
        monthNavFragment.appendChild(currentMonth);
        container.appendChild(monthNavFragment);
        return {
            container: container,
            yearElement: yearElement,
            monthElement: monthElement,
        };
    }
    function buildMonths() {
        Object(__WEBPACK_IMPORTED_MODULE_3__utils_dom__["a" /* clearNode */])(self.monthNav);
        self.monthNav.appendChild(self.prevMonthNav);
        if (self.config.showMonths) {
            self.yearElements = [];
            self.monthElements = [];
        }
        for (var m = self.config.showMonths; m--;) {
            var month = buildMonth();
            self.yearElements.push(month.yearElement);
            self.monthElements.push(month.monthElement);
            self.monthNav.appendChild(month.container);
        }
        self.monthNav.appendChild(self.nextMonthNav);
    }
    function buildMonthNav() {
        self.monthNav = Object(__WEBPACK_IMPORTED_MODULE_3__utils_dom__["b" /* createElement */])("div", "flatpickr-months");
        self.yearElements = [];
        self.monthElements = [];
        self.prevMonthNav = Object(__WEBPACK_IMPORTED_MODULE_3__utils_dom__["b" /* createElement */])("span", "flatpickr-prev-month");
        self.prevMonthNav.innerHTML = self.config.prevArrow;
        self.nextMonthNav = Object(__WEBPACK_IMPORTED_MODULE_3__utils_dom__["b" /* createElement */])("span", "flatpickr-next-month");
        self.nextMonthNav.innerHTML = self.config.nextArrow;
        buildMonths();
        Object.defineProperty(self, "_hidePrevMonthArrow", {
            get: function () { return self.__hidePrevMonthArrow; },
            set: function (bool) {
                if (self.__hidePrevMonthArrow !== bool) {
                    Object(__WEBPACK_IMPORTED_MODULE_3__utils_dom__["f" /* toggleClass */])(self.prevMonthNav, "flatpickr-disabled", bool);
                    self.__hidePrevMonthArrow = bool;
                }
            },
        });
        Object.defineProperty(self, "_hideNextMonthArrow", {
            get: function () { return self.__hideNextMonthArrow; },
            set: function (bool) {
                if (self.__hideNextMonthArrow !== bool) {
                    Object(__WEBPACK_IMPORTED_MODULE_3__utils_dom__["f" /* toggleClass */])(self.nextMonthNav, "flatpickr-disabled", bool);
                    self.__hideNextMonthArrow = bool;
                }
            },
        });
        self.currentYearElement = self.yearElements[0];
        updateNavigationCurrentMonth();
        return self.monthNav;
    }
    function buildTime() {
        self.calendarContainer.classList.add("hasTime");
        if (self.config.noCalendar)
            self.calendarContainer.classList.add("noCalendar");
        var defaults = Object(__WEBPACK_IMPORTED_MODULE_4__utils_dates__["f" /* getDefaultHours */])(self.config);
        self.timeContainer = Object(__WEBPACK_IMPORTED_MODULE_3__utils_dom__["b" /* createElement */])("div", "flatpickr-time");
        self.timeContainer.tabIndex = -1;
        var separator = Object(__WEBPACK_IMPORTED_MODULE_3__utils_dom__["b" /* createElement */])("span", "flatpickr-time-separator", ":");
        var hourInput = Object(__WEBPACK_IMPORTED_MODULE_3__utils_dom__["c" /* createNumberInput */])("flatpickr-hour", {
            "aria-label": self.l10n.hourAriaLabel,
        });
        self.hourElement = hourInput.getElementsByTagName("input")[0];
        var minuteInput = Object(__WEBPACK_IMPORTED_MODULE_3__utils_dom__["c" /* createNumberInput */])("flatpickr-minute", {
            "aria-label": self.l10n.minuteAriaLabel,
        });
        self.minuteElement = minuteInput.getElementsByTagName("input")[0];
        self.hourElement.tabIndex = self.minuteElement.tabIndex = -1;
        self.hourElement.value = Object(__WEBPACK_IMPORTED_MODULE_2__utils__["d" /* pad */])(self.latestSelectedDateObj
            ? self.latestSelectedDateObj.getHours()
            : self.config.time_24hr
                ? defaults.hours
                : military2ampm(defaults.hours));
        self.minuteElement.value = Object(__WEBPACK_IMPORTED_MODULE_2__utils__["d" /* pad */])(self.latestSelectedDateObj
            ? self.latestSelectedDateObj.getMinutes()
            : defaults.minutes);
        self.hourElement.setAttribute("step", self.config.hourIncrement.toString());
        self.minuteElement.setAttribute("step", self.config.minuteIncrement.toString());
        self.hourElement.setAttribute("min", self.config.time_24hr ? "0" : "1");
        self.hourElement.setAttribute("max", self.config.time_24hr ? "23" : "12");
        self.hourElement.setAttribute("maxlength", "2");
        self.minuteElement.setAttribute("min", "0");
        self.minuteElement.setAttribute("max", "59");
        self.minuteElement.setAttribute("maxlength", "2");
        self.timeContainer.appendChild(hourInput);
        self.timeContainer.appendChild(separator);
        self.timeContainer.appendChild(minuteInput);
        if (self.config.time_24hr)
            self.timeContainer.classList.add("time24hr");
        if (self.config.enableSeconds) {
            self.timeContainer.classList.add("hasSeconds");
            var secondInput = Object(__WEBPACK_IMPORTED_MODULE_3__utils_dom__["c" /* createNumberInput */])("flatpickr-second");
            self.secondElement = secondInput.getElementsByTagName("input")[0];
            self.secondElement.value = Object(__WEBPACK_IMPORTED_MODULE_2__utils__["d" /* pad */])(self.latestSelectedDateObj
                ? self.latestSelectedDateObj.getSeconds()
                : defaults.seconds);
            self.secondElement.setAttribute("step", self.minuteElement.getAttribute("step"));
            self.secondElement.setAttribute("min", "0");
            self.secondElement.setAttribute("max", "59");
            self.secondElement.setAttribute("maxlength", "2");
            self.timeContainer.appendChild(Object(__WEBPACK_IMPORTED_MODULE_3__utils_dom__["b" /* createElement */])("span", "flatpickr-time-separator", ":"));
            self.timeContainer.appendChild(secondInput);
        }
        if (!self.config.time_24hr) {
            self.amPM = Object(__WEBPACK_IMPORTED_MODULE_3__utils_dom__["b" /* createElement */])("span", "flatpickr-am-pm", self.l10n.amPM[Object(__WEBPACK_IMPORTED_MODULE_2__utils__["c" /* int */])((self.latestSelectedDateObj
                ? self.hourElement.value
                : self.config.defaultHour) > 11)]);
            self.amPM.title = self.l10n.toggleTitle;
            self.amPM.tabIndex = -1;
            self.timeContainer.appendChild(self.amPM);
        }
        return self.timeContainer;
    }
    function buildWeekdays() {
        if (!self.weekdayContainer)
            self.weekdayContainer = Object(__WEBPACK_IMPORTED_MODULE_3__utils_dom__["b" /* createElement */])("div", "flatpickr-weekdays");
        else
            Object(__WEBPACK_IMPORTED_MODULE_3__utils_dom__["a" /* clearNode */])(self.weekdayContainer);
        for (var i = self.config.showMonths; i--;) {
            var container = Object(__WEBPACK_IMPORTED_MODULE_3__utils_dom__["b" /* createElement */])("div", "flatpickr-weekdaycontainer");
            self.weekdayContainer.appendChild(container);
        }
        updateWeekdays();
        return self.weekdayContainer;
    }
    function updateWeekdays() {
        if (!self.weekdayContainer) {
            return;
        }
        var firstDayOfWeek = self.l10n.firstDayOfWeek;
        var weekdays = __spreadArrays(self.l10n.weekdays.shorthand);
        if (firstDayOfWeek > 0 && firstDayOfWeek < weekdays.length) {
            weekdays = __spreadArrays(weekdays.splice(firstDayOfWeek, weekdays.length), weekdays.splice(0, firstDayOfWeek));
        }
        for (var i = self.config.showMonths; i--;) {
            self.weekdayContainer.children[i].innerHTML = "\n      <span class='flatpickr-weekday'>\n        " + weekdays.join("</span><span class='flatpickr-weekday'>") + "\n      </span>\n      ";
        }
    }
    function buildWeeks() {
        self.calendarContainer.classList.add("hasWeeks");
        var weekWrapper = Object(__WEBPACK_IMPORTED_MODULE_3__utils_dom__["b" /* createElement */])("div", "flatpickr-weekwrapper");
        weekWrapper.appendChild(Object(__WEBPACK_IMPORTED_MODULE_3__utils_dom__["b" /* createElement */])("span", "flatpickr-weekday", self.l10n.weekAbbreviation));
        var weekNumbers = Object(__WEBPACK_IMPORTED_MODULE_3__utils_dom__["b" /* createElement */])("div", "flatpickr-weeks");
        weekWrapper.appendChild(weekNumbers);
        return {
            weekWrapper: weekWrapper,
            weekNumbers: weekNumbers,
        };
    }
    function changeMonth(value, isOffset) {
        if (isOffset === void 0) { isOffset = true; }
        var delta = isOffset ? value : value - self.currentMonth;
        if ((delta < 0 && self._hidePrevMonthArrow === true) ||
            (delta > 0 && self._hideNextMonthArrow === true))
            return;
        self.currentMonth += delta;
        if (self.currentMonth < 0 || self.currentMonth > 11) {
            self.currentYear += self.currentMonth > 11 ? 1 : -1;
            self.currentMonth = (self.currentMonth + 12) % 12;
            triggerEvent("onYearChange");
            buildMonthSwitch();
        }
        buildDays();
        triggerEvent("onMonthChange");
        updateNavigationCurrentMonth();
    }
    function clear(triggerChangeEvent, toInitial) {
        if (triggerChangeEvent === void 0) { triggerChangeEvent = true; }
        if (toInitial === void 0) { toInitial = true; }
        self.input.value = "";
        if (self.altInput !== undefined)
            self.altInput.value = "";
        if (self.mobileInput !== undefined)
            self.mobileInput.value = "";
        self.selectedDates = [];
        self.latestSelectedDateObj = undefined;
        if (toInitial === true) {
            self.currentYear = self._initialDate.getFullYear();
            self.currentMonth = self._initialDate.getMonth();
        }
        if (self.config.enableTime === true) {
            var _a = Object(__WEBPACK_IMPORTED_MODULE_4__utils_dates__["f" /* getDefaultHours */])(self.config), hours = _a.hours, minutes = _a.minutes, seconds = _a.seconds;
            setHours(hours, minutes, seconds);
        }
        self.redraw();
        if (triggerChangeEvent)
            triggerEvent("onChange");
    }
    function close() {
        self.isOpen = false;
        if (!self.isMobile) {
            if (self.calendarContainer !== undefined) {
                self.calendarContainer.classList.remove("open");
            }
            if (self._input !== undefined) {
                self._input.classList.remove("active");
            }
        }
        triggerEvent("onClose");
    }
    function destroy() {
        if (self.config !== undefined)
            triggerEvent("onDestroy");
        for (var i = self._handlers.length; i--;) {
            self._handlers[i].remove();
        }
        self._handlers = [];
        if (self.mobileInput) {
            if (self.mobileInput.parentNode)
                self.mobileInput.parentNode.removeChild(self.mobileInput);
            self.mobileInput = undefined;
        }
        else if (self.calendarContainer && self.calendarContainer.parentNode) {
            if (self.config.static && self.calendarContainer.parentNode) {
                var wrapper = self.calendarContainer.parentNode;
                wrapper.lastChild && wrapper.removeChild(wrapper.lastChild);
                if (wrapper.parentNode) {
                    while (wrapper.firstChild)
                        wrapper.parentNode.insertBefore(wrapper.firstChild, wrapper);
                    wrapper.parentNode.removeChild(wrapper);
                }
            }
            else
                self.calendarContainer.parentNode.removeChild(self.calendarContainer);
        }
        if (self.altInput) {
            self.input.type = "text";
            if (self.altInput.parentNode)
                self.altInput.parentNode.removeChild(self.altInput);
            delete self.altInput;
        }
        if (self.input) {
            self.input.type = self.input._type;
            self.input.classList.remove("flatpickr-input");
            self.input.removeAttribute("readonly");
        }
        [
            "_showTimeInput",
            "latestSelectedDateObj",
            "_hideNextMonthArrow",
            "_hidePrevMonthArrow",
            "__hideNextMonthArrow",
            "__hidePrevMonthArrow",
            "isMobile",
            "isOpen",
            "selectedDateElem",
            "minDateHasTime",
            "maxDateHasTime",
            "days",
            "daysContainer",
            "_input",
            "_positionElement",
            "innerContainer",
            "rContainer",
            "monthNav",
            "todayDateElem",
            "calendarContainer",
            "weekdayContainer",
            "prevMonthNav",
            "nextMonthNav",
            "monthsDropdownContainer",
            "currentMonthElement",
            "currentYearElement",
            "navigationCurrentMonth",
            "selectedDateElem",
            "config",
        ].forEach(function (k) {
            try {
                delete self[k];
            }
            catch (_) { }
        });
    }
    function isCalendarElem(elem) {
        return self.calendarContainer.contains(elem);
    }
    function documentClick(e) {
        if (self.isOpen && !self.config.inline) {
            var eventTarget_1 = Object(__WEBPACK_IMPORTED_MODULE_3__utils_dom__["e" /* getEventTarget */])(e);
            var isCalendarElement = isCalendarElem(eventTarget_1);
            var isInput = eventTarget_1 === self.input ||
                eventTarget_1 === self.altInput ||
                self.element.contains(eventTarget_1) ||
                (e.path &&
                    e.path.indexOf &&
                    (~e.path.indexOf(self.input) ||
                        ~e.path.indexOf(self.altInput)));
            var lostFocus = !isInput &&
                !isCalendarElement &&
                !isCalendarElem(e.relatedTarget);
            var isIgnored = !self.config.ignoredFocusElements.some(function (elem) {
                return elem.contains(eventTarget_1);
            });
            if (lostFocus && isIgnored) {
                if (self.config.allowInput) {
                    self.setDate(self._input.value, false, self.config.altInput
                        ? self.config.altFormat
                        : self.config.dateFormat);
                }
                if (self.timeContainer !== undefined &&
                    self.minuteElement !== undefined &&
                    self.hourElement !== undefined &&
                    self.input.value !== "" &&
                    self.input.value !== undefined) {
                    updateTime();
                }
                self.close();
                if (self.config &&
                    self.config.mode === "range" &&
                    self.selectedDates.length === 1)
                    self.clear(false);
            }
        }
    }
    function changeYear(newYear) {
        if (!newYear ||
            (self.config.minDate && newYear < self.config.minDate.getFullYear()) ||
            (self.config.maxDate && newYear > self.config.maxDate.getFullYear()))
            return;
        var newYearNum = newYear, isNewYear = self.currentYear !== newYearNum;
        self.currentYear = newYearNum || self.currentYear;
        if (self.config.maxDate &&
            self.currentYear === self.config.maxDate.getFullYear()) {
            self.currentMonth = Math.min(self.config.maxDate.getMonth(), self.currentMonth);
        }
        else if (self.config.minDate &&
            self.currentYear === self.config.minDate.getFullYear()) {
            self.currentMonth = Math.max(self.config.minDate.getMonth(), self.currentMonth);
        }
        if (isNewYear) {
            self.redraw();
            triggerEvent("onYearChange");
            buildMonthSwitch();
        }
    }
    function isEnabled(date, timeless) {
        var _a;
        if (timeless === void 0) { timeless = true; }
        var dateToCheck = self.parseDate(date, undefined, timeless);
        if ((self.config.minDate &&
            dateToCheck &&
            Object(__WEBPACK_IMPORTED_MODULE_4__utils_dates__["b" /* compareDates */])(dateToCheck, self.config.minDate, timeless !== undefined ? timeless : !self.minDateHasTime) < 0) ||
            (self.config.maxDate &&
                dateToCheck &&
                Object(__WEBPACK_IMPORTED_MODULE_4__utils_dates__["b" /* compareDates */])(dateToCheck, self.config.maxDate, timeless !== undefined ? timeless : !self.maxDateHasTime) > 0))
            return false;
        if (!self.config.enable && self.config.disable.length === 0)
            return true;
        if (dateToCheck === undefined)
            return false;
        var bool = !!self.config.enable, array = (_a = self.config.enable) !== null && _a !== void 0 ? _a : self.config.disable;
        for (var i = 0, d = void 0; i < array.length; i++) {
            d = array[i];
            if (typeof d === "function" &&
                d(dateToCheck))
                return bool;
            else if (d instanceof Date &&
                dateToCheck !== undefined &&
                d.getTime() === dateToCheck.getTime())
                return bool;
            else if (typeof d === "string") {
                var parsed = self.parseDate(d, undefined, true);
                return parsed && parsed.getTime() === dateToCheck.getTime()
                    ? bool
                    : !bool;
            }
            else if (typeof d === "object" &&
                dateToCheck !== undefined &&
                d.from &&
                d.to &&
                dateToCheck.getTime() >= d.from.getTime() &&
                dateToCheck.getTime() <= d.to.getTime())
                return bool;
        }
        return !bool;
    }
    function isInView(elem) {
        if (self.daysContainer !== undefined)
            return (elem.className.indexOf("hidden") === -1 &&
                elem.className.indexOf("flatpickr-disabled") === -1 &&
                self.daysContainer.contains(elem));
        return false;
    }
    function onBlur(e) {
        var isInput = e.target === self._input;
        var valueChanged = self._input.value.trimEnd() !== getDateStr();
        if (isInput &&
            valueChanged &&
            !(e.relatedTarget && isCalendarElem(e.relatedTarget))) {
            self.setDate(self._input.value, true, e.target === self.altInput
                ? self.config.altFormat
                : self.config.dateFormat);
        }
    }
    function onKeyDown(e) {
        var eventTarget = Object(__WEBPACK_IMPORTED_MODULE_3__utils_dom__["e" /* getEventTarget */])(e);
        var isInput = self.config.wrap
            ? element.contains(eventTarget)
            : eventTarget === self._input;
        var allowInput = self.config.allowInput;
        var allowKeydown = self.isOpen && (!allowInput || !isInput);
        var allowInlineKeydown = self.config.inline && isInput && !allowInput;
        if (e.keyCode === 13 && isInput) {
            if (allowInput) {
                self.setDate(self._input.value, true, eventTarget === self.altInput
                    ? self.config.altFormat
                    : self.config.dateFormat);
                self.close();
                return eventTarget.blur();
            }
            else {
                self.open();
            }
        }
        else if (isCalendarElem(eventTarget) ||
            allowKeydown ||
            allowInlineKeydown) {
            var isTimeObj = !!self.timeContainer &&
                self.timeContainer.contains(eventTarget);
            switch (e.keyCode) {
                case 13:
                    if (isTimeObj) {
                        e.preventDefault();
                        updateTime();
                        focusAndClose();
                    }
                    else
                        selectDate(e);
                    break;
                case 27:
                    e.preventDefault();
                    focusAndClose();
                    break;
                case 8:
                case 46:
                    if (isInput && !self.config.allowInput) {
                        e.preventDefault();
                        self.clear();
                    }
                    break;
                case 37:
                case 39:
                    if (!isTimeObj && !isInput) {
                        e.preventDefault();
                        var activeElement = getClosestActiveElement();
                        if (self.daysContainer !== undefined &&
                            (allowInput === false ||
                                (activeElement && isInView(activeElement)))) {
                            var delta_1 = e.keyCode === 39 ? 1 : -1;
                            if (!e.ctrlKey)
                                focusOnDay(undefined, delta_1);
                            else {
                                e.stopPropagation();
                                changeMonth(delta_1);
                                focusOnDay(getFirstAvailableDay(1), 0);
                            }
                        }
                    }
                    else if (self.hourElement)
                        self.hourElement.focus();
                    break;
                case 38:
                case 40:
                    e.preventDefault();
                    var delta = e.keyCode === 40 ? 1 : -1;
                    if ((self.daysContainer &&
                        eventTarget.$i !== undefined) ||
                        eventTarget === self.input ||
                        eventTarget === self.altInput) {
                        if (e.ctrlKey) {
                            e.stopPropagation();
                            changeYear(self.currentYear - delta);
                            focusOnDay(getFirstAvailableDay(1), 0);
                        }
                        else if (!isTimeObj)
                            focusOnDay(undefined, delta * 7);
                    }
                    else if (eventTarget === self.currentYearElement) {
                        changeYear(self.currentYear - delta);
                    }
                    else if (self.config.enableTime) {
                        if (!isTimeObj && self.hourElement)
                            self.hourElement.focus();
                        updateTime(e);
                        self._debouncedChange();
                    }
                    break;
                case 9:
                    if (isTimeObj) {
                        var elems = [
                            self.hourElement,
                            self.minuteElement,
                            self.secondElement,
                            self.amPM,
                        ]
                            .concat(self.pluginElements)
                            .filter(function (x) { return x; });
                        var i = elems.indexOf(eventTarget);
                        if (i !== -1) {
                            var target = elems[i + (e.shiftKey ? -1 : 1)];
                            e.preventDefault();
                            (target || self._input).focus();
                        }
                    }
                    else if (!self.config.noCalendar &&
                        self.daysContainer &&
                        self.daysContainer.contains(eventTarget) &&
                        e.shiftKey) {
                        e.preventDefault();
                        self._input.focus();
                    }
                    break;
                default:
                    break;
            }
        }
        if (self.amPM !== undefined && eventTarget === self.amPM) {
            switch (e.key) {
                case self.l10n.amPM[0].charAt(0):
                case self.l10n.amPM[0].charAt(0).toLowerCase():
                    self.amPM.textContent = self.l10n.amPM[0];
                    setHoursFromInputs();
                    updateValue();
                    break;
                case self.l10n.amPM[1].charAt(0):
                case self.l10n.amPM[1].charAt(0).toLowerCase():
                    self.amPM.textContent = self.l10n.amPM[1];
                    setHoursFromInputs();
                    updateValue();
                    break;
            }
        }
        if (isInput || isCalendarElem(eventTarget)) {
            triggerEvent("onKeyDown", e);
        }
    }
    function onMouseOver(elem, cellClass) {
        if (cellClass === void 0) { cellClass = "flatpickr-day"; }
        if (self.selectedDates.length !== 1 ||
            (elem &&
                (!elem.classList.contains(cellClass) ||
                    elem.classList.contains("flatpickr-disabled"))))
            return;
        var hoverDate = elem
            ? elem.dateObj.getTime()
            : self.days.firstElementChild.dateObj.getTime(), initialDate = self.parseDate(self.selectedDates[0], undefined, true).getTime(), rangeStartDate = Math.min(hoverDate, self.selectedDates[0].getTime()), rangeEndDate = Math.max(hoverDate, self.selectedDates[0].getTime());
        var containsDisabled = false;
        var minRange = 0, maxRange = 0;
        for (var t = rangeStartDate; t < rangeEndDate; t += __WEBPACK_IMPORTED_MODULE_4__utils_dates__["e" /* duration */].DAY) {
            if (!isEnabled(new Date(t), true)) {
                containsDisabled =
                    containsDisabled || (t > rangeStartDate && t < rangeEndDate);
                if (t < initialDate && (!minRange || t > minRange))
                    minRange = t;
                else if (t > initialDate && (!maxRange || t < maxRange))
                    maxRange = t;
            }
        }
        var hoverableCells = Array.from(self.rContainer.querySelectorAll("*:nth-child(-n+" + self.config.showMonths + ") > ." + cellClass));
        hoverableCells.forEach(function (dayElem) {
            var date = dayElem.dateObj;
            var timestamp = date.getTime();
            var outOfRange = (minRange > 0 && timestamp < minRange) ||
                (maxRange > 0 && timestamp > maxRange);
            if (outOfRange) {
                dayElem.classList.add("notAllowed");
                ["inRange", "startRange", "endRange"].forEach(function (c) {
                    dayElem.classList.remove(c);
                });
                return;
            }
            else if (containsDisabled && !outOfRange)
                return;
            ["startRange", "inRange", "endRange", "notAllowed"].forEach(function (c) {
                dayElem.classList.remove(c);
            });
            if (elem !== undefined) {
                elem.classList.add(hoverDate <= self.selectedDates[0].getTime()
                    ? "startRange"
                    : "endRange");
                if (initialDate < hoverDate && timestamp === initialDate)
                    dayElem.classList.add("startRange");
                else if (initialDate > hoverDate && timestamp === initialDate)
                    dayElem.classList.add("endRange");
                if (timestamp >= minRange &&
                    (maxRange === 0 || timestamp <= maxRange) &&
                    Object(__WEBPACK_IMPORTED_MODULE_4__utils_dates__["g" /* isBetween */])(timestamp, initialDate, hoverDate))
                    dayElem.classList.add("inRange");
            }
        });
    }
    function onResize() {
        if (self.isOpen && !self.config.static && !self.config.inline)
            positionCalendar();
    }
    function open(e, positionElement) {
        if (positionElement === void 0) { positionElement = self._positionElement; }
        if (self.isMobile === true) {
            if (e) {
                e.preventDefault();
                var eventTarget = Object(__WEBPACK_IMPORTED_MODULE_3__utils_dom__["e" /* getEventTarget */])(e);
                if (eventTarget) {
                    eventTarget.blur();
                }
            }
            if (self.mobileInput !== undefined) {
                self.mobileInput.focus();
                self.mobileInput.click();
            }
            triggerEvent("onOpen");
            return;
        }
        else if (self._input.disabled || self.config.inline) {
            return;
        }
        var wasOpen = self.isOpen;
        self.isOpen = true;
        if (!wasOpen) {
            self.calendarContainer.classList.add("open");
            self._input.classList.add("active");
            triggerEvent("onOpen");
            positionCalendar(positionElement);
        }
        if (self.config.enableTime === true && self.config.noCalendar === true) {
            if (self.config.allowInput === false &&
                (e === undefined ||
                    !self.timeContainer.contains(e.relatedTarget))) {
                setTimeout(function () { return self.hourElement.select(); }, 50);
            }
        }
    }
    function minMaxDateSetter(type) {
        return function (date) {
            var dateObj = (self.config["_" + type + "Date"] = self.parseDate(date, self.config.dateFormat));
            var inverseDateObj = self.config["_" + (type === "min" ? "max" : "min") + "Date"];
            if (dateObj !== undefined) {
                self[type === "min" ? "minDateHasTime" : "maxDateHasTime"] =
                    dateObj.getHours() > 0 ||
                        dateObj.getMinutes() > 0 ||
                        dateObj.getSeconds() > 0;
            }
            if (self.selectedDates) {
                self.selectedDates = self.selectedDates.filter(function (d) { return isEnabled(d); });
                if (!self.selectedDates.length && type === "min")
                    setHoursFromDate(dateObj);
                updateValue();
            }
            if (self.daysContainer) {
                redraw();
                if (dateObj !== undefined)
                    self.currentYearElement[type] = dateObj.getFullYear().toString();
                else
                    self.currentYearElement.removeAttribute(type);
                self.currentYearElement.disabled =
                    !!inverseDateObj &&
                        dateObj !== undefined &&
                        inverseDateObj.getFullYear() === dateObj.getFullYear();
            }
        };
    }
    function parseConfig() {
        var boolOpts = [
            "wrap",
            "weekNumbers",
            "allowInput",
            "allowInvalidPreload",
            "clickOpens",
            "time_24hr",
            "enableTime",
            "noCalendar",
            "altInput",
            "shorthandCurrentMonth",
            "inline",
            "static",
            "enableSeconds",
            "disableMobile",
        ];
        var userConfig = __assign(__assign({}, JSON.parse(JSON.stringify(element.dataset || {}))), instanceConfig);
        var formats = {};
        self.config.parseDate = userConfig.parseDate;
        self.config.formatDate = userConfig.formatDate;
        Object.defineProperty(self.config, "enable", {
            get: function () { return self.config._enable; },
            set: function (dates) {
                self.config._enable = parseDateRules(dates);
            },
        });
        Object.defineProperty(self.config, "disable", {
            get: function () { return self.config._disable; },
            set: function (dates) {
                self.config._disable = parseDateRules(dates);
            },
        });
        var timeMode = userConfig.mode === "time";
        if (!userConfig.dateFormat && (userConfig.enableTime || timeMode)) {
            var defaultDateFormat = flatpickr.defaultConfig.dateFormat || __WEBPACK_IMPORTED_MODULE_0__types_options__["b" /* defaults */].dateFormat;
            formats.dateFormat =
                userConfig.noCalendar || timeMode
                    ? "H:i" + (userConfig.enableSeconds ? ":S" : "")
                    : defaultDateFormat + " H:i" + (userConfig.enableSeconds ? ":S" : "");
        }
        if (userConfig.altInput &&
            (userConfig.enableTime || timeMode) &&
            !userConfig.altFormat) {
            var defaultAltFormat = flatpickr.defaultConfig.altFormat || __WEBPACK_IMPORTED_MODULE_0__types_options__["b" /* defaults */].altFormat;
            formats.altFormat =
                userConfig.noCalendar || timeMode
                    ? "h:i" + (userConfig.enableSeconds ? ":S K" : " K")
                    : defaultAltFormat + (" h:i" + (userConfig.enableSeconds ? ":S" : "") + " K");
        }
        Object.defineProperty(self.config, "minDate", {
            get: function () { return self.config._minDate; },
            set: minMaxDateSetter("min"),
        });
        Object.defineProperty(self.config, "maxDate", {
            get: function () { return self.config._maxDate; },
            set: minMaxDateSetter("max"),
        });
        var minMaxTimeSetter = function (type) { return function (val) {
            self.config[type === "min" ? "_minTime" : "_maxTime"] = self.parseDate(val, "H:i:S");
        }; };
        Object.defineProperty(self.config, "minTime", {
            get: function () { return self.config._minTime; },
            set: minMaxTimeSetter("min"),
        });
        Object.defineProperty(self.config, "maxTime", {
            get: function () { return self.config._maxTime; },
            set: minMaxTimeSetter("max"),
        });
        if (userConfig.mode === "time") {
            self.config.noCalendar = true;
            self.config.enableTime = true;
        }
        Object.assign(self.config, formats, userConfig);
        for (var i = 0; i < boolOpts.length; i++)
            self.config[boolOpts[i]] =
                self.config[boolOpts[i]] === true ||
                    self.config[boolOpts[i]] === "true";
        __WEBPACK_IMPORTED_MODULE_0__types_options__["a" /* HOOKS */].filter(function (hook) { return self.config[hook] !== undefined; }).forEach(function (hook) {
            self.config[hook] = Object(__WEBPACK_IMPORTED_MODULE_2__utils__["a" /* arrayify */])(self.config[hook] || []).map(bindToInstance);
        });
        self.isMobile =
            !self.config.disableMobile &&
                !self.config.inline &&
                self.config.mode === "single" &&
                !self.config.disable.length &&
                !self.config.enable &&
                !self.config.weekNumbers &&
                /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        for (var i = 0; i < self.config.plugins.length; i++) {
            var pluginConf = self.config.plugins[i](self) || {};
            for (var key in pluginConf) {
                if (__WEBPACK_IMPORTED_MODULE_0__types_options__["a" /* HOOKS */].indexOf(key) > -1) {
                    self.config[key] = Object(__WEBPACK_IMPORTED_MODULE_2__utils__["a" /* arrayify */])(pluginConf[key])
                        .map(bindToInstance)
                        .concat(self.config[key]);
                }
                else if (typeof userConfig[key] === "undefined")
                    self.config[key] = pluginConf[key];
            }
        }
        if (!userConfig.altInputClass) {
            self.config.altInputClass =
                getInputElem().className + " " + self.config.altInputClass;
        }
        triggerEvent("onParseConfig");
    }
    function getInputElem() {
        return self.config.wrap
            ? element.querySelector("[data-input]")
            : element;
    }
    function setupLocale() {
        if (typeof self.config.locale !== "object" &&
            typeof flatpickr.l10ns[self.config.locale] === "undefined")
            self.config.errorHandler(new Error("flatpickr: invalid locale " + self.config.locale));
        self.l10n = __assign(__assign({}, flatpickr.l10ns.default), (typeof self.config.locale === "object"
            ? self.config.locale
            : self.config.locale !== "default"
                ? flatpickr.l10ns[self.config.locale]
                : undefined));
        __WEBPACK_IMPORTED_MODULE_5__utils_formatting__["d" /* tokenRegex */].D = "(" + self.l10n.weekdays.shorthand.join("|") + ")";
        __WEBPACK_IMPORTED_MODULE_5__utils_formatting__["d" /* tokenRegex */].l = "(" + self.l10n.weekdays.longhand.join("|") + ")";
        __WEBPACK_IMPORTED_MODULE_5__utils_formatting__["d" /* tokenRegex */].M = "(" + self.l10n.months.shorthand.join("|") + ")";
        __WEBPACK_IMPORTED_MODULE_5__utils_formatting__["d" /* tokenRegex */].F = "(" + self.l10n.months.longhand.join("|") + ")";
        __WEBPACK_IMPORTED_MODULE_5__utils_formatting__["d" /* tokenRegex */].K = "(" + self.l10n.amPM[0] + "|" + self.l10n.amPM[1] + "|" + self.l10n.amPM[0].toLowerCase() + "|" + self.l10n.amPM[1].toLowerCase() + ")";
        var userConfig = __assign(__assign({}, instanceConfig), JSON.parse(JSON.stringify(element.dataset || {})));
        if (userConfig.time_24hr === undefined &&
            flatpickr.defaultConfig.time_24hr === undefined) {
            self.config.time_24hr = self.l10n.time_24hr;
        }
        self.formatDate = Object(__WEBPACK_IMPORTED_MODULE_4__utils_dates__["c" /* createDateFormatter */])(self);
        self.parseDate = Object(__WEBPACK_IMPORTED_MODULE_4__utils_dates__["d" /* createDateParser */])({ config: self.config, l10n: self.l10n });
    }
    function positionCalendar(customPositionElement) {
        if (typeof self.config.position === "function") {
            return void self.config.position(self, customPositionElement);
        }
        if (self.calendarContainer === undefined)
            return;
        triggerEvent("onPreCalendarPosition");
        var positionElement = customPositionElement || self._positionElement;
        var calendarHeight = Array.prototype.reduce.call(self.calendarContainer.children, (function (acc, child) { return acc + child.offsetHeight; }), 0), calendarWidth = self.calendarContainer.offsetWidth, configPos = self.config.position.split(" "), configPosVertical = configPos[0], configPosHorizontal = configPos.length > 1 ? configPos[1] : null, inputBounds = positionElement.getBoundingClientRect(), distanceFromBottom = window.innerHeight - inputBounds.bottom, showOnTop = configPosVertical === "above" ||
            (configPosVertical !== "below" &&
                distanceFromBottom < calendarHeight &&
                inputBounds.top > calendarHeight);
        var top = window.pageYOffset +
            inputBounds.top +
            (!showOnTop ? positionElement.offsetHeight + 2 : -calendarHeight - 2);
        Object(__WEBPACK_IMPORTED_MODULE_3__utils_dom__["f" /* toggleClass */])(self.calendarContainer, "arrowTop", !showOnTop);
        Object(__WEBPACK_IMPORTED_MODULE_3__utils_dom__["f" /* toggleClass */])(self.calendarContainer, "arrowBottom", showOnTop);
        if (self.config.inline)
            return;
        var left = window.pageXOffset + inputBounds.left;
        var isCenter = false;
        var isRight = false;
        if (configPosHorizontal === "center") {
            left -= (calendarWidth - inputBounds.width) / 2;
            isCenter = true;
        }
        else if (configPosHorizontal === "right") {
            left -= calendarWidth - inputBounds.width;
            isRight = true;
        }
        Object(__WEBPACK_IMPORTED_MODULE_3__utils_dom__["f" /* toggleClass */])(self.calendarContainer, "arrowLeft", !isCenter && !isRight);
        Object(__WEBPACK_IMPORTED_MODULE_3__utils_dom__["f" /* toggleClass */])(self.calendarContainer, "arrowCenter", isCenter);
        Object(__WEBPACK_IMPORTED_MODULE_3__utils_dom__["f" /* toggleClass */])(self.calendarContainer, "arrowRight", isRight);
        var right = window.document.body.offsetWidth -
            (window.pageXOffset + inputBounds.right);
        var rightMost = left + calendarWidth > window.document.body.offsetWidth;
        var centerMost = right + calendarWidth > window.document.body.offsetWidth;
        Object(__WEBPACK_IMPORTED_MODULE_3__utils_dom__["f" /* toggleClass */])(self.calendarContainer, "rightMost", rightMost);
        if (self.config.static)
            return;
        self.calendarContainer.style.top = top + "px";
        if (!rightMost) {
            self.calendarContainer.style.left = left + "px";
            self.calendarContainer.style.right = "auto";
        }
        else if (!centerMost) {
            self.calendarContainer.style.left = "auto";
            self.calendarContainer.style.right = right + "px";
        }
        else {
            var doc = getDocumentStyleSheet();
            if (doc === undefined)
                return;
            var bodyWidth = window.document.body.offsetWidth;
            var centerLeft = Math.max(0, bodyWidth / 2 - calendarWidth / 2);
            var centerBefore = ".flatpickr-calendar.centerMost:before";
            var centerAfter = ".flatpickr-calendar.centerMost:after";
            var centerIndex = doc.cssRules.length;
            var centerStyle = "{left:" + inputBounds.left + "px;right:auto;}";
            Object(__WEBPACK_IMPORTED_MODULE_3__utils_dom__["f" /* toggleClass */])(self.calendarContainer, "rightMost", false);
            Object(__WEBPACK_IMPORTED_MODULE_3__utils_dom__["f" /* toggleClass */])(self.calendarContainer, "centerMost", true);
            doc.insertRule(centerBefore + "," + centerAfter + centerStyle, centerIndex);
            self.calendarContainer.style.left = centerLeft + "px";
            self.calendarContainer.style.right = "auto";
        }
    }
    function getDocumentStyleSheet() {
        var editableSheet = null;
        for (var i = 0; i < document.styleSheets.length; i++) {
            var sheet = document.styleSheets[i];
            if (!sheet.cssRules)
                continue;
            try {
                sheet.cssRules;
            }
            catch (err) {
                continue;
            }
            editableSheet = sheet;
            break;
        }
        return editableSheet != null ? editableSheet : createStyleSheet();
    }
    function createStyleSheet() {
        var style = document.createElement("style");
        document.head.appendChild(style);
        return style.sheet;
    }
    function redraw() {
        if (self.config.noCalendar || self.isMobile)
            return;
        buildMonthSwitch();
        updateNavigationCurrentMonth();
        buildDays();
    }
    function focusAndClose() {
        self._input.focus();
        if (window.navigator.userAgent.indexOf("MSIE") !== -1 ||
            navigator.msMaxTouchPoints !== undefined) {
            setTimeout(self.close, 0);
        }
        else {
            self.close();
        }
    }
    function selectDate(e) {
        e.preventDefault();
        e.stopPropagation();
        var isSelectable = function (day) {
            return day.classList &&
                day.classList.contains("flatpickr-day") &&
                !day.classList.contains("flatpickr-disabled") &&
                !day.classList.contains("notAllowed");
        };
        var t = Object(__WEBPACK_IMPORTED_MODULE_3__utils_dom__["d" /* findParent */])(Object(__WEBPACK_IMPORTED_MODULE_3__utils_dom__["e" /* getEventTarget */])(e), isSelectable);
        if (t === undefined)
            return;
        var target = t;
        var selectedDate = (self.latestSelectedDateObj = new Date(target.dateObj.getTime()));
        var shouldChangeMonth = (selectedDate.getMonth() < self.currentMonth ||
            selectedDate.getMonth() >
                self.currentMonth + self.config.showMonths - 1) &&
            self.config.mode !== "range";
        self.selectedDateElem = target;
        if (self.config.mode === "single")
            self.selectedDates = [selectedDate];
        else if (self.config.mode === "multiple") {
            var selectedIndex = isDateSelected(selectedDate);
            if (selectedIndex)
                self.selectedDates.splice(parseInt(selectedIndex), 1);
            else
                self.selectedDates.push(selectedDate);
        }
        else if (self.config.mode === "range") {
            if (self.selectedDates.length === 2) {
                self.clear(false, false);
            }
            self.latestSelectedDateObj = selectedDate;
            self.selectedDates.push(selectedDate);
            if (Object(__WEBPACK_IMPORTED_MODULE_4__utils_dates__["b" /* compareDates */])(selectedDate, self.selectedDates[0], true) !== 0)
                self.selectedDates.sort(function (a, b) { return a.getTime() - b.getTime(); });
        }
        setHoursFromInputs();
        if (shouldChangeMonth) {
            var isNewYear = self.currentYear !== selectedDate.getFullYear();
            self.currentYear = selectedDate.getFullYear();
            self.currentMonth = selectedDate.getMonth();
            if (isNewYear) {
                triggerEvent("onYearChange");
                buildMonthSwitch();
            }
            triggerEvent("onMonthChange");
        }
        updateNavigationCurrentMonth();
        buildDays();
        updateValue();
        if (!shouldChangeMonth &&
            self.config.mode !== "range" &&
            self.config.showMonths === 1)
            focusOnDayElem(target);
        else if (self.selectedDateElem !== undefined &&
            self.hourElement === undefined) {
            self.selectedDateElem && self.selectedDateElem.focus();
        }
        if (self.hourElement !== undefined)
            self.hourElement !== undefined && self.hourElement.focus();
        if (self.config.closeOnSelect) {
            var single = self.config.mode === "single" && !self.config.enableTime;
            var range = self.config.mode === "range" &&
                self.selectedDates.length === 2 &&
                !self.config.enableTime;
            if (single || range) {
                focusAndClose();
            }
        }
        triggerChange();
    }
    var CALLBACKS = {
        locale: [setupLocale, updateWeekdays],
        showMonths: [buildMonths, setCalendarWidth, buildWeekdays],
        minDate: [jumpToDate],
        maxDate: [jumpToDate],
        positionElement: [updatePositionElement],
        clickOpens: [
            function () {
                if (self.config.clickOpens === true) {
                    bind(self._input, "focus", self.open);
                    bind(self._input, "click", self.open);
                }
                else {
                    self._input.removeEventListener("focus", self.open);
                    self._input.removeEventListener("click", self.open);
                }
            },
        ],
    };
    function set(option, value) {
        if (option !== null && typeof option === "object") {
            Object.assign(self.config, option);
            for (var key in option) {
                if (CALLBACKS[key] !== undefined)
                    CALLBACKS[key].forEach(function (x) { return x(); });
            }
        }
        else {
            self.config[option] = value;
            if (CALLBACKS[option] !== undefined)
                CALLBACKS[option].forEach(function (x) { return x(); });
            else if (__WEBPACK_IMPORTED_MODULE_0__types_options__["a" /* HOOKS */].indexOf(option) > -1)
                self.config[option] = Object(__WEBPACK_IMPORTED_MODULE_2__utils__["a" /* arrayify */])(value);
        }
        self.redraw();
        updateValue(true);
    }
    function setSelectedDate(inputDate, format) {
        var dates = [];
        if (inputDate instanceof Array)
            dates = inputDate.map(function (d) { return self.parseDate(d, format); });
        else if (inputDate instanceof Date || typeof inputDate === "number")
            dates = [self.parseDate(inputDate, format)];
        else if (typeof inputDate === "string") {
            switch (self.config.mode) {
                case "single":
                case "time":
                    dates = [self.parseDate(inputDate, format)];
                    break;
                case "multiple":
                    dates = inputDate
                        .split(self.config.conjunction)
                        .map(function (date) { return self.parseDate(date, format); });
                    break;
                case "range":
                    dates = inputDate
                        .split(self.l10n.rangeSeparator)
                        .map(function (date) { return self.parseDate(date, format); });
                    break;
                default:
                    break;
            }
        }
        else
            self.config.errorHandler(new Error("Invalid date supplied: " + JSON.stringify(inputDate)));
        self.selectedDates = (self.config.allowInvalidPreload
            ? dates
            : dates.filter(function (d) { return d instanceof Date && isEnabled(d, false); }));
        if (self.config.mode === "range")
            self.selectedDates.sort(function (a, b) { return a.getTime() - b.getTime(); });
    }
    function setDate(date, triggerChange, format) {
        if (triggerChange === void 0) { triggerChange = false; }
        if (format === void 0) { format = self.config.dateFormat; }
        if ((date !== 0 && !date) || (date instanceof Array && date.length === 0))
            return self.clear(triggerChange);
        setSelectedDate(date, format);
        self.latestSelectedDateObj =
            self.selectedDates[self.selectedDates.length - 1];
        self.redraw();
        jumpToDate(undefined, triggerChange);
        setHoursFromDate();
        if (self.selectedDates.length === 0) {
            self.clear(false);
        }
        updateValue(triggerChange);
        if (triggerChange)
            triggerEvent("onChange");
    }
    function parseDateRules(arr) {
        return arr
            .slice()
            .map(function (rule) {
            if (typeof rule === "string" ||
                typeof rule === "number" ||
                rule instanceof Date) {
                return self.parseDate(rule, undefined, true);
            }
            else if (rule &&
                typeof rule === "object" &&
                rule.from &&
                rule.to)
                return {
                    from: self.parseDate(rule.from, undefined),
                    to: self.parseDate(rule.to, undefined),
                };
            return rule;
        })
            .filter(function (x) { return x; });
    }
    function setupDates() {
        self.selectedDates = [];
        self.now = self.parseDate(self.config.now) || new Date();
        var preloadedDate = self.config.defaultDate ||
            ((self.input.nodeName === "INPUT" ||
                self.input.nodeName === "TEXTAREA") &&
                self.input.placeholder &&
                self.input.value === self.input.placeholder
                ? null
                : self.input.value);
        if (preloadedDate)
            setSelectedDate(preloadedDate, self.config.dateFormat);
        self._initialDate =
            self.selectedDates.length > 0
                ? self.selectedDates[0]
                : self.config.minDate &&
                    self.config.minDate.getTime() > self.now.getTime()
                    ? self.config.minDate
                    : self.config.maxDate &&
                        self.config.maxDate.getTime() < self.now.getTime()
                        ? self.config.maxDate
                        : self.now;
        self.currentYear = self._initialDate.getFullYear();
        self.currentMonth = self._initialDate.getMonth();
        if (self.selectedDates.length > 0)
            self.latestSelectedDateObj = self.selectedDates[0];
        if (self.config.minTime !== undefined)
            self.config.minTime = self.parseDate(self.config.minTime, "H:i");
        if (self.config.maxTime !== undefined)
            self.config.maxTime = self.parseDate(self.config.maxTime, "H:i");
        self.minDateHasTime =
            !!self.config.minDate &&
                (self.config.minDate.getHours() > 0 ||
                    self.config.minDate.getMinutes() > 0 ||
                    self.config.minDate.getSeconds() > 0);
        self.maxDateHasTime =
            !!self.config.maxDate &&
                (self.config.maxDate.getHours() > 0 ||
                    self.config.maxDate.getMinutes() > 0 ||
                    self.config.maxDate.getSeconds() > 0);
    }
    function setupInputs() {
        self.input = getInputElem();
        if (!self.input) {
            self.config.errorHandler(new Error("Invalid input element specified"));
            return;
        }
        self.input._type = self.input.type;
        self.input.type = "text";
        self.input.classList.add("flatpickr-input");
        self._input = self.input;
        if (self.config.altInput) {
            self.altInput = Object(__WEBPACK_IMPORTED_MODULE_3__utils_dom__["b" /* createElement */])(self.input.nodeName, self.config.altInputClass);
            self._input = self.altInput;
            self.altInput.placeholder = self.input.placeholder;
            self.altInput.disabled = self.input.disabled;
            self.altInput.required = self.input.required;
            self.altInput.tabIndex = self.input.tabIndex;
            self.altInput.type = "text";
            self.input.setAttribute("type", "hidden");
            if (!self.config.static && self.input.parentNode)
                self.input.parentNode.insertBefore(self.altInput, self.input.nextSibling);
        }
        if (!self.config.allowInput)
            self._input.setAttribute("readonly", "readonly");
        updatePositionElement();
    }
    function updatePositionElement() {
        self._positionElement = self.config.positionElement || self._input;
    }
    function setupMobile() {
        var inputType = self.config.enableTime
            ? self.config.noCalendar
                ? "time"
                : "datetime-local"
            : "date";
        self.mobileInput = Object(__WEBPACK_IMPORTED_MODULE_3__utils_dom__["b" /* createElement */])("input", self.input.className + " flatpickr-mobile");
        self.mobileInput.tabIndex = 1;
        self.mobileInput.type = inputType;
        self.mobileInput.disabled = self.input.disabled;
        self.mobileInput.required = self.input.required;
        self.mobileInput.placeholder = self.input.placeholder;
        self.mobileFormatStr =
            inputType === "datetime-local"
                ? "Y-m-d\\TH:i:S"
                : inputType === "date"
                    ? "Y-m-d"
                    : "H:i:S";
        if (self.selectedDates.length > 0) {
            self.mobileInput.defaultValue = self.mobileInput.value = self.formatDate(self.selectedDates[0], self.mobileFormatStr);
        }
        if (self.config.minDate)
            self.mobileInput.min = self.formatDate(self.config.minDate, "Y-m-d");
        if (self.config.maxDate)
            self.mobileInput.max = self.formatDate(self.config.maxDate, "Y-m-d");
        if (self.input.getAttribute("step"))
            self.mobileInput.step = String(self.input.getAttribute("step"));
        self.input.type = "hidden";
        if (self.altInput !== undefined)
            self.altInput.type = "hidden";
        try {
            if (self.input.parentNode)
                self.input.parentNode.insertBefore(self.mobileInput, self.input.nextSibling);
        }
        catch (_a) { }
        bind(self.mobileInput, "change", function (e) {
            self.setDate(Object(__WEBPACK_IMPORTED_MODULE_3__utils_dom__["e" /* getEventTarget */])(e).value, false, self.mobileFormatStr);
            triggerEvent("onChange");
            triggerEvent("onClose");
        });
    }
    function toggle(e) {
        if (self.isOpen === true)
            return self.close();
        self.open(e);
    }
    function triggerEvent(event, data) {
        if (self.config === undefined)
            return;
        var hooks = self.config[event];
        if (hooks !== undefined && hooks.length > 0) {
            for (var i = 0; hooks[i] && i < hooks.length; i++)
                hooks[i](self.selectedDates, self.input.value, self, data);
        }
        if (event === "onChange") {
            self.input.dispatchEvent(createEvent("change"));
            self.input.dispatchEvent(createEvent("input"));
        }
    }
    function createEvent(name) {
        var e = document.createEvent("Event");
        e.initEvent(name, true, true);
        return e;
    }
    function isDateSelected(date) {
        for (var i = 0; i < self.selectedDates.length; i++) {
            var selectedDate = self.selectedDates[i];
            if (selectedDate instanceof Date &&
                Object(__WEBPACK_IMPORTED_MODULE_4__utils_dates__["b" /* compareDates */])(selectedDate, date) === 0)
                return "" + i;
        }
        return false;
    }
    function isDateInRange(date) {
        if (self.config.mode !== "range" || self.selectedDates.length < 2)
            return false;
        return (Object(__WEBPACK_IMPORTED_MODULE_4__utils_dates__["b" /* compareDates */])(date, self.selectedDates[0]) >= 0 &&
            Object(__WEBPACK_IMPORTED_MODULE_4__utils_dates__["b" /* compareDates */])(date, self.selectedDates[1]) <= 0);
    }
    function updateNavigationCurrentMonth() {
        if (self.config.noCalendar || self.isMobile || !self.monthNav)
            return;
        self.yearElements.forEach(function (yearElement, i) {
            var d = new Date(self.currentYear, self.currentMonth, 1);
            d.setMonth(self.currentMonth + i);
            if (self.config.showMonths > 1 ||
                self.config.monthSelectorType === "static") {
                self.monthElements[i].textContent =
                    Object(__WEBPACK_IMPORTED_MODULE_5__utils_formatting__["b" /* monthToStr */])(d.getMonth(), self.config.shorthandCurrentMonth, self.l10n) + " ";
            }
            else {
                self.monthsDropdownContainer.value = d.getMonth().toString();
            }
            yearElement.value = d.getFullYear().toString();
        });
        self._hidePrevMonthArrow =
            self.config.minDate !== undefined &&
                (self.currentYear === self.config.minDate.getFullYear()
                    ? self.currentMonth <= self.config.minDate.getMonth()
                    : self.currentYear < self.config.minDate.getFullYear());
        self._hideNextMonthArrow =
            self.config.maxDate !== undefined &&
                (self.currentYear === self.config.maxDate.getFullYear()
                    ? self.currentMonth + 1 > self.config.maxDate.getMonth()
                    : self.currentYear > self.config.maxDate.getFullYear());
    }
    function getDateStr(specificFormat) {
        var format = specificFormat ||
            (self.config.altInput ? self.config.altFormat : self.config.dateFormat);
        return self.selectedDates
            .map(function (dObj) { return self.formatDate(dObj, format); })
            .filter(function (d, i, arr) {
            return self.config.mode !== "range" ||
                self.config.enableTime ||
                arr.indexOf(d) === i;
        })
            .join(self.config.mode !== "range"
            ? self.config.conjunction
            : self.l10n.rangeSeparator);
    }
    function updateValue(triggerChange) {
        if (triggerChange === void 0) { triggerChange = true; }
        if (self.mobileInput !== undefined && self.mobileFormatStr) {
            self.mobileInput.value =
                self.latestSelectedDateObj !== undefined
                    ? self.formatDate(self.latestSelectedDateObj, self.mobileFormatStr)
                    : "";
        }
        self.input.value = getDateStr(self.config.dateFormat);
        if (self.altInput !== undefined) {
            self.altInput.value = getDateStr(self.config.altFormat);
        }
        if (triggerChange !== false)
            triggerEvent("onValueUpdate");
    }
    function onMonthNavClick(e) {
        var eventTarget = Object(__WEBPACK_IMPORTED_MODULE_3__utils_dom__["e" /* getEventTarget */])(e);
        var isPrevMonth = self.prevMonthNav.contains(eventTarget);
        var isNextMonth = self.nextMonthNav.contains(eventTarget);
        if (isPrevMonth || isNextMonth) {
            changeMonth(isPrevMonth ? -1 : 1);
        }
        else if (self.yearElements.indexOf(eventTarget) >= 0) {
            eventTarget.select();
        }
        else if (eventTarget.classList.contains("arrowUp")) {
            self.changeYear(self.currentYear + 1);
        }
        else if (eventTarget.classList.contains("arrowDown")) {
            self.changeYear(self.currentYear - 1);
        }
    }
    function timeWrapper(e) {
        e.preventDefault();
        var isKeyDown = e.type === "keydown", eventTarget = Object(__WEBPACK_IMPORTED_MODULE_3__utils_dom__["e" /* getEventTarget */])(e), input = eventTarget;
        if (self.amPM !== undefined && eventTarget === self.amPM) {
            self.amPM.textContent =
                self.l10n.amPM[Object(__WEBPACK_IMPORTED_MODULE_2__utils__["c" /* int */])(self.amPM.textContent === self.l10n.amPM[0])];
        }
        var min = parseFloat(input.getAttribute("min")), max = parseFloat(input.getAttribute("max")), step = parseFloat(input.getAttribute("step")), curValue = parseInt(input.value, 10), delta = e.delta ||
            (isKeyDown ? (e.which === 38 ? 1 : -1) : 0);
        var newValue = curValue + step * delta;
        if (typeof input.value !== "undefined" && input.value.length === 2) {
            var isHourElem = input === self.hourElement, isMinuteElem = input === self.minuteElement;
            if (newValue < min) {
                newValue =
                    max +
                        newValue +
                        Object(__WEBPACK_IMPORTED_MODULE_2__utils__["c" /* int */])(!isHourElem) +
                        (Object(__WEBPACK_IMPORTED_MODULE_2__utils__["c" /* int */])(isHourElem) && Object(__WEBPACK_IMPORTED_MODULE_2__utils__["c" /* int */])(!self.amPM));
                if (isMinuteElem)
                    incrementNumInput(undefined, -1, self.hourElement);
            }
            else if (newValue > max) {
                newValue =
                    input === self.hourElement ? newValue - max - Object(__WEBPACK_IMPORTED_MODULE_2__utils__["c" /* int */])(!self.amPM) : min;
                if (isMinuteElem)
                    incrementNumInput(undefined, 1, self.hourElement);
            }
            if (self.amPM &&
                isHourElem &&
                (step === 1
                    ? newValue + curValue === 23
                    : Math.abs(newValue - curValue) > step)) {
                self.amPM.textContent =
                    self.l10n.amPM[Object(__WEBPACK_IMPORTED_MODULE_2__utils__["c" /* int */])(self.amPM.textContent === self.l10n.amPM[0])];
            }
            input.value = Object(__WEBPACK_IMPORTED_MODULE_2__utils__["d" /* pad */])(newValue);
        }
    }
    init();
    return self;
}
function _flatpickr(nodeList, config) {
    var nodes = Array.prototype.slice
        .call(nodeList)
        .filter(function (x) { return x instanceof HTMLElement; });
    var instances = [];
    for (var i = 0; i < nodes.length; i++) {
        var node = nodes[i];
        try {
            if (node.getAttribute("data-fp-omit") !== null)
                continue;
            if (node._flatpickr !== undefined) {
                node._flatpickr.destroy();
                node._flatpickr = undefined;
            }
            node._flatpickr = FlatpickrInstance(node, config || {});
            instances.push(node._flatpickr);
        }
        catch (e) {
            console.error(e);
        }
    }
    return instances.length === 1 ? instances[0] : instances;
}
if (typeof HTMLElement !== "undefined" &&
    typeof HTMLCollection !== "undefined" &&
    typeof NodeList !== "undefined") {
    HTMLCollection.prototype.flatpickr = NodeList.prototype.flatpickr = function (config) {
        return _flatpickr(this, config);
    };
    HTMLElement.prototype.flatpickr = function (config) {
        return _flatpickr([this], config);
    };
}
var flatpickr = function (selector, config) {
    if (typeof selector === "string") {
        return _flatpickr(window.document.querySelectorAll(selector), config);
    }
    else if (selector instanceof Node) {
        return _flatpickr([selector], config);
    }
    else {
        return _flatpickr(selector, config);
    }
};
flatpickr.defaultConfig = {};
flatpickr.l10ns = {
    en: __assign({}, __WEBPACK_IMPORTED_MODULE_1__l10n_default__["a" /* default */]),
    default: __assign({}, __WEBPACK_IMPORTED_MODULE_1__l10n_default__["a" /* default */]),
};
flatpickr.localize = function (l10n) {
    flatpickr.l10ns.default = __assign(__assign({}, flatpickr.l10ns.default), l10n);
};
flatpickr.setDefaults = function (config) {
    flatpickr.defaultConfig = __assign(__assign({}, flatpickr.defaultConfig), config);
};
flatpickr.parseDate = Object(__WEBPACK_IMPORTED_MODULE_4__utils_dates__["d" /* createDateParser */])({});
flatpickr.formatDate = Object(__WEBPACK_IMPORTED_MODULE_4__utils_dates__["c" /* createDateFormatter */])({});
flatpickr.compareDates = __WEBPACK_IMPORTED_MODULE_4__utils_dates__["b" /* compareDates */];
if (typeof jQuery !== "undefined" && typeof jQuery.fn !== "undefined") {
    jQuery.fn.flatpickr = function (config) {
        return _flatpickr(this, config);
    };
}
Date.prototype.fp_incr = function (days) {
    return new Date(this.getFullYear(), this.getMonth(), this.getDate() + (typeof days === "string" ? parseInt(days, 10) : days));
};
if (typeof window !== "undefined") {
    window.flatpickr = flatpickr;
}
/* harmony default export */ __webpack_exports__["a"] = (flatpickr);


/***/ }),
/* 17 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony export (immutable) */ __webpack_exports__["f"] = toggleClass;
/* harmony export (immutable) */ __webpack_exports__["b"] = createElement;
/* harmony export (immutable) */ __webpack_exports__["a"] = clearNode;
/* harmony export (immutable) */ __webpack_exports__["d"] = findParent;
/* harmony export (immutable) */ __webpack_exports__["c"] = createNumberInput;
/* harmony export (immutable) */ __webpack_exports__["e"] = getEventTarget;
function toggleClass(elem, className, bool) {
    if (bool === true)
        return elem.classList.add(className);
    elem.classList.remove(className);
}
function createElement(tag, className, content) {
    var e = window.document.createElement(tag);
    className = className || "";
    content = content || "";
    e.className = className;
    if (content !== undefined)
        e.textContent = content;
    return e;
}
function clearNode(node) {
    while (node.firstChild)
        node.removeChild(node.firstChild);
}
function findParent(node, condition) {
    if (condition(node))
        return node;
    else if (node.parentNode)
        return findParent(node.parentNode, condition);
    return undefined;
}
function createNumberInput(inputClassName, opts) {
    var wrapper = createElement("div", "numInputWrapper"), numInput = createElement("input", "numInput " + inputClassName), arrowUp = createElement("span", "arrowUp"), arrowDown = createElement("span", "arrowDown");
    if (navigator.userAgent.indexOf("MSIE 9.0") === -1) {
        numInput.type = "number";
    }
    else {
        numInput.type = "text";
        numInput.pattern = "\\d*";
    }
    if (opts !== undefined)
        for (var key in opts)
            numInput.setAttribute(key, opts[key]);
    wrapper.appendChild(numInput);
    wrapper.appendChild(arrowUp);
    wrapper.appendChild(arrowDown);
    return wrapper;
}
function getEventTarget(event) {
    try {
        if (typeof event.composedPath === "function") {
            var path = event.composedPath();
            return path[0];
        }
        return event.target;
    }
    catch (error) {
        return event.target;
    }
}


/***/ }),
/* 18 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "c", function() { return createDateFormatter; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "d", function() { return createDateParser; });
/* harmony export (immutable) */ __webpack_exports__["b"] = compareDates;
/* unused harmony export compareTimes */
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "g", function() { return isBetween; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "a", function() { return calculateSecondsSinceMidnight; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "h", function() { return parseSeconds; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "e", function() { return duration; });
/* harmony export (immutable) */ __webpack_exports__["f"] = getDefaultHours;
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__formatting__ = __webpack_require__(5);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__types_options__ = __webpack_require__(2);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__l10n_default__ = __webpack_require__(3);



var createDateFormatter = function (_a) {
    var _b = _a.config, config = _b === void 0 ? __WEBPACK_IMPORTED_MODULE_1__types_options__["b" /* defaults */] : _b, _c = _a.l10n, l10n = _c === void 0 ? __WEBPACK_IMPORTED_MODULE_2__l10n_default__["b" /* english */] : _c, _d = _a.isMobile, isMobile = _d === void 0 ? false : _d;
    return function (dateObj, frmt, overrideLocale) {
        var locale = overrideLocale || l10n;
        if (config.formatDate !== undefined && !isMobile) {
            return config.formatDate(dateObj, frmt, locale);
        }
        return frmt
            .split("")
            .map(function (c, i, arr) {
            return __WEBPACK_IMPORTED_MODULE_0__formatting__["a" /* formats */][c] && arr[i - 1] !== "\\"
                ? __WEBPACK_IMPORTED_MODULE_0__formatting__["a" /* formats */][c](dateObj, locale, config)
                : c !== "\\"
                    ? c
                    : "";
        })
            .join("");
    };
};
var createDateParser = function (_a) {
    var _b = _a.config, config = _b === void 0 ? __WEBPACK_IMPORTED_MODULE_1__types_options__["b" /* defaults */] : _b, _c = _a.l10n, l10n = _c === void 0 ? __WEBPACK_IMPORTED_MODULE_2__l10n_default__["b" /* english */] : _c;
    return function (date, givenFormat, timeless, customLocale) {
        if (date !== 0 && !date)
            return undefined;
        var locale = customLocale || l10n;
        var parsedDate;
        var dateOrig = date;
        if (date instanceof Date)
            parsedDate = new Date(date.getTime());
        else if (typeof date !== "string" &&
            date.toFixed !== undefined)
            parsedDate = new Date(date);
        else if (typeof date === "string") {
            var format = givenFormat || (config || __WEBPACK_IMPORTED_MODULE_1__types_options__["b" /* defaults */]).dateFormat;
            var datestr = String(date).trim();
            if (datestr === "today") {
                parsedDate = new Date();
                timeless = true;
            }
            else if (config && config.parseDate) {
                parsedDate = config.parseDate(date, format);
            }
            else if (/Z$/.test(datestr) ||
                /GMT$/.test(datestr)) {
                parsedDate = new Date(date);
            }
            else {
                var matched = void 0, ops = [];
                for (var i = 0, matchIndex = 0, regexStr = ""; i < format.length; i++) {
                    var token = format[i];
                    var isBackSlash = token === "\\";
                    var escaped = format[i - 1] === "\\" || isBackSlash;
                    if (__WEBPACK_IMPORTED_MODULE_0__formatting__["d" /* tokenRegex */][token] && !escaped) {
                        regexStr += __WEBPACK_IMPORTED_MODULE_0__formatting__["d" /* tokenRegex */][token];
                        var match = new RegExp(regexStr).exec(date);
                        if (match && (matched = true)) {
                            ops[token !== "Y" ? "push" : "unshift"]({
                                fn: __WEBPACK_IMPORTED_MODULE_0__formatting__["c" /* revFormat */][token],
                                val: match[++matchIndex],
                            });
                        }
                    }
                    else if (!isBackSlash)
                        regexStr += ".";
                }
                parsedDate =
                    !config || !config.noCalendar
                        ? new Date(new Date().getFullYear(), 0, 1, 0, 0, 0, 0)
                        : new Date(new Date().setHours(0, 0, 0, 0));
                ops.forEach(function (_a) {
                    var fn = _a.fn, val = _a.val;
                    return (parsedDate = fn(parsedDate, val, locale) || parsedDate);
                });
                parsedDate = matched ? parsedDate : undefined;
            }
        }
        if (!(parsedDate instanceof Date && !isNaN(parsedDate.getTime()))) {
            config.errorHandler(new Error("Invalid date provided: " + dateOrig));
            return undefined;
        }
        if (timeless === true)
            parsedDate.setHours(0, 0, 0, 0);
        return parsedDate;
    };
};
function compareDates(date1, date2, timeless) {
    if (timeless === void 0) { timeless = true; }
    if (timeless !== false) {
        return (new Date(date1.getTime()).setHours(0, 0, 0, 0) -
            new Date(date2.getTime()).setHours(0, 0, 0, 0));
    }
    return date1.getTime() - date2.getTime();
}
function compareTimes(date1, date2) {
    return (3600 * (date1.getHours() - date2.getHours()) +
        60 * (date1.getMinutes() - date2.getMinutes()) +
        date1.getSeconds() -
        date2.getSeconds());
}
var isBetween = function (ts, ts1, ts2) {
    return ts > Math.min(ts1, ts2) && ts < Math.max(ts1, ts2);
};
var calculateSecondsSinceMidnight = function (hours, minutes, seconds) {
    return hours * 3600 + minutes * 60 + seconds;
};
var parseSeconds = function (secondsSinceMidnight) {
    var hours = Math.floor(secondsSinceMidnight / 3600), minutes = (secondsSinceMidnight - hours * 3600) / 60;
    return [hours, minutes, secondsSinceMidnight - hours * 3600 - minutes * 60];
};
var duration = {
    DAY: 86400000,
};
function getDefaultHours(config) {
    var hours = config.defaultHour;
    var minutes = config.defaultMinute;
    var seconds = config.defaultSeconds;
    if (config.minDate !== undefined) {
        var minHour = config.minDate.getHours();
        var minMinutes = config.minDate.getMinutes();
        var minSeconds = config.minDate.getSeconds();
        if (hours < minHour) {
            hours = minHour;
        }
        if (hours === minHour && minutes < minMinutes) {
            minutes = minMinutes;
        }
        if (hours === minHour && minutes === minMinutes && seconds < minSeconds)
            seconds = config.minDate.getSeconds();
    }
    if (config.maxDate !== undefined) {
        var maxHr = config.maxDate.getHours();
        var maxMinutes = config.maxDate.getMinutes();
        hours = Math.min(hours, maxHr);
        if (hours === maxHr)
            minutes = Math.min(maxMinutes, minutes);
        if (hours === maxHr && minutes === maxMinutes)
            seconds = config.maxDate.getSeconds();
    }
    return { hours: hours, minutes: minutes, seconds: seconds };
}


/***/ }),
/* 19 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

if (typeof Object.assign !== "function") {
    Object.assign = function (target) {
        var args = [];
        for (var _i = 1; _i < arguments.length; _i++) {
            args[_i - 1] = arguments[_i];
        }
        if (!target) {
            throw TypeError("Cannot convert undefined or null to object");
        }
        var _loop_1 = function (source) {
            if (source) {
                Object.keys(source).forEach(function (key) { return (target[key] = source[key]); });
            }
        };
        for (var _a = 0, args_1 = args; _a < args_1.length; _a++) {
            var source = args_1[_a];
            _loop_1(source);
        }
        return target;
    };
}


/***/ }),
/* 20 */
/***/ (function(module, exports, __webpack_require__) {

// style-loader: Adds some css to the DOM by adding a <style> tag

// load the styles
var content = __webpack_require__(21);
if(typeof content === 'string') content = [[module.i, content, '']];
// Prepare cssTransformation
var transform;

var options = {}
options.transform = transform
// add the styles to the DOM
var update = __webpack_require__(22)(content, options);
if(content.locals) module.exports = content.locals;
// Hot Module Replacement
if(false) {
	// When the styles change, update the <style> tags
	if(!content.locals) {
		module.hot.accept("!!../../../node_modules/css-loader/index.js!./airbnb-modified.css", function() {
			var newContent = require("!!../../../node_modules/css-loader/index.js!./airbnb-modified.css");
			if(typeof newContent === 'string') newContent = [[module.id, newContent, '']];
			update(newContent);
		});
	}
	// When the module is disposed, remove the <style> tags
	module.hot.dispose(function() { update(); });
}

/***/ }),
/* 21 */
/***/ (function(module, exports, __webpack_require__) {

exports = module.exports = __webpack_require__(1)(false);
// imports


// module
exports.push([module.i, ".date-filter .flatpickr-calendar {\r\n    background: transparent;\r\n    opacity: 0;\r\n    display: none;\r\n    text-align: center;\r\n    visibility: hidden;\r\n    padding: 0;\r\n    -webkit-animation: none;\r\n    animation: none;\r\n    direction: ltr;\r\n    border: 0;\r\n    font-size: 14px;\r\n    line-height: 24px;\r\n    border-radius: 5px;\r\n    position: absolute;\r\n    width: 264px;\r\n    -webkit-box-sizing: border-box;\r\n    box-sizing: border-box;\r\n    -ms-touch-action: manipulation;\r\n    touch-action: manipulation;\r\n    background: #fff;\r\n    -webkit-box-shadow: 1px 0 0 #eee, -1px 0 0 #eee, 0 1px 0 #eee, 0 -1px 0 #eee,\r\n    0 3px 13px rgba(0, 0, 0, 0.08);\r\n    box-shadow: 1px 0 0 #eee, -1px 0 0 #eee, 0 1px 0 #eee, 0 -1px 0 #eee,\r\n    0 3px 13px rgba(0, 0, 0, 0.08);\r\n}\r\n\r\n.date-filter .flatpickr-calendar.open,\r\n.date-filter .flatpickr-calendar.inline {\r\n    opacity: 1;\r\n    max-height: 640px;\r\n    visibility: visible;\r\n}\r\n\r\n.date-filter .flatpickr-calendar.open {\r\n    display: inline-block;\r\n    z-index: 99999;\r\n}\r\n\r\n.date-filter .flatpickr-calendar.animate.open {\r\n    -webkit-animation: fpFadeInDown 300ms cubic-bezier(0.23, 1, 0.32, 1);\r\n    animation: fpFadeInDown 300ms cubic-bezier(0.23, 1, 0.32, 1);\r\n}\r\n\r\n.date-filter .flatpickr-calendar.inline {\r\n    display: block;\r\n    position: relative;\r\n    top: 2px;\r\n}\r\n\r\n.date-filter .flatpickr-calendar.static {\r\n    position: absolute;\r\n    top: calc(100% + 2px);\r\n}\r\n\r\n.date-filter .flatpickr-calendar.static.open {\r\n    z-index: 999;\r\n    display: block;\r\n}\r\n\r\n.date-filter\r\n.flatpickr-calendar.multiMonth\r\n.flatpickr-days\r\n.dayContainer:nth-child(n + 1)\r\n.flatpickr-day.inRange:nth-child(7n + 7) {\r\n    -webkit-box-shadow: none !important;\r\n    box-shadow: none !important;\r\n}\r\n\r\n.date-filter\r\n.flatpickr-calendar.multiMonth\r\n.flatpickr-days\r\n.dayContainer:nth-child(n + 2)\r\n.flatpickr-day.inRange:nth-child(7n + 1) {\r\n    -webkit-box-shadow: -2px 0 0 #e6e6e6, 5px 0 0 #e6e6e6;\r\n    box-shadow: -2px 0 0 #e6e6e6, 5px 0 0 #e6e6e6;\r\n}\r\n\r\n.date-filter .flatpickr-calendar .hasWeeks .dayContainer,\r\n.date-filter .flatpickr-calendar .hasTime .dayContainer {\r\n    border-bottom: 0;\r\n    border-bottom-right-radius: 0;\r\n    border-bottom-left-radius: 0;\r\n}\r\n\r\n.date-filter .flatpickr-calendar .hasWeeks .dayContainer {\r\n    border-left: 0;\r\n}\r\n\r\n.date-filter .flatpickr-calendar.showTimeInput.hasTime .flatpickr-time {\r\n    height: 40px;\r\n    border-top: 1px solid #eee;\r\n}\r\n\r\n.date-filter .flatpickr-calendar.noCalendar.hasTime .flatpickr-time {\r\n    height: auto;\r\n}\r\n\r\n.date-filter .flatpickr-calendar:before,\r\n.date-filter .flatpickr-calendar:after {\r\n    position: absolute;\r\n    display: block;\r\n    pointer-events: none;\r\n    border: solid transparent;\r\n    content: '';\r\n    height: 0;\r\n    width: 0;\r\n    left: 22px;\r\n}\r\n\r\n.date-filter .flatpickr-calendar.rightMost:before,\r\n.date-filter .flatpickr-calendar.rightMost:after {\r\n    left: auto;\r\n    right: 22px;\r\n}\r\n\r\n.date-filter .flatpickr-calendar:before {\r\n    border-width: 5px;\r\n    margin: 0 -5px;\r\n}\r\n\r\n.date-filter .flatpickr-calendar:after {\r\n    border-width: 4px;\r\n    margin: 0 -4px;\r\n}\r\n\r\n.date-filter .flatpickr-calendar.arrowTop:before,\r\n.date-filter .flatpickr-calendar.arrowTop:after {\r\n    bottom: 100%;\r\n}\r\n\r\n.date-filter .flatpickr-calendar.arrowTop:before {\r\n    border-bottom-color: #eee;\r\n}\r\n\r\n.date-filter .flatpickr-calendar.arrowTop:after {\r\n    border-bottom-color: #fff;\r\n}\r\n\r\n.date-filter .flatpickr-calendar.arrowBottom:before,\r\n.date-filter .flatpickr-calendar.arrowBottom:after {\r\n    top: 100%;\r\n}\r\n\r\n.date-filter .flatpickr-calendar.arrowBottom:before {\r\n    border-top-color: #eee;\r\n}\r\n\r\n.date-filter .flatpickr-calendar.arrowBottom:after {\r\n    border-top-color: #fff;\r\n}\r\n\r\n.date-filter .flatpickr-calendar:focus {\r\n    outline: 0;\r\n}\r\n\r\n.flatpickr-wrapper.date-filter {\r\n    display: block;\r\n    width: 100%;\r\n}\r\n\r\n.flatpickr-months {\r\n    display: -webkit-box;\r\n    display: -webkit-flex;\r\n    display: -ms-flexbox;\r\n    display: flex;\r\n}\r\n\r\n.flatpickr-months .flatpickr-month {\r\n    background: transparent;\r\n    color: #3c3f40;\r\n    fill: #3c3f40;\r\n    height: 28px;\r\n    line-height: 1;\r\n    text-align: center;\r\n    position: relative;\r\n    -webkit-user-select: none;\r\n    -moz-user-select: none;\r\n    -ms-user-select: none;\r\n    user-select: none;\r\n    overflow: hidden;\r\n    -webkit-box-flex: 1;\r\n    -webkit-flex: 1;\r\n    -ms-flex: 1;\r\n    flex: 1;\r\n}\r\n\r\n.flatpickr-months .flatpickr-prev-month,\r\n.flatpickr-months .flatpickr-next-month {\r\n    text-decoration: none;\r\n    cursor: pointer;\r\n    position: absolute;\r\n    top: 0px;\r\n    line-height: 16px;\r\n    height: 28px;\r\n    padding: 10px;\r\n    z-index: 3;\r\n    color: #3c3f40;\r\n    fill: #3c3f40;\r\n}\r\n\r\n.flatpickr-months .flatpickr-prev-month.disabled,\r\n.flatpickr-months .flatpickr-next-month.disabled {\r\n    display: none;\r\n}\r\n\r\n.flatpickr-months .flatpickr-prev-month i,\r\n.flatpickr-months .flatpickr-next-month i {\r\n    position: relative;\r\n}\r\n\r\n.flatpickr-months .flatpickr-prev-month.flatpickr-prev-month,\r\n.flatpickr-months .flatpickr-next-month.flatpickr-prev-month {\r\n    /*\r\n            /*rtl:begin:ignore*/\r\n    /*\r\n            */\r\n    left: 0;\r\n    /*\r\n            /*rtl:end:ignore*/\r\n    /*\r\n            */\r\n}\r\n\r\n/*\r\n      /*rtl:begin:ignore*/\r\n/*\r\n      /*rtl:end:ignore*/\r\n.flatpickr-months .flatpickr-prev-month.flatpickr-next-month,\r\n.flatpickr-months .flatpickr-next-month.flatpickr-next-month {\r\n    /*\r\n            /*rtl:begin:ignore*/\r\n    /*\r\n            */\r\n    right: 0;\r\n    /*\r\n            /*rtl:end:ignore*/\r\n    /*\r\n            */\r\n}\r\n\r\n/*\r\n      /*rtl:begin:ignore*/\r\n/*\r\n      /*rtl:end:ignore*/\r\n.flatpickr-months .flatpickr-prev-month:hover,\r\n.flatpickr-months .flatpickr-next-month:hover {\r\n    color: #f64747;\r\n}\r\n\r\n.flatpickr-months .flatpickr-prev-month:hover svg,\r\n.flatpickr-months .flatpickr-next-month:hover svg {\r\n    fill: #f64747;\r\n}\r\n\r\n.flatpickr-months .flatpickr-prev-month svg,\r\n.flatpickr-months .flatpickr-next-month svg {\r\n    width: 14px;\r\n    height: 14px;\r\n}\r\n\r\n.flatpickr-months .flatpickr-prev-month svg path,\r\n.flatpickr-months .flatpickr-next-month svg path {\r\n    -webkit-transition: fill 0.1s;\r\n    transition: fill 0.1s;\r\n    fill: inherit;\r\n}\r\n\r\n.numInputWrapper {\r\n    position: relative;\r\n    height: auto;\r\n}\r\n\r\n.numInputWrapper input,\r\n.numInputWrapper span {\r\n    display: inline-block;\r\n}\r\n\r\n.numInputWrapper input {\r\n    width: 100%;\r\n}\r\n\r\n.numInputWrapper input::-ms-clear {\r\n    display: none;\r\n}\r\n\r\n.numInputWrapper span {\r\n    position: absolute;\r\n    right: 0;\r\n    width: 14px;\r\n    padding: 0 4px 0 2px;\r\n    height: 50%;\r\n    line-height: 50%;\r\n    opacity: 0;\r\n    cursor: pointer;\r\n    border: 1px solid rgba(64, 72, 72, 0.15);\r\n    -webkit-box-sizing: border-box;\r\n    box-sizing: border-box;\r\n}\r\n\r\n.numInputWrapper span:hover {\r\n    background: rgba(0, 0, 0, 0.1);\r\n}\r\n\r\n.numInputWrapper span:active {\r\n    background: rgba(0, 0, 0, 0.2);\r\n}\r\n\r\n.numInputWrapper span:after {\r\n    display: block;\r\n    content: '';\r\n    position: absolute;\r\n}\r\n\r\n.numInputWrapper span.arrowUp {\r\n    top: 0;\r\n    border-bottom: 0;\r\n}\r\n\r\n.numInputWrapper span.arrowUp:after {\r\n    border-left: 4px solid transparent;\r\n    border-right: 4px solid transparent;\r\n    border-bottom: 4px solid rgba(64, 72, 72, 0.6);\r\n    top: 26%;\r\n}\r\n\r\n.numInputWrapper span.arrowDown {\r\n    top: 50%;\r\n}\r\n\r\n.numInputWrapper span.arrowDown:after {\r\n    border-left: 4px solid transparent;\r\n    border-right: 4px solid transparent;\r\n    border-top: 4px solid rgba(64, 72, 72, 0.6);\r\n    top: 40%;\r\n}\r\n\r\n.numInputWrapper span svg {\r\n    width: inherit;\r\n    height: auto;\r\n}\r\n\r\n.numInputWrapper span svg path {\r\n    fill: rgba(60, 63, 64, 0.5);\r\n}\r\n\r\n.numInputWrapper:hover {\r\n    background: rgba(0, 0, 0, 0.05);\r\n}\r\n\r\n.numInputWrapper:hover span {\r\n    opacity: 1;\r\n}\r\n\r\n.flatpickr-current-month {\r\n    font-size: 135%;\r\n    line-height: inherit;\r\n    font-weight: 300;\r\n    color: inherit;\r\n    position: absolute;\r\n    width: 75%;\r\n    left: 12.5%;\r\n    padding: 6.16px 0 0 0;\r\n    line-height: 1;\r\n    height: 28px;\r\n    display: inline-block;\r\n    text-align: center;\r\n    -webkit-transform: translate3d(0px, 0px, 0px);\r\n    transform: translate3d(0px, 0px, 0px);\r\n}\r\n\r\n.flatpickr-current-month span.cur-month {\r\n    font-family: inherit;\r\n    font-weight: 700;\r\n    color: inherit;\r\n    display: inline-block;\r\n    margin-left: 0.5ch;\r\n    padding: 0;\r\n}\r\n\r\n.flatpickr-current-month span.cur-month:hover {\r\n    background: rgba(0, 0, 0, 0.05);\r\n}\r\n\r\n.flatpickr-current-month .numInputWrapper {\r\n    width: 6ch;\r\n    width: 7ch \\0;\r\n    display: inline-block;\r\n}\r\n\r\n.flatpickr-current-month .numInputWrapper span.arrowUp:after {\r\n    border-bottom-color: #3c3f40;\r\n}\r\n\r\n.flatpickr-current-month .numInputWrapper span.arrowDown:after {\r\n    border-top-color: #3c3f40;\r\n}\r\n\r\n.flatpickr-current-month input.cur-year {\r\n    background: transparent;\r\n    -webkit-box-sizing: border-box;\r\n    box-sizing: border-box;\r\n    color: inherit;\r\n    cursor: text;\r\n    padding: 0 0 0 0.5ch;\r\n    margin: 0;\r\n    display: inline-block;\r\n    font-size: inherit;\r\n    font-family: inherit;\r\n    font-weight: 300;\r\n    line-height: inherit;\r\n    height: auto;\r\n    border: 0;\r\n    border-radius: 0;\r\n    vertical-align: initial;\r\n}\r\n\r\n.flatpickr-current-month input.cur-year:focus {\r\n    outline: 0;\r\n}\r\n\r\n.flatpickr-current-month input.cur-year[disabled],\r\n.flatpickr-current-month input.cur-year[disabled]:hover {\r\n    font-size: 100%;\r\n    color: rgba(60, 63, 64, 0.5);\r\n    background: transparent;\r\n    pointer-events: none;\r\n}\r\n\r\n.date-filter .flatpickr-weekdays {\r\n    background: transparent;\r\n    text-align: center;\r\n    overflow: hidden;\r\n    width: 100%;\r\n    display: -webkit-box;\r\n    display: -webkit-flex;\r\n    display: -ms-flexbox;\r\n    display: flex;\r\n    -webkit-box-align: center;\r\n    -webkit-align-items: center;\r\n    -ms-flex-align: center;\r\n    align-items: center;\r\n    height: 28px;\r\n    max-width: 250px;\r\n}\r\n\r\n.flatpickr-weekdays .flatpickr-weekdaycontainer {\r\n    display: -webkit-box;\r\n    display: -webkit-flex;\r\n    display: -ms-flexbox;\r\n    display: flex;\r\n    -webkit-box-flex: 1;\r\n    -webkit-flex: 1;\r\n    -ms-flex: 1;\r\n    flex: 1;\r\n}\r\n\r\nspan.flatpickr-weekday {\r\n    cursor: default;\r\n    font-size: 90%;\r\n    background: transparent;\r\n    color: rgba(0, 0, 0, 0.54);\r\n    line-height: 1;\r\n    margin: 0;\r\n    text-align: center;\r\n    display: block;\r\n    -webkit-box-flex: 1;\r\n    -webkit-flex: 1;\r\n    -ms-flex: 1;\r\n    flex: 1;\r\n    font-weight: bolder;\r\n}\r\n\r\n.dayContainer,\r\n.flatpickr-weeks {\r\n    padding: 1px 0 0 0;\r\n}\r\n\r\n.date-filter .flatpickr-days {\r\n    position: relative;\r\n    overflow: hidden;\r\n    display: -webkit-box;\r\n    display: -webkit-flex;\r\n    display: -ms-flexbox;\r\n    display: flex;\r\n    -webkit-box-align: start;\r\n    -webkit-align-items: flex-start;\r\n    -ms-flex-align: start;\r\n    align-items: flex-start;\r\n    width: 264px;\r\n}\r\n\r\n.flatpickr-days:focus {\r\n    outline: 0;\r\n}\r\n\r\n.date-filter .dayContainer {\r\n    padding: 0;\r\n    outline: 0;\r\n    text-align: left;\r\n    width: 100%;\r\n    min-width: 250px;\r\n    max-width: 250px;\r\n    -webkit-box-sizing: border-box;\r\n    box-sizing: border-box;\r\n    display: inline-block;\r\n    display: -ms-flexbox;\r\n    display: -webkit-box;\r\n    display: -webkit-flex;\r\n    display: flex;\r\n    -webkit-flex-wrap: wrap;\r\n    flex-wrap: wrap;\r\n    -ms-flex-wrap: wrap;\r\n    -ms-flex-pack: justify;\r\n    -webkit-justify-content: space-around;\r\n    justify-content: space-around;\r\n    -webkit-transform: translate3d(0px, 0px, 0px);\r\n    transform: translate3d(0px, 0px, 0px);\r\n    opacity: 1;\r\n}\r\n\r\n.dayContainer + .dayContainer {\r\n    -webkit-box-shadow: -1px 0 0 #eee;\r\n    box-shadow: -1px 0 0 #eee;\r\n}\r\n\r\n.flatpickr-day {\r\n    background: none;\r\n    border: 1px solid transparent;\r\n    border-radius: 150px;\r\n    -webkit-box-sizing: border-box;\r\n    box-sizing: border-box;\r\n    color: #404848;\r\n    cursor: pointer;\r\n    font-weight: 400;\r\n    width: 14.2857143%;\r\n    -webkit-flex-basis: 14.2857143%;\r\n    -ms-flex-preferred-size: 14.2857143%;\r\n    flex-basis: 14.2857143%;\r\n    max-width: 39px;\r\n    height: 39px;\r\n    line-height: 39px;\r\n    margin: 0;\r\n    display: inline-block;\r\n    position: relative;\r\n    -webkit-box-pack: center;\r\n    -webkit-justify-content: center;\r\n    -ms-flex-pack: center;\r\n    justify-content: center;\r\n    text-align: center;\r\n}\r\n\r\n.flatpickr-day.inRange,\r\n.flatpickr-day.prevMonthDay.inRange,\r\n.flatpickr-day.nextMonthDay.inRange,\r\n.flatpickr-day.today.inRange,\r\n.flatpickr-day.prevMonthDay.today.inRange,\r\n.flatpickr-day.nextMonthDay.today.inRange,\r\n.flatpickr-day:hover,\r\n.flatpickr-day.prevMonthDay:hover,\r\n.flatpickr-day.nextMonthDay:hover,\r\n.flatpickr-day:focus,\r\n.flatpickr-day.prevMonthDay:focus,\r\n.flatpickr-day.nextMonthDay:focus {\r\n    cursor: pointer;\r\n    outline: 0;\r\n    background: #e9e9e9;\r\n    border-color: #e9e9e9;\r\n}\r\n\r\n.flatpickr-day.today {\r\n    border-color: #f64747;\r\n}\r\n\r\n.flatpickr-day.today:hover,\r\n.flatpickr-day.today:focus {\r\n    border-color: #f64747;\r\n    background: #f64747;\r\n    color: #fff;\r\n}\r\n\r\n.flatpickr-day.selected,\r\n.flatpickr-day.startRange,\r\n.flatpickr-day.endRange,\r\n.flatpickr-day.selected.inRange,\r\n.flatpickr-day.startRange.inRange,\r\n.flatpickr-day.endRange.inRange,\r\n.flatpickr-day.selected:focus,\r\n.flatpickr-day.startRange:focus,\r\n.flatpickr-day.endRange:focus,\r\n.flatpickr-day.selected:hover,\r\n.flatpickr-day.startRange:hover,\r\n.flatpickr-day.endRange:hover,\r\n.flatpickr-day.selected.prevMonthDay,\r\n.flatpickr-day.startRange.prevMonthDay,\r\n.flatpickr-day.endRange.prevMonthDay,\r\n.flatpickr-day.selected.nextMonthDay,\r\n.flatpickr-day.startRange.nextMonthDay,\r\n.flatpickr-day.endRange.nextMonthDay {\r\n    background: #4f99ff;\r\n    -webkit-box-shadow: none;\r\n    box-shadow: none;\r\n    color: #fff;\r\n    border-color: #4f99ff;\r\n}\r\n\r\n.flatpickr-day.selected.startRange,\r\n.flatpickr-day.startRange.startRange,\r\n.flatpickr-day.endRange.startRange {\r\n    border-radius: 50px 0 0 50px;\r\n}\r\n\r\n.flatpickr-day.selected.endRange,\r\n.flatpickr-day.startRange.endRange,\r\n.flatpickr-day.endRange.endRange {\r\n    border-radius: 0 50px 50px 0;\r\n}\r\n\r\n.flatpickr-day.selected.startRange + .endRange:not(:nth-child(7n + 1)),\r\n.flatpickr-day.startRange.startRange + .endRange:not(:nth-child(7n + 1)),\r\n.flatpickr-day.endRange.startRange + .endRange:not(:nth-child(7n + 1)) {\r\n    -webkit-box-shadow: -10px 0 0 #4f99ff;\r\n    box-shadow: -10px 0 0 #4f99ff;\r\n}\r\n\r\n.flatpickr-day.selected.startRange.endRange,\r\n.flatpickr-day.startRange.startRange.endRange,\r\n.flatpickr-day.endRange.startRange.endRange {\r\n    border-radius: 50px;\r\n}\r\n\r\n.flatpickr-day.inRange {\r\n    border-radius: 0;\r\n    -webkit-box-shadow: -5px 0 0 #e9e9e9, 5px 0 0 #e9e9e9;\r\n    box-shadow: -5px 0 0 #e9e9e9, 5px 0 0 #e9e9e9;\r\n}\r\n\r\n.flatpickr-day.disabled,\r\n.flatpickr-day.disabled:hover,\r\n.flatpickr-day.prevMonthDay,\r\n.flatpickr-day.nextMonthDay,\r\n.flatpickr-day.notAllowed,\r\n.flatpickr-day.notAllowed.prevMonthDay,\r\n.flatpickr-day.notAllowed.nextMonthDay {\r\n    color: rgba(64, 72, 72, 0.3);\r\n    background: transparent;\r\n    border-color: #e9e9e9;\r\n    cursor: default;\r\n}\r\n\r\n.flatpickr-day.disabled,\r\n.flatpickr-day.disabled:hover {\r\n    cursor: not-allowed;\r\n    color: rgba(64, 72, 72, 0.1);\r\n}\r\n\r\n.flatpickr-day.week.selected {\r\n    border-radius: 0;\r\n    -webkit-box-shadow: -5px 0 0 #4f99ff, 5px 0 0 #4f99ff;\r\n    box-shadow: -5px 0 0 #4f99ff, 5px 0 0 #4f99ff;\r\n}\r\n\r\n.flatpickr-day.hidden {\r\n    visibility: hidden;\r\n}\r\n\r\n.rangeMode .flatpickr-day {\r\n    margin-top: 1px;\r\n}\r\n\r\n.flatpickr-weekwrapper {\r\n    display: inline-block;\r\n    float: left;\r\n}\r\n\r\n.flatpickr-weekwrapper .flatpickr-weeks {\r\n    padding: 0 12px;\r\n    -webkit-box-shadow: 1px 0 0 #eee;\r\n    box-shadow: 1px 0 0 #eee;\r\n}\r\n\r\n.flatpickr-weekwrapper .flatpickr-weekday {\r\n    float: none;\r\n    width: 100%;\r\n    line-height: 28px;\r\n}\r\n\r\n.flatpickr-weekwrapper span.flatpickr-day,\r\n.flatpickr-weekwrapper span.flatpickr-day:hover {\r\n    display: block;\r\n    width: 100%;\r\n    max-width: none;\r\n    color: rgba(64, 72, 72, 0.3);\r\n    background: transparent;\r\n    cursor: default;\r\n    border: none;\r\n}\r\n\r\n.flatpickr-innerContainer {\r\n    display: block;\r\n    display: -webkit-box;\r\n    display: -webkit-flex;\r\n    display: -ms-flexbox;\r\n    display: flex;\r\n    -webkit-box-sizing: border-box;\r\n    box-sizing: border-box;\r\n    overflow: hidden;\r\n}\r\n\r\n.flatpickr-rContainer {\r\n    display: inline-block;\r\n    padding: 0;\r\n    -webkit-box-sizing: border-box;\r\n    box-sizing: border-box;\r\n}\r\n\r\n.flatpickr-time {\r\n    text-align: center;\r\n    outline: 0;\r\n    display: block;\r\n    height: 0;\r\n    line-height: 40px;\r\n    max-height: 40px;\r\n    -webkit-box-sizing: border-box;\r\n    box-sizing: border-box;\r\n    overflow: hidden;\r\n    display: -webkit-box;\r\n    display: -webkit-flex;\r\n    display: -ms-flexbox;\r\n    display: flex;\r\n}\r\n\r\n.flatpickr-time:after {\r\n    content: '';\r\n    display: table;\r\n    clear: both;\r\n}\r\n\r\n.flatpickr-time .numInputWrapper {\r\n    -webkit-box-flex: 1;\r\n    -webkit-flex: 1;\r\n    -ms-flex: 1;\r\n    flex: 1;\r\n    width: 40%;\r\n    height: 40px;\r\n    float: left;\r\n}\r\n\r\n.flatpickr-time .numInputWrapper span.arrowUp:after {\r\n    border-bottom-color: #404848;\r\n}\r\n\r\n.flatpickr-time .numInputWrapper span.arrowDown:after {\r\n    border-top-color: #404848;\r\n}\r\n\r\n.flatpickr-time.hasSeconds .numInputWrapper {\r\n    width: 26%;\r\n}\r\n\r\n.flatpickr-time.time24hr .numInputWrapper {\r\n    width: 49%;\r\n}\r\n\r\n.flatpickr-time input {\r\n    background: transparent;\r\n    -webkit-box-shadow: none;\r\n    box-shadow: none;\r\n    border: 0;\r\n    border-radius: 0;\r\n    text-align: center;\r\n    margin: 0;\r\n    padding: 0;\r\n    height: inherit;\r\n    line-height: inherit;\r\n    color: #404848;\r\n    font-size: 14px;\r\n    position: relative;\r\n    -webkit-box-sizing: border-box;\r\n    box-sizing: border-box;\r\n}\r\n\r\n.flatpickr-time input.flatpickr-hour {\r\n    font-weight: bold;\r\n}\r\n\r\n.flatpickr-time input.flatpickr-minute,\r\n.flatpickr-time input.flatpickr-second {\r\n    font-weight: 400;\r\n}\r\n\r\n.flatpickr-time input:focus {\r\n    outline: 0;\r\n    border: 0;\r\n}\r\n\r\n.flatpickr-time .flatpickr-time-separator,\r\n.flatpickr-time .flatpickr-am-pm {\r\n    height: inherit;\r\n    display: inline-block;\r\n    float: left;\r\n    line-height: inherit;\r\n    color: #404848;\r\n    font-weight: bold;\r\n    width: 2%;\r\n    -webkit-user-select: none;\r\n    -moz-user-select: none;\r\n    -ms-user-select: none;\r\n    user-select: none;\r\n    -webkit-align-self: center;\r\n    -ms-flex-item-align: center;\r\n    align-self: center;\r\n}\r\n\r\n.flatpickr-time .flatpickr-am-pm {\r\n    outline: 0;\r\n    width: 18%;\r\n    cursor: pointer;\r\n    text-align: center;\r\n    font-weight: 400;\r\n}\r\n\r\n.flatpickr-time input:hover,\r\n.flatpickr-time .flatpickr-am-pm:hover,\r\n.flatpickr-time input:focus,\r\n.flatpickr-time .flatpickr-am-pm:focus {\r\n    background: #f6f6f6;\r\n}\r\n\r\n.date-filter .flatpickr-input {\r\n    width: 100%;\r\n}\r\n\r\n.flatpickr-input[readonly] {\r\n    cursor: pointer;\r\n}\r\n\r\n@-webkit-keyframes fpFadeInDown {\r\n    from {\r\n        opacity: 0;\r\n        -webkit-transform: translate3d(0, -20px, 0);\r\n        transform: translate3d(0, -20px, 0);\r\n    }\r\n    to {\r\n        opacity: 1;\r\n        -webkit-transform: translate3d(0, 0, 0);\r\n        transform: translate3d(0, 0, 0);\r\n    }\r\n}\r\n\r\n@keyframes fpFadeInDown {\r\n    from {\r\n        opacity: 0;\r\n        -webkit-transform: translate3d(0, -20px, 0);\r\n        transform: translate3d(0, -20px, 0);\r\n    }\r\n    to {\r\n        opacity: 1;\r\n        -webkit-transform: translate3d(0, 0, 0);\r\n        transform: translate3d(0, 0, 0);\r\n    }\r\n}\r\n\r\n.date-filter .flatpickr-calendar {\r\n    width: 100%;\r\n    max-width: 250px;\r\n}\r\n\r\n.dayContainer {\r\n    padding: 0;\r\n    border-right: 0;\r\n}\r\n\r\nspan.flatpickr-day,\r\nspan.flatpickr-day.prevMonthDay,\r\nspan.flatpickr-day.nextMonthDay {\r\n    border-radius: 0 !important;\r\n    border: 1px solid #e9e9e9;\r\n    max-width: none;\r\n    border-right-color: transparent;\r\n}\r\n\r\nspan.flatpickr-day:nth-child(n + 8),\r\nspan.flatpickr-day.prevMonthDay:nth-child(n + 8),\r\nspan.flatpickr-day.nextMonthDay:nth-child(n + 8) {\r\n    border-top-color: transparent;\r\n}\r\n\r\nspan.flatpickr-day:nth-child(7n-6),\r\nspan.flatpickr-day.prevMonthDay:nth-child(7n-6),\r\nspan.flatpickr-day.nextMonthDay:nth-child(7n-6) {\r\n    border-left: 0;\r\n}\r\n\r\nspan.flatpickr-day:nth-child(n + 36),\r\nspan.flatpickr-day.prevMonthDay:nth-child(n + 36),\r\nspan.flatpickr-day.nextMonthDay:nth-child(n + 36) {\r\n    border-bottom: 0;\r\n}\r\n\r\nspan.flatpickr-day:nth-child(-n + 7),\r\nspan.flatpickr-day.prevMonthDay:nth-child(-n + 7),\r\nspan.flatpickr-day.nextMonthDay:nth-child(-n + 7) {\r\n    margin-top: 0;\r\n}\r\n\r\nspan.flatpickr-day.today:not(.selected),\r\nspan.flatpickr-day.prevMonthDay.today:not(.selected),\r\nspan.flatpickr-day.nextMonthDay.today:not(.selected) {\r\n    border-color: #e9e9e9;\r\n    border-right-color: transparent;\r\n    border-top-color: transparent;\r\n    border-bottom-color: #f64747;\r\n}\r\n\r\nspan.flatpickr-day.today:not(.selected):hover,\r\nspan.flatpickr-day.prevMonthDay.today:not(.selected):hover,\r\nspan.flatpickr-day.nextMonthDay.today:not(.selected):hover {\r\n    border: 1px solid #f64747;\r\n}\r\n\r\nspan.flatpickr-day.startRange,\r\nspan.flatpickr-day.prevMonthDay.startRange,\r\nspan.flatpickr-day.nextMonthDay.startRange,\r\nspan.flatpickr-day.endRange,\r\nspan.flatpickr-day.prevMonthDay.endRange,\r\nspan.flatpickr-day.nextMonthDay.endRange {\r\n    border-color: #4f99ff;\r\n}\r\n\r\nspan.flatpickr-day.today,\r\nspan.flatpickr-day.prevMonthDay.today,\r\nspan.flatpickr-day.nextMonthDay.today,\r\nspan.flatpickr-day.selected,\r\nspan.flatpickr-day.prevMonthDay.selected,\r\nspan.flatpickr-day.nextMonthDay.selected {\r\n    z-index: 2;\r\n}\r\n\r\n.rangeMode .flatpickr-day {\r\n    margin-top: -1px;\r\n}\r\n\r\n.flatpickr-weekwrapper .flatpickr-weeks {\r\n    -webkit-box-shadow: none;\r\n    box-shadow: none;\r\n}\r\n\r\n.flatpickr-weekwrapper span.flatpickr-day {\r\n    border: 0;\r\n    margin: -1px 0 0 -1px;\r\n}\r\n\r\n.hasWeeks .flatpickr-days {\r\n    border-right: 0;\r\n}\r\n", ""]);

// exports


/***/ }),
/* 22 */
/***/ (function(module, exports, __webpack_require__) {

/*
	MIT License http://www.opensource.org/licenses/mit-license.php
	Author Tobias Koppers @sokra
*/

var stylesInDom = {};

var	memoize = function (fn) {
	var memo;

	return function () {
		if (typeof memo === "undefined") memo = fn.apply(this, arguments);
		return memo;
	};
};

var isOldIE = memoize(function () {
	// Test for IE <= 9 as proposed by Browserhacks
	// @see http://browserhacks.com/#hack-e71d8692f65334173fee715c222cb805
	// Tests for existence of standard globals is to allow style-loader
	// to operate correctly into non-standard environments
	// @see https://github.com/webpack-contrib/style-loader/issues/177
	return window && document && document.all && !window.atob;
});

var getElement = (function (fn) {
	var memo = {};

	return function(selector) {
		if (typeof memo[selector] === "undefined") {
			memo[selector] = fn.call(this, selector);
		}

		return memo[selector]
	};
})(function (target) {
	return document.querySelector(target)
});

var singleton = null;
var	singletonCounter = 0;
var	stylesInsertedAtTop = [];

var	fixUrls = __webpack_require__(23);

module.exports = function(list, options) {
	if (typeof DEBUG !== "undefined" && DEBUG) {
		if (typeof document !== "object") throw new Error("The style-loader cannot be used in a non-browser environment");
	}

	options = options || {};

	options.attrs = typeof options.attrs === "object" ? options.attrs : {};

	// Force single-tag solution on IE6-9, which has a hard limit on the # of <style>
	// tags it will allow on a page
	if (!options.singleton) options.singleton = isOldIE();

	// By default, add <style> tags to the <head> element
	if (!options.insertInto) options.insertInto = "head";

	// By default, add <style> tags to the bottom of the target
	if (!options.insertAt) options.insertAt = "bottom";

	var styles = listToStyles(list, options);

	addStylesToDom(styles, options);

	return function update (newList) {
		var mayRemove = [];

		for (var i = 0; i < styles.length; i++) {
			var item = styles[i];
			var domStyle = stylesInDom[item.id];

			domStyle.refs--;
			mayRemove.push(domStyle);
		}

		if(newList) {
			var newStyles = listToStyles(newList, options);
			addStylesToDom(newStyles, options);
		}

		for (var i = 0; i < mayRemove.length; i++) {
			var domStyle = mayRemove[i];

			if(domStyle.refs === 0) {
				for (var j = 0; j < domStyle.parts.length; j++) domStyle.parts[j]();

				delete stylesInDom[domStyle.id];
			}
		}
	};
};

function addStylesToDom (styles, options) {
	for (var i = 0; i < styles.length; i++) {
		var item = styles[i];
		var domStyle = stylesInDom[item.id];

		if(domStyle) {
			domStyle.refs++;

			for(var j = 0; j < domStyle.parts.length; j++) {
				domStyle.parts[j](item.parts[j]);
			}

			for(; j < item.parts.length; j++) {
				domStyle.parts.push(addStyle(item.parts[j], options));
			}
		} else {
			var parts = [];

			for(var j = 0; j < item.parts.length; j++) {
				parts.push(addStyle(item.parts[j], options));
			}

			stylesInDom[item.id] = {id: item.id, refs: 1, parts: parts};
		}
	}
}

function listToStyles (list, options) {
	var styles = [];
	var newStyles = {};

	for (var i = 0; i < list.length; i++) {
		var item = list[i];
		var id = options.base ? item[0] + options.base : item[0];
		var css = item[1];
		var media = item[2];
		var sourceMap = item[3];
		var part = {css: css, media: media, sourceMap: sourceMap};

		if(!newStyles[id]) styles.push(newStyles[id] = {id: id, parts: [part]});
		else newStyles[id].parts.push(part);
	}

	return styles;
}

function insertStyleElement (options, style) {
	var target = getElement(options.insertInto)

	if (!target) {
		throw new Error("Couldn't find a style target. This probably means that the value for the 'insertInto' parameter is invalid.");
	}

	var lastStyleElementInsertedAtTop = stylesInsertedAtTop[stylesInsertedAtTop.length - 1];

	if (options.insertAt === "top") {
		if (!lastStyleElementInsertedAtTop) {
			target.insertBefore(style, target.firstChild);
		} else if (lastStyleElementInsertedAtTop.nextSibling) {
			target.insertBefore(style, lastStyleElementInsertedAtTop.nextSibling);
		} else {
			target.appendChild(style);
		}
		stylesInsertedAtTop.push(style);
	} else if (options.insertAt === "bottom") {
		target.appendChild(style);
	} else {
		throw new Error("Invalid value for parameter 'insertAt'. Must be 'top' or 'bottom'.");
	}
}

function removeStyleElement (style) {
	if (style.parentNode === null) return false;
	style.parentNode.removeChild(style);

	var idx = stylesInsertedAtTop.indexOf(style);
	if(idx >= 0) {
		stylesInsertedAtTop.splice(idx, 1);
	}
}

function createStyleElement (options) {
	var style = document.createElement("style");

	options.attrs.type = "text/css";

	addAttrs(style, options.attrs);
	insertStyleElement(options, style);

	return style;
}

function createLinkElement (options) {
	var link = document.createElement("link");

	options.attrs.type = "text/css";
	options.attrs.rel = "stylesheet";

	addAttrs(link, options.attrs);
	insertStyleElement(options, link);

	return link;
}

function addAttrs (el, attrs) {
	Object.keys(attrs).forEach(function (key) {
		el.setAttribute(key, attrs[key]);
	});
}

function addStyle (obj, options) {
	var style, update, remove, result;

	// If a transform function was defined, run it on the css
	if (options.transform && obj.css) {
	    result = options.transform(obj.css);

	    if (result) {
	    	// If transform returns a value, use that instead of the original css.
	    	// This allows running runtime transformations on the css.
	    	obj.css = result;
	    } else {
	    	// If the transform function returns a falsy value, don't add this css.
	    	// This allows conditional loading of css
	    	return function() {
	    		// noop
	    	};
	    }
	}

	if (options.singleton) {
		var styleIndex = singletonCounter++;

		style = singleton || (singleton = createStyleElement(options));

		update = applyToSingletonTag.bind(null, style, styleIndex, false);
		remove = applyToSingletonTag.bind(null, style, styleIndex, true);

	} else if (
		obj.sourceMap &&
		typeof URL === "function" &&
		typeof URL.createObjectURL === "function" &&
		typeof URL.revokeObjectURL === "function" &&
		typeof Blob === "function" &&
		typeof btoa === "function"
	) {
		style = createLinkElement(options);
		update = updateLink.bind(null, style, options);
		remove = function () {
			removeStyleElement(style);

			if(style.href) URL.revokeObjectURL(style.href);
		};
	} else {
		style = createStyleElement(options);
		update = applyToTag.bind(null, style);
		remove = function () {
			removeStyleElement(style);
		};
	}

	update(obj);

	return function updateStyle (newObj) {
		if (newObj) {
			if (
				newObj.css === obj.css &&
				newObj.media === obj.media &&
				newObj.sourceMap === obj.sourceMap
			) {
				return;
			}

			update(obj = newObj);
		} else {
			remove();
		}
	};
}

var replaceText = (function () {
	var textStore = [];

	return function (index, replacement) {
		textStore[index] = replacement;

		return textStore.filter(Boolean).join('\n');
	};
})();

function applyToSingletonTag (style, index, remove, obj) {
	var css = remove ? "" : obj.css;

	if (style.styleSheet) {
		style.styleSheet.cssText = replaceText(index, css);
	} else {
		var cssNode = document.createTextNode(css);
		var childNodes = style.childNodes;

		if (childNodes[index]) style.removeChild(childNodes[index]);

		if (childNodes.length) {
			style.insertBefore(cssNode, childNodes[index]);
		} else {
			style.appendChild(cssNode);
		}
	}
}

function applyToTag (style, obj) {
	var css = obj.css;
	var media = obj.media;

	if(media) {
		style.setAttribute("media", media)
	}

	if(style.styleSheet) {
		style.styleSheet.cssText = css;
	} else {
		while(style.firstChild) {
			style.removeChild(style.firstChild);
		}

		style.appendChild(document.createTextNode(css));
	}
}

function updateLink (link, options, obj) {
	var css = obj.css;
	var sourceMap = obj.sourceMap;

	/*
		If convertToAbsoluteUrls isn't defined, but sourcemaps are enabled
		and there is no publicPath defined then lets turn convertToAbsoluteUrls
		on by default.  Otherwise default to the convertToAbsoluteUrls option
		directly
	*/
	var autoFixUrls = options.convertToAbsoluteUrls === undefined && sourceMap;

	if (options.convertToAbsoluteUrls || autoFixUrls) {
		css = fixUrls(css);
	}

	if (sourceMap) {
		// http://stackoverflow.com/a/26603875
		css += "\n/*# sourceMappingURL=data:application/json;base64," + btoa(unescape(encodeURIComponent(JSON.stringify(sourceMap)))) + " */";
	}

	var blob = new Blob([css], { type: "text/css" });

	var oldSrc = link.href;

	link.href = URL.createObjectURL(blob);

	if(oldSrc) URL.revokeObjectURL(oldSrc);
}


/***/ }),
/* 23 */
/***/ (function(module, exports) {


/**
 * When source maps are enabled, `style-loader` uses a link element with a data-uri to
 * embed the css on the page. This breaks all relative urls because now they are relative to a
 * bundle instead of the current page.
 *
 * One solution is to only use full urls, but that may be impossible.
 *
 * Instead, this function "fixes" the relative urls to be absolute according to the current page location.
 *
 * A rudimentary test suite is located at `test/fixUrls.js` and can be run via the `npm test` command.
 *
 */

module.exports = function (css) {
  // get current location
  var location = typeof window !== "undefined" && window.location;

  if (!location) {
    throw new Error("fixUrls requires window.location");
  }

	// blank or null?
	if (!css || typeof css !== "string") {
	  return css;
  }

  var baseUrl = location.protocol + "//" + location.host;
  var currentDir = baseUrl + location.pathname.replace(/\/[^\/]*$/, "/");

	// convert each url(...)
	/*
	This regular expression is just a way to recursively match brackets within
	a string.

	 /url\s*\(  = Match on the word "url" with any whitespace after it and then a parens
	   (  = Start a capturing group
	     (?:  = Start a non-capturing group
	         [^)(]  = Match anything that isn't a parentheses
	         |  = OR
	         \(  = Match a start parentheses
	             (?:  = Start another non-capturing groups
	                 [^)(]+  = Match anything that isn't a parentheses
	                 |  = OR
	                 \(  = Match a start parentheses
	                     [^)(]*  = Match anything that isn't a parentheses
	                 \)  = Match a end parentheses
	             )  = End Group
              *\) = Match anything and then a close parens
          )  = Close non-capturing group
          *  = Match anything
       )  = Close capturing group
	 \)  = Match a close parens

	 /gi  = Get all matches, not the first.  Be case insensitive.
	 */
	var fixedCss = css.replace(/url\s*\(((?:[^)(]|\((?:[^)(]+|\([^)(]*\))*\))*)\)/gi, function(fullMatch, origUrl) {
		// strip quotes (if they exist)
		var unquotedOrigUrl = origUrl
			.trim()
			.replace(/^"(.*)"$/, function(o, $1){ return $1; })
			.replace(/^'(.*)'$/, function(o, $1){ return $1; });

		// already a full url? no change
		if (/^(#|data:|http:\/\/|https:\/\/|file:\/\/\/)/i.test(unquotedOrigUrl)) {
		  return fullMatch;
		}

		// convert the url to a full url
		var newUrl;

		if (unquotedOrigUrl.indexOf("//") === 0) {
		  	//TODO: should we add protocol?
			newUrl = unquotedOrigUrl;
		} else if (unquotedOrigUrl.indexOf("/") === 0) {
			// path should be relative to the base url
			newUrl = baseUrl + unquotedOrigUrl; // already starts with '/'
		} else {
			// path should be relative to current directory
			newUrl = currentDir + unquotedOrigUrl.replace(/^\.\//, ""); // Strip leading './'
		}

		// send back the fixed url(...)
		return "url(" + JSON.stringify(newUrl) + ")";
	});

	// send back the fixed css
	return fixedCss;
};


/***/ }),
/* 24 */
/***/ (function(module, exports, __webpack_require__) {

var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c("div", [
    _c("div", { staticClass: "filter_field" }, [
      _c("input", {
        ref: "datePicker",
        staticClass:
          "block w-full form-control-sm form-input-bordered h-9 px-2 py-0 form-select text-gray-700",
        class: { "!cursor-not-allowed": _vm.disabled },
        attrs: {
          disabled: _vm.disabled,
          type: "text",
          placeholder: _vm.__("Date From / To")
        },
        domProps: { value: _vm.value }
      })
    ])
  ])
}
var staticRenderFns = []
render._withStripped = true
module.exports = { render: render, staticRenderFns: staticRenderFns }
if (false) {
  module.hot.accept()
  if (module.hot.data) {
    require("vue-hot-reload-api")      .rerender("data-v-129c8b54", module.exports)
  }
}

/***/ }),
/* 25 */
/***/ (function(module, exports, __webpack_require__) {

var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c(
    "div",
    [
      _vm.filters.length > 0
        ? _c(
            "div",
            {
              staticClass: "border border-gray-500 rounded flex flex-wrap mb-5"
            },
            [
              _c("div", { staticClass: "w-full" }, [
                _c(
                  "div",
                  { staticClass: "search_transactions" },
                  [
                    _vm._l(this.filters, function(filter, index) {
                      return _c(
                        "div",
                        { key: index, staticClass: "filter_item" },
                        [
                          filter.component == "date-range-filter"
                            ? _c(filter.component, {
                                key: filter.name,
                                tag: "date-range-filter",
                                attrs: {
                                  "filter-key": filter.class,
                                  "resource-name": _vm.resourceName
                                },
                                on: {
                                  change: function($event) {
                                    return _vm.$emit("filter-changed")
                                  },
                                  input: function($event) {
                                    return _vm.$emit("filter-changed")
                                  }
                                }
                              })
                            : _vm._e()
                        ],
                        1
                      )
                    }),
                    _vm._v(" "),
                    _c("div", { staticClass: "filter_item" }, [
                      _c(
                        "button",
                        {
                          directives: [
                            {
                              name: "tooltip",
                              rawName: "v-tooltip",
                              value: {
                                targetClasses: ["it-has-a-tooltip"],
                                placement: "top",
                                content: _vm.__("Reset Filters"),
                                classes: ["tooltip_reset"]
                              },
                              expression:
                                "{\n                  targetClasses: ['it-has-a-tooltip'],\n                  placement: 'top',\n                  content: __('Reset Filters'),\n                  classes: ['tooltip_reset']\n                }"
                            }
                          ],
                          staticClass:
                            "btn bg-gray-600 text-white h-9 px-3 hover:bg-gray-500",
                          on: {
                            click: function($event) {
                              return _vm.resetFilters()
                            }
                          }
                        },
                        [
                          _c(
                            "svg",
                            {
                              attrs: {
                                xmlns: "http://www.w3.org/2000/svg",
                                width: "16.866",
                                height: "18.447",
                                viewBox: "0 0 16.866 18.447"
                              }
                            },
                            [
                              _c(
                                "g",
                                { attrs: { transform: "translate(0 0)" } },
                                [
                                  _c("path", {
                                    attrs: {
                                      d:
                                        "M24.417,3.658a7.354,7.354,0,0,1,9.56-.252l-2.189.083a.509.509,0,0,0,.019,1.017h.019l3.36-.124a.508.508,0,0,0,.49-.509v-.06h0L35.552.49a.509.509,0,1,0-1.017.038l.079,2.083A8.364,8.364,0,0,0,23.735,2.9a8.367,8.367,0,0,0-2.516,8.178.506.506,0,0,0,.493.388.441.441,0,0,0,.121-.015.509.509,0,0,0,.373-.614A7.349,7.349,0,0,1,24.417,3.658Z",
                                      transform: "translate(-20.982 0)",
                                      fill: "#ffffff"
                                    }
                                  }),
                                  _c("path", {
                                    attrs: {
                                      d:
                                        "M91.8,185.6a.508.508,0,1,0-.987.241,7.348,7.348,0,0,1-11.832,7.387l2.215-.2a.509.509,0,1,0-.094-1.013l-3.349.3a.508.508,0,0,0-.46.554l.3,3.349a.508.508,0,0,0,.5.463.183.183,0,0,0,.045,0,.508.508,0,0,0,.46-.554l-.181-2.038a8.308,8.308,0,0,0,4.833,1.842c.143.008.286.011.426.011A8.365,8.365,0,0,0,91.8,185.6Z",
                                      transform: "translate(-75.175 -178.237)",
                                      fill: "#ffffff"
                                    }
                                  })
                                ]
                              )
                            ]
                          )
                        ]
                      )
                    ])
                  ],
                  2
                )
              ])
            ]
          )
        : _vm._e(),
      _vm._v(" "),
      _c("total-occupied", { ref: "totalOccupied" })
    ],
    1
  )
}
var staticRenderFns = []
render._withStripped = true
module.exports = { render: render, staticRenderFns: staticRenderFns }
if (false) {
  module.hot.accept()
  if (module.hot.data) {
    require("vue-hot-reload-api")      .rerender("data-v-b9bc2c0a", module.exports)
  }
}

/***/ }),
/* 26 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ })
/******/ ]);