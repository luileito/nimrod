/*!
 * GenUtils | Luis A. Leiva | MIT license
 * Some generic utilities.
 */

/**
 * @class
 * @description This just documents the CommonJS/AMD/etc. module closure.
 */
;(function(module, global) {

  /**
   * Some generic utilities.
   * @module GenUtils 
   */
  module.exports = {
    /**
     * Get object keys.
     * @alias module:GenUtils.objKeys
     * @param {object} obj  Target object.
     * @return {array}
     */
    objKeys: function(obj) {
      var keys = [];
      for (var k in obj) {
        keys.push(k);
      }
      return keys;
    },
    /**
     * Increase object key by some amount.
     * @alias module:GenUtils.increaseCounter
     * @param {object} obj  Target object.
     * @param {string} key  Target object key.
     * @param {int}    val  New value (default: 1).
     */
    increaseCounter: function(obj, key, val) {
      if (typeof val === 'undefined') val = 1;
      if (typeof obj[key] !== 'undefined') {
        obj[key] += val;
      } else {
        obj[key] = 1;
      }    
    },
    /**
     * Compute bounding box of an element.
     * @alias module:GenUtils.boundingBoxSize
     * @param {object}  elem       Target object.
     * @param {boolean} isVisible  Whether the element is visible or not.
     * @return {object} { area: number, width: number, height: number }
     */
    boundingBoxSize: function(elem, isVisible) {
      var w, h;
      if (!isVisible) {
        var span = document.createElement("span");
        span.innerHTML = elem;
        document.body.appendChild(span);
        w = span.offsetWidth;
        h = span.offsetHeight;
        document.body.removeChild(span);
        span = null;
      } else {
        w = elem.offsetWidth;
        h = elem.offsetHeight;
      }
      return { area: w * h,  height: h, width: w };
    },
    /**
     * Remove leading and trailing whitespaces from string.
     * @alias module:GenUtils.trim
     * @param {string} str Input string.
     * @return {string}
     */ 
    trim: function(str) {
      return str.replace(/^\s+|\s+$/g, "");
    },
    /**
     * Remove HTML tags from string.
     * @alias module:GenUtils.stripTags
     * @param {string} html Input string.
     * @return {string}
     */
    stripTags: function(html) {
      var div = document.createElement("div");
      div.innerHTML = html;
      var txt = div.textContent || div.innerText;
      div = null;
      return txt;
    },
    /**
     * Encode HTML tags.
     * @alias module:GenUtils.encodeEntities
     * @param {string} html Input string.
     * @return {string}
     */
    encodeEntities: function(html) {
      var ta = document.createElement("textarea");
      ta.textContent  = html;
      ta.innerContent = html;
      return ta.innerHTML;
    },
    /**
     * Decode HTML tags.
     * @alias module:GenUtils.decodeEntities
     * @param {string} html Input string.
     * @return {string}
     */
    decodeEntities: function(str) {
      var ta = document.createElement("textarea");
      ta.innerHTML = str;
      var html = ta.value;
      ta = null;
      return html;
    },
    /**
     * Escape HTML tags.
     * @alias module:GenUtils.escapeHTML
     * @param {string} html Input string.
     * @return {string}
     */
    escapeHTML: function(html) {
      var ta = document.createElement("textarea");
      ta.textContent = html;
      return ta.innerHTML;
    },
    /**
     * Unescape HTML tags.
     * @alias module:GenUtils.unescapeHTML
     * @param {string} html Input string.
     * @return {string}
     */
    unescapeHTML: function(str) {
      var ta = document.createElement("textarea");
      ta.innerHTML = str;
      return ta.textContent;
    },
    /**
     * Unescape HTML tags.
     * @alias module:GenUtils.addClass
     * @param {object} elem DOM element.
     * @param {string} cls CSS class name.
     */
    addClass: function(elem, cls) {
      if (elem.className) {
        if (elem.className.indexOf(cls) < 0) elem.className += " " + cls;
      } else {
        elem.className = cls;
      }
    },
    /**
     * Retrieve element dataset.
     * @alias module:GenUtils.getDataset
     * @param {object} elem DOM element.
     * @return {object}
     */
    getDataset: function(elem) {
      var data = {};
      if (elem.dataset) {
        data = elem.dataset;
      } else if (elem.attributes) {
        // Fallback for older browsers
        for (var i = 0, t = elem.attributes.length; i < t; i++) {
          var prefix = "data-", attrib = elem.attributes[i];
          if (attrib.name.indexOf(prefix) === 0) {
            // Don't put "data-" in attr name
            var attrName = attrib.name.substr(prefix.length);
            data[attrName] = attrib.value;
          }
        }
      }
      return data;
    },
    /**
     * Parse element datasets form HTML string.
     * @alias module:GenUtils.parseDatasets
     * @param {string} html Input HTML string.
     * @param {array}  arr  Placeholder used for recursion.
     * @return {array}
     */
    parseDatasets: function(html, arr) {
      var div = document.createElement("div");
      div.innerHTML = html;
      for (var i = 0; i < div.children.length; i++) {
        var child = div.children[i];
        arr.push(child);
        this.parseDatasets(child, arr);
      }
      div = null;
      return arr;
    },
    /**
     * Fire a function after some time delay.
     * @alias module:GenUtils.throttle
     * @param {function} fn   Callback function.
     * @param {int}      time Delay.
     */
    throttle: function(fn, time) {
      var t = 0;
      return function(){
        var args = arguments, ctx = this;
        clearTimeout(t);
        t = setTimeout(function(){
          fn.apply(ctx, args);
        }, time);
      };
    },
    /**
     * Fire a function after some time delay.
     * @alias module:GenUtils.fadeOut
     * @param {object}   elem DOM element.
     * @param {function} fn   Callback function.
     */
    fadeOut: function(elem, callback) {
      var newOpVal = (elem.style.opacity || 1) - 0.05;
      elem.style.opacity = newOpVal;
      elem.style.filter = 'alpha(opacity=' + (newOpVal*100) + ')';
      if (newOpVal > 0) {
        var self = this;
        setTimeout(function(){
          self.fadeOut(elem, callback);
        }, 30);
      } else {
        callback();
      }
    }
  
  };

})('object' === typeof module ? module : {}, this);
