// These are fallback methods mainly for Internet Explorer <= 8

if (!Array.prototype.indexOf) {
  /** 
   * Retrieves the index of an element in array.
   * This is actually a fallback of Array.prototype.indexOf for old browsers. 
   * @global
   * @param {mixed} obj   Array item to search for.
   * @param {int}   start Offset to start search.
   * @return {int}        Item index > 0 on success, -1 otherwise.
   */
  Array.prototype.indexOf = function(obj, start) {
    for (var i = (start || 0), j = this.length; i < j; i++) {
      if (this[i] === obj) { return i; }
    }
    return -1;
  };
}

if (!Node.prototype.getElementsByClassName) {
  /** 
   * Retrieves a DOM NodeList of elements matching a given class.
   * This is actually a fallback of Node.prototype.getElementsByClassName for old browsers. 
   * @global
   * @param {string} cls CSS class name.
   * @return {array}
   */
  Node.prototype.getElementsByClassName = function(cls) {
    var res = [];
    var els = this.getElementsByTagName("*");
    var re = new RegExp('(^| )'+cls+'( |$)');
    for (var i = 0, n = els.length; i < n; i++) {
      if (re.test(els[i].className)) res.push(els[i]);
    }
    return res;
  };
}
