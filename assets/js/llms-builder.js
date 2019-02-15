(function($){
/**
 * @license almond 0.3.3 Copyright jQuery Foundation and other contributors.
 * Released under MIT license, http://github.com/requirejs/almond/LICENSE
 */
//Going sloppy to avoid 'use strict' string cost, but strict practices should
//be followed.
/*global setTimeout: false */

var requirejs, require, define;
(function (undef) {
	var main, req, makeMap, handlers,
		defined = {},
		waiting = {},
		config = {},
		defining = {},
		hasOwn = Object.prototype.hasOwnProperty,
		aps = [].slice,
		jsSuffixRegExp = /\.js$/;

	function hasProp(obj, prop) {
		return hasOwn.call(obj, prop);
	}

	/**
	 * Given a relative module name, like ./something, normalize it to
	 * a real name that can be mapped to a path.
	 * @param {String} name the relative name
	 * @param {String} baseName a real name that the name arg is relative
	 * to.
	 * @returns {String} normalized name
	 */
	function normalize(name, baseName) {
		var nameParts, nameSegment, mapValue, foundMap, lastIndex,
			foundI, foundStarMap, starI, i, j, part, normalizedBaseParts,
			baseParts = baseName && baseName.split("/"),
			map = config.map,
			starMap = (map && map['*']) || {};

		//Adjust any relative paths.
		if (name) {
			name = name.split('/');
			lastIndex = name.length - 1;

			// If wanting node ID compatibility, strip .js from end
			// of IDs. Have to do this here, and not in nameToUrl
			// because node allows either .js or non .js to map
			// to same file.
			if (config.nodeIdCompat && jsSuffixRegExp.test(name[lastIndex])) {
				name[lastIndex] = name[lastIndex].replace(jsSuffixRegExp, '');
			}

			// Starts with a '.' so need the baseName
			if (name[0].charAt(0) === '.' && baseParts) {
				//Convert baseName to array, and lop off the last part,
				//so that . matches that 'directory' and not name of the baseName's
				//module. For instance, baseName of 'one/two/three', maps to
				//'one/two/three.js', but we want the directory, 'one/two' for
				//this normalization.
				normalizedBaseParts = baseParts.slice(0, baseParts.length - 1);
				name = normalizedBaseParts.concat(name);
			}

			//start trimDots
			for (i = 0; i < name.length; i++) {
				part = name[i];
				if (part === '.') {
					name.splice(i, 1);
					i -= 1;
				} else if (part === '..') {
					// If at the start, or previous value is still ..,
					// keep them so that when converted to a path it may
					// still work when converted to a path, even though
					// as an ID it is less than ideal. In larger point
					// releases, may be better to just kick out an error.
					if (i === 0 || (i === 1 && name[2] === '..') || name[i - 1] === '..') {
						continue;
					} else if (i > 0) {
						name.splice(i - 1, 2);
						i -= 2;
					}
				}
			}
			//end trimDots

			name = name.join('/');
		}

		//Apply map config if available.
		if ((baseParts || starMap) && map) {
			nameParts = name.split('/');

			for (i = nameParts.length; i > 0; i -= 1) {
				nameSegment = nameParts.slice(0, i).join("/");

				if (baseParts) {
					//Find the longest baseName segment match in the config.
					//So, do joins on the biggest to smallest lengths of baseParts.
					for (j = baseParts.length; j > 0; j -= 1) {
						mapValue = map[baseParts.slice(0, j).join('/')];

						//baseName segment has  config, find if it has one for
						//this name.
						if (mapValue) {
							mapValue = mapValue[nameSegment];
							if (mapValue) {
								//Match, update name to the new value.
								foundMap = mapValue;
								foundI = i;
								break;
							}
						}
					}
				}

				if (foundMap) {
					break;
				}

				//Check for a star map match, but just hold on to it,
				//if there is a shorter segment match later in a matching
				//config, then favor over this star map.
				if (!foundStarMap && starMap && starMap[nameSegment]) {
					foundStarMap = starMap[nameSegment];
					starI = i;
				}
			}

			if (!foundMap && foundStarMap) {
				foundMap = foundStarMap;
				foundI = starI;
			}

			if (foundMap) {
				nameParts.splice(0, foundI, foundMap);
				name = nameParts.join('/');
			}
		}

		return name;
	}

	function makeRequire(relName, forceSync) {
		return function () {
			//A version of a require function that passes a moduleName
			//value for items that may need to
			//look up paths relative to the moduleName
			var args = aps.call(arguments, 0);

			//If first arg is not require('string'), and there is only
			//one arg, it is the array form without a callback. Insert
			//a null so that the following concat is correct.
			if (typeof args[0] !== 'string' && args.length === 1) {
				args.push(null);
			}
			return req.apply(undef, args.concat([relName, forceSync]));
		};
	}

	function makeNormalize(relName) {
		return function (name) {
			return normalize(name, relName);
		};
	}

	function makeLoad(depName) {
		return function (value) {
			defined[depName] = value;
		};
	}

	function callDep(name) {
		if (hasProp(waiting, name)) {
			var args = waiting[name];
			delete waiting[name];
			defining[name] = true;
			main.apply(undef, args);
		}

		if (!hasProp(defined, name) && !hasProp(defining, name)) {
			throw new Error('No ' + name);
		}
		return defined[name];
	}

	//Turns a plugin!resource to [plugin, resource]
	//with the plugin being undefined if the name
	//did not have a plugin prefix.
	function splitPrefix(name) {
		var prefix,
			index = name ? name.indexOf('!') : -1;
		if (index > -1) {
			prefix = name.substring(0, index);
			name = name.substring(index + 1, name.length);
		}
		return [prefix, name];
	}

	//Creates a parts array for a relName where first part is plugin ID,
	//second part is resource ID. Assumes relName has already been normalized.
	function makeRelParts(relName) {
		return relName ? splitPrefix(relName) : [];
	}

	/**
	 * Makes a name map, normalizing the name, and using a plugin
	 * for normalization if necessary. Grabs a ref to plugin
	 * too, as an optimization.
	 */
	makeMap = function (name, relParts) {
		var plugin,
			parts = splitPrefix(name),
			prefix = parts[0],
			relResourceName = relParts[1];

		name = parts[1];

		if (prefix) {
			prefix = normalize(prefix, relResourceName);
			plugin = callDep(prefix);
		}

		//Normalize according
		if (prefix) {
			if (plugin && plugin.normalize) {
				name = plugin.normalize(name, makeNormalize(relResourceName));
			} else {
				name = normalize(name, relResourceName);
			}
		} else {
			name = normalize(name, relResourceName);
			parts = splitPrefix(name);
			prefix = parts[0];
			name = parts[1];
			if (prefix) {
				plugin = callDep(prefix);
			}
		}

		//Using ridiculous property names for space reasons
		return {
			f: prefix ? prefix + '!' + name : name, //fullName
			n: name,
			pr: prefix,
			p: plugin
		};
	};

	function makeConfig(name) {
		return function () {
			return (config && config.config && config.config[name]) || {};
		};
	}

	handlers = {
		require: function (name) {
			return makeRequire(name);
		},
		exports: function (name) {
			var e = defined[name];
			if (typeof e !== 'undefined') {
				return e;
			} else {
				return (defined[name] = {});
			}
		},
		module: function (name) {
			return {
				id: name,
				uri: '',
				exports: defined[name],
				config: makeConfig(name)
			};
		}
	};

	main = function (name, deps, callback, relName) {
		var cjsModule, depName, ret, map, i, relParts,
			args = [],
			callbackType = typeof callback,
			usingExports;

		//Use name if no relName
		relName = relName || name;
		relParts = makeRelParts(relName);

		//Call the callback to define the module, if necessary.
		if (callbackType === 'undefined' || callbackType === 'function') {
			//Pull out the defined dependencies and pass the ordered
			//values to the callback.
			//Default to [require, exports, module] if no deps
			deps = !deps.length && callback.length ? ['require', 'exports', 'module'] : deps;
			for (i = 0; i < deps.length; i += 1) {
				map = makeMap(deps[i], relParts);
				depName = map.f;

				//Fast path CommonJS standard dependencies.
				if (depName === "require") {
					args[i] = handlers.require(name);
				} else if (depName === "exports") {
					//CommonJS module spec 1.1
					args[i] = handlers.exports(name);
					usingExports = true;
				} else if (depName === "module") {
					//CommonJS module spec 1.1
					cjsModule = args[i] = handlers.module(name);
				} else if (hasProp(defined, depName) ||
						   hasProp(waiting, depName) ||
						   hasProp(defining, depName)) {
					args[i] = callDep(depName);
				} else if (map.p) {
					map.p.load(map.n, makeRequire(relName, true), makeLoad(depName), {});
					args[i] = defined[depName];
				} else {
					throw new Error(name + ' missing ' + depName);
				}
			}

			ret = callback ? callback.apply(defined[name], args) : undefined;

			if (name) {
				//If setting exports via "module" is in play,
				//favor that over return value and exports. After that,
				//favor a non-undefined return value over exports use.
				if (cjsModule && cjsModule.exports !== undef &&
						cjsModule.exports !== defined[name]) {
					defined[name] = cjsModule.exports;
				} else if (ret !== undef || !usingExports) {
					//Use the return value from the function.
					defined[name] = ret;
				}
			}
		} else if (name) {
			//May just be an object definition for the module. Only
			//worry about defining if have a module name.
			defined[name] = callback;
		}
	};

	requirejs = require = req = function (deps, callback, relName, forceSync, alt) {
		if (typeof deps === "string") {
			if (handlers[deps]) {
				//callback in this case is really relName
				return handlers[deps](callback);
			}
			//Just return the module wanted. In this scenario, the
			//deps arg is the module name, and second arg (if passed)
			//is just the relName.
			//Normalize module name, if it contains . or ..
			return callDep(makeMap(deps, makeRelParts(callback)).f);
		} else if (!deps.splice) {
			//deps is a config object, not an array.
			config = deps;
			if (config.deps) {
				req(config.deps, config.callback);
			}
			if (!callback) {
				return;
			}

			if (callback.splice) {
				//callback is an array, which means it is a dependency list.
				//Adjust args if there are dependencies
				deps = callback;
				callback = relName;
				relName = null;
			} else {
				deps = undef;
			}
		}

		//Support require(['a'])
		callback = callback || function () {};

		//If relName is a function, it is an errback handler,
		//so remove it.
		if (typeof relName === 'function') {
			relName = forceSync;
			forceSync = alt;
		}

		//Simulate async callback;
		if (forceSync) {
			main(undef, deps, callback, relName);
		} else {
			//Using a non-zero value because of concern for what old browsers
			//do, and latest browsers "upgrade" to 4 if lower value is used:
			//http://www.whatwg.org/specs/web-apps/current-work/multipage/timers.html#dom-windowtimers-settimeout:
			//If want a value immediately, use require('id') instead -- something
			//that works in almond on the global level, but not guaranteed and
			//unlikely to work in other AMD implementations.
			setTimeout(function () {
				main(undef, deps, callback, relName);
			}, 4);
		}

		return req;
	};

	/**
	 * Just drops the config on the floor, but returns req in case
	 * the config return value is used.
	 */
	req.config = function (cfg) {
		return req(cfg);
	};

	/**
	 * Expose module registry for debugging and tooling
	 */
	requirejs._defined = defined;

	define = function (name, deps, callback) {
		if (typeof name !== 'string') {
			throw new Error('See almond README: incorrect module build, no module name');
		}

		//This module may not have dependencies
		if (!deps.splice) {
			//deps is not an array, so probably means
			//an object literal or factory function for
			//the value. Adjust args.
			callback = deps;
			deps = [];
		}

		if (!hasProp(defined, name) && !hasProp(waiting, name)) {
			waiting[name] = [name, deps, callback];
		}
	};

	define.amd = {
		jQuery: true
	};
}());

define("vendor/almond", function(){});

/**
 * This is a slightly modified and forward compatible version of the @wordpress/hooks package
 * as included in the Gutenberg feature plugin version 3.8.0
 */
window.llms=window.llms||{};
// use the core hooks if available
if ( 'undefined' !== typeof window.wp && 'undefined' !== typeof window.wp.hooks ) {
	window.llms.hooks = window.wp.hooks;
// otherwise load our own
} else {
	window.llms.hooks=function(n){var r={};function e(t){if(r[t])return r[t].exports;var o=r[t]={i:t,l:!1,exports:{}};return n[t].call(o.exports,o,o.exports,e),o.l=!0,o.exports}return e.m=n,e.c=r,e.d=function(n,r,t){e.o(n,r)||Object.defineProperty(n,r,{configurable:!1,enumerable:!0,get:t})},e.r=function(n){Object.defineProperty(n,"__esModule",{value:!0})},e.n=function(n){var r=n&&n.__esModule?function(){return n.default}:function(){return n};return e.d(r,"a",r),r},e.o=function(n,r){return Object.prototype.hasOwnProperty.call(n,r)},e.p="",e(e.s=209)}({209:function(n,r,e){"use strict";e.r(r);var t=function(n){return"string"!=typeof n||""===n?(console.error("The namespace must be a non-empty string."),!1):!!/^[a-zA-Z][a-zA-Z0-9_.\-\/]*$/.test(n)||(console.error("The namespace can only contain numbers, letters, dashes, periods, underscores and slashes."),!1)};var o=function(n){return"string"!=typeof n||""===n?(console.error("The hook name must be a non-empty string."),!1):/^__/.test(n)?(console.error("The hook name cannot begin with `__`."),!1):!!/^[a-zA-Z][a-zA-Z0-9_.-]*$/.test(n)||(console.error("The hook name can only contain numbers, letters, dashes, periods and underscores."),!1)};var i=function(n){return function(r,e,i){var u=arguments.length>3&&void 0!==arguments[3]?arguments[3]:10;if(o(r)&&t(e))if("function"==typeof i)if("number"==typeof u){var c={callback:i,priority:u,namespace:e};if(n[r]){for(var a=n[r].handlers,l=0;l<a.length&&!(a[l].priority>u);)l++;a.splice(l,0,c),(n.__current||[]).forEach(function(n){n.name===r&&n.currentIndex>=l&&n.currentIndex++})}else n[r]={handlers:[c],runs:0};"hookAdded"!==r&&b("hookAdded",r,e,i,u)}else console.error("If specified, the hook priority must be a number.");else console.error("The hook callback must be a function.")}};var u=function(n,r){return function(e,i){if(o(e)&&(r||t(i))){if(!n[e])return 0;var u=0;if(r)u=n[e].handlers.length,n[e]={runs:n[e].runs,handlers:[]};else for(var c=n[e].handlers,a=function(r){c[r].namespace===i&&(c.splice(r,1),u++,(n.__current||[]).forEach(function(n){n.name===e&&n.currentIndex>=r&&n.currentIndex--}))},l=c.length-1;l>=0;l--)a(l);return"hookRemoved"!==e&&b("hookRemoved",e,i),u}}};var c=function(n){return function(r){return r in n}};var a=function(n,r){return function(e){n[e]||(n[e]={handlers:[],runs:0}),n[e].runs++;for(var t=n[e].handlers,o=arguments.length,i=new Array(o>1?o-1:0),u=1;u<o;u++)i[u-1]=arguments[u];if(!t||!t.length)return r?i[0]:void 0;var c={name:e,currentIndex:0};for(n.__current.push(c),n[e]||(n[e]={runs:0,handlers:[]});c.currentIndex<t.length;){var a=t[c.currentIndex].callback.apply(null,i);r&&(i[0]=a),c.currentIndex++}return n.__current.pop(),r?i[0]:void 0}};var l=function(n){return function(){return n.__current&&n.__current.length?n.__current[n.__current.length-1].name:null}};var s=function(n){return function(r){return void 0===r?void 0!==n.__current[0]:!!n.__current[0]&&r===n.__current[0].name}};var d=function(n){return function(r){if(o(r))return n[r]&&n[r].runs?n[r].runs:0}};var f=function(){var n=Object.create(null),r=Object.create(null);return n.__current=[],r.__current=[],{addAction:i(n),addFilter:i(r),removeAction:u(n),removeFilter:u(r),hasAction:c(n),hasFilter:c(r),removeAllActions:u(n,!0),removeAllFilters:u(r,!0),doAction:a(n),applyFilters:a(r,!0),currentAction:l(n),currentFilter:l(r),doingAction:s(n),doingFilter:s(r),didAction:d(n),didFilter:d(r),actions:n,filters:r}};e.d(r,"addAction",function(){return p}),e.d(r,"addFilter",function(){return v}),e.d(r,"removeAction",function(){return m}),e.d(r,"removeFilter",function(){return A}),e.d(r,"hasAction",function(){return _}),e.d(r,"hasFilter",function(){return F}),e.d(r,"removeAllActions",function(){return g}),e.d(r,"removeAllFilters",function(){return y}),e.d(r,"doAction",function(){return b}),e.d(r,"applyFilters",function(){return k}),e.d(r,"currentAction",function(){return x}),e.d(r,"currentFilter",function(){return I}),e.d(r,"doingAction",function(){return w}),e.d(r,"doingFilter",function(){return O}),e.d(r,"didAction",function(){return T}),e.d(r,"didFilter",function(){return j}),e.d(r,"actions",function(){return z}),e.d(r,"filters",function(){return Z}),e.d(r,"createHooks",function(){return f});var h=f(),p=h.addAction,v=h.addFilter,m=h.removeAction,A=h.removeFilter,_=h.hasAction,F=h.hasFilter,g=h.removeAllActions,y=h.removeAllFilters,b=h.doAction,k=h.applyFilters,x=h.currentAction,I=h.currentFilter,w=h.doingAction,O=h.doingFilter,T=h.didAction,j=h.didFilter,z=h.actions,Z=h.filters}});
}
;
define("vendor/wp-hooks", function(){});

/**
 * Returns the WordPress-loaded version of Underscore for use with things that need it and use Require.
 * @return   obj
 * @since    3.16.0
 * @version  3.16.0
 */
define( 'underscore',[],function() {
	return _;
} );

/**
 * Returns the WordPress-loaded version of Backbone for use with things that need it and use Require.
 * @return   obj
 * @since    3.16.0
 * @version  3.16.0
 */
define( 'backbone',[],function() {
	return Backbone;
} );

/**
 * Returns the WordPress-loaded version of Underscore for use with things that need it and use Require.
 * @return   obj
 * @since    3.16.0
 * @version  3.16.0
 */
define( 'jquery',[],function() {
	return jQuery;
} );

/*!
* Backbone.CollectionView, v1.3.4
* Copyright (c)2013 Rotunda Software, LLC.
* Distributed under MIT license
* http://github.com/rotundasoftware/backbone-collection-view
*/

( function( root, factory ) {
	// UMD wrapper
	if ( typeof define === 'function' && define.amd ) {
		// AMD
		define( 'vendor/backbone.collectionView',[ 'underscore', 'backbone', 'jquery' ], factory );
	} else if ( typeof exports !== 'undefined' ) {
		// Node/CommonJS
		module.exports = factory( require('underscore' ), require( 'backbone' ), require( 'backbone' ).$ );
	} else {
		// Browser globals
		factory( root._, root.Backbone, ( root.jQuery || root.Zepto || root.$ ) );
	}
}( this, function( _, Backbone, $ ) {
	var mDefaultModelViewConstructor = Backbone.View;

	var kDefaultReferenceBy = "model";

	var kOptionsRequiringRerendering = [ "collection", "modelView", "modelViewOptions", "itemTemplate", "itemTemplateFunction", "detachedRendering" ];

	var kStylesForEmptyListCaption = {
		"background" : "transparent",
		"border" : "none",
		"box-shadow" : "none"
	};

	Backbone.CollectionView = Backbone.View.extend( {

		tagName : "ul",

		events : {
			"mousedown > li, tbody > tr > td" : "_listItem_onMousedown",
			"dblclick > li, tbody > tr > td" : "_listItem_onDoubleClick",
			"click" : "_listBackground_onClick",
			"click ul.collection-view, table.collection-view" : "_listBackground_onClick",
			"keydown" : "_onKeydown"
		},

		// only used if Backbone.Courier is available
		spawnMessages : {
			"focus" : "focus"
		},

		//only used if Backbone.Courier is available
		passMessages : { "*" : "." },

		// viewOption definitions with default values.
		initializationOptions : [
			{ "collection" : null },
			{ "modelView" : null },
			{ "modelViewOptions" : {} },
			{ "itemTemplate" : null },
			{ "itemTemplateFunction" : null },
			{ "selectable" : true },
			{ "clickToSelect" : true },
			{ "selectableModelsFilter" : null },
			{ "visibleModelsFilter" : null },
			{ "sortableModelsFilter" : null },
			{ "selectMultiple" : false },
			{ "clickToToggle" : false },
			{ "processKeyEvents" : true },
			{ "sortable" : false },
			{ "sortableOptions" : null },
			{ "reuseModelViews" : true },
			{ "detachedRendering" : false },
			{ "emptyListCaption" : null }
		],

		initialize : function( options ) {
			Backbone.ViewOptions.add( this, "initializationOptions" ); // setup the ViewOptions functionality.
			this.setOptions( options ); // and make use of any provided options

			if( ! this.collection ) this.collection = new Backbone.Collection();

			this._hasBeenRendered = false;

			if( this._isBackboneCourierAvailable() ) {
				Backbone.Courier.add( this );
			}

			this.$el.data( "view", this ); // needed for connected sortable lists
			this.$el.addClass( "collection-view collection-list" ); // collection-list is in there for legacy purposes
			if( this.selectable ) this.$el.addClass( "selectable" );

			if( this.selectable && this.processKeyEvents )
				this.$el.attr( "tabindex", 0 ); // so we get keyboard events

			this.selectedItems = [];

			this._updateItemTemplate();

			if( this.collection )
				this._registerCollectionEvents();

			this.viewManager = new ChildViewContainer();
		},

		_onOptionsChanged : function( changedOptions, originalOptions ) {
			var _this = this;
			var rerender = false;

			_.each( _.keys( changedOptions ), function( changedOptionKey ) {
				var newVal = changedOptions[ changedOptionKey ];
				var oldVal = originalOptions[ changedOptionKey ];
				switch( changedOptionKey ) {
					case "collection" :
						if ( newVal !== oldVal ) {
							_this.stopListening( oldVal );
							_this._registerCollectionEvents();
						}
						break;
					case "selectMultiple" :
						if( ! newVal && _this.selectedItems.length > 1 )
							_this.setSelectedModel( _.first( _this.selectedItems ), { by : "cid" } );
						break;
					case "selectable" :
						if( ! newVal && _this.selectedItems.length > 0 )
							_this.setSelectedModels( [] );

						if( newVal && this.processKeyEvents ) _this.$el.attr( "tabindex", 0 ); // so we get keyboard events
						else _this.$el.removeAttr( "tabindex", 0 );
						break;
					case "sortable" :
						changedOptions.sortable ? _this._setupSortable() : _this.$el.sortable( "destroy" );
						break;
					case "selectableModelsFilter" :
						_this.reapplyFilter( 'selectableModels' );
						break;
					case "sortableOptions" :
						_this.$el.sortable( "destroy" );
						_this._setupSortable();
						break;
					case "sortableModelsFilter" :
						_this.reapplyFilter( 'sortableModels' );
						break;
					case "visibleModelsFilter" :
						_this.reapplyFilter( 'visibleModels' );
						break;
					case "itemTemplate" :
						_this._updateItemTemplate();
						break;
					case "processKeyEvents" :
						if( newVal && this.selectable ) _this.$el.attr( "tabindex", 0 ); // so we get keyboard events
						else _this.$el.removeAttr( "tabindex", 0 );
						break;
					case "modelView" :
						//need to remove all old view instances
						_this.viewManager.each( function( view ) {
							_this.viewManager.remove( view );
							// destroy the View itself
							view.remove();
						} );
						break;
				}
				if( _.contains( kOptionsRequiringRerendering, changedOptionKey ) ) rerender = true;
			} );

			if( this._hasBeenRendered && rerender ) {
				this.render();
			}
		},

		setOption : function( optionName, optionValue ) { // now is mearly a wrapper around backbone.viewOptions' setOptions()
			var optionHash = {};
			optionHash[ optionName ] = optionValue;
			this.setOptions( optionHash );
		},

		getSelectedModel : function( options ) {
			return this.selectedItems.length ? _.first( this.getSelectedModels( options ) ) : null;
		},

		getSelectedModels : function ( options ) {
			var _this = this;

			options = _.extend( {}, {
				by : kDefaultReferenceBy
			}, options );

			var referenceBy = options.by;
			var items = [];

			switch( referenceBy ) {
				case "id" :
					_.each( this.selectedItems, function ( item ) {
						items.push( _this.collection.get( item ).id );
					} );
					break;
				case "cid" :
					items = items.concat( this.selectedItems );
					break;
				case "offset" :
					var curLineNumber = 0;

					var itemElements = this._getVisibleItemEls();

					itemElements.each( function() {
						var thisItemEl = $( this );
						if( thisItemEl.is( ".selected" ) )
							items.push( curLineNumber );
						curLineNumber++;
					} );
					break;
				case "model" :
					_.each( this.selectedItems, function ( item ) {
						items.push( _this.collection.get( item ) );
					} );
					break;
				case "view" :
					_.each( this.selectedItems, function ( item ) {
						items.push( _this.viewManager.findByModel( _this.collection.get( item ) ) );
					} );
					break;
				default :
					throw new Error( "Invalid referenceBy option: " + referenceBy );
					break;
			}

			return items;

		},

		setSelectedModels : function( newSelectedItems, options ) {
			if( ! _.isArray( newSelectedItems ) ) throw "Invalid parameter value";
			if( ! this.selectable && newSelectedItems.length > 0 ) return; // used to throw error, but there are some circumstances in which a list can be selectable at times and not at others, don't want to have to worry about catching errors

			options = _.extend( {}, {
				silent : false,
				by : kDefaultReferenceBy
			}, options );

			var referenceBy = options.by;
			var newSelectedCids = [];

			switch( referenceBy ) {
				case "cid" :
					newSelectedCids = newSelectedItems;
					break;
				case "id" :
					this.collection.each( function( thisModel ) {
						if( _.contains( newSelectedItems, thisModel.id ) ) newSelectedCids.push( thisModel.cid );
					} );
					break;
				case "model" :
					newSelectedCids = _.pluck( newSelectedItems, "cid" );
					break;
				case "view" :
					_.each( newSelectedItems, function( item ) {
						newSelectedCids.push( item.model.cid );
					} );
					break;
				case "offset" :
					var curLineNumber = 0;
					var selectedItems = [];

					var itemElements = this._getVisibleItemEls();
					itemElements.each( function() {
						var thisItemEl = $( this );
						if( _.contains( newSelectedItems, curLineNumber ) )
							newSelectedCids.push( thisItemEl.attr( "data-model-cid" ) );
						curLineNumber++;
					} );
					break;
				default :
					throw new Error( "Invalid referenceBy option: " + referenceBy );
					break;
			}

			var oldSelectedModels = this.getSelectedModels();
			var oldSelectedCids = _.clone( this.selectedItems );

			this.selectedItems = this._convertStringsToInts( newSelectedCids );
			this._validateSelection();

			var newSelectedModels = this.getSelectedModels();

			if( ! this._containSameElements( oldSelectedCids, this.selectedItems ) )
			{
				this._addSelectedClassToSelectedItems( oldSelectedCids );

				if( ! options.silent )
				{
					if( this._isBackboneCourierAvailable() ) {
						this.spawn( "selectionChanged", {
							selectedModels : newSelectedModels,
							oldSelectedModels : oldSelectedModels
						} );
					} else this.trigger( "selectionChanged", newSelectedModels, oldSelectedModels );
				}

				this.updateDependentControls();
			}
		},

		setSelectedModel : function( newSelectedItem, options ) {
			if( ! newSelectedItem && newSelectedItem !== 0 )
				this.setSelectedModels( [], options );
			else
				this.setSelectedModels( [ newSelectedItem ], options );
		},

		getView : function( reference, options ) {
			options = _.extend( {}, {
				by : kDefaultReferenceBy
			}, options );

			switch( options.by ) {
				case "id" :
				case "cid" :
					var model = this.collection.get( reference ) || null;
					return model && this.viewManager.findByModel( model );
					break;
				case "offset" :
					var itemElements = this._getVisibleItemEls();
					return $( itemElements.get( reference ) );
					break;
				case "model" :
					return this.viewManager.findByModel( reference );
					break;
				default :
					throw new Error( "Invalid referenceBy option: " + referenceBy );
					break;
			}
		},

		render : function() {
			var _this = this;

			this._hasBeenRendered = true;

			if( this.selectable ) this._saveSelection();

			var modelViewContainerEl;

			// If collection view element is a table and it has a tbody
			// within it, render the model views inside of the tbody
			modelViewContainerEl = this._getContainerEl();

			var oldViewManager = this.viewManager;
			this.viewManager = new ChildViewContainer();

			// detach each of our subviews that we have already created to represent models
			// in the collection. We are going to re-use the ones that represent models that
			// are still here, instead of creating new ones, so that we don't loose state
			// information in the views.
			oldViewManager.each( function( thisModelView ) {
				// to boost performance, only detach those views that will be sticking around.
				// we won't need the other ones later, so no need to detach them individually.
				if( this.reuseModelViews && this.collection.get( thisModelView.model.cid ) ) {
					thisModelView.$el.detach();
				} else thisModelView.remove();
			}, this );

			modelViewContainerEl.empty();
			var fragmentContainer;

			if( this.detachedRendering )
				fragmentContainer = document.createDocumentFragment();

			this.collection.each( function( thisModel ) {
				var thisModelView = oldViewManager.findByModelCid( thisModel.cid );
				if( ! this.reuseModelViews || _.isUndefined( thisModelView ) ) {
					// if the model view has not already been created on a
					// previous render then create and initialize it now.
					thisModelView = this._createNewModelView( thisModel, this._getModelViewOptions( thisModel ) );
				}

				this._insertAndRenderModelView( thisModelView, fragmentContainer || modelViewContainerEl );
			}, this );

			if( this.detachedRendering )
				modelViewContainerEl.append( fragmentContainer );

			if( this.sortable ) this._setupSortable();

			this._showEmptyListCaptionIfAppropriate();

			if( this._isBackboneCourierAvailable() )
				this.spawn( "render" );
			else this.trigger( "render" );

			if( this.selectable ) {
				this._restoreSelection();
				this.updateDependentControls();
			}

			this.forceRerenderOnNextSortEvent = false;
		},

		_showEmptyListCaptionIfAppropriate : function ( ) {
			this._removeEmptyListCaption();

			if( this.emptyListCaption ) {
				var visibleEls = this._getVisibleItemEls();

				if( visibleEls.length === 0 ) {
					var emptyListString;

					if( _.isFunction( this.emptyListCaption ) )
						emptyListString = this.emptyListCaption();
					else
						emptyListString = this.emptyListCaption;

					var $emptyListCaptionEl;
					var $varEl = $( "<var class='empty-list-caption'>" + emptyListString + "</var>" );

					// need to wrap the empty caption to make it fit the rendered list structure (either with an li or a tr td)
					if( this._isRenderedAsList() )
						$emptyListCaptionEl = $varEl.wrapAll( "<li class='not-sortable'></li>" ).parent().css( kStylesForEmptyListCaption );
					else
						$emptyListCaptionEl = $varEl.wrapAll( "<tr class='not-sortable'><td colspan='1000'></td></tr>" ).parent().parent().css( kStylesForEmptyListCaption );

					this._getContainerEl().append( $emptyListCaptionEl );
				}
			}
		},

		_removeEmptyListCaption : function( ) {
			if( this._isRenderedAsList() )
				this._getContainerEl().find( "> li > var.empty-list-caption" ).parent().remove();
			else
				this._getContainerEl().find( "> tr > td > var.empty-list-caption" ).parent().parent().remove();
		},

		// Render a single model view in container object "parentElOrDocumentFragment", which is either
		// a documentFragment or a jquery object. optional arg atIndex is not support for document fragments.
		_insertAndRenderModelView : function( modelView, parentElOrDocumentFragment, atIndex ) {
			var thisModelViewWrapped = this._wrapModelView( modelView );

			if( parentElOrDocumentFragment.nodeType === 11 ) // if we are inserting into a document fragment, we need to use the DOM appendChild method
				parentElOrDocumentFragment.appendChild( thisModelViewWrapped.get( 0 ) );
			else {
				var numberOfModelViewsCurrentlyInDOM = parentElOrDocumentFragment.children().length;
				if( ! _.isUndefined( atIndex ) && atIndex >= 0 && atIndex < numberOfModelViewsCurrentlyInDOM )
					// note this.collection.length might be greater than parentElOrDocumentFragment.children().length here
					parentElOrDocumentFragment.children().eq( atIndex ).before( thisModelViewWrapped );
				else {
					// if we are attempting to insert a modelView in an position that is beyond what is currently in the
					// DOM, then make a note that we need to re-render the collection view on the next sort event. If we dont
					// force this re-render, we can end up with modelViews in the wrong order when the collection defines
					// a comparator and multiple models are added at once. See https://github.com/rotundasoftware/backbone.collectionView/issues/69
					if( ! _.isUndefined( atIndex ) && atIndex > numberOfModelViewsCurrentlyInDOM ) this.forceRerenderOnNextSortEvent = true;

					parentElOrDocumentFragment.append( thisModelViewWrapped );
				}
			}

			this.viewManager.add( modelView );

			// we have to render the modelView after it has been put in context, as opposed to in the
			// initialize function of the modelView, because some rendering might be dependent on
			// the modelView's context in the DOM tree. For example, if the modelView stretch()'s itself,
			// it must be in full context in the DOM tree or else the stretch will not behave as intended.
			var renderResult = modelView.render();

			// return false from the view's render function to hide this item
			if( renderResult === false ) {
				thisModelViewWrapped.hide();
				thisModelViewWrapped.addClass( "not-visible" );
			}

			var hideThisModelView = false;
			if( _.isFunction( this.visibleModelsFilter ) )
				hideThisModelView = ! this.visibleModelsFilter( modelView.model );

			if( thisModelViewWrapped.children().length === 1 )
				thisModelViewWrapped.toggle( ! hideThisModelView );
			else modelView.$el.toggle( ! hideThisModelView );

			thisModelViewWrapped.toggleClass( "not-visible", hideThisModelView );

			if( ! hideThisModelView && this.emptyListCaption ) this._removeEmptyListCaption();
		},

		updateDependentControls : function() {
			if( this._isBackboneCourierAvailable() ) {
				this.spawn( "updateDependentControls", {
					selectedModels : this.getSelectedModels()
				} );
			} else this.trigger( "updateDependentControls", this.getSelectedModels() );
		},

		// Override `Backbone.View.remove` to also destroy all Views in `viewManager`
		remove : function() {
			this.viewManager.each( function( view ) {
				view.remove();
			} );

			Backbone.View.prototype.remove.apply( this, arguments );
		},

		reapplyFilter : function( whichFilter ) {
			var _this = this;

			if( ! _.contains( [ "selectableModels", "sortableModels", "visibleModels" ], whichFilter ) ) {
				throw new Error( "Invalid filter identifier supplied to reapplyFilter: " + whichFilter );
			}

			switch( whichFilter ) {
				case "visibleModels":
					_this.viewManager.each( function( thisModelView ) {
						var notVisible = _this.visibleModelsFilter && ! _this.visibleModelsFilter.call( _this, thisModelView.model );

						thisModelView.$el.toggleClass( "not-visible", notVisible );
						if( _this._modelViewHasWrapperLI( thisModelView ) ) {
							thisModelView.$el.closest( "li" ).toggleClass( "not-visible", notVisible ).toggle( ! notVisible );
						} else thisModelView.$el.toggle( ! notVisible );
					} );

					this._showEmptyListCaptionIfAppropriate();
					break;
				case "sortableModels":
					_this.$el.sortable( "destroy" );

					_this.viewManager.each( function( thisModelView ) {
						var notSortable = _this.sortableModelsFilter && ! _this.sortableModelsFilter.call( _this, thisModelView.model );

						thisModelView.$el.toggleClass( "not-sortable", notSortable );
						if( _this._modelViewHasWrapperLI( thisModelView ) ) {
							thisModelView.$el.closest( "li" ).toggleClass( "not-sortable", notSortable );
						}
					} );

					_this._setupSortable();
					break;
				case "selectableModels":
					_this.viewManager.each( function( thisModelView ) {
						var notSelectable = _this.selectableModelsFilter && ! _this.selectableModelsFilter.call( _this, thisModelView.model );

						thisModelView.$el.toggleClass( "not-selectable", notSelectable );
						if( _this._modelViewHasWrapperLI( thisModelView ) ) {
							thisModelView.$el.closest( "li" ).toggleClass( "not-selectable", notSelectable );
						}
					} );

					_this._validateSelection();
					break;
			}
		},

		// A method to remove the view relating to model.
		_removeModelView : function( modelView ) {
			if( this.selectable ) this._saveSelection();

			this.viewManager.remove( modelView ); // Remove the view from the viewManager
			if( this._modelViewHasWrapperLI( modelView ) ) modelView.$el.parent().remove(); // Remove the li wrapper from the DOM
			modelView.remove(); // Remove the view from the DOM and stop listening to events

			if( this.selectable ) this._restoreSelection();

			this._showEmptyListCaptionIfAppropriate();
		},

		_validateSelectionAndRender : function() {
			this._validateSelection();
			this.render();
		},

		_registerCollectionEvents : function() {

			this.listenTo( this.collection, "add", function( model ) {
				var modelView;
				if( this._hasBeenRendered ) {
					modelView = this._createNewModelView( model, this._getModelViewOptions( model ) );
					this._insertAndRenderModelView( modelView, this._getContainerEl(), this.collection.indexOf( model ) );
				}

				if( this._isBackboneCourierAvailable() )
					this.spawn( "add", modelView );
				else this.trigger( "add", modelView );
			} );

			this.listenTo( this.collection, "remove", function( model ) {
				var modelView;

				if( this._hasBeenRendered ) {
					modelView = this.viewManager.findByModelCid( model.cid );
					this._removeModelView( modelView );
				}

				if( this._isBackboneCourierAvailable() )
					this.spawn( "remove" );
				else this.trigger( "remove" );
			} );

			this.listenTo( this.collection, "reset", function() {
				if( this._hasBeenRendered ) this.render();
				if( this._isBackboneCourierAvailable() )
					this.spawn( "reset" );
				else this.trigger( "reset" );
			} );

			// we should not be listening to change events on the model as a default behavior. the models
			// should be responsible for re-rendering themselves if necessary, and if the collection does
			// also need to re-render as a result of a model change, this should be handled by overriding
			// this method. by default the collection view should not re-render in response to model changes
			// this.listenTo( this.collection, "change", function( model ) {
			// 	if( this._hasBeenRendered ) this.viewManager.findByModel( model ).render();
			// 	if( this._isBackboneCourierAvailable() )
			// 		this.spawn( "change", { model : model } );
			// } );

			this.listenTo( this.collection, "sort", function( collection, options ) {
				if( this._hasBeenRendered && ( options.add !== true || this.forceRerenderOnNextSortEvent ) ) this.render();
				if( this._isBackboneCourierAvailable() )
					this.spawn( "sort" );
				else this.trigger( "sort" );
			} );
		},

		_getContainerEl : function() {
			if ( this._isRenderedAsTable() ) {
				// not all tables have a tbody, so we test
				var tbody = this.$el.find( "> tbody" );
				if ( tbody.length > 0 )
					return tbody;
			}
			return this.$el;
		},

		_getClickedItemId : function( theEvent ) {
			var clickedItemId = null;

			// important to use currentTarget as opposed to target, since we could be bubbling
			// an event that took place within another collectionList
			var clickedItemEl = $( theEvent.currentTarget );
			if( clickedItemEl.closest( ".collection-view" ).get(0) !== this.$el.get(0) ) return;

			// determine which list item was clicked. If we clicked in the blank area
			// underneath all the elements, we want to know that too, since in this
			// case we will want to deselect all elements. so check to see if the clicked
			// DOM element is the list itself to find that out.
			var clickedItem = clickedItemEl.closest( "[data-model-cid]" );
			if( clickedItem.length > 0 )
			{
				clickedItemId = clickedItem.attr( "data-model-cid" );
				if( $.isNumeric( clickedItemId ) ) clickedItemId = parseInt( clickedItemId, 10 );
			}

			return clickedItemId;
		},

		_updateItemTemplate : function() {
			var itemTemplateHtml;
			if( this.itemTemplate )
			{
				if( $( this.itemTemplate ).length === 0 )
					throw "Could not find item template from selector: " + this.itemTemplate;

				itemTemplateHtml = $( this.itemTemplate ).html();
			}
			else
				itemTemplateHtml = this.$( ".item-template" ).html();

			if( itemTemplateHtml ) this.itemTemplateFunction = _.template( itemTemplateHtml );

		},

		_validateSelection : function() {
			// note can't use the collection's proxy to underscore because "cid" is not an attribute,
			// but an element of the model object itself.
			var modelReferenceIds = _.pluck( this.collection.models, "cid" );
			this.selectedItems = _.intersection( modelReferenceIds, this.selectedItems );

			if( _.isFunction( this.selectableModelsFilter ) )
			{
				this.selectedItems = _.filter( this.selectedItems, function( thisItemId ) {
					return this.selectableModelsFilter.call( this, this.collection.get( thisItemId ) );
				}, this );
			}
		},

		_saveSelection : function() {
			// save the current selection. use restoreSelection() to restore the selection to the state it was in the last time saveSelection() was called.
			if( ! this.selectable ) throw "Attempt to save selection on non-selectable list";
			this.savedSelection = {
				items : _.clone( this.selectedItems ),
				offset : this.getSelectedModel( { by : "offset" } )
			};
		},

		_restoreSelection : function() {
			if( ! this.savedSelection ) throw "Attempt to restore selection but no selection has been saved!";

			// reset selectedItems to empty so that we "redraw" all "selected" classes
			// when we set our new selection. We do this because it is likely that our
			// contents have been refreshed, and we have thus lost all old "selected" classes.
			this.setSelectedModels( [], { silent : true } );

			if( this.savedSelection.items.length > 0 )
			{
				// first try to restore the old selected items using their reference ids.
				this.setSelectedModels( this.savedSelection.items, { by : "cid", silent : true } );

				// all the items with the saved reference ids have been removed from the list.
				// ok. try to restore the selection based on the offset that used to be selected.
				// this is the expected behavior after a item is deleted from a list (i.e. select
				// the line that immediately follows the deleted line).
				if( this.selectedItems.length === 0 )
					this.setSelectedModel( this.savedSelection.offset, { by : "offset" } );

				// Trigger a selection changed if the previously selected items were not all found
				if (this.selectedItems.length !== this.savedSelection.items.length)
				{
					if( this._isBackboneCourierAvailable() ) {
						this.spawn( "selectionChanged", {
							selectedModels : this.getSelectedModels(),
							oldSelectedModels : []
						} );
					} else this.trigger( "selectionChanged", this.getSelectedModels(), [] );
				}
			}
		},

		_addSelectedClassToSelectedItems : function( oldItemsIdsWithSelectedClass ) {
			if( _.isUndefined( oldItemsIdsWithSelectedClass ) ) oldItemsIdsWithSelectedClass = [];

			// oldItemsIdsWithSelectedClass is used for optimization purposes only. If this info is supplied then we
			// only have to add / remove the "selected" class from those items that "selected" state has changed.

			var itemsIdsFromWhichSelectedClassNeedsToBeRemoved = oldItemsIdsWithSelectedClass;
			itemsIdsFromWhichSelectedClassNeedsToBeRemoved = _.without( itemsIdsFromWhichSelectedClassNeedsToBeRemoved, this.selectedItems );

			_.each( itemsIdsFromWhichSelectedClassNeedsToBeRemoved, function( thisItemId ) {
				this._getContainerEl().find( "[data-model-cid=" + thisItemId + "]" ).removeClass( "selected" );

				if( this._isRenderedAsList() ) {
					this._getContainerEl().find( "li[data-model-cid=" + thisItemId + "] > *" ).removeClass( "selected" );
				}
			}, this );

			var itemsIdsFromWhichSelectedClassNeedsToBeAdded = this.selectedItems;
			itemsIdsFromWhichSelectedClassNeedsToBeAdded = _.without( itemsIdsFromWhichSelectedClassNeedsToBeAdded, oldItemsIdsWithSelectedClass );

			_.each( itemsIdsFromWhichSelectedClassNeedsToBeAdded, function( thisItemId ) {
				this._getContainerEl().find( "[data-model-cid=" + thisItemId + "]" ).addClass( "selected" );

				if( this._isRenderedAsList() ) {
					this._getContainerEl().find( "li[data-model-cid=" + thisItemId + "] > *" ).addClass( "selected" );
				}
			}, this );
		},

		_reorderCollectionBasedOnHTML : function() {

			var _this = this;

			this._getContainerEl().children().each( function() {
				var thisModelCid = $( this ).attr( "data-model-cid" );

				if( thisModelCid )
				{
					// remove the current model and then add it back (at the end of the collection).
					// When we are done looping through all models, they will be in the correct order.
					var thisModel = _this.collection.get( thisModelCid );
					if( thisModel )
					{
						_this.collection.remove( thisModel, { silent : true } );
						_this.collection.add( thisModel, { silent : true, sort : ! _this.collection.comparator } );
					}
				}
			} );

			if( this._isBackboneCourierAvailable() ) this.spawn( "reorder" );
			else this.collection.trigger( "reorder" );

			if( this.collection.comparator ) this.collection.sort();

		},

		_getModelViewConstructor : function( thisModel ) {
			return this.modelView || mDefaultModelViewConstructor;
		},

		_getModelViewOptions : function( thisModel ) {
			var modelViewOptions = this.modelViewOptions;
			if( _.isFunction( modelViewOptions ) ) modelViewOptions = modelViewOptions( thisModel );

			return _.extend( { model : thisModel }, modelViewOptions );
		},

		_createNewModelView : function( model, modelViewOptions ) {
			var modelViewConstructor = this._getModelViewConstructor( model );
			if( _.isUndefined( modelViewConstructor ) ) throw "Could not find modelView constructor for model";

			var newModelView = new( modelViewConstructor )( modelViewOptions );
			newModelView.collectionListView = newModelView.collectionView = this;  // collectionListView for legacy

			return newModelView;
		},

		_wrapModelView : function( modelView ) {
			var _this = this;

			// we use items client ids as opposed to real ids, since we may not have a representation
			// of these models on the server
			var modelViewWrapperEl;

			if( this._isRenderedAsTable() ) {
				// if we are rendering the collection in a table, the template $el is a tr so we just need to set the data-model-cid
				modelViewWrapperEl = modelView.$el;
				modelView.$el.attr( "data-model-cid", modelView.model.cid );
			}
			else if( this._isRenderedAsList() ) {
				// if we are rendering the collection in a list, we need wrap each item in an <li></li> (if its not already an <li>)
				// and set the data-model-cid
				if( modelView.$el.is( "li" ) ) {
					modelViewWrapperEl = modelView.$el;
					modelView.$el.attr( "data-model-cid", modelView.model.cid );
				} else {
					modelViewWrapperEl = modelView.$el.wrapAll( "<li data-model-cid='" + modelView.model.cid + "'></li>" ).parent();
				}
			}

			if( _.isFunction( this.sortableModelsFilter ) )
				if( ! this.sortableModelsFilter.call( _this, modelView.model ) ) {
					modelViewWrapperEl.addClass( "not-sortable" );
					modelView.$el.addClass( "not-selectable" );
				}

			if( _.isFunction( this.selectableModelsFilter ) )
				if( ! this.selectableModelsFilter.call( _this, modelView.model ) ) {
					modelViewWrapperEl.addClass( "not-selectable" );
					modelView.$el.addClass( "not-selectable" );
				}

			return modelViewWrapperEl;
		},

		_convertStringsToInts : function( theArray ) {
			return _.map( theArray, function( thisEl ) {
				if( ! _.isString( thisEl ) ) return thisEl;
				var thisElAsNumber = parseInt( thisEl, 10 );
				return( thisElAsNumber == thisEl ? thisElAsNumber : thisEl );
			} );
		},

		_containSameElements : function( arrayA, arrayB ) {
			if( arrayA.length != arrayB.length ) return false;
			var intersectionSize = _.intersection( arrayA, arrayB ).length;
			return intersectionSize == arrayA.length; // and must also equal arrayB.length, since arrayA.length == arrayB.length
		},

		_isRenderedAsTable : function() {
			return this.$el.prop( "tagName" ).toLowerCase() === "table";
		},

		_isRenderedAsList : function() {
			return ! this._isRenderedAsTable();
		},

		_modelViewHasWrapperLI : function( modelView ) {
			return this._isRenderedAsList() && ! modelView.$el.is( "li" );
		},

		// Returns the wrapper HTML element for each visible modelView.
		// When rendering in a table context, the returned elements are the $el of each modelView.
		// When rendering in a list context,
		//   If the $el of the modelView is an <li>, the returned elements are the $el of each modelView.
		//   Otherwise, the returned elements are the <li>'s the collectionView wrapped around each modelView $el.
		_getVisibleItemEls : function() {
			var itemElements = [];
			itemElements = this._getContainerEl().find( "> [data-model-cid]:not(.not-visible)" );

			return itemElements;
		},

		_charCodes : {
			upArrow : 38,
			downArrow : 40
		},

		_isBackboneCourierAvailable : function() {
			return !_.isUndefined( Backbone.Courier );
		},

		_setupSortable : function() {
			var sortableOptions = _.extend( {
				axis : "y",
				distance : 10,
				forcePlaceholderSize : true,
				items : this._isRenderedAsTable() ? "> tbody > tr:not(.not-sortable)" : "> li:not(.not-sortable)",
				start : _.bind( this._sortStart, this ),
				change : _.bind( this._sortChange, this ),
				stop : _.bind( this._sortStop, this ),
				receive : _.bind( this._receive, this ),
				over : _.bind( this._over, this )
			}, _.result( this, "sortableOptions" ) );

			this.$el = this.$el.sortable( sortableOptions );
			//this.$el.sortable( "enable" ); // in case it was disabled previously
		},

		_sortStart : function( event, ui ) {
			var modelBeingSorted = this.collection.get( ui.item.attr( "data-model-cid" ) );
			if( this._isBackboneCourierAvailable() )
				this.spawn( "sortStart", { modelBeingSorted : modelBeingSorted } );
			else this.trigger( "sortStart", modelBeingSorted );
		},

		_sortChange : function( event, ui ) {
			var modelBeingSorted = this.collection.get( ui.item.attr( "data-model-cid" ) );

			if( this._isBackboneCourierAvailable() )
				this.spawn( "sortChange", { modelBeingSorted : modelBeingSorted } );
			else this.trigger( "sortChange", modelBeingSorted );
		},

		_sortStop : function( event, ui ) {
			var modelBeingSorted = this.collection.get( ui.item.attr( "data-model-cid" ) );
			var modelViewContainerEl = this._getContainerEl();
			var newIndex = modelViewContainerEl.children().index( ui.item );

			if( newIndex == -1 && modelBeingSorted ) {
				// the element was removed from this list. can happen if this sortable is connected
				// to another sortable, and the item was dropped into the other sortable.
				this.collection.remove( modelBeingSorted );
			}

			if( ! modelBeingSorted ) return; // something is wacky. we don't mess with this case, preferring to guarantee that we can always provide a reference to the model

			this._reorderCollectionBasedOnHTML();
			this.updateDependentControls();

			if( this._isBackboneCourierAvailable() )
				this.spawn( "sortStop", { modelBeingSorted : modelBeingSorted, newIndex : newIndex } );
			else this.trigger( "sortStop", modelBeingSorted, newIndex );
		},

		_receive : function( event, ui ) {

			var senderListEl = ui.sender;
			var senderCollectionListView = senderListEl.data( "view" );
			if( ! senderCollectionListView || ! senderCollectionListView.collection ) return;

			var newIndex = this._getContainerEl().children().index( ui.item );
			var modelReceived = senderCollectionListView.collection.get( ui.item.attr( "data-model-cid" ) );
			senderCollectionListView.collection.remove( modelReceived );
			this.collection.add( modelReceived, { at : newIndex } );
			modelReceived.collection = this.collection; // otherwise will not get properly set, since modelReceived.collection might already have a value.
			this.setSelectedModel( modelReceived );
		},

		_over : function( event, ui ) {
			// when an item is being dragged into the sortable,
			// hide the empty list caption if it exists
			this._getContainerEl().find( "> var.empty-list-caption" ).hide();
		},

		_onKeydown : function( event ) {
			if( ! this.processKeyEvents ) return true;

			var trap = false;

			if( this.getSelectedModels( { by : "offset" } ).length == 1 )
			{
				// need to trap down and up arrows or else the browser
				// will end up scrolling a autoscroll div.

				var currentOffset = this.getSelectedModel( { by : "offset" } );
				if( event.which === this._charCodes.upArrow && currentOffset !== 0 )
				{
					this.setSelectedModel( currentOffset - 1, { by : "offset" } );
					trap = true;
				}
				else if( event.which === this._charCodes.downArrow && currentOffset !== this.collection.length - 1 )
				{
					this.setSelectedModel( currentOffset + 1, { by : "offset" } );
					trap = true;
				}
			}

			return ! trap;
		},

		_listItem_onMousedown : function( theEvent ) {
			var clickedItemId = this._getClickedItemId( theEvent );

			if( clickedItemId ) {
				var clickedModel = this.collection.get( clickedItemId );
				if( this._isBackboneCourierAvailable() ) {
					var data = {
						clickedModel : clickedModel,
						metaKeyPressed : theEvent.ctrlKey || theEvent.metaKey
					};

					_.each( [ 'preventDefault', 'stopPropagation', 'stopImmediatePropagation' ], function( thisMethod ) {
						data[ thisMethod ] = function() {
							theEvent[ thisMethod ]();
						};
					} );

					this.spawn( "click", data );
				}
				else this.trigger( "click", clickedModel );
			}

			if( ! this.selectable || ! this.clickToSelect ) return;

			if( clickedItemId )
			{
				// Exit if an unselectable item was clicked
				if( _.isFunction( this.selectableModelsFilter ) &&
					! this.selectableModelsFilter.call( this, this.collection.get( clickedItemId ) ) )
				{
					return;
				}

				// a selectable list item was clicked
				if( this.selectMultiple && theEvent.shiftKey )
				{
					var firstSelectedItemIndex = -1;

					if( this.selectedItems.length > 0 )
					{
						this.collection.find( function( thisItemModel ) {
							firstSelectedItemIndex++;

							// exit when we find our first selected element
							return _.contains( this.selectedItems, thisItemModel.cid );
						}, this );
					}

					var clickedItemIndex = -1;
					this.collection.find( function( thisItemModel ) {
						clickedItemIndex++;

						// exit when we find the clicked element
						return thisItemModel.cid == clickedItemId;
					}, this );

					var shiftKeyRootSelectedItemIndex = firstSelectedItemIndex == -1 ? clickedItemIndex : firstSelectedItemIndex;
					var minSelectedItemIndex = Math.min( clickedItemIndex, shiftKeyRootSelectedItemIndex );
					var maxSelectedItemIndex = Math.max( clickedItemIndex, shiftKeyRootSelectedItemIndex );

					var newSelectedItems = [];
					for( var thisIndex = minSelectedItemIndex; thisIndex <= maxSelectedItemIndex; thisIndex ++ )
						newSelectedItems.push( this.collection.at( thisIndex ).cid );
					this.setSelectedModels( newSelectedItems, { by : "cid" } );

					// shift clicking will usually highlight selectable text, which we do not want.
					// this is a cross browser (hopefully) snippet that deselects all text selection.
					if( document.selection && document.selection.empty )
						document.selection.empty();
					else if(window.getSelection) {
						var sel = window.getSelection();
						if( sel && sel.removeAllRanges )
							sel.removeAllRanges();
					}
				}
				else if( ( this.selectMultiple || _.contains( this.selectedItems, clickedItemId ) ) && ( this.clickToToggle || theEvent.metaKey || theEvent.ctrlKey ) )
				{
					if( _.contains( this.selectedItems, clickedItemId ) )
						this.setSelectedModels( _.without( this.selectedItems, clickedItemId ), { by : "cid" } );
					else this.setSelectedModels( _.union( this.selectedItems, [clickedItemId] ), { by : "cid" } );
				}
				else
					this.setSelectedModels( [ clickedItemId ], { by : "cid" } );
			}
			else
				// the blank area of the list was clicked
				this.setSelectedModels( [] );

		},

		_listItem_onDoubleClick : function( theEvent ) {

			var clickedItemId = this._getClickedItemId( theEvent );

			if( clickedItemId )
			{
				var clickedModel = this.collection.get( clickedItemId );

				if( this._isBackboneCourierAvailable() )
					this.spawn( "doubleClick", { clickedModel : clickedModel, metaKeyPressed : theEvent.ctrlKey || theEvent.metaKey } );
				else this.trigger( "doubleClick", clickedModel );
			}
		},

		_listBackground_onClick : function( theEvent ) {
			if( ! this.selectable || ! this.clickToSelect ) return;
			if( ! $( theEvent.target ).is( ".collection-view" ) ) return;

			this.setSelectedModels( [] );
		}

	}, {
		setDefaultModelViewConstructor : function( theConstructor ) {
			mDefaultModelViewConstructor = theConstructor;
		}
	});

	/*
	* Backbone.ViewOptions, v0.2.4
	* Copyright (c)2014 Rotunda Software, LLC.
	* Distributed under MIT license
	* http://github.com/rotundasoftware/backbone.viewOptions
	*/

	Backbone.ViewOptions = {};

	Backbone.ViewOptions.add = function( view, optionsDeclarationsProperty ) {
		if( _.isUndefined( optionsDeclarationsProperty ) ) optionsDeclarationsProperty = "options";

		// ****************** Public methods added to view ******************

		view.setOptions = function( options ) {
			var _this = this;
			var optionsThatWereChanged = {};
			var optionsThatWereChangedPreviousValues = {};

			var optionDeclarations = _.result( this, optionsDeclarationsProperty );

			if( ! _.isUndefined( optionDeclarations ) ) {
				var normalizedOptionDeclarations = _normalizeOptionDeclarations( optionDeclarations );

				_.each( normalizedOptionDeclarations, function( thisOptionProperties, thisOptionName ) {
					var thisOptionRequired = thisOptionProperties.required;
					var thisOptionDefaultValue = thisOptionProperties.defaultValue;

					if( thisOptionRequired ) {
						// note we do not throw an error if a required option is not supplied, but it is
						// found on the object itself (due to a prior call of view.setOptions, most likely)

						if( ( ! options || ! _.contains( _.keys( options ), thisOptionName ) ) && _.isUndefined( _this[ thisOptionName ] ) )
							throw new Error( "Required option \"" + thisOptionName + "\" was not supplied." );

						if( options && _.contains( _.keys( options ), thisOptionName ) && _.isUndefined( options[ thisOptionName ] ) )
							throw new Error( "Required option \"" + thisOptionName + "\" can not be set to undefined." );
					}

					// attach the supplied value of this option, or the appropriate default value, to the view object
					if( options && thisOptionName in options && ! _.isUndefined( options[ thisOptionName ] ) ) {
						var oldValue = _this[ thisOptionName ];
						var newValue = options[ thisOptionName ];
						// if this option already exists on the view, and the new value is different,
						// make a note that we will be changing it
						if( ! _.isUndefined( oldValue ) && oldValue !== newValue ) {
							optionsThatWereChangedPreviousValues[ thisOptionName ] = oldValue;
							optionsThatWereChanged[ thisOptionName ] = newValue;
						}
						_this[ thisOptionName ] = newValue;
						// note we do NOT delete the option off the options object here so that
						// multiple views can be passed the same options object without issue.
					}
					else if( _.isUndefined( _this[ thisOptionName ] ) ) {
						// note defaults do not write over any existing properties on the view itself.
						_this[ thisOptionName ] = thisOptionDefaultValue;
					}
				} );
			}

			if( _.keys( optionsThatWereChanged ).length > 0 ) {
				if( _.isFunction( _this.onOptionsChanged ) )
					_this.onOptionsChanged( optionsThatWereChanged, optionsThatWereChangedPreviousValues );
				else if( _.isFunction( _this._onOptionsChanged ) )
					_this._onOptionsChanged( optionsThatWereChanged, optionsThatWereChangedPreviousValues );
			}
		};

		view.getOptions = function() {
			var optionDeclarations = _.result( this, optionsDeclarationsProperty );
			if( _.isUndefined( optionDeclarations ) ) return {};

			var normalizedOptionDeclarations = _normalizeOptionDeclarations( optionDeclarations );
			var optionsNames = _.keys( normalizedOptionDeclarations );

			return _.pick( this, optionsNames );
		};
	};

	// ****************** Private Utility Functions ******************

	function _normalizeOptionDeclarations( optionDeclarations ) {
		// convert our short-hand option syntax (with exclamation marks, etc.)
		// to a simple array of standard option declaration objects.

		var normalizedOptionDeclarations = {};

		if( ! _.isArray( optionDeclarations ) ) throw new Error( "Option declarations must be an array." );

		_.each( optionDeclarations, function( thisOptionDeclaration ) {
			var thisOptionName, thisOptionRequired, thisOptionDefaultValue;

			thisOptionRequired = false;
			thisOptionDefaultValue = undefined;

			if( _.isString( thisOptionDeclaration ) )
				thisOptionName = thisOptionDeclaration;
			else if( _.isObject( thisOptionDeclaration ) ) {
				thisOptionName = _.first( _.keys( thisOptionDeclaration ) );
				if( _.isFunction( thisOptionDeclaration[ thisOptionName ] ) )
					thisOptionDefaultValue = thisOptionDeclaration[ thisOptionName ];
				else
					thisOptionDefaultValue = _.clone( thisOptionDeclaration[ thisOptionName ] );
			}
			else throw new Error( "Each element in the option declarations array must be either a string or an object." );

			if( thisOptionName[ thisOptionName.length - 1 ] === "!" ) {
				thisOptionRequired = true;
				thisOptionName = thisOptionName.slice( 0, thisOptionName.length - 1 );
			}

			normalizedOptionDeclarations[ thisOptionName ] = normalizedOptionDeclarations[ thisOptionName ] || {};
			normalizedOptionDeclarations[ thisOptionName ].required = thisOptionRequired;
			if( ! _.isUndefined( thisOptionDefaultValue ) ) normalizedOptionDeclarations[ thisOptionName ].defaultValue = thisOptionDefaultValue;
		} );

		return normalizedOptionDeclarations;
	}


	// Backbone.BabySitter
	// -------------------
	// v0.0.6
	//
	// Copyright (c)2013 Derick Bailey, Muted Solutions, LLC.
	// Distributed under MIT license
	//
	// http://github.com/babysitterjs/backbone.babysitter

	// Backbone.ChildViewContainer
	// ---------------------------
	//
	// Provide a container to store, retrieve and
	// shut down child views.

	ChildViewContainer = (function(Backbone, _){

		// Container Constructor
		// ---------------------

		var Container = function(views){
			this._views = {};
			this._indexByModel = {};
			this._indexByCustom = {};
			this._updateLength();

			_.each(views, this.add, this);
		};

		// Container Methods
		// -----------------

		_.extend(Container.prototype, {

			// Add a view to this container. Stores the view
			// by `cid` and makes it searchable by the model
			// cid (and model itself). Optionally specify
			// a custom key to store an retrieve the view.
			add: function(view, customIndex){
				var viewCid = view.cid;

				// store the view
				this._views[viewCid] = view;

				// index it by model
				if (view.model){
					this._indexByModel[view.model.cid] = viewCid;
				}

				// index by custom
				if (customIndex){
					this._indexByCustom[customIndex] = viewCid;
				}

				this._updateLength();
			},

			// Find a view by the model that was attached to
			// it. Uses the model's `cid` to find it.
			findByModel: function(model){
				return this.findByModelCid(model.cid);
			},

			// Find a view by the `cid` of the model that was attached to
			// it. Uses the model's `cid` to find the view `cid` and
			// retrieve the view using it.
			findByModelCid: function(modelCid){
				var viewCid = this._indexByModel[modelCid];
				return this.findByCid(viewCid);
			},

			// Find a view by a custom indexer.
			findByCustom: function(index){
				var viewCid = this._indexByCustom[index];
				return this.findByCid(viewCid);
			},

			// Find by index. This is not guaranteed to be a
			// stable index.
			findByIndex: function(index){
				return _.values(this._views)[index];
			},

			// retrieve a view by it's `cid` directly
			findByCid: function(cid){
				return this._views[cid];
			},

			findIndexByCid : function( cid ) {
				var index = -1;
				var view = _.find( this._views, function ( view ) {
					index++;
					if( view.model.cid == cid )
						return view;
				} );
				return ( view ) ? index : -1;
			},

			// Remove a view
			remove: function(view){
				var viewCid = view.cid;

				// delete model index
				if (view.model){
					delete this._indexByModel[view.model.cid];
				}

				// delete custom index
				_.any(this._indexByCustom, function(cid, key) {
					if (cid === viewCid) {
						delete this._indexByCustom[key];
						return true;
					}
				}, this);

				// remove the view from the container
				delete this._views[viewCid];

				// update the length
				this._updateLength();
			},

			// Call a method on every view in the container,
			// passing parameters to the call method one at a
			// time, like `function.call`.
			call: function(method){
				this.apply(method, _.tail(arguments));
			},

			// Apply a method on every view in the container,
			// passing parameters to the call method one at a
			// time, like `function.apply`.
			apply: function(method, args){
				_.each(this._views, function(view){
					if (_.isFunction(view[method])){
						view[method].apply(view, args || []);
					}
				});
			},

			// Update the `.length` attribute on this container
			_updateLength: function(){
				this.length = _.size(this._views);
			}
		});

		// Borrowing this code from Backbone.Collection:
		// http://backbonejs.org/docs/backbone.html#section-106
		//
		// Mix in methods from Underscore, for iteration, and other
		// collection related features.
		var methods = ['forEach', 'each', 'map', 'find', 'detect', 'filter',
			       'select', 'reject', 'every', 'all', 'some', 'any', 'include',
			       'contains', 'invoke', 'toArray', 'first', 'initial', 'rest',
			       'last', 'without', 'isEmpty', 'pluck'];

		_.each(methods, function(method) {
			Container.prototype[method] = function() {
				var views = _.values(this._views);
				var args = [views].concat(_.toArray(arguments));
				return _[method].apply(_, args);
			};
		});

		// return the public API
		return Container;
	})(Backbone, _);

	return Backbone.CollectionView;
} ) );

//
// backbone.trackit - 0.1.0
// The MIT License
// Copyright (c) 2013 The New York Times, CMS Group, Matthew DeLambo <delambo@gmail.com>
//
(function() {

	// Unsaved Record Keeping
	// ----------------------

	// Collection of all models in an app that have unsaved changes.
	var unsavedModels = [];

	// If the given model has unsaved changes then add it to
	// the `unsavedModels` collection, otherwise remove it.
	var updateUnsavedModels = function(model) {
		if (!_.isEmpty(model._unsavedChanges)) {
			if (!_.findWhere(unsavedModels, {cid:model.cid})) unsavedModels.push(model);
		} else {
			unsavedModels = _.filter(unsavedModels, function(m) { return model.cid != m.cid; });
		}
	};

	// Unload Handlers
	// ---------------

	// Helper which returns a prompt message for an unload handler.
	// Uses the given function name (one of the callback names
	// from the `model.unsaved` configuration hash) to evaluate
	// whether a prompt is needed/returned.
	var getPrompt = function(fnName) {
		var prompt, args = _.rest(arguments);
		// Evaluate and return a boolean result. The given `fn` may be a
		// boolean value, a function, or the name of a function on the model.
		var evaluateModelFn = function(model, fn) {
			if (_.isBoolean(fn)) return fn;
			return (_.isString(fn) ? model[fn] : fn).apply(model, args);
		};
		_.each(unsavedModels, function(model) {
			if (!prompt && evaluateModelFn(model, model._unsavedConfig[fnName]))
				prompt = model._unsavedConfig.prompt;
		});
		return prompt;
	};

	// Wrap Backbone.History.navigate so that in-app routing
	// (`router.navigate('/path')`) can be intercepted with a
	// confirmation if there are any unsaved models.
	Backbone.History.prototype.navigate = _.wrap(Backbone.History.prototype.navigate, function(oldNav, fragment, options) {
		var prompt = getPrompt('unloadRouterPrompt', fragment, options);
		if (prompt) {
			if (confirm(prompt + ' \n\nAre you sure you want to leave this page?')) {
				oldNav.call(this, fragment, options);
			}
		} else {
			oldNav.call(this, fragment, options);
		}
	});

	// Create a browser unload handler which is triggered
	// on the refresh, back, or forward button.
	window.onbeforeunload = function(e) {
		return getPrompt('unloadWindowPrompt', e);
	};

	// Backbone.Model API
	// ------------------

	_.extend(Backbone.Model.prototype, {

		unsaved: {},
		_trackingChanges: false,
		_originalAttrs: {},
		_unsavedChanges: {},

		// Opt in to tracking attribute changes
		// between saves.
		startTracking: function() {
			this._unsavedConfig = _.extend({}, {
				prompt: 'You have unsaved changes!',
				unloadRouterPrompt: false,
				unloadWindowPrompt: false
			}, this.unsaved || {});
			this._trackingChanges = true;
			this._resetTracking();
			this._triggerUnsavedChanges();
			return this;
		},

		// Resets the default tracking values
		// and stops tracking attribute changes.
		stopTracking: function() {
			this._trackingChanges = false;
			this._originalAttrs = {};
			this._unsavedChanges = {};
			this._triggerUnsavedChanges();
			return this;
		},

		// Gets rid of accrued changes and
		// resets state.
		restartTracking: function() {
			this._resetTracking();
			this._triggerUnsavedChanges();
			return this;
		},

		// Restores this model's attributes to
		// their original values since tracking
		// started, the last save, or last restart.
		resetAttributes: function() {
			if (!this._trackingChanges) return;
			this.attributes = this._originalAttrs;
			this._resetTracking();
			this._triggerUnsavedChanges();
			return this;
		},

		// Symmetric to Backbone's `model.changedAttributes()`,
		// except that this returns a hash of the model's attributes that
		// have changed since the last save, or `false` if there are none.
		// Like `changedAttributes`, an external attributes hash can be
		// passed in, returning the attributes in that hash which differ
		// from the model.
		unsavedAttributes: function(attrs) {
			if (!attrs) return _.isEmpty(this._unsavedChanges) ? false : _.clone(this._unsavedChanges);
			var val, changed = false, old = this._unsavedChanges;
			for (var attr in attrs) {
				if (_.isEqual(old[attr], (val = attrs[attr]))) continue;
				(changed || (changed = {}))[attr] = val;
			}
			return changed;
		},

		_resetTracking: function() {
			this._originalAttrs = _.clone(this.attributes);
			this._unsavedChanges = {};
		},

		// Trigger an `unsavedChanges` event on this model,
		// supplying the result of whether there are unsaved
		// changes and a changed attributes hash.
		_triggerUnsavedChanges: function() {
			this.trigger('unsavedChanges', !_.isEmpty(this._unsavedChanges), _.clone(this._unsavedChanges));
			if (this.unsaved) updateUnsavedModels(this);
		}
	});

	// Wrap `model.set()` and update the internal
	// unsaved changes record keeping.
	Backbone.Model.prototype.set = _.wrap(Backbone.Model.prototype.set, function(oldSet, key, val, options) {
		var attrs, ret;
		if (key == null) return this;
		// Handle both `"key", value` and `{key: value}` -style arguments.
		if (typeof key === 'object') {
			attrs = key;
			options = val;
		} else {
			(attrs = {})[key] = val;
		}
		options || (options = {});

		// Delegate to Backbone's set.
		ret = oldSet.call(this, attrs, options);

		if (this._trackingChanges && !options.silent) {
			_.each(attrs, _.bind(function(val, key) {
				if (_.isEqual(this._originalAttrs[key], val))
					delete this._unsavedChanges[key];
				else
					this._unsavedChanges[key] = val;
			}, this));
			this._triggerUnsavedChanges();
		}
		return ret;
	});

	// Intercept `model.save()` and reset tracking/unsaved
	// changes if it was successful.
	Backbone.sync = _.wrap(Backbone.sync, function(oldSync, method, model, options) {
		options || (options = {});

		if (method == 'update') {
			options.success = _.wrap(options.success, _.bind(function(oldSuccess, data, textStatus, jqXHR) {
				var ret;
				if (oldSuccess) ret = oldSuccess.call(this, data, textStatus, jqXHR);
				if (model._trackingChanges) {
					model._resetTracking();
					model._triggerUnsavedChanges();
				}
				return ret;
			}, this));
		}
		return oldSync(method, model, options);
	});

})();
define("vendor/backbone.trackit", function(){});

/**
 * Image object model for use in various models for the 'image' attribute
 * @since    3.16.0
 * @version  3.16.0
 */
define( 'Models/Image',[], function() {

	return Backbone.Model.extend( {

		defaults: {
			enabled: 'no',
			id: '',
			size: 'full',
			src: '',
		},

		initialize: function() {
			this.startTracking();
		},

	} );
} );

/**
 * Model relationships mixin
 * @since    3.16.0
 * @version  3.16.11
 */
define( 'Models/_Relationships',[], function() {

	return {

		/**
		 * Default relationship settings object
		 * @type  {Object}
		 */
		relationship_defaults: {
			parent: {},
			children: {},
		},

		/**
		 * Relationship settings object
		 * Should be overriden in the model
		 * @type  {Object}
		 */
		relationships: {},

		/**
		 * Initialize all parent and child relationships
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		init_relationships: function( options ) {

			var rels = this.get_relationships();

			// initialize parent relaxtionships
			// useful when adding a model to ensure parent is initialized
			if ( rels.parent && options && options.parent ) {
				this.set_parent( options.parent );
			}

			// initialize all children relationships
			_.each( rels.children, function( child_data, child_key ) {

				if ( ! child_data.conditional || true === child_data.conditional( this ) ) {

					var child_val = this.get( child_key ),
						child;

					if ( child_data.lookup ) {
						child = child_data.lookup( child_val );
					} else if ( 'model' === child_data.type ) {
						child = window.llms_builder.construct.get_model( child_data.class, child_val );
					} else if ( 'collection' === child_data.type ) {
						child = window.llms_builder.construct.get_collection( child_data.class, child_val );
					}

					this.set( child_key, child );

					// if the child defines a parent, save a reference to the parent on the child
					if ( 'model' === child_data.type ) {
						this._maybe_set_parent_reference( child );

					// save directly to each model in the collection
					} else if ( 'collection' === child_data.type ) {

						child.parent = this;
						child.each( function( child_model ) {

							this._maybe_set_parent_reference( child_model );

						}, this );

					}

				}

			}, this );

		},

		/**
		 * Retrieve the property names for all children of the model
		 * @return   array
		 * @since    3.16.11
		 * @version  3.16.11
		 */
		get_child_props: function() {

			var props = [];

			_.each( this.get_relationships().children, function( data, key ) {

				if ( ! data.conditional || true === data.conditional( this ) ) {
					props.push( key );
				}

			}, this );

			return props;

		},

		/**
		 * Retrieve the model's parent (if set)
		 * @return   obj|false
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		get_parent: function() {

			var rels = this.get_relationships();

			if ( rels.parent ) {
				return rels.parent.reference;
			}

			return false;

		},

		/**
		 * Retrieve relationships for the model
		 * Extends with defaults
		 * @return   obj
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		get_relationships: function() {

			return $.extend( true, this.relationships, this.relationship_defaults );

		},

		/**
		 * Set the parent reference for the given model
		 * @param    obj   obj   parent model obj
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		set_parent: function( obj ) {
			this.relationships.parent.reference = obj;
		},

		/**
		 * Set up the parent relationships for qualifying children during relationship initialization
		 * @param    obj   model  child model
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		_maybe_set_parent_reference: function( model ) {

			if ( ! model || ! model.get_relationships ) {
				return;
			}
			var rels = model.get_relationships();
			if ( rels.parent && rels.parent.model === this.get( 'type' ) ) {
				model.set_parent( this );
			}

		},

	};

} );

/**
 * Quiz Question Choice
 * @since    3.16.0
 * @version  3.16.0
 */
define( 'Models/QuestionChoice',[ 'Models/Image', 'Models/_Relationships' ], function( Image, Relationships ) {

	return Backbone.Model.extend( _.defaults( {

		/**
		 * Model relationships
		 * @type  {Object}
		 */
		relationships: {
			parent: {
				model: 'llms_question',
				type: 'model',
			},
			children: {
				choice: {
					conditional: function( model ) {
						return ( 'image' === model.get( 'choice_type' ) );
					},
					class: 'Image',
					model: 'image',
					type: 'model',
				},
			},
		},

		/**
		 * Model defaults
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		defaults: function() {
			return {
				id: _.uniqueId( 'temp_' ),
				choice: '',
				choice_type: 'text',
				correct: false,
				marker: 'A',
				question_id: '',
				type: 'choice',
			}
		},

		/**
		 * Initializer
		 * @param    obj   data     object of model attributes
		 * @param    obj   options  additional options
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		initialize: function( data, options ) {

			this.startTracking();
			this.init_relationships( options );

		},

		/**
		 * Retrieve the choice's parent question
		 * @return   obj
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		get_parent: function() {
			return this.collection.parent;
		},

		/**
		 * Retrieve the ID used when trashing the model
		 * @return   string
		 * @since    3.17.1
		 * @version  3.17.1
		 */
		get_trash_id: function() {
			return this.get( 'question_id' ) + ':' + this.get( 'id' );
		},

		/**
		 * Determine if "selection" is enabled for the question type
		 * Choice type questions are selectable by reorder type questions are not but still use choices
		 * @return   {Boolean}
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		is_selectable: function() {
			return this.get_parent().get( 'question_type' ).get_choice_selectable();
		},

	}, Relationships ) );

} );

/**
 * Question Choice Collection
 * @since    3.16.0
 * @version  3.16.0
 */
define( 'Collections/QuestionChoices',[ 'Models/QuestionChoice' ], function( model ) {

	return Backbone.Collection.extend( {

		/**
		 * Model for collection items
		 * @type  obj
		 */
		model: model,

		initialize: function() {

			// reorder called by QuestionList view when sortable drops occur
			this.on( 'reorder', this.update_order );

			// when a choice is added or removed, update order
			this.on( 'add', this.update_order );
			this.on( 'remove', this.update_order );

			// when a choice is added or remove, ensure min/max correct answers exist
			this.on( 'add', this.update_correct );
			this.on( 'remove', this.update_correct );

			// when a choice is toggled, ensure min/max correct exist
			this.on( 'correct-update', this.update_correct );

		},

		/**
		 * Retrieve the number of correct choices in the collection
		 * @return   int
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		count_correct: function() {

			return _.size( this.get_correct() );

		},

		/**
		 * Retrieve the collection reduced to only correct choices
		 * @return   obj
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		get_correct: function() {
			return this.filter( function( choice ) {
				return choice.get( 'correct' );
			} );
		},

		/**
		 * Ensure min/max correct choices exist in the collection based on the question's settings
		 * @param    obj      choice  model of the choice that was toggled
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		update_correct: function( choice ) {

			if ( ! this.parent.get( 'question_type' ).get_choice_selectable() ) {
				return;
			}

			var siblings = this.without( choice ), // exclude the toggled choice from loops
				question = this.parent;

			// if multiple choices aren't enabled turn all other choices to incorrect
			if ( 'no' === question.get( 'multi_choices' ) ) {
				_.each( siblings, function( model ) {
					model.set( 'correct', false );
				} );
			}

			// if we don't have a single corret answer & the question has points, set one
			// allows users to create quizzes / questions with no points and therefore no correct answers are allowed
			if ( 0 === this.count_correct() && question.get( 'points' ) > 0 ) {
				var models = 1 === this.size() ? this.models : siblings;
				_.first( models ).set( 'correct', true );
			}

		},

		/**
		 * Update the marker attr of each choice in the list to reflect the order of the collection
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		update_order: function() {

			var self = this,
				question = this.parent;

			this.each( function( choice ) {
				choice.set( 'marker', question.get( 'question_type' ).get_choice_markers()[ self.indexOf( choice ) ] );
			} );

		},

	} );

} );

/**
 * Quiz Question Type
 * @since    3.16.0
 * @version  3.16.0
 */
define( 'Models/QuestionType',[], function() {

	return Backbone.Model.extend( {

		/**
		 * Get model default attributes
		 * @return   obj
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		defaults: function() {
			return {
				choices: false,
				clarifications: true,
				default_choices: [],
				description: true,
				icon: 'question',
				id: 'generic',
				image: true,
				keywords: [],
				name: 'Generic',
				placeholder: '',
				points: true,
				video: true,
			}
		},

		/**
		 * Retrieve an array of keywords for the question type
		 * Used for filtering questions by search term in the quiz builder
		 * @return   array
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		get_keywords: function() {

			var name = this.get( 'name' ),
				words = [ name ];

			return words.concat( this.get( 'keywords' ) ).concat( name.split( ' ' ) );

		},

		/**
		 * Get marker array for the question choices
		 * @return   array
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		get_choice_markers: function() {

			return this._get_choice_option( 'markers' );

		},

		/**
		 * Determine if the question's choices are selectable
		 * @return   bool
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		get_choice_selectable: function() {

			return this._get_choice_option( 'selectable' );

		},

		/**
		 * Get the choice type (text,image)
		 * @return   string
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		get_choice_type: function() {

			return this._get_choice_option( 'type' );

		},

		/**
		 * Retrieve defined min. choices
		 * @return   int
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		get_min_choices: function() {

			return this._get_choice_option( 'min' );

		},

		/**
		 * Get type-defined max choices
		 * @return   string
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		get_max_choices: function() {

			return this._get_choice_option( 'max' );

		},

		/**
		 * Determine if multi-choice selection is enabled
		 * @return   bool
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		get_multi_choices: function() {

			var choices = this.get( 'choices' );

			if ( ! choices  ) {
				return false;
			}

			return this._get_choice_option( 'multi' );

		},

		/**
		 * Retrieve data from the type's "choices" attribute
		 * Allows quick handling of types with no choice definitions w/o additional checks
		 * @param    string   option  name of the choice option to retrieve
		 * @return   mixed
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		_get_choice_option: function( option ) {

			var choices = this.get( 'choices' );

			if ( ! choices || ! choices[ option ] ) {
				return false;
			}

			return choices[ option ];

		},

	} );

} );

/**
 * Utility functions for Models
 * @since    3.16.0
 * @version  3.17.1
 */
define( 'Models/_Utilities',[], function() {

	return {

		fields: [],

		/**
		 * Retrieve the edit post link for the current model
		 * @return   string
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		get_edit_post_link: function() {

			if ( this.has_temp_id() ) {
				return '';
			}

			return window.llms_builder.admin_url + 'post.php?post=' + this.get( 'id' ) + '&action=edit';

		},

		/**
		 * Retrieve schema fields defined for the model
		 * @return   object
		 * @since    3.17.0
		 * @version  3.17.1
		 */
		get_settings_fields: function() {

			var schema = this.schema || {};
			return window.llms_builder.schemas.get( schema, this.get( 'type' ).replace( 'llms_', '' ), this );

		},

		/**
		 * Determine if the model has a temporary ID
		 * @return   {Boolean}
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		has_temp_id: function() {

			return ( ! _.isNumber( this.get( 'id' ) ) && 0 === this.get( 'id' ).indexOf( 'temp_' ) );

		},

		/**
		 * Initializes 3rd party custom schema (field) data for a model
		 * @return   void
		 * @since    3.17.0
		 * @version  3.17.0
		 */
		init_custom_schema: function() {

			var groups = _.filter( this.get_settings_fields(), function( group ) {
				return ( group.custom );
			} );

			_.each( groups, function( group ) {
				_.each( _.flatten(  group.fields ), function( field ) {


					var keys = [ field.attribute ],
						customs = this.get( 'custom' );

					if ( field.switch_attribute ) {
						keys.push( field.switch_attribute );
					}

					_.each( keys, function( key ) {
						var attr = field.attribute_prefix ? field.attribute_prefix + key : key;
						if ( customs && customs[ attr ] ) {
							this.set( key, customs[ attr ][0] );
						}
					}, this );

				}, this );
			}, this );

		},

	};

} );

/**
 * Quiz Question
 * @since    3.16.0
 * @version  3.27.0
 */
define( 'Models/Question',[
		'Models/Image',
		'Collections/Questions',
		'Collections/QuestionChoices',
		'Models/QuestionType',
		'Models/_Relationships',
		'Models/_Utilities'
	], function(
		Image,
		Questions,
		QuestionChoices,
		QuestionType,
		Relationships,
		Utilities
	) {

	return Backbone.Model.extend( _.defaults( {

		/**
		 * Model relationships
		 * @type  {Object}
		 */
		relationships: {
			parent: {
				model: 'llms_quiz',
				type: 'model',
			},
			children: {
				choices: {
					class: 'QuestionChoices',
					model: 'choice',
					type: 'collection',
				},
				image: {
					class: 'Image',
					model: 'image',
					type: 'model',
				},
				questions: {
					class: 'Questions',
					conditional: function( model ) {
						var type = model.get( 'question_type' ),
							type_id = _.isString( type ) ? type : type.get( 'id' );
						return ( 'group' === type_id );
					},
					model: 'llms_question',
					type: 'collection',
				},
				question_type: {
					class: 'QuestionType',
					lookup: function( val ) {
						if ( _.isString( val ) ) {
							return window.llms_builder.questions.get( val );
						}
						return val;
					},
					model: 'question_type',
					type: 'model',
				},
			}
		},

		/**
		 * Model defaults
		 * @return   obj
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		defaults: function() {
			return {
				id: _.uniqueId( 'temp_' ),
				choices: [],
				content: '',
				description_enabled: 'no',
				image: {},
				multi_choices: 'no',
				menu_order: 1,
				points: 1,
				question_type: 'generic',
				questions: [], // for question groups
				parent_id: '',
				title: '',
				type: 'llms_question',
				video_enabled: 'no',
				video_src: '',

				_expanded: false,
			}
		},

		/**
		 * Initializer
		 * @param    obj   data     object of data for the model
		 * @param    obj   options  additional options
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		initialize: function( data, options ) {

			var self = this;

			this.startTracking();
			this.init_relationships( options );

			if ( false !== this.get( 'question_type' ).choices ) {

				this._ensure_min_choices();

				// when a choice is removed, maybe add back some defaults so we always have the minimum
				this.listenTo( this.get( 'choices' ), 'remove', function() {
					// new itmes are added at index 0 when there's only 1 item in the collection, not sure why exactly...
					setTimeout( function() {
						self._ensure_min_choices();
					}, 0 );
				} );

			}

			// ensure question types that don't support points don't record default 1 point in database
			if ( ! this.get( 'question_type' ).get( 'points' ) ) {
				this.set( 'points', 0 );
			}

			_.delay( function( self ) {
				self.on( 'change:points', self.get_parent().update_points, self.get_parent() );
			}, 1, this );

		},

		/**
		 * Add a new question choice
		 * @param    obj   data     object of choice data
		 * @param    obj   options  additional options
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		add_choice: function( data, options ) {

			var max = this.get( 'question_type' ).get_max_choices();
			if ( this.get( 'choices' ).size() >= max ) {
				return;
			}

			data = data || {};
			options = options || {};

			data.choice_type = this.get( 'question_type' ).get_choice_type();
			data.question_id = this.get( 'id' );
			options.parent = this;

			var choice = this.get( 'choices' ).add( data, options );

			Backbone.pubSub.trigger( 'question-add-choice', choice, this );

		},

		/**
		 * Collapse question_type attribute during full syncs to save to database
		 * Not needed because question types cannot be adjusted after question creation
		 * Called from sync controller
		 * @param    obj      atts       flat object of attributes to be saved to db
		 * @param    string   sync_type  full or partial
		 *                                 full indicates a force resync or that the model isn't persisted yet
		 * @return   obj
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		before_save: function( atts, sync_type  ) {
			if ( 'full' === sync_type ) {
				atts.question_type = this.get( 'question_type' ).get( 'id' );
			}
			return atts;
		},

		/**
		 * Retrieve the model's parent (if set)
		 * @return   obj|false
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		get_parent: function() {

			var rels = this.get_relationships();

			if ( rels.parent ) {
				if ( this.collection && this.collection.parent ) {
					return this.collection.parent;
				} else if ( rels.parent.reference ) {
					return rels.parent.reference;
				}
			}

			return false;

		},

		/**
		 * Retrieve the translated post type name for the model's type
		 * @param    bool     plural  if true, returns the plural, otherwise returns singular
		 * @return   string
		 * @since    3.27.0
		 * @version  3.27.0
		 */
		get_l10n_type: function( plural ) {

			if ( plural ) {
				return LLMS.l10n.translate( 'questions' );
			}

			return LLMS.l10n.translate( 'question' );
		},

		/**
		 * Gets the index of the question within it's parent
		 * Question numbers skip content elements
		 * & content elements skip questions
		 * @return   int
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		get_type_index: function() {

			// current models type, used to check the predicate in the filter function below
			var curr_type = this.get( 'question_type' ).get( 'id' ),
				questions;

			questions = this.collection.filter( function( question ) {

				var type = question.get( 'question_type' ).get( 'id' );

				// if current model is not content, return all non-content questions
				if ( curr_type !== 'content' ) {
					return ( 'content' !== type );
				}

				// current model is content, return only content questions
				return 'content' === type;

			} );

			return questions.indexOf( this );

		},

		/**
		 * Gets iterator for the given type
		 * Questions use numbers and content uses alphabet
		 * @return   mixed
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		get_type_iterator: function() {

			var index = this.get_type_index();

			if ( -1 === index ) {
				return '';
			}

			if ( 'content' === this.get( 'question_type' ).get( 'id' ) ) {
				var alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.split( '' );
				return alphabet[ index ];
			}

			return index + 1;

		},


		get_qid: function() {

			var parent = this.get_parent_question(),
				prefix = '';

			if ( parent ) {

				prefix = parent.get_qid() + '.';

			}

			// return short_id + this.get_type_iterator();
			return prefix + this.get_type_iterator();

		},

		/**
		 * Retrieve the parent question (if the question is in a question group)
		 * @return   obj|false
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		get_parent_question: function() {

			if ( this.is_in_group() ) {

				return this.collection.parent;

			}

			return false;

		},

		/**
		 * Retrieve the parent quiz
		 * @return   obj
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		get_parent_quiz: function() {
			return this.get_parent();
		},

		/**
		 * Points getter
		 * ensures that 0 is always returned if the question type doesn't support points
		 * @return   int
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		get_points: function() {

			if ( ! this.get( 'question_type' ).get( 'points' ) ) {
				return 0;
			}

			return this.get( 'points' );

		},

		/**
		 * Retrieve the questions percentage value within the quiz
		 * @return   string
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		get_points_percentage: function() {

			var total = this.get_parent().get( '_points' ),
				points = this.get( 'points' );

			if ( 0 === total ) {
				return '0%';
			}

			return ( ( points / total ) * 100 ).toFixed( 2 ) + '%';

		},

		/**
		 * Deterine if the question belongs to a question group
		 * @return   {Boolean}
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		is_in_group: function() {

			return ( 'question' === this.collection.parent.get( 'type' ) );

		},

		_ensure_min_choices: function() {

			var choices = this.get( 'choices' );
			while ( choices.size() < this.get( 'question_type' ).get_min_choices() ) {
				this.add_choice();
			}

		},

	}, Relationships, Utilities ) );

} );

/**
 * Questions Collection
 * @since    3.16.0
 * @version  3.16.0
 */
define( 'Collections/Questions',[ 'Models/Question' ], function( model ) {

	return Backbone.Collection.extend( {

		/**
		 * Model for collection items
		 * @type  obj
		 */
		model: model,

		/**
		 * Initialize
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		initialize: function() {

			// reorder called by QuestionList view when sortable drops occur
			this.on( 'reorder', this.update_order );

			// when a question is added or removed, update order
			this.on( 'add', this.update_order );
			this.on( 'remove', this.update_order );

			this.on( 'add', this.update_parent );

		},

		/**
		 * Update the order attr of each question in the list to reflect the order of the collection
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		update_order: function() {

			var self = this;

			this.each( function( question ) {

				question.set( 'menu_order', self.indexOf( question ) + 1 );

			} );

		},

		/**
		 * When adding a question to a question list, update the question's parent
		 * Will ensure that questions moved into and out of groups always have the corerct parent_id
		 * @param    obj   model  instance of the question model
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		update_parent: function( model ) {

			model.set( 'parent_id', this.parent.get( 'id' ) );

		},

	} );

} );

/**
 * Quiz Schema
 * @since    3.17.6
 * @version  3.24.0
 */
define( 'Schemas/Quiz',[], function() {

	return window.llms.hooks.applyFilters( 'llms_define_quiz_schema', {

		default: {
			title: LLMS.l10n.translate( 'General Settings' ),
			toggleable: true,
			fields: [
				[
					{
						attribute: 'permalink',
						id: 'permalink',
						type: 'permalink',
					},
				], [
					{
						attribute: 'content',
						id: 'description',
						label: LLMS.l10n.translate( 'Description' ),
						type: 'editor',
					},
				], [
					{
						attribute: 'passing_percent',
						id: 'passing-percent',
						label: LLMS.l10n.translate( 'Passing Percentage' ),
						min: 0,
						max: 100,
						tip: LLMS.l10n.translate( 'Minimum percentage of total points required to pass the quiz' ),
						type: 'number',
					},
					{
						attribute: 'allowed_attempts',
						id: 'allowed-attempts',
						label: LLMS.l10n.translate( 'Limit Attempts' ),
						switch_attribute: 'limit_attempts',
						tip: LLMS.l10n.translate( 'Limit the maximum number of times a student can take this quiz' ),
						type: 'switch-number',
					},
					{
						attribute: 'time_limit',
						id: 'time-limit',
						label: LLMS.l10n.translate( 'Time Limit' ),
						min: 1,
						max: 360,
						switch_attribute: 'limit_time',
						tip: LLMS.l10n.translate( 'Enforce a maximum number of minutes a student can spend on each attempt' ),
						type: 'switch-number',
					},
				], [
					{
						attribute: 'show_correct_answer',
						id: 'show-correct-answer',
						label: LLMS.l10n.translate( 'Show Correct Answers' ),
						tip: LLMS.l10n.translate( 'When enabled, students will be shown the correct answer to any question they answered incorrectly.' ),
						type: 'switch',
					},
					{
						attribute: 'random_questions',
						id: 'random-questions',
						label: LLMS.l10n.translate( 'Randomize Question Order' ),
						tip: LLMS.l10n.translate( 'Display questions in a random order for each attempt. Content questions are locked into their defined positions.' ),
						type: 'switch',
					},
				],

			],
		},

	} );

} );

/**
 * Quiz Model
 * @since    3.16.0
 * @version  3.24.0
 */
define( 'Models/Quiz',[
		'Collections/Questions',
		'Models/Lesson',
		'Models/Question',
		'Models/_Relationships',
		'Models/_Utilities',
		'Schemas/Quiz',
	], function(
		Questions,
		Lesson,
		Question,
		Relationships,
		Utilities,
		QuizSchema
	) {

	return Backbone.Model.extend( _.defaults( {

		/**
		 * model relationships
		 * @type  {Object}
		 */
		relationships: {
			parent: {
				model: 'lesson',
				type: 'model',
			},
			children: {
				questions: {
					class: 'Questions',
					model: 'llms_question',
					type: 'collection',
				},
			}
		},

		/**
		 * Lesson Settings Schema
		 * @type  {Object}
		 */
		schema: QuizSchema,

		/**
		 * New lesson defaults
		 * @return   obj
		 * @since    3.16.0
		 * @version  3.16.6
		 */
		defaults: function() {

			return {
				id: _.uniqueId( 'temp_' ),
				title: LLMS.l10n.translate( 'New Quiz' ),
				type: 'llms_quiz',
				lesson_id: '',

				status: 'draft',

				// editable fields
				content: '',
				allowed_attempts: 5,
				limit_attempts: 'no',
				limit_time: 'no',
				passing_percent: 65,
				name: '',
				random_answers: 'no',
				time_limit: 30,
				show_correct_answer: 'no',

				questions: [],

				// calculated
				_points: 0,

				// display
				permalink: '',
				_show_settings: false,
				_questions_loaded: false,
			};

		},

		/**
		 * Initializer
		 * @return   void
		 * @since    3.16.0
		 * @version  3.24.0
		 */
		initialize: function() {

			this.init_custom_schema();
			this.startTracking();
			this.init_relationships();

			this.listenTo( this.get( 'questions' ), 'add', this.update_points );
			this.listenTo( this.get( 'questions' ), 'remove', this.update_points );

			this.set( '_points', this.get_total_points() );

			// when a quiz is published, ensure the parent lesson is marked as "Enabled" for quizzing
			this.on( 'change:status', function() {
				if ( 'publish' === this.get( 'status' ) ) {
					this.get_parent().set( 'quiz_enabled', 'yes' );
				}
			} );

			window.llms.hooks.doAction( 'llms_quiz_model_init', this );

		},

		/**
		 * Add a new question to the quiz
		 * @param    obj   data   question data
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		add_question: function( data ) {

			data.parent_id = this.get( 'id' );
			var question = this.get( 'questions' ).add( data, {
				parent: this,
			} );
			Backbone.pubSub.trigger( 'quiz-add-question', question, this );

		},

		/**
		 * Retrieve the translated post type name for the model's type
		 * @param    bool     plural  if true, returns the plural, otherwise returns singular
		 * @return   string
		 * @since    3.16.12
		 * @version  3.16.12
		 */
		get_l10n_type: function( plural ) {

			if ( plural ) {
				return LLMS.l10n.translate( 'quizzes' );
			}

			return LLMS.l10n.translate( 'quiz' );
		},

		/**
		 * Retrieve the quiz's total points
		 * @return   int
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		get_total_points: function() {

			var points = 0;

			this.get( 'questions' ).each( function( question ) {
				points += question.get_points();
			} );

			return points;

		},

		/**
		 * Lazy load questions via AJAX
		 * @param    {Function}  cb  callback function
		 * @return   void
		 * @since    3.19.2
		 * @version  3.19.2
		 */
		load_questions: function( cb ) {

			if ( this.get( '_questions_loaded' ) ) {

				cb();

			} else {

				var self = this;

				LLMS.Ajax.call( {
					data: {
						action: 'llms_builder',
						action_type: 'lazy_load',
						course_id: window.llms_builder.CourseModel.get( 'id' ),
						load_id: this.get( 'id' ),
					},
					error: function( xhr, status, error ) {

						console.log( xhr, status, error );
						window.llms_builder.debug.log( '==== start load_questions error ====', xhr, status, error, '==== finish load_questions error ====' );
						cb( true );

					},
					success: function( res ) {
						if ( res && res.questions ) {
							self.set( '_questions_loaded', true );
							if ( res.questions ) {
								_.each( res.questions, self.add_question, self );
							}
							cb();
						} else {
							cb( true );
						}
					}

				} );

			}


		},

		/**
		 * Update total number of points calculated property
		 * @return   int
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		update_points: function() {

			this.set( '_points', this.get_total_points() );

		},

	}, Relationships, Utilities ) );

} );

/**
 * Lesson Schemas
 * @since    3.17.0
 * @version  3.25.4
 */
define( 'Schemas/Lesson',[], function() {

	return window.llms.hooks.applyFilters( 'llms_define_lesson_schema', {

		default: {
			title: LLMS.l10n.translate( 'General Settings' ),
			toggleable: true,
			fields: [
				[
					{
						attribute: 'permalink',
						id: 'permalink',
						type: 'permalink',
					},
				], [
					{
						attribute: 'video_embed',
						id: 'video-embed',
						label: LLMS.l10n.translate( 'Video Embed URL' ),
						type: 'video_embed',
					},
					{
						attribute: 'audio_embed',
						id: 'audio-embed',
						label: LLMS.l10n.translate( 'Audio Embed URL' ),
						type: 'audio_embed',
					},
				], [
					{
						attribute: 'free_lesson',
						id: 'free-lesson',
						label: LLMS.l10n.translate( 'Free Lesson' ),
						tip: LLMS.l10n.translate( "Free lessons can be accessed without enrollment." ),
						type: 'switch',
					},
					{
						attribute: 'require_passing_grade',
						id: 'require-passing-grade',
						label: LLMS.l10n.translate( 'Require Passing Grade on Quiz' ),
						tip: LLMS.l10n.translate( "When enabled, students must pass this lesson's quiz before the lesson can be completed." ),
						type: 'switch',
						condition: function() {
							return ( 'yes' === this.get( 'quiz_enabled' ) );
						},
					},
					{
						attribute: 'require_assignment_passing_grade',
						id: 'require-assignment-passing-grade',
						label: LLMS.l10n.translate( 'Require Passing Grade on Assignment' ),
						tip: LLMS.l10n.translate( "When enabled, students must pass this lesson's assignment before the lesson can be completed." ),
						type: 'switch',
						condition: function() {
							return ( 'undefined' !== window.llms_builder.assignments && 'yes' === this.get( 'assignment_enabled' ) );
						},
					},
					{
						attribute: 'points',
						id: 'points',
						label: LLMS.l10n.translate( 'Lesson Weight' ),
						label_after: LLMS.l10n.translate( 'POINTS' ),
						min: 0,
						max: 99,
						tip: LLMS.l10n.translate( 'Determines the weight of the lesson when calculating the overall grade of the course.' ),
						tip_position: 'top-left',
						type: 'number',
						condition: function() {
							return ( ( 'yes' === this.get( 'quiz_enabled' ) ) || ( 'undefined' !== window.llms_builder.assignments && 'yes' === this.get( 'assignment_enabled' ) ) );
						},
					},
				], [
					{
						attribute: 'prerequisite',
						condition: function() {
							return ( false === this.is_first_in_course() );
						},
						id: 'prerequisite',
						label: LLMS.l10n.translate( 'Prerequisite' ),
						switch_attribute: 'has_prerequisite',
						type: 'switch-select',
						options: function() {
							return this.get_available_prereq_options();
						},
					},
				], [
					{
						attribute: 'drip_method',
						id: 'drip-method',
						label: LLMS.l10n.translate( 'Drip Method' ),
						switch_attribute: 'drip_method',
						type: 'select',
						options: function() {

							var options = [
								{
									key: '',
									val: LLMS.l10n.translate( 'None' ),
								},
								{
									key: 'date',
									val: LLMS.l10n.translate( 'On a specific date' ),
								},
								{
									key: 'enrollment',
									val: LLMS.l10n.translate( '# of days after course enrollment' ),
								},
							];

							if ( this.get_course() && this.get_course().get( 'start_date' ) ) {
								options.push( {
									key: 'start',
									val: LLMS.l10n.translate( '# of days after course start date' ),
								} );
							}

							if ( 'yes' === this.get( 'has_prerequisite' ) ) {
								options.push( {
									key: 'prerequisite',
									val: LLMS.l10n.translate( '# of days after prerequisite lesson completion' ),
								} );
							}

							return options;

						},
					},
					{
						attribute: 'days_before_available',
						condition: function() {
							return ( -1 !== [ 'enrollment', 'start', 'prerequisite' ].indexOf( this.get( 'drip_method' ) ) );
						},
						id: 'days-before-available',
						label: LLMS.l10n.translate( '# of days' ),
						min: 0,
						type: 'number',
					},
					{
						attribute: 'date_available',
						date_format: 'Y-m-d',
						condition: function() {
							return ( 'date' === this.get( 'drip_method' ) );
						},
						id: 'date-available',
						label: LLMS.l10n.translate( 'Date' ),
						timepicker: 'false',
						type: 'datepicker',
					},
					{
						attribute: 'time_available',
						condition: function() {
							return ( 'date' === this.get( 'drip_method' ) );
						},
						datepicker: 'false',
						date_format: 'h:i A',
						id: 'time-available',
						label: LLMS.l10n.translate( 'Time' ),
						type: 'datepicker',
					},
				],
			],
		},

	} );

} );

/**
 * Lesson Model
 * @since    3.13.0
 * @version  3.27.0
 */
define( 'Models/Lesson',[ 'Models/Quiz', 'Models/_Relationships', 'Models/_Utilities', 'Schemas/Lesson' ], function( Quiz, Relationships, Utilities, LessonSchema ) {

	return Backbone.Model.extend( _.defaults( {

		/**
		 * Model relationships
		 * @type  {Object}
		 */
		relationships: {
			parents: {
				model: 'section',
				type: 'model',
			},
			children: {
				quiz: {
					class: 'Quiz',
					conditional: function( model ) {
						// if quiz is enabled OR not enabled but we have some quiz data as an obj
						return ( 'yes' === model.get( 'quiz_enabled' ) || ! _.isEmpty( model.get( 'quiz' ) ) );
					},
					model: 'llms_quiz',
					type: 'model',
				},
			},
		},

		/**
		 * Lesson Settings Schema
		 * @type  {Object}
		 */
		schema: LessonSchema,

		/**
		 * New lesson defaults
		 * @return   obj
		 * @since    3.13.0
		 * @version  3.24.0
		 */
		defaults: function() {
			return {
				id: _.uniqueId( 'temp_' ),
				title: LLMS.l10n.translate( 'New Lesson' ),
				type: 'lesson',
				order: this.collection ? this.collection.length + 1 : 1,
				parent_course: window.llms_builder.course.id,
				parent_section: '',

				// urls
				edit_url: '',
				view_url: '',

				// editable fields
				content: '',
				audio_embed: '',
				has_prerequisite: 'no',
				require_passing_grade: 'yes',
				require_assignment_passing_grade: 'yes',
				video_embed: '',
				free_lesson: '',
				points: 1,

				// other fields
				assignment: {}, // assignment model/data
				assignment_enabled: 'no',

				quiz: {}, // quiz model/data
				quiz_enabled: 'no',

				_forceSync: false,

			};
		},

		/**
		 * Initializer
		 * @return   void
		 * @since    3.16.0
		 * @version  3.17.0
		 */
		initialize: function() {

			this.init_custom_schema();
			this.startTracking();
			this.maybe_init_assignments();
			this.init_relationships();

			// if the lesson ID isn't set on a quiz, set it
			var quiz = this.get( 'quiz' );
			if ( ! _.isEmpty( quiz ) && ! quiz.get( 'lesson_id' ) ) {
				quiz.set( 'lesson_id', this.get( 'id' ) );
			}

			window.llms.hooks.doAction( 'llms_lesson_model_init', this );

		},

		/**
		 * Retrieve a reference to the parent course of the lesson
		 * @return   obj
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		get_course: function() {
			return this.get_parent().get_parent();
		},

		/**
		 * Retrieve the translated post type name for the model's type
		 * @param    bool     plural  if true, returns the plural, otherwise returns singular
		 * @return   string
		 * @since    3.16.12
		 * @version  3.16.12
		 */
		get_l10n_type: function( plural ) {

			if ( plural ) {
				return LLMS.l10n.translate( 'lessons' );
			}

			return LLMS.l10n.translate( 'lesson' );
		},

		/**
		 * Override default get_parent to grab from collection if models parent isn't set
		 * @return   obj
		 * @since    3.17.0
		 * @version  3.17.0
		 */
		get_parent: function() {

			var rels = this.get_relationships();
			if ( rels.parent && rels.parent.reference ) {
				return rels.parent.reference;
			} else if ( this.collection && this.collection.parent ) {
				return this.collection.parent;
			}
			return false;

		},

		/**
		 * Retrieve the questions percentage value within the quiz
		 * @return   string
		 * @since    3.24.0
		 * @version  3.24.0
		 */
		get_points_percentage: function() {

			var total = this.get_course().get_total_points(),
				points = this.get( 'points' ) * 1;

			if ( ! _.isNumber( points ) ) {
				points = 0;
			}

			if ( 0 === total ) {
				return '0%';
			}

			return ( ( points / total ) * 100 ).toFixed( 2 ) + '%';

		},

		/**
		 * Retrieve an array of prerequisite options available for the current lesson
		 * @return   obj
		 * @since    3.17.0
		 * @version  3.17.0
		 */
		get_available_prereq_options: function() {

			var parent_section_index = this.get_parent().collection.indexOf( this.get_parent() ),
				lesson_index_in_section = this.collection.indexOf( this ),
				options = [];

			this.get_course().get( 'sections' ).each( function( section, curr_sec_index ) {
				if ( curr_sec_index <= parent_section_index ) {
					var group = {
							/* translators: %1$d = section order number, %2$s = section title */
							label: LLMS.l10n.replace( 'Section %1$d: %2$s', {
								'%1$d': section.get( 'order' ),
								'%2$s': section.get( 'title' )
							} ),
							options: [],
						};

					section.get( 'lessons' ).each( function( lesson, curr_les_index ) {
						if ( curr_sec_index !== parent_section_index || curr_les_index < lesson_index_in_section ) {
							/* translators: %1$d = lesson order number, %2$s = lesson title */
							group.options.push( {
								key: lesson.get( 'id' ),
								val: LLMS.l10n.replace( 'Lesson %1$d: %2$s', {
									'%1$d': lesson.get( 'order' ),
									'%2$s': lesson.get( 'title' )
								} ),
							} );
						}
					}, this );

					options.push( group );
				}
			}, this );

			return options;

		},

		/**
		 * Add a new quiz to the lesson
		 * @param    obj   data   object of quiz data used to construct a new quiz model
		 * @return   obj          model for the created quiz
		 * @since    3.16.0
		 * @version  3.27.0
		 */
		add_quiz: function( data ) {

			data = data || {};

			data.lesson_id = this.id;
			data._questions_loaded = true;

			if ( ! data.title ) {

				data.title = LLMS.l10n.replace( '%1$s Quiz', {
					'%1$s': this.get( 'title' ),
				} );

			}

			this.set( 'quiz', data );
			this.init_relationships();

			var quiz = this.get( 'quiz' );
			this.set( 'quiz_enabled', 'yes' );

			window.llms.hooks.doAction( 'llms_lesson_add_quiz', quiz, this );

			return quiz;

		},

		/**
		 * Determine if this is the first lesson
		 * @return   {Boolean}
		 * @since    3.17.0
		 * @version  3.17.0
		 */
		is_first_in_course: function() {

			// if it's not the first item in the section it cant be the first lesson
			if ( this.collection.indexOf( this ) ) {
				return false;
			}

			// if it's not the first section it cant' be first lesson
			var section = this.get_parent();
			if ( section.collection.indexOf( section ) ) {
				return false;
			}

			// it's first lesson in first section
			return true;

		},

		/**
		 * Initialize lesson assignments *if* the assignments addon is availalbe and enabled
		 * @return   void
		 * @since    3.17.0
		 * @version  3.17.0
		 */
		maybe_init_assignments: function() {

			if ( ! window.llms_builder.assignments ) {
				return;
			}

			this.relationships.children.assignment = {
				class: 'Assignment',
				conditional: function( model ) {
					// if assignment is enabled OR not enabled but we have some assignment data as an obj
					return ( 'yes' === model.get( 'assignment_enabled' ) || ! _.isEmpty( model.get( 'assignment' ) ) );
				},
				model: 'llms_assignment',
				type: 'model',
			};

		},

	}, Relationships, Utilities ) );

} );

/**
 * Lessons Collection
 * @since    3.13.0
 * @version  3.17.0
 */
define( 'Collections/Lessons',[ 'Models/Lesson' ], function( model ) {

	return Backbone.Collection.extend( {

		/**
		 * Model for collection items
		 * @type  obj
		 */
		model: model,

		/**
		 * Initializer
		 * @return   void
		 * @since    3.16.0
		 * @version  3.17.0
		 */
		initialize: function() {

			// reorder called by LessonList view when sortable drops occur
			this.on( 'reorder', this.on_reorder );

			// when a lesson is added or removed, update order
			this.on( 'add', this.on_reorder );
			this.on( 'remove', this.on_reorder );

		},

		/**
		 * On lesson reorder callback
		 *
		 * Update the order attr of each lesson to reflect the new lesson order
		 * Validate prerequisite (if set) and unset it if it's no longer a valid prereq
		 *
		 * @return   void
		 * @since    3.17.0
		 * @version  3.17.0
		 */
		on_reorder: function() {
			this.update_order();
			this.validate_prereqs();
		},

		/**
		 * Update lesson order attribute of all lessons when lessons are reordered
		 * @return      void
		 * @since       3.16.0
		 * @version     3.17.0
		 */
		update_order: function() {

			this.each( function( lesson ) {
				lesson.set( 'order', this.indexOf( lesson ) + 1 );
			}, this );

		},


		/**
		 * Validate prerequisite (if set) and unset it if it's no longer a valid prereq
		 * @return   void
		 * @since    3.17.0
		 * @version  3.17.0
		 */
		validate_prereqs: function() {

			this.each( function( lesson ) {

				// validate prereqs
				if ( 'yes' === lesson.get( 'has_prerequisite' ) ) {
					var valid = _.pluck( _.flatten( _.pluck( lesson.get_available_prereq_options(), 'options' ) ), 'key' );
					if ( -1 === valid.indexOf( lesson.get( 'prerequisite' ) * 1 ) ) {
						lesson.set( {
							prerequisite: 0,
							has_prerequisite: 'no',
						} );
					}
				}

			}, this );

		},

	} );

} );

/**
 * Quiz Question Type Collection
 * @since    3.16.0
 * @version  3.16.0
 */
define( 'Collections/QuestionTypes',[ 'Models/QuestionType' ], function( model ) {

	return Backbone.Collection.extend( {

		/**
		 * Model for collection items
		 * @type  obj
		 */
		model: model,

		/**
		 * Initializer
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		initialize: function() {

			this.on( 'add', this.comparator );
			this.on( 'remove', this.comparator );

		},

		/**
		 * Comparator (sorts collection)
		 * @param    obj   model  QuestionType model
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		comparator: function( model ) {

			return model.get( 'group' ).order;

		},

	} );

} );

/**
 * Section Model
 * @since    3.16.0
 * @version  3.16.12
 */
define( 'Models/Section',[ 'Collections/Lessons', 'Models/_Relationships' ], function( Lessons, Relationships ) {

	return Backbone.Model.extend( _.defaults( {

		relationships: {
			parent: {
				model: 'course',
				type: 'model',
			},
			children: {
				lessons: {
					class: 'Lessons',
					model: 'lesson',
					type: 'collection',
				},
			}
		},

		/**
		 * New section defaults
		 * @return   obj
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		defaults: function() {
			return {
				id: _.uniqueId( 'temp_' ),
				lessons: [],
				order: this.collection ? this.collection.length + 1 : 1,
				parent_course: window.llms_builder.course.id,
				title: LLMS.l10n.translate( 'New Section' ),
				type: 'section',

				_expanded: false,
				_selected: false,
			};
		},

		/**
		 * Initialize
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		initialize: function() {

			this.startTracking();
			this.init_relationships();

		},

		/**
		 * Add a lesson to the section
		 * @param    obj   data     hash of lesson data (creates new lesson)
		 *                          or existing lesson as a Backbone.Model
		 * @param    obj   options  has of options
		 * @return   obj            Backbone.Model of the new/updated lesson
		 * @since    3.16.0
		 * @version  3.16.11
		 */
		add_lesson: function( data, options ) {

			data = data || {};
			options = options || {};

			if ( data instanceof Backbone.Model ) {
				data.set( 'parent_section', this.get( 'id' ) );
				data.set_parent( this );
			} else {
				data.parent_section = this.get( 'id' );
			}

			return this.get( 'lessons' ).add( data, options );

		},

		/**
		 * Retrieve the translated post type name for the model's type
		 * @param    bool     plural  if true, returns the plural, otherwise returns singular
		 * @return   string
		 * @since    3.16.12
		 * @version  3.16.12
		 */
		get_l10n_type: function( plural ) {

			if ( plural ) {
				return LLMS.l10n.translate( 'sections' );
			}

			return LLMS.l10n.translate( 'section' );
		},

		/**
		 * Get next section in the collection
		 * @param    bool     circular   if true handles the collection in a circle
		 *                               	if current is the last section, returns the first section
		 *                               	if current is the first section, returns the last section
		 * @return   obj|false
		 * @since    3.16.11
		 * @version  3.16.11
		 */
		get_next: function( circular ) {
			return this._get_sibling( 'next', circular );
		},

		/**
		 * Get prev section in the collection
		 * @param    bool     circular   if true handles the collection in a circle
		 *                               	if current is the last section, returns the first section
		 *                               	if current is the first section, returns the last section
		 * @return   obj|false
		 * @since    3.16.11
		 * @version  3.16.11
		 */
		get_prev: function( circular ) {
			return this._get_sibling( 'prev', circular );
		},

		/**
		 * Get a sibling section
		 * @param    string   direction  siblings direction [next|prev]
		 * @param    bool     circular   if true handles the collection in a circle
		 *                               	if current is the last section, returns the first section
		 *                               	if current is the first section, returns the last section
		 * @return   obj|false
		 * @since    3.16.11
		 * @version  3.16.11
		 */
		_get_sibling: function( direction, circular ) {

			circular = ( 'undefined' === circular ) ? true : circular;

			var max = this.collection.size() - 1,
				index = this.collection.indexOf( this ),
				sibling_index;

			if ( 'next' === direction ) {
				sibling_index = index + 1;
			} else if ( 'prev' === direction ) {
				sibling_index = index - 1;
			}

			// dont retrieve greater than max or less than min
			if ( sibling_index <= max || sibling_index <= 0 ) {

				return this.collection.at( sibling_index );

			} else if ( circular ) {

				if ( 'next' === direction ) {
					return this.collection.first();
				} else if ( 'prev' === direction ) {
					return this.collection.last();
				}

			}

			return false;

		},

	}, Relationships ) );

} );

/**
 * Sections Collection
 * @since    3.16.0
 * @version  3.16.0
 */
define( 'Collections/Sections',[ 'Models/Section' ], function( model ) {

	return Backbone.Collection.extend( {

		/**
		 * Model for collection items
		 * @type  obj
		 */
		model: model,

		/**
		 * Initialize
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		initialize: function() {

			var self = this;

			// reorder called by SectionList view when sortable drops occur
			this.on( 'reorder', this.update_order );

			// when a section is added or removed, update order
			this.on( 'add', this.update_order );
			this.on( 'remove', this.update_order );

		},

		/**
		 * Update the order attr of each section in the list to reflect the order of the collection
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		update_order: function() {

			var self = this;

			this.each( function( section ) {

				section.set( 'order', self.indexOf( section ) + 1 );

			} );

		},

	} );

} );

/**
 * Lessons Collection
 * @since    3.13.0
 * @version  3.16.0
 */
define( 'Collections/loader',[
		'Collections/Lessons',
		'Collections/QuestionChoices',
		'Collections/Questions',
		'Collections/QuestionTypes',
		'Collections/Sections'
	], function(
		Lessons,
		QuestionChoices,
		Questions,
		QuestionTypes,
		Sections
	) {

	return {
		Lessons: Lessons,
		QuestionChoices: QuestionChoices,
		Questions: Questions,
		QuestionTypes: QuestionTypes,
		Sections: Sections,
	};

} );

/**
 * Abstract LifterLMS Model
 * @since    3.17.0
 * @version  3.17.0
 */
define( 'Models/Abstract',[ 'Models/_Relationships', 'Models/_Utilities' ], function( Relationships, Utilities ) {

	return Backbone.Model.extend( _.defaults( {}, Relationships, Utilities ) );

} );

/**
 * Course Model
 * @since    3.16.0
 * @version  3.24.0
 */
define( 'Models/Course',[ 'Collections/Sections', 'Models/_Relationships', 'Models/_Utilities' ], function( Sections, Relationships, Utilities ) {

	return Backbone.Model.extend( _.defaults( {

		relationships: {
			children: {
				sections: {
					class: 'Sections',
					model: 'section',
					type: 'collection',
				},
			}
		},

		/**
		 * New Course Defaults
		 * @return   obj
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		defaults: function() {
			return {
				edit_url: '',
				sections: [],
				title: 'New Course',
				type: 'course',
				view_url: '',
			}
		},

		/**
		 * Init
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		initialize: function() {

			this.startTracking();
			this.init_relationships();

			// Sidebar "New Section" button broadcast
			Backbone.pubSub.on( 'add-new-section', this.add_section, this );

			// Sidebar "New Lesson" button broadcast
			Backbone.pubSub.on( 'add-new-lesson', this.add_lesson, this );

			Backbone.pubSub.on( 'lesson-search-select', this.add_existing_lesson, this );

		},

		/**
		 * Add an existing lesson to the course
		 * Duplicate a lesson from this or another course or attach an orphaned lesson
		 * @param    obj   lesson  lesson data obj
		 * @return   void
		 * @since    3.16.0
		 * @version  3.24.0
		 */
		add_existing_lesson: function( lesson ) {

			var data = lesson.data;

			if ( 'clone' === lesson.action ) {

				delete data.id;

				// if a quiz is attached, duplicate the quiz also
				if ( data.quiz ) {
					data.quiz = _.prepareQuizObjectForCloning( data.quiz );
					data.quiz._questions_loaded = true;
				}

			} else {

				data._forceSync = true;

			}

			delete data.order;
			delete data.parent_course;
			delete data.parent_section;

			this.add_lesson( data );

		},

		/**
		 * Add a new lesson to the course
		 * @param    obj   data   lesson data
		 * @return   obj          Backbone.Model of the lesson
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		add_lesson: function( data ) {

			data = data || {};
			var options = {},
				section;

			if ( ! data.parent_section ) {
				section = this.get_selected_section();
				if ( ! section ) {
					section = this.get( 'sections' ).last();
				}
			} else {
				section = this.get( 'sections' ).get( data.parent_section );
			}

			data._selected = true;

			data.parent_course = this.get( 'id' );

			var lesson = section.add_lesson( data, options );
			Backbone.pubSub.trigger( 'new-lesson-added', lesson );

			// expand the section
			section.set( '_expanded', true );

			return lesson;

		},

		/**
		 * Add a new section to the course
		 * @param    obj   data   section data
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		add_section: function( data ) {

			data = data || {};
			var sections = this.get( 'sections' ),
				options = {},
				selected = this.get_selected_section();

			// if a section is selected, add the new section after the currently selected one
			if ( selected ) {
				options.at = sections.indexOf( selected ) + 1;
			}

			sections.add( data, options );

		},

		/**
		 * Retrieve the currently selected section in the course
		 * @return   obj|undefined
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		get_selected_section: function() {

			return this.get( 'sections' ).find( function( model ) {
				return model.get( '_selected' );
			} );

		},

		/**
		 * Retrieve the total number of points in the course
		 * @return   int
		 * @since    3.24.0
		 * @version  3.24.0
		 */
		get_total_points: function() {

			var points = 0;

			this.get( 'sections' ).each( function( section ) {
				section.get( 'lessons' ).each( function( lesson ) {
					var lesson_points = lesson.get( 'points' );
					if ( ! _.isNumber( lesson_points ) ) {
						lesson_points = 0;
					}
					points += lesson_points * 1;
				} );
			} );

			return points;

		},

	}, Relationships, Utilities ) );

} );

/**
 * Load all models
 * @return   obj
 * @since    3.16.0
 * @version  3.17.0
 */
define( 'Models/loader',[
		'Models/Abstract',
		'Models/Course',
		'Models/Image',
		'Models/Lesson',
		'Models/Question',
		'Models/QuestionChoice',
		'Models/QuestionType',
		'Models/Quiz',
		'Models/Section'
	],
	function(
		Abstract,
		Course,
		Image,
		Lesson,
		Question,
		QuestionChoice,
		QuestionType,
		Quiz,
		Section
	) {

	return {
		Abstract: Abstract,
		Course: Course,
		Image: Image,
		Lesson: Lesson,
		Question: Question,
		QuestionChoice: QuestionChoice,
		QuestionType: QuestionType,
		Quiz: Quiz,
		Section: Section,
	};

} );

/**
 * Detachable model
 * @type     {Object}
 * @since    3.16.12
 * @version  3.16.12
 */
define( 'Views/_Detachable',[], function() {

	return {

		/**
		 * DOM Events
		 * @type  {Object}
		 * @since    3.16.12
		 * @version  3.16.12
		 */
		events: {
			'click a[href="#llms-detach-model"]': 'detach_model',
		},

		/**
		 * Detaches a model from it's parent (doesn't delete)
		 * @param    obj   event  js event object
		 * @return   void
		 * @since    3.16.12
		 * @version  3.16.12
		 */
		detach_model: function( event ) {

			if ( event ) {
				event.preventDefault();
				event.stopPropagation();
			}

			var msg = LLMS.l10n.replace( 'Are you sure you want to detach this %s?', {
				'%s': this.model.get_l10n_type(),
			} );

			if ( window.confirm( msg ) ) {

				if ( this.model.collection ) {
					this.model.collection.remove( this.model );
				}

				// publish global event
				Backbone.pubSub.trigger( 'model-detached', this.model );

				// trigger local event so extending views can run other actions where necessary
				this.trigger( 'model-trashed', this.model );

			}

		},

	}

} );

/**
 * Handles UX and Events for inline editing of views
 * Use with a Model's View
 * Allows editing model.title field via .llms-editable-title elements
 * @type     {Object}
 * @since    3.16.0
 * @version  3.25.4
 */
define( 'Views/_Editable',[], function() {

	return {

		media_lib: null,

		/**
		 * DOM Events
		 * @type  {Object}
		 * @since    3.16.0
		 * @version  3.17.8
		 */
		events: {
			'click .llms-add-image': 'open_media_lib',
			'click a[href="#llms-edit-slug"]': 'make_slug_editable',
			'click a[href="#llms-remove-image"]': 'remove_image',
			'change .llms-editable-select select': 'on_select',
			'change .llms-switch input[type="checkbox"]': 'toggle_switch',
			'change .llms-editable-radio input': 'on_radio_select',
			'focusin .llms-input': 'on_focus',
			'focusout .llms-input': 'on_blur',
			'keydown .llms-input': 'on_keydown',
			'input .llms-input[type="number"]': 'on_blur',
			'paste .llms-input[data-formatting]': 'on_paste',
		},

		/**
		 * Retrieve a list of allowed tags for a given element
		 * @param    obj   $el  jQuery selector for the element
		 * @return   array
		 * @since    3.16.0
		 * @version  3.17.8
		 */
		get_allowed_tags: function( $el ) {

			if ( $el.attr( 'data-formatting' ) ) {
				return _.map( $el.attr( 'data-formatting' ).split( ',' ), function( tag ) {
					return tag.trim();
				} );
			}

			return [ 'b', 'i', 'u', 'strong', 'em' ];

		},

		/**
		 * Retrieve the content of an element
		 * @param    obj   $el  jQuery object of the element
		 * @return   string
		 * @since    3.16.0
		 * @version  3.17.8
		 */
		get_content: function( $el ) {

			if ( 'INPUT' === $el[0].tagName ) {
				return $el.val();
			}

			if ( ! $el.attr( 'data-formatting' ) && ! $el.hasClass( 'ql-editor' ) ) {
				return $el.text();
			}

			return _.stripFormatting( $el.html(), this.get_allowed_tags( $el ) );

		},

		/**
		 * Determine if changes have been made to the element
		 * @param    {[obj]}   event  js event object
		 * @return   {Boolean}        true when changes have been made, false otherwise
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		has_changed: function( event ) {
			var $el = $( event.target );
			return ( $el.attr( 'data-original-content' ) !== this.get_content( $el ) );
		},

		/**
		 * Ensure that new content is at least 1 character long
		 * @param    obj   event  js event object
		 * @return   boolean
		 * @since    3.16.0
		 * @version  3.17.2
		 */
		is_valid: function( event ) {

			var self = this,
				$el = $( event.target ),
				content = this.get_content( $el ),
				type = $el.attr( 'data-type' );

			if ( ( $el.attr( 'required' ) || $el.attr( 'data-required' ) ) && content.length < 1 ) {
				return false;
			}

			if ( 'url' === type || 'video' === type ) {
				if ( ! this._validate_url( this.get_content( $el ) ) ) {
					return false;
				}

			} else if ( 'permalink' === type ) {

				LLMS.Ajax.call( {
					data: {
						action: 'llms_builder',
						action_type: 'get_permalink',
						course_id: window.llms_builder.CourseModel.get( 'id' ),
						id: self.model.get( 'id' ),
						title: self.model.get( 'title' ),
						slug: content,
					},
					beforeSend: function() {
						LLMS.Spinner.start( $el.closest( '.llms-editable-toggle-group' ), 'small' );
					},
					success: function( r ) {

						if ( r.permalink && r.slug ) {
							self.model.set( 'permalink', r.permalink );
							self.model.set( 'name', r.slug );
							self.render();
						}

					}
				} );

			}

			return true;

		},

		/**
		 * Initialize datepicker elements
		 * @return   void
		 * @since    3.17.0
		 * @version  3.17.0
		 */
		init_datepickers: function() {

			this.$el.find( '.llms-editable-date input' ).each( function() {

				$( this ).datetimepicker( {
					format: $( this ).attr( 'data-date-format' ) || 'Y-m-d h:i A',
					datepicker: ( undefined === $( this ).attr( 'data-date-datepicker' ) ) ? true : ( 'true' == $( this ).attr( 'data-date-datepicker' ) ),
					timepicker: ( undefined === $( this ).attr( 'data-date-timepicker' ) ) ? true : ( 'true' == $( this ).attr( 'data-date-timepicker' ) ),
					onClose: function( current_time, $input ) {
						$input.blur();
					}
				} );

			} );

		},

		/**
		 * Initialize elements that allow inline formatting
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		init_formatting_els: function() {

			var self = this;

			this.$el.find( '.llms-input-formatting[data-formatting]' ).each( function() {

				var formatting = $( this ).attr( 'data-formatting' ).split( ',' ),
					attr = $( this ).attr( 'data-attribute' );

				var ed = new Quill( this, {
					modules: {
						toolbar: [ formatting ],
						keyboard: {
							bindings: {
								tab: {
									key: 9,
									handler: function( range, context ) {
										return true;
									},
								},
								13: {
									key: 13,
									handler: function( range, context ) {
										ed.root.blur();
										return false;
									},
								},
							},
						},
					},
					placeholder: $( this ).attr( 'data-placeholder' ),
					theme: 'bubble',
				} );

				ed.on( 'text-change', function( delta, oldDelta, source ) {
					self.model.set( attr, self.get_content( $( ed.root ) ) );
				} );

				Backbone.pubSub.trigger( 'formatting-ed-init', ed, $( this ), self );

			} );

		},

		/**
		 * Initialize editable select elements
		 * @return   void
		 * @since    3.16.0
		 * @version  3.25.4
		 */
		init_selects: function() {

			this.$el.find( '.llms-editable-select select' ).llmsSelect2( {
				width: '100%',
			} ).trigger( 'change' );

		},

		/**
		 * Blur/focusout function for .llms-editable-title elements
		 * Automatically saves changes if changes have been made
		 * @param    obj   event  js event object
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.6
		 */
		on_blur: function( event ) {

			event.stopPropagation();

			this.model.set( '_has_focus', false, { silent: true } );

			var self = this,
				$el = $( event.target ),
				changed = this.has_changed( event );

			if ( changed ) {

				if ( ! self.is_valid( event ) ) {
					self.revert_edits( event );
				} else {
					this.save_edits( event );
				}

			}

		},

		/**
		 * Focus event for editable inputs
		 * @param    obj   event  js event object
		 * @return   void
		 * @since    3.16.6
		 * @version  3.16.6
		 */
		on_focus: function( event ) {

			event.stopPropagation();
			this.model.set( '_has_focus', true, { silent: true } );

		},

		/**
		 * Handle content pasted into contenteditable fields
		 * This will ensure that HTML from RTF editors isn't pasted into the dom
		 * @param    obj   event  js event obj
		 * @return   void
		 * @since    3.17.8
		 * @version  3.17.8
		 */
		on_paste: function( event ) {

			event.preventDefault();
			event.stopPropagation();

			var text = ( event.originalEvent || event ).clipboardData.getData( 'text/plain' );
			window.document.execCommand( 'insertText', false, text );

		},

		/**
		 * Change event for selectables
		 * @param    obj   event  js event object
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		on_select: function( event ) {

			var $el = $( event.target ),
				multi = ( $el.attr( 'multiple' ) ),
				attr = $el.attr( 'name' ),
				$selected = $el.find( 'option:selected' ),
				val;

			if ( multi ) {
				val = [];
				val = $selected.map( function() {
					return this.value;
				} ).get();
			} else {
				val = $selected[0].value;
			}

			this.model.set( attr, val );

		},

		/**
		 * Change event for radio element groups
		 * @param    obj   event  js event object
		 * @return   void
		 * @since    3.17.6
		 * @version  3.17.6
		 */
		on_radio_select: function( event ) {

			var $el = $( event.target ),
				attr = $el.attr( 'name' ),
				val = $el.val();

			this.model.set( attr, val );

		},

		/**
		 * Keydown function for .llms-editable-title elements
		 * Blurs
		 * @param    {obj}   event  js event object
		 * @return   void
		 * @since    3.16.0
		 * @version  3.17.8
		 */
		on_keydown: function( event ) {

			event.stopPropagation();

			var self = this,
				key = event.which || event.keyCode,
				shift = event.shiftKey;
				// ctrl = event.metaKey || event.ctrlKey;

			switch ( key ) {

				case 13: // enter
					// shift + enter should add a return
					if ( ! shift ) {
						event.preventDefault();
						event.target.blur();
					}
				break;

				case 27: // escape
					event.preventDefault();
					this.revert_edits( event );
					event.target.blur();
				break;

			}

		},

		/**
		 * Open the WP media lib
		 * @param    obj   event  js event object
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.6
		 */
		open_media_lib: function( event ) {

			event.stopPropagation();

			var self = this,
				$el = $( event.currentTarget );

			if ( self.media_lib ) {

				self.media_lib.uploader.uploader.param( 'post_id' );

			} else {

				self.media_lib = wp.media.frames.file_frame = wp.media( {
					title: LLMS.l10n.translate( 'Select an image' ),
					button: {
						text: LLMS.l10n.translate( 'Use this image' ),
					},
					multiple: false	// Set to true to allow multiple files to be selected
				} );

				self.media_lib.on( 'select', function() {

					var size = $el.attr( 'data-image-size' ),
						attachment = self.media_lib.state().get( 'selection' ).first().toJSON(),
						image = self.model.get( $el.attr( 'data-attribute' ) ),
						url;

					if ( size && attachment.sizes[ size ] ) {
						url = attachment.sizes[ size ].url;
					} else {
						url = attachment.url;
					}

					image.set( {
						id: attachment.id,
						src: url,
					} );

				} );

			}

			self.media_lib.open();


		},

		/**
		 * Click event to remove an image
		 * @param    obj   event  js event obj
		 * @return   voids
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		remove_image: function( event ) {

			event.preventDefault();

			this.model.get( $( event.currentTarget ).attr( 'data-attribute' ) ).set( {
				id: '',
				src: '',
			} );

		},

		/**
		 * Helper to undo changes
		 * Bound to "escape" key via on_keydwon function
		 * @param    obj   event  js event object
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		revert_edits: function( event ) {
			var $el = $( event.target ),
				val = $el.attr( 'data-original-content' );
			$el.html( val );
		},

		/**
		 * Sync chages to the model and DB
		 * @param    {obj}   event  js event object
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		save_edits: function( event ) {

			var $el = $( event.target ),
				val = this.get_content( $el );

			this.model.set( $el.attr( 'data-attribute' ), val );

		},

		/**
		 * Change event for a switch element
		 * @param    obj   event  js event object
		 * @return   void
		 * @since    3.16.0
		 * @version  3.17.0
		 */
		toggle_switch: function( event ) {

			event.stopPropagation();
			var $el = $( event.target ),
				attr = $el.attr( 'name' ),
				rerender = $el.attr( 'data-rerender' ),
				val;

			if ( $el.is( ':checked' ) ) {
				val = $el.attr( 'data-on' ) ? $el.attr( 'data-on' ) : 'yes';
			} else {
				val = $el.attr( 'data-off' ) ? $el.attr( 'data-off' ) : 'no';
			}

			if ( -1 !== attr.indexOf( '.' ) ) {

				var split = attr.split( '.' );

				if ( 'parent' === split[0] ) {
					this.model.get_parent().set( split[1], val );
				} else {
					this.model.get( split[0] ).set( split[1], val );
				}


			} else {

				this.model.set( attr, val );

			}

			this.trigger( attr.replace( '.', '-' ) + '_toggle', val );

			if ( ! rerender || 'yes' === rerender ) {
				var self = this;
				setTimeout( function() {
					self.render();
				}, 100 );
			}

		},

		/**
		 * Initializes a WP Editor on a textarea
		 * @param    string   id        CSS ID of the editor (don't include #)
		 * @param    obj      settings  optional object of settings to pass to wp.editor.initialize()
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		init_editor: function( id, settings ) {

			settings = settings || {};

			wp.editor.remove( id );

			wp.editor.initialize( id, $.extend( true, wp.editor.getDefaultSettings(), {
				mediaButtons: true,
				tinymce: {
					toolbar1: 'bold,italic,strikethrough,bullist,numlist,blockquote,hr,alignleft,aligncenter,alignright,link,unlink,wp_adv',
					toolbar2: 'formatselect,underline,alignjustify,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help',
					setup: _.bind( this.on_editor_ready, this ),
				}
			}, settings ) );

		},

		/**
		 * Setup a permalink editor to allow editing of a permalink
		 * @param    obj   event  js event object
		 * @return   void
		 * @since    3.16.6
		 * @version  3.16.6
		 */
		make_slug_editable: function( event ) {

			var self = this,
				$btn = $( event.currentTarget ),
				$link = $btn.prevAll( 'a' ),
				$input = $btn.prev( 'input.permalink' ),
				full_url = $link.attr( 'href' ),
				slug = $input.val(),
				short_url = full_url.replace( slug, '' );

			// hide the button
			$btn.hide();

			// make the link not clickable
			$link.css( {
				color: '#999',
				'pointer-events': 'none',
				'text-decoration': 'none',
			} );

			// remove the current slug & trailing slash from the URL
			$link.text( short_url.substring( 0, short_url.length - 1 ) );

			// focus in on the field
			$input.show().focus();

		},

		/**
		 * Callback function called after initialization of an editor
		 * Updates UI if a label is present
		 * Binds a change event to ensure editor changes are saved to the model
		 * @param    obj   editor  wp.editor instance
		 * @return   void
		 * @since    3.16.0
		 * @version  3.17.1
		 */
		on_editor_ready: function( editor ) {

			var self = this,
				$ed = $( '#' + editor.id ),
				$parent = $ed.closest( '.llms-editable-editor' ),
				$label = $parent.find( '.llms-label' ),
				prop = $ed.attr( 'data-attribute' )

			if ( $label.length ) {
				$label.prependTo( $parent.find( '.wp-editor-tools' ) );
			}

			// save changes to the model via Visual ed
			editor.on( 'change', function( event ) {
				self.model.set( prop, wp.editor.getContent( editor.id ) );
			} );

			// save changes via Text ed
			$ed.on( 'input', function( event ) {
				self.model.set( prop, $ed.val() );
			} );

			// trigger an input on the Text ed when quicktags buttons are clicked
			$parent.on( 'click', '.quicktags-toolbar .ed_button', function() {
				setTimeout( function() {
					$ed.trigger( 'input' );
				}, 10 );
			} );

		},

		_validate_url: function( str ) {

			var a = document.createElement( 'a' );
			a.href = str;
			return ( a.host && a.host !== window.location.host );

		}

	};

} );

/**
 * _receive override for Backbone.CollectionView core
 * enables connection with jQuery UI draggable buttons
 * @since    3.16.0
 * @version  3.16.0
 */
define( 'Views/_Receivable',[], function() {

	return {

		/**
		 * Overloads the function from Backbone.CollectionView core because it doesn't properly handle
		 * receieves from a jQuery UI draggable object
		 * @param    obj   event  js event object
		 * @param    obj   ui     jQuery UI object
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		_receive : function( event, ui ) {

			// came from sidebar drag
			if ( ui.sender.hasClass( 'ui-draggable' ) ) {
				var index = this._getContainerEl().children().index( ui.helper );
				ui.helper.remove(); // remove the helper
				this.collection.add( {}, { at: index } );
				return;
			}

			var senderListEl = ui.sender;
			var senderCollectionListView = senderListEl.data( 'view' );
			if( ! senderCollectionListView || ! senderCollectionListView.collection ) return;

			var newIndex = this._getContainerEl().children().index( ui.item );
			var modelReceived = senderCollectionListView.collection.get( ui.item.attr( 'data-model-cid' ) );
			senderCollectionListView.collection.remove( modelReceived );
			this.collection.add( modelReceived, { at : newIndex } );
			modelReceived.collection = this.collection; // otherwise will not get properly set, since modelReceived.collection might already have a value.
			this.setSelectedModel( modelReceived );
		},

	}

} );


/**
 * Shiftable view mixin function
 * @since    3.16.0
 * @version  3.16.0
 */
define( 'Views/_Shiftable',[], function() {

	return {

		/**
		 * Conditionally hide action buttons based on section position in collection
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		maybe_hide_shiftable_buttons: function() {

			if ( ! this.model.collection ) {
				return;
			}

			var type = this.model.get( 'type' );

			if ( this.model.collection.first() === this.model ) {
				this.$el.find( '.shift-up--' + type ).hide();
			} else if ( this.model.collection.last() === this.model ) {
				this.$el.find( '.shift-down--' + type ).hide();
			}

		},

		/**
		 * Move an item in a collection from one position to another
		 * @param    int   old_index  current (old) index within the collection
		 * @param    int   new_index  desired (new) index within the collection
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		shift: function( old_index, new_index ) {

			var collection = this.model.collection;

			collection.remove( this.model );
			collection.add( this.model, { at: new_index } );
			collection.trigger( 'reorder' );

		},

		/**
		 * Move an item down the tree one position
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		shift_down: function( e ) {

			e.preventDefault();
			var index = this.model.collection.indexOf( this.model );
			this.shift( index, index + 1 );

		},

		/**
		 * Move an item up the tree one position
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		shift_up: function( e ) {

			e.preventDefault();
			var index = this.model.collection.indexOf( this.model );
			this.shift( index, index - 1 );

		},

	};

} );

/**
 * Subview utility mixin
 * @since    3.16.0
 * @version  3.16.0
 */
define( 'Views/_Subview',[], function() {

	return {

		subscriptions: {},

		/**
		 * Name of the current subview
		 * @type  {String}
		 */
		state: '',

		/**
		 * Object of subview data
		 * @type  {Object}
		 */
		views: {},

		/**
		 * Retrieve a subview by name from this.views
		 * @param    string   name   name of the subview
		 * @return   obl|false
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		get_subview: function( name ) {

			if ( this.views[ name ] ) {
				return this.views[ name ];
			}

			return false;

		},

		events_subscribe: function( events ) {

			_.each( events, function( func, event ) {

				this.subscriptions[ event ] = func;
				Backbone.pubSub.on( event, func, this );

			}, this );

		},

		events_unsubscribe: function() {

			_.each( this.subscriptions, function( func, event ) {

				Backbone.pubSub.off( event, func, this );
				delete this.subscriptions[ event ];

			}, this );

		},

		/**
		 * Remove a single subview (and all it's subviews) by name
		 * @param    string   name   name of the subview
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		remove_subview: function( name ) {

			var view = this.get_subview( name );

			if ( ! view ) {
				return;
			}

			if ( view.instance ) {

				// remove the subviews if the view has subviews
				if ( ! _.isEmpty( view.instance.views ) ) {
					view.instance.events_unsubscribe();
					view.instance.remove_subviews();
				}

				view.instance.off();
				view.instance.off( null, null, null );
				view.instance.remove();
				view.instance.undelegateEvents();

				// _.each( view.instance, function( val, key ) {
				// 	delete view.instance[ key ];
				// } );

				view.instance = null;

			}

		},

		/**
		 * Remove all subviews (and all the subviews of those subviews)
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		remove_subviews: function() {

			_.each( this.views, function( data, name ) {

				this.remove_subview( name );

			}, this );

		},

		/**
		 * Render subviews based on current state
		 * @param    obj   view_data  additional data to pass to the subviews
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		render_subviews: function( view_data ) {

			view_data = view_data || {};

			_.each( this.views, function( data, name ) {

				if ( this.state === data.state ) {

					this.render_subview( name, view_data );

				} else {

					this.remove_subview( name );

				}

			}, this );

		},

		/**
		 * Render a single subview by name
		 * @param    string   name       name of the subview
		 * @param    obj      view_data  additional data to pass to the subview initializer
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		render_subview: function( name, view_data ) {

			var view = this.get_subview( name );

			if ( ! view ) {
				return;
			}

			this.remove_subview( name );

			if ( ! view.instance ) {
				view.instance = new view.class( view_data );
			}

			view.instance.render();

		},

		/**
		 * Set the current subview
		 * Must call render after!
		 * @param    string   state  name of the state [builder|editor]
		 * @return   obj             this for chaining
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		set_state: function ( state ) {

			this.state = state;
			return this;

		},

	}

} );

/**
 * Trashable model
 * @type     {Object}
 * @since    3.16.12
 * @version  3.16.12
 */
define( 'Views/_Trashable',[], function() {

	return {

		/**
		 * DOM Events
		 * @type  {Object}
		 * @since    3.16.12
		 * @version  3.16.12
		 */
		events: {
			'click a[href="#llms-trash-model"]': 'trash_model',
		},

		/**
		 * Remove a model from it's parent and delete it
		 * @param    obj   event  js event object
		 * @return   void
		 * @since    3.16.12
		 * @version  3.16.12
		 */
		trash_model: function( event ) {

			if ( event ) {
				event.preventDefault();
				event.stopPropagation();
			}

			var msg = LLMS.l10n.replace( 'Are you sure you want to move this %s to the trash?', {
				'%s': this.model.get_l10n_type(),
			} );

			if ( window.confirm( msg ) ) {

				if ( this.model.collection ) {
					this.model.collection.remove( this.model );
				}

				// publish event
				Backbone.pubSub.trigger( 'model-trashed', this.model );

				// trigger local event so extending views can run other actions where necessary
				this.trigger( 'model-trashed', this.model );

			}

		},

	}

} );

/**
 * Load view mixins
 * @return   obj
 * @since    3.17.1
 * @version  3.17.1
 */
define( 'Views/_loader',[
		'Views/_Detachable',
		'Views/_Editable',
		'Views/_Receivable',
		'Views/_Shiftable',
		'Views/_Subview',
		'Views/_Trashable'
	],
	function(
		Detachable,
		Editable,
		Receivable,
		Shiftable,
		Subview,
		Trashable
	) {

	return {
		Detachable: Detachable,
		Editable: Editable,
		Receivable: Receivable,
		Shiftable: Shiftable,
		Subview: Subview,
		Trashable: Trashable,
	};

} );

/**
 * Constructor functions for constructing models, views, and collections
 * @since    3.16.0
 * @version  3.17.1
 */
define( 'Controllers/Construct',[
		'Collections/loader',
		'Models/loader',
		'Views/_loader'
	], function(
		Collections,
		Models,
		Views
	) {

	return function() {

		/**
		 * Internal getter
		 * Constructs new Collections, Models, and Views
		 * @param    obj      type     type of object to construct [Collection,Model,View]
		 * @param    string   name     name of the object to construct
		 * @param    obj      data     object data to pass into the object's constructor
		 * @param    obj      options  object options to pass into the constructor
		 * @return   obj
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		function get( type, name, data, options ) {

			if ( ! type[ name ] ) {
				console.log( '"' + name + '" not found.' );
				return false;
			}

			return new type[ name ]( data, options );

		}

		/**
		 * Instantiate a collection
		 * @param    string   name     Collection class name (EG: "Sections")
		 * @param    array    data     Array of model objects to pass to the constructor
		 * @param    obj      options  Object of options to pass to the constructor
		 * @return   obj
		 * @since    3.17.0
		 * @version  3.17.0
		 */
		this.get_collection = function( name, data, options ) {

			return get( Collections, name, data, options );

		};

		/**
		 * Instantiate a model
		 * @param    string   name     Model class name (EG: "Section")
		 * @param    obj      data     Object of model attributes to pass to the constructor
		 * @param    obj      options  Object of options to pass to the constructor
		 * @return   obj
		 * @since    3.17.0
		 * @version  3.17.0
		 */
		this.get_model = function( name, data, options ) {

			return get( Models, name, data, options );

		};

		/**
		 * Let 3rd parties extend a view using any of the mixin (_) views
		 * @param    {obj}     view     base object used for the view
		 * @param... {string}  extends  any number of strings that should be mixed into the view
		 * @return   obj
		 * @since    3.17.1
		 * @version  3.17.1
		 */
		this.extend_view = function() {

			var view = arguments[0],
				i = 1;

			while ( arguments[ i ] ) {

				var classname = arguments[ i ];
				if ( Views[ classname ] ) {

					if ( view.events && Views[ classname ].events ) {
						view.events = _.defaults( view.events, Views[ classname ].events );
					}

					view = _.defaults( view, Views[ classname ] );

				}

				i++;
			}

			return Backbone.View.extend( view );

		};

		/**
		 * Allows custom collection registration by extending the default BackBone collection
		 * @param    string   name   model name
		 * @param    obj      props  properties to extend the collection with
		 * @return   void
		 * @since    3.17.1
		 * @version  3.17.1
		 */
		this.register_collection = function( name, props ) {

			Collections[ name ] = Backbone.Collection.extend( props );

		};

		/**
		 * Allows custom model registration by extending the default abstract model
		 * @param    string   name   model name
		 * @param    obj      props  properties to extend the abstract model with
		 * @return   void
		 * @since    3.17.0
		 * @version  3.17.0
		 */
		this.register_model = function( name, props ) {

			Models[ name ] = Models['Abstract'].extend( props );

		};

		return this;

	};

} );

/**
 * LifterLMS Builder Debugging suite
 * @since    3.16.0
 * @version  3.16.0
 */
define( 'Controllers/Debug',[], function() {

 	return function( settings ) {

 		var self = this,
 			enabled = settings.enabled || false;

		/**
		 * Disable debugging
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
 		this.disable = function() {

 			self.log( 'LifterLMS Builder debugging disabled' );
 			enabled = false;

 		};

		/**
		 * Enable debugging
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
 		this.enable = function() {

 			enabled = true;
 			self.log( 'LifterLMS Builder debugging enabled' );

 		};

 		/**
 		 * General logging function
 		 * Logs to the js console only if logging is enabled
 		 * @return   void
 		 * @since    3.16.0
 		 * @version  3.16.0
 		 */
 		this.log = function() {

			if ( ! enabled ) {
				return;
			}

			_.each( arguments, function( data ) {
				console.log( data );
			} );

 		};

 		/**
 		 * Toggles current state of the logger on or off
 		 * @return   void
 		 * @since    3.16.0
 		 * @version  3.16.0
 		 */
 		this.toggle = function() {

			if ( enabled ) {
				self.disable();
			} else {
				self.enable();
			}

 		};

 		// on startup, log a message if logging is enabled
 		if ( enabled ) {
 			self.enable();
 		}

 	}

 } );

/**
 * Model schema functions
 * @since    3.17.0
 * @version  3.17.0
 */
define( 'Controllers/Schemas',[], function() {

	/**
	 * Main Schemas class
	 * @param    obj   schemas  schemas definitions initialized via PHP filters
	 * @return   obj
	 * @since    3.17.0
	 * @version  3.17.0
	 */
	return function( schemas ) {

		// initialize any custom schemas defined via PHP
		var custom_schemas = schemas;
		_.each( custom_schemas, function( type ) {
			_.each( type, function( schema ) {
				schema.custom = true;
			} );
		} );

		/**
		 * Retrieve a schema for a given model by type
		 * Extends default schemas definitions with custom 3rd party definitions
		 * @param    obj      schema      default schema definition from the model (or empty object if none defined)
		 * @param    string   model_type  the model type ('lesson', 'quiz', etc)
		 * @param    obj      model       Instance of the Backbone.Model for the given model
		 * @return   obj
		 * @since    3.17.0
		 * @version  3.17.0
		 */
		this.get = function( schema, model_type, model ) {

			// extend the default schema with custom php schemas for the type if they exist
			if ( custom_schemas[ model_type ] ) {
				schema = _.extend( schema, custom_schemas[ model_type ] );
			}

			return schema;

		};

		return this;

	};

} );

/**
 * Sync builder data to the server
 * @since    3.16.0
 * @version  3.19.4
 */
define( 'Controllers/Sync',[], function() {

 	return function( Course, settings ) {

 		this.saving = false;

 		var self = this,
 			autosave = true,
 			check_interval = null,
 			check_interval_ms = settings.check_interval_ms || 10000,
 			detached = new Backbone.Collection(),
 			trashed = new Backbone.Collection();

		/**
		 * init
		 * @return   void
		 * @since    3.16.7
		 * @version  3.16.7
		 */
 		function init() {

 			// determine if autosaving is possible
 			if ( 'undefined' === typeof wp.heartbeat ) {

 				window.llms_builder.debug.log( 'WordPress Heartbeat disabled. Autosaving is disabled!' );
 				autosave = false;

 			}

			// setup the check interval
			if ( check_interval_ms ) {
				self.set_check_interval( check_interval_ms );
			}

			// warn when users attempt to leave the page
			$( window ).on( 'beforeunload', function() {

				if ( self.has_unsaved_changes() ) {
					check_for_changes();
					return 'Are you sure you want to abandon your changes?';
				}

			} );

 		};

 		/*
 			 /$$             /$$                                             /$$                           /$$
 			|__/            | $$                                            | $$                          |__/
 			 /$$ /$$$$$$$  /$$$$$$    /$$$$$$   /$$$$$$  /$$$$$$$   /$$$$$$ | $$        /$$$$$$   /$$$$$$  /$$
 			| $$| $$__  $$|_  $$_/   /$$__  $$ /$$__  $$| $$__  $$ |____  $$| $$       |____  $$ /$$__  $$| $$
 			| $$| $$  \ $$  | $$    | $$$$$$$$| $$  \__/| $$  \ $$  /$$$$$$$| $$        /$$$$$$$| $$  \ $$| $$
 			| $$| $$  | $$  | $$ /$$| $$_____/| $$      | $$  | $$ /$$__  $$| $$       /$$__  $$| $$  | $$| $$
 			| $$| $$  | $$  |  $$$$/|  $$$$$$$| $$      | $$  | $$|  $$$$$$$| $$      |  $$$$$$$| $$$$$$$/| $$
 			|__/|__/  |__/   \___/   \_______/|__/      |__/  |__/ \_______/|__/       \_______/| $$____/ |__/
 			                                                                                    | $$
 			                                                                                    | $$
 			                                                                                    |__/
 		*/

 		/**
 		 * Adds error message(s) to the data object returned by heartbeat-tick
 		 * @param    obj            data  llms_builder data object from heartbeat-tick
 		 * @param    string|array   err   error messages array or string
 		 * @return   obj
 		 * @since    3.16.0
 		 * @version  3.16.0
 		 */
		function add_error_msg( data, err ) {

			if ( 'success' === data.status ) {
				data.message = [];
			}

			data.status = 'error';
			if ( 'string' === typeof err ) {
				err = [ err ];
			}
			data.message = data.message.concat( err );

			return data;

		};

		/**
		 * Publish sync status so other areas of the application can see what's happening here
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		function check_for_changes() {

			var data = {};
			data.changes = self.get_unsaved_changes();
			data.has_unsaved_changes = self.has_unsaved_changes( data.changes );
			data.saving = self.saving;

			window.llms_builder.debug.log( '==== start changes check ====', data, '==== finish changes check ====' );

			Backbone.pubSub.trigger( 'current-save-status', data );

		};

		/**
		 * Manually Save data via Admin AJAX when the heartbeat API has been disabled
		 * @return   void
		 * @since    3.16.7
		 * @version  3.16.7
		 */
		function do_ajax_save() {

			// prevent simultaneous saves
			if ( self.saving ) {
				return;
			}

			var changes = self.get_unsaved_changes();

			// only send data if we have data to send
			if ( self.has_unsaved_changes( changes ) ) {

				changes.id = Course.get( 'id' );

				LLMS.Ajax.call( {
					data: {
						action: 'llms_builder',
						action_type: 'ajax_save',
						course_id: changes.id,
						llms_builder: JSON.stringify( changes ),
					},
					beforeSend: function() {

						window.llms_builder.debug.log( '==== start do_ajax_save before ====', changes, '==== finish do_ajax_save before ====' );

						self.saving = true;

						Backbone.pubSub.trigger( 'heartbeat-send', self );

					},
					error: function( xhr, status, error ) {

						window.llms_builder.debug.log( '==== start do_ajax_save error ====', data, '==== finish do_ajax_save error ====' );

						self.saving = false;

						Backbone.pubSub.trigger( 'heartbeat-tick', self, {
							status: 'error',
							message: xhr.responseText + ' (' + error + ' ' + status +')',
						} );

					},
					success: function( res ) {

						if ( ! res.llms_builder ) {
							return;
						}

						window.llms_builder.debug.log( '==== start do_ajax_save success ====', res, '==== finish do_ajax_save success ====' );

						res.llms_builder = process_removals( res.llms_builder );
						res.llms_builder = process_updates( res.llms_builder );

						self.saving = false;

						Backbone.pubSub.trigger( 'heartbeat-tick', self, res.llms_builder );

					}

				} );

			}


		};

		/**
		 * Retrieve all the attributes changed on a model since the last sync
		 *
		 * For a new model (a model with a temp ID) or a model where _forceSync has been defined ALL atts will be returned
		 * For an existing model (without a temp ID) only retrieves changed attributes as tracked by Backbone.TrackIt
		 *
		 * This function excludes any attributes defined as child attributes via the models relationship settings
		 *
		 * @param    obj   model  instance of a Backbone.Model
		 * @return   obj
		 * @since    3.16.0
		 * @version  3.16.6
		 */
		function get_changed_attributes( model ) {

			var atts = {},
				sync_type;

			// don't save mid editing
			if ( model.get( '_has_focus' ) ) {
				return atts;
			}

			// model hasn't been persisted to the database to get a real ID yet
			// send *all* of it's atts
			if ( has_temp_id( model ) || true === model.get( '_forceSync' ) ) {

				atts = _.clone( model.attributes );
				sync_type = 'full';

			// only send the changed atts
			} else {

				atts = model.unsavedAttributes();
				sync_type = 'partial';

			}

			var exclude = ( model.get_relationships ) ? model.get_child_props() : [];
			atts = _.omit( atts, function( val, key ) {

				// exclude keys that start with an underscore which are used by the
				// application but don't need to be stored in the database
				if ( 0 === key.indexOf( '_' ) ) {
					return true;
				} else if ( -1 !== exclude.indexOf( key ) ) {
					return true;
				}
				return false;

			} );

			if ( model.before_save ) {
				atts = model.before_save( atts, sync_type );
			}

			return atts;

		};

		/**
		 * Get all the changes to an object (either a Model or a Collection of models)
		 * Returns only changes to models and the IDs of that model (should changes exist)
		 * Uses get_changed_attributes() to determine if all atts or only changed atts are needed
		 * Processes children intelligently to only return changed children rather than the entire collection of children
		 *
		 * @param    obj        object  instance of a Backbone.Model or Backbone.Collection
		 * @return   obj|array	  		if object is a model, returns an object
		 *                            	if object is a collection, returns an array of objects
		 * @since    3.16.0
		 * @version  3.16.11
		 */
		function get_changes_to_object( object ) {

			var changed_atts;

			if ( object instanceof Backbone.Model ) {

				changed_atts = get_changed_attributes( object );

				if ( object.get_relationships ) {

					_.each( object.get_child_props(), function( prop ) {

						var children = get_changes_to_object( object.get( prop ) );
						if ( ! _.isEmpty( children ) ) {
							changed_atts[ prop ] = children;
						}

					} );

				}

				// if we have any data, add the id to the model
				if ( ! _.isEmpty( changed_atts ) ) {
					changed_atts.id = object.get( 'id' );
				}

			} else if ( object instanceof Backbone.Collection ) {

				changed_atts = [];
				object.each( function( model ) {
					var model_changes = get_changes_to_object( model );
					if ( ! _.isEmpty( model_changes ) ) {
						changed_atts.push( model_changes );
					}
				} );

			}

			return changed_atts;

		};

		/**
		 * Determines if a model has a temporary ID or a real persisted ID
		 * @param    obj   model  instance of a model
		 * @return   boolean
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		function has_temp_id( model ) {

			return ( ! _.isNumber( model.id ) && 0 === model.id.indexOf( 'temp_' ) );

		};

		/**
		 * Compares changes synced to the server against current model and restarts
		 * tracking on elements that haven't changed since the last sync
		 * @param    obj   model  instance of a Backbone.Model
		 * @param    obj   data   data set that was processed by the server
		 * @return   void
		 * @since    3.16.11
		 * @version  3.19.4
		 */
		function maybe_restart_tracking( model, data ) {

			Backbone.pubSub.trigger( model.get( 'type' ) + '-maybe-restart-tracking', model, data );

			var omit = [ 'id', 'orig_id' ];

			if ( model.get_relationships ) {
				omit.concat( model.get_child_props() );
			}

			_.each( _.omit( data, omit ), function( val, prop ) {

				if ( _.isEqual( model.get( prop ), val ) ) {
					delete model._unsavedChanges[ prop ];
					model._originalAttrs[ prop ] = val;
				}

			} );

			// if syncing was forced, allow tracking to move forward as normal moving forward
			model.unset( '_forceSync' );

		};

		/**
		 * Processes response data from heartbeat-tick related to trashing & detaching models
		 * On success, removes from local removal collection
		 * On error, appends error messages to the data object returned to UI for on-screen feedback
		 * @param    obj   data  data.llms_builder object from heartbeat-tick response
		 * @return   obj
		 * @since    3.16.0
		 * @version  3.17.1
		 */
		function process_removals( data ) {

			// check removals for errors
			var removals = {
				detach: detached,
				trash: trashed,
			};

			_.each( removals, function( coll, key ) {

				if ( data[ key ] ) {

					var errors = [];

					_.each( data[ key ] , function( info ) {

						// succesfully detached, remove it from the detached collection
						if ( ! info.error ) {

							coll.remove( info.id );

						} else {

							errors.push( info.error );

						}

					} );

					if ( errors.length ) {
						_.extend( data, add_error_msg( data, errors ) );
					}

				}

			} );

			return data;
		}

		/**
		 * Processes response data from heartbeat-tick related to creating / updating a single object
		 * Handles both collections and models as a recursive function
		 * @param    {[type]}   data       [description]
		 * @param    {[type]}   type       [description]
		 * @param    {[type]}   parent     [description]
		 * @param    {[type]}   main_data  [description]
		 * @return   {[type]}
		 * @since    3.16.0
		 * @version  3.16.11
		 */
		function process_object_updates( data, type, parent, main_data ) {

			if ( ! data[ type ] ) {
				return data;
			}

			if ( parent.get( type ) instanceof Backbone.Model ) {

				var info = data[ type ];

				if ( info.error ) {

					_.extend( main_data, add_error_msg( main_data, info.error ) );

				} else {

					var model = parent.get( type );

					// update temp ids with the real id
					if ( info.id != info.orig_id ) {
						model.set( 'id', info.id );
						delete model._unsavedChanges.id;
					}
					maybe_restart_tracking( model, info );

					// check children
					if ( model.get_relationships ) {

						_.each( model.get_child_props(), function( child_key ) {
							_.extend( data[ type ], process_object_updates( data[ type ], child_key, model, main_data ) );
						} );

					}

				}

			} else if ( parent.get( type ) instanceof Backbone.Collection ) {

				_.each( data[ type ], function( info, index ) {

					if ( info.error ) {

						_.extend( main_data, add_error_msg( main_data, info.error ) );

					} else {

						var model = parent.get( type ).get( info.orig_id );

						// update temp ids with the real id
						if ( info.id != info.orig_id ) {
							model.set( 'id', info.id );
							delete model._unsavedChanges.id;
						}
						maybe_restart_tracking( model, info );

						// check children
						if ( model.get_relationships ) {

							_.each( model.get_child_props(), function( child_key ) {
								_.extend( data[ type ], process_object_updates( data[ type ][ index ], child_key, model, main_data ) );
							} );

						}

					}

				} );

			}

			return main_data;

		};

		/**
		 * Processes response data from heartbeat-tick related to updating & creating new models
		 * On success, removes from local removal collection
		 * On error, appends error messages to the data object returned to UI for on-screen feedback
		 * @param    obj   data  data.llms_builder object from heartbeat-tick response
		 * @return   obj
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		function process_updates( data ) {

			// only mess with updates data
			if ( ! data.updates ) {
				return data;
			}

			if ( data.updates ) {
				data = process_object_updates( data.updates, 'sections', Course, data );
			}

			return data;

		};

		/*
			                     /$$       /$$ /$$                                     /$$
			                    | $$      | $$|__/                                    |__/
			  /$$$$$$  /$$   /$$| $$$$$$$ | $$ /$$  /$$$$$$$        /$$$$$$   /$$$$$$  /$$
			 /$$__  $$| $$  | $$| $$__  $$| $$| $$ /$$_____/       |____  $$ /$$__  $$| $$
			| $$  \ $$| $$  | $$| $$  \ $$| $$| $$| $$              /$$$$$$$| $$  \ $$| $$
			| $$  | $$| $$  | $$| $$  | $$| $$| $$| $$             /$$__  $$| $$  | $$| $$
			| $$$$$$$/|  $$$$$$/| $$$$$$$/| $$| $$|  $$$$$$$      |  $$$$$$$| $$$$$$$/| $$
			| $$____/  \______/ |_______/ |__/|__/ \_______/       \_______/| $$____/ |__/
			| $$                                                            | $$
			| $$                                                            | $$
			|__/                                                            |__/
		*/

		/**
		 * Retrieve all unsaved changes for the builder instance
		 * @return   obj
		 * @since    3.16.0
		 * @version  3.17.1
		 */
		this.get_unsaved_changes = function() {

			return {
				detach: detached.pluck( 'id' ),
				trash: trashed.pluck( 'id' ),
				updates: get_changes_to_object( Course ),

			}
		};

		/**
		 * Check if the builder instance has unsaved changes
		 * @param    obj      changes    optionally pass in an object from the return of this.get_unsaved_changes()
		 *                               save some resources by not running the check twice during heartbeats
		 * @return   boolean
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		this.has_unsaved_changes = function( changes ) {

			if ( 'undefined' === typeof changes ) {
				changes = self.get_unsaved_changes();
			}

			// check all possible keys, once we find one with content we have some changes to persist
			var found = _.find( changes, function( data ) {

				return ( false === _.isEmpty( data ) );

			} );

			return found ? true : false;

		};

		/**
		 * Save changes right now.
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.7
		 */
		this.save_now = function() {
			if ( autosave ) {
				wp.heartbeat.connectNow();
			} else {
				do_ajax_save();
			}
		};

		/**
		 * Update the interval that checks for changes to the builder instance
		 * @param    int        ms   time (in milliseconds) to run the check on
		 *                           pass 0 to disable the check
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		this.set_check_interval = function( ms ) {
			check_interval_ms = ms;
			if ( check_interval ) {
				clearInterval( check_interval );
			}
			if ( check_interval_ms ) {
				check_interval = setInterval( check_for_changes, check_interval_ms );
			}
		};

		/*
			 /$$ /$$             /$$
			| $$|__/            | $$
			| $$ /$$  /$$$$$$$ /$$$$$$    /$$$$$$  /$$$$$$$   /$$$$$$   /$$$$$$   /$$$$$$$
			| $$| $$ /$$_____/|_  $$_/   /$$__  $$| $$__  $$ /$$__  $$ /$$__  $$ /$$_____/
			| $$| $$|  $$$$$$   | $$    | $$$$$$$$| $$  \ $$| $$$$$$$$| $$  \__/|  $$$$$$
			| $$| $$ \____  $$  | $$ /$$| $$_____/| $$  | $$| $$_____/| $$       \____  $$
			| $$| $$ /$$$$$$$/  |  $$$$/|  $$$$$$$| $$  | $$|  $$$$$$$| $$       /$$$$$$$/
			|__/|__/|_______/    \___/   \_______/|__/  |__/ \_______/|__/      |_______/
		*/

		/**
		 * Listen for detached models and send them to the server for persistence
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		Backbone.pubSub.on( 'model-detached', function( model ) {

			// detached models with temp ids haven't been persisted so we don't care
			if ( has_temp_id( model ) ) {
				return;
			}

			detached.add( _.clone( model.attributes ) );

		} );

		/**
		 * Listen for trashed models and send them to the server for deletion
		 * @since    3.16.0
		 * @version  3.17.1
		 */
		Backbone.pubSub.on( 'model-trashed', function( model ) {

			// if the model has a temp ID we don't have to persist the deletion
			if ( has_temp_id( model ) ) {
				return;
			}

			var data = _.clone( model.attributes );

			if ( model.get_trash_id ) {
				data.id = model.get_trash_id();
			}

			trashed.add( data );

		} );

		/*
			 /$$                                       /$$     /$$                             /$$
			| $$                                      | $$    | $$                            | $$
			| $$$$$$$   /$$$$$$   /$$$$$$   /$$$$$$  /$$$$$$  | $$$$$$$   /$$$$$$   /$$$$$$  /$$$$$$
			| $$__  $$ /$$__  $$ |____  $$ /$$__  $$|_  $$_/  | $$__  $$ /$$__  $$ |____  $$|_  $$_/
			| $$  \ $$| $$$$$$$$  /$$$$$$$| $$  \__/  | $$    | $$  \ $$| $$$$$$$$  /$$$$$$$  | $$
			| $$  | $$| $$_____/ /$$__  $$| $$        | $$ /$$| $$  | $$| $$_____/ /$$__  $$  | $$ /$$
			| $$  | $$|  $$$$$$$|  $$$$$$$| $$        |  $$$$/| $$$$$$$/|  $$$$$$$|  $$$$$$$  |  $$$$/
			|__/  |__/ \_______/ \_______/|__/         \___/  |_______/  \_______/ \_______/   \___/
		*/

		/**
		 * Add data to the WP heartbeat to persist new models, changes, and deletions to the DB
		 * @since    3.16.0
		 * @version  3.16.7
		 */
		$( document ).on( 'heartbeat-send', function( event, data ) {

			// prevent simultaneous saves
			if ( self.saving ) {
				return;
			}

			var changes = self.get_unsaved_changes();

			// only send data if we have data to send
			if ( self.has_unsaved_changes( changes ) ) {

				changes.id = Course.get( 'id' );
				self.saving = true;
				data.llms_builder = JSON.stringify( changes );

			}

			window.llms_builder.debug.log( '==== start heartbeat-send ====', data, '==== finish heartbeat-send ====' );

			Backbone.pubSub.trigger( 'heartbeat-send', self );

		} );

		/**
		 * Confirm detachments & deletions and replace temp IDs with new persisted IDs
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		$( document ).on( 'heartbeat-tick', function( event, data ) {

			if ( ! data.llms_builder ) {
				return;
			}

			window.llms_builder.debug.log( '==== start heartbeat-tick ====', data, '==== finish heartbeat-tick ====' );

			data.llms_builder = process_removals( data.llms_builder );
			data.llms_builder = process_updates( data.llms_builder );

			self.saving = false;

			Backbone.pubSub.trigger( 'heartbeat-tick', self, data.llms_builder );

		} );

		/**
		 * On heartbeat errors publish an error to the main builder application
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		$( document ).on( 'heartbeat-error', function( event, data ) {

			window.llms_builder.debug.log( '==== start heartbeat-error ====', data, '==== finish heartbeat-error ====' );

			self.saving = false;

			Backbone.pubSub.trigger( 'heartbeat-tick', self, {
				status: 'error',
				message: data.responseText + ' (' + data.status + ' ' + data.statusText +')',
			} );

		} );

		/*
			 /$$           /$$   /$$
			|__/          |__/  | $$
			 /$$ /$$$$$$$  /$$ /$$$$$$
			| $$| $$__  $$| $$|_  $$_/
			| $$| $$  \ $$| $$  | $$
			| $$| $$  | $$| $$  | $$ /$$
			| $$| $$  | $$| $$  |  $$$$/
			|__/|__/  |__/|__/   \___/
		*/
		init();

		return this;

	};

} );

/**
 * Single Lesson View
 * @since    3.16.0
 * @version  3.27.0
 */
define( 'Views/Lesson',[
		'Views/_Detachable',
		'Views/_Editable',
		'Views/_Shiftable',
		'Views/_Trashable'
	], function(
		Detachable,
		Editable,
		Shiftable,
		Trashable
	) {

	return Backbone.View.extend( _.defaults( {

		/**
		 * Get default attributes for the html wrapper element
		 * @return   obj
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		attributes: function() {
			return {
				'data-id': this.model.id,
				'data-section-id': this.model.get( 'parent_section' ),
			};
		},

		/**
		 * HTML class names
		 * @type  {String}
		 */
		className: 'llms-builder-item llms-lesson',

		/**
		 * Events
		 * @type  {Object}
		 * @since    3.16.0
		 * @version  3.16.12
		 */
		events: _.defaults( {
			'click .edit-lesson': 'open_lesson_editor',
			'click .edit-quiz': 'open_quiz_editor',
			'click .edit-assignment': 'open_assignment_editor',
			'click .section-prev': 'section_prev',
			'click .section-next': 'section_next',
			'click .shift-up--lesson': 'shift_up',
			'click .shift-down--lesson': 'shift_down',
		}, Detachable.events, Editable.events, Trashable.events ),

		/**
		 * HTML element wrapper ID attribute
		 * @return   string
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		id: function() {
			return 'llms-lesson-' + this.model.id;
		},

		/**
		 * Wrapper Tag name
		 * @type  {String}
		 */
		tagName: 'li',

		/**
		 * Get the underscore template
		 * @type  {[type]}
		 */
		template: wp.template( 'llms-lesson-template' ),

		/**
		 * Initialization callback func (renders the element on screen)
		 * @return   void
		 * @since    3.14.1
		 * @version  3.14.1
		 */
		initialize: function() {

			this.render();

			this.listenTo( this.model, 'change', this.render );

			Backbone.pubSub.on(  'lesson-selected', this.on_select, this );
			Backbone.pubSub.on(  'new-lesson-added', this.on_select, this );

		},

		/**
		 * Compiles the template and renders the view
		 * @return   self (for chaining)
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		render: function() {

			this.$el.html( this.template( this.model ) );
			this.maybe_hide_shiftable_buttons();
			if ( this.model.get( '_selected' ) ) {
				this.$el.addClass( 'selected' );
			} else {
				this.$el.removeClass( 'selected' );
			}
			return this;

		},

		/**
		 * Click event for the assignment editor action icon
		 * Opens sidebar to the assignment editor tab
		 * @param    obj event JS Event obj.
		 * @return   void
		 * @since    3.17.0
		 * @version  3.27.0
		 */
		open_assignment_editor: function( event ) {

			if ( event ) {
				event.preventDefault();
			}

			Backbone.pubSub.trigger( 'lesson-selected', this.model, 'assignment' );
			this.model.set( '_selected', true );
			this.set_hash( 'assignment' );

		},

		/**
		 * Click event for lesson settings action icon
		 * Opens sidebar to the lesson editor tab
		 * @param    obj event JS Event obj.
		 * @return   void
		 * @since    3.16.0
		 * @version  3.27.0
		 */
		open_lesson_editor: function( event ) {

			if ( event ) {
				event.preventDefault();
			}

			Backbone.pubSub.trigger( 'lesson-selected', this.model, 'lesson' );
			this.model.set( '_selected', true );
			this.set_hash( false );

		},

		/**
		 * Click event for the quiz editor action icon
		 * Opens sidebar to the quiz editor tab
		 * @param    obj event JS Event obj.
		 * @return   void
		 * @since    3.16.0
		 * @version  3.27.0
		 */
		open_quiz_editor: function( event ) {

			if ( event ) {
				event.preventDefault();
			}

			Backbone.pubSub.trigger( 'lesson-selected', this.model, 'quiz' );
			this.model.set( '_selected', true );
			this.set_hash( 'quiz' );

		},

		/**
		 * When a lesson is selected mark it as selected in the hidden prop
		 * Allows views to re-render and reflect current state properly
		 * @param    obj   model  lesson model that's been selected
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		on_select: function( model ) {

			if ( this.model.id !== model.id ) {
				this.model.set( '_selected', false );
			}

		},

		/**
		 * Click event for the "Next Section" button
		 * @param    obj   event   js event obj
		 * @return   void
		 * @since    3.16.11
		 * @version  3.16.11
		 */
		section_next: function( event ) {
			event.preventDefault();
			this._move_to_section( 'next' );
		},

		/**
		 * Click event for the "Previous Section" button
		 * @param    obj   event   js event obj
		 * @return   void
		 * @since    3.16.11
		 * @version  3.16.11
		 */
		section_prev: function( event ) {
			event.preventDefault();
			this._move_to_section( 'prev' );
		},

		/**
		 * Adds a hash for deeplinking to a specific lesson tab
		 * @param  string  subtab subtab [quiz|assignment]
		 * @return void
		 * @since   3.27.0
		 * @version 3.27.0
		 */
		set_hash: function( subtab ) {

			var hash = 'lesson:' + this.model.get( 'id' );

			if ( subtab ) {
				hash += ':' + subtab;
			}

			window.location.hash = hash;

		},

		/**
		 * Move the lesson into a new section
		 * @param    string   direction  direction [prev|next]
		 * @return   void
		 * @since    3.16.11
		 * @version  3.16.11
		 */
		_move_to_section: function( direction ) {

			var from_coll = this.model.collection,
				to_section;

			if ( 'next' === direction ) {
				to_section = from_coll.parent.get_next();
			} else if ( 'prev' === direction ) {
				to_section = from_coll.parent.get_prev();
			}

			if ( to_section ) {

				from_coll.remove( this.model );
				to_section.add_lesson( this.model );
				to_section.set( '_expanded', true );

			}

		},

	}, Detachable, Editable, Shiftable, Trashable ) );

} );

/**
 * Single Section View
 * @since    3.13.0
 * @version  3.16.0
 */
define( 'Views/LessonList',[ 'Views/Lesson', 'Views/_Receivable' ], function( LessonView, Receivable ) {

	return Backbone.CollectionView.extend( _.defaults( {

		className: 'llms-lessons',

		/**
		 * Section model
		 * @type  {[type]}
		 */
		modelView: LessonView,

		/**
		 * Are sections selectable?
		 * @type  {Bool}
		 */
		selectable: false,

		/**
		 * Are sections sortable?
		 * @type  {Bool}
		 */
		sortable: true,

		sortableOptions: {
			axis: false,
			connectWith: '.llms-lessons',
			cursor: 'move',
			handle: '.drag-lesson',
			items: '.llms-lesson',
			placeholder: 'llms-lesson llms-sortable-placeholder',
		},

		sortable_start: function( collection ) {
			$( '.llms-lessons' ).addClass( 'dragging' );
		},

		sortable_stop: function( collection ) {
			$( '.llms-lessons' ).removeClass( 'dragging' );
		},

		/**
		 * Overloads the function from Backbone.CollectionView core because it doesn't send stop events
		 * if moving from one sortable to another... :-(
		 * @param    obj   event  js event object
		 * @param    obj   ui     jQuery UI object
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		_sortStop : function( event, ui ) {

			var modelBeingSorted = this.collection.get( ui.item.attr( 'data-model-cid' ) ),
				modelViewContainerEl = this._getContainerEl(),
				newIndex = modelViewContainerEl.children().index( ui.item );

			if ( newIndex == -1 && modelBeingSorted ) {
				this.collection.remove( modelBeingSorted );
			}

			this._reorderCollectionBasedOnHTML();
			this.updateDependentControls();

			if( this._isBackboneCourierAvailable() ) {
				this.spawn( 'sortStop', { modelBeingSorted : modelBeingSorted, newIndex : newIndex } );
			} else {
				this.trigger( 'sortStop', modelBeingSorted, newIndex );
			}

		},

	}, Receivable ) );

} );

/**
 * Single Section View
 * @since    3.13.0
 * @version  3.16.12
 */
define( 'Views/Section',[
		'Views/LessonList',
		'Views/_Editable',
		'Views/_Shiftable',
		'Views/_Trashable'
	], function(
		LessonListView,
		Editable,
		Shiftable,
		Trashable
	) {

	return Backbone.View.extend( _.defaults( {

		/**
		 * Get default attributes for the html wrapper element
		 * @return   obj
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		attributes: function() {
			return {
				'data-id': this.model.id,
			};
		},

		/**
		 * Element classnames
		 * @type  {String}
		 */
		className: 'llms-builder-item llms-section',

		/**
		 * Events
		 * @type     {Object}
		 * @since    3.16.0
		 * @version  3.16.12
		 */
		events: _.defaults( {

			'click': 'select',
			'click .expand': 'expand',
			'click .collapse': 'collapse',
			'click .shift-up--section': 'shift_up',
			'click .shift-down--section': 'shift_down',

			'mouseenter .llms-lessons': 'on_mouseenter',

		}, Editable.events, Trashable.events ),

		/**
		 * HTML element wrapper ID attribute
		 * @return   string
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		id: function() {
			return 'llms-section-' + this.model.id;
		},

		/**
		 * Wrapper Tag name
		 * @type  {String}
		 */
		tagName: 'li',

		/**
		 * Get the underscore template
		 * @type  {[type]}
		 */
		template: wp.template( 'llms-section-template' ),

		/**
		 * Initialization callback func (renders the element on screen)
		 * @return   void
		 * @since    3.13.0
		 * @version  3.16.0
		 */
		initialize: function() {

			this.render();
			this.listenTo( this.model, 'change', this.render );
			this.listenTo( this.model, 'change:_expanded', this.toggle_expanded );
			this.lessonListView.collection.on( 'add', this.on_lesson_add, this );

			this.dragTimeout = null;

			Backbone.pubSub.on( 'expand-all', this.expand, this );
			Backbone.pubSub.on( 'collapse-all', this.collapse, this );

		},

		/**
		 * Render the section
		 * Initalizes a new collection and views for all lessons in the section
		 * @return   void
		 * @since    3.13.0
		 * @version  3.16.0
		 */
		render: function() {

			this.$el.html( this.template( this.model.toJSON() ) );

			this.maybe_hide_shiftable_buttons();

			this.lessonListView = new LessonListView( {
				el: this.$el.find( '.llms-lessons' ),
				collection: this.model.get( 'lessons' ),
			} );
			this.lessonListView.render();
			this.lessonListView.on( 'sortStart', this.lessonListView.sortable_start );
			this.lessonListView.on( 'sortStop', this.lessonListView.sortable_stop );

			// selection changes
			this.lessonListView.on( 'selectionChanged', this.active_lesson_change, this );

			this.maybe_hide_trash_button();

			return this;

		},

		active_lesson_change: function( current, previous ) {

			Backbone.pubSub.trigger( 'active-lesson-change', {
				current: current,
				previous: previous,
			} );

		},

		/**
		 * Collapse lessons within the section
		 * @param    obj   event    js event object
		 * @param    bool  update   if true, updates the model to reflect the new state
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		collapse: function( event, update ) {

			if ( 'undefined' === typeof update ) {
				update = true;
			}

			if ( event ) {
				event.stopPropagation();
				event.preventDefault();
			}

			this.$el.removeClass( 'expanded' ).find( '.drag-expanded' ).removeClass( 'drag-expanded' );
			if ( update ) {
				this.model.set( '_expanded', false );
			}
			Backbone.pubSub.trigger( 'section-toggle', this.model );

		},

		/**
		 * Expand lessons within the section
		 * @param    obj   event    js event object
		 * @param    bool  update   if true, updates the model to reflect the new state
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		expand: function( event, update ) {

			if ( 'undefined' === typeof update ) {
				update = true;
			}

			if ( event ) {
				event.stopPropagation();
				event.preventDefault();
			}

			this.$el.addClass( 'expanded' );
			if ( update ) {
				this.model.set( '_expanded', true );
			}
			Backbone.pubSub.trigger( 'section-toggle', this.model );

		},

		maybe_hide_trash_button: function() {

			var $btn = this.$el.find( '.trash--section' );

			if ( this.model.get( 'lessons' ).isEmpty() ) {

				$btn.show();

			} else {

				$btn.hide()

			}

		},

		/**
		 * When a lesson is added to the section trigger a collection reorder & update the lesson's id
		 * @param    obj   model  Lesson model
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		on_lesson_add: function( model ) {

			this.lessonListView.collection.trigger( 'reorder' );
			model.set( 'parent_section', this.model.get( 'id' ) );
			this.expand();

		},

		on_mouseenter: function( event ) {


			if ( $( event.target ).hasClass( 'dragging' ) ) {

				$( '.drag-expanded' ).removeClass( 'drag-expanded' );
				$( event.target ).addClass( 'drag-expanded' );

			}

		},

		/**
		 * Expand
		 * @param    {[type]}   model  [description]
		 * @param    {[type]}   value  [description]
		 * @return   {[type]}
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		toggle_expanded: function( model, value ) {

			if ( value ) {
				this.expand( null, false );
			} else {
				this.collapse( null, false );
			}

		},

	}, Editable, Shiftable, Trashable ) );

} );

/**
 * Single Section View
 * @since    3.13.0
 * @version  3.16.0
 */
define( 'Views/SectionList',[ 'Views/Section', 'Views/_Receivable' ], function( SectionView, Receivable ) {

	return Backbone.CollectionView.extend( _.defaults( {

		/**
		 * Parent element
		 * @type  {String}
		 */
		el: '#llms-sections',

		events : {
			'mousedown > li.llms-section > .llms-builder-header .llms-headline' : '_listItem_onMousedown',
			// 'dblclick > li, tbody > tr > td' : '_listItem_onDoubleClick',
			'click' : '_listBackground_onClick',
			'click ul.collection-view' : '_listBackground_onClick',
			'keydown' : '_onKeydown'
		},

		/**
		 * Section model
		 * @type  {[type]}
		 */
		modelView: SectionView,

		/**
		 * Enable keyboard events
		 * @type  {Bool}
		 */
		processKeyEvents: false,

		/**
		 * Are sections selectable?
		 * @type  {Bool}
		 */
		selectable: true,

		/**
		 * Are sections sortable?
		 * @type  {Bool}
		 */
		sortable: true,

		sortableOptions: {
			axis: false,
			cursor: 'move',
			handle: '.drag-section',
			items: '.llms-section',
			placeholder: 'llms-section llms-sortable-placeholder',
		},

		sortable_start: function( collection ) {
			this.$el.addClass( 'dragging' );
		},

		sortable_stop: function( collection ) {
			this.$el.removeClass( 'dragging' );
		},

	}, Receivable ) );

} );

/**
 * Single Course View
 * @since    3.13.0
 * @version  3.16.0
 */
define( 'Views/Course',[ 'Views/SectionList', 'Views/_Editable' ], function( SectionListView, Editable ) {

	return Backbone.View.extend( _.defaults( {

		/**
		 * Get default attributes for the html wrapper element
		 * @return   obj
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		attributes: function() {
			return {
				'data-id': this.model.id,
			};
		},

		/**
		 * HTML element selector
		 * @type  {String}
		 */
		el: '#llms-builder-main',

		/**
		 * Wrapper Tag name
		 * @type  {String}
		 */
		tagName: 'div',

		/**
		 * Get the underscore template
		 * @type  {[type]}
		 */
		template: wp.template( 'llms-course-template' ),

		/**
		 * Initialization callback func (renders the element on screen)
		 * @return   void
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		initialize: function() {

			var self = this;

			// this.listenTo( this.model, 'sync', this.render );
			this.render();

			this.sectionListView = new SectionListView( {
				collection: this.model.get( 'sections' ),
			} );
			this.sectionListView.render();
			// drag and drop start
			this.sectionListView.on( 'sortStart', this.sectionListView.sortable_start );
			// drag and drop stop
			this.sectionListView.on( 'sortStop', this.sectionListView.sortable_stop );
			// selection changes
			this.sectionListView.on( 'selectionChanged', this.active_section_change );
			// "select" a section when it's added to the course
			this.listenTo( this.model.get( 'sections'), 'add', this.on_section_add );

			Backbone.pubSub.on( 'section-toggle', this.on_section_toggle, this );

			Backbone.pubSub.on( 'expand-section', this.expand_section, this );

			Backbone.pubSub.on( 'lesson-selected', this.active_lesson_change, this );

		},

		/**
		 * Compiles the template and renders the view
		 * @return   self (for chaining)
		 * @since    3.13.0
		 * @version  3.13.0
		 */
		render: function() {
			this.$el.html( this.template( this.model ) );
			return this;
		},

		active_lesson_change: function( model ) {

			// set parent section to be active
			var section = this.model.get( 'sections' ).get( model.get( 'parent_section' ) );
			this.sectionListView.setSelectedModel( section );

		},

		/**
		 * When a section "selection" changes in the list
		 * Update each section model so we can figure out which one is selected from other views
		 * @param    array   current   array of selected models
		 * @param    array   previous  array of previously selected models
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		active_section_change: function( current, previous ) {

			_.each( current, function( model ) {
				model.set( '_selected', true );
			} );

			_.each( previous, function( model ) {
				model.set( '_selected', false );
			} );

		},

		/**
		 * "Selects" the new section when it's added to the course
		 * @param    obj   model  Section model that's just been added
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		on_section_add: function( model ) {

			this.sectionListView.setSelectedModel( model );

		},

		/**
		 * When expanding/collapsing sections
		 * if collapsing, unselect, if expanding, select
		 * @param    obj   model  toggled section
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		on_section_toggle: function( model ) {

			var selected = model.get( '_expanded' ) ? [ model ] : [];
			this.sectionListView.setSelectedModels( selected );

		}

	}, Editable ) );

} );

/**
 * Model settings fields view
 * @since    3.17.0
 * @version  3.24.0
 */
define( 'Views/SettingsFields',[], function() {

	return Backbone.View.extend( _.defaults( {

		/**
		 * DOM events
		 * @type  {Object}
		 */
		events: {
			'click .llms-settings-group-toggle': 'toggle_group',
		},

		/**
		 * Processed fields data
		 * Allows access by ID without traversing the schema
		 * @type  {Object}
		 */
		fields: {},

		/**
		 * Wrapper Tag name
		 * @type  {String}
		 */
		tagName: 'div',

		/**
		 * Get the underscore template
		 * @type  {[type]}
		 */
		template: wp.template( 'llms-settings-fields-template' ),

		/**
		 * Initialization callback func (renders the element on screen)
		 * @return   void
		 * @since    3.17.0
		 * @version  3.17.0
		 */
		// initialize: function() {},

		/**
		 * Retrieve an array of all editor fields in all groups
		 * @return   array
		 * @since    3.17.1
		 * @version  3.17.1
		 */
		get_editor_fields: function() {
			return _.filter( this.fields, function( field ) {
				return this.is_editor_field( field.type );
			}, this );
		},

		/**
		 * Get settings group data from a model
		 * @return   {[type]}
		 * @since    3.17.0
		 * @version  3.17.0
		 */
		get_groups: function() {

			return this.model.get_settings_fields();

		},

		/**
		 * Determine if a settings group is hidden in localStorage
		 * @param    string   group_id  id of the group
		 * @return   {Boolean}
		 * @since    3.17.0
		 * @version  3.17.0
		 */
		is_group_hidden: function( group_id ) {

			var id = 'llms-' + this.model.get( 'type' ) + '-settings-group--' + group_id;

			if ( 'undefined' !== window.localStorage ) {
				return ( 'hidden' === window.localStorage.getItem( id ) );
			}

			return false;

		},

		/**
		 * Get the switch attribute for a field with switches
		 * @param    obj   field  field data obj
		 * @return   string
		 * @since    3.17.0
		 * @version  3.17.0
		 */
		get_switch_attribute: function( field ) {

			return field.switch_attribute ? field.switch_attribute : field.attribute;

		},

		/**
		 * Determine if a field has a switch
		 * @param    string   type  field type string
		 * @return   {Boolean}
		 * @since    3.17.0
		 * @version  3.17.0
		 */
		has_switch: function( type ) {
			return ( -1 !== type.indexOf( 'switch' ) );
		},

		/**
		 * Determine if a field is a default (text) field
		 * @param    string   type  field type string
		 * @return   {Boolean}
		 * @since    3.17.0
		 * @version  3.17.0
		 */
		is_default_field: function( type ) {

			var types = [ 'audio_embed', 'datepicker', 'number', 'text', 'video_embed' ];
			return ( -1 !== types.indexOf( type.replace( 'switch-', '' ) ) );

		},

		/**
		 * Determine if a field is a WYSIWYG editor field
		 * @param    string   type  field type string
		 * @return   {Boolean}
		 * @since    3.17.1
		 * @version  3.17.1
		 */
		is_editor_field: function( type ) {

			var types = [ 'editor', 'switch-editor' ];
			return ( -1 !== types.indexOf( type.replace( 'switch-', '' ) ) );

		},

		/**
		 * Determine if a switch is enabled for a field
		 * @param    obj   field  field data object
		 * @return   {Boolean}
		 * @since    3.17.0
		 * @version  3.17.6
		 */
		is_switch_condition_met: function( field ) {

			return ( field.switch_on === this.model.get( field.switch_attribute ) );

		},

		/**
		 * Compiles the template and renders the view
		 * @return   self (for chaining)
		 * @since    3.17.0
		 * @version  3.17.1
		 */
		render: function() {

			this.$el.html( this.template( this ) );

			// if editors exist, render them
			_.each( this.get_editor_fields(), function( field ) {
				this.render_editor( field );
			}, this );

			return this;

		},

		/**
		 * Renders an editor field
		 * @param    obj   field  field data object
		 * @return   void
		 * @since    3.17.1
		 * @version  3.17.1
		 */
		render_editor: function( field ) {

			var self = this;

			wp.editor.remove( field.id );
			field.settings.tinymce.setup = function( editor ) {

				var $ed = $( '#' + editor.id ),
					$parent = $ed.closest( '.llms-editable-editor' ),
					$label = $parent.find( '.llms-label' ),
					prop = $ed.attr( 'data-attribute' )

				if ( $label.length ) {
					$label.prependTo( $parent.find( '.wp-editor-tools' ) );
				}

				// save changes to the model via Visual ed
				editor.on( 'change', function( event ) {
					self.model.set( prop, wp.editor.getContent( editor.id ) );
				} );

				// save changes via Text ed
				$ed.on( 'input', function( event ) {
					self.model.set( prop, $ed.val() );
				} );

				// trigger an input on the Text ed when quicktags buttons are clicked
				$parent.on( 'click', '.quicktags-toolbar .ed_button', function() {
					setTimeout( function() {
						$ed.trigger( 'input' );
					}, 10 );
				} );
			};

			wp.editor.initialize( field.id, field.settings );

		},

		/**
		 * Get the HTML for a select field
		 * @param    obj      options    flat or multi-dimensional options object
		 * @param    string   attribute  name of the select field's attribute
		 * @return   string
		 * @since    3.17.0
		 * @version  3.17.2
		 */
		render_select_options: function( options, attribute ) {

			var html = '',
				selected = this.model.get( attribute );

			function option_html( label, val ) {

				return '<option value="' + val + '"' + _.selected( val, selected ) + '>' + label + '</option>';

			}

			_.each( options, function( option, index ) {

				// this will be an key:val object
				if ( 'string' === typeof option ) {
					html += option_html( option, index );
				// either option group or array of key,val objects
				} else if ( 'object' === typeof option ) {
					// option group
					if ( option.label && option.options ) {
						html += '<optgroup label="' + option.label + '">';
						html += this.render_select_options( option.options, attribute );
					} else {
						html += option_html( option.val, option.key );
					}
				}

			}, this );

			return html;

		},

		/**
		 * Setup and fill fields with default data based on field type
		 * @param    obj   orig_field   original field as defined in the settings
		 * @param    int   field_index  index of the field in the current row
		 * @return   obj
		 * @since    3.17.0
		 * @version  3.24.0
		 */
		setup_field: function( orig_field, field_index ) {

			var defaults = {
				classes: [],
				id: _.uniqueId( orig_field.attribute + '_' ),
				input_type: 'text',
				label: '',
				options: {},
				placeholder: '',
				tip: '',
				tip_position: 'top-right',
				settings: {},
			};

			// check the field condition if set
			if ( orig_field.condition && false === _.bind( orig_field.condition, this.model )() ) {
				return false;
			}

			switch ( orig_field.type ) {

				case 'audio_embed':
					defaults.classes.push( 'llms-editable-audio' );
					defaults.placeholder = 'https://';
					defaults.tip = LLMS.l10n.translate( 'Use SoundCloud or Spotify audio URLS.' );
					defaults.input_type = 'url';
				break;

				case 'datepicker':
					defaults.classes.push( 'llms-editable-date' );
				break;

				case 'editor':
				case 'switch-editor':
					var orig_settings = orig_field.settings || {};
					defaults.settings = $.extend( true, wp.editor.getDefaultSettings(), {
						mediaButtons: true,
						tinymce: {
							toolbar1: 'bold,italic,strikethrough,bullist,numlist,blockquote,hr,alignleft,aligncenter,alignright,link,unlink,wp_adv',
							toolbar2: 'formatselect,underline,alignjustify,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help',
						}
					}, orig_settings );
				break;

				case 'number':
					defaults.input_type = 'number';
				break;

				case 'permalink':
					defaults.label = LLMS.l10n.translate( 'Permalink' );
				break;

				case 'video_embed':
					defaults.classes.push( 'llms-editable-video' );
					defaults.placeholder = 'https://';
					defaults.tip = LLMS.l10n.translate( 'Use YouTube, Vimeo, or Wistia video URLS.' );
					defaults.input_type = 'url';
				break;

			}

			if ( this.has_switch( orig_field.type ) ) {
				defaults.switch_on = 'yes';
				defaults.switch_off = 'no';
			}

			var field = _.defaults( _.deepClone( orig_field ), defaults );

			// if options is a function run it
			if ( _.isFunction( field.options ) ) {
				field.options = _.bind( field.options, this.model )();
			}

			// if it's a radio field options values can be submitted as images
			// this will transform those images into <img> html
			if ( -1 !== [ 'radio', 'switch-radio' ].indexOf( orig_field.type ) ) {

				var has_images = false;
				_.each( orig_field.options, function( val, key ) {
					if ( -1 !== val.indexOf( '.png' ) || -1 !== val.indexOf( '.jpg' ) ) {
						field.options[key] = '<span><img src="' + val + '"></span>';
						has_images = true;
					}
				} );
				if ( has_images ) {
					field.classes.push( 'has-images' );
				}

			}

			// transform classes array to a css class string
			if ( field.classes.length ) {
				field.classes = ' ' + field.classes.join( ' ' );
			}

			this.fields[ field.id ] = field;

			return field;

		},

		/**
		 * Determine if toggling a switch select should rerender the view
		 * @param    string   field_type  field type string
		 * @return   boolean
		 * @since    3.17.0
		 * @version  3.17.0
		 */
		should_rerender_on_toggle: function( field_type ) {

			return ( -1 !== field_type.indexOf( 'switch-' ) ) ? 'yes' : 'no';

		},

		/**
		 * Click event for toggling visibility of settings groups
		 * If localStorage is available, persist state
		 * @param    obj   event  js event object
		 * @return   void
		 * @since    3.17.0
		 * @version  3.17.0
		 */
		toggle_group: function( event ) {

			event.preventDefault();

			var $el = $( event.currentTarget ),
				$group = $el.closest( '.llms-model-settings' );

			$group.toggleClass( 'hidden' );

			if ( 'undefined' !== window.localStorage ) {

				var id = $group.attr( 'id' );
				if ( $group.hasClass( 'hidden' ) ) {
					window.localStorage.setItem( id, 'hidden' );
				} else {
					window.localStorage.removeItem( id );
				}

			}

		},

	} ) );

} );

/**
 * Lesson Editor (Sidebar) View
 * @since    3.17.0
 * @version  3.24.0
 */
define( 'Views/LessonEditor',[
		'Views/_Detachable',
		'Views/_Editable',
		'Views/_Trashable',
		'Views/_Subview',
		'Views/SettingsFields'
	], function(
		Detachable,
		Editable,
		Trashable,
		Subview,
		SettingsFields
	) {

	return Backbone.View.extend( _.defaults( {

		/**
		 * Current view state
		 * @type  {String}
		 */
		state: 'default',

		/**
		 * Current Subviews
		 * @type  {Object}
		 */
		views: {
			settings: {
				class: SettingsFields,
				instance: null,
				state: 'default',
			},
		},

		el: '#llms-editor-lesson',

		/**
		 * Events
		 * @type  {Object}
		 */
		events: _.defaults( {}, Detachable.events, Editable.events, Trashable.events ),

		/**
		 * Template function
		 * @type  {[type]}
		 */
		template: wp.template( 'llms-lesson-settings-template' ),

		/**
		 * Init
		 * @param    obj   data  parent template data
		 * @return   void
		 * @since    3.17.0
		 * @version  3.24.0
		 */
		initialize: function( data ) {

			this.model = data.lesson;

			var change_events = [
				'change:date_available',
				'change:drip_method',
				'change:time_available',
			];
			_.each( change_events, function( event ) {
				this.listenTo( this.model, event, this.render );
			}, this );

			// render only the tooltip for points percentage when points change
			this.listenTo( this.model, 'change:points', this.render_points_percentage );

			// when the "has_prerequisite" attr is toggled ON
			// trigger the prereq select object to set the default (first available) prereq for the lesson
			this.listenTo( this.model, 'change:has_prerequisite', function( lesson, val ) {
				if ( 'yes' === val ) {
					this.$el.find( 'select[name="prerequisite"]' ).trigger( 'change' );
				}
			} );

		},

		/**
		 * Render the view
		 * @return   obj
		 * @since    3.17.0
		 * @version  3.24.0
		 */
		render: function() {

			this.$el.html( this.template( this.model ) );

			this.remove_subview( 'settings' );

			this.render_subview( 'settings', {
				el: '#llms-lesson-settings-fields',
				model: this.model,
			} );

			this.init_datepickers();
			this.init_selects();

			this.render_points_percentage();

			return this;

		},

		/**
		 * Render the portion of the template which displays the points percentage
		 * @return   void
		 * @since    3.24.0
		 * @version  3.24.0
		 */
		render_points_percentage: function() {
			this.$el.find( '#llms-model-settings-field--points .llms-editable-input' )
				.addClass( 'tip--top-left' )
				.attr( 'data-tip', this.model.get_points_percentage() );
		}

	}, Detachable, Editable, Trashable, Subview, SettingsFields ) );

} );

/**
 * Single Quiz View
 * @since    3.16.0
 * @version  3.16.0
 */
define( 'Views/Popover',[], function() {

	return Backbone.View.extend( {

		defaults: {
			placement: 'auto',
			// container: document.body,
			width: 'auto',
			trigger: 'manual',
			style: 'light',
			animation: 'pop',
			title: '',
			content: '',
			closeable: false,
			backdrop: false,
			onShow: function( $el ) {},
			onHide: function( $el ) {},
		},

		/**
		 * Wrapper Tag name
		 * @type  {String}
		 */
		tagName: 'div',

		/**
		 * Initialization callback func (renders the element on screen)
		 * @return   void
		 * @since    3.14.1
		 * @version  3.14.1
		 */
		initialize: function( data ) {

			if ( this.$el.length ) {
				this.defaults.container = this.$el.parent();
			}

			this.args = _.defaults( data.args, this.defaults );
			this.render();

		},

		/**
		 * Compiles the template and renders the view
		 * @return   self (for chaining)
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		render: function() {

			this.$el.webuiPopover( this.args );
			return this;

		},

		/**
		 * Hide the popover
		 * @return   self (for chaining)
		 * @since    3.16.0
		 * @version  3.16.12
		 */
		hide: function() {

			this.$el.webuiPopover( 'hide' );
			return this;

		},

		/**
		 * Show the popover
		 * @return   self (for chaining)
		 * @since    3.16.0
		 * @version  3.16.12
		 */
		show: function() {

			this.$el.webuiPopover( 'show' );
			return this;

		},

	} );

} );

/**
 * Post Popover Serach content View
 * @since    3.16.0
 * @version  3.17.0
 */
define( 'Views/PostSearch',[], function() {

	return Backbone.View.extend( {

		/**
		 * DOM Events
		 * @type     obj
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		events: {
			'select2:select': 'add_post',
		},

		/**
		 * Wrapper Tag name
		 * @type  {String}
		 */
		tagName: 'select',

		/**
		 * Initializer
		 * @param    obj   data  customize the search box with data
		 * @return   void
		 * @since    3.16.12
		 * @version  3.16.12
		 */
		initialize: function( data ) {

			this.post_type = data.post_type;
			this.searching_message = data.searching_message || LLMS.l10n.translate( 'Searching...' );

		},

		/**
		 * Select event, adds the existing lesson to the course
		 * @param    obj   event  select2:select event object
		 * @since    3.16.0
		 * @version  3.17.0
		 */
		add_post: function( event ) {

			var type = this.$el.attr( 'data-post-type' );

			Backbone.pubSub.trigger( type.replace( 'llms_', '' ) + '-search-select', event.params.data, event );
			this.$el.val( null ).trigger( 'change' );

		},

		/**
		 * Render the section
		 * Initalizes a new collection and views for all lessons in the section
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.12
		 */
		render: function() {
			var self = this;
			setTimeout( function () {
				self.$el.llmsSelect2( {
					ajax: {
						dataType: 'JSON',
						delay: 250,
						method: 'POST',
						url: window.ajaxurl,
						data: function( params ) {
							return {
								action: 'llms_builder',
								action_type: 'search',
								course_id: window.llms_builder.course.id,
								post_type: self.post_type,
								term: params.term,
								page: params.page,
								_ajax_nonce: wp_ajax_data.nonce,
							};
						},
						// error: function( xhr, status, error ) {
						// 	console.log( status, error );
						// },
					},
					dropdownParent: $( '.wrap.lifterlms.llms-builder' ),
					// don't escape html from render_result
					escapeMarkup: function( markup ) {
						return markup;
					},
					placeholder: self.searching_message,
					templateResult: self.render_result,
					width: '100%',
				} );
				self.$el.attr( 'data-post-type', self.post_type );
			}, 0 );
			return this;

		},

		/**
		 * Render a nicer UI for each search result in the in the Select2 search results
		 * @param    object   res  result data
		 * @return   string
		 * @since    3.16.0
		 * @version  3.16.12
		 */
		render_result: function( res ) {

			var $html = $( '<div class="llms-existing-lesson-result" />' );

			if ( res.loading ) {
				return $html.append( res.text );
			}

			var $side = $( '<aside class="llms-existing-action" />' ),
				$main = $( '<div class="llms-existing-info" />' );
				icon = ( 'attach' === res.action ) ? 'paperclip' : 'clone',
				text = ( 'attach' === res.action ) ? LLMS.l10n.translate( 'Attach' ) : LLMS.l10n.translate( 'Clone' );

			$side.append( '<i class="fa fa-' + icon + '" aria-hidden="true"></i><small>' + text + '</small>' );

			$main.append( '<h4>' + res.data.title + '</h4>' );
			$main.append( '<h5>' + LLMS.l10n.translate( 'ID' ) + ': <em>' + res.data.id + '</em></h5>' );

			_.each( res.parents, function( parent ) {
				$main.append( '<h5>' + parent + '</em></h5>' );
			} );

			return $html.append( $side ).append( $main );

		},

	} );

} );

/**
 * Single Lesson View
 * @since    3.16.0
 * @version  3.27.0
 */
define( 'Views/QuestionType',[ 'Views/Popover', 'Views/PostSearch' ], function( Popover, QuestionSearch ) {

	return Backbone.View.extend( {

		/**
		 * HTML class names
		 * @type  {String}
		 */
		className: 'llms-question-type',

		events: {
			'click .llms-add-question': 'add_question',
		},

		/**
		 * HTML element wrapper ID attribute
		 * @return   string
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		id: function() {
			return 'llms-question-type-' + this.model.id;
		},

		/**
		 * Wrapper Tag name
		 * @type  {String}
		 */
		tagName: 'li',

		/**
		 * Get the underscore template
		 * @type  {[type]}
		 */
		template: wp.template( 'llms-question-type-template' ),

		/**
		 * Initialization callback func (renders the element on screen)
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		initialize: function() {

			this.render();

		},

		/**
		 * Compiles the template and renders the view
		 * @return   self (for chaining)
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		render: function() {
			this.$el.html( this.template( this.model ) );
			return this;
		},

		/**
		 * Add a question of the selected type to the current quiz
		 * @return   void
		 * @since    3.16.0
		 * @version  3.27.0
		 */
		add_question: function() {

			if ( 'existing' === this.model.get( 'id' ) ) {
				this.add_existing_question_click();
			} else {
				this.add_new_question();
			}

		},

		/**
		 * Add a new question to the quiz
		 * @return  void
		 * @since   3.27.0
		 * @version 3.27.0
		 */
		add_existing_question_click: function() {

			var pop = new Popover( {
				el: '#llms-add-question--existing',
				args: {
					backdrop: true,
					closeable: true,
					container: '#llms-builder-sidebar',
					dismissible: true,
					placement: 'top-left',
					width: 'calc( 100% - 40px )',
					offsetLeft: 250,
					offsetTop: 60,
					title: LLMS.l10n.translate( 'Add Existing Question' ),
					content: new QuestionSearch( {
						post_type: 'llms_question',
						searching_message: LLMS.l10n.translate( 'Search for existing questions...' ),
					} ).render().$el,
				}
			} );

			pop.show();
			Backbone.pubSub.on( 'question-search-select', function( event ) {
				pop.hide();
				this.add_existing_question( event );
			}, this );

		},

		add_existing_question: function( event ) {

			var question = event.data;

			if ( 'clone' === event.action ) {
				question = _.prepareQuestionObjectForCloning( question );
			} else {
				question._forceSync = true;
			}

			question._expanded = true;
			this.quiz.add_question( question );

			this.quiz.trigger( 'new-question-added' );

		},

		/**
		 * Add a new question to the quiz
		 * @return  void
		 * @since   3.27.0
		 * @version 3.27.0
		 */
		add_new_question: function() {

			this.quiz.add_question( {
				_expanded: true,
				choices: this.model.get( 'default_choices' ) ? this.model.get( 'default_choices' ) : null,
				question_type: this.model,
			} );

			this.quiz.trigger( 'new-question-added' );

		},

		// filter: function( term ) {

		// 	var words = this.model.get_keywords().map( function( word ) {
		// 		return word.toLowerCase();
		// 	} );

		// 	term = term.toLowerCase();

		// 	if ( -1 === words.indexOf( term ) ) {
		// 		this.$el.addClass( 'filtered' );
		// 	} else {
		// 		this.$el.removeClass( 'filtered' );
		// 	}

		// },

		// clear_filter: function() {
		// 	this.$el.removeClass( 'filtered' );
		// }

	} );

} );

/**
 * Quiz question bank view
 * @since    3.16.0
 * @version  3.16.0
 */
define( 'Views/QuestionBank',[ 'Views/QuestionType' ], function( QuestionView ) {

	return Backbone.CollectionView.extend( {

		className: 'llms-question',

		/**
		 * Parent element
		 * @type  {String}
		 */
		el: '#llms-question-bank',

		/**
		 * Section model
		 * @type  {[type]}
		 */
		modelView: QuestionView,

		/**
		 * Are sections selectable?
		 * @type  {Bool}
		 */
		selectable: false,

		/**
		 * Are sections sortable?
		 * @type  {Bool}
		 */
		sortable: false,

	} );

} );

/**
 * Single Question Choice View
 * @since    3.16.0
 * @version  3.16.0
 */
define( 'Views/QuestionChoice',[ 'Views/_Editable', ], function( Editable ) {

	return Backbone.View.extend( _.defaults( {

		/**
		 * HTML class names
		 * @type  {String}
		 */
		className: 'llms-question-choice',

		events: _.defaults( {
			'change input[name="correct"]': 'toggle_correct',
			'click .llms-action-icon[href="#llms-add-choice"]': 'add_choice',
			'click .llms-action-icon[href="#llms-del-choice"]': 'del_choice',
		}, Editable.events ),

		/**
		 * HTML element wrapper ID attribute
		 * @return   string
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		id: function() {
			return 'llms-question-choice-' + this.model.id;
		},

		/**
		 * Wrapper Tag name
		 * @type  {String}
		 */
		tagName: 'li',

		/**
		 * Get the underscore template
		 * @type  {[type]}
		 */
		template: wp.template( 'llms-question-choice-template' ),

		/**
		 * Initialization callback func (renders the element on screen)
		 * @return   void
		 * @since    3.14.1
		 * @version  3.14.1
		 */
		initialize: function() {

			this.render();

			this.listenTo( this.model.collection, 'add', this.maybe_disable_buttons );
			this.listenTo( this.model, 'change', this.render );

			if ( 'image' === this.model.get( 'choice_type' ) ) {
				this.listenTo( this.model.get( 'choice' ), 'change', this.render );
			}

		},

		/**
		 * Compiles the template and renders the view
		 * @return   self (for chaining)
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		render: function() {
			this.$el.html( this.template( this.model ) );
			return this;
		},

		/**
		 * Add a new choice to the current choice list
		 * Adds *after* the clicked choice
		 * @param    obj   event  JS event object
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		add_choice: function( event ) {

			event.stopPropagation();
			event.preventDefault();

			var index = this.model.collection.indexOf( this.model );
			this.model.collection.parent.add_choice( {}, {
				at: index + 1,
			} );

		},

		/**
		 * Delete the choice from the choice list & ensure there's at least one correct choice
		 * @param    obj   event  js event obj
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		del_choice: function( event ) {

			event.preventDefault();
			Backbone.pubSub.trigger( 'model-trashed', this.model );
			this.model.collection.remove( this.model );

		},

		/**
		 * When the correct answer input changes sync status to model
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		toggle_correct: function() {

			var correct = this.$el.find( 'input[name="correct"]' ).is( ':checked' );
			this.model.set( 'correct', correct );
			this.model.collection.trigger( 'correct-update', this.model );

		},

	}, Editable ) );

} );

/**
 * Quiz question bank view
 * @since    3.16.0
 * @version  3.16.0
 */
define( 'Views/QuestionChoiceList',[ 'Views/QuestionChoice' ], function( ChoiceView ) {

	return Backbone.CollectionView.extend( {

		className: 'llms-quiz-questions',

		/**
		 * Choice model view
		 * @type  {[type]}
		 */
		modelView: ChoiceView,

		/**
		 * Enable keyboard events
		 * @type  {Bool}
		 */
		processKeyEvents: false,

		/**
		 * Are sections selectable?
		 * @type  {Bool}
		 */
		selectable: false,

		/**
		 * Are sections sortable?
		 * @type  {Bool}
		 */
		sortable: true,

		sortableOptions: {
			axis: false,
			// connectWith: '.llms-lessons',
			cursor: 'move',
			handle: '.llms-choice-id',
			items: '.llms-question-choice',
			placeholder: 'llms-question-choice llms-sortable-placeholder',
		},

		sortable_start: function( model ) {
			this.$el.addClass( 'dragging' );
		},

		sortable_stop: function( model ) {
			this.$el.removeClass( 'dragging' );
		},

	} );

} );

/**
 * Single Question View
 * @since    3.16.0
 * @version  3.27.0
 */
define( 'Views/Question',[
		'Views/_Detachable',
		'Views/_Editable',
		'Views/QuestionChoiceList'
	], function(
		Detachable,
		Editable,
		ChoiceListView
	) {

	return Backbone.View.extend( _.defaults( {

		/**
		 * Generate CSS classes for the question
		 * @return   string
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		className: function() {
			return 'llms-question qtype--' + this.model.get( 'question_type' ).get( 'id' );
		},

		events: _.defaults( {
			'click .clone--question': 'clone',
			'click .delete--question': 'delete',
			'click .expand--question': 'expand',
			'click .collapse--question': 'collapse',
			'change input[name="question_points"]': 'update_points',
		}, Detachable.events, Editable.events ),

		/**
		 * HTML element wrapper ID attribute
		 * @return   string
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		id: function() {
			return 'llms-question-' + this.model.id;
		},

		/**
		 * Wrapper Tag name
		 * @type  {String}
		 */
		tagName: 'li',

		/**
		 * Get the underscore template
		 * @type  {[type]}
		 */
		template: wp.template( 'llms-question-template' ),

		/**
		 * Initialization callback func (renders the element on screen)
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		initialize: function() {

			var change_events = [
				'change:_expanded',
				'change:menu_order',
			];
			_.each( change_events, function( event ) {
				this.listenTo( this.model, event, this.render );
			}, this );

			this.listenTo( this.model.get( 'image' ), 'change', this.render );

			this.listenTo( this.model.get_parent(), 'change:_points', this.render_points_percentage );

			this.on( 'multi_choices_toggle', this.multi_choices_toggle, this );

			Backbone.pubSub.on( 'del-question-choice', this.del_choice, this );

		},

		/**
		 * Compiles the template and renders the view
		 * @return   self (for chaining)
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		render: function() {

			this.$el.html( this.template( this.model ) );

			if ( this.model.get( 'question_type').get( 'choices' ) ) {

				this.choiceListView = new ChoiceListView( {
					el: this.$el.find( '.llms-question-choices' ),
					collection: this.model.get( 'choices' ),
				} );
				this.choiceListView.render();
				this.choiceListView.on( 'sortStart', this.choiceListView.sortable_start );
				this.choiceListView.on( 'sortStop', this.choiceListView.sortable_stop );

			}

			if ( 'group' === this.model.get( 'question_type' ).get( 'id' ) ) {

				var self = this;
				setTimeout( function() {
					self.questionListView = self.collectionListView.quiz.get_question_list( {
						el: self.$el.find( '.llms-quiz-questions' ),
						collection: self.model.get( 'questions' ),
					} );
					self.questionListView.render();
					self.questionListView.on( 'sortStart', self.questionListView.sortable_start );
					self.questionListView.on( 'sortStop', self.questionListView.sortable_stop );
				}, 1 );

			}

			if ( this.model.get( 'description_enabled' ) ) {
				this.init_editor( 'question-desc--' + this.model.get( 'id' ) );
			}

			if ( this.model.get( 'clarifications_enabled' ) ) {
				this.init_editor( 'question-clarifications--' + this.model.get( 'id' ), {
					mediaButtons: false,
					tinymce: {
						toolbar1: 'bold,italic,strikethrough,bullist,numlist,alignleft,aligncenter,alignright',
						toolbar2: '',
						setup: _.bind( this.on_editor_ready, this ),
					}
				} );
			}

			this.init_formatting_els();
			this.init_selects();

			return this;
		},

		/**
		 * rerender points percentage when question points are updated
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		render_points_percentage: function() {

			this.$el.find( '.llms-question-points' ).attr( 'data-tip', this.model.get_points_percentage() );

		},

		/**
		 * Click event to duplicate a question within a quiz
		 * @param    obj   event  js event object
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		clone: function( event ) {

			event.stopPropagation();
			event.preventDefault();
			this.model.collection.add( this._get_question_clone( this.model ) );

		},

		/**
		 * Recursive clone function which will correctly clone children of a question
		 * @param    obj   question  queston model
		 * @return   obj
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		_get_question_clone: function( question ) {

			// create a duplicate
			var clone = _.clone( question.attributes );

			// remove id (we want the duplicate to have a temp id)
			delete clone.id;

			clone.parent_id = question.get( 'id' );

			// set the question type ID
			clone.question_type = question.get( 'question_type' ).get( 'id' );

			// clone the image attributes seperately
			clone.image = _.clone( question.get( 'image' ).attributes );

			// if it has choices clone all the choices
			if ( question.get( 'choices' ) ) {

				clone.choices = [];

				question.get( 'choices' ).each( function ( choice ) {

					var choice_clone = _.clone( choice.attributes );
					delete choice_clone.id;
					delete choice_clone.question_id;

					clone.choices.push( choice_clone );

				} );

			}

			if ( 'group' === question.get( 'question_type' ).get( 'id' ) ) {

				clone.questions = [];
				question.get( 'questions' ).each( function( child ) {
					clone.questions.push( this._get_question_clone( child ) );
				}, this );

			}

			return clone;

		},

		/**
		 * Collapse a question and hide it's settings
		 * @param obj event js event obj.
		 * @return   void
		 * @since    3.16.0
		 * @version  3.27.0
		 */
		collapse: function( event ) {

			if ( event ) {
				event.preventDefault();
			}

			this.model.set( '_expanded', false );

		},

		/**
		 * Delete the question from a quiz / question group
		 * @param    obj   event  js event object
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		delete: function( event ) {

			event.preventDefault();

			if ( window.confirm( LLMS.l10n.translate( 'Are you sure you want to delete this question?' ) ) ) {

				this.model.collection.remove( this.model );
				Backbone.pubSub.trigger( 'model-trashed', this.model );

			}

		},

		/**
		 * Click event to reveal a question's settings & choices
		 * @param obj event js event obj.
		 * @return   void
		 * @since    3.16.0
		 * @version  3.27.0
		 */
		expand: function( event ) {

			if ( event ) {
				event.preventDefault();
			}

			this.model.set( '_expanded', true );

		},

		/**
		 * When toggling multiple correct answers *off* remove all correct choices except the first correct choice in the list
		 * @param    string   val  value of the question's `multi_choice` attr [yes|no]
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		multi_choices_toggle: function( val ) {

			if ( 'yes' === val ) {
				return;
			}

			this.model.get( 'choices' ).update_correct( _.first( this.model.get( 'choices' ).get_correct() ) );

		},

		/**
		 * Update the model's points when the value of the points input is updated
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		update_points: function() {

			this.model.set( 'points', this.$el.find( 'input[name="question_points"]' ).val() * 1 );

		}

	}, Detachable, Editable ) );

} );

/**
 * Quiz question bank view
 * @since    3.16.0
 * @version  3.16.0
 */
define( 'Views/QuestionList',[ 'Views/Question' ], function( QuestionView ) {

	return Backbone.CollectionView.extend( {

		className: 'llms-quiz-questions',

		/**
		 * Parent element
		 * @type  {String}
		 */
		// el: '#llms-quiz-questions',

		/**
		 * Section model
		 * @type  {[type]}
		 */
		modelView: QuestionView,

		/**
		 * Enable keyboard events
		 * @type  {Bool}
		 */
		processKeyEvents: false,

		/**
		 * Are sections selectable?
		 * @type  {Bool}
		 */
		selectable: false,

		/**
		 * Are sections sortable?
		 * @type  {Bool}
		 */
		sortable: true,

		sortableOptions: {
			axis: false,
			connectWith: '.llms-quiz-questions',
			cursor: 'move',
			handle: '.llms-data-stamp',
			items: '.llms-question',
			placeholder: 'llms-question llms-sortable-placeholder',
		},

		/**
		 * Highlight drop areas when dragging starts
		 * @param    obj   model  model being sorted
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		sortable_start: function( model ) {
			var selector = 'group' === model.get( 'question_type' ).get( 'id' ) ? '.llms-editor-tab > .llms-quiz-questions' : '.llms-quiz-questions';
			$( selector ).addClass( 'dragging' );
		},

		/**
		 * Remove highlights when dragging stops
		 * @param    obj   model  model being sorted
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		sortable_stop: function() {
			$( '.llms-quiz-questions' ).removeClass( 'dragging' );
		},

		/**
		 * Overrides receive to ensure that question groups can't be moved into queston groups
		 * @param    obj   event  js event object
		 * @param    obj   ui     jQuery UI Sortable ui object
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		_receive : function( event, ui ) {

			event.stopPropagation();

			// prevent moving a question group into a question group
			if ( ui.item.hasClass( 'qtype--group' ) && $( event.target ).closest( '.qtype--group' ).length ) {;
				ui.sender.sortable( 'cancel' );
				return;
			}

			var senderListEl = ui.sender;
			var senderCollectionListView = senderListEl.data( "view" );
			if( ! senderCollectionListView || ! senderCollectionListView.collection ) return;

			var newIndex = this._getContainerEl().children().index( ui.item );
			var modelReceived = senderCollectionListView.collection.get( ui.item.attr( "data-model-cid" ) );
			senderCollectionListView.collection.remove( modelReceived );
			this.collection.add( modelReceived, { at : newIndex } );
			modelReceived.collection = this.collection; // otherwise will not get properly set, since modelReceived.collection might already have a value.
			this.setSelectedModel( modelReceived );
		},

		/**
		 * Override to allow manipulatino of placeholder element
		 * @param    {[type]}   event  [description]
		 * @param    {[type]}   ui     [description]
		 * @return   {[type]}
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		_sortStart : function( event, ui ) {

			var modelBeingSorted = this.collection.get( ui.item.attr( "data-model-cid" ) );

			ui.placeholder.addClass( 'qtype--' + modelBeingSorted.get( 'question_type' ).get( 'id' ) );

			if( this._isBackboneCourierAvailable() )
				this.spawn( "sortStart", { modelBeingSorted : modelBeingSorted } );
			else this.trigger( "sortStart", modelBeingSorted );
		},

		/**
		 * Overloads the function from Backbone.CollectionView core because it doesn't send stop events
		 * if moving from one sortable to another... :-(
		 * @param    obj   event  js event object
		 * @param    obj   ui     jQuery UI object
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		_sortStop : function( event, ui ) {

			event.stopPropagation();

			var modelBeingSorted = this.collection.get( ui.item.attr( 'data-model-cid' ) ),
				modelViewContainerEl = this._getContainerEl(),
				newIndex = modelViewContainerEl.children().index( ui.item );

			if ( newIndex == -1 && modelBeingSorted ) {
				this.collection.remove( modelBeingSorted );
			}

			this._reorderCollectionBasedOnHTML();
			this.updateDependentControls();

			if( this._isBackboneCourierAvailable() ) {
				this.spawn( 'sortStop', { modelBeingSorted : modelBeingSorted, newIndex : newIndex } );
			} else {
				this.trigger( 'sortStop', modelBeingSorted, newIndex );
			}

		},

	} );

} );

/**
 * Single Quiz View
 * @since    3.16.0
 * @version  3.24.0
 */
define( 'Views/Quiz',[
		'Models/Quiz',
		'Views/Popover',
		'Views/PostSearch',
		'Views/QuestionBank',
		'Views/QuestionList',
		'Views/SettingsFields',
		'Views/_Detachable',
		'Views/_Editable',
		'Views/_Subview',
		'Views/_Trashable'
	], function(
		QuizModel,
		Popover,
		PostSearch,
		QuestionBank,
		QuestionList,
		SettingsFields,
		Detachable,
		Editable,
		Subview,
		Trashable
	) {

	return Backbone.View.extend( _.defaults( {

		/**
		 * Current view state
		 * @type  {String}
		 */
		state: 'default',

		/**
		 * Current Subviews
		 * @type  {Object}
		 */
		views: {
			settings: {
				class: SettingsFields,
				instance: null,
				state: 'default',
			},
			bank: {
				class: QuestionBank,
				instance: null,
				state: 'default',
			},
			list: {
				class: QuestionList,
				instance: null,
				state: 'default',
			},
		},

		el: '#llms-editor-quiz',

		/**
		 * Events
		 * @type  {Object}
		 */
		events: _.defaults( {
			'click #llms-existing-quiz': 'add_existing_quiz_click',
			'click #llms-new-quiz': 'add_new_quiz',
			'click #llms-show-question-bank': 'show_tools',
			'click .bulk-toggle': 'bulk_toggle',
			// 'keyup #llms-question-bank-filter': 'filter_question_types',
			// 'search #llms-question-bank-filter': 'filter_question_types',
		}, Detachable.events, Editable.events, Trashable.events ),

		/**
		 * Wrapper Tag name
		 * @type  {String}
		 */
		tagName: 'div',

		/**
		 * Get the underscore template
		 * @type  {[type]}
		 */
		template: wp.template( 'llms-quiz-template' ),

		/**
		 * Initialization callback func (renders the element on screen)
		 * @return   void
		 * @since    3.16.0
		 * @version  3.19.2
		 */
		initialize: function( data ) {

			this.lesson = data.lesson;

			// initialize the model if the quiz is enabled or it's disabled but we still have data for a quiz
			if ( 'yes' === this.lesson.get( 'quiz_enabled' ) || ! _.isEmpty( this.lesson.get( 'quiz' ) ) ) {

				this.model = this.lesson.get( 'quiz' );

				/**
				 * @todo  this is a terrilbe terrible patch
				 *        I've spent nearly 3 days trying to figure out how to not use this line of code
				 *        ISSUE REPRODUCTION:
				 *        Open course builder
				 *        Open a lesson (A) and add a quiz
				 *        Switch to a new lesson (B)
				 *        Add a new quiz
				 *        Return to lesson A and the quizzes parent will be set to LESSON B
				 *        This will happen for *every* quiz in the builder...
				 *        Adding this set_parent on init guarantees that the quizzes correct parent is set
				 *        after adding new quizzes to other lessons
				 *        it's awful and it's gross...
				 *        I'm confused and tired and going to miss release dates again because of it
				 */
				this.model.set_parent( this.lesson );

				this.listenTo( this.model, 'change:_points', this.render_points );

			}

			this.on( 'model-trashed', this.on_trashed );

		},

		/**
		 * Compiles the template and renders the view
		 * @return   self (for chaining)
		 * @since    3.16.0
		 * @version  3.19.2
		 */
		render: function() {

			this.$el.html( this.template( this.model ) );

			// render the quiz builder
			if ( this.model ) {

				// don't allow interaction until questions are lazy loaded
				LLMS.Spinner.start( this.$el );

				this.render_subview( 'settings', {
					el: '#llms-quiz-settings-fields',
					model: this.model,
				} );

				this.init_datepickers();
				this.init_selects();

				this.render_subview( 'bank', {
					collection: window.llms_builder.questions,
				} );

				var last_group = null,
					group = null;
				// let all the question types reference the quiz for adding questions quickly
				this.get_subview( 'bank' ).instance.viewManager.each( function( view ) {

					view.quiz = this.model;

					group = view.model.get( 'group' ).name;

					if ( last_group !== group ) {
						last_group = group;
						view.$el.before( '<li class="llms-question-bank-header"><h4>' + group + '</h4></li>' );
					}

				}, this );

				this.model.load_questions( _.bind( function( err ) {

					if ( err ) {
						alert( LLMS.l10n.translate( 'An error occurred while trying to load the questions. Please refresh the page and try again.' ) );
						return this;
					}

					LLMS.Spinner.stop( this.$el );
					this.render_subview( 'list', {
						el: '#llms-quiz-questions',
						collection: this.model.get( 'questions' ),
					} );
					var list = this.get_subview( 'list' ).instance;
					list.quiz = this;
					list.collection.on( 'add', function() {
						list.collection.trigger( 'reorder' );
					}, this );
					list.on( 'sortStart', list.sortable_start );
					list.on( 'sortStop', list.sortable_stop );

				}, this ) );

				this.model.on( 'new-question-added', function() {
					var $questions = this.$el.find( '#llms-quiz-questions' );
					$questions.animate( { scrollTop: $questions.prop( 'scrollHeight' ) }, 200 );
				}, this );

			}

			return this;

		},

		/**
		 * On quiz points update, update the value of the Total Points area in the header
		 * @param    obj   quiz    Instance of the quiz model
		 * @param    int   points  Updated number of points
		 * @return   void
		 * @since    3.17.6
		 * @version  3.17.6
		 */
		render_points: function( quiz, points ) {

			this.$el.find( '#llms-quiz-total-points' ).text( points );

		},

		/**
		 * Bulk expand / collapse question buttons
		 * @param    obj   event  js event object
		 * @return   obj
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		bulk_toggle: function( event ) {

			var expanded = ( 'expand' === $( event.target ).attr( 'data-action' ) );

			this.model.get( 'questions' ).each( function( question ) {
				question.set( '_expanded', expanded );
			} );

		},

		/**
		 * Adds a new quiz to a lesson which currently has no quiz associated wlith it
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		add_new_quiz: function() {

			var quiz = this.lesson.get( 'quiz' );
			if ( _.isEmpty( quiz ) ) {
				quiz = this.lesson.add_quiz();
			} else {
				this.lesson.set( 'quiz_enabled', 'yes' );
			}

			this.model = quiz;
			this.render();

		},


		/**
		 * Add an existing quiz to a lesson
		 * @param    obj  event  js event object
		 * @since    3.16.0
		 * @version  3.24.0
		 */
		add_existing_quiz: function( event ) {

			this.post_search_popover.hide();

			var quiz = event.data;

			if ( 'clone' === event.action ) {

				quiz = _.prepareQuizObjectForCloning( quiz );

			} else {

				quiz._forceSync = true;

			}

			delete quiz.lesson_id;

			this.lesson.add_quiz( quiz );
			this.model = this.lesson.get( 'quiz' );
			this.render();

		},

		/**
		 * Open add existing quiz popover
		 * @param    obj   event  JS event object
		 * @return   void
		 * @since    3.16.12
		 * @version  3.16.12
		 */
		add_existing_quiz_click: function( event ) {

			event.preventDefault();

			this.post_search_popover = new Popover( {
				el: '#llms-existing-quiz',
				args: {
					backdrop: true,
					closeable: true,
					container: '.wrap.lifterlms.llms-builder',
					dismissible: true,
					placement: 'left',
					width: 480,
					title: LLMS.l10n.translate( 'Add Existing Quiz' ),
					content: new PostSearch( {
						post_type: 'llms_quiz',
						searching_message: LLMS.l10n.translate( 'Search for existing quizzes...' ),
					} ).render().$el,
					onHide: function() {
						Backbone.pubSub.off( 'quiz-search-select' );
					},
				}
			} );

			this.post_search_popover.show();
			Backbone.pubSub.once( 'quiz-search-select', this.add_existing_quiz, this );

		},

		// filter_question_types: _.debounce( function( event ) {

		// 	var term = $( event.target ).val();

		// 	this.QuestionBankView.viewManager.each( function( view ) {
		// 		if ( ! term ) {
		// 			view.clear_filter();
		// 		} else {
		// 			view.filter( term );
		// 		}
		// 	} );


		// }, 300 ),

		/**
		 * Callback function when the quiz has been deleted
		 * @param    object   quiz  Quiz Model
		 * @return   void
		 * @since    3.16.6
		 * @version  3.16.6
		 */
		on_trashed: function( quiz ) {

			this.lesson.set( 'quiz_enabled', 'no' );
			this.lesson.set( 'quiz', '' );

			delete this.model;

			this.render();

		},

		/**
		 * "Add Question" button click event
		 * Creates a popover with question type list interface
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		show_tools: function() {

			// create popover
			var pop = new Popover( {
				el: '#llms-show-question-bank',
				args: {
					backdrop: true,
					closeable: true,
					container: '#llms-builder-sidebar',
					dismissible: true,
					placement: 'top-left',
					width: 'calc( 100% - 40px )',
					title: LLMS.l10n.translate( 'Add a Question' ),
					url: '#llms-quiz-tools',
				}
			} );

			// show it
			pop.show();

			// if a question is added, hide the popover
			this.model.on( 'new-question-added', function() {
				pop.hide();
			} );

		},

		get_question_list: function( options ) {
			return new QuestionList( options );
		}

	}, Detachable, Editable, Subview, Trashable, SettingsFields ) );

} );

/**
 * Single Assignment View
 * @since    3.17.0
 * @version  3.17.7
 */
define( 'Views/Assignment',[
		'Views/Popover',
		'Views/PostSearch',
		'Views/_Detachable',
		'Views/_Editable',
		'Views/_Trashable',
		'Views/_Subview',
		'Views/SettingsFields'
	], function(
		Popover,
		PostSearch,
		Detachable,
		Editable,
		Trashable,
		Subview,
		SettingsFields
	) {

	return Backbone.View.extend( _.defaults( {

		/**
		 * Current view state
		 * @type  {String}
		 */
		state: 'default',

		/**
		 * Current Subviews
		 * @type  {Object}
		 */
		views: {
			settings: {
				class: SettingsFields,
				instance: null,
				state: 'default',
			},
		},

		el: '#llms-editor-assignment',

		/**
		 * DOM Events
		 * @return   obj
		 * @since    3.17.1
		 * @version  3.17.1
		 */
		events: function() {
			var addon_events = this.is_addon_available() ? window.llms_builder.assignments.get_view_events() : {};
			return _.defaults( {
				'click #llms-existing-assignment': 'add_existing_assignment_click',
				'click #llms-new-assignment': 'add_new_assignment',
			}, Detachable.events, Editable.events, Trashable.events, addon_events );
		},

		/**
		 * Wrapper Tag name
		 * @type  {String}
		 */
		tagName: 'div',

		/**
		 * Get the underscore template
		 * @type  {[type]}
		 */
		template: wp.template( 'llms-assignment-template' ),

		/**
		 * Initialization callback func (renders the element on screen)
		 * @return   void
		 * @since    3.17.0
		 * @version  3.17.2
		 */
		initialize: function( data ) {

			this.lesson = data.lesson;

			// initialize the model if the assignment is enabled or it's disabled but we still have data for a assignment
			if ( 'yes' === this.lesson.get( 'assignment_enabled' ) || ! _.isEmpty( this.lesson.get( 'assignment' ) ) ) {

				this.model = this.lesson.get( 'assignment' );

				/**
				 * @todo  this is a terrilbe terrible patch
				 *        I've spent nearly 3 days trying to figure out how to not use this line of code
				 *        ISSUE REPRODUCTION:
				 *        Open course builder
				 *        Open a lesson (A) and add a assignment
				 *        Switch to a new lesson (B)
				 *        Add a new assignment
				 *        Return to lesson A and the assignment's parent will be set to LESSON B
				 *        This will happen for *every* assignment in the builder...
				 *        Adding this set_parent on init guarantees that the assignment's correct parent is set
				 *        after adding new assignment's to other lessons
				 *        it's awful and it's gross...
				 *        I'm confused and tired and going to miss release dates again because of it
				 */
				this.model.set_parent( this.lesson );

			}

			this.on( 'model-trashed', this.on_trashed );

		},

		/**
		 * Compiles the template and renders the view
		 * @return   self (for chaining)
		 * @since    3.17.0
		 * @version  3.17.7
		 */
		render: function() {

			this.$el.html( this.template( this.model ) );

			if ( this.model && this.is_addon_available() ) {

				this.stopListening( this.model, 'change:assignment_type', this.render );

				this.render_subview( 'settings', {
					el: '#llms-assignment-settings-fields',
					model: this.model,
				} );

				// this.init_datepickers();
				this.init_selects();

				window.llms_builder.assignments.render_editor( this );

				this.listenTo( this.model, 'change:assignment_type', this.render );

			}

			return this;

		},

		/**
		 * Adds a new assignment to a lesson which currently has no assignment associated wlith it
		 * @return   void
		 * @since    3.17.0
		 * @version  3.17.0
		 */
		add_new_assignment: function() {

			if ( this.is_addon_available() ) {

				this.model = window.llms_builder.assignments.get_assignment( {
					/* translators: %1$s = associated lesson title */
					title: LLMS.l10n.replace( '%1$s Assignment', {
						'%1$s': this.lesson.get( 'title' ),
					} ),
					lesson_id: this.lesson.get( 'id' ),
				} );

				this.lesson.set( 'assignment_enabled', 'yes' );
				this.lesson.set( 'assignment', this.model );

				this.render();

			} else {

				this.show_ad_popover( '#llms-new-assignment' );

			}


		},

		/**
		 * When an assignment is selected from the post select popover
		 * instantiate it and add it to the current lesson
		 * @param    object   event  data from the select2 select event
		 * @since    3.17.0
		 * @version  3.17.0
		 */
		add_existing_assignment: function( event ) {

			this.post_search_popover.hide();

			var assignment = event.data;

			if ( 'clone' === event.action ) {

				delete assignment.id;

			} else {

				assignment._forceSync = true;

			}

			assignment.lesson_id = this.lesson.get( 'id' )

			assignment = window.llms_builder.construct.get_model( 'Assignment', assignment );

			this.lesson.set( 'assignment_enabled', 'yes' );
			this.lesson.set( 'assignment', assignment );
			this.model = assignment;

			this.render();

		},

		/**
		 * Open add existing assignment popover
		 * @param    obj   event  JS event object
		 * @return   void
		 * @since    3.17.0
		 * @version  3.17.0
		 */
		add_existing_assignment_click: function( event ) {

			event.preventDefault();

			if ( this.is_addon_available() ) {

				this.post_search_popover = new Popover( {
					el: '#llms-existing-assignment',
					args: {
						backdrop: true,
						closeable: true,
						container: '.wrap.lifterlms.llms-builder',
						dismissible: true,
						placement: 'left',
						width: 480,
						title: LLMS.l10n.translate( 'Add Existing Assignment' ),
						content: new PostSearch( {
							post_type: 'llms_assignment',
							searching_message: LLMS.l10n.translate( 'Search for existing assignments...' ),
						} ).render().$el,
						onHide: function() {
							Backbone.pubSub.off( 'assignment-search-select' );
						},
					}
				} );

				this.post_search_popover.show();
				Backbone.pubSub.once( 'assignment-search-select', this.add_existing_assignment, this );

			} else {

				this.show_ad_popover( '#llms-existing-assignment' );

			}


		},

		/**
		 * Determine if Assignments addon is available to use
		 * @return   {Boolean}
		 * @since    3.17.0
		 * @version  3.17.0
		 */
		is_addon_available: function() {

			return ( window.llms_builder.assignments );

		},

		/**
		 * Called when assignment is trashed
		 * @param    obj   assignment  Assignment model
		 * @return   void
		 * @since    3.17.0
		 * @version  3.17.0
		 */
		on_trashed: function( assignment ) {

			this.lesson.set( 'assignment_enabled', 'no' );
			this.lesson.set( 'assignment', '' );

			delete this.model;

			this.render();

		},

		/**
		 * Shows a dirty dirty ad popoever for advanced assignments
		 * @param    string   el  jQuery selector string
		 * @return   void
		 * @since    3.17.0
		 * @version  3.17.0
		 */
		show_ad_popover: function( el ) {

			var h3 = LLMS.l10n.translate( 'Get Your Students Taking Action' ),
				p = 'Great learning content is only half of teaching online. When your learners fully engage, they will take your content and move into action. Remove barriers for your learners by telling them what to do to apply what they just learned. Create graded assignments or simply give them a checklist of action items to complete before moving on.',
				btn = LLMS.l10n.translate( 'Get Assignments Now!' ),
				url = 'https://lifterlms.com/product/lifterlms-assignments?utm_source=LifterLMS%20Plugin&utm_medium=Assignment%20Builder%20Button&utm_campaign=Assignment%20Addon%20Upsell&utm_content=3.17.0';

			this.ad_popover = new Popover( {
				el: el,
				args: {
					backdrop: true,
					closeable: true,
					container: '.wrap.lifterlms.llms-builder',
					dismissible: true,
					// placement: 'left',
					width: 380,
					title: LLMS.l10n.translate( 'Unlock LifterLMS Assignments' ),
					content: '<h3>' + h3 + '</h3><p>' + p + '</p><br><p><a class="llms-button-primary" href="' + url + '" target="_blank">' + btn + '</a></p>'
				}
			} );

			this.ad_popover.show();

		},

	}, Detachable, Editable, Trashable, Subview, SettingsFields ) );

} );

/**
 * Sidebar Editor View
 * @since    3.16.0
 * @version  3.27.0
 */
define( 'Views/Editor',[
		'Views/LessonEditor',
		'Views/Quiz',
		'Views/Assignment',
		'Views/_Subview'
	], function(
		LessonEditor,
		Quiz,
		Assignment,
		Subview
	) {

	return Backbone.View.extend( _.defaults( {

		/**
		 * Current view state
		 * @type  {String}
		 */
		state: 'lesson', // [lesson|quiz]

		/**
		 * Current Subviews
		 * @type  {Object}
		 */
		views: {
			lesson: {
				class: LessonEditor,
				instance: null,
				state: 'lesson',
			},
			assignment: {
				class: Assignment,
				instance: null,
				state: 'assignment',
			},
			quiz: {
				class: Quiz,
				instance: null,
				state: 'quiz',
			},
		},

		/**
		 * HTML element selector
		 * @type  {String}
		 */
		el: '#llms-editor',

		events: {
			'click .llms-editor-nav a[href="#llms-editor-close"]': 'close_editor',
			'click .llms-editor-nav a:not([href="#llms-editor-close"])': 'switch_tab',
		},

		/**
		 * Wrapper Tag name
		 * @type  {String}
		 */
		tagName: 'div',

		/**
		 * Get the underscore template
		 * @type  {[type]}
		 */
		template: wp.template( 'llms-editor-template' ),

		/**
		 * Initialization callback func (renders the element on screen)
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		initialize: function( data ) {

			this.SidebarView = data.SidebarView;
			if ( data.tab ) {
				this.state = data.tab;
			}

		},

		/**
		 * Compiles the template and renders the view
		 * @return   self (for chaining)
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		render: function( view_data ) {

			view_data = view_data || {};

			this.$el.html( this.template( this ) );

			this.render_subviews( _.extend( view_data, {
				lesson: this.model,
			} ) );

			return this;

		},

		/**
		 * Click event for close sidebar editor button
		 * Sends event to main SidebarView to trigger editor closing events
		 * @param    obj   event  js event obj
		 * @return   void
		 * @since    3.16.0
		 * @version  3.27.0
		 */
		close_editor: function( event ) {

			event.preventDefault();
			Backbone.pubSub.trigger( 'sidebar-editor-close' );
			window.location.hash = '';

		},

		/**
		 * Click event for switching tabs in the editor navigation
		 * @param    object  event  js event object
		 * @return   void
		 * @since    3.16.0
		 * @version  3.27.0
		 */
		switch_tab: function( event ) {

			event.preventDefault();

			var $btn = $( event.target ),
				view = $btn.attr( 'data-view' ),
				$tab = this.$el.find( $btn.attr( 'href' ) );

			this.set_state( view ).render();
			this.set_hash( view );

			// Backbone.pubSub.trigger( 'editor-tab-activated', $btn.attr( 'href' ).substring( 1 ) );

		},

		/**
		 * Adds a hash for deeplinking to a specific lesson tab
		 * @param  string  subtab subtab [quiz|assignment]
		 * @return void
		 * @since   3.27.0
		 * @version 3.27.0
		 */
		set_hash: function( subtab ) {

			var hash = 'lesson:' + this.model.get( 'id' );

			if ( 'lesson' !== subtab ) {
				hash += ':' + subtab;
			}

			window.location.hash = hash;

		},

	}, Subview ) );

} );

/**
 * Sidebar Elements View
 * @since    3.16.0
 * @version  3.16.12
 */
define( 'Views/Elements',[ 'Models/Section', 'Views/Section', 'Models/Lesson', 'Views/Lesson', 'Views/Popover', 'Views/PostSearch' ], function( Section, SectionView, Lesson, LessonView, Popover, LessonSearch ) {

	return Backbone.View.extend( {

		/**
		 * HTML element selector
		 * @type  {String}
		 */
		el: '#llms-elements',

		events: {
			'click #llms-new-section': 'add_new_section',
			'click #llms-new-lesson': 'add_new_lesson',
			'click #llms-existing-lesson': 'add_existing_lesson',
		},

		/**
		 * Wrapper Tag name
		 * @type  {String}
		 */
		tagName: 'div',

		/**
		 * Get the underscore template
		 * @type  {[type]}
		 */
		template: wp.template( 'llms-elements-template' ),

		/**
		 * Initialization callback func (renders the element on screen)
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		initialize: function( data ) {

			// save a reference to the main Course view
			this.SidebarView = data.SidebarView;

			// watch course sections and enable/disable lesson buttons conditionally
			this.listenTo( this.SidebarView.CourseView.model.get( 'sections' ), 'add', this.maybe_disable_buttons );
			this.listenTo( this.SidebarView.CourseView.model.get( 'sections' ), 'remove', this.maybe_disable_buttons );

		},

		/**
		 * Compiles the template and renders the view
		 * @return   self (for chaining)
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		render: function() {

			this.$el.html( this.template() );
			this.draggable();
			this.maybe_disable_buttons();

			return this;
		},

		draggable: function() {

			$( '#llms-new-section' ).draggable( {
				appendTo: '#llms-sections',
				cancel: false,
				connectToSortable: '.llms-sections',
				helper: function() {
					return new SectionView( { model: new Section() } ).render().$el;
				},
				start: function() {
					$( '.llms-sections' ).addClass( 'dragging' );
				},
				stop: function() {
					$( '.llms-sections' ).removeClass( 'dragging' );
				},
			} );

			$( '#llms-new-lesson' ).draggable( {
				// appendTo: '#llms-sections .llms-section:first-child .llms-lessons',
				appendTo: '#llms-sections',
				cancel: false,
				connectToSortable: '.llms-lessons',
				helper: function() {
					return new LessonView( { model: new Lesson() } ).render().$el;
				},
				start: function() {

					$( '.llms-lessons' ).addClass( 'dragging' );

				},
				stop: function() {
					$( '.llms-lessons' ).removeClass( 'dragging' );
					$( '.drag-expanded' ).removeClass( '.drag-expanded' );
				},
			} );

		},

		add_new_section: function( event ) {

			event.preventDefault();
			Backbone.pubSub.trigger( 'add-new-section' );
		},

		add_new_lesson: function( event ) {
			event.preventDefault();
			Backbone.pubSub.trigger( 'add-new-lesson' );
		},

		/**
		 * Show the popover to add an existing lessons
		 * @param    object   event  JS Event OBject
		 * @return   void
		 * @since    3.16.12
		 * @version  3.16.12
		 */
		add_existing_lesson: function( event ) {

			event.preventDefault();

			var pop = new Popover( {
				el: '#llms-existing-lesson',
				args: {
					backdrop: true,
					closeable: true,
					container: '.wrap.lifterlms.llms-builder',
					dismissible: true,
					placement: 'left',
					width: 480,
					title: LLMS.l10n.translate( 'Add Existing Lesson' ),
					content: new LessonSearch( {
						post_type: 'lesson',
						searching_message: LLMS.l10n.translate( 'Search for existing lessons...' ),
					} ).render().$el,
				}
			} );

			pop.show();
			Backbone.pubSub.on( 'lesson-search-select', function() {
				pop.hide()
			} );

		},

		/**
		 * Disables lesson add buttons if no sections are available to add a lesson to
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		maybe_disable_buttons: function() {

			var $els = $( '#llms-new-lesson, #llms-existing-lesson' );

			if ( ! this.SidebarView.CourseView.model.get( 'sections' ).length ) {
				$els.attr( 'disabled', 'disabled' );
			} else {
				$els.removeAttr( 'disabled' );
			}

		},

	} );

} );

/**
 * Sidebar Utilities View
 * @since    3.16.0
 * @version  3.16.0
 */
define( 'Views/Utilities',[], function() {

	return Backbone.View.extend( {

		/**
		 * HTML element selector
		 * @type  {String}
		 */
		el: '#llms-utilities',

		events: {
			'click #llms-collapse-all': 'collapse_all',
			'click #llms-expand-all': 'expand_all'
		},

		/**
		 * Wrapper Tag name
		 * @type  {String}
		 */
		tagName: 'div',

		/**
		 * Get the underscore template
		 * @type  {[type]}
		 */
		template: wp.template( 'llms-utilities-template' ),

		/**
		 * Initialization callback func (renders the element on screen)
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		initialize: function() {

			// this.render();

		},

		/**
		 * Compiles the template and renders the view
		 * @return   self (for chaining)
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		render: function() {
			this.$el.html( this.template() );
			return this;
		},

		/**
		 * Collapse all sections
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		collapse_all: function( event ) {
			event.preventDefault();
			Backbone.pubSub.trigger( 'collapse-all' );
		},

		/**
		 * Expand all sections
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		expand_all: function( event ) {
			event.preventDefault();
			Backbone.pubSub.trigger( 'expand-all' );
		},

	} );

} );

/**
 * Main sidebar view
 * @since    3.16.0
 * @version  3.16.7
 */
define( 'Views/Sidebar',[
		'Views/Editor',
		'Views/Elements',
		'Views/Utilities',
		'Views/_Subview'
	], function(
		Editor,
		Elements,
		Utilities,
		Subview
	) {

	return Backbone.View.extend( _.defaults( {

		/**
		 * Current builder state
		 * @type  {String}
		 */
		state: 'builder', // [builder|editor]

		/**
		 * Current Subviews
		 * @type  {Object}
		 */
		views: {
			elements: {
				class: Elements,
				instance: null,
				state: 'builder',
			},
			utilities: {
				class: Utilities,
				instance: null,
				state: 'builder',
			},
			editor: {
				class: Editor,
				instance: null,
				state: 'editor',
			},
		},

		/**
		 * HTML element selector
		 * @type  {String}
		 */
		el: '#llms-builder-sidebar',

		/**
		 * DOM events
		 * @type  {Object}
		 */
		events: {
			'click #llms-save-button': 'save_now',
			'click #llms-exit-button': 'exit_now',
			'click .llms-builder-error': 'clear_errors',
		},

		/**
		 * Wrapper Tag name
		 * @type  {String}
		 */
		tagName: 'aside',

		/**
		 * Get the underscore template
		 * @type  {[type]}
		 */
		template: wp.template( 'llms-sidebar-template' ),

		/**
		 * Initialization callback func (renders the element on screen)
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		initialize: function( data ) {

			// save a reference to the main Course view
			this.CourseView = data.CourseView;

			this.render();

			Backbone.pubSub.on( 'current-save-status', this.changes_made, this );

			Backbone.pubSub.on( 'heartbeat-send', this.heartbeat_send, this );
			Backbone.pubSub.on( 'heartbeat-tick', this.heartbeat_tick, this );

			Backbone.pubSub.on( 'lesson-selected', this.on_lesson_select, this );
			Backbone.pubSub.on( 'sidebar-editor-close', this.on_editor_close, this );

			this.$saveButton = $( '#llms-save-button' );

		},

		/**
		 * Compiles the template and renders the view
		 * @return   self (for chaining)
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		render: function( view_data ) {

			view_data = view_data || {};

			this.$el.html( this.template() );

			this.render_subviews( _.extend( view_data, {
				SidebarView: this,
			} ) );

			var $el = $( '.wrap.lifterlms.llms-builder' );
			if ( 'builder' === this.state ) {
				$el.removeClass( 'editor-active' );
			} else {
				$el.addClass( 'editor-active' );
			}

			this.$saveButton = this.$el.find( '#llms-save-button' );

			return this;

		},

		/**
		 * Adds error message element
		 * @param    {[type]}   $err  [description]
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		add_error: function( $err ) {

			this.$el.find( '.llms-builder-save' ).prepend( $err );

		},

		/**
		 * Clear any existing error message elements
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		clear_errors: function() {

			this.$el.find( '.llms-builder-save .llms-builder-error' ).remove();

		},

		/**
		 * Update save status button when changes are detected
		 * runs on an interval to check status of course regularly for unsaved changes
		 * @param    obj   sync  instance of the sync controller
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		changes_made: function( sync ) {

			// if a save is currently running, don't do anything
			if ( sync.saving ) {
				return;
			}

			if ( sync.has_unsaved_changes ) {

				this.$saveButton.attr( 'data-status', 'unsaved' );
				this.$saveButton.removeAttr( 'disabled' );

			} else {

				this.$saveButton.attr( 'data-status', 'saved' );
				this.$saveButton.attr( 'disabled', 'disabled' );

			}

		},

		/**
		 * Exit the builder and return to the WP Course Editor
		 * @return   void
		 * @since    3.16.7
		 * @version  3.16.7
		 */
		exit_now: function() {

			window.location.href = window.llms_builder.CourseModel.get_edit_post_link();

		},

		/**
		 * Triggered when a heartbeat send event starts containing builder information
		 * @param    obj   sync  instance of the sync controller
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		heartbeat_send: function( sync ) {

			if ( sync.saving ) {
				LLMS.Spinner.start( this.$saveButton.find( 'i' ), 'small' );
				this.$saveButton.attr( {
					'data-status': 'saving',
					disabled: 'disabled',
				} );
			}

		},

		/**
		 * Triggered when a heartbeat tick completes and updates save status or appends errors
		 * @param    obj   sync  instance of the sync controller
		 * @param    obj   data  updated data
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		heartbeat_tick: function( sync, data ) {

			if ( ! sync.saving ) {

				var status = 'saved';

				this.clear_errors();

				if ( 'error' === data.status ) {

					status = 'error';

					var msg = data.message,
						$err = $( '<ol class="llms-builder-error" />' );

					if ( 'object' === typeof msg ) {
						_.each( msg, function( txt ) {
							$err.append( '<li>' + txt + '</li>' );
						} );
					} else {
						$err = $err.append( '<li>' + msg + '</li>' );;
					}

					this.add_error( $err );

				}

				this.$saveButton.find( '.llms-spinning' ).remove();
				this.$saveButton.attr( {
					'data-status': status,
					disabled: 'disabled',
				} );

			}

		},

		/**
		 * Determine if the editor is the currently active state
		 * @return   boolean
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		is_editor_active: function() {

			return ( 'editor' === this.state );

		},

		/**
		 * Triggered when the editor closes, updates state to be the course builder view
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		on_editor_close: function() {

			this.set_state( 'builder' ).render();

		},

		/**
		 * When a lesson is selected, opens the sidebar to the editor view
		 * @param    obj   lesson_model  instance of the lesson model which was selected
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		on_lesson_select: function( lesson_model, tab ) {

			if ( 'editor' !== this.state ) {
				this.set_state( 'editor' );
			} else {
				this.remove_subview( 'editor' );
			}

			this.render( {
				model: lesson_model,
				tab: tab,
			} );

		},

		/**
		 * Save button click event
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		save_now: function() {

			window.llms_builder.sync.save_now();

		},

	}, Subview ) );

} );

/**
 * LifterLMS JS Builder App Bootstrap
 * @since    3.16.0
 * @version  3.27.0
 */
require( [
	'vendor/wp-hooks',
	'vendor/backbone.collectionView',
	'vendor/backbone.trackit',
	'Controllers/Construct',
	'Controllers/Debug',
	'Controllers/Schemas',
	'Controllers/Sync',
	'Models/loader',
	'Views/Course',
	'Views/Sidebar'
], function(
	Hooks,
	CV,
	TrackIt,
	Construct,
	Debug,
	Schemas,
	Sync,
	Models,
	CourseView,
	SidebarView
) {

	window.llms_builder.debug = new Debug( window.llms_builder.debug );
	window.llms_builder.construct = new Construct();
	window.llms_builder.schemas = new Schemas( window.llms_builder.schemas );

	/**
	 * Compare values, used by _.checked & _.selected mixins
	 * @param    mixed   expected  expected value, probably a string (the value of a select option or checkbox element)
	 * @param    mixed   actual    actual value, probably a string (the return of model.get( 'something' ) )
	 *                             				 but could be an array like a multiselect
	 * @return   boolean
	 * @since    3.17.2
	 * @version  3.17.2
	 */
	function value_compare( expected, actual ) {
		return ( ( _.isArray( actual ) && -1 !== actual.indexOf( expected ) ) || expected == actual );
	};

	/**
	 * Underscores templating utilities
	 * @since    3.17.0
	 * @version  3.27.0
	 */
	_.mixin( {

		/**
		 * Determine if two values are equal and output checked attribute if they are
		 * Useful for templating checkboxes & radio elements
		 * Like WP Core PHP checked() but in JS
		 * @param    mixed   expected  expected element value
		 * @param    mixed   actual    actual element value
		 * @return   void
		 * @since    3.17.0
		 * @version  3.17.2
		 */
		checked: function( expected, actual ) {
			if ( value_compare( expected, actual ) ) {
				return ' checked="checked"';
			}
			return '';
		},

		/**
		 * Recursively clone an object via _.clone()
		 * @param    obj   obj  object to clone
		 * @return   obj
		 * @since    3.17.7
		 * @version  3.17.7
		 */
		deepClone: function( obj ) {

			var clone = _.clone( obj );

			_.each( clone, function( val, key ) {
				if ( ! _.isFunction( val ) && _.isObject( val ) ) {
					clone[ key ] = _.deepClone( val );
				};
			} );

			return clone;

		},

		/**
		 * Strips IDs & Parent References from quizzes and all quiz questions
		 * @param    obj   quiz   raw quiz object (not a model)
		 * @return   obj
		 * @since    3.24.0
		 * @version  3.27.0
		 */
		prepareQuizObjectForCloning: function( quiz ) {

			delete quiz.id;
			delete quiz.lesson_id;

			_.each( quiz.questions, function( question ) {

				question = _.prepareQuestionObjectForCloning( question );

			} );

			return quiz;

		},

		/**
		 * Strips IDs & Parent References from a question
		 * @param    obj   question   raw question object (not a model).
		 * @return   obj
		 * @since    3.27.0
		 * @version  3.27.0
		 */
		prepareQuestionObjectForCloning: function( question ) {

			delete question.id;
			delete question.parent_id;

			if ( question.image && _.isObject( question.image ) ) {
				question.image._forceSync = true;
			}

			if ( question.choices ) {

				_.each( question.choices, function( choice ) {

					delete choice.question_id;
					delete choice.id;
					if ( 'image' === choice.choice_type && _.isObject( choice.choice ) ) {
						choice.choice._forceSync = true;
					}

				} );

			}

			return question;

		},

		/**
		 * Determine if two values are equal and output seleted attribute if they are
		 * Useful for templating select elements
		 * Like WP Core PHP selected() but in JS
		 * @param    mixed   expected  expected element value
		 * @param    mixed   actual    actual element value
		 * @return   void
		 * @since    3.17.0
		 * @version  3.17.2
		 */
		selected: function( expected, actual ) {
			if ( value_compare( expected, actual ) ) {
				return ' selected="selected"';
			}
			return '';
		},

		/**
		 * Generic function for stripping HTML tags from a string
		 * @param    string   content       raw string
		 * @param    array   allowed_tags  array of allowed HTML tags
		 * @return   string
		 * @since    3.17.8
		 * @version  3.17.8
		 */
		stripFormatting: function( content, allowed_tags ) {

			if ( ! allowed_tags ) {
				allowed_tags = [ 'b', 'i', 'u', 'strong', 'em' ];
			}

			var $html = $( '<div>' + content + '</div>' );

			$html.find( '*' ).not( allowed_tags.join( ',' ) ).each( function( ) {

				$( this ).replaceWith( this.innerHTML );

			} );

			return $html.html();

		},

	} );

	Backbone.pubSub = _.extend( {}, Backbone.Events );

	$( document ).trigger( 'llms-builder-pre-init' );

	window.llms_builder.questions = window.llms_builder.construct.get_collection( 'QuestionTypes', window.llms_builder.questions );

	var CourseModel = window.llms_builder.construct.get_model( 'Course', window.llms_builder.course );
	window.llms_builder.CourseModel = CourseModel;

	window.llms_builder.sync = new Sync( CourseModel, window.llms_builder.sync );

	var Course = new CourseView( {
		model: CourseModel,
	} );

	var Sidebar = new SidebarView( {
		CourseView: Course
	} );

	$( document ).trigger( 'llms-builder-init', {
		course: Course,
		sidebar: Sidebar,
	} );

	/**
	 * Do deeplinking to Lesson / Quiz / Assignments
	 * Hash should be in the form of #lesson:{lesson_id}:{subtab}
	 * subtab can be either "quiz" or "assignment". If none found assumes the "lesson" tab
	 * @since   3.27.0
	 * @version 3.27.0
	 */
	if ( window.location.hash ) {
		var hash = window.location.hash;
		if ( -1 === hash.indexOf( '#lesson:' ) ) {
			return;
		}
		var parts = hash.replace( '#lesson:', '' ).split( ':' ),
			$lesson = $( '#llms-lesson-' + parts[0] );

		if ( $lesson.length ) {
			$lesson.closest( '.llms-builder-item.llms-section' ).find( 'a.llms-action-icon.expand' ).trigger( 'click' );
			var subtab = parts[1] ? parts[1] : 'lesson';
			$( '#llms-lesson-' + parts[0] ).find( 'a.llms-action-icon.edit-' + subtab ).trigger( 'click' );
		}

	}

} );

define("main", function(){});

}(jQuery));

//# sourceMappingURL=llms-builder.js.map
