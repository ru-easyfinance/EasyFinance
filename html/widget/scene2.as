import flash.events.MouseEvent;
import flash.events.TimerEvent;
import flash.text.TextFormat;
import flash.utils.Timer;

pb.visible = true;
in_last_in_border.visible = false;
out_last_in_border.visible = false;
out_credit_in_border.visible = false;
out_plan_in_border.visible = false;
last_money_in_border.visible = false;


rpc.call('getTextForSecondFrame');

var secondFrameStrings:Array = new Array();

var tmr:Timer = new Timer(1000);
tmr.stop();

/*
 * Настройка поведения логотипа 
 */
logo.addEventListener(MouseEvent.CLICK, onLogo3Click);
logo.buttonMode = true;
logo.useHandCursor = true;

/*
 * Настройка кнопки перехода на следующую сцену
 */
get_result.addEventListener(MouseEvent.CLICK, onGetResultClick);
get_result.buttonMode = true;
get_result.useHandCursor = true;
get_result.mouseChildren = false;

/*
 * Установка текстовых форматов для второй сцены
 */
var gr_tf:TextFormat = new TextFormat();
gr_tf.font = "Arial";
gr_tf.size = 14;
gr_tf.color = 0xf15a22;
gr_tf.underline = true;

var ff_tf:TextFormat = new TextFormat();
ff_tf.font = "Arial";
ff_tf.size = 11;
ff_tf.color = 0xf15a22;
ff_tf.underline = true;
ff_tf.letterSpacing = -1;


in_last.addEventListener(MouseEvent.MOUSE_OVER, onInLastRollOver);
in_last.addEventListener(MouseEvent.MOUSE_OUT, onItemRollOut);
in_last_in.addEventListener(MouseEvent.MOUSE_OVER, onInLastRollOver);
in_last_in.addEventListener(MouseEvent.MOUSE_OUT, onItemRollOut);
in_last_in.addEventListener(MouseEvent.CLICK, onInLasClick);
in_last.mouseChildren = false;

out_last.addEventListener(MouseEvent.MOUSE_OVER, onOutLastRollOver);
out_last.addEventListener(MouseEvent.MOUSE_OUT, onItemRollOut);
out_last_in.addEventListener(MouseEvent.MOUSE_OVER, onOutLastRollOver);
out_last_in.addEventListener(MouseEvent.MOUSE_OUT, onItemRollOut);
out_last_in.addEventListener(MouseEvent.CLICK, onOutLastClick);
out_last.mouseChildren = false;

out_credit.addEventListener(MouseEvent.MOUSE_OVER, onOutCreditRollOver);
out_credit.addEventListener(MouseEvent.MOUSE_OUT, onItemRollOut);
out_credit_in.addEventListener(MouseEvent.MOUSE_OVER, onOutCreditRollOver);
out_credit_in.addEventListener(MouseEvent.MOUSE_OUT, onItemRollOut);
out_credit_in.addEventListener(MouseEvent.CLICK, onOutCreditClick);
out_credit.mouseChildren = false;

out_plan.addEventListener(MouseEvent.MOUSE_OVER, onOutPlanRollOver);
out_plan.addEventListener(MouseEvent.MOUSE_OUT, onItemRollOut);
out_plan_in.addEventListener(MouseEvent.MOUSE_OVER, onOutPlanRollOver);
out_plan_in.addEventListener(MouseEvent.MOUSE_OUT, onItemRollOut);
out_plan_in.addEventListener(MouseEvent.CLICK, onOutPlanClick);
out_plan.mouseChildren = false;

last_money.addEventListener(MouseEvent.MOUSE_OVER, onLastMoneyRollOver);
last_money.addEventListener(MouseEvent.MOUSE_OUT, onItemRollOut);
last_money_in.addEventListener(MouseEvent.MOUSE_OVER, onLastMoneyRollOver);
last_money_in.addEventListener(MouseEvent.MOUSE_OUT, onItemRollOut);
last_money_in.addEventListener(MouseEvent.CLICK, onLastMoneyClick);
last_money.mouseChildren = false;

function prepareSecondFrame()
{
	fill_fields.text = secondFrameStrings["fill_fields"];
	
	forget_finance._txt.text = secondFrameStrings["forget_finance"];
	// forget_finance._txt.setTextFormat(ff_tf);
	
	forget_finance.addEventListener(MouseEvent.CLICK, onForgetFinanceClick);
	forget_finance.buttonMode = true;
	forget_finance.useHandCursor = true;
	forget_finance.mouseChildren = false;
		
	in_last._txt.text =	secondFrameStrings["in_last"]; 		
		
	out_last._txt.text = secondFrameStrings["out_last"];		
		
	out_credit._txt.text = secondFrameStrings["out_credit"];		
		
	out_plan._txt.text = secondFrameStrings["out_plan"];
		
	last_money._txt.text = secondFrameStrings["last_money"];
		
	get_result._txt.text = secondFrameStrings["get_result"];	
	// get_result._txt.setTextFormat(gr_tf);

}

function showTooltip(ev:TimerEvent)
{
	trace('fsfdsfds');
	addChildAt(tlp, 20);
}

function onInLasClick(ev:MouseEvent)
{
	in_last_in_border.visible = false;
}

function onOutLastClick(ev:MouseEvent)
{
	out_last_in_border.visible = false;
}

function onOutCreditClick(ev:MouseEvent)
{
	out_credit_in_border.visible = false;
}

function onOutPlanClick(ev:MouseEvent)
{
	out_plan_in_border.visible = false;
}

function onLastMoneyClick(ev:MouseEvent)
{
	last_money_in_border.visible = false;
}

function onInLastRollOver(ev:MouseEvent)
{
//	tlp = new Tooltip(secondFrameStrings["in_last_tooltip"], ev.stageX+2, ev.stageY-20 );
	tlp = new Tooltip(secondFrameStrings["in_last_tooltip"], 12, 110 );
	
	tmr = new Timer(1000, 1);
	tmr.addEventListener(TimerEvent.TIMER, showTooltip);
	tmr.start();
}

function onOutLastRollOver(ev:MouseEvent)
{
//	tlp = new Tooltip(secondFrameStrings["out_last_tooltip"], ev.stageX+2, ev.stageY-20 );
	tlp = new Tooltip(secondFrameStrings["out_last_tooltip"], 12, 150 );
	
	tmr = new Timer(1000, 1);
	tmr.addEventListener(TimerEvent.TIMER, showTooltip);
	tmr.start();	
}

function onOutCreditRollOver(ev:MouseEvent)
{
//	tlp = new Tooltip(secondFrameStrings["out_credit_tooltip"], ev.stageX+2, ev.stageY-20 );
	tlp = new Tooltip(secondFrameStrings["out_credit_tooltip"], 12, 190 );
	
	tmr = new Timer(1000, 1);
	tmr.addEventListener(TimerEvent.TIMER, showTooltip);
	tmr.start();
}

function onOutPlanRollOver(ev:MouseEvent)
{
//	tlp = new Tooltip(secondFrameStrings["out_plan_tooltip"], ev.stageX+2, ev.stageY-20 );
	tlp = new Tooltip(secondFrameStrings["out_plan_tooltip"], 12, 230 );
	
	tmr = new Timer(1000, 1);
	tmr.addEventListener(TimerEvent.TIMER, showTooltip);
	tmr.start();
}

function onLastMoneyRollOver(ev:MouseEvent)
{
//	tlp = new Tooltip(secondFrameStrings["last_money_tooltip"], ev.stageX+2, ev.stageY-20 );
	tlp = new Tooltip(secondFrameStrings["last_money_tooltip"], 12, 270 );
	
	tmr = new Timer(1000, 1);
	tmr.addEventListener(TimerEvent.TIMER, showTooltip);
	tmr.start();	
}

function onItemRollOut(ev:MouseEvent)
{
	tmr.stop();
	
	try
	{
		removeChild(tlp);
	}
	catch (e:Error)
	{
		
	}
}

function onForgetFinanceClick(ev:MouseEvent)
{
	var req:URLRequest = new URLRequest(secondFrameStrings["forget_finance_link"]);
	
	navigateToURL(req, '_blank');	
}

function onGetResultClick(ev:flash.events.MouseEvent)
{
	var errorCount = 0;
	
	if (in_last_in.text == "")
	{
			in_last_in_border.visible = true;
			errorCount++;
	}
	
	if (out_last_in.text == "")
	{
			out_last_in_border.visible = true;
			errorCount++;
	}
	
	if (out_credit_in.text == "")
	{
			out_credit_in_border.visible = true;
			errorCount++;
	}
	
	if (out_plan_in.text == "")
	{
			out_plan_in_border.visible = true;
			errorCount++;
	}
	
	if (last_money_in.text == "")
	{
			last_money_in_border.visible = true;
			errorCount++;
	}	


	
	if (errorCount == 0)
	{
		rpc.addParam(in_last_in.text, XMLRPCDataTypes.STRING);
		rpc.addParam(out_last_in.text, XMLRPCDataTypes.STRING);
		rpc.addParam(out_credit_in.text, XMLRPCDataTypes.STRING);
		rpc.addParam(out_plan_in.text, XMLRPCDataTypes.STRING);
		rpc.addParam(last_money_in.text, XMLRPCDataTypes.STRING);
		
		pb.visible = true;
		
		rpc.call('getTextForThirdFrame');		
	}
}
function onLogo3Click(ev:flash.events.MouseEvent)
{
	var req:URLRequest = new URLRequest("http://easyfinance.ru/");
	
	navigateToURL(req, '_blank');	
}