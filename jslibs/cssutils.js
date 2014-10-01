/*!
 * CSSUtils | Luis A. Leiva | MIT license
 * Some CSS utilities.
 */

/**
 * @class
 * @description This just documents the CommonJS/AMD/etc. module closure.
 */
;(function(module, global) {

  /**
   * Some CSS utilities.
   * @module CSSUtils 
   */
  module.exports = {
    /**
     * Apply CSS styles to element.
     * @alias module:CSSUtils.applyCSS
     * @param {object} elem DOM element.
     * @param {string} prop CSS property.
     */
    applyCSS: function(elem, styles) {
      for (var s in styles) {
        elem.style[s] = styles[s];
      }
    },
    /**
     * Get the CSS style of a DOM element, after all styles are applied to the page. 
     * @alias module:CSSUtils.getStyle
     * @param {object} elem DOM element.
     * @param {string} prop CSS property.
     * @return {string}
     */
    getStyle: function (elem, prop) {
      var value = "";
      // normalize
      prop = prop.toLowerCase();
      if (window.getComputedStyle) { // W3C
        value = window.getComputedStyle(elem, null).getPropertyValue(prop);
      } else if (elem.currentStyle) { // IE: font-size -> fontSize
        prop = this.dash2camel(prop);
        value = elem.currentStyle[prop];
      }
      return value;
    },
    /**
     * Set a CSS style for a given DOM element.
     * @alias module:CSSUtils.setStyle
     * @param {object} elem  DOM element
     * @param {string} prop  CSS property
     * @param {string} value CSS value
     */    
    setStyle: function (elem, prop, value) {
      prop = prop.toLowerCase();
      if (elem.style.setProperty) { // W3C & IE >= 9
        // using "important" instead of null will override user-defined rules
        elem.style.setProperty(prop, value, "important"); 
      } else if (style.setAttribute) { // IE: font-size -> fontSize
        prop = this.dash2camel(prop);
        elem.style.setAttribute(prop, value);
      }   
    },
    /** 
     * Convert str with dashes to camelCaseNotation.
     * @alias module:CSSUtils.dash2camel
     * @param {string}  str  Input string.
     * @return {string}
     */
    dash2camel: function(str) {
      return str.replace(/\-(\w)/g, function(strMatch, p1){
        return p1.toUpperCase();
      });
    },
    /** 
     * R,G,B array color to hexadecimal.
     * @alias module:CSSUtils.rgb2hex
     * @param {array} R,G,B values.
     * @return {int}
     */
    rgb2hex: function(rgb) {
      var r = rgb[0].toString(16); if (r.length < 2) r += r;
      var g = rgb[1].toString(16); if (g.length < 2) g += g;
      var b = rgb[2].toString(16); if (b.length < 2) b += b;
      return r+g+b;
    },
    /** 
     * R,G,B array color to decimal number.
     * @alias module:CSSUtils.rgb2dec
     * @param {array} R,G,B values.
     * @return {int}
     */
    rgb2dec: function(rgb) {
      return parseInt(this.rgb2hex(rgb), 16);
    },
    /** 
     * Number to CSS color.
     * @alias module:CSSUtils.cssColor
     * @param {int} num Decimal number
     * @return {string}
     */
    cssColor: function(num) {
      var str = Math.floor(num).toString(16);
      // zeropad 
      for (var i = str.length; i < 6; ++i) {
        str = "0" + str;
      }
      return "#" + str.toUpperCase();
    },
    /** 
     * Convert RGBA to RGB.
     * @alias module:CSSUtils.rgba2rgb
     * @param {int} num Decimal number
     * @return {string}
     */
    rgba2rgb: function(rgba) {
      var col = [ rgba[0], rgba[1], rgba[2] ];
      var alp = rgba[3], opa = 1 - rgba[3];
      var matte = [255, 255, 255];
      return [
        alp * col[0] + opa * matte[0],
        alp * col[1] + opa * matte[1],
        alp * col[2] + opa * matte[2]
      ];
    },
    /** 
     * Compute difference between 2 RGB colors.
     * @alias module:CSSUtils.rgbDiff
     * @param {int} num Decimal number
     * @return {string}
     */
    rgbDiff: function(rgb1, rgb2) {
      var diff = Math.abs(rgb1[0] - rgb2[0]) + 
                 Math.abs(rgb1[1] - rgb2[1]) +
                 Math.abs(rgb1[2] - rgb2[2]);
      return diff / (3*255);
    },
//    /** 
//     * Convert RGB to HSL.
//     * @alias module:CSSUtils.rgb2hsl
//     * @param {string} rgb RGB color
//     * @return {string}
//     */
//    rgb2hsl: function(rgb) {
//      var r = rgb[0]/255, g = rgb[1]/255, b = rgb[2]/255;
//      var max = Math.max(r, g, b), min = Math.min(r, g, b);
//      var h, s, l = (max + min) / 2;
//      if (max == min) {
//        h = s = 0; // achromatic
//      } else {
//        var d = max - min;
//        s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
//        switch(max){
//          case r: h = (g - b) / d + (g < b ? 6 : 0); break;
//          case g: h = (b - r) / d + 2; break;
//          case b: h = (r - g) / d + 4; break;
//        }
//        h /= 6;
//      }
//      return [ Math.floor(h * 360), Math.floor(s * 100), Math.floor(l * 100) ];
//    },
    /** 
     * Compute color contrast.
     * @alias module:CSSUtils.colorContrast
     * @param {object} elem DOM element.
     * @return {number}
     */
    colorContrast: function(elem) {
      var fgColor = this.parseColor( this.getStyle(elem, "color") )
        , bgColor = this.parseColor( this.getStyle(elem, "background-color") )
        , fgBright = this.brightness(fgColor)
        , bgBright = this.brightness(bgColor)
//          , diff = this.rgbDiff(fgColor, bgColor)
        , diff = Math.abs(fgBright - bgBright)
        ;
      return Number( diff.toFixed(2) );
    },
    /** 
     * Compute color brightness.
     * @alias module:CSSUtils.brightness
     * @param {string} rgb RGB color.
     * @return {number}
     */
    brightness: function(rgb) {
      return ((rgb[0]*299) + (rgb[1]*587) + (rgb[2]*114)) / 1000;
    },
    /** 
     * Transform color to a 3 or 4 length tuple (array).
     * @alias module:CSSUtils.colorTuple
     * @param {string} color RGB color.
     * @return {array}
     */
    colorTuple: function(color) {
      var tuple = [];
      var fparen = color.indexOf('(');
      var lparen = color.indexOf(')');
      color = color.substring(fparen+1, lparen);
      tuple = color.split(',');
      for (var j = 0; j < tuple.length; ++j) {
        tuple[j] = parseInt(tuple[j]);
      }
      return tuple;
    },
    /**
     * Convert a color to an array of R,G,B values in [0, 255].
     * @alias module:CSSUtils.parseColor
     * @param {string} color  Color definition: rgb(R,G,B) or rgba(R,G,B,A) or hsl(H,S,L) or hsl(H,S,L,A) or #RGB or # RRGGBB.
     * @return {array}
     */    
    parseColor: function(color) {
      var rgb = [];
      // option 1: rgb(R,G,B) format; optionally rgba(R,G,B,A)
      if (color[0] == 'r') {
        rgb = this.colorTuple(color);
        if (rgb.length > 3) rgb = this.rgba2rgb(rgb);
      }
      // option 2: hsl(H,S,L) format; optionally hsla(H,S,L,A)
      else if (color[0] == 'h') {
        var hsl = this.colorTuple(color);
        rgb = hsl.length > 3 ? this.hsla2rgb(rgb) : this.hsl2rgb(rgb);
      }
      // option 3: #RRGGBB format
      else if (color[0] == "#") {
        // check also the shorthand notation (#F00 = #FF0000)
        var start  = 1;
        var offset = color.length < 6 ? 1 : 2;
        for (var i = 0, col = ""; i < 3; ++i) {
          col = color.substr(start, offset);
          if (offset == 1) col += col;
          rgb[i] = parseInt(col,16);
          start += offset;
        }
      }
      // option 4: transparent
      else if (color == "transparent") {
        // Note: IE 7-8 supports transparent only for `background` and `border`. `color:transparent` is drawn black in IE.
        rgb = [255, 255, 255];
      }
//      // option 5: string definition (requires colordef.js)
//      else if (color in CSSColorStrings) {
//        rgb = this.parseColor( CSSColorStrings[color] );
//      }
      // else ... bad color definition
      return rgb;
    },
    /**
     * Retrieve the parts of a dimension definition (e.g. "15px", "2.5em"...)
     * @alias module:CSSUtils.parseDimension
     * @param {string}   dim          Input dimension.
     * @return {object}  result
     * @return {number}  result.value
     * @return {string}  result.unit
     */        
    parseDimension: function(dim) {
      var value = parseFloat(dim);
      var parts = dim.split(value);
      return { value:value, unit:parts[1] };
    }

  };

})('object' === typeof module ? module : {}, this);
