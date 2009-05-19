$(function() {

	/**
	 * Browsers
	 */
	var ie6 = ie55 = sm = ff = nc = false;

	if ($.browser.msie) {
       ie6 = navigator.appVersion.split(";")[1].split(" ")[2] == "6.0" ? true:false;
       ie55 = navigator.appVersion.split(";")[1].split(" ")[2] == "5.5" ? true:false;
	} else if ($.browser.mozilla) {
		sm = navigator.userAgent.split(" ")[9].indexOf("SeaMonkey") != -1 ? true:false;
		if (!sm) {
			nc = navigator.userAgent.indexOf("Navigator") != -1 ? true:false;
			if (!nc) { ff = true }
		}
	}
	if ($.browser.msie) { $("body").addClass("ie"); } 
	else if ($.browser.safari) { $("body").addClass("safari"); } 
	else if ($.browser.opera)  { $("body").addClass("opera");  } 
	else if (sm) { $("body").addClass("sm"); } 
	else if (ff) { $("body").addClass("ff"); } 
	else if (nc) { $("body").addClass("nc"); } 	
	if ($.browser.mozilla) {$('body').addClass('moz')}

	/**
	 * CSS Rules
	 */
	if (!$.browser.mozilla) {
		$('#buttons a').css('display','inline-block');
		$('#buttons a:eq(0)').css({display:'block'});
		$('#buttons a:eq(1)').css({left:'0px', position:'relative'});
		$('#buttons a:eq(2)').css({left:'3px', position:'relative'});
	} 
});



$("p img.titleImg").ready(function() {
	var len = $("p img.titleImg").size();
	for (var i = 0; i < len; i++)
		$("p img").eq(i).css("margin-left", "-"+$("p img.titleImg").eq(i).width()-40+"px")
});
