/*!
    "[...] but the many languages began at Babel, which Nimrod, the hunter
     before the Lord, began to build." -- Quaker Bible, Gen 10:8-10
*/
;Lazy.domReady(function(){

  var _pluginName = "nimrod-gettext"
    , _numVersion = "1.0"
    , _connection = 0
    ;

  // Stats & rankings ----------------------------------------------------------
  
  var _elemData  = {} // Non-interaction Data  
    , _eventData = {} // Interaction data
    , _timeZero  = (new Date).getTime()
    ;

  function initDataFields(k) {
    if (typeof _elemData[k] === 'undefined') {
      _elemData[k] = {};
    }
    return _elemData[k];
  };

  function nimrodId(el) {
    return TrackLib.XPath.getXPath(el, true);
  };

  function getResourcesByElements(arr) {
    var res = [];
    for (var i = 0; i < arr.length; i++) {
      var obj = arr[i];
      for (var key in obj) {
        var values = obj[key];
        for (var j = 0; j < values.length; j++) {
          res.push(values[j]);
        }
      }
    }
    return res;
  };

  function getResourcesByData(data) {
    var els, res = [];
    if (data.ntag) {
      els = JSON.parse(data.ntag);
      res = res.concat( getResourcesByElements(els) );
    }
    if (data.natt) {
      els = JSON.parse(data.natt)
      res = res.concat( getResourcesByElements(els) );
    }
    return res;
  };
  
  function trackElementRendering(el) {
    var data = GenUtil.getDataset(el);
    if (data && (data.ntag || data.natt)) {
      var key   = nimrodId(el)
        , entry = initDataFields(key)
        , data  = GenUtil.getDataset(el)
        , isVisible = el.offsetWidth > 0 || el.offsetHeight > 0
        , size = GenUtil.boundingBoxSize(el, isVisible)
        ;
      entry.visible = +isVisible;
      entry.height  = size.height;
      entry.width   = size.width;
      entry.fgcolor = CssUtil.getStyle(el, "color");
      entry.bgcolor = CssUtil.getStyle(el, "background-color");
      if (typeof entry.resources === 'undefined') {
        entry.resources = getResourcesByData(data);
      }
    }
  };
  
  function trackElementUsage(e) {
    // We need to read a valid Nimrod dataset
    var elem = e.currentTarget || e.srcElement
      , data = GenUtil.getDataset(elem)
      ;
    if (data && (data.ntag || data.natt)) {
      var key = nimrodId(elem);
      _eventData[key] = _eventData[key] || { timeline: {} };
      var timeDelta = (new Date).getTime() - _timeZero;
      _eventData[key].timeline[timeDelta] = e.type;
      if (typeof _eventData[key].resources === 'undefined') {
        _eventData[key].resources = getResourcesByData(data);
      }
    }
  };

  // Other callbacks -----------------------------------------------------------
  
  function startNimrod() {
    setupNimrodElements(document);
    // Setup additional events
    var unload = (typeof window.onbeforeunload === 'function') ? "beforeunload" : "unload";
    TrackLib.Events.add(window, unload, flushData);
    TrackLib.Events.add(document, "DOMNodeInsertedIntoDocument", GenUtil.throttle(function(e){
      setupNimrodElements(e.target);
    }, 250));
    initData();
  };

  function setupNimrodElements(node) {
    var elms = node.getElementsByClassName(_pluginName)
      , evts = "mouseover mouseout click touchstart touchend"
      , cnts = 1
      ;
    for (var i = 0, n = elms.length; i < n; i++) {
      var el = elms[i];
      trackElementRendering(el);
      TrackLib.Events.addMulti(el, evts, trackElementUsage);
    }
  };

  function initData() {
    console.log(_elemData);
    submitData({
        elements: _elemData
    }, true, setConnection);
  };

  function appendData() {
    if (GenUtil.objKeys(_eventData).length === 0) return;
    console.log(_eventData);
    submitData({
      events: _eventData
    }, false);
  };
  
  function submitData(info, async, cb) {
    var data = "info=" + JSON.stringify(info);
    // We need to inform about user ID and src file
    var bodyData = GenUtil.getDataset(document.getElementsByTagName('body')[0]);
    data += "&nid=" + bodyData.nid;
    data += "&nih=" + bodyData.nih;
    
    // TODO: decouple `save.php` from this script
    TrackLib.XHR.sendAjaxRequest({
           url: Lazy.basepath("../ajax.php"),
         async: async,    
      postdata: data, 
      callback: cb
    });
    
    // Cleanup
    _elemData  = {};
    _eventData = {};
  };
  
  function flushData() {
    if (_connection) {
      appendData();
    }
  };

  function setConnection(response) {
    _connection = (parseInt(response) > 0);
    if (_connection) {
      setInterval(function(){
        appendData();
      }, 5000);
    }
  };
  
  // Init ----------------------------------------------------------------------
  
  var CssUtil = Lazy.require("cssutils");
  var GenUtil = Lazy.require("genutils");
  
  Lazy.load(["json2", "legacy", "tracklib", "xvis"], startNimrod);

});
