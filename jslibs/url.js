/*!
 * URL | Luis A. Leiva | MIT license
 * Some URL utilities.
 */

/**
 * @class
 * @description This just documents the CommonJS/AMD/etc. module closure.
 */
;(function(module, global) {

  /**
   * Some URL utilities.
   * @module URL 
   */
  module.exports = {
    /**
     * Encodes URL.
     * This function behaves exactly as PHP's urlencode.
     * @alias module:URL.encode
     * @param {string} url The URL.
     * @return {string}
     */
    encode: function(url) {
      return encodeURIComponent(url).replace(/!/g, '%21').replace(/'/g, '%27')
                                    .replace(/\(/g, '%28').replace(/\)/g, '%29').replace(/\*/g, '%2A').replace(/%20/g, '+');
    },
    /**
     * Decode URL.
     * This function behaves exactly as PHP's urldecode.
     * @alias module:URL.decode
     * @param {string} url The URL.
     * @return {string}
     */
    decode: function(url) {
      return decodeURIComponent(url).replace(/%21/g, '!').replace(/%27/g, "'")
                                    .replace(/%28/g, '(').replace(/%29/g, ')').replace(/%2A/g, '*').replace(/\+/g, ' ');
    },
    /**
     * Redirect browser to a given URL.
     * @alias module:URL.navTo
     * @param {string} url The URL.
     */
    navTo: function(url) {
      window.location.href = url;
    },
    /**
     * Reload current page.
     * @alias module:URL.reload
     */
    reload: function() {
      window.location.reload();
    },
    /**
     * Get the raw value of a GET param.
     * @alias module:URL.gup
     * @param {string} name The param name.
     */
    gup: function(name) {
      name = name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
      var regex = new RegExp( "[\\?&]"+name+"=([^&#]*)" );
      var results = regex.exec(window.location.href);

      return results == null ? "" : results[1];
    },
    /**
     * Get the decoded value of a GET param.
     * @alias module:URL.getQueryParam
     * @param {string} name The param name.
     */
    getQueryParam: function(name) {
      return this.decode(this.gup(name));
    }

  };

})('object' === typeof module ? module : {}, this);
