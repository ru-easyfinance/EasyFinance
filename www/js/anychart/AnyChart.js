var AnyChart = function() {
    this.constructor(arguments);
};

AnyChart.utils = {};
AnyChart.utils.hasProp = function(target) { return typeof target != "undefined"; };
AnyChart.utils.push = function(arr, item) { arr[arr.length] = item; };

//--------------------------------------------------------------------------------------
//browser and os detection
//--------------------------------------------------------------------------------------

var tmpUa = (navigator && navigator.userAgent) ? navigator.userAgent.toLowerCase() : null;
var tmpUp = (navigator && navigator.platform) ? navigator.platform.toLowerCase() : null;

AnyChart.platform = {};
AnyChart.platform.isWin = tmpUp ? /win/.test(tmpUp) : /win/.test(tmpUa);
AnyChart.platform.isMac = !AnyChart.platform.isWin && (tmpUp ? /mac/.test(tmpUp) : /mac/.test(tmpUa));
AnyChart.platform.hasDom = AnyChart.utils.hasProp(document.getElementById) && AnyChart.utils.hasProp(document.getElementsByTagName) && AnyChart.utils.hasProp(document.createElement);
AnyChart.platform.webKit = /webkit/.test(tmpUa) ? parseFloat(tmpUa.replace(/^.*webkit\/(\d+(\.\d+)?).*$/, "$1")) : false;
AnyChart.platform.isIE = ! +"\v1";
AnyChart.platform.isFirefox = /firefox/.test(tmpUa);

AnyChart.platform.protocol = location.protocol == "https:" ? "https:" : "http:";

//--------------------------------------------------------------------------------------
//Flash Player version
//--------------------------------------------------------------------------------------
AnyChart.platform.flashPlayerVersion = [0, 0, 0];
if (AnyChart.utils.hasProp(navigator.plugins) && typeof navigator.plugins["Shockwave Flash"] == "object") {
    var d = navigator.plugins["Shockwave Flash"].description;
    if (d && !(AnyChart.utils.hasProp(navigator.mimeTypes) && navigator.mimeTypes["application/x-shockwave-flash"] && !navigator.mimeTypes["application/x-shockwave-flash"].enabledPlugin)) {
        AnyChart.platform.isIE = false; // cascaded feature detection for Internet Explorer
        d = d.replace(/^.*\s+(\S+\s+\S+$)/, "$1");
        AnyChart.platform.flashPlayerVersion[0] = parseInt(d.replace(/^(.*)\..*$/, "$1"), 10);
        AnyChart.platform.flashPlayerVersion[1] = parseInt(d.replace(/^.*\.(.*)\s.*$/, "$1"), 10);
        AnyChart.platform.flashPlayerVersion[2] = /[a-zA-Z]/.test(d) ? parseInt(d.replace(/^.*[a-zA-Z]+(.*)$/, "$1"), 10) : 0;
    }
} else if (typeof window.ActiveXObject != "undefined") {
    try {
        var a = new ActiveXObject("ShockwaveFlash.ShockwaveFlash");
        if (a) { // a will return null when ActiveX is disabled
            var d = a.GetVariable("$version");
            if (d) {
                AnyChart.platform.isIE = true; // cascaded feature detection for Internet Explorer
                d = d.split(" ")[1].split(",");
                AnyChart.platform.flashPlayerVersion = [parseInt(d[0], 10), parseInt(d[1], 10), parseInt(d[2], 10)];
            }
        }
    }
    catch (e) { }
}
AnyChart.platform.hasRequiredVersion = AnyChart.platform.flashPlayerVersion != null && Number(AnyChart.platform.flashPlayerVersion[0]) >= 9;
AnyChart.platform.needFormFix = AnyChart.platform.hasRequiredVersion && AnyChart.platform.isIE && AnyChart.platform.isWin;
if (AnyChart.platform.needFormFix) {
    //check player version
    AnyChart.platform.needFormFix = Number(AnyChart.platform.flashPlayerVersion[0]) == 9;
    AnyChart.platform.needFormFix = AnyChart.platform.needFormFix && Number(AnyChart.platform.flashPlayerVersion[1]) == 0;
    AnyChart.platform.needFormFix = AnyChart.platform.needFormFix && Number(AnyChart.platform.flashPlayerVersion[2]) < 115;
}
//--------------------------------------------------------------------------------------
//Event listeners
//--------------------------------------------------------------------------------------
AnyChart.utils.addGlobalEventListener = function(event, fn) {
    if (AnyChart.utils.hasProp(window.addEventListener)) window.addEventListener(event, fn, false);
    else if (AnyChart.utils.hasProp(document.addEventListener)) document.addEventListener(event, fn, false);
    else if (AnyChart.utils.hasProp(window.attachEvent)) AnyChart.utils.attachEvent(window, "on" + event, fn);
    else if (typeof window["on" + event] == "function") {
        var fnOld = window["on" + event];
        window["on" + event] = function() {
            fnOld();
            fn();
        };
    } else win["on" + event] = fn;
};
AnyChart.utils.listeners = [];
AnyChart.utils.attachEvent = function(target, event, fn) {
    target.attachEvent(event, fn);
    AnyChart.utils.push(AnyChart.utils.listeners, [target, event, fn]);
};

//--------------------------------------------------------------------------------------
//DomLoad function
//--------------------------------------------------------------------------------------
AnyChart.utils.isDomLoaded = false;
AnyChart.utils.domLoadListeners = [];
AnyChart.utils.addDomLoadEventListener = function(fn) {
    if (AnyChart.utils.isDomLoaded) fn();
    else AnyChart.utils.push(AnyChart.utils.domLoadListeners, fn);
};
AnyChart.utils.execDomLoadListeners = function() {
    if (AnyChart.utils.isDomLoaded) return;
    try { var t = document.getElementsByTagName("body")[0].appendChild(document.createElement("span")); t.parentNode.removeChild(t); } catch (e) { return; }

    AnyChart.utils.isDomLoaded = true;

    var len = AnyChart.utils.domLoadListeners.length;
    for (var i = 0; i < len; i++)
        AnyChart.utils.domLoadListeners[i]();
};
AnyChart.utils.registerDomLoad = function() {
    if (!AnyChart.platform.hasDom) return;
    if (AnyChart.utils.hasProp(document.readyState) && (document.readyState == "complete" || document.getElementsByTagName("body")[0] || document.body))
        AnyChart.utils.execDomLoadListeners();

    if (!AnyChart.utils.isDomLoaded) {
        if (AnyChart.utils.hasProp(document.addEventListener))
            document.addEventListener("DOMContentLoaded", AnyChart.utils.execDomLoadListeners, false);

        if (AnyChart.platform.isIE && AnyChart.platform.isWin) {
            document.attachEvent("onreadystatechange", function() {
                if (document.readyState == "complete") {
                    document.detachEvent("onreadystatechange", arguments.callee);
                    AnyChart.utils.execDomLoadListeners();
                }
            });

            if (window == top) {
                (function() {
                    if (AnyChart.utils.isDomLoaded) { return; }
                    try { document.documentElement.doScroll("left"); } catch (e) {
                        setTimeout(arguments.callee, 0);
                        return;
                    }
                    AnyChart.utils.execDomLoadListeners();
                })();
            }
        }
        if (AnyChart.platform.webKit) {
            (function() {
                if (AnyChart.utils.isDomLoaded) { return; }
                if (!/loaded|complete/.test(document.readyState)) {
                    setTimeout(arguments.callee, 0);
                    return;
                }
                AnyChart.utils.execDomLoadListeners();
            })();
        }
        AnyChart.utils.addGlobalEventListener("load", AnyChart.utils.execDomLoadListeners);
    }
};

//--------------------------------------------------------------------------------------
//Events calling
//--------------------------------------------------------------------------------------
AnyChart.dispatchEvent = function(chartId, eventObj) {
    if (chartId == null || eventObj == null) return;
    if (AnyChart.chartsMap && AnyChart.chartsMap[chartId])
        AnyChart.chartsMap[chartId].dispatchEvent(eventObj);
}

AnyChart.getChartById = function(chartId) {    
    return (chartId != null && AnyChart.chartsMap && AnyChart.chartsMap[chartId]) ? AnyChart.chartsMap[chartId] : null;
}
//--------------------------------------------------------------------------------------
//Disposing
//--------------------------------------------------------------------------------------

AnyChart.charts = [];
AnyChart.chartsMap = {};
AnyChart.register = function(stock) {
    stock.id = "__AnyChart___" + AnyChart.charts.length;
    AnyChart.chartsMap[stock.id] = stock;
    AnyChart.utils.push(AnyChart.charts, stock);
}

AnyChart.disposeFlashObject = function(obj, id) {
    if (obj && obj.nodeName == "OBJECT") {
        if (AnyChart.platform.isIE && AnyChart.platform.isWin) {
            obj.style.display = "none";
            (function() {
                if (obj.readyState == 4) {
                    if (AnyChart.platform.needFormFix && id != null)
                        AnyChart.disposeFlashObjectInIE(window[id]);

                    AnyChart.disposeFlashObjectInIE(obj);
                }
                else {
                    setTimeout(arguments.callee, 10);
                }
            })();
        } else {
            obj.parentNode.removeChild(obj);
        }
    }
};

AnyChart.disposeFlashObjectInIE = function(obj) {
    for (var j in obj) {
        if (typeof obj[j] == "function") {
            obj[j] = null;
        }
    }
    if (obj.parentNode) obj.parentNode.removeChild(obj);
};

AnyChart.registerDispose = function() {
    if (AnyChart.platform.isIE && AnyChart.platform.isWin) {

        var dispose = function() {
            if (AnyChart) {
                if (AnyChart.utils && AnyChart.utils.listeners) {
                    var len = AnyChart.utils.listeners.length;
                    var i;
                    for (i = 0; i < len; i++)
                        AnyChart.utils.listeners[i][0].detachEvent(AnyChart.utils.listeners[i][1], AnyChart.utils.listeners[i][2]);
                }
                if (AnyChart.charts) {
                    len = AnyChart.charts.length;
                    for (i = 0; i < len; i++) {
                        AnyChart.charts[i].dispose();
                    }
                }

                for (i in AnyChart)
                    AnyChart[i] = null;
            }

            AnyChart = null;
        }

        window.attachEvent("onbeforeunload", function() {
            dispose();
        });
        window.attachEvent("onunload", function() {
            dispose();
        });
    }
};

//--------------------------------------------------------------------------------------
//JS CONVERTER
//--------------------------------------------------------------------------------------
AnyChart.utils.JSConverter = {
    isAttribute: function(prop) {
        var type = typeof prop;
        return type == "string" || type == "number" || type == "boolean";
    },

    isArray: function(prop) {
        return typeof prop != "string" && typeof prop.length != "undefined";
    },

    createNode: function(nodeName, data) {
        var res = "<" + nodeName;
        
        if (typeof data["functionName"] != "undefined") {
          data["function"] = data["functionName"];
          delete data["functionName"];
        }
        
        for (var j in data) {
            if (j != "format" && j != "text" && j != "custom_attribute_value" && j != "attr" && AnyChart.utils.JSConverter.isAttribute(data[j])) {
                res += " " + j + "=\"" + data[j] + "\"";
            }
        }
        res += ">";
        for (var j in data) {
            if (j == "arg" && AnyChart.utils.JSConverter.isArray(data[j])) {
                var args = data[j];
                for (var i = 0;i<args.length;i++) {
                  res += "<arg><![CDATA["+args[i]+"]]></arg>";
                }
            }else if (j == "custom_attribute_value" || j == "attr") {
                res += "<![CDATA[" + data[j] + "]]>";
            } else if (j == "format" || j == "text") {
                res += "<" + j + "><![CDATA[" + data[j] + "]]></" + j + ">";
            } else if (AnyChart.utils.JSConverter.isArray(data[j])) {
                var nodes = data[j];
                for (var i = 0; i < nodes.length; i++) {
                    res += AnyChart.utils.JSConverter.createNode(j, nodes[i]);
                }
            } else if (!AnyChart.utils.JSConverter.isAttribute(data[j])) {
                res += AnyChart.utils.JSConverter.createNode(j, data[j]);
            }
        }
        res += "</" + nodeName + ">";
        return res;
    },

    convert: function(obj) {
        return AnyChart.utils.JSConverter.createNode("anychart", obj);
    }
};

//--------------------------------------------------------------------------------------
//Firefox print fix
//--------------------------------------------------------------------------------------
AnyChart.FFPrintFix = {};
AnyChart.FFPrintFix.fix = function(targetChart, targetNode, pngData, w, h, targetNodeName, targetId) {
    var head = document.getElementsByTagName("head");
    head = (head.length > 0) ? head[0] : null;
    if (head == null) return;
    
    targetChart.ffPrintScreenStyle = AnyChart.FFPrintFix.createDisplayStyle(head, w, h, targetNodeName, targetId);
    targetChart.ffPrintStyle = AnyChart.FFPrintFix.createPrintStyle(head, w, h, targetNodeName, targetId);
    targetChart.ffPrintFixImg = AnyChart.FFPrintFix.createImage(targetNode, pngData);
}

AnyChart.FFPrintFix.createDisplayStyle = function(head, w, h, targetNodeName, targetId) {
    //crete style node
    var style = document.createElement("style");
    style.setAttribute("type", "text/css");
    style.setAttribute("media", "screen");

    //write style.
    var objDescriptor = targetNodeName + "#" + targetId;
    var imgDescriptor = objDescriptor + " img";
    var objRule = " object { width:" + w + "; height:" + h + ";padding:0; margin:0; }\n";
    var imgRule = " { display: none; }";
    
    style.appendChild(document.createTextNode(objDescriptor + objRule));
    style.appendChild(document.createTextNode(imgDescriptor + imgRule));

    //add style to head
    return head.appendChild(style);
}

AnyChart.FFPrintFix.createPrintStyle = function(head, w, h, targetNodeName, targetId) {
    //crete style node
    var style = document.createElement("style");
    style.setAttribute("type", "text/css");
    style.setAttribute("media", "print");

    //write style.
    var objDescriptor = targetNodeName + "#" + targetId;
    var imgDescriptor = objDescriptor + " img";
    var objRule = " object { display: none; }\n";
    var imgRule = " { display: block; width: " + w + "; height: " + h + "; }";

    style.appendChild(document.createTextNode(objDescriptor + objRule));
    style.appendChild(document.createTextNode(imgDescriptor + imgRule));
    
    //add style to head
    return head.appendChild(style);
}

AnyChart.FFPrintFix.createImage = function(targetNode, pngData) {
    var img = document.createElement("img");
    img = targetNode.appendChild(img);
    img.src = "data:image/png;base64," + pngData;

    return img;
}

//--------------------------------------------------------------------------------------
//main()
//--------------------------------------------------------------------------------------
AnyChart.utils.registerDomLoad();
AnyChart.registerDispose();

//--------------------------------------------------------------------------------------
//AnyChart main code
//--------------------------------------------------------------------------------------

AnyChart.swfFile = null;
AnyChart.preloaderSWFFile = null;

AnyChart.messages = {
    preloaderInit: "Initializing... ",
    preloaderLoading: "Loading... ",

    init: "Initializing...",
    loadingXML: "Loading xml...",
    loadingResources: "Loading resources...",
    loadingTemplates: "Loading templates...",
    noData: "No data",
    waitingForData: "Waiting for data..."    
}

AnyChart.width = 550;
AnyChart.height = 400;
AnyChart.enableFirefoxPrintPreviewFix = true;
AnyChart.enableMouseEvents = true;

AnyChart.prototype = {

    //flash movie paths
    swfFile: null,
    preloaderSWFFile: null,

    //flash obj params
    id: null,
    width: null,
    height: null,
    bgColor: null,
    wMode: null,
    enableFirefoxPrintPreviewFix: false,

    //flash obj
    flashObject: null,
    target: null,
    ffPrintFixImg: null,
    ffPrintScreenStyle: null,
    ffPrintStyle: null,

    //stock settings
    messages: null,
    xmlFile: null,
    _xmlData: null,

    //events
    enableMouseEvents: true,

    //------------------------------------------------------------------------------------------------------
    //  CONSTRUCTOR
    //------------------------------------------------------------------------------------------------------

    constructor: function(args) {
        this.swfFile = AnyChart.swfFile;
        this.preloaderSWFFile = AnyChart.preloaderSWFFile;

        if (args.length > 0) {
            this.swfFile = args[0];
            if (args.length > 1)
                this.preloaderSWFFile = args[1];
        }
        this.target = null;
        this.ffPrintFixImg = null;
        this.ffPrintScreenStyle = null;
        this.ffPrintStyle = null;

        this.messages = {};
        this.messages.preloaderInit = AnyChart.messages.preloaderInit;
        this.messages.preloaderLoading = AnyChart.messages.preloaderLoading;

        this.messages.init = AnyChart.messages.init;
        this.messages.loadingXML = AnyChart.messages.loadingXML;
        this.messages.loadingResources = AnyChart.messages.loadingResources;
        this.messages.loadingTemplates = AnyChart.messages.loadingTemplates;
        this.messages.noData = AnyChart.messages.noData;
        this.messages.waitingForData = AnyChart.messages.waitingForData;

        this.width = AnyChart.width;
        this.height = AnyChart.height;
        this.bgColor = "#FFFFFF";

        this._isChartCreated = false;
        this._isHTMLWrited = false;
        this._needSetXMLFileAfterCreation = false;
        this._needSetXMLDataAfterCreation = false;

        this.visible = true;

        this.enableFirefoxPrintPreviewFix = AnyChart.enableFirefoxPrintPreviewFix;

        this.enableMouseEvents = AnyChart.enableMouseEvents;

        this._listeners = null;

        AnyChart.register(this);
    },

    //------------------------------------------------------------------------------------------------------
    //  HTML Embedding
    //------------------------------------------------------------------------------------------------------

    write: function(target) {
        this._isChartCreated = false;
        this._isHTMLWrited = false;
        if (!AnyChart.platform.hasRequiredVersion) return;
        if (arguments.length == 0) {
            target = "__chart_generated_container__" + this.id;
            document.write("<div id=\"" + target + "\"></div>");
        }
        this._createFlashObject(target);
    },

    _createFlashObject: function(target) {
        if (!AnyChart.utils.hasProp(target) || target == null) return;
        if (!AnyChart.platform.hasDom || (AnyChart.platform.webKit && AnyChart.platform.webKit < 312)) return;

        var ths = this;

        AnyChart.utils.addDomLoadEventListener(function() {
            if (typeof target == "string")
                ths._execCreateFlashObject(document.getElementById(target));
            else
                ths._execCreateFlashObject(target);
        });
    },

    _addParam: function(target, paramName, paramValue) {
        var node = document.createElement("param");
        node.setAttribute("name", paramName);
        node.setAttribute("value", paramValue);
        target.appendChild(node);
    },

    _generateStringParam: function(paramName, paramValue) {
        return "<param name=\"" + paramName + "\" value=\"" + paramValue + "\" />";
    },

    _rebuildExternalInterfaceFunctionForFormFix: function(obj, functionName) {
        eval('obj[functionName] = function(){return eval(this.CallFunction("<invoke name=\\"' + functionName + '\\" returntype=\\"javascript\\">" + __flash__argumentsToXML(arguments,0) + "</invoke>"));}');
    },

    _execCreateFlashObject: function(target) {
        this.target = target;
        this.enableFirefoxPrintPreviewFix = this.enableFirefoxPrintPreviewFix && AnyChart.platform.isFirefox;

        var width = this.width + "";
        var height = this.height + "";
        var path = this.preloaderSWFFile ? this.preloaderSWFFile : this.swfFile;

        if (AnyChart.platform.isIE && AnyChart.platform.isWin) {
            var htmlCode = "<object classid=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\"";
            htmlCode += " id=\"" + this.id + "\"";
            htmlCode += " width=\"" + width + "\"";
            htmlCode += " height=\"" + height + "\"";
            htmlCode += " style=\"visibility:" + (this.visible ? "visible" : "hidden") + "\"";
            htmlCode += " codebase=\"" + AnyChart.platform.protocol + "//fpdownload.macromedia.com/get/flashplayer/current/swflash.cab\">";

            htmlCode += this._generateStringParam("movie", path);
            htmlCode += this._generateStringParam("bgcolor", this.bgColor);
            htmlCode += this._generateStringParam("allowScriptAccess", "always");
            htmlCode += this._generateStringParam("flashvars", this._buildFlashVars());

            if (this.wMode != null) htmlCode += this._generateStringParam("wmode", this.wMode);

            htmlCode += "</object>";

            if (AnyChart.platform.needFormFix) {

                var targetForm = null;
                var tmp = target;
                while (tmp) {
                    if (tmp.nodeName != null && tmp.nodeName.toLowerCase() == "form") {
                        targetForm = tmp;
                        break;
                    }
                    tmp = tmp.parentNode;
                }
                if (targetForm != null) {

                    window[this.id] = {};
                    window[this.id].SetReturnValue = function() { };
                    target.innerHTML = htmlCode;

                    window[this.id].SetReturnValue = null;
                    var fncts = {};
                    for (var j in window[this.id]) {
                        if (typeof (window[this.id][j]) == 'function')
                            fncts[j] = window[this.id][j];
                    }
                    this.flashObject = window[this.id] = targetForm[this.id];

                    for (var j in fncts) {
                        this._rebuildExternalInterfaceFunctionForFormFix(this.flashObject, j);
                    }

                } else {
                    target.innerHTML = htmlCode;
                    this.flashObject = document.getElementById(this.id);
                }

            } else {
                target.innerHTML = htmlCode;
                this.flashObject = document.getElementById(this.id);
            }

        } else {
            var obj = document.createElement("object");
            obj.setAttribute("type", "application/x-shockwave-flash");
            obj.setAttribute("id", this.id);
            obj.setAttribute("width", width);
            obj.setAttribute("height", height);
            obj.setAttribute("data", path);
            obj.setAttribute("style", "visibility: " + (this.visible ? "visible" : "hidden"));

            this._addParam(obj, "movie", path);
            this._addParam(obj, "bgcolor", this.bgColor);
            this._addParam(obj, "allowScriptAccess", "always");
            this._addParam(obj, "flashvars", this._buildFlashVars());

            if (this.wMode != null) this._addParam(obj, "wmode", this.wMode);

            if (target.hasChildNodes()) {
                while (target.childNodes.length > 0) {
                    target.removeChild(target.firstChild);
                }
            }

            this.flashObject = target.appendChild(obj);
        }
        this._isHTMLWrited = this.flashObject != null;
    },

    _buildFlashVars: function() {
        var res = "";
        res = "__externalobjid=" + this.id;
        if (this.preloaderSWFFile != null) res += "&swffile=" + this.swfFile;
        if (this.xmlFile != null) res += "&xmlfile=" + this.xmlFile;
        if (this.enableMouseEvents) res += "&__enableevents=1";
        if (this.messages) {
            if (this.messages.preloaderInit != null) res += "&preloaderInitText=" + this.messages.preloaderInit;
            if (this.messages.preloaderLoading != null) res += "&preloaderLoadingText=" + this.messages.preloaderLoading;
            if (this.messages.init != null) res += "&initText=" + this.messages.init;
            if (this.messages.loadingXML != null) res += "&xmlLoadingText=" + this.messages.loadingXML;
            if (this.messages.loadingTemplates != null) res += "&templatesLoadingText=" + this.messages.loadingTemplates;
            if (this.messages.loadingResources != null) res += "&resourcesLoadingText=" + this.messages.loadingResources;
            if (this.messages.noData != null) res += "&nodatatext=" + this.messages.noData;
            if (this.messages.waitingForData != null) res += "&waitingfordatatext=" + this.messages.waitingForData;
        }

        return res;
    },

    //------------------------------------------------------------------------------------------------------
    //  Size update
    //------------------------------------------------------------------------------------------------------

    setSize: function(width, height) {
        this.width = width;
        this.height = height;
        if (this.flashObject) {
            this.flashObject.setAttribute("width", this.width + "");
            this.flashObject.setAttribute("height", this.height + "");
            this.updatePrintForFirefox();
        }
    },

    //------------------------------------------------------------------------------------------------------
    //  FIREFOX PRINT PREVIEW FIX
    //------------------------------------------------------------------------------------------------------
    _onBeforeChartDraw: function() {
        this._createFFPrintFixObjects();
    },

    _createFFPrintFixObjects: function() {
        if (!this.enableFirefoxPrintPreviewFix || this.target == null) return;

        var imgData = this.getPng();
        if (imgData == null || imgData.length == 0) return;

        var targetId = this.target.getAttribute("id");
        if (targetId == null) {
            targetId = "__stockchartcontainer__" + this.id;
            this.target.setAttribute("id", targetId);
        }

        AnyChart.FFPrintFix.fix(this, this.target, imgData, this.width + "", this.height + "", this.target.nodeName, targetId);
    },

    _disposeFFPrintFixObjects: function() {
        if (!this.enableFirefoxPrintPreviewFix || this.target == null) return;

        if (this.ffPrintFixImg) {
            if (this.ffPrintFixImg.parentNode) this.ffPrintFixImg.parentNode.removeChild(this.ffPrintFixImg);
            this.ffPrintFixImg = null;
        }

        if (this.ffPrintScreenStyle) {
            if (this.ffPrintScreenStyle.parentNode) this.ffPrintScreenStyle.parentNode.removeChild(this.ffPrintScreenStyle);
            this.ffPrintScreenStyle = null;
        }

        if (this.ffPrintStyle) {
            if (this.ffPrintStyle.parentNode) this.ffPrintStyle.parentNode.removeChild(this.ffPrintStyle);
            this.ffPrintStyle = null;
        }
    },

    updatePrintForFirefox: function() {
        this._disposeFFPrintFixObjects();
        this._createFFPrintFixObjects();
    },

    //------------------------------------------------------------------------------------------------------
    //  EVENT DISPATCHING
    //------------------------------------------------------------------------------------------------------

    _listeners: null,
    addEventListener: function(event, callback) {
        if (this._listeners == null) this._listeners = {};
        if (this._listeners[event] == null) this._listeners[event] = [];
        this._listeners[event].push(callback);
    },

    removeEventListener: function(event, callback) {
        if (this._listeners == null || this._listeners[event] == null) return;

        var index = -1;
        for (var i = 0; i < this._listeners[event].length; i++) {
            if (this._listeners[event][i] == callback) {
                index = i;
                break;
            }
        }
        if (index != -1)
            this._listeners[event].splice(index, 1);
    },

    dispatchEvent: function(e) {
        if (e == null || e.type == null) return;
        if (e.type == "create") this._onBeforeChartCreate();

        else if (e.type == "draw") this._onBeforeChartDraw();

        e.target = this;
        if (this._listeners == null || this._listeners[e.type] == null) return;
        var len = this._listeners[e.type].length;
        for (var i = 0; i < len; i++) {
            this._listeners[e.type][i](e);
        }
    },

    //------------------------------------------------------------------------------------------------------
    //  show/hide
    //------------------------------------------------------------------------------------------------------

    visible: true,

    show: function() {
        this.visible = true;
        if (this.flashObject) {
            this.flashObject.style.visibility = "visible";
            this.flashObject.setAttribute("width", this.width + "");
            this.flashObject.setAttribute("height", this.height + "");
        }
    },

    hide: function() {
        this.visible = false;
        if (this.flashObject) {
            this.flashObject.style.visibility = "hidden";
            this.flashObject.setAttribute("width", "1px");
            this.flashObject.setAttribute("height", "1px");
        }
    },

    //------------------------------------------------------------------------------------------------------
    //  DISPOSING
    //------------------------------------------------------------------------------------------------------

    dispose: function() {
        this.remove();
    },

    removeFlashObject: function() {
        if (this.flashObject) {
            if (this.flashObject.Dispose) this.flashObject.Dispose();
            AnyChart.disposeFlashObject(this.flashObject, this.id);

            this._disposeFFPrintFixObjects();
        }
        this.flashObject = null;
    },

    remove: function() {
        this.removeFlashObject();
        if (AnyChart && AnyChart.charts) {
            for (var i = 0; i < AnyChart.charts.length; i++) {
                if (AnyChart.charts[i] == this) {
                    AnyChart.charts[i] = null;
                    AnyChart.charts.splice(i, 1);
                    break;
                }
            }
        }
        if (AnyChart && AnyChart.chartsMap && this.id != null) {
            AnyChart.chartsMap[this.id] = null;
        }
    },

    //------------------------------------------------------------------------------------------------------
    //  CHART DATA MANIPULATION
    //------------------------------------------------------------------------------------------------------

    _isChartCreated: false,
    _isHTMLWrited: false,
    _needSetXMLFileAfterCreation: false,
    _needSetXMLDataAfterCreation: false,

    _xmlData: null,

    _onBeforeChartCreate: function() {
        this._isChartCreated = true;
        if (this._needSetXMLFileAfterCreation) this._execSetXMLFile();
        if (this._needSetXMLDataAfterCreation) this._execSetXMLData();
    },

    setXMLFile: function(xmlFile) {
        this.xmlFile = xmlFile;

        if (this._isChartCreated) {
            if (this._isHTMLWrited) this._execSetXMLFile();
        } else {
            this._needSetXMLFileAfterCreation = true;
        }
    },

    _execSetXMLFile: function() {
        if (this.xmlFile != null && this.flashObject && this.flashObject.SetXMLDataFromURL)
            this.flashObject.SetXMLDataFromURL(this.xmlFile);
    },

    _isXMLData: function(data) {
        var strData = String(data);
        while (strData.charAt(0) == " " && strData.length > 0) strData = strData.substr(1);
        return strData.charAt(0) == "<";
    },

    setData: function(data) {
        if (data == null) return;

        if (this._isXMLData(data))
            this._xmlData = String(data);
        else
            this._xmlData = AnyChart.utils.JSConverter.convert(data);

        if (this._isChartCreated)
            this._execSetXMLData();
        else
            this._needSetXMLDataAfterCreation = this._xmlData != null;
    },

    setJSData: function(data) {
        this.setData(data);
    },

    _execSetXMLData: function() {
        if (this._xmlData != null && this.flashObject && this.flashObject.SetXMLDataFromString)
            this.flashObject.SetXMLDataFromString(this._xmlData);
    },

    //------------------------------------------------------------------------------------------------------
    //          ANYCHART EXTERNAL INTERFACE
    //------------------------------------------------------------------------------------------------------

    updateData: function(path, data) {
        if (this.flashObject != null && this.flashObject.UpdateData != null)
            this.flashObject.UpdateData(path, data);
    },

    updatePointData: function(groupName, pointName, data) {
        if (this.flashObject != null && this.flashObject.UpdatePointData != null)
            this.flashObject.UpdatePointData(groupName, pointName, data);
    },

    setPlotCustomAttribute: function(attributeName, attributeValue) {
        if (this.flashObject != null && this.flashObject.SetPlotCustomAttribute != null)
            this.flashObject.SetPlotCustomAttribute(attributeName, attributeValue);
    },

    addSeries: function(seriesData) {
        if (this.flashObject != null && this.flashObject.AddSeries != null)
            this.flashObject.AddSeries(seriesData);
    },

    removeSeries: function(seriesId) {
        if (this.flashObject != null && this.flashObject.RemoveSeries != null)
            this.flashObject.RemoveSeries(seriesId);
    },

    addSeriesAt: function(index, seriesData) {
        if (this.flashObject != null && this.flashObject.AddSeriesAt != null)
            this.flashObject.AddSeriesAt(seriesId);
    },

    updateSeries: function(seriesId, seriesData) {
        if (this.flashObject != null && this.flashObject.UpdateSeries != null)
            this.flashObject.UpdateSeries(seriesId, seriesData);
    },

    showSeries: function(seriesId, seriesData) {
        if (this.flashObject != null && this.flashObject.ShowSeries != null)
            this.flashObject.ShowSeries(seriesId, seriesData);
    },

    addPoint: function(seriesId, pointData) {
        if (this.flashObject != null && this.flashObject.AddPoint != null)
            this.flashObject.AddPoint(seriesId, pointData);
    },

    addPointAt: function(seriesId, pointIndex, pointData) {
        if (this.flashObject != null && this.flashObject.AddPointAt != null)
            this.flashObject.AddPointAt(seriesId, pointIndex, pointData);
    },

    removePoint: function(seriesId, pointId) {
        if (this.flashObject != null && this.flashObject.RemovePoint != null)
            this.flashObject.RemovePoint(seriesId, pointId);
    },

    updatePoint: function(seriesId, pointId, pointData) {
        if (this.flashObject != null && this.flashObject.UpdatePoint != null)
            this.flashObject.UpdatePoint(seriesId, pointId, pointData);
    },

    clear: function() {
        if (this.flashObject != null && this.flashObject.Clear != null)
            this.flashObject.Clear();
    },

    refresh: function() {
        if (this.flashObject != null && this.flashObject.Refresh != null)
            this.flashObject.Refresh();
    },

    highlightSeries: function(seriesId, highlighted) {
        if (this.flashObject != null && this.flashObject.HighlightSeries != null)
            this.flashObject.HighlightSeries(seriesId, highlighted);
    },

    highlightPoint: function(seriesId, pointId, highlighted) {
        if (this.flashObject != null && this.flashObject.HighlightPoint != null)
            this.flashObject.HighlightPoint(seriesId, pointId, highlighted);
    },

    highlightCategory: function(categoryName, highlighted) {
        if (this.flashObject != null && this.flashObject.HighlightCategory != null)
            this.flashObject.HighlightCategory(categoryName, highlighted);
    },

    selectPoint: function(seriesId, pointId, selected) {
        if (this.flashObject != null && this.flashObject.SelectPoint != null)
            this.flashObject.SelectPoint(seriesId, pointId, selected);
    },

    view_highlightSeries: function(viewId, seriesId, highlighted) {
        if (this.flashObject != null && this.flashObject.View_HighlightSeries != null)
            this.flashObject.View_HighlightSeries(viewId, seriesId, highlighted);
    },

    view_highlightPoint: function(viewId, seriesId, pointId, highlighted) {
        if (this.flashObject != null && this.flashObject.View_HighlightPoint != null)
            this.flashObject.View_HighlightPoint(viewId, seriesId, pointId, highlighted);
    },

    view_highlightCategory: function(viewId, categoryName, highlighted) {
        if (this.flashObject != null && this.flashObject.View_HighlightCategory != null)
            this.flashObject.View_HighlightCategory(viewId, categoryName, highlighted);
    },

    view_selectPoint: function(viewId, seriesId, pointId, selected) {
        if (this.flashObject != null && this.flashObject.View_SelectPoint != null)
            this.flashObject.View_SelectPoint(viewId, seriesId, pointId, selected);
    },

    //--------------------------------------
    // dashboard data manipulation
    //--------------------------------------

    view_setPlotCustomAttribute: function(viewId, attributeName, attributeValue) {
        if (this.flashObject != null && this.flashObject.View_SetPlotCustomAttribute != null)
            this.flashObject.View_SetPlotCustomAttribute(viewId, attributeName, attributeValue);
    },

    view_addSeries: function(viewId, seriesData) {        
        if (this.flashObject != null && this.flashObject.View_AddSeries != null)
            this.flashObject.View_AddSeries(viewId, seriesData);
    },

    view_removeSeries: function(viewId, seriesId) {
        if (this.flashObject != null && this.flashObject.View_RemoveSeries != null)
            this.flashObject.View_RemoveSeries(viewId, seriesId);
    },

    view_addSeriesAt: function(viewId, index, seriesData) {
        if (this.flashObject != null && this.flashObject.View_AddSeriesAt != null)
            this.flashObject.View_AddSeriesAt(viewId, seriesId);
    },

    view_updateSeries: function(viewId, seriesId, seriesData) {
        if (this.flashObject != null && this.flashObject.View_UpdateSeries != null)
            this.flashObject.View_UpdateSeries(viewId, seriesId, seriesData);
    },

    view_showSeries: function(viewId, seriesId, seriesData) {
        if (this.flashObject != null && this.flashObject.View_ShowSeries != null)
            this.flashObject.View_ShowSeries(viewId, seriesId, seriesData);
    },

    view_addPoint: function(viewId, seriesId, pointData) {
        if (this.flashObject != null && this.flashObject.View_AddPoint != null)
            this.flashObject.View_AddPoint(viewId, seriesId, pointData);
    },

    view_addPointAt: function(viewId, seriesId, pointIndex, pointData) {
        if (this.flashObject != null && this.flashObject.View_AddPointAt != null)
            this.flashObject.View_AddPointAt(viewId, seriesId, pointIndex, pointData);
    },

    view_removePoint: function(viewId, seriesId, pointId) {
        if (this.flashObject != null && this.flashObject.View_RemovePoint != null)
            this.flashObject.View_RemovePoint(viewId, seriesId, pointId);
    },

    view_updatePoint: function(seriesId, pointId, pointData) {
        if (this.flashObject != null && this.flashObject.View_UpdatePoint != null)
            this.flashObject.View_UpdatePoint(viewId, seriesId, pointId, pointData);
    },

    view_clear: function(viewId) {
        if (this.flashObject != null && this.flashObject.View_Clear != null)
            this.flashObject.View_Clear(viewId);
    },

    view_refresh: function(viewId) {
        if (this.flashObject != null && this.flashObject.View_Refresh != null)
            this.flashObject.View_Refresh(viewId);
    },

    //--------------------------------------
    // end of data manipulation
    //--------------------------------------

    updateViewPointData: function(viewName, groupName, pointName, data) {
        if (this.flashObject != null && this.flashObject.UpdateViewPointData != null)
            this.flashObject.UpdateViewPointData(groupName, pointName, data);
    },

    //------------------------------------
    //			actions
    //------------------------------------

    setXMLDataFromString: function(data) {
        if (this.flashObject != null &&
			this.flashObject.SetXMLDataFromString != null)
            this.flashObject.SetXMLDataFromString(data.toString());
    },

    setXMLDataFromURL: function(url) {
        if (this.flashObject != null &&
			this.flashObject.SetXMLDataFromURL != null) {
            this.flashObject.SetXMLDataFromURL(url);
        }
    },

    /**
    * Sets XML path to the certain view in dashboard.
    * @param {String} viewId view id
    * @param {String} xmlPath path to XML file 
    */
    setViewXMLFile: function(viewId, url) {
        if (this.flashObject != null &&
			this.flashObject.UpdateViewFromURL != null)
            this.flashObject.UpdateViewFromURL(viewId, url);
    },

    /**
    * Sets XML string to the certain view in dashboard.
    * @param {String} viewId view id
    * @param {String} data string with XML
    */
    setViewData: function(viewId, data) {
        if (this.flashObject != null &&
			this.flashObject.UpdateViewFromString != null)
            this.flashObject.UpdateViewFromString(viewId, data);
    },

    /**
    * Displays loading message
    * 
    * dashboard:
    * setLoading(viewId, messageText)
    * 
    * global:
    * setLoading(messageText)
    * 
    * @param {String} messageTextOrViewId message text or view id
    * @param {String} messageText message text
    */
    setLoading: function() {
        if (this.flashObject && this.flashObject.SetLoading)
            this.flashObject.SetLoading(arguments[0]);
    },

    /**
    * Gets base64 encoded png chart screenshot
    * @return {String}
    */
    getPng: function() {
        return this.flashObject.GetPngScreen();
    },

    /**
    * Gets base64 encoded jpeg chart screenshot
    * @return {String}
    */
    getJpeg: function() {
        return this.flashObject.GetJPEGScreen();
    },

    /**
    * Runs chart printing dialog
    */
    printChart: function() {
        this.flashObject.PrintChart();
    },

    /**
    * Runs image saving dialog
    */
    saveAsImage: function() {
        this.flashObject.SaveAsImage();
    },

    /**
    * Runs pdf saving dialog
    */
    saveAsPDF: function() {
        this.flashObject.SaveAsPDF();
    },

    /**
    * Gets information
    * @return {Object}
    */
    getInformation: function() {
        return this.flashObject.GetInformation();
    },

    scrollXTo: function(xValue) {
        if (this.flashObject != null && this.flashObject.ScrollXTo != null)
            this.flashObject.ScrollXTo(xValue);
    },

    scrollYTo: function(yValue) {
        if (this.flashObject != null && this.flashObject.ScrollYTo != null)
            this.flashObject.ScrollYTo(yValue);
    },

    scrollTo: function(xValue, yValue) {
        if (this.flashObject != null && this.flashObject.ScrollTo != null)
            this.flashObject.ScrollTo(xValue, yValue);
    },

    viewScrollXTo: function(viewName, xValue) {
        if (this.flashObject != null && this.flashObject.ViewScrollXTo != null)
            this.flashObject.ViewScrollXTo(viewName, xValue);
    },

    viewScrollYTo: function(viewName, yValue) {
        if (this.flashObject != null && this.flashObject.ViewScrollXTo != null)
            this.flashObject.ViewScrollYTo(viewName, yValue);
    },

    viewScrollTo: function(viewName, xValue, yValue) {
        if (this.flashObject != null && this.flashObject.ViewScrollTo != null)
            this.flashObject.ViewScrollTo(viewName, xValue, yValue);
    },

    setXZoom: function(settings) {
        if (this.flashObject != null && this.flashObject.SetXZoom != null)
            this.flashObject.SetXZoom(settings);
    },

    setYZoom: function(settings) {
        if (this.flashObject != null && this.flashObject.SetYZoom != null)
            this.flashObject.SetYZoom(settings);
    },

    setZoom: function(xZoomSettings, yZoomSettings) {
        if (this.flashObject != null && this.flashObject.SetZoom != null)
            this.flashObject.SetZoom(xZoomSettings, yZoomSettings);
    },

    setViewXZoom: function(viewName, settings) {
        if (this.flashObject != null && this.flashObject.SetViewXZoom != null)
            this.flashObject.SetViewXZoom(viewName, settings);
    },

    setViewYZoom: function(viewName, settings) {
        if (this.flashObject != null && this.flashObject.SetViewYZoom != null)
            this.flashObject.SetViewYZoom(viewName, settings);
    },

    setViewZoom: function(viewName, xZoomSettings, yZoomSettings) {
        if (this.flashObject != null && this.flashObject.SetViewZoom != null)
            this.flashObject.SetViewZoom(viewName, xZoomSettings, yZoomSettings);
    },

    getXScrollInfo: function() {
        if (this.flashObject != null && this.flashObject.GetXScrollInfo != null)
            return this.flashObject.GetXScrollInfo();
    },

    getYScrollInfo: function() {
        if (this.flashObject != null && this.flashObject.GetYScrollInfo != null)
            return this.flashObject.GetYScrollInfo();
    },

    getViewXScrollInfo: function(viewName) {
        if (this.flashObject != null && this.flashObject.GetViewXScrollInfo != null)
            return this.flashObject.GetViewXScrollInfo(viewName);
    },

    getViewYScrollInfo: function(viewName) {
        if (this.flashObject != null && this.flashObject.GetViewYScrollInfo != null)
            return this.flashObject.GetViewYScrollInfo(viewName);
    }


};