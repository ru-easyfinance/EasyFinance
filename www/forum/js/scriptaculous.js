var Scriptaculous={Version:"1.7.0",require:function(A){document.write('<script type="text/javascript" src="'+A+'"><\/script>')},load:function(){if((typeof Prototype=="undefined")||(typeof Element=="undefined")||(typeof Element.Methods=="undefined")||parseFloat(Prototype.Version.split(".")[0]+"."+Prototype.Version.split(".")[1])<1.5){throw ("script.aculo.us requires the Prototype JavaScript framework >= 1.5.0")}$A(document.getElementsByTagName("script")).findAll(function(A){return(A.src&&A.src.match(/scriptaculous\.js(\?.*)?$/))}).each(function(B){var C=B.src.replace(/scriptaculous\.js(\?.*)?$/,"");var A=B.src.match(/\?.*load=([a-z,]*)/);(A?A[1]:"builder,effects,dragdrop,controls,slider").split(",").each(function(D){Scriptaculous.require(C+D+".js")})})}};Scriptaculous.load();