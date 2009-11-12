import flash.events.MouseEvent;
import flash.text.TextFormat;
import flash.events.Event;

var thirdFrameStrings:Array = new Array();

var gradus = 0;
var curGrad = 0;

var tm:Timer = new Timer(1000);
tm.stop();

/*
 * Настройка поведения логотипа 
 */
logo.addEventListener(MouseEvent.CLICK, onLogo2Click);
logo.buttonMode = true;
logo.useHandCursor = true;


how_use.addEventListener(MouseEvent.CLICK, onHowClick);
how_use.buttonMode = true;
how_use.useHandCursor = true;
how_use.mouseChildren = false;

money_link_text.addEventListener(MouseEvent.CLICK, onMore1Click);
money_link_text.buttonMode = true;
money_link_text.useHandCursor = true;
money_link_text.mouseChildren = false;
money_link_text.tabEnabled = false;

credit_link_text.addEventListener(MouseEvent.CLICK, onMore2Click);
credit_link_text.buttonMode = true;
credit_link_text.useHandCursor = true;
credit_link_text.mouseChildren = false;
credit_link_text.tabEnabled = false;

out_manage_link_text.addEventListener(MouseEvent.CLICK, onMore3Click);
out_manage_link_text.buttonMode = true;
out_manage_link_text.useHandCursor = true;
out_manage_link_text.mouseChildren = false;
out_manage_link_text.tabEnabled = false;

in_vs_out_link_text.addEventListener(MouseEvent.CLICK, onMore4Click);
in_vs_out_link_text.buttonMode = true;
in_vs_out_link_text.useHandCursor = true;
in_vs_out_link_text.mouseChildren = false;
in_vs_out_link_text.tabEnabled = false;


var mt_tf:TextFormat = money_text.getTextFormat();
// mt_tf.italic = true;


var fg_tf:TextFormat = final_grade.getTextFormat();
// fg_tf.italic = true;

var more_tf:TextFormat = money_link_text._txt.getTextFormat();
more_tf.underline = true;


function prepareThirdFrame()
{
	money_link_text._txt.setTextFormat(more_tf);
	credit_link_text._txt.setTextFormat(more_tf);
	out_manage_link_text._txt.setTextFormat(more_tf);
	in_vs_out_link_text._txt.setTextFormat(more_tf);
	
	fin_grade.text = thirdFrameStrings["fin_grade"];
	
	thirdFrameStrings["money"];
	credit.text = thirdFrameStrings["credit"];
	out_manage.text = thirdFrameStrings["out_manage"];
	in_vs_out.text = thirdFrameStrings["in_vs_out"];
	fin_state.text = thirdFrameStrings["fin_state"];
	how_use._txt.text = thirdFrameStrings["how_use"];
	
	money_grade.text = thirdFrameStrings["money_grade"];
	money_text.text = thirdFrameStrings["money_text"];
	t1.gotoAndStop(thirdFrameStrings["money_color"]);
	money_text.setTextFormat(mt_tf);
	
	credit_grade.text = thirdFrameStrings["credit_grade"];
	credit_text.text = thirdFrameStrings["credit_text"];
	t2.gotoAndStop(thirdFrameStrings["credit_color"]);
	credit_text.setTextFormat(mt_tf);
	
	out_manage_grade.text = thirdFrameStrings["out_manage_grade"];
	out_manage_text.text = thirdFrameStrings["out_manage_text"];
	t3.gotoAndStop(thirdFrameStrings["out_manage_color"]);
	out_manage_text.setTextFormat(mt_tf);
	
		
	in_vs_out_grade.text = thirdFrameStrings["in_vs_out_grade"];
	in_vs_out_text.text = thirdFrameStrings["in_vs_out_text"];
	t4.gotoAndStop(thirdFrameStrings["in_vs_out_color"]);
	in_vs_out_text.setTextFormat(mt_tf);
		
	fin_state_grade.text = thirdFrameStrings["fin_state_grade"];
	taho._txt.text = thirdFrameStrings["fin_state_color"];
	
	var rotateDelta = 180 + 80;
	var gradeDelta = 300;
	var delta = rotateDelta / gradeDelta;
	gradus = int(thirdFrameStrings["fin_state_color"]) * delta;
	
	trace(gradus);
	
	tm = new Timer(100, 0);
	tm.addEventListener(TimerEvent.TIMER, onRotateTimer);
	tm.start();		
	
	/*var mt:Matrix = taho._arrow.transform.matrix;
	mt.rotate(2 * Math.PI * (gradus/360));
	taho._arrow.transform.matrix = mt;*/
	
	final_grade.text = thirdFrameStrings["final_grade"];
	final_grade.setTextFormat(fg_tf);
}

function onRotateTimer(ev:TimerEvent)
{
	if (curGrad < gradus)
	{
		var mt:Matrix = taho._arrow.transform.matrix;
		mt.rotate(2 * Math.PI * (10/360));
		taho._arrow.transform.matrix = mt;	
		
		curGrad += 10;
	}
	else
	{
		tm.stop();
	}
}

function onLogo2Click(ev:flash.events.MouseEvent)
{
	var req:URLRequest = new URLRequest(thirdFrameStrings["logo_link"]);
	
	navigateToURL(req, '_blank');	
}

function onHowClick(ev:flash.events.MouseEvent)
{
	var req:URLRequest = new URLRequest(thirdFrameStrings["how_use_link"]);
	
	navigateToURL(req, '_blank');		
}


function onMore1Click(ev:Event)
{
	var req:URLRequest = new URLRequest(thirdFrameStrings["money_link"]);
	
	navigateToURL(req, '_blank');	
}

function onMore2Click(ev:Event)
{
	var req:URLRequest = new URLRequest(thirdFrameStrings["credit_link"]);
	
	navigateToURL(req, '_blank');	
}

function onMore3Click(ev:Event)
{
	var req:URLRequest = new URLRequest(thirdFrameStrings["out_manage_link"]);
	
	navigateToURL(req, '_blank');	
}

function onMore4Click(ev:Event)
{
	var req:URLRequest = new URLRequest(thirdFrameStrings["in_vs_out_link"]);
	
	navigateToURL(req, '_blank');	
}