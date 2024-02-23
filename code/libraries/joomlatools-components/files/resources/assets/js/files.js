//history.js
/**
 * History.js Core
 * @author Benjamin Arthur Lupton <contact@balupton.com>
 * @copyright 2010-2011 Benjamin Arthur Lupton <contact@balupton.com>
 * @license New BSD License <http://creativecommons.org/licenses/BSD/>
 */

/**
 * Ercan: https://github.com/balupton/history.js/issues/48 applied. 
 */
(function(window,undefined){
	"use strict";

	// ========================================================================
	// Initialise

	// Localise Globals
	var
		console = window.console||undefined, // Prevent a JSLint complain
		document = window.document, // Make sure we are using the correct document
		navigator = window.navigator, // Make sure we are using the correct navigator
		sessionStorage = window.sessionStorage||false, // sessionStorage
		setTimeout = window.setTimeout,
		clearTimeout = window.clearTimeout,
		setInterval = window.setInterval,
		clearInterval = window.clearInterval,
		JSON = window.JSON,
		alert = window.alert,
		History = window.History = window.History||{}, // Public History Object
		history = window.history; // Old History Object

	// MooTools Compatibility
	JSON.stringify = JSON.stringify||JSON.encode;
	JSON.parse = JSON.parse||JSON.decode;

	// Check Existence
	if ( typeof History.init !== 'undefined' ) {
		throw new Error('History.js Core has already been loaded...');
	}

	// Initialise History
	History.init = function(){
		// Check Load Status of Adapter
		if ( typeof History.Adapter === 'undefined' ) {
			return false;
		}

		// Check Load Status of Core
		if ( typeof History.initCore !== 'undefined' ) {
			History.initCore();
		}

		// Check Load Status of HTML4 Support
		if ( typeof History.initHtml4 !== 'undefined' ) {
			History.initHtml4();
		}

		// Return true
		return true;
	};


	// ========================================================================
	// Initialise Core

	// Initialise Core
	History.initCore = function(){
		// Initialise
		if ( typeof History.initCore.initialized !== 'undefined' ) {
			// Already Loaded
			return false;
		}
		else {
			History.initCore.initialized = true;
		}


		// ====================================================================
		// Options

		/**
		 * History.options
		 * Configurable options
		 */
		History.options = History.options||{};

		/**
		 * History.options.hashChangeInterval
		 * How long should the interval be before hashchange checks
		 */
		History.options.hashChangeInterval = History.options.hashChangeInterval || 100;

		/**
		 * History.options.safariPollInterval
		 * How long should the interval be before safari poll checks
		 */
		History.options.safariPollInterval = History.options.safariPollInterval || 500;

		/**
		 * History.options.doubleCheckInterval
		 * How long should the interval be before we perform a double check
		 */
		History.options.doubleCheckInterval = History.options.doubleCheckInterval || 500;

		/**
		 * History.options.storeInterval
		 * How long should we wait between store calls
		 */
		History.options.storeInterval = History.options.storeInterval || 1000;

		/**
		 * History.options.busyDelay
		 * How long should we wait between busy events
		 */
		History.options.busyDelay = History.options.busyDelay || 250;

		/**
		 * History.options.debug
		 * If true will enable debug messages to be logged
		 */
		History.options.debug = History.options.debug || false;

		/**
		 * History.options.initialTitle
		 * What is the title of the initial state
		 */
		History.options.initialTitle = History.options.initialTitle || document.title;


		// ====================================================================
		// Interval record

		/**
		 * History.intervalList
		 * List of intervals set, to be cleared when document is unloaded.
		 */
		History.intervalList = [];

		/**
		 * History.clearAllIntervals
		 * Clears all setInterval instances.
		 */
		History.clearAllIntervals = function(){
			var i, il = History.intervalList;
			if (typeof il !== "undefined" && il !== null) {
				for (i = 0; i < il.length; i++) {
					clearInterval(il[i]);
				}
				History.intervalList = null;
			}
		};


		// ====================================================================
		// Debug

		/**
		 * History.debug(message,...)
		 * Logs the passed arguments if debug enabled
		 */
		History.debug = function(){
			if ( (History.options.debug||false) ) {
				History.log.apply(History,arguments);
			}
		};

		/**
		 * History.log(message,...)
		 * Logs the passed arguments
		 */
		History.log = function(){
			// Prepare
			var
				consoleExists = !(typeof console === 'undefined' || typeof console.log === 'undefined' || typeof console.log.apply === 'undefined'),
				textarea = document.getElementById('log'),
				message,
				i,n,
				args,arg
				;

			// Write to Console
			if ( consoleExists ) {
				args = Array.prototype.slice.call(arguments);
				message = args.shift();
				if ( typeof console.debug !== 'undefined' ) {
					console.debug.apply(console,[message,args]);
				}
				else {
					console.log.apply(console,[message,args]);
				}
			}
			else {
				message = ("\n"+arguments[0]+"\n");
			}

			// Write to log
			for ( i=1,n=arguments.length; i<n; ++i ) {
				arg = arguments[i];
				if ( typeof arg === 'object' && typeof JSON !== 'undefined' ) {
					try {
						arg = JSON.stringify(arg);
					}
					catch ( Exception ) {
						// Recursive Object
					}
				}
				message += "\n"+arg+"\n";
			}

			// Textarea
			if ( textarea ) {
				textarea.value += message+"\n-----\n";
				textarea.scrollTop = textarea.scrollHeight - textarea.clientHeight;
			}
			// No Textarea, No Console
			else if ( !consoleExists ) {
				alert(message);
			}

			// Return true
			return true;
		};


		// ====================================================================
		// Emulated Status

		/**
		 * History.getInternetExplorerMajorVersion()
		 * Get's the major version of Internet Explorer
		 * @return {integer}
		 * @license Public Domain
		 * @author Benjamin Arthur Lupton <contact@balupton.com>
		 * @author James Padolsey <https://gist.github.com/527683>
		 */
		History.getInternetExplorerMajorVersion = function(){
			var result = History.getInternetExplorerMajorVersion.cached =
					(typeof History.getInternetExplorerMajorVersion.cached !== 'undefined')
				?	History.getInternetExplorerMajorVersion.cached
				:	(function(){
						var v = 3,
								div = document.createElement('div'),
								all = div.getElementsByTagName('i');
						while ( (div.innerHTML = '<!--[if gt IE ' + (++v) + ']><i></i><![endif]-->') && all[0] ) {}
						return (v > 4) ? v : false;
					})()
				;
			return result;
		};

		/**
		 * History.isInternetExplorer()
		 * Are we using Internet Explorer?
		 * @return {boolean}
		 * @license Public Domain
		 * @author Benjamin Arthur Lupton <contact@balupton.com>
		 */
		History.isInternetExplorer = function(){
			var result =
				History.isInternetExplorer.cached =
				(typeof History.isInternetExplorer.cached !== 'undefined')
					?	History.isInternetExplorer.cached
					:	Boolean(History.getInternetExplorerMajorVersion())
				;
			return result;
		};

		/**
		 * History.emulated
		 * Which features require emulating?
		 */
		History.emulated = {
			pushState: !Boolean(
				window.history && window.history.pushState && window.history.replaceState
				&& !(
					(/ Mobile\/([1-7][a-z]|(8([abcde]|f(1[0-8]))))/i).test(navigator.userAgent) /* disable for versions of iOS before version 4.3 (8F190) */
					|| (/AppleWebKit\/5([0-2]|3[0-2])/i).test(navigator.userAgent) /* disable for the mercury iOS browser, or at least older versions of the webkit engine */
				)
			),
			hashChange: Boolean(
				!(('onhashchange' in window) || ('onhashchange' in document))
				||
				(History.isInternetExplorer() && History.getInternetExplorerMajorVersion() < 8)
			)
		};

		/**
		 * History.enabled
		 * Is History enabled?
		 */
		History.enabled = !History.emulated.pushState;

		/**
		 * History.bugs
		 * Which bugs are present
		 */
		History.bugs = {
			/**
			 * Safari 5 and Safari iOS 4 fail to return to the correct state once a hash is replaced by a `replaceState` call
			 * https://bugs.webkit.org/show_bug.cgi?id=56249
			 */
			setHash: Boolean(!History.emulated.pushState && navigator.vendor === 'Apple Computer, Inc.' && /AppleWebKit\/5([0-2]|3[0-3])/.test(navigator.userAgent)),

			/**
			 * Safari 5 and Safari iOS 4 sometimes fail to apply the state change under busy conditions
			 * https://bugs.webkit.org/show_bug.cgi?id=42940
			 */
			safariPoll: Boolean(!History.emulated.pushState && navigator.vendor === 'Apple Computer, Inc.' && /AppleWebKit\/5([0-2]|3[0-3])/.test(navigator.userAgent)),

			/**
			 * MSIE 6 and 7 sometimes do not apply a hash even it was told to (requiring a second call to the apply function)
			 */
			ieDoubleCheck: Boolean(History.isInternetExplorer() && History.getInternetExplorerMajorVersion() < 8),

			/**
			 * MSIE 6 requires the entire hash to be encoded for the hashes to trigger the onHashChange event
			 */
			hashEscape: Boolean(History.isInternetExplorer() && History.getInternetExplorerMajorVersion() < 7)
		};

		/**
		 * History.isEmptyObject(obj)
		 * Checks to see if the Object is Empty
		 * @param {Object} obj
		 * @return {boolean}
		 */
		History.isEmptyObject = function(obj) {
			for ( var name in obj ) {
				return false;
			}
			return true;
		};

		/**
		 * History.cloneObject(obj)
		 * Clones a object and eliminate all references to the original contexts
		 * @param {Object} obj
		 * @return {Object}
		 */
		History.cloneObject = function(obj) {
			var hash,newObj;
			if ( obj ) {
				hash = JSON.stringify(obj);
				newObj = JSON.parse(hash);
			}
			else {
				newObj = {};
			}
			return newObj;
		};


		// ====================================================================
		// URL Helpers

		/**
		 * History.getRootUrl()
		 * Turns "http://mysite.com/dir/page.html?asd" into "http://mysite.com"
		 * @return {String} rootUrl
		 */
		History.getRootUrl = function(){
			// Create
			var rootUrl = document.location.protocol+'//'+(document.location.hostname||document.location.host);
			if ( document.location.port||false ) {
				rootUrl += ':'+document.location.port;
			}
			rootUrl += '/';

			// Return
			return rootUrl;
		};

		/**
		 * History.getBaseHref()
		 * Fetches the `href` attribute of the `<base href="...">` element if it exists
		 * @return {String} baseHref
		 */
		History.getBaseHref = function(){
			// Create
			var
				baseElements = document.getElementsByTagName('base'),
				baseElement = null,
				baseHref = '';

			// Test for Base Element
			if ( baseElements.length === 1 ) {
				// Prepare for Base Element
				baseElement = baseElements[0];
				baseHref = baseElement.href.replace(/[^\/]+$/,'');
			}

			// Adjust trailing slash
			baseHref = baseHref.replace(/\/+$/,'');
			if ( baseHref ) baseHref += '/';

			// Return
			return baseHref;
		};

		/**
		 * History.getBaseUrl()
		 * Fetches the baseHref or basePageUrl or rootUrl (whichever one exists first)
		 * @return {String} baseUrl
		 */
		History.getBaseUrl = function(){
			// Create
			var baseUrl = History.getBaseHref()||History.getBasePageUrl()||History.getRootUrl();

			// Return
			return baseUrl;
		};

		/**
		 * History.getPageUrl()
		 * Fetches the URL of the current page
		 * @return {String} pageUrl
		 */
		History.getPageUrl = function(){
			// Fetch
			var
				State = History.getState(false,false),
				stateUrl = (State||{}).url||document.location.href,
				pageUrl;

			// Create
			pageUrl = stateUrl.replace(/\/+$/,'').replace(/[^\/]+$/,function(part,index,string){
				return (/\./).test(part) ? part : part+'/';
			});

			// Return
			return pageUrl;
		};

		/**
		 * History.getBasePageUrl()
		 * Fetches the Url of the directory of the current page
		 * @return {String} basePageUrl
		 */
		History.getBasePageUrl = function(){
			// Create
			var basePageUrl = document.location.href.replace(/[#\?].*/,'').replace(/[^\/]+$/,function(part,index,string){
				return (/[^\/]$/).test(part) ? '' : part;
			}).replace(/\/+$/,'')+'/';

			// Return
			return basePageUrl;
		};

		/**
		 * History.getFullUrl(url)
		 * Ensures that we have an absolute URL and not a relative URL
		 * @param {string} url
		 * @param {Boolean} allowBaseHref
		 * @return {string} fullUrl
		 */
		History.getFullUrl = function(url,allowBaseHref){
			// Prepare
			var fullUrl = url, firstChar = url.substring(0,1);
			allowBaseHref = (typeof allowBaseHref === 'undefined') ? true : allowBaseHref;

			// Check
			if ( /[a-z]+\:\/\//.test(url) ) {
				// Full URL
			}
			else if ( firstChar === '/' ) {
				// Root URL
				fullUrl = History.getRootUrl()+url.replace(/^\/+/,'');
			}
			else if ( firstChar === '#' ) {
				// Anchor URL
				fullUrl = History.getPageUrl().replace(/#.*/,'')+url;
			}
			else if ( firstChar === '?' ) {
				// Query URL
				fullUrl = History.getPageUrl().replace(/[\?#].*/,'')+url;
			}
			else {
				// Relative URL
				if ( allowBaseHref ) {
					fullUrl = History.getBaseUrl()+url.replace(/^(\.\/)+/,'');
				} else {
					fullUrl = History.getBasePageUrl()+url.replace(/^(\.\/)+/,'');
				}
				// We have an if condition above as we do not want hashes
				// which are relative to the baseHref in our URLs
				// as if the baseHref changes, then all our bookmarks
				// would now point to different locations
				// whereas the basePageUrl will always stay the same
			}

			// Return
			return fullUrl.replace(/\#$/,'');
		};

		/**
		 * History.getShortUrl(url)
		 * Ensures that we have a relative URL and not a absolute URL
		 * @param {string} url
		 * @return {string} url
		 */
		History.getShortUrl = function(url){
			// Prepare
			var shortUrl = url, baseUrl = History.getBaseUrl(), rootUrl = History.getRootUrl();

			// Trim baseUrl
			if ( History.emulated.pushState ) {
				// We are in a if statement as when pushState is not emulated
				// The actual url these short urls are relative to can change
				// So within the same session, we the url may end up somewhere different
				shortUrl = shortUrl.replace(baseUrl,'');
			}

			// Trim rootUrl
			shortUrl = shortUrl.replace(rootUrl,'/');

			// Ensure we can still detect it as a state
			if ( History.isTraditionalAnchor(shortUrl) ) {
				shortUrl = './'+shortUrl;
			}

			// Clean It
			shortUrl = shortUrl.replace(/^(\.\/)+/g,'./').replace(/\#$/,'');

			// Return
			return shortUrl;
		};


		// ====================================================================
		// State Storage

		/**
		 * History.store
		 * The store for all session specific data
		 */
		History.store = {};

		/**
		 * History.idToState
		 * 1-1: State ID to State Object
		 */
		History.idToState = History.idToState||{};

		/**
		 * History.stateToId
		 * 1-1: State String to State ID
		 */
		History.stateToId = History.stateToId||{};

		/**
		 * History.urlToId
		 * 1-1: State URL to State ID
		 */
		History.urlToId = History.urlToId||{};

		/**
		 * History.storedStates
		 * Store the states in an array
		 */
		History.storedStates = History.storedStates||[];

		/**
		 * History.savedStates
		 * Saved the states in an array
		 */
		History.savedStates = History.savedStates||[];

		/**
		 * History.noramlizeStore()
		 * Noramlize the store by adding necessary values
		 */
		History.normalizeStore = function(){
			History.store.idToState = History.store.idToState||{};
			History.store.urlToId = History.store.urlToId||{};
			History.store.stateToId = History.store.stateToId||{};
		};

		/**
		 * History.getState()
		 * Get an object containing the data, title and url of the current state
		 * @param {Boolean} friendly
		 * @param {Boolean} create
		 * @return {Object} State
		 */
		History.getState = function(friendly,create){
			// Prepare
			if ( typeof friendly === 'undefined' ) { friendly = true; }
			if ( typeof create === 'undefined' ) { create = true; }

			// Fetch
			var State = History.getLastSavedState();

			// Create
			if ( !State && create ) {
				State = History.createStateObject();
			}

			// Adjust
			if ( friendly ) {
				State = History.cloneObject(State);
				State.url = State.cleanUrl||State.url;
			}

			// Return
			return State;
		};

		/**
		 * History.getIdByState(State)
		 * Gets a ID for a State
		 * @param {State} newState
		 * @return {String} id
		 */
		History.getIdByState = function(newState){

			// Fetch ID
			var id = History.extractId(newState.url),
				str;
			
			if ( !id ) {
				// Find ID via State String
				str = History.getStateString(newState);
				if ( typeof History.stateToId[str] !== 'undefined' ) {
					id = History.stateToId[str];
				}
				else if ( typeof History.store.stateToId[str] !== 'undefined' ) {
					id = History.store.stateToId[str];
				}
				else {
					// Generate a new ID
					while ( true ) {
						id = (new Date()).getTime() + String(Math.random()).replace(/\D/g,'');
						if ( typeof History.idToState[id] === 'undefined' && typeof History.store.idToState[id] === 'undefined' ) {
							break;
						}
					}

					// Apply the new State to the ID
					History.stateToId[str] = id;
					History.idToState[id] = newState;
				}
			}

			// Return ID
			return id;
		};

		/**
		 * History.normalizeState(State)
		 * Expands a State Object
		 * @param {object} State
		 * @return {object}
		 */
		History.normalizeState = function(oldState){
			// Variables
			var newState, dataNotEmpty;

			// Prepare
			if ( !oldState || (typeof oldState !== 'object') ) {
				oldState = {};
			}

			// Check
			if ( typeof oldState.normalized !== 'undefined' ) {
				return oldState;
			}

			// Adjust
			if ( !oldState.data || (typeof oldState.data !== 'object') ) {
				oldState.data = {};
			}

			// ----------------------------------------------------------------

			// Create
			newState = {};
			newState.normalized = true;
			newState.title = oldState.title||'';
			newState.url = History.getFullUrl(History.unescapeString(oldState.url||document.location.href));
			newState.hash = History.getShortUrl(newState.url);
			newState.data = History.cloneObject(oldState.data);

			// Fetch ID
			newState.id = History.getIdByState(newState);

			// ----------------------------------------------------------------

			// Clean the URL
			newState.cleanUrl = newState.url.replace(/\??\&_suid.*/,'');
			newState.url = newState.cleanUrl;

			// Check to see if we have more than just a url
			dataNotEmpty = !History.isEmptyObject(newState.data);

			// Apply
			if ( newState.title || dataNotEmpty ) {
				// Add ID to Hash
				newState.hash = History.getShortUrl(newState.url).replace(/\??\&_suid.*/,'');
				if ( !/\?/.test(newState.hash) ) {
					newState.hash += '?';
				}
				newState.hash += '&_suid='+newState.id;
			}

			// Create the Hashed URL
			newState.hashedUrl = History.getFullUrl(newState.hash);

			// ----------------------------------------------------------------

			// Update the URL if we have a duplicate
			if ( (History.emulated.pushState || History.bugs.safariPoll) && History.hasUrlDuplicate(newState) ) {
				newState.url = newState.hashedUrl;
			}

			// ----------------------------------------------------------------

			// Return
			return newState;
		};

		/**
		 * History.createStateObject(data,title,url)
		 * Creates a object based on the data, title and url state params
		 * @param {object} data
		 * @param {string} title
		 * @param {string} url
		 * @return {object}
		 */
		History.createStateObject = function(data,title,url){
			// Hashify
			var State = {
				'data': data,
				'title': title,
				'url': url
			};

			// Expand the State
			State = History.normalizeState(State);

			// Return object
			return State;
		};

		/**
		 * History.getStateById(id)
		 * Get a state by it's UID
		 * @param {String} id
		 */
		History.getStateById = function(id){
			// Prepare
			id = String(id);

			// Retrieve
			var State = History.idToState[id] || History.store.idToState[id] || undefined;

			// Return State
			return State;
		};

		/**
		 * Get a State's String
		 * @param {State} passedState
		 */
		History.getStateString = function(passedState){
			// Prepare
			var State, cleanedState, str;

			// Fetch
			State = History.normalizeState(passedState);

			// Clean
			cleanedState = {
				data: State.data,
				title: passedState.title,
				url: passedState.url
			};

			// Fetch
			str = JSON.stringify(cleanedState);

			// Return
			return str;
		};

		/**
		 * Get a State's ID
		 * @param {State} passedState
		 * @return {String} id
		 */
		History.getStateId = function(passedState){
			// Prepare
			var State, id;
			
			// Fetch
			State = History.normalizeState(passedState);

			// Fetch
			id = State.id;

			// Return
			return id;
		};

		/**
		 * History.getHashByState(State)
		 * Creates a Hash for the State Object
		 * @param {State} passedState
		 * @return {String} hash
		 */
		History.getHashByState = function(passedState){
			// Prepare
			var State, hash;
			
			// Fetch
			State = History.normalizeState(passedState);

			// Hash
			hash = State.hash;

			// Return
			return hash;
		};

		/**
		 * History.extractId(url_or_hash)
		 * Get a State ID by it's URL or Hash
		 * @param {string} url_or_hash
		 * @return {string} id
		 */
		History.extractId = function ( url_or_hash ) {
			// Prepare
			var id,parts,url;

			// Extract
			parts = /(.*)\&_suid=([0-9]+)$/.exec(url_or_hash);
			url = parts ? (parts[1]||url_or_hash) : url_or_hash;
			id = parts ? String(parts[2]||'') : '';

			// Return
			return id||false;
		};

		/**
		 * History.isTraditionalAnchor
		 * Checks to see if the url is a traditional anchor or not
		 * @param {String} url_or_hash
		 * @return {Boolean}
		 */
		History.isTraditionalAnchor = function(url_or_hash){
			// Check
			var isTraditional = !(/[\/\?\.]/.test(url_or_hash));

			// Return
			return isTraditional;
		};

		/**
		 * History.extractState
		 * Get a State by it's URL or Hash
		 * @param {String} url_or_hash
		 * @return {State|null}
		 */
		History.extractState = function(url_or_hash,create){
			// Prepare
			var State = null, id, url;
			create = create||false;

			// Fetch SUID
			id = History.extractId(url_or_hash);
			if ( id ) {
				State = History.getStateById(id);
			}

			// Fetch SUID returned no State
			if ( !State ) {
				// Fetch URL
				url = History.getFullUrl(url_or_hash);

				// Check URL
				id = History.getIdByUrl(url)||false;
				if ( id ) {
					State = History.getStateById(id);
				}

				// Create State
				if ( !State && create && !History.isTraditionalAnchor(url_or_hash) ) {
					State = History.createStateObject(null,null,url);
				}
			}

			// Return
			return State;
		};

		/**
		 * History.getIdByUrl()
		 * Get a State ID by a State URL
		 */
		History.getIdByUrl = function(url){
			// Fetch
			var id = History.urlToId[url] || History.store.urlToId[url] || undefined;

			// Return
			return id;
		};

		/**
		 * History.getLastSavedState()
		 * Get an object containing the data, title and url of the current state
		 * @return {Object} State
		 */
		History.getLastSavedState = function(){
			return History.savedStates[History.savedStates.length-1]||undefined;
		};

		/**
		 * History.getLastStoredState()
		 * Get an object containing the data, title and url of the current state
		 * @return {Object} State
		 */
		History.getLastStoredState = function(){
			return History.storedStates[History.storedStates.length-1]||undefined;
		};

		/**
		 * History.hasUrlDuplicate
		 * Checks if a Url will have a url conflict
		 * @param {Object} newState
		 * @return {Boolean} hasDuplicate
		 */
		History.hasUrlDuplicate = function(newState) {
			// Prepare
			var hasDuplicate = false,
				oldState;

			// Fetch
			oldState = History.extractState(newState.url);

			// Check
			hasDuplicate = oldState && oldState.id !== newState.id;

			// Return
			return hasDuplicate;
		};

		/**
		 * History.storeState
		 * Store a State
		 * @param {Object} newState
		 * @return {Object} newState
		 */
		History.storeState = function(newState){
			// Store the State
			History.urlToId[newState.url] = newState.id;

			// Push the State
			History.storedStates.push(History.cloneObject(newState));

			// Return newState
			return newState;
		};

		/**
		 * History.isLastSavedState(newState)
		 * Tests to see if the state is the last state
		 * @param {Object} newState
		 * @return {boolean} isLast
		 */
		History.isLastSavedState = function(newState){
			// Prepare
			var isLast = false,
				newId, oldState, oldId;

			// Check
			if ( History.savedStates.length ) {
				newId = newState.id;
				oldState = History.getLastSavedState();
				oldId = oldState.id;

				// Check
				isLast = (newId === oldId);
			}

			// Return
			return isLast;
		};

		/**
		 * History.saveState
		 * Push a State
		 * @param {Object} newState
		 * @return {boolean} changed
		 */
		History.saveState = function(newState){
			// Check Hash
			if ( History.isLastSavedState(newState) ) {
				return false;
			}

			// Push the State
			History.savedStates.push(History.cloneObject(newState));

			// Return true
			return true;
		};

		/**
		 * History.getStateByIndex()
		 * Gets a state by the index
		 * @param {integer} index
		 * @return {Object}
		 */
		History.getStateByIndex = function(index){
			// Prepare
			var State = null;

			// Handle
			if ( typeof index === 'undefined' ) {
				// Get the last inserted
				State = History.savedStates[History.savedStates.length-1];
			}
			else if ( index < 0 ) {
				// Get from the end
				State = History.savedStates[History.savedStates.length+index];
			}
			else {
				// Get from the beginning
				State = History.savedStates[index];
			}

			// Return State
			return State;
		};


		// ====================================================================
		// Hash Helpers

		/**
		 * History.getHash()
		 * Gets the current document hash
		 * @return {string}
		 */
		History.getHash = function(){
			var hash = History.unescapeHash(document.location.hash);
			return hash;
		};

		/**
		 * History.unescapeString()
		 * Unescape a string
		 * @param {String} str
		 * @return {string}
		 */
		History.unescapeString = function(str){
			// Prepare
			var result = str,
				tmp;

			// Unescape hash
			while ( true ) {
				tmp = window.decodeURI(result);
				if ( tmp === result ) {
					break;
				}
				result = tmp;
			}

			// Return result
			return result;
		};

		/**
		 * History.unescapeHash()
		 * normalize and Unescape a Hash
		 * @param {String} hash
		 * @return {string}
		 */
		History.unescapeHash = function(hash){
			// Prepare
			var result = History.normalizeHash(hash);

			// Unescape hash
			result = History.unescapeString(result);

			// Return result
			return result;
		};

		/**
		 * History.normalizeHash()
		 * normalize a hash across browsers
		 * @return {string}
		 */
		History.normalizeHash = function(hash){
			// Prepare
			var result = hash.replace(/[^#]*#/,'').replace(/#.*/, '');

			// Return result
			return result;
		};

		/**
		 * History.setHash(hash)
		 * Sets the document hash
		 * @param {string} hash
		 * @return {History}
		 */
		History.setHash = function(hash,queue){
			// Prepare
			var adjustedHash, State, pageUrl;

			// Handle Queueing
			if ( queue !== false && History.busy() ) {
				// Wait + Push to Queue
				//History.debug('History.setHash: we must wait', arguments);
				History.pushQueue({
					scope: History,
					callback: History.setHash,
					args: arguments,
					queue: queue
				});
				return false;
			}

			// Log
			//History.debug('History.setHash: called',hash);

			// Prepare
			adjustedHash = History.escapeHash(hash);

			// Make Busy + Continue
			History.busy(true);

			// Check if hash is a state
			State = History.extractState(hash,true);
			if ( State && !History.emulated.pushState ) {
				// Hash is a state so skip the setHash
				//History.debug('History.setHash: Hash is a state so skipping the hash set with a direct pushState call',arguments);

				// PushState
				History.pushState(State.data,State.title,State.url,false);
			}
			else if ( document.location.hash !== adjustedHash ) {
				// Hash is a proper hash, so apply it

				// Handle browser bugs
				if ( History.bugs.setHash ) {
					// Fix Safari Bug https://bugs.webkit.org/show_bug.cgi?id=56249

					// Fetch the base page
					pageUrl = History.getPageUrl();

					// Safari hash apply
					History.pushState(null,null,pageUrl+'#'+adjustedHash,false);
				}
				else {
					// Normal hash apply
					document.location.hash = adjustedHash;
				}
			}

			// Chain
			return History;
		};

		/**
		 * History.escape()
		 * normalize and Escape a Hash
		 * @return {string}
		 */
		History.escapeHash = function(hash){
			// Prepare
			var result = History.normalizeHash(hash);

			// Escape hash
			result = window.encodeURI(result);

			// IE6 Escape Bug
			if ( !History.bugs.hashEscape ) {
				// Restore common parts
				result = result
					.replace(/\%21/g,'!')
					.replace(/\%26/g,'&')
					.replace(/\%3D/g,'=')
					.replace(/\%3F/g,'?');
			}

			// Return result
			return result;
		};

		/**
		 * History.getHashByUrl(url)
		 * Extracts the Hash from a URL
		 * @param {string} url
		 * @return {string} url
		 */
		History.getHashByUrl = function(url){
			// Extract the hash
			var hash = String(url)
				.replace(/([^#]*)#?([^#]*)#?(.*)/, '$2')
				;

			// Unescape hash
			hash = History.unescapeHash(hash);

			// Return hash
			return hash;
		};

		/**
		 * History.setTitle(title)
		 * Applies the title to the document
		 * @param {State} newState
		 * @return {Boolean}
		 */
		History.setTitle = function(newState){
			// Prepare
			var title = newState.title,
				firstState;

			// Initial
			if ( !title ) {
				firstState = History.getStateByIndex(0);
				if ( firstState && firstState.url === newState.url ) {
					title = firstState.title||History.options.initialTitle;
				}
			}

			// Apply
			try {
				document.getElementsByTagName('title')[0].innerHTML = title.replace('<','&lt;').replace('>','&gt;').replace(' & ',' &amp; ');
			}
			catch ( Exception ) { }
			document.title = title;

			// Chain
			return History;
		};


		// ====================================================================
		// Queueing

		/**
		 * History.queues
		 * The list of queues to use
		 * First In, First Out
		 */
		History.queues = [];

		/**
		 * History.busy(value)
		 * @param {boolean} value [optional]
		 * @return {boolean} busy
		 */
		History.busy = function(value){
			// Apply
			if ( typeof value !== 'undefined' ) {
				//History.debug('History.busy: changing ['+(History.busy.flag||false)+'] to ['+(value||false)+']', History.queues.length);
				History.busy.flag = value;
			}
			// Default
			else if ( typeof History.busy.flag === 'undefined' ) {
				History.busy.flag = false;
			}

			// Queue
			if ( !History.busy.flag ) {
				// Execute the next item in the queue
				clearTimeout(History.busy.timeout);
				var fireNext = function(){
					var i, queue, item;
					if ( History.busy.flag ) return;
					for ( i=History.queues.length-1; i >= 0; --i ) {
						queue = History.queues[i];
						if ( queue.length === 0 ) continue;
						item = queue.shift();
						History.fireQueueItem(item);
						History.busy.timeout = setTimeout(fireNext,History.options.busyDelay);
					}
				};
				History.busy.timeout = setTimeout(fireNext,History.options.busyDelay);
			}

			// Return
			return History.busy.flag;
		};

		/**
		 * History.busy.flag
		 */
		History.busy.flag = false;

		/**
		 * History.fireQueueItem(item)
		 * Fire a Queue Item
		 * @param {Object} item
		 * @return {Mixed} result
		 */
		History.fireQueueItem = function(item){
			return item.callback.apply(item.scope||History,item.args||[]);
		};

		/**
		 * History.pushQueue(callback,args)
		 * Add an item to the queue
		 * @param {Object} item [scope,callback,args,queue]
		 */
		History.pushQueue = function(item){
			// Prepare the queue
			History.queues[item.queue||0] = History.queues[item.queue||0]||[];

			// Add to the queue
			History.queues[item.queue||0].push(item);

			// Chain
			return History;
		};

		/**
		 * History.queue (item,queue), (func,queue), (func), (item)
		 * Either firs the item now if not busy, or adds it to the queue
		 */
		History.queue = function(item,queue){
			// Prepare
			if ( typeof item === 'function' ) {
				item = {
					callback: item
				};
			}
			if ( typeof queue !== 'undefined' ) {
				item.queue = queue;
			}

			// Handle
			if ( History.busy() ) {
				History.pushQueue(item);
			} else {
				History.fireQueueItem(item);
			}

			// Chain
			return History;
		};

		/**
		 * History.clearQueue()
		 * Clears the Queue
		 */
		History.clearQueue = function(){
			History.busy.flag = false;
			History.queues = [];
			return History;
		};


		// ====================================================================
		// IE Bug Fix

		/**
		 * History.stateChanged
		 * States whether or not the state has changed since the last double check was initialised
		 */
		History.stateChanged = false;

		/**
		 * History.doubleChecker
		 * Contains the timeout used for the double checks
		 */
		History.doubleChecker = false;

		/**
		 * History.doubleCheckComplete()
		 * Complete a double check
		 * @return {History}
		 */
		History.doubleCheckComplete = function(){
			// Update
			History.stateChanged = true;

			// Clear
			History.doubleCheckClear();

			// Chain
			return History;
		};

		/**
		 * History.doubleCheckClear()
		 * Clear a double check
		 * @return {History}
		 */
		History.doubleCheckClear = function(){
			// Clear
			if ( History.doubleChecker ) {
				clearTimeout(History.doubleChecker);
				History.doubleChecker = false;
			}

			// Chain
			return History;
		};

		/**
		 * History.doubleCheck()
		 * Create a double check
		 * @return {History}
		 */
		History.doubleCheck = function(tryAgain){
			// Reset
			History.stateChanged = false;
			History.doubleCheckClear();

			// Fix IE6,IE7 bug where calling history.back or history.forward does not actually change the hash (whereas doing it manually does)
			// Fix Safari 5 bug where sometimes the state does not change: https://bugs.webkit.org/show_bug.cgi?id=42940
			if ( History.bugs.ieDoubleCheck ) {
				// Apply Check
				History.doubleChecker = setTimeout(
					function(){
						History.doubleCheckClear();
						if ( !History.stateChanged ) {
							//History.debug('History.doubleCheck: State has not yet changed, trying again', arguments);
							// Re-Attempt
							tryAgain();
						}
						return true;
					},
					History.options.doubleCheckInterval
				);
			}

			// Chain
			return History;
		};


		// ====================================================================
		// Safari Bug Fix

		/**
		 * History.safariStatePoll()
		 * Poll the current state
		 * @return {History}
		 */
		History.safariStatePoll = function(){
			// Poll the URL

			// Get the Last State which has the new URL
			var
				urlState = History.extractState(document.location.href),
				newState;

			// Check for a difference
			if ( !History.isLastSavedState(urlState) ) {
				newState = urlState;
			}
			else {
				return;
			}

			// Check if we have a state with that url
			// If not create it
			if ( !newState ) {
				//History.debug('History.safariStatePoll: new');
				newState = History.createStateObject();
			}

			// Apply the New State
			//History.debug('History.safariStatePoll: trigger');
			History.Adapter.trigger(window,'popstate');

			// Chain
			return History;
		};


		// ====================================================================
		// State Aliases

		/**
		 * History.back(queue)
		 * Send the browser history back one item
		 * @param {Integer} queue [optional]
		 */
		History.back = function(queue){
			//History.debug('History.back: called', arguments);

			// Handle Queueing
			if ( queue !== false && History.busy() ) {
				// Wait + Push to Queue
				//History.debug('History.back: we must wait', arguments);
				History.pushQueue({
					scope: History,
					callback: History.back,
					args: arguments,
					queue: queue
				});
				return false;
			}

			// Make Busy + Continue
			History.busy(true);

			// Fix certain browser bugs that prevent the state from changing
			History.doubleCheck(function(){
				History.back(false);
			});

			// Go back
			history.go(-1);

			// End back closure
			return true;
		};

		/**
		 * History.forward(queue)
		 * Send the browser history forward one item
		 * @param {Integer} queue [optional]
		 */
		History.forward = function(queue){
			//History.debug('History.forward: called', arguments);

			// Handle Queueing
			if ( queue !== false && History.busy() ) {
				// Wait + Push to Queue
				//History.debug('History.forward: we must wait', arguments);
				History.pushQueue({
					scope: History,
					callback: History.forward,
					args: arguments,
					queue: queue
				});
				return false;
			}

			// Make Busy + Continue
			History.busy(true);

			// Fix certain browser bugs that prevent the state from changing
			History.doubleCheck(function(){
				History.forward(false);
			});

			// Go forward
			history.go(1);

			// End forward closure
			return true;
		};

		/**
		 * History.go(index,queue)
		 * Send the browser history back or forward index times
		 * @param {Integer} queue [optional]
		 */
		History.go = function(index,queue){
			//History.debug('History.go: called', arguments);

			// Prepare
			var i;

			// Handle
			if ( index > 0 ) {
				// Forward
				for ( i=1; i<=index; ++i ) {
					History.forward(queue);
				}
			}
			else if ( index < 0 ) {
				// Backward
				for ( i=-1; i>=index; --i ) {
					History.back(queue);
				}
			}
			else {
				throw new Error('History.go: History.go requires a positive or negative integer passed.');
			}

			// Chain
			return History;
		};


		// ====================================================================
		// HTML5 State Support

		// Non-Native pushState Implementation
		if ( History.emulated.pushState ) {
			/*
			 * Provide Skeleton for HTML4 Browsers
			 */

			// Prepare
			var emptyFunction = function(){};
			History.pushState = History.pushState||emptyFunction;
			History.replaceState = History.replaceState||emptyFunction;
		} // History.emulated.pushState

		// Native pushState Implementation
		else {
			/*
			 * Use native HTML5 History API Implementation
			 */

			/**
			 * History.onPopState(event,extra)
			 * Refresh the Current State
			 */
			History.onPopState = function(event,extra){
				// Prepare
				var stateId = false, newState = false, currentHash, currentState;

				// Reset the double check
				History.doubleCheckComplete();

				// Check for a Hash, and handle apporiatly
				currentHash	= History.getHash();
				if ( currentHash ) {
					// Expand Hash
					currentState = History.extractState(currentHash||document.location.href,true);
					if ( currentState ) {
						// We were able to parse it, it must be a State!
						// Let's forward to replaceState
						//History.debug('History.onPopState: state anchor', currentHash, currentState);
						History.replaceState(currentState.data, currentState.title, currentState.url, false);
					}
					else {
						// Traditional Anchor
						//History.debug('History.onPopState: traditional anchor', currentHash);
						History.Adapter.trigger(window,'anchorchange');
						History.busy(false);
					}

					// We don't care for hashes
					History.expectedStateId = false;
					return false;
				}

				// Ensure
				stateId = History.Adapter.extractEventData('state',event,extra) || false;

				// Fetch State
				if ( stateId ) {
					// Vanilla: Back/forward button was used
					newState = History.getStateById(stateId);
				}
				else if ( History.expectedStateId ) {
					// Vanilla: A new state was pushed, and popstate was called manually
					newState = History.getStateById(History.expectedStateId);
				}
				else {
					// Initial State
					newState = History.extractState(document.location.href);
				}

				// The State did not exist in our store
				if ( !newState ) {
					// Regenerate the State
					newState = History.createStateObject(null,null,document.location.href);
				}

				// Clean
				History.expectedStateId = false;

				// Check if we are the same state
				if ( History.isLastSavedState(newState) ) {
					// There has been no change (just the page's hash has finally propagated)
					//History.debug('History.onPopState: no change', newState, History.savedStates);
					History.busy(false);
					return false;
				}

				// Store the State
				History.storeState(newState);
				History.saveState(newState);

				// Force update of the title
				History.setTitle(newState);

				// Fire Our Event
				History.Adapter.trigger(window,'statechange');
				History.busy(false);

				// Return true
				return true;
			};
			History.Adapter.bind(window,'popstate',History.onPopState);

			/**
			 * History.pushState(data,title,url)
			 * Add a new State to the history object, become it, and trigger onpopstate
			 * We have to trigger for HTML4 compatibility
			 * @param {object} data
			 * @param {string} title
			 * @param {string} url
			 * @return {true}
			 */
			History.pushState = function(data,title,url,queue){
				//History.debug('History.pushState: called', arguments);

				// Check the State
				if ( History.getHashByUrl(url) && History.emulated.pushState ) {
					throw new Error('History.js does not support states with fragement-identifiers (hashes/anchors).');
				}

				// Handle Queueing
				if ( queue !== false && History.busy() ) {
					// Wait + Push to Queue
					//History.debug('History.pushState: we must wait', arguments);
					History.pushQueue({
						scope: History,
						callback: History.pushState,
						args: arguments,
						queue: queue
					});
					return false;
				}

				// Make Busy + Continue
				History.busy(true);

				// Create the newState
				var newState = History.createStateObject(data,title,url);

				// Check it
				if ( History.isLastSavedState(newState) ) {
					// Won't be a change
					History.busy(false);
				}
				else {
					// Store the newState
					History.storeState(newState);
					History.expectedStateId = newState.id;

					// Push the newState
					history.pushState(newState.id,newState.title,newState.url);

					// Fire HTML5 Event
					History.Adapter.trigger(window,'popstate');
				}

				// End pushState closure
				return true;
			};

			/**
			 * History.replaceState(data,title,url)
			 * Replace the State and trigger onpopstate
			 * We have to trigger for HTML4 compatibility
			 * @param {object} data
			 * @param {string} title
			 * @param {string} url
			 * @return {true}
			 */
			History.replaceState = function(data,title,url,queue){
				//History.debug('History.replaceState: called', arguments);

				// Check the State
				if ( History.getHashByUrl(url) && History.emulated.pushState ) {
					throw new Error('History.js does not support states with fragement-identifiers (hashes/anchors).');
				}

				// Handle Queueing
				if ( queue !== false && History.busy() ) {
					// Wait + Push to Queue
					//History.debug('History.replaceState: we must wait', arguments);
					History.pushQueue({
						scope: History,
						callback: History.replaceState,
						args: arguments,
						queue: queue
					});
					return false;
				}

				// Make Busy + Continue
				History.busy(true);

				// Create the newState
				var newState = History.createStateObject(data,title,url);

				// Check it
				if ( History.isLastSavedState(newState) ) {
					// Won't be a change
					History.busy(false);
				}
				else {
					// Store the newState
					History.storeState(newState);
					History.expectedStateId = newState.id;

					// Push the newState
					history.replaceState(newState.id,newState.title,newState.url);

					// Fire HTML5 Event
					History.Adapter.trigger(window,'popstate');
				}

				// End replaceState closure
				return true;
			};

		} // !History.emulated.pushState


		// ====================================================================
		// Initialise

		/**
		 * Load the Store
		 */
		if ( sessionStorage ) {
			// Fetch
			try {
				History.store = JSON.parse(sessionStorage.getItem('History.store'))||{};
			}
			catch ( err ) {
				History.store = {};
			}

			// Normalize
			History.normalizeStore();
		}
		else {
			// Default Load
			History.store = {};
			History.normalizeStore();
		}

		/**
		 * Clear Intervals on exit to prevent memory leaks
		 */
		History.Adapter.bind(window,"beforeunload",History.clearAllIntervals);
		History.Adapter.bind(window,"unload",History.clearAllIntervals);

		/**
		 * Create the initial State
		 */
		History.saveState(History.storeState(History.extractState(document.location.href,true)));

		/**
		 * Bind for Saving Store
		 */
		if ( sessionStorage ) {
			// When the page is closed
			History.onUnload = function(){
				// Prepare
				var	currentStore, item;

				// Fetch
				try {
					currentStore = JSON.parse(sessionStorage.getItem('History.store'))||{};
				}
				catch ( err ) {
					currentStore = {};
				}

				// Ensure
				currentStore.idToState = currentStore.idToState || {};
				currentStore.urlToId = currentStore.urlToId || {};
				currentStore.stateToId = currentStore.stateToId || {};

				// Sync
				for ( item in History.idToState ) {
					if ( !History.idToState.hasOwnProperty(item) ) {
						continue;
					}
					currentStore.idToState[item] = History.idToState[item];
				}
				for ( item in History.urlToId ) {
					if ( !History.urlToId.hasOwnProperty(item) ) {
						continue;
					}
					currentStore.urlToId[item] = History.urlToId[item];
				}
				for ( item in History.stateToId ) {
					if ( !History.stateToId.hasOwnProperty(item) ) {
						continue;
					}
					currentStore.stateToId[item] = History.stateToId[item];
				}

				// Update
				History.store = currentStore;
				History.normalizeStore();

				// Store
				sessionStorage.setItem('History.store',JSON.stringify(currentStore));
			};

			// For Internet Explorer
			History.intervalList.push(setInterval(History.onUnload,History.options.storeInterval));
			
			// For Other Browsers
			History.Adapter.bind(window,'beforeunload',History.onUnload);
			History.Adapter.bind(window,'unload',History.onUnload);
			
			// Both are enabled for consistency
		}

		// Non-Native pushState Implementation
		if ( !History.emulated.pushState ) {
			// Be aware, the following is only for native pushState implementations
			// If you are wanting to include something for all browsers
			// Then include it above this if block

			/**
			 * Setup Safari Fix
			 */
			if ( History.bugs.safariPoll ) {
				History.intervalList.push(setInterval(History.safariStatePoll, History.options.safariPollInterval));
			}

			/**
			 * Ensure Cross Browser Compatibility
			 */
			if ( navigator.vendor === 'Apple Computer, Inc.' || (navigator.appCodeName||'') === 'Mozilla' ) {
				/**
				 * Fix Safari HashChange Issue
				 */

				// Setup Alias
				History.Adapter.bind(window,'hashchange',function(){
					History.Adapter.trigger(window,'popstate');
				});

				// Initialise Alias
				if ( History.getHash() ) {
					History.Adapter.onDomLoad(function(){
						History.Adapter.trigger(window,'hashchange');
					});
				}
			}

		} // !History.emulated.pushState


	}; // History.initCore

	// Try and Initialise History
	History.init();

})(window);

/**
 * History.js MooTools Adapter
 * @author Benjamin Arthur Lupton <contact@balupton.com>
 * @copyright 2010-2011 Benjamin Arthur Lupton <contact@balupton.com>
 * @license New BSD License <http://creativecommons.org/licenses/BSD/>
 */

// Closure
(function(window,undefined){
	"use strict";

	// Localise Globals
	var
		History = window.History = window.History||{},
		MooTools = window.MooTools,
		Element = window.Element;

	// Check Existence
	if ( typeof History.Adapter !== 'undefined' ) {
		throw new Error('History.js Adapter has already been loaded...');
	}

	// Make MooTools aware of History.js Events
	if (MooTools.version.substring(0, 3) === '1.2') {
		Element.NativeEvents = $extend(Element.NativeEvents, {
			'popstate':2,
			'hashchange':2
		});
	} else {
		Object.append(Element.NativeEvents,{
			'popstate':2,
			'hashchange':2
		});
	}
	

	// Add the Adapter
	History.Adapter = {
		/**
		 * History.Adapter.bind(el,event,callback)
		 * @param {Element|string} el
		 * @param {string} event - custom and standard events
		 * @param {function} callback
		 * @return {void}
		 */
		bind: function(el,event,callback){
			var El = typeof el === 'string' ? document.id(el) : el;
			El.addEvent(event,callback);
		},

		/**
		 * History.Adapter.trigger(el,event)
		 * @param {Element|string} el
		 * @param {string} event - custom and standard events
		 * @param {Object=} extra - a object of extra event data (optional)
		 * @return void
		 */
		trigger: function(el,event,extra){
			var El = typeof el === 'string' ? document.id(el) : el;
			El.fireEvent(event,extra);
		},

		/**
		 * History.Adapter.extractEventData(key,event,extra)
		 * @param {string} key - key for the event data to extract
		 * @param {string} event - custom and standard events
		 * @return {mixed}
		 */
		extractEventData: function(key,event){
			// MooTools Native then MooTools Custom
			var result = (event && event.event && event.event[key]) || (event && event[key]) || undefined;

			// Return
			return result;
		},

		/**
		 * History.Adapter.onDomLoad(callback)
		 * @param {function} callback
		 * @return {void}
		 */
		onDomLoad: function(callback) {
			window.addEvent('domready',callback);
		}
	};

	// Try and Initialise History
	if ( typeof History.init !== 'undefined' ) {
		History.init();
	}

})(window);



//ejs.js
(function () {
    var rsplit = function (string, regex) {
        var result = regex.exec(string), retArr = new Array(), first_idx, last_idx, first_bit;
        while (result != null) {
            first_idx = result.index;
            last_idx = regex.lastIndex;
            if ((first_idx) != 0) {
                first_bit = string.substring(0, first_idx);
                retArr.push(string.substring(0, first_idx));
                string = string.slice(first_idx)
            }
            retArr.push(result[0]);
            string = string.slice(result[0].length);
            result = regex.exec(string)
        }
        if (!string == "") {
            retArr.push(string)
        }
        return retArr
    }, chop = function (string) {
        return string.substr(0, string.length - 1)
    }, extend = function (d, s) {
        for (var n in s) {
            if (s.hasOwnProperty(n)) {
                d[n] = s[n]
            }
        }
    };
    EJS = function (options) {
        options = typeof options == "string" ? {view: options} : options;
        this.set_options(options);
        if (options.precompiled) {
            this.template = {};
            this.template.process = options.precompiled;
            EJS.update(this.name, this);
            return
        }
        if (options.element) {
            if (typeof options.element == "string") {
                var name = options.element;
                options.element = document.getElementById(options.element);
                if (options.element == null) {
                    throw name + "does not exist!"
                }
            }
            if (options.element.value) {
                this.text = options.element.value
            } else {
                this.text = options.element.innerHTML
            }
            this.name = options.element.id;
            this.type = "["
        } else {
            if (options.url) {
                options.url = EJS.endExt(options.url, this.extMatch);
                this.name = this.name ? this.name : options.url;
                var url = options.url;
                var template = EJS.get(this.name, this.cache);
                if (template) {
                    return template
                }
                if (template == EJS.INVALID_PATH) {
                    return null
                }
                try {
                    this.text = EJS.request(url + (this.cache ? "" : "?" + Math.random()))
                } catch (e) {
                }
                if (this.text == null) {
                    throw ({type: "EJS", message: "There is no template at " + url})
                }
            }
        }
        var template = new EJS.Compiler(this.text, this.type);
        template.compile(options, this.name);
        EJS.update(this.name, this);
        this.template = template
    };
    EJS.prototype = {
        render: function (object, extra_helpers) {
            object = object || {};
            this._extra_helpers = extra_helpers;
            var v = new EJS.Helpers(object, extra_helpers || {});
            return this.template.process.call(object, object, v)
        }, update: function (element, options) {
            if (typeof element == "string") {
                element = document.getElementById(element)
            }
            if (options == null) {
                _template = this;
                return function (object) {
                    EJS.prototype.update.call(_template, element, object)
                }
            }
            if (typeof options == "string") {
                params = {};
                params.url = options;
                _template = this;
                params.onComplete = function (request) {
                    var object = eval(request.responseText);
                    EJS.prototype.update.call(_template, element, object)
                };
                EJS.ajax_request(params)
            } else {
                element.innerHTML = this.render(options)
            }
        }, out: function () {
            return this.template.out
        }, set_options: function (options) {
            this.type = options.type || EJS.type;
            this.cache = options.cache != null ? options.cache : EJS.cache;
            this.text = options.text || null;
            this.name = options.name || null;
            this.ext = options.ext || EJS.ext;
            this.extMatch = new RegExp(this.ext.replace(/\./, "."))
        }
    };
    EJS.endExt = function (path, match) {
        if (!path) {
            return null
        }
        match.lastIndex = 0;
        return path + (match.test(path) ? "" : this.ext)
    };
    EJS.Scanner = function (source, left, right) {
        extend(this, {
            left_delimiter: left + "%",
            right_delimiter: "%" + right,
            double_left: left + "%%",
            double_right: "%%" + right,
            left_equal: left + "%=",
            left_comment: left + "%#"
        });
        this.SplitRegexp = left == "[" ? /(\[%%)|(%%\])|(\[%=)|(\[%#)|(\[%)|(%\]\n)|(%\])|(\n)/ : new RegExp("(" + this.double_left + ")|(%%" + this.double_right + ")|(" + this.left_equal + ")|(" + this.left_comment + ")|(" + this.left_delimiter + ")|(" + this.right_delimiter + "\n)|(" + this.right_delimiter + ")|(\n)");
        this.source = source;
        this.stag = null;
        this.lines = 0
    };
    EJS.Scanner.to_text = function (input) {
        if (input == null || input === undefined) {
            return ""
        }
        if (input instanceof Date) {
            return input.toDateString()
        }
        if (input.toString) {
            return input.toString()
        }
        return ""
    };
    EJS.Scanner.prototype = {
        scan: function (block) {
            scanline = this.scanline;
            regex = this.SplitRegexp;
            if (!this.source == "") {
                var source_split = rsplit(this.source, /\n/);
                for (var i = 0; i < source_split.length; i++) {
                    var item = source_split[i];
                    this.scanline(item, regex, block)
                }
            }
        }, scanline: function (line, regex, block) {
            this.lines++;
            var line_split = rsplit(line, regex);
            for (var i = 0; i < line_split.length; i++) {
                var token = line_split[i];
                if (token != null) {
                    try {
                        block(token, this)
                    } catch (e) {
                        throw {type: "EJS.Scanner", line: this.lines}
                    }
                }
            }
        }
    };
    EJS.Buffer = function (pre_cmd, post_cmd) {
        this.line = new Array();
        this.script = "";
        this.pre_cmd = pre_cmd;
        this.post_cmd = post_cmd;
        for (var i = 0; i < this.pre_cmd.length; i++) {
            this.push(pre_cmd[i])
        }
    };
    EJS.Buffer.prototype = {
        push: function (cmd) {
            this.line.push(cmd)
        }, cr: function () {
            this.script = this.script + this.line.join("; ");
            this.line = new Array();
            this.script = this.script + "\n"
        }, close: function () {
            if (this.line.length > 0) {
                for (var i = 0; i < this.post_cmd.length; i++) {
                    this.push(pre_cmd[i])
                }
                this.script = this.script + this.line.join("; ");
                line = null
            }
        }
    };
    EJS.Compiler = function (source, left) {
        this.pre_cmd = ["var ___ViewO = [];"];
        this.post_cmd = new Array();
        this.source = " ";
        if (source != null) {
            if (typeof source == "string") {
                source = source.replace(/\r\n/g, "\n");
                source = source.replace(/\r/g, "\n");
                this.source = source
            } else {
                if (source.innerHTML) {
                    this.source = source.innerHTML
                }
            }
            if (typeof this.source != "string") {
                this.source = ""
            }
        }
        left = left || "<";
        var right = ">";
        switch (left) {
            case"[":
                right = "]";
                break;
            case"<":
                break;
            default:
                throw left + " is not a supported deliminator";
                break
        }
        this.scanner = new EJS.Scanner(this.source, left, right);
        this.out = ""
    };
    EJS.Compiler.prototype = {
        compile: function (options, name) {
            options = options || {};
            this.out = "";
            var put_cmd = "___ViewO.push(";
            var insert_cmd = put_cmd;
            var buff = new EJS.Buffer(this.pre_cmd, this.post_cmd);
            var content = "";
            var clean = function (content) {
                content = content.replace(/\\/g, "\\\\");
                content = content.replace(/\n/g, "\\n");
                content = content.replace(/"/g, '\\"');
                return content
            };
            this.scanner.scan(function (token, scanner) {
                if (scanner.stag == null) {
                    switch (token) {
                        case"\n":
                            content = content + "\n";
                            buff.push(put_cmd + '"' + clean(content) + '");');
                            buff.cr();
                            content = "";
                            break;
                        case scanner.left_delimiter:
                        case scanner.left_equal:
                        case scanner.left_comment:
                            scanner.stag = token;
                            if (content.length > 0) {
                                buff.push(put_cmd + '"' + clean(content) + '")')
                            }
                            content = "";
                            break;
                        case scanner.double_left:
                            content = content + scanner.left_delimiter;
                            break;
                        default:
                            content = content + token;
                            break
                    }
                } else {
                    switch (token) {
                        case scanner.right_delimiter:
                            switch (scanner.stag) {
                                case scanner.left_delimiter:
                                    if (content[content.length - 1] == "\n") {
                                        content = chop(content);
                                        buff.push(content);
                                        buff.cr()
                                    } else {
                                        buff.push(content)
                                    }
                                    break;
                                case scanner.left_equal:
                                    buff.push(insert_cmd + "(EJS.Scanner.to_text(" + content + ")))");
                                    break
                            }
                            scanner.stag = null;
                            content = "";
                            break;
                        case scanner.double_right:
                            content = content + scanner.right_delimiter;
                            break;
                        default:
                            content = content + token;
                            break
                    }
                }
            });
            if (content.length > 0) {
                buff.push(put_cmd + '"' + clean(content) + '")')
            }
            buff.close();
            this.out = buff.script + ";";
            var to_be_evaled = "/*" + name + "*/this.process = function(_CONTEXT,_VIEW) { try { with(_VIEW) { with (_CONTEXT) {" + this.out + " return ___ViewO.join('');}}}catch(e){e.lineNumber=null;throw e;}};";
            try {
                eval(to_be_evaled)
            } catch (e) {
                if (typeof JSLINT != "undefined") {
                    JSLINT(this.out);
                    for (var i = 0; i < JSLINT.errors.length; i++) {
                        var error = JSLINT.errors[i];
                        if (error.reason != "Unnecessary semicolon.") {
                            error.line++;
                            var e = new Error();
                            e.lineNumber = error.line;
                            e.message = error.reason;
                            if (options.view) {
                                e.fileName = options.view
                            }
                            throw e
                        }
                    }
                } else {
                    throw e
                }
            }
        }
    };
    EJS.config = function (options) {
        EJS.cache = options.cache != null ? options.cache : EJS.cache;
        EJS.type = options.type != null ? options.type : EJS.type;
        EJS.ext = options.ext != null ? options.ext : EJS.ext;
        var templates_directory = EJS.templates_directory || {};
        EJS.templates_directory = templates_directory;
        EJS.get = function (path, cache) {
            if (cache == false) {
                return null
            }
            if (templates_directory[path]) {
                return templates_directory[path]
            }
            return null
        };
        EJS.update = function (path, template) {
            if (path == null) {
                return
            }
            templates_directory[path] = template
        };
        EJS.INVALID_PATH = -1
    };
    EJS.config({cache: true, type: "<", ext: ".ejs"});
    EJS.Helpers = function (data, extras) {
        this._data = data;
        this._extras = extras;
        extend(this, extras)
    };
    EJS.Helpers.prototype = {
        view: function (options, data, helpers) {
            if (!helpers) {
                helpers = this._extras
            }
            if (!data) {
                data = this._data
            }
            return new EJS(options).render(data, helpers)
        }, to_text: function (input, null_text) {
            if (input == null || input === undefined) {
                return null_text || ""
            }
            if (input instanceof Date) {
                return input.toDateString()
            }
            if (input.toString) {
                return input.toString().replace(/\n/g, "<br />").replace(/''/g, "'")
            }
            return ""
        }
    };
    EJS.newRequest = function () {
        var factories = [function () {
            return new ActiveXObject("Msxml2.XMLHTTP")
        }, function () {
            return new XMLHttpRequest()
        }, function () {
            return new ActiveXObject("Microsoft.XMLHTTP")
        }];
        for (var i = 0; i < factories.length; i++) {
            try {
                var request = factories[i]();
                if (request != null) {
                    return request
                }
            } catch (e) {
                continue
            }
        }
    };
    EJS.request = function (path) {
        var request = new EJS.newRequest();
        request.open("GET", path, false);
        try {
            request.send(null)
        } catch (e) {
            return null
        }
        if (request.status == 404 || request.status == 2 || (request.status == 0 && request.responseText == "")) {
            return null
        }
        return request.responseText
    };
    EJS.ajax_request = function (params) {
        params.method = (params.method ? params.method : "GET");
        var request = new EJS.newRequest();
        request.onreadystatechange = function () {
            if (request.readyState == 4) {
                if (request.status == 200) {
                    params.onComplete(request)
                } else {
                    params.onComplete(request)
                }
            }
        };
        request.open(params.method, params.url);
        request.send(null)
    };
    EJS.Helpers.prototype.date_tag = function (C, O, A) {
        if (!(O instanceof Date)) {
            O = new Date()
        }
        var B = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
        var G = [], D = [], P = [];
        var J = O.getFullYear();
        var H = O.getMonth();
        var N = O.getDate();
        for (var M = J - 15; M < J + 15; M++) {
            G.push({value: M, text: M})
        }
        for (var E = 0; E < 12; E++) {
            D.push({value: (E), text: B[E]})
        }
        for (var I = 0; I < 31; I++) {
            P.push({value: (I + 1), text: (I + 1)})
        }
        var L = this.select_tag(C + "[year]", J, G, {id: C + "[year]"});
        var F = this.select_tag(C + "[month]", H, D, {id: C + "[month]"});
        var K = this.select_tag(C + "[day]", N, P, {id: C + "[day]"});
        return L + F + K
    };
    EJS.Helpers.prototype.form_tag = function (B, A) {
        A = A || {};
        A.action = B;
        if (A.multipart == true) {
            A.method = "post";
            A.enctype = "multipart/form-data"
        }
        return this.start_tag_for("form", A)
    };
    EJS.Helpers.prototype.form_tag_end = function () {
        return this.tag_end("form")
    };
    EJS.Helpers.prototype.hidden_field_tag = function (A, C, B) {
        return this.input_field_tag(A, C, "hidden", B)
    };
    EJS.Helpers.prototype.input_field_tag = function (A, D, C, B) {
        B = B || {};
        B.id = B.id || A;
        B.value = D || "";
        B.type = C || "text";
        B.name = A;
        return this.single_tag_for("input", B)
    };
    EJS.Helpers.prototype.is_current_page = function (A) {
        return (window.location.href == A || window.location.pathname == A ? true : false)
    };
    EJS.Helpers.prototype.link_to = function (B, A, C) {
        if (!B) {
            var B = "null"
        }
        if (!C) {
            var C = {}
        }
        if (C.confirm) {
            C.onclick = ' var ret_confirm = confirm("' + C.confirm + '"); if(!ret_confirm){ return false;} ';
            C.confirm = null
        }
        C.href = A;
        return this.start_tag_for("a", C) + B + this.tag_end("a")
    };
    EJS.Helpers.prototype.submit_link_to = function (B, A, C) {
        if (!B) {
            var B = "null"
        }
        if (!C) {
            var C = {}
        }
        C.onclick = C.onclick || "";
        if (C.confirm) {
            C.onclick = ' var ret_confirm = confirm("' + C.confirm + '"); if(!ret_confirm){ return false;} ';
            C.confirm = null
        }
        C.value = B;
        C.type = "submit";
        C.onclick = C.onclick + (A ? this.url_for(A) : "") + "return false;";
        return this.start_tag_for("input", C)
    };
    EJS.Helpers.prototype.link_to_if = function (F, B, A, D, C, E) {
        return this.link_to_unless((F == false), B, A, D, C, E)
    };
    EJS.Helpers.prototype.link_to_unless = function (E, B, A, C, D) {
        C = C || {};
        if (E) {
            if (D && typeof D == "function") {
                return D(B, A, C, D)
            } else {
                return B
            }
        } else {
            return this.link_to(B, A, C)
        }
    };
    EJS.Helpers.prototype.link_to_unless_current = function (B, A, C, D) {
        C = C || {};
        return this.link_to_unless(this.is_current_page(A), B, A, C, D)
    };
    EJS.Helpers.prototype.password_field_tag = function (A, C, B) {
        return this.input_field_tag(A, C, "password", B)
    };
    EJS.Helpers.prototype.select_tag = function (D, G, H, F) {
        F = F || {};
        F.id = F.id || D;
        F.value = G;
        F.name = D;
        var B = "";
        B += this.start_tag_for("select", F);
        for (var E = 0; E < H.length; E++) {
            var C = H[E];
            var A = {value: C.value};
            if (C.value == G) {
                A.selected = "selected"
            }
            B += this.start_tag_for("option", A) + C.text + this.tag_end("option")
        }
        B += this.tag_end("select");
        return B
    };
    EJS.Helpers.prototype.single_tag_for = function (A, B) {
        return this.tag(A, B, "/>")
    };
    EJS.Helpers.prototype.start_tag_for = function (A, B) {
        return this.tag(A, B)
    };
    EJS.Helpers.prototype.submit_tag = function (A, B) {
        B = B || {};
        B.type = B.type || "submit";
        B.value = A || "Submit";
        return this.single_tag_for("input", B)
    };
    EJS.Helpers.prototype.tag = function (C, E, D) {
        if (!D) {
            var D = ">"
        }
        var B = " ";
        for (var A in E) {
            if (E[A] != null) {
                var F = E[A].toString()
            } else {
                var F = ""
            }
            if (A == "Class") {
                A = "class"
            }
            if (F.indexOf("'") != -1) {
                B += A + '="' + F + '" '
            } else {
                B += A + "='" + F + "' "
            }
        }
        return "<" + C + B + D
    };
    EJS.Helpers.prototype.tag_end = function (A) {
        return "</" + A + ">"
    };
    EJS.Helpers.prototype.text_area_tag = function (A, C, B) {
        B = B || {};
        B.id = B.id || A;
        B.name = B.name || A;
        C = C || "";
        if (B.size) {
            B.cols = B.size.split("x")[0];
            B.rows = B.size.split("x")[1];
            delete B.size
        }
        B.cols = B.cols || 50;
        B.rows = B.rows || 4;
        return this.start_tag_for("textarea", B) + C + this.tag_end("textarea")
    };
    EJS.Helpers.prototype.text_tag = EJS.Helpers.prototype.text_area_tag;
    EJS.Helpers.prototype.text_field_tag = function (A, C, B) {
        return this.input_field_tag(A, C, "text", B)
    };
    EJS.Helpers.prototype.url_for = function (A) {
        return 'window.location="' + A + '";'
    };
    EJS.Helpers.prototype.img_tag = function (B, C, A) {
        A = A || {};
        A.src = B;
        A.alt = C;
        return this.single_tag_for("img", A)
    }

})();


//spin.min.js
(function (t, e) {
    if (!t.Koowa) t.Koowa = {};
    if (typeof exports == "object") module.exports = e(); else if (typeof define == "function" && define.amd) define(e); else t.Koowa.Spinner = e()
})(this, function () {
    "use strict";
    var t = ["webkit", "Moz", "ms", "O"], e = {}, i;

    function o(t, e) {
        var i = document.createElement(t || "div"), o;
        for (o in e) i[o] = e[o];
        return i
    }

    function n(t) {
        for (var e = 1, i = arguments.length; e < i; e++) t.appendChild(arguments[e]);
        return t
    }

    var r = function () {
        var t = o("style", {type: "text/css"});
        n(document.getElementsByTagName("head")[0], t);
        return t.sheet || t.styleSheet
    }();

    function s(t, o, n, s) {
        var a = ["opacity", o, ~~(t * 100), n, s].join("-"), f = .01 + n / s * 100,
            l = Math.max(1 - (1 - t) / o * (100 - f), t), u = i.substring(0, i.indexOf("Animation")).toLowerCase(),
            d = u && "-" + u + "-" || "";
        if (!e[a]) {
            r.insertRule("@" + d + "keyframes " + a + "{" + "0%{opacity:" + l + "}" + f + "%{opacity:" + t + "}" + (f + .01) + "%{opacity:1}" + (f + o) % 100 + "%{opacity:" + t + "}" + "100%{opacity:" + l + "}" + "}", r.cssRules.length);
            e[a] = 1
        }
        return a
    }

    function a(e, i) {
        var o = e.style, n, r;
        i = i.charAt(0).toUpperCase() + i.slice(1);
        for (r = 0; r < t.length; r++) {
            n = t[r] + i;
            if (o[n] !== undefined) return n
        }
        if (o[i] !== undefined) return i
    }

    function f(t, e) {
        for (var i in e) t.style[a(t, i) || i] = e[i];
        return t
    }

    function l(t) {
        for (var e = 1; e < arguments.length; e++) {
            var i = arguments[e];
            for (var o in i) if (t[o] === undefined) t[o] = i[o]
        }
        return t
    }

    function u(t) {
        var e = {x: t.offsetLeft, y: t.offsetTop};
        while (t = t.offsetParent) e.x += t.offsetLeft, e.y += t.offsetTop;
        return e
    }

    function d(t, e) {
        return typeof t == "string" ? t : t[e % t.length]
    }

    var p = {
        lines: 12,
        length: 7,
        width: 5,
        radius: 10,
        rotate: 0,
        corners: 1,
        color: "#000",
        direction: 1,
        speed: 1,
        trail: 100,
        opacity: 1 / 4,
        fps: 20,
        zIndex: 2e9,
        className: "spinner",
        top: "auto",
        left: "auto",
        position: "relative"
    };

    function c(t) {
        if (typeof this == "undefined") return new c(t);
        this.opts = l(t || {}, c.defaults, p)
    }

    c.defaults = {};
    l(c.prototype, {
        spin: function (t) {
            this.stop();
            var e = this, n = e.opts,
                r = e.el = f(o(0, {className: n.className}), {position: n.position, width: 0, zIndex: n.zIndex}),
                s = n.radius + n.length + n.width, a, l;
            if (t) {
                t.insertBefore(r, t.firstChild || null);
                l = u(t);
                a = u(r);
                f(r, {
                    left: (n.left == "auto" ? l.x - a.x + (t.offsetWidth >> 1) : parseInt(n.left, 10) + s) + "px",
                    top: (n.top == "auto" ? l.y - a.y + (t.offsetHeight >> 1) : parseInt(n.top, 10) + s) + "px"
                })
            }
            r.setAttribute("role", "progressbar");
            e.lines(r, e.opts);
            if (!i) {
                var d = 0, p = (n.lines - 1) * (1 - n.direction) / 2, c, h = n.fps, m = h / n.speed,
                    y = (1 - n.opacity) / (m * n.trail / 100), g = m / n.lines;
                (function v() {
                    d++;
                    for (var t = 0; t < n.lines; t++) {
                        c = Math.max(1 - (d + (n.lines - t) * g) % m * y, n.opacity);
                        e.opacity(r, t * n.direction + p, c, n)
                    }
                    e.timeout = e.el && setTimeout(v, ~~(1e3 / h))
                })()
            }
            return e
        }, stop: function () {
            var t = this.el;
            if (t) {
                clearTimeout(this.timeout);
                if (t.parentNode) t.parentNode.removeChild(t);
                this.el = undefined
            }
            return this
        }, lines: function (t, e) {
            var r = 0, a = (e.lines - 1) * (1 - e.direction) / 2, l;

            function u(t, i) {
                return f(o(), {
                    position: "absolute",
                    width: e.length + e.width + "px",
                    height: e.width + "px",
                    background: t,
                    boxShadow: i,
                    transformOrigin: "left",
                    transform: "rotate(" + ~~(360 / e.lines * r + e.rotate) + "deg) translate(" + e.radius + "px" + ",0)",
                    borderRadius: (e.corners * e.width >> 1) + "px"
                })
            }

            for (; r < e.lines; r++) {
                l = f(o(), {
                    position: "absolute",
                    top: 1 + ~(e.width / 2) + "px",
                    transform: e.hwaccel ? "translate3d(0,0,0)" : "",
                    opacity: e.opacity,
                    animation: i && s(e.opacity, e.trail, a + r * e.direction, e.lines) + " " + 1 / e.speed + "s linear infinite"
                });
                if (e.shadow) n(l, f(u("#000", "0 0 4px " + "#000"), {top: 2 + "px"}));
                n(t, n(l, u(d(e.color, r), "0 0 1px rgba(0,0,0,.1)")))
            }
            return t
        }, opacity: function (t, e, i) {
            if (e < t.childNodes.length) t.childNodes[e].style.opacity = i
        }
    });

    function h() {
        function t(t, e) {
            return o("<" + t + ' xmlns="urn:schemas-microsoft.com:vml" class="spin-vml">', e)
        }

        r.addRule(".spin-vml", "behavior:url(#default#VML)");
        c.prototype.lines = function (e, i) {
            var o = i.length + i.width, r = 2 * o;

            function s() {
                return f(t("group", {coordsize: r + " " + r, coordorigin: -o + " " + -o}), {width: r, height: r})
            }

            var a = -(i.width + i.length) * 2 + "px", l = f(s(), {position: "absolute", top: a, left: a}), u;

            function p(e, r, a) {
                n(l, n(f(s(), {
                    rotation: 360 / i.lines * e + "deg",
                    left: ~~r
                }), n(f(t("roundrect", {arcsize: i.corners}), {
                    width: o,
                    height: i.width,
                    left: i.radius,
                    top: -i.width >> 1,
                    filter: a
                }), t("fill", {color: d(i.color, e), opacity: i.opacity}), t("stroke", {opacity: 0}))))
            }

            if (i.shadow) for (u = 1; u <= i.lines; u++) p(u, -2, "progid:DXImageTransform.Microsoft.Blur(pixelradius=2,makeshadow=1,shadowopacity=.3)");
            for (u = 1; u <= i.lines; u++) p(u);
            return n(e, l)
        };
        c.prototype.opacity = function (t, e, i, o) {
            var n = t.firstChild;
            o = o.shadow && o.lines || 0;
            if (n && e + o < n.childNodes.length) {
                n = n.childNodes[e + o];
                n = n && n.firstChild;
                n = n && n.firstChild;
                if (n) n.opacity = i
            }
        }
    }

    var m = f(o("group"), {behavior: "url(#default#VML)"});
    if (!a(m, "transform") && m.adj) h(); else i = a(m, "animation");
    return c
});


//files.utilities.js
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */

if(!Files) var Files = {};

Files.Filesize = function(size) {
    this.size = size;
};

Files.Filesize.prototype.units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];

Files.Filesize.prototype.humanize = function() {
    var i = 0,
        size = this.size;

    while (size >= 1024) {
        size /= 1024;
        i++;
    }

    return (i === 0 || size % 1 === 0 ? size : size.toFixed(2)) + ' ' + Koowa.translate(this.units[i]);
};

Files.urlEncoder = function(value)
{
    value = encodeURI(value);

    var replacements = {'\\?': '%3F', '#': '%23'}

    for(var key in replacements)
    {   var regexp = new RegExp(key, 'g');
        value = value.replace(regexp, replacements[key]);
    }

    return value;
};

Files.escape = function(string)
{
	let entityMap = {
		"&": "&amp;",
		"<": "&lt;",
		">": "&gt;",
		'"': '&quot;',
		"'": '&#39;',
		"/": '&#x2F;'
	};

	return String(string).replace(/[&<>"'\/]/g, function (s) {return entityMap[s]});
};

Files.FileTypes = {};
Files.FileTypes.map = {
	'audio': ['aif','aiff','alac','amr','flac','ogg','m3u','m4a','mid','mp3','mpa','wav','wma'],
	'video': ['3gp','avi','flv','mkv','mov','mp4','mpg','mpeg','rm','swf','vob','wmv'],
	'image': ['bmp','gif','jpg','jpeg','png','psd','tif','tiff'],
	'document': ['doc','docx','rtf','txt','xls','xlsx','pdf','ppt','pptx','pps','xml'],
	'archive': ['7z','gz','rar','tar','zip']
};

Files.getFileType = function(extension) {
	var type = 'document',
        map = Files.FileTypes.map;

	extension = extension.toLowerCase();

    for (var key in map) {
        if (map.hasOwnProperty(key)) {
            var extensions = map[key];
            if (extensions.indexOf(extension) != -1) {
                type = key;
                break;
            }
        }
    }

	return type;
};



//files.state.js
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */

if (!Files) var Files = {};

Files.State = new Class({
	Implements: Options,
	data: {},
	defaults: {},
	options: {
		defaults: {}
	},
	initialize: function(options) {
		this.setOptions(options);

		if (this.options.data) {
            Object.append(this.data, this.options.data);
		}
		if (this.options.defaults) {
            Object.append(this.defaults, this.options.defaults);
            Object.append(this.data, this.defaults);
		}
	},
	getData: function() {
		return this.data;
	},
	setDefaults: function() {
		this.set(this.defaults);

		return this;
	},
	set: function(key, value) {
		if (typeOf(key) == 'object') {
            Object.append(this.data, key);
		} else {
			this.data[key] = value;
		}

		return this;
	},
	get: function(key, def) {
		return this.data[key] || def;
	},
	unset: function(key) {
		delete this.data[key];
	}
});



//files.template.js
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */

if(!Files) var Files = {};

(function() {

var cache = {};

Files.Template = new Class({
	Implements: [Events],
	render: function(layout) {
		var tmpl = this.template;

		layout = layout || 'default';

		if (layout !== 'default') {
			tmpl = layout+'_'+tmpl;
		}

		this.fireEvent('beforeRender', {layout: layout, template: tmpl});

		var rendered = new EJS({element: tmpl}).render(this),
			result = new Files.Template[layout.capitalize()](rendered);

		this.fireEvent('afterRender', {layout: layout, template: tmpl, result: result});

		return result;
	}
});

Files.Template.Details = new Class({
	initialize: function(html) {
		var el = new Element('div', {html: html}).getElement('table');
		if (el) {
			return el;
		}
		else {
			var str = '<table><tbody>'+html+'</tbody></table>';
			return new Element('div', {html: str}).getElement('tr');
		}
	}
});

Files.Template.Default = new Class({
	initialize: function(html) {
		return new Element('div', {html: html}).getFirst();
	}
});

Files.Template.Icons = new Class({
	initialize: function(html) {
		return new Element('div', {html: html}).getFirst();
	}
});

Files.Template.Compact = new Class({
	initialize: function(html) {
		var el = new Element('div', {html: html}).getElement('div');
		if (el) {
			return el;
		}
		else {
			var str = '<table><tbody>'+html+'</tbody></table>';
			return new Element('div', {html: str}).getElement('tr');
		}

		//return new Element('div', {html: html}).getFirst();
	}
});

})();



//files.grid.js
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */

if(!Files) var Files = {};

Files.Grid = new Class({
	Implements: [Events, Options],
	layout: 'icons',
	options: {
		onClickFolder: function (){},
		onClickFile: function (){},
		onClickImage: function (){},
		onDeleteNode: function (){},
		onSwitchLayout: function (){},
		switchers: '.k-js-layout-switcher',
		layout: false,
		spinner_container: '.k-loader-container',
		batch_delete: false,
		icon_size: 150,
		types: null // null for all or array to filter for folder, file and image
	},

	initialize: function(container, options) {
		this.setOptions(options);

		this.spinner_container = kQuery(this.options.spinner_container);

		this.nodes = new Hash();
		this.container = document.id(container);

		if (this.options.switchers) {
			this.options.switchers = document.getElements(this.options.switchers);
		}

		if (this.options.batch_delete) {
			this.options.batch_delete = document.getElement(this.options.batch_delete);
		}

		if (this.options.layout) {
			this.setLayout(this.options.layout);
		}
		this.render();
		this.attachEvents();
	},
	attachEvents: function() {

		var that = this,
			createEvent = function(selector, event_name) {
				that.container.addEvent(selector, function(e) {
					e.stop();
					that.fireEvent(event_name, arguments);
				});
			};
		createEvent('click:relay(.files-folder a.navigate)', 'clickFolder');
		createEvent('click:relay(.files-file a.navigate)', 'clickFile');
		createEvent('click:relay(.files-image a.navigate)', 'clickImage');

		/*
		 * Checkbox events
		 */
		var fireCheck = function(e) {
		    if(e.target.match('a.navigate')) {
		        return;
		    }
			if (e.target.get('tag') == 'input') {
				e.target.setProperty('checked', !e.target.getProperty('checked'));
			}
			var box = e.target.getParent('.files-node-shadow');
			if (!box) {
				box = e.target.match('.files-node') ? e.target :  e.target.getParent('.files-node');
			}

			that.checkNode(box.retrieve('row'));
		};

		this.container.addEvent('click:relay(div.js-select-node)', fireCheck.bind(this));
    	this.container.addEvent('click:relay(input.files-select)', fireCheck.bind(this));

        // Check the box when user clicks on the row
        this.container.addEvent('click', function(event) {
            if (that.layout !== 'details') {
                return;
            }

            var target = event.target;

            if (target.get('tag') === 'a' || target.get('tag') === 'input') {
                return;
            }

            if (target.get('tag') === 'i' && target.hasClass('icon-download')) {
                return;
            }

            if (target.get('tag') === 'span' && target.getParent().get('tag') === 'a') {
            	return;
			}

            var node = target.getParent('.files-node-shadow') || target.getParent('.files-node');

            if(node) {
                row = node.retrieve('row');

                if (row) {
                    that.checkNode(row);
                }
            }
        });

		/*
		 * Delete events
		 */
		var deleteEvt = function(e) {
			if (e.stop) {
				e.stop();
			}

			var box = e.target.getParent('.files-node-shadow');
			if (!box) {
				box = e.target.match('.files-node') ? e.target :  e.target.getParent('.files-node');
			}

			this.erase(box.retrieve('row').path);
		}.bind(this);

		this.container.addEvent('click:relay(.delete-node)', deleteEvt);

		that.addEvent('afterDeleteNodeFail', function(context) {
			var xhr = context.xhr,
				response = JSON.decode(xhr.responseText, true);

			if (response && response.error) {
				alert(response.error);
			}
		});

		if (this.options.batch_delete) {
			var chain = new Chain(),
				chain_call = function() {
					chain.callChain();
				};

			this.addEvent('afterCheckNode', function() {
				var checked = this.container.getElements('input[type=checkbox]:checked');
				this.options.batch_delete.setProperty('disabled', !checked.length);
			}.bind(this));

			this.options.batch_delete.addEvent('click', function(e) {
				e.stop();

				var file_count = 0,
					files = [],
					folder_count = 0,
					folders = [],
					checkboxes = this.container.getElements('input[type=checkbox]:checked.files-select')
					.filter(function(el) {
						if (el.checked) {
							var box = el.getParent('.files-node-shadow') || el.getParent('.files-node'),
								name = box.retrieve('row').name;

							if (el.getParent('.files-node').hasClass('files-folder')) {
								folder_count++;
								folders.push(name)
							} else {
								file_count++;
								files.push(name);
							}
							return true;
						}
					});

				var message = '';
				// special case for single deletes
				if (file_count+folder_count === 1) {
					message = Koowa.translate('You are deleting {item}. Are you sure?', {
                        item: folder_count ? folders[0] : files[0]
                    });
				} else {
					var count = file_count+folder_count,
					    items = Koowa.translate('{count} files and folders');

                    message = Koowa.translate('You are deleting {items}. Are you sure?');

					if (!folder_count && file_count) {
						items = Koowa.translate('{count} files');
					} else if (folder_count && !file_count) {
						items = Koowa.translate('{count} folders');
					}

					items   = items.replace('{count}', count);
					message = message.replace('{items}', items);
				}

				if (!checkboxes.length || !confirm(message)) {
					return false;
				}

				that.addEvent('afterDeleteNode', chain_call);
				that.addEvent('afterDeleteNodeFail', chain_call);

				checkboxes.each(function(el) {
					if (!el.checked) {
						return;
					}
					chain.chain(function() {
						deleteEvt({target: el});
					});
				});
				chain.chain(function() {
					that.removeEvent('afterDeleteNode', chain_call);
					that.removeEvent('afterDeleteNodeFail', chain_call);
					chain.clearChain();
				});
				chain.callChain();
			}.bind(this));
		}

		if (this.options.switchers) {
            this.options.switchers.addEvent('click', function(e) {
                e.stop();
                var layout = this.get('data-layout');
                that.setLayout(layout);
                that._updateSwitchers(layout);
            });
		}

		if (this.options.icon_size) {
			var size = this.options.icon_size;
			this.addEvent('beforeRenderObject', function(context) {
				context.object.icon_size = size;
			});
		}

		this.container.addEvent('click:relay(.k-js-files-sortable)', function(event) {
			var header = event.target.match('th') ? event.target : event.target.getParent('th'),
				state  = {
					sort: header.get('data-name'),
					direction: 'asc'
				};

			if (header.hasClass('k-js-files-sorted')) {
				state.direction = 'desc';
			}

			that.setState(state);

			that.fireEvent('setState', state);
		});

		var input = kQuery('.k-search__field', '#files-canvas'),
			empty_button = kQuery(".k-search__empty"),
			send = function(value) {
				var state = {search: typeof value === 'undefined' ? input.val() : value};

				that.setState(state);
				that.fireEvent('setState', state);

				if (!state.search || state.search === '') {
					empty_button.removeClass("k-is-visible");
				}
			};

		input.blur(function() {
			send();
		})
		.on('keypress', function(event) {
			if (event.which === 13) { // enter key
				send();
				input.blur();
			}
		})
		.on('input', function(event) {
			var v = kQuery(this).val();

			if (v) {
				empty_button.addClass("k-is-visible");
			} else {
				empty_button.removeClass("k-is-visible");
			}

		});

		if (input.val()) {
			empty_button.addClass("k-is-visible");
		}

		kQuery('.k-search__empty', '#files-canvas').click(function() {
			event.preventDefault();

			if (input.val()) {
				input.val('');
				send('');
			}
		});
	},
	setState: function(state) {
		if (typeof state.search !== 'undefined') {
			var search = document.id('files-canvas').getElement('.search_button');
			if (search) {
				search.set('value', state.search);
			}
		}

		var headers = this.container.getElements('.k-js-files-sortable'),
			header  = headers.filter('[data-name="'+state.sort+'"]')[0];

		if (!header) {
			return;
		}

		headers.removeClass('k-js-files-sorted').removeClass('k-js-files-sorted-desc');

		kQuery('.k-js-sort-icon').remove();

		header.addClass('k-js-files-sorted'+(state.direction === 'asc' ? '' : '-desc'));

		var icon = kQuery('<span class="k-js-sort-icon k-icon-sort-'+(state.direction === 'asc' ? 'ascending' : 'descending')+'" />');

		kQuery('th[data-name="'+state.sort+'"]').find('a').append(icon);
	},
	/**
	 * fire_events is used when switching layouts so that client events to
	 * catch the user interactions don't get messed up
	 */
	checkNode: function(row, fire_events) {
		var box = row.element,
		    node = row.element.match('.files-node') ? row.element : row.element.getElement('.files-node'),
			checkbox = box.getElement('input[type=checkbox]')
			;
		if (fire_events !== false) {
			this.fireEvent('beforeCheckNode', {row: row, checkbox: checkbox});
		}

		var old = checkbox.getProperty('checked');

		var card = node.getElement('.k-card');

		if (old) {
			node.removeClass('k-is-selected');

			if (card) card.removeClass('k-is-selected');
		} else {
			node.addClass('k-is-selected');

			if (card) card.addClass('k-is-selected');
		}

		row.checked = !old;
		checkbox.setProperty('checked', !old);

		if (fire_events !== false) {
			this.fireEvent('afterCheckNode', {row: row, checkbox: checkbox});
		}

	},
	erase: function(node) {
		if (typeof node === 'string') {
			node = this.nodes.get(node);
		}
		if (node) {
			this.fireEvent('beforeDeleteNode', {node: node});
			var success = function() {
				if (node.element) {
					node.element.dispose();
				}

				this.nodes.erase(node.path);

				this.fireEvent('afterDeleteNode', {node: node});
			}.bind(this),
				failure = function(xhr) {
					this.fireEvent('afterDeleteNodeFail', {node: node, xhr: xhr});
				}.bind(this);
			node['delete'](success, failure);
		}
	},
	render: function() {
		this.fireEvent('beforeRender');

		this.container.empty();
		this.root = new Files.Grid.Root(this.layout);
		this.container.adopt(this.root.element);

		this.renew();

		this.setFootable();

		this.fireEvent('afterRender');
	},
	setFootable: function() {
        var $footable = kQuery('.k-js-responsive-table');

        if ($footable.length && this.layout === 'details')
        {
			if (!$footable.hasClass('footable'))
			{
				$footable.footable({
					toggleSelector: '.footable-toggle',
					breakpoints: {
						phone: 400,
						tablet: 600,
						desktop: 800
					}
				});
			}
			else $footable.trigger('footable_redraw');
        }
	},
	renderObject: function(object, position) {
		position = position || 'alphabetical';

		this.fireEvent('beforeRenderObject', {object: object, position: position});

		object.element = object.render(this.layout);
		object.element.store('row', object);

		if (position == 'last') {
			this.root.adopt(object.element, 'bottom');
		}
		else if (position == 'first') {
			this.root.adopt(object.element);
		}
		else {
			var index = this.nodes.filter(function(node){
				return node.type == object.type;
			}).getKeys();

			if (index.length === 0) {
				if (object.type === 'folder') {
					var keys = this.nodes.getKeys();
					if (keys.length) {
						// there are files so append it before the first file
						var target = this.nodes.get(keys[0]);
						object.element.inject(target.element, 'before');
					}
					else {
						this.root.adopt(object.element, 'bottom');
					}
				}
				else {
					this.root.adopt(object.element, 'bottom');
				}

			}
			else {
				index.push(object.path);
				index = index.sort();

				var obj_index = index.indexOf(object.path);
				var length = index.length;
				if (obj_index === 0) {
					var target = this.nodes.get(index[1]);
					object.element.inject(target.element, 'before');
				}
				else {
					var target = obj_index+1 === length ? index[length-2] : index[obj_index-1];
					target = this.nodes.get(target);
					object.element.inject(target.element, 'after');
				}
			}
		}

		this.fireEvent('afterRenderObject', {object: object, position: position});

		return object.element;
	},
	getCount: function() {
		return this.nodes.getLength();
	},
	reset: function() {
		this.fireEvent('beforeReset');

		this.nodes.each(function(node) {
			if (node.element) {
				node.element.dispose();
			}
			this.nodes.erase(node.path);
		}.bind(this));

		this.fireEvent('afterReset');
	},
	insert: function(object, position) {
		this.fireEvent('beforeInsertNode', {object: object, position: position});

		if (!this.options.types || this.options.types.contains(object.type)) {
			this.renderObject(object, position);

			this.nodes.set(object.path, object);

			this.fireEvent('afterInsertNode', {node: object, position: position});
		}
	},
	/**
	 * Insert multiple rows, possibly coming from a JSON request
	 */
	insertRows: function(rows) {
		var data = {rows: rows};

		this.fireEvent('beforeInsertRows', data);

        Object.each(data.rows, function(row) {
			var cls = Files[row.type.capitalize()];
			var item = new cls(row);
			this.insert(item, 'last');
		}.bind(this));

		if (this.options.icon_size) {
			this.setIconSize(this.options.icon_size);
		}

		this.fireEvent('afterInsertRows', data);
	},
	renew: function() {
		this.fireEvent('beforeRenew');

		var folders = this.getFolders(),
			files = this.getFiles(),
			that = this,
			renew = function(node) {
				var node = that.nodes.get(node);

				if (node.element) {
					node.element.dispose();
				}
				that.renderObject(node, 'last');

				if (node.checked) {
					that.checkNode(node, false);
				}
			};
		folders.each(renew);
		files.each(renew);

		this.fireEvent('afterRenew');
	},
	setLayout: function(layout) {
		if (layout) {
			this.fireEvent('beforeSetLayout', {layout: layout});

			this.layout = layout;
			if (this.options.switchers) {
                this._updateSwitchers(layout);
			}

			this.fireEvent('afterSetLayout', {layout: layout});

			this.render();
		}

	},
	getFolders: function() {
		return this.nodes.filter(function(node) {
			return node.type === 'folder';
		}).getKeys().sort();
	},
	getFiles: function() {
		return this.nodes.filter(function(node) {
			return node.type === 'file' || node.type == 'image';
		}).getKeys().sort();
	},
	setIconSize: function(size) {
		this.fireEvent('beforeSetIconSize', {size: size});

		this.options.icon_size = size;

		if (this.nodes.getKeys().length && this.layout == 'icons') {
			this.container.getElements('.imgTotal').setStyles({
	            width: size + 'px',
	            height: (size * 0.75) + 'px'
	        });
	        this.container.getElements('.imgOutline .ellipsis').setStyle('width', size + 'px');
		}

    	this.fireEvent('afterSetIconSize', {size: size});
	},
    spin: function(){
		this.spinner_container.removeClass('k-is-hidden');
    },
    unspin: function(){
		this.spinner_container.addClass('k-is-hidden');
		kodekitUI.gallery();
		kodekitUI.sidebarToggle();
    },
    /**
     * Updates the active state on the switchers
     * @param layout   string, current layout
     * @private
     */
    _updateSwitchers: function(layout){
        this.options.switchers.removeClass('k-is-active').filter(function(el) {
            return el.get('data-layout') == layout;
        }).addClass('k-is-active');
    }
});

Files.Grid.Root = new Class({
	Implements: Files.Template,
	template: 'container',
	initialize: function(layout) {
		this.element = this.render(layout);
	},
	adopt: function(element, position) {
		position = position || 'top';
		var parent = this.element;
		if (this.element.get('tag') == 'table') {
			parent = this.element.getElement('tbody');
		}

		if (element.get('tag') === 'tr') {
			var tbody = parent.getElement('tbody');
			if (tbody) {
				parent = tbody;
			}
		}

        //Legacy
        if (!element.injectInside) element.injectInside = element.inject;
		element.injectInside(parent, position);
	}
});



//files.tree.js
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */

if(!Files) var Files = {};

(function($){

    /**
     * Files.Tree is a wrapper for Koowa.Tree, which wraps jqTree
     * @type extend Koowa.Tree
     */
    Files.Tree = Koowa.Tree.extend({
        /**
         * Get the default options
         * @returns options combined with the defaults from parent classes
         */
        getDefaults: function(){

            var self = this,
                defaults = {
                    initial_response: false,
                    autoOpen: 0, //root.open = true on previous script
                    onSelectNode: function(){},
                    dataFilter: function(response){
                        return self.filterData(response);
                    }
                };

            return $.extend(true, {}, this.supr(), defaults); // get the defaults from the parent and merge them
        },
        filterData: function(response) {

            var that = this;

            var data = response.entities,
                parse = function(item, parent)
                {
                    var path = (!parent && that.options.root_path) ? that.options.root_path + '/' : ''; // Prepend root folder if set
                    path += (parent && parent.path) ? parent.path+'/' : '';
                    path += item.name;

                    //Parse attributes
                    //@TODO check if 'type' is necessary
                    item = $.extend(item, {
                        id: path,
                        path: path,
                        url: '#'+path,
                        type: 'folder'
                    });

                    if (item.children) {
                        var children = [];
                        Object.each(item.children, function(child) {
                            children.push(parse(child, item));
                        });
                        item.children = children;
                    }

                    return item;
                };

            if (response.meta.total) {
                Object.each(data, function(item, key) {
                    parse(item);
                });
            }

            return this.parseData(data);
        },
        /**
         * Customized parseData method due to using json, in a already nested data format
         * @param list json returned data
         * @returns data
         */
        parseData: function(list){
            var tree = {
                label: this.options.root.text,
                url: '#',
                children: list
            };

            if (this.options.root_path)
            {
                tree.id = this.options.root_path;
                tree.url = '#' + tree.id;
            }

            return [tree];
        },
        fromUrl: function(url, callback) {

            var self = this;
            this.tree('loadDataFromUrl', url, null, function(response){
                /**
                 * @TODO refactor chaining support to this.selectPath so it works even when the tree isn't loaded yet
                 */
                if(Files.app && Files.app.hasOwnProperty('active')) self.selectPath(Files.app.active);

                if (callback) {
                    callback(response);
                }
            });

        },
        /**
         * Select a path, pass '' to select the root
         * @param path string
         */
        selectPath: function(path) {

            var node = this.tree('getNodeById', path);

            if(!node) {
                var tree = this.tree('getTree');
                node = tree.children.length ? tree.children[0] : null;
            }

            this.tree('selectNode', node);
        },
        /**
         * Append a node to the tree
         * Required properties are 'id' and 'label', other properties are optional.
         * If no parent specified then the node is appended to the current selected node.
         * Pass parent as null for adding the node to root
         *
         * This API is intended for adding user created nodes, don't use this API to add multiple items or to refresh
         * the tree with updated data from the server.
         * Use fromUrl instead, as it's performance optimized for that purpose.
         *
         * @param row
         * @param parent    optional    Node instance, pass 'null' to force the node to be added to the root
         */
        appendNode: function(row, parent){

            if(parent === undefined) parent = this.tree('getSelectedNode');
            if(parent === false)     parent = this.tree('getTree').children[0]; //Get the root node when nothing is selected

            var node, data = $.extend(true, {}, row, {
                path: row.id,
                url: '#'+row.id,
                type: 'folder'
            });

            /**
             * If there's siblings, make sure it's added in alphabetical order
             */
            if(parent && parent.children && parent.children.length) {
                var name = data.label.toLowerCase();
                if(parent.children[0].name.toLowerCase() > name) {
                    node = this.tree('addNodeBefore', data, parent.children[0]);
                } else if(parent.children[parent.children.length - 1].name.toLowerCase() < name) {
                    node = this.tree('appendNode', data, parent);
                } else {
                    var i = 0;
                    while(parent.children[i].name.toLowerCase() < name) {
                        i++;
                    }
                    node = this.tree('addNodeBefore', data, parent.children[i]);
                }
            } else {
                node = this.tree('appendNode', data, parent);
            }
            /**
             * @TODO please investigate:
             * It may be counter-productive to always navigate into newly created folders, investigate if
             * just selecting the folder in the grid is a better workflow as it allows creating multiple folders with
             * lesser clicking around.
             */
            this.tree('selectNode', node);

            return this;
        },

        /**
         * Remove a node by path
         * @param path
         */
        removeNode: function(path){

            var node = this.tree('getNodeById', path);
            if(node) {
                this.tree('removeNode', node);
            }

        },

        attachHandlers: function(){

            this._attachHandlers(); // Attach needed events from Koowa.Tree._attachHandlers

            var options = this.options, self = this, initial = this.options.initial_response;

            this.element.bind({
                'tree.init': function(){
                    self.element.on('tree.select', function(event){

                        var element;
                        if(event.node) { // When event.node is null, it's actually a deselect event
                            element = $(event.node.element);

                            self.tree('openNode', event.node); // open the selected node, if not open already

                            //Fire custom select node handler
                            if(!initial) {
                                options.onSelectNode(event.node);
                            } else {
                                initial = false;
                            }
                        }
                        if(event.node && !event.node.hasOwnProperty('is_open') && event.node.getLevel() === 2) {
                            self.scrollIntoView(event.node, self.element, 300);
                        }

                        /**
                         * Sidebar.js will fire a resize event when it sets the height on load, we want our animated scroll
                         * to happen after that, but not on future resize events as it would confuse the user experience
                         */

                        self.element.one('resize', function(){
                            if(self.tree('getSelectedNode')) {
                                self.scrollIntoView(self.tree('getSelectedNode'), self.element, 900);
                            }
                        });
                    });
                },
                // Animate a scroll to the node being opened so child elements scroll into view
                'tree.open': function(event) {
                    self.scrollIntoView(event.node, self.element, 300);
                }
            });

        }
    });

}(window.kQuery));


//files.row.js
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */

if(!Files) var Files = {};

Files.Row = new Class({
	Implements: [Options, Events, Files.Template],
	initialize: function(object, options) {
		this.setOptions(options);

        Object.each(object, function(value, key) {
			this[key] = value;
		}.bind(this));

        if (typeof this.name !== 'string') {
            this.name = '';
        }

		if (!this.path) {
			this.path = (object.folder ? object.folder+'/' : '') + object.name;
		}
		this.identifier = this.path;

		this.filepath = (object.folder ? this.encodePath(object.folder)+'/' : '') + this.encode(object.name);
	},
	encodePath: function(path, encoder) {
		var parts = path.split('/');

		if (!encoder) {
			encoder = this.encode;
		}

		parts = parts.map(function(part) {
			return encoder(part);
		});

		return parts.join('/');
	},
	encode: function(string) {
		return string;
	},
	realpath: function(string) {
		return string;
	}
});

Files.File = new Class({
	Extends: Files.Row,

	type: 'file',
	template: 'file',
	initialize: function(object, options) {
		this.parent(object, options);

		if (Files.app) {
			this.baseurl = Files.app.baseurl;
		}
		
		this.size = new Files.Filesize(this.metadata.size);
		this.filetype = Files.getFileType(this.metadata.extension);

		this.client_cache = false;
	},
	getModifiedDate: function(formatted) {
        if (this.metadata.modified_date) {
            var date = new Date();
            date.setTime(this.metadata.modified_date*1000);
            if (formatted) {
				return date.toLocaleString('default', { year: 'numeric', month: 'short', day: 'numeric' });
            } else {
                return date;
            }
        }

        return null;
	},
	'delete': function(success, failure) {
		this.fireEvent('beforeDeleteRow');

		var that = this,
			request = new Request.JSON({
				url: Files.app.createRoute({view: 'file', folder: that.folder, name: that.name}),
				method: 'post',
				data: {
					'_method': 'delete',
					'csrf_token': Files.token
				},
				onSuccess: function(response) {
					if (typeof success == 'function') {
						success(response);
					}
					that.fireEvent('afterDeleteRow', {status: true, response: response, request: this});
				},
				onFailure: function(xhr) {
					if (xhr.status == 204 || xhr.status == 1223) {
						// Mootools thinks it failed, weird
						return this.onSuccess();
					}

					response = xhr.responseText;
					if (typeof failure == 'function') {
						failure(xhr);
					}
					else {
						response = JSON.decode(xhr.responseText, true);
						error = response && response.error ? response.error : Koowa.translate('An error occurred during request');
						alert(error);
					}

					that.fireEvent('afterDeleteRow', {status: false, response: response, request: this, xhr: xhr});
				}
			});
		request.send();
	}
});

Files.Image = new Class({
	Extends: Files.File,

	type: 'image',
	template: 'image',
	initialize: function(object, options) {
		this.parent(object, options);

		this.image = this.baseurl+'/'+this.encodePath(this.filepath, this.realpath);

		this.client_cache = false;
	},
	getThumbnail: function(success, failure) {
		var that = this,
			request = new Request.JSON({
				url: Files.app.createRoute({view: 'file', name: that.name, folder: that.folder, thumbnails: Files.app.options.thumbnails}),
				method: 'get',
				onSuccess: function(response, responseText) {
					if (typeof success == 'function') {
						success(response);
					}
				},
				onFailure: function(xhr) {
					response = xhr.responseText;

					if (typeof failure == 'function') {
						failure(xhr);
					}
					else {
						response = JSON.decode(xhr.responseText, true);
						error = response && response.error ? response.error : Koowa.translate('An error occurred during request');
						alert(error);
					}
				}
			});
		request.send();
	}
});


Files.Folder = new Class({
	Extends: Files.Row,

	type: 'folder',
	template: 'folder',

	'add': function(success, failure, complete) {
		this.fireEvent('beforeAddRow');

		var that = this;
			request = new Request.JSON({
				url: Files.app.createRoute({view: 'folder', name: that.name, folder: Files.app.getPath()}),
				method: 'post',
				data: {
					'_action': 'add',
					'csrf_token': Files.token
				},
				onSuccess: function(response) {
					if (typeof success == 'function') {
						success(response);
					}

					that.fireEvent('afterAddRow', {status: true, response: response, request: this});
				},
				onFailure: function(xhr) {
					response = xhr.responseText;

					if (typeof failure == 'function') {
						failure(xhr);
					}
					else {
						response = JSON.decode(xhr.responseText, true);
						error = response && response.error ? response.error : Koowa.translate('An error occurred during request');
						alert(error);
					}

					that.fireEvent('afterAddRow', {status: false, response: response, request: this, xhr: xhr});
				},
                onComplete: function(response){
                    if (typeof complete == 'function') {
                        complete(response);
                    }
                }
			});
		request.send();
	},
	'delete': function(success, failure) {
		var that = this,
			request = new Request.JSON({
				url: Files.app.createRoute({view: 'folder', folder: Files.app.getPath(), name: that.name}),
				method: 'post',
				data: {
					'_method': 'delete',
					'csrf_token': Files.token
				},
				onSuccess: function(response) {
					if (typeof success == 'function') {
						success(response);
					}

					that.fireEvent('afterDeleteRow', {status: true, response: response, request: this});
				},
				onFailure: function(xhr) {
					if (xhr.status == 204 || xhr.status == 1223) {
						// Mootools thinks it failed, weird
						return this.onSuccess();
					}
					response = xhr.responseText;

					if (typeof failure == 'function') {
						failure(xhr);
					}
					else {
						response = JSON.decode(xhr.responseText, true);
						error = response && response.error ? response.error : Koowa.translate('An error occurred during request');
						alert(error);
					}

					that.fireEvent('afterDeleteRow', {status: false, response: response, request: this, xhr: xhr});
				}
			});
		request.send();
	}
});


//files.paginator.js
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */
Files.Paginator = new Class({
	Implements: [Options, Events],
	state: null,
	values: {
		total: 0,
		limit: 0,
		offset: 0,
		page_total: 0,
		page_current: 0
	},
	initialize: function(element, options) {
		if (options.state) {
			this.state = options.state;
			this.setData(this.state.getData());
		}

		this.setOptions(options);

		var element = document.id(element);

		this.element = element;
		this.elements = {
			page_total: element.getElement('.page-total'),
			page_current: element.getElement('.page-current'),
			page_start: element.getElement('.start a'),
			page_next: element.getElement('.next a'),
			page_prev: element.getElement('.prev a'),
			page_end: element.getElement('.end a'),
			page_container: element.getElement('.k-pagination__pages'),
			pages: {},
			limit_box: element.getElement('select')
		};
		this.setValues();

		this.element.addEvent('click:relay(a)', function(e) {
			e.stop();
			if (e.target.get('data-enabled') == '0') {
				return;
			}
			this.fireEvent('clickPage', e.target);
		}.bind(this));
		this.elements.limit_box.addEvent('change', function(e) {
			e.stop();
			this.fireEvent('changeLimit', this.elements.limit_box.get('value'));
		}.bind(this));

	},
	setValues: function() {
		this.fireEvent('beforeSetValues');

		var values = this.values, els = this.elements;

		this.setPageData(els.page_start, {offset: 0});

		this.setPageData(els.page_end, {offset: (values.page_total-1)*values.limit});

		this.setPageData(els.page_prev, {offset: Math.max(0, (values.page_current-2)*values.limit)});

		var offset = Math.min(((values.page_total-1)*values.limit),(values.page_current*values.limit));
		this.setPageData(els.page_next, {offset: offset});

		this.element.getElements('.k-js-page').dispose();
		var i = 1;
		while (i <= values.page_total) {
            var el = null,
				active = false;

			if (i == values.page_current) {
				active = true;
				el = new Element('span', {text: i});
			} else if (i < 3 || Math.abs(i-values.page_total) < 2 || Math.abs(i-(values.page_current)) < 2) {
                // Add a page link for the first and last two pages or a page around the current one
				el = new Element('a', {
					href: '#',
					text: i,
					'data-limit': values.limit,
					'data-offset': (i-1)*values.limit
				});
			} else if (Math.abs(i-values.page_current) < 3) {
                // Add an ellipsis for the gaps
                el = new Element('span', {html: '&hellip;'});
            }

            if (el) {
            	var li = new Element('li', {
            		'class': 'k-js-page ' + (active ? 'k-is-active': '')
				});
				el.inject(li);
                els.pages[i] = el;
                li.inject(els.page_container);
				els.page_next.getParent().inject(els.page_container);
            }

			i++;
		}

		els.limit_box.set('value', values.limit);

		this.fireEvent('afterSetValues');
	},
	setPageData: function(page, data) {
		this.fireEvent('beforeSetPageData', {page: page, data: data});

		var limit = data.limit || this.values.limit;
		page.set('data-limit', limit);
		page.set('data-offset', data.offset);

		var method = data.offset == this.values.offset ? 'addClass' : 'removeClass',
            wrap = page;
        if(wrap.getParent() !== this.elements.page_container && wrap.getParent() !== this.element && !wrap.getParent().match('ul')) {
            wrap = wrap.getParent();

            if(wrap.getParent() !== this.elements.page_container && wrap.getParent() !== this.element && !wrap.getParent().match('ul')) {
                wrap = wrap.getParent();
            }
        }
		wrap[method]('off disabled');
		page.set('data-enabled', (data.offset != this.values.offset)-0);

		this.fireEvent('afterSetPageData', {page: page, data: data});
	},
	setData: function(data) {
		this.fireEvent('beforeSetData', {data: data});

		var values = this.values;
		if (data.total == 0) {
			values.limit = this.state.get('limit');
			values.offset = this.state.get('offset');
			values.total = 0;
			values.page_total = 1;
			values.page_current = 1;
		} else {
            Object.each(data, function(value, key) {
				values[key] = value;
			});

			values.limit = Math.max(values.limit, 1);
			values.offset = Math.max(values.offset, 0);

			if (values.limit > values.total) {
				values.offset = 0;
			}

			if (!values.limit) {
				values.offset = 0;
				values.limit = values.total;
			}

			values.page_total = Math.ceil(values.total/values.limit);

			if (values.offset > values.total) {
				values.offset = (values.page_total-1)*values.limit;
			}
			values.page_current = Math.floor(values.offset/values.limit)+1;
		}

		this.fireEvent('afterSetData', {data: data});
	}
});



//files.pathway.js
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */

(function(){

    //This is a private function, we don't need it to be a method to Files.Pathway, or globally available in general
    var updatePathway = function(list, pathway, buffer, width, offset){

        var index = width - offset, sizes = buffer[index] || buffer.max, last = list.getChildren().length - 1;

        list.getChildren().each(function(folder, index){
            if(index > 0 && index < last) {
                //folder.setStyle('width', sizes[index].value);
                if(sizes[index].value <= 48) {
                    folder.removeClass('overflow-ellipsis');
                } else {
                    folder.addClass('overflow-ellipsis');
                }
            }
        });

    };


    if (!this.Files) this.Files = {};

    Files.Pathway = new Class({
        Implements: [Options],
        element: false,
        options: {
            element: 'files-pathway',
            offset: 8
        },
        initialize: function(options) {
            this.setOptions(options);
        },
        setPath: function(app){

            if (!this.element) {

                this.element = document.id(this.options.element);
            }

            this.element.getParent();
            var pathway = this.element;
            pathway.empty();
            var list = new Element('ul'),
                wrap = function(app, title, path, icon){
                    var result, link;

                    result = new Element('li', {
                        events: {
                            click: function(){
                                app.navigate(path);
                            }
                        }
                    });

                    link = new Element('a', {
                        'class': 'k-breadcrumb__content',
                        html: title
                    });

                    result.grab(link);

                    if(icon) {
                       // link.grab(new Element('span', {'class': 'divider'}), 'top');
                    }
                    return result;
                };

            var path = '', root_path = '';

            // Check if we are rendering sub-trees
            if (app.options.root_path) {
                path = root_path = app.options.root_path;
            }

            var root = wrap(app, '<span class="k-icon-home" aria-hidden="true"></span><span class="k-visually-hidden">'+app.container.title+'</span>', path, false)
                        .addClass('k-breadcrumb__home')
                        .getElement('a').getParent();

            list.adopt(root);

            var base_path = app.getPath();

            if (root_path) {
                base_path = base_path.replace(root_path, '');
            }

            var folders = base_path.split('/'), path = root_path;

            folders.each(function(title){
                if(title.trim()) {
                    path += path ? '/'+title : title;
                    list.adopt(wrap(app, title, path, true));
                }
            });

            list.getLast().addClass('k-breadcrumb__active');

            pathway.setStyle('visibility', 'hidden');
            pathway.adopt(list);

            //Whenever the path changes, the buffer used in the resize handler is outdated, so have to be reattached
            if(this.pathway) {
                window.removeEvent('resize', this.pathway);

                this.pathway = false;
            }

            if(list.getChildren().length > 2) {

                var widths = {}, ceil = 0, offset = list.getFirst().getSize().x + list.getLast().getSize().x;
                list.getChildren().each(function(item, i){
                    if(item.match(':first-child') || item.match(':last-child')) return;
                    var x = item.getSize().x;
                    widths[i] = {key: i, value: x};
                    ceil += x;
                });

                //Create resize buffer
                var buffer = {}, queue = ceil;
                buffer[ceil] = buffer.max = widths;
                while(queue > 0) {
                    --queue;

                    var max = {key: null, value: 0}, sizelist = {};
                    for (var key in widths){
                        if (widths.hasOwnProperty(key)) {
                            var item = widths[key];
                            if(item.value > max.value) max = item;
                            sizelist[key] = {key: item.key, value: item.value};
                        }
                    }
                    --sizelist[max.key].value;

                    buffer[queue] = sizelist;
                    widths = sizelist;
                }

                updatePathway(list, pathway, buffer, pathway.getSize().x, offset);
                pathway.setStyle('visibility', 'visible');

                this.pathway = function(){
                    updatePathway(list, pathway, buffer, pathway.getSize().x, offset)
                };
                window.addEvent('resize', this.pathway);

            } else {
                pathway.setStyle('visibility', 'visible');
            }
        }
    });

})();



//files.app.js
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @codekit-prepend "files.utilities.js", "files.state.js", "files.template.js", "files.grid.js", files.tree.js", "files.row.js", "files.paginator.js", "files.pathway.js"
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */

if(!Files) var Files = {};

Files.blank_image = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAMAAAAoyzS7AAAABGdBTUEAALGPC/xhBQAAAAd0SU1FB9MICA0xMTLhM9QAAAADUExURf///6fEG8gAAAABdFJOUwBA5thmAAAACXBIWXMAAAsSAAALEgHS3X78AAAACklEQVQIHWNgAAAAAgABz8g15QAAAABJRU5ErkJggg==';

Files.App = new Class({
    Implements: [Events, Options],

    _tmpl_cache: {},
    active: null,
    title: '',
    cookie: null,
    options: {
        root_path: '',
        root_text: 'Root folder',
        cookie: {
            name: null,
            path: '/'
        },
        persistent: true,
        thumbnails: true,
        types: null,
        container: null,
        active: null,
        pathway: {
            element: 'files-pathway'
        },
        state: {
            defaults: {}
        },
        tree: {
            enabled: true,
            element: '#files-tree'
        },
        grid: {
            element: 'files-grid',
            batch_delete: '#toolbar-delete',
            icon_size: 150
        },
        paginator: {
            element: 'files-paginator'
        },
        folder_dialog: {
            view: '#files-new-folder-modal',
            input: '#files-new-folder-input',
            open_button: '.js-open-folder-modal',
            create_button: '#files-new-folder-create',
            //Fires when the form for creating a new folder is submitted
            onSubmit: function(){},
            //Fires when the json request for creating a folder is complete
            onCreate: function(folder_dialog){
                folder_dialog.input.set('value', '');
                folder_dialog.create_button.removeClass('valid').setProperty('disabled', 'disabled');
            },
            onOpen: function(folder_dialog){
                kQuery.magnificPopup.open({
                    items: {
                        src: kQuery(folder_dialog.view),
                        type: 'inline'
                    },
                    callbacks: {
                        open: function(){
                            setTimeout(function(){
                                folder_dialog.input.focus();
                            }, 100);
                        }
                    }
                });
            },
            onClose: function(){
                kQuery.magnificPopup.close();
            },
            onInit: function(folder_dialog){
                var input    = kQuery(folder_dialog.input),
                    trigger  = kQuery(folder_dialog.create_button),
                    validate = function(){
                        if (kQuery.trim(kQuery(this).val())) {
                            trigger.addClass('valid').prop('disabled', false);
                        } else {
                            trigger.removeClass('valid').prop('disabled', true);
                        }
                    };

                input.on('change', validate);

                if(window.addEventListener) {
                    input.on('input', validate);
                } else {
                    input.on('keyup', validate);
                }
            }
        },
        uploader_dialog: {
            view: '#files-upload',
            button: '#toolbar-upload'
        },
        move_dialog: {
            view: '#files-move-modal',
            button: '.btn-primary',
            open_button: '#toolbar-move'
        },
        copy_dialog: {
            view: '#files-copy-modal',
            button: '.btn-primary',
            open_button: '#toolbar-copy'
        },
        history: {
            enabled: true
        },
        router: {
            defaults: {
                option: 'com_files',
                view: 'files',
                format: 'json'
            }
        },
        initial_response: null,
        refresh_button: '#toolbar-refresh',

        onAfterSetGrid: function(){
            window.addEvent('resize', function(){
                this.setDimensions(true);
            }.bind(this));
            this.grid.addEvent('onAfterRenew', function(){
                this.setDimensions(true);
            }.bind(this));
            this.addEvent('onUploadFile', function(){
                this.setDimensions(true);
            }.bind(this));
        },
        onAfterNavigate: function(path) {
            if (path !== undefined) {
                this.setTitle(this.folder.name || this.container.title);
                kQuery('#upload-files-to, .upload-files-to').text(path ? path : this.container.title);
            }
        },
        onBeforeSetContainer: function(response) {
            if (typeof response.container !== 'undefined') {
                response.container.title = this.options.root_text;
            }
        }
    },
    initialize: function(options) {
        if (Files.Config) {
            Object.merge(options, Files.Config);
        }

        this.setOptions(options);

        this.fireEvent('onInitialize', this);

        if (this.options.cookie.name) {
            this.cookie = this.options.cookie.name;
        }

        if (this.cookie === null && this.options.persistent && this.options.container) {
            var container = typeof this.options.container === 'string' ? this.options.container : this.options.container.slug;
            this.cookie = 'com.files.container.'+container;
        }

        if(this.options.pathway) {
            this.setPathway();
        }
        this.setState();
        this.setHistory();
        this.setGrid();
        this.setPaginator();

        var url = this.getUrl();
        if (url.getData('container') && !this.options.container) {
            this.options.container = url.getData('container');
        }

        if (url.getData('folder')) {
            this.options.active = url.getData('folder');
        }

        if (this.options.thumbnails) {
            this.addEvent('afterSelect', function(resp) {
                this.setThumbnails();
            });
        }

        if(this.options.uploader_dialog) {
            this.setUploaderDialog();
        }

        if (this.options.container) {
            this.setContainer(this.options.container);
        }

        if (this.options.refresh_button) {
            var refresh = document.getElement(this.options.refresh_button),
                self    = this;

            if (refresh) {
                refresh.addEvent('click', function(e) {
                    e.stop();
                    self.navigate(undefined, 'stateless', true);
                    self.setTree();
                });
            }
        }
    },
    setState: function() {
        this.fireEvent('beforeSetState');

        if (this.cookie && this.options.persistent) {
            var state = Cookie.read(this.cookie+'.state'),
                obj   = JSON.decode(state, true);

            if (obj) {
                if (typeof this.getUrl().getData('folder') === 'undefined') {
                    this.options.active = obj.folder;
                }

                delete obj.folder;

                this.options.state.defaults = Object.merge({}, this.options.state.defaults, obj);

            }

        }

        var opts = this.options.state;
        this.state = new Files.State(opts);

        this.fireEvent('afterSetState');
    },
    setHistory: function() {
        this.fireEvent('beforeSetHistory');

        if (this.options.history.enabled) {
            var that = this;
            this.history = History;
            window.addEvent('popstate', function(e) {
                if (e) { e.stop(); }

                var state = History.getState(),
                    old_state = that.state.getData(),
                    new_state = state.data,
                    state_changed = false;

                Object.each(old_state, function(value, key) {
                    if (state_changed === true) {
                        return;
                    }
                    if (new_state && new_state[key] && value !== new_state[key]) {
                        state_changed = true;
                    }
                });

                if (that.container && (state_changed || that.active !== state.data.folder)) {
                    var set_state = Object.append({}, state.data);
                    ['option', 'view', 'layout', 'folder', 'container'].each(function(key) {
                        delete set_state[key];
                    });
                    that.state.set(set_state);
                    that.navigate(state.data.folder, 'stateless');
                }
            });
            this.addEvent('afterNavigate', function(path, type) {
                if (type !== 'stateless' && that.history) {
                    var obj = {
                            folder: that.active,
                            container: that.container ? that.container.slug : null
                        },
                        state = this.state.getData();

                    Object.each(state, function(value, key) {
                        if (typeof value !== 'function' && typeof value !== 'undefined') {
                            obj[key] = value;
                        }
                    });

                    var method = type === 'initial' ? 'replaceState' : 'pushState';
                    var url = that.getUrl().setData(obj, true).set('fragment', '').toString();

                    that.history[method](obj, null, url);
                }
            });
        }

        this.fireEvent('afterSetHistory');
    },
    /**
     * type can be 'stateless' for no state or 'initial' to use replaceState
     * response can be set if you want to set the results without an AJAX request.
     */
    navigate: function(path, type, revalidate_cache, response) {
        this.fireEvent('beforeNavigate', [path, type]);
        if (path !== undefined) {
            if (this.active) {
                // Reset offset if we are changing folders
                this.state.set('offset', 0);
            }
            this.active = path == '/' ? '' : path;
        }

        this.grid.reset();
        this.grid.spin();

        var parts = this.active.split('/'),
            name = parts[parts.length ? parts.length-1 : 0],
            folder = parts.slice(0, parts.length-1).join('/'),
            that = this,
            url_builder = function(url) {
                if (revalidate_cache) {
                    url['revalidate_cache'] = 1;
                }
                url['_'] = Date.now(); // Ignore client cache
                return this.createRoute(url);
            }.bind(this),
            handleResponse = function(response) {
                if (response) {
                    if (response.status !== false) {
                        Object.each(response.entities, function(item) {
                            if (!item.baseurl) {
                                item.baseurl = that.baseurl;
                            }
                        });

                        that.grid.insertRows(response.entities);

                        if (!response.partial) {
                            that.grid.unspin();
                            that.response = response;
                            that.fireEvent('afterSelect', response);
                        }
                    } else if (response.error) {
                        alert(response.error);
                    }
                }
            };

        this.folder = new Files.Folder({'folder': folder, 'name': name});

        if (response) {
            handleResponse(response);
            this.grid.unspin();
        } else {
            this.fetch(this.folder.path, url_builder)
                .done(handleResponse).progress(handleResponse);
        }

        if (this.cookie) {
            var data = kQuery.extend(true, {}, this.state.data);
            data.folder = this.active;
            Cookie.write(this.cookie+'.state', JSON.encode(data), this.options.cookie);
        }

        this.fireEvent('afterNavigate', [path, type]);
    },
    fetch: function(path, url_builder) {
        var self = this,
            deferred = kQuery.Deferred(),
            fail = function(xhr) {
                var response = JSON.decode(xhr.responseText, true);

                if (response && response.error) {
                    alert(response.error);
                }
            },
            query = Object.append({view: 'nodes', folder: path}, this.state.getData());

        if (this.ajax_cache) {
            this.ajax_cache.abort();
        }

        if (typeof query.limit !== 'undefined' && query.limit !== 0) {
            this.ajax_cache = kQuery.getJSON(url_builder(query)).fail(fail);
            return this.ajax_cache;
        }

        query.limit = 100;

        var done = function(response) {
            if (!response || typeof response.entities === 'undefined' || typeof response.meta === 'undefined') {
                deferred.reject('');

                return;
            }

            if (response.meta.offset + response.entities.length < response.meta.total) {
                response.partial = true;
                deferred.notify(response);

                query.offset = response.meta.offset+response.meta.limit;
                self.ajax_cache = kQuery.getJSON(url_builder(query)).done(done).fail(fail);
            } else {
                response.completed = true;
                deferred.resolve(response);
            }
        };

        self.ajax_cache = kQuery.getJSON(url_builder(query)).done(done).fail(fail);

        return deferred.promise();
    },

    setContainer: function(container) {
        var setter = function(item) {
            this.fireEvent('beforeSetContainer', {container: item});

            this.container = item;
            this.baseurl = Files.sitebase + '/' + item.relative_path;

            this.active = '';

            if (this.uploader) {
                if (this.container.parameters.allowed_extensions) {
                    this.uploader.settings.filters = [
                        {title: Koowa.translate('All Files'), extensions: this.container.parameters.allowed_extensions.join(',')}
                    ];
                }

                if (this.container.parameters.maximum_size) {
                    this.uploader.settings.max_file_size = this.container.parameters.maximum_size;
                    var max_size = document.id('upload-max-size');
                    if (max_size) {
                        max_size.set('html', new Files.Filesize(this.container.parameters.maximum_size).humanize());
                    }
                }
            }

            if (this.container.parameters.thumbnails === true) {
                this.state.set('thumbnails', this.options.thumbnails);
            }
            else this.options.thumbnails = false;

            if (this.options.types !== null) {
                this.options.grid.types = this.options.types;
                this.state.set('types', this.options.types);
            }

            if (this.options.folder_dialog && document.getElement(this.options.folder_dialog.view) && document.getElement(this.options.folder_dialog.view).getElement('form')) {
                this.setFolderDialog();
            }

            if (this.options.move_dialog) {
                this.move_dialog = new Files.MoveDialog(this.options.move_dialog);
            }

            if (this.options.copy_dialog) {
                this.copy_dialog = new Files.CopyDialog(this.options.copy_dialog);
            }

            this.fireEvent('afterSetContainer', {container: item});

            this.setTree();

            this.active = this.options.active || '';
            this.options.active = '';

            if (typeof this.options.initial_response === 'string') {
                this.options.initial_response = JSON.decode(this.options.initial_response);
            }

            this.navigate(this.active, 'initial', false, this.options.initial_response);
        }.bind(this);

        if (typeof container === 'string') {
            new Request.JSON({
                url: this.createRoute({view: 'container', slug: container, container: false}),
                method: 'get',
                onSuccess: function(response) {
                    setter(response.entities[0]);
                }.bind(this)
            }).send();
        } else {
            setter(container);
        }
    },
    setPaginator: function() {
        this.fireEvent('beforeSetPaginator');

        var opts = this.options.paginator,
            state = this.state;

        Object.append(opts, {
            'state' : state,
            'onClickPage': function(el) {
                this.state.set('limit', el.get('data-limit'));
                this.state.set('offset', el.get('data-offset'));

                this.navigate();
            }.bind(this),
            'onChangeLimit': function(limit) {
                this.state.set('limit', limit);

                // Recalculate offset
                var total = Files.app.paginator.values.total,
                    offset = Files.app.paginator.values.offset;

                if (total) {
                    offset = limit ? Math.floor((offset/limit)*limit) : 0;
                }

                this.state.set('offset', offset);

                this.navigate();
            }.bind(this)
        });
        this.paginator = new Files.Paginator(opts.element, opts);


        var that = this;
        that.addEvent('afterSelect', function(response) {
            that.paginator.setData({
                limit: response.meta.limit,
                offset: response.meta.offset,
                total: response.meta.total
            });
            that.paginator.setValues();
        });

        this.fireEvent('afterSetPaginator');
    },
    setGrid: function() {
        this.fireEvent('beforeSetGrid');

        var that = this,
            opts = this.options.grid,
            key = this.cookie+'.grid.layout';

        if (this.cookie && Cookie.read(key)) {
            opts.layout = Cookie.read(key);
        }

        Object.append(opts, {
            'onClickFolder': function(e) {
                var target = document.id(e.target),
                    node = target.getParent('.files-node-shadow') || target.getParent('.files-node'),
                    path = node.retrieve('row').path;
                if (path) {
                    this.navigate(path);
                }
            }.bind(this),
            'onClickImage': function(e) {
                var target = document.id(e.target),
                    node = target.getParent('.files-node-shadow') || target.getParent('.files-node'),
                    row = node.retrieve('row'),
                    img = that.createRoute({view: 'file', format: 'html', name: row.name, folder: row.folder});

                if (img) {
                    kQuery.magnificPopup.open({
                        items: {
                            src: img,
                            type: 'image'
                        }
                    });
                }
            },
            'onClickFile': function(e) {
                var target = document.id(e.target),
                    node = target.getParent('.files-node-shadow') || target.getParent('.files-node'),
                    row = node.retrieve('row'),
                    copy = Object.append({}, row);

                copy.template = 'file_preview';

                copy = copy.render();

                var element = kQuery(copy);
                element.addClass('mfp-hide k-ui-namespace');
                kQuery.magnificPopup.open({
                    items: {
                        src: element,
                        type: 'inline'
                    }
                });
            },
            'onBeforeRenderObject': function(context) {
                var row = context.object;
                row.download_link = that.createRoute({view: 'file', format: 'html', name: row.name, folder: row.folder});
            }.bind(this),
            'onAfterSetLayout': function(context) {

                if (context.layout === 'icons' || context.layout === 'details') {
                    var layout = context.layout === 'icons' ? 'gallery' : 'table',
                        remove = layout === 'gallery' ? 'table' : 'gallery';

                    this.container.removeClass('k-'+remove).addClass('k-'+layout);
                    kQuery('#files-grid-container').removeClass('k-'+remove+'-container').addClass('k-'+layout+'-container');
                    kQuery('#files-paginator-container').removeClass('k-'+remove+'-pagination').addClass('k-'+layout+'-pagination');
                }

                if (key) {
                    Cookie.write(key, context.layout, that.options.cookie);
                }
            },
            onAfterRender: function() {
                this.setState(that.state.data);

                if (that.grid) {
                    that.setThumbnails();
                    kodekitUI.gallery();
                }
            },
            onSetState: function(state) {
                this.state.set(state);

                this.navigate();
            }.bind(this),
            onAfterInsertRows: function() {
                this.setFootable();
            }
        });
        this.grid = new Files.Grid(this.options.grid.element, opts);

        this.fireEvent('afterSetGrid');
    },
    setTree: function() {
        this.fireEvent('beforeSetTree');

        if (this.options.tree.enabled) {
            var opts = Object.merge({root_path: this.options.root_path}, this.options.tree);
                that = this;

            opts = kQuery.extend(true, {}, {
                onSelectNode: function(node) {
                    if (node.id || node.url) {
                        var path = node && node.id ? node.id : '';
                        if (path != that.active) {
                            that.navigate(path);
                        }
                    }
                },
                root: {
                    text: this.options.root_text
                },
                initial_response: !!this.options.initial_response
            }, opts);
            this.tree = new Files.Tree(kQuery(opts.element), opts);
            var config = {view: 'folders', 'tree': '1', 'limit': '2000'};
            if (this.options.root_path) config.folder = this.options.root_path;
            this.tree.fromUrl(this.createRoute(config));

            this.addEvent('afterNavigate', function(path, type) {
                if(path !== undefined && (!type || (type != 'initial' && type != 'stateless'))) {
                    that.tree.selectPath(path);
                }
            });

            if (this.grid) {
                this.grid.addEvent('afterDeleteNode', function(context) {
                    var node = context.node;
                    if (node.type == 'folder') {
                        that.tree.removeNode(node.path);
                    }
                });
            }
        }

        this.fireEvent('afterSetTree');
    },
    /**
     * Create the folder dialog markup and link up events
     */
    setFolderDialog: function(){

        var self = this;

        this._folder_dialog = {
            view: document.getElement(this.options.folder_dialog.view),
            input: document.getElement(this.options.folder_dialog.input),
            open_button: document.getElement(this.options.folder_dialog.open_button),
            create_button: document.getElement(this.options.folder_dialog.create_button)
        };

        if(this.options.folder_dialog.onInit) {
            this.options.folder_dialog.onInit.call(this, this._folder_dialog);
        }

        if (this._folder_dialog.open_button) {
            this._folder_dialog.open_button.addEvent('click', function(e) {
                e.stop();

                if (this.hasClass('unauthorized')) {
                    return;
                }

                Files.app.openFolderDialog();
            });
        }

        if (this._folder_dialog.view.getElement('form')) {
            this._folder_dialog.view.getElement('form').addEvent('submit', function(e){
                e.stop();

                self._folder_dialog.create_button.setProperty('disabled', 'disabled');

                if(self.options.folder_dialog.onSubmit) {
                    self.options.folder_dialog.onSubmit.call(self, self._folder_dialog);
                }
                var element = self._folder_dialog.input;
                var value = element.get('value').trim();
                if (value.length > 0) {
                    var folder = new Files.Folder({name: value, folder: Files.app.getPath()});
                    folder.add(function(response, responseText) {
                        if (response.status === false) {
                            return alert(response.error);
                        }
                        var el = response.entities[0];
                        var cls = Files[el.type.capitalize()];
                        var row = new cls(el);
                        Files.app.grid.insert(row);
                        Files.app.tree.appendNode({
                            id: row.path,
                            label: row.name
                        });

                        if(self.options.folder_dialog.onCreate) {
                            self.options.folder_dialog.onCreate.call(self, self._folder_dialog);
                        }

                        self.closeFolderDialog();
                    }, null, function() {
                        self._folder_dialog.create_button.setProperty('disabled', false);
                    });
                }
            });
        }

    },
    /**
     * Opens the folder dialog, using the customizable control handle, if the instance exists
     * @return returns a boolean indicating wether there's a folder dialog active
     */
    openFolderDialog: function(){

        if(this.options.folder_dialog) {
            this.options.folder_dialog.onOpen.call(this, this._folder_dialog);
        }

        return !!this.options.folder_dialog;
    },
    /**
     * Closes the folder dialog, using the customizable control handle, if the instance exists
     * @return returns a boolean indicating wether there's a folder dialog active
     */
    closeFolderDialog: function(){

        if(this.options.folder_dialog) {
            this.options.folder_dialog.onClose.call(this, this._folder_dialog);
        }

        return !!this.options.folder_dialog;
    },
    /**
     * Sets the IE Flash workaround and FLOC fix, and hooks the markup with events for the uploader dialog
     */
    setUploaderDialog: function(){
        var self   = this,
            button = document.getElement(this.options.uploader_dialog.button),
            view   = document.getElement(this.options.uploader_dialog.view);

        if (view) {
            this._tmp_uploader = new Element('div', {style: 'display:none'}).inject(document.body);

            document.getElement(this.options.uploader_dialog.view).getParent().inject(this._tmp_uploader).setStyle('visibility', '');
        }

        if (button) {
            button.addEvent('click', function(e){
                e.stop();

                if (this.hasClass('unauthorized')) {
                    return;
                }

                self.openUploaderDialog();
            });
        }
    },
    /**
     * Opens up the Uploader dialog and performs IE flash workaround
     * @return returns a boolean indicating wether there's a uploader dialog active
     */
    openUploaderDialog: function(){

        if(this.uploader) {
            var self = this, handleClose = function(){
                document.getElement(self.options.uploader_dialog.view).getParent().inject(self._tmp_uploader);
                SqueezeBox.removeEvent('close', handleClose);
            };
            SqueezeBox.addEvent('close', handleClose);
            SqueezeBox.open(document.getElement(self.options.uploader_dialog.view).getParent(), {
                handler: 'adopt',
                size: {x: 700, y: document.getElement(self.options.uploader_dialog.view).getParent().measure(function(){
                    this.setStyle('width', 700);
                    var height = this.getSize().y;
                    this.setStyle('width', '');
                    return height;
                })}
            });
            window.addEvent('QueueChanged', this._changeUploaderDialogHeight.bind(this));
        }

        return !!this.uploader;
    },
    /**
     * Closes the Uploader dialog and performs IE flash workaround
     * @return returns a boolean indicating wether there's a uploader dialog active
     */
    closeUploaderDialog: function(){

        if(this.uploader) {
            SqueezeBox.close();
            window.removeEvent('QueueChanged', this._changeUploaderDialogHeight);
        }

        return !!this.uploader;
    },
    /**
     * Updates the uploader dialog height
     * @private
     */
    _changeUploaderDialogHeight: function(){
        var height = document.getElement(this.options.uploader_dialog.view).getParent().scrollHeight;
        SqueezeBox.resize({x: 700, y: height});
    },
    getUrl: function() {
        return new URI(window.location.href);
    },
    getPath: function() {
        return this.active;
    },
    setThumbnails: function() {
        this.setDimensions(true);

        var nodes = this.grid.nodes,
            that = this;
        if (nodes.getLength())
        {
            nodes.each(function(node)
            {
                var img = node.element.getElement('img.image-thumbnail');

                if (img)
                {
                    img.addEvent('load', function(){
                        this.addClass('loaded');
                    });

                    var source = Files.blank_image;

                    if (node.thumbnail) {
                        source = Files.sitebase + '/' + node.encodePath(node.thumbnail.relative_path, Files.urlEncoder);
                    } else if (node.download_link) {
                        source = node.download_link;
                    }

                    img.set('src', source);

                    (node.element.getElement('.files-node') || node.element).addClass('loaded').removeClass('loading');
                }
            });
        }

    },
    setDimensions: function(force){

        if(!this._cached_grid_width) this._cached_grid_width = 0;

        //Only fire if the cache have changed
        if(this._cached_grid_width != this.grid.root.element.getSize().x || force) {
            var width = this.grid.root.element.getSize().x,
                factor = width/(this.grid.options.icon_size.toInt()+40),
                limit = Math.min(Math.floor(factor), this.grid.nodes.getLength());

            this.grid.root.element.getElements('.files-node-shadow').each(function(element, i, elements){
                element.setStyle('width', (100/limit)+'%');
            }, this);

            this._cached_grid_width = this.grid.root.element.getSize().x;
        }
    },
    setPathway: function() {
        this.fireEvent('beforeSetPathway');

        var pathway = new Files.Pathway(this.options.pathway);
        this.addEvent('afterSetTitle', pathway.setPath.bind(pathway, this));

        this.fireEvent('afterSetPathway');
    },
    setTitle: function(title) {
        this.fireEvent('beforeSetTitle', {title: title});

        this.title = title;

        this.fireEvent('afterSetTitle', {title: title});
    },
    createRoute: function(query) {
        query = Object.merge({}, this.options.router.defaults, query || {});

        if (query.container !== false && !query.container && this.container) {
            query.container = this.container.slug;
        } else {
            delete query.container;
        }

        if (query.format == 'html') {
            delete query.format;
        }

        var route = '?'+new Hash(query).filter(function(value, key) {
            return typeof value !== 'function';
        }).toQueryString();

        return this.options.base_url ? this.options.base_url+route : route;
    }
});



//files.compact.js
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */

if (!Files) Files = {};
Files.Compact = {};

Files.Compact.App = new Class({
	Extends: Files.App,
	Implements: [Events, Options],
    cookie: null,
	options: {
        persistent: false,
		types: ['file', 'image'],
		editor: null,
		preview: 'files-preview',
        state: {
            defaults: {
                limit: 0,
                offset: 0
            }
        },
		grid: {
			layout: 'compact',
			batch_delete: false
		},
		history: {
			enabled: false
		},
        uploader_dialog: false,
        folder_dialog: false,
        copy_dialog: false,
        move_dialog: false
	},

	initialize: function(options) {
		this.parent(options);

		this.editor = this.options.editor;
		this.preview = document.id(this.options.preview);

	},
	setPaginator: function() {
	},
	setGrid: function() {
		var opts = this.options.grid;
		var that = this;
        Object.append(opts, {
			'onClickImage': function(e) {
				var target = document.id(e.target),
				    node = target.getParent('.files-node-shadow') || target.getParent('.files-node');

				node.getParent().getChildren().removeClass('active');
				node.addClass('active');
				var row = node.retrieve('row');
				var copy = Object.append({}, row);
				copy.template = 'details_image';

				that.preview.empty();

				copy.render('compact').inject(that.preview);

				that.preview.getElement('img').set('src', copy.image).setStyle('display', 'block');
			},
			'onClickFile': function(e) {
				var target = document.id(e.target),
			   		node = target.getParent('.files-node-shadow') || target.getParent('.files-node');

				node.getParent().getChildren().removeClass('active');
				node.addClass('active');
				var row = node.retrieve('row');
				var copy = Object.append({}, row);
				copy.template = 'details_file';

				that.preview.empty();

				copy.render('compact').inject(that.preview);
			},
			onAfterRender: function() {
				this.setState(that.state.data);
			},
			onSetState: function(state) {
				this.state.set(state);

				this.navigate();
			}.bind(this)
		});
		this.grid = new Files.Grid(this.options.grid.element, opts);
	}
});



//files.attachments.app.js
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */

if (!Files.Attachments) Files.Attachments = {};

Files.Attachments.App = new Class({
    Extends: Files.App,
    Implements: [Events, Options],
    cookie: false,
    attachments: {},
    options: {
        url: null,
        pathway: false,
        persistent: false,
        types: ['file', 'image'],
        preview:  'files-preview',
        state: {
            defaults: {
                limit: 0,
                offset: 0
            }
        },
        grid: {
            cookie: false,
            layout: 'attachments',
            element: 'attachments-container'
        },
        history: {
            enabled: false
        },
        uploader_dialog: false,
        folder_dialog: false,
        copy_dialog: false,
        move_dialog: false,
        onAfterNavigate: function(path) {
            // Do nothing
        }
    },
    initialize: function(options) {
        this.url = options.url;

        this.parent(options);

        this.preview = document.id(this.options.preview);

        var app = this;

        if (callback = options.callback)
        {
            if (typeof window.parent[callback] == 'function') {
                window.parent[callback](app);
            }
        }
    },
    navigate: function(path, type, revalidate_cache, response) {
        this.fireEvent('beforeNavigate', [path, type]);

        var that = this;

        if (this.url)
        {
            var url = this.url;

            url += '&_' + Date.now();

            that.grid.reset(); // Flush current content.

            this.grid.spin();

            new Request.JSON({
                url: url,
                method: 'get',
                onSuccess: function(response)
                {
                    that.grid.insertRows(response.entities);
                    that.grid.unspin();
                }
            }).send();
        }

        this.fireEvent('afterNavigate', [path, type]);
    },
    setPaginator: function() {
    },
    setGrid: function() {
        var opts = this.options.grid;
        var that = this;

        Object.append(opts, {
            'onClickAttachment': function (e) {
                var target = document.id(e.target),
                    node = target.getParent('.files-node-shadow') || target.getParent('.files-node');

                node.getParent().getChildren().removeClass('active');
                node.addClass('active');
                var row = node.retrieve('row');
                var copy = Object.append({}, row);
                copy.template = 'details_attachment';

                that.preview.empty();

                copy.render('attachments').inject(that.preview);

                if (copy.file.type == 'image')
                {
                    if (copy.file.thumbnail) {
                        that.preview.getElement('img').set('src', Files.sitebase + '/' + row.encodePath(copy.file.thumbnail.relative_path, Files.urlEncoder)).setStyle('display', 'block');
                    } else {
                        that.preview.getElement('img').set('src', that.createRoute({view: 'file', format: 'html', name: copy.file.name, routed: 1}));
                    }
                }

                that.grid.selected = row.name;
            }
        });

        this.grid = new Files.Attachments.Grid(this.options.grid.element, opts);

    }
});

Files.Attachments.Grid = new Class({
    Extends: Files.Grid,
    select: function(node) {
        if (typeof node === 'string') {
            node = this.nodes.get(node);
        }

        var handler = 'click' + node.type.capitalize();

        this.fireEvent(handler, {target: node.element.getElement('a.navigate')});
    },
    unselect: function() {
        this.container.getElements('.files-node').removeClass('active');
        this.selected = null;
    },
    attachEvents: function() {
        var that = this;
        this.container.addEvent('click:relay(.files-attachment a.navigate)', function(e) {
            e.stop();
            that.fireEvent('clickAttachment', arguments);
        });
    },
    /**
     * Insert multiple rows, possibly coming from a JSON request
     */
    insertRows: function(rows) {
        this.fireEvent('beforeInsertRows', {rows: rows});

        Object.each(rows, function(row) {
            var item = new Files.Attachment(row);
            this.insert(item, 'last');
        }.bind(this));

        if (this.options.icon_size) {
            this.setIconSize(this.options.icon_size);
        }

        this.fireEvent('afterInsertRows', {rows: rows});
    },
});

Files.Template.Attachments = new Class({
    initialize: function(html)
    {
        var el = new Element('div', {html: html}).getFirst();

        if (el.getElement('.template-item'))  {
            el = el.getElement('.template-item').getFirst();
        }


        return el;
    }
});

Files.Attachment = new Class({
    Extends: Files.Row,

    type: 'attachment',
    template: 'attachment',
    initialize: function(object, options) {
        this.parent(object, options);

        this.size = new Files.Filesize(this.file.metadata.size);
        this.filetype = Files.getFileType(this.file.metadata.extension);
    },
    delete: function(success, failure) {
        // Do nothing, just call the success event handler.
        if (typeof success == 'function') {
            success();
        }
    },
    getAttachedDate: function(formatted) {
        if (this.attached_on_timestamp) {
            var date = new Date();
            date.setTime(this.attached_on_timestamp*1000);
            if (formatted) {
                return date.toLocaleString('default', { year: 'numeric', month: 'short', day: 'numeric' });
            } else {
                return date;
            }
        }

        return null;
    }
});


//files.uploader.js
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright    Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license        GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */

if (!Files) var Files = {};

(function($) {

    Files.createUploader = function (options) {
        options = $.extend({}, {
            element: '#files-upload-multi',
            container: Files.app.container,
            multi_selection: true,
            url: Files.app.createRoute({
                view: 'file',
                plupload: 1,
                thumbnails: Files.app.options.thumbnails
            }),
            multipart_params: {
                _action: 'add',
                csrf_token: Files.token,
                folder: Files.app.getPath()
            },
            check_duplicates:true,
            chunking:true,
            autostart:true,
            uploaded: function(event, data) {
                var item, row, path,
                    json = data.result.response;

                if (json.status && typeof Files.app !== 'undefined') {
                    item = json.entities[0];
                    path = (item.folder ? item.folder+'/' : '') + item.name;

                    if (typeof Files.app.grid.nodes[path] === 'undefined') {
                        var cls = Files[item.type.capitalize()];
                        row = new cls(item);
                        Files.app.grid.insert(row);
                    } else {
                        row = Files.app.grid.nodes[path];

                        if (item.metadata) {
                            row.metadata = item.metadata;
                            row.size = new Files.Filesize(row.metadata.size);
                        }
                    }

                    if (row.type == 'image')
                    {
                        var image = row.element.getElement('img');

                        if (image) {

                            var setThumbnail = function(row)
                            {
                                var source = Files.blank_image;

                                if (row.thumbnail) {
                                    source = Files.sitebase + '/' + row.encodePath(row.thumbnail.relative_path, Files.urlEncoder);
                                } else if (row.download_link) {
                                    source = row.download_link;
                                }

                                image.set('src', source).addClass('loaded').removeClass('loading');

                                /* @TODO We probably do not need this anymore? Layouts have changed and these elements/classes no longer exist */
                                var element = row.element.getElement('.files-node');

                                if (element) {
                                    element.addClass('loaded').removeClass('loading');
                                }
                            };

                            setThumbnail(row);

                            /* @TODO Test if this is necessary: This is for the thumb margins to recalculate */
                            window.fireEvent('resize');
                        }
                    }
                    Files.app.fireEvent('uploadFile', [row]);
                }
            }

        }, options);

        var element = kQuery(options.element);

        delete options.element;

        if (element.length === 0) {
            return;
        }

        element.uploader(options);

        Files.app.uploader = element.uploader('instance').getUploader();

        Files.app.addEvent('afterNavigate', function(path, type) {
            if (typeof Files.app.uploader !== 'undefined') {
                Files.app.uploader.settings.multipart_params.folder = Files.app.getPath();
            }
        });
    };

})(kQuery);




//files.copymove.js
/**
 * Joomlatools Framework - https://www.joomlatools.com/developer/framework/
 *
 * @copyright	Copyright (C) 2011 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/joomlatools/joomlatools-framework-files for the canonical source repository
 */

if(!Files) var Files = {};

(function($) {

var CopyMoveDialog = Koowa.Class.extend({
    initialize: function(options) {
        this.supr();

        options = {
            view: $(options.view),
            tree: $(options.view).find('.k-js-tree-container'),
            button: $(options.button, options.view),
            open_button: $(options.open_button)
        };

        this.setOptions(options);
        this.attachEvents();
    },
    setTree: function(tree)
    {
        var app = Files.app;

        if (!app.tree)
        {
            var opts = {
                root_path: app.options.root_path,
                root: {
                    text: app.options.root_text
                },
                element: $('<div></div>'),
                initial_response: !!this.options.initial_response
            };

            app.tree = new Files.Tree(opts.element, opts);

            var config = {view: 'folders', 'tree': '1', 'limit': '2000'};

            if (app.options.root_path) config.folder = app.options.root_path;

            app.tree.fromUrl(app.createRoute(config), function () {
                tree.tree('loadData', $.parseJSON(app.tree.tree('toJson')));
            });

            app.addEvent('afterNavigate', function(path, type) {
                if(path !== undefined && (!type || (type != 'initial' && type != 'stateless'))) {
                    app.tree.selectPath(path);
                }
            });

            if (app.grid) {
                app.grid.addEvent('afterDeleteNode', function(context) {
                    var node = context.node;
                    if (node.type == 'folder') {
                        app.tree.removeNode(node.path);
                    }
                });
            }
        }
        else tree.tree('loadData', $.parseJSON(app.tree.tree('toJson')));

        return app.tree;
    },
    attachEvents: function() {
        var self = this;

        if (this.options.open_button) {
            this.options.open_button.click(function(event) {
                event.preventDefault();

                self.show();
            });
        }

        if (this.options.view.find('form')) {
            this.options.view.find('form').submit(function(event) {
                event.preventDefault();

                self.submit();
            });
        }
    },
    show: function() {
        var options = this.options,
            count = Object.getLength(this.getSelectedNodes());

        if (options.open_button.hasClass('unauthorized') || !count) {
            return;
        }

        var tree = new Koowa.Tree(this.options.tree, {
            onCanSelectNode: function (node) {
                return (node.path != Files.app.getPath());
            }
        });

        this.setTree(tree);

        this.getSelectedNodes().each(function(node) {
            var tree_node = tree.tree('getNodeById', node.path);
            if (tree_node) {
                tree.tree('removeNode', tree_node);
            }
        });

        $.magnificPopup.open({
            items: {
                src: $(options.view),
                type: 'inline'
            }
        });
    },
    hide: function() {
        if (this.options.tree instanceof $) {
            this.options.tree.empty();
        }

        $.magnificPopup.close();
    },
    getSelectedNodes: function() {
        return Files.app.grid.nodes.filter(function(row) { return row.checked });
    },
    handleError: function(xhr) {
        var response = JSON.decode(xhr.responseText, true);

        this.hide();

        if (response && response.error) {
            alert(response.error);
        }
    }
});

Files.CopyDialog = CopyMoveDialog.extend({
    submit: function() {
        var self  = this,
            nodes = this.getSelectedNodes(),
            names = Object.values(nodes.map(function(node) { return node.name; })),
            destination = this.options.view.find('.k-js-tree-container').tree('getSelectedNode').path,
            url = Files.app.createRoute({view: 'nodes', folder: Files.app.getPath()});

        if (!names.length) {
            return;
        }

        this.options.button.prop('disabled', true);

        Files.app.grid.fireEvent('beforeCopyNodes', {nodes: nodes});

        $.ajax(url, {
            type: 'POST',
            data: {
                'name' : names, // names are passed in POST to circumvent 2k characters rule in URL
                'destination_folder': destination || '',
                '_action': 'copy',
                'csrf_token': Files.token
            }
        }).done(function(response) {
            var tree = Files.app.tree,
                refresh_tree = false;

            nodes.each(function(node) {
                var tree_node = tree.tree('getNodeById', node.path);
                if (tree_node) {
                    refresh_tree = true;
                }
            });

            Files.app.grid.fireEvent('afterCopyNodes', {nodes: nodes});

            if (refresh_tree) {
                tree.fromUrl(Files.app.createRoute({view: 'folders', 'tree': '1', 'limit': '2000'}));
            }

            self.hide();
        }).fail($.proxy(this.handleError, this))
        .always(function() {
            self.options.button.prop('disabled', false);
        });
    }
});

Files.MoveDialog = CopyMoveDialog.extend({
    submit: function() {
        var self  = this,
            nodes = this.getSelectedNodes(),
            names = Object.values(nodes.map(function(node) { return node.name; })),
            destination = this.options.view.find('.k-js-tree-container').tree('getSelectedNode').path,
            url = Files.app.createRoute({view: 'nodes', folder: Files.app.getPath()});

        if (!names.length) {
            return;
        }

        this.options.button.prop('disabled', true);

        Files.app.grid.fireEvent('beforeMoveNodes', {nodes: nodes});

        $.ajax(url, {
            type: 'POST',
            data: {
                'name' : names, // names are passed in POST to circumvent 2k characters rule in URL
                'destination_folder': destination || '',
                '_action': 'move',
                'csrf_token': Files.token
            }
        }).done(function(response) {
            var tree = Files.app.tree,
                refresh_tree = false;

            nodes.each(function(node) {
                if (node.element) {
                    node.element.dispose();
                }

                Files.app.grid.nodes.erase(node.path);

                var tree_node = tree.tree('getNodeById', node.path);
                if (tree_node) {
                    refresh_tree = true;
                }
            });

            Files.app.grid.fireEvent('afterMoveNodes', {nodes: nodes});

            if (refresh_tree) {
               tree.fromUrl(Files.app.createRoute({view: 'folders', 'tree': '1', 'limit': '2000'}));
            }

            self.hide();
        }).fail($.proxy(this.handleError, this))
        .always(function() {
            self.options.button.prop('disabled', false);
        });
    }
});

})(window.kQuery);

