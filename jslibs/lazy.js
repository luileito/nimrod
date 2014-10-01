/*!
 * Lazy | Luis A. Leiva | MIT license
 * A library to load JS libraries on demand.
 */

/**
 * @class
 * @description This just documents the CommonJS/AMD/etc. module closure.
 */
;(function() {

  function createScript(filepath) {
    var scriptElem = document.createElement('script');
    scriptElem.type = "text/javascript";
    scriptElem.src = filepath;
    return scriptElem;
  };

  var _scripts = document.getElementsByTagName('script')
    , _nScript = _scripts.length
    , _currentScript = _scripts[_nScript - 1]
    , _cachedScripts = {}
    , _cachedFiles = []
    ;
  // Remember all loaded scripts
  for (var i = 0; i < _nScript; i++) {
    _cachedFiles.push( _scripts[i].getAttribute("src") );
  }
  
  // Grab path of currently executing script
  var _pathParts = _currentScript.src.split("/");
  _pathParts.splice(_pathParts.length - 1, 1);
  var _path = _pathParts.join("/");
  //var _ext = _pathParts[_pathParts.length - 1] == "src" ? ".js" : ".min.js";
  var _ext = ".js";

  function getScriptUrl(jsModule) {
    return jsModule.indexOf(".js") > 0 ? jsModule : _path + "/" + jsModule + _ext;
  };

  function loadLibFile(jsModule, callback) {
    var src = getScriptUrl(jsModule);
    if (_cachedFiles.indexOf(src) < 0) {
      var script = createScript(src);
      _currentScript.parentNode.insertBefore(script, _currentScript);
      script.onload = function(e) {
        libFileLoaded(src, callback);
      };
      script.onerror = function(e) {
        throw new Error(jsModule + " cannot be loaded.");
      };
      ieLoadFix(script, function() {
        libFileLoaded(src, callback);
      });
    } else {
      callback();
    }
  };

  function libFileLoaded(src, callback) {
    _cachedFiles.push(src);
    callback();
  };
  
  function ieLoadFix(script, callback) {
    if (script.readyState === "loaded" || script.readyState === "complete") {
      callback();
    } else {
      setTimeout(function(){
        ieLoadFix(script, callback);
      }, 100);
    }
  };
  
  function loadScript(jsModule, callback, nopadding) {
    var exports, str, src = getScriptUrl(jsModule), async = (typeof callback === 'function');
    if (src in _cachedScripts) {
      if (async) {
        callback(_cachedScripts[src]);
      } else {
        exports = _cachedScripts[src];
      }
    } else {
      var request = new XMLHttpRequest();
      request.onreadystatechange = function(){
        if (this.readyState == 4) { // Request complete
          if (this.status == 200) { // Request sucessful
            var res = this.responseText;
            if (async) {
              callback(res);
              _cachedScripts[src] = res;
            } else if (this.getResponseHeader('content-type').indexOf('application/json') != -1) {
              exports = JSON.parse(res);
            } else {
              if (nopadding) {
                str = "var exports;" + res + "\n\ntrue;"; 
              } else {
                var source = res.match(/^\s*(?:(['"]use strict['"])(?:;\r?\n?|\r?\n))?\s*((?:.*\r?\n?)*)/);
                str = '(function(){'+source[1]+';var undefined,exports,module={exports:exports};'+source[2]+'\n\nreturn module.exports;})();';
              }
              try {
                exports = eval.apply(window, [str]);
              } catch (err) {
                throw err + ", so " + jsModule + " cannot be exported.";
              } finally {
                _cachedScripts[src] = exports;
              }
            }
          } else {
            throw "Request error: " + jsModule + " cannot be loaded.";
          }
        }
      };
      request.open("GET", src, async);
      request.send();
    }
    return exports;
  };

  /**
   * A library to load scripts on demand.
   * @module Lazy
   */
  window.Lazy = {
    /** 
     * Load a non-modularized file that gets exposed in the global namespace. 
     * @alias module:Lazy.load
     * @param {mixed}     jsLibs    Single library (js file) or array of js files.
     * @param {function}  callback  Callback function after loading the js lib.
     */
    load: function(jsLibs, callback) {
      if (typeof jsLibs === 'string') jsLibs = jsLibs.split(" ");
      for (var i = 0; i < jsLibs.length; i++) {
        loadLibFile(jsLibs[i], function(){
          jsLibs.shift();
          if (jsLibs.length === 0) callback();
        });
      }
    },
    /** 
     * Load a non-modularized file that gets bound to a given variable. 
     * @alias module:Lazy.require
     * @param {string}    jsLib     Single library (js file).
     * @param {function}  callback  Callback function after requiring the js lib.
     */
    require: function(jsLib, callback) {
      return loadScript(jsLib, callback);
    },
    /** 
     * Load a non-modularized file that gets bound to a previously loaded module.
     * @alias module:Lazy.extend
     * @param {string}  jsLib   Single library (js file).
     * @param {object}  module  Module that gets extended with the s lib.
     */
    extend: function(jsLib, module) {
      var res = loadScript(jsLib);
      for (var r in res) module[r] = res[r];
      return module;
    },
    /** 
     * Resolve file path, assuming that everything is located in the same dir.
     * @alias module:Lazy.basepath
     * @param {string}    file      Target file.
     */
    basepath: function(file) {
      return _path + "/" + file;
    },
    /** 
     * A quick'n'dirty method to fire a function upon DOM readiness.
     * @alias module:Lazy.domReady
     * @param {function}  callback  Callback function on DOM ready.
     */
    domReady: function(callback) {
      if (document.addEventListener) {
        document.addEventListener("DOMContentLoaded", function(){
          //document.removeEventListener("DOMContentLoaded", arguments.callee, false);
          callback();
        }, false);
      } else if (document.attachEvent) {
        document.attachEvent("onreadystatechange", function(){
          if (document.readyState === 'complete') {
            //document.detachEvent("onreadystatechange", arguments.callee);
            callback();
          }
        });
      }
    }
  };

})();
