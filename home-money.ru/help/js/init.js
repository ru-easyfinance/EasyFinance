var css_browser_selector=function(){var ua=navigator.userAgent.toLowerCase(),is=function(t){return ua.indexOf(t)!=-1;},h=document.getElementsByTagName('html')[0],b=(!(/opera|webtv/i.test(ua))&&/msie\s(\d)/.test(ua))?('ie ie'+RegExp.$1):is('firefox/2')?'gecko ff2':is('firefox/3')?'gecko ff3':is('gecko/')?'gecko':is('opera/9')?'opera opera9':/opera\s(\d)/.test(ua)?'opera opera'+RegExp.$1:is('konqueror')?'konqueror':is('chrome')?'chrome webkit safari':is('applewebkit/')?'webkit safari':is('mozilla/')?'gecko':'',os=(is('x11')||is('linux'))?' linux':is('mac')?' mac':is('win')?' win':'';var c=b+os+' js';h.className+=h.className?' '+c:c;}();
jQuery.noConflict();


function loadSWF(key, obj)
{
	var id = "flash";

	var opt = {};
	opt[1]        = {};
	opt[1].title  = 'Создание категории';
	opt[1].folder = 'cat_create';
	opt[1].width  = 712;
	opt[1].height = 550;

	opt[2]        = {};
	opt[2].title  = 'Удаление категории';
	opt[2].folder = 'cat_del';
	opt[2].width  = 712;
	opt[2].height = 550;

	opt[3]        = {};
	opt[3].title  = 'Создание подкатегории';
	opt[3].folder = 'subcat_create';
	opt[3].width  = 712;
	opt[3].height = 550;

	opt[4]        = {};
	opt[4].title  = 'Добавление операции';
	opt[4].folder = 'operation_add';
	opt[4].width  = 712;
	opt[4].height = 550;

	opt[5]        = {};
	opt[5].title  = 'Редактирование операции';
	opt[5].folder = 'operation_editing';
	opt[5].width  = 712;
	opt[5].height = 550;

	opt[6]        = {};
	opt[6].title  = 'Удаление операции';
	opt[6].folder = 'operation_del';
	opt[6].width  = 712;
	opt[6].height = 550;

	opt[7]        = {};
	opt[7].title  = 'Планирование бюджета';
	opt[7].folder = 'budget_planning';
	opt[7].width  = 712;
	opt[7].height = 550;

	opt[8]        = {};
	opt[8].title  = 'Добавление счета';
	opt[8].folder = 'bill_add';
	opt[8].width  = 712;
	opt[8].height = 498;

	opt[9]        = {};
	opt[9].title  = 'Регулярные транзакции';
	opt[9].folder = 'regular_transactions';
	opt[9].width  = 712;
	opt[9].height = 550;


	if(opt[key] == undefined) {
		alert('Раздел не найден');
		return;
	}


	var el = jQuery(obj);
	var parentBlock = el.parents('#nav');
	el.parent().addClass('current');
	parentBlock.find('a').not(el).parent().removeClass('current');


	document.title = 'Home-money.ru :: '+opt[key].title;

	jQuery('#title').text(opt[key].title);

	var flashvars = {
		autostart : false,
		thumb : 'swf/'+opt[key].folder+'/FirstFrame.png',
		thumbscale : 60,
		color : '0x76B900,0x76B900'
	};
	var params = {
		bgcolor : '#76B900',
		quality : 'best',
		allowfullscreen : true,
		scale : 'showall',
		allowscriptaccess : 'always'
	};
	swfobject.embedSWF("swf/"+opt[key].folder+"/swf.swf", id, opt[key].width, opt[key].height, "9.0.115", "swf/expressInstall.swf", flashvars, params);
	return false;
}