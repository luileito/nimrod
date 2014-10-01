(function(){

  // Note: This script assumes that Lazy lib is already loaded.
  
  var Url = Lazy.require("url");
  var CssUtil = Lazy.require("cssutils");
  
  // Synchronous loading
  Lazy.load("tracklib", init);
  
  function init() {
    var xpath = Url.getQueryParam('xvis');
    if (xpath) {
      var result = document.evaluate(xpath, document, null, XPathResult.ANY_TYPE, null);
      var node = result.iterateNext();
      
      var viewport = TrackLib.Dimension.getWindowSize();
      var overlay = document.createElement("div");
      CssUtil.applyCSS(overlay, {
            position: "absolute"
          , top: 0
          , left: 0
//          , backgroundColor: "white"
//          , opacity: 0.5
          , width: viewport.width + "px"
          , height: viewport.height + "px"
          , cursor: "move"
      });
    
      var borderSize  = 2;
      var paddingSize = 5;
      
      var npos = TrackLib.Dimension.getElementPosition(node);
      var nsiz = TrackLib.Dimension.getElementSize(node);
      var bbox = document.createElement("div");
      CssUtil.applyCSS(bbox, {
            position: "absolute"
          , top: npos.top   - (borderSize + paddingSize) + "px"
          , left: npos.left - (borderSize + paddingSize) + "px"
          , backgroundColor: "transparent"
          , border: "3px solid red"
          , padding: paddingSize + "px"
          , width:  nsiz.width  + "px"
          , height: nsiz.height + "px"
      });
      
      overlay.setAttribute("title", "You are in review mode. Click to operate the UI.");
      overlay.appendChild(bbox);
      document.body.appendChild(overlay);
    }
    TrackLib.Events.add(overlay, "click", function(e){
      document.body.removeChild(overlay);
    });
  };

})();
