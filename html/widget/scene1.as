import flash.events.MouseEvent;
import flash.text.TextFormat;

var firstFrameStrings:Array = new Array();

pb.visible = true;

rpc.call('getTextForFirstFrame');

/*
 * Настройка поведения логотипа 
 */
//logo.addEventListener(MouseEvent.CLICK, onLogoClick);
//logo.buttonMode = true;
//logo.useHandCursor = true;

/*
 * Настройка кнопки перехода на следующую сцену
 */
pass_the_test.addEventListener(MouseEvent.CLICK, onPassTheTestClick);
pass_the_test.buttonMode = true;
pass_the_test.useHandCursor = true;
pass_the_test.mouseChildren = false;

/*
 * Установка текстовых форматов для первой сцены
 */
var wtk_tf:TextFormat = new TextFormat();
wtk_tf.bold = true;
wtk_tf.font = "Arial";
wtk_tf.size = 14;
wtk_tf.align = "center";

var ptt_tf:TextFormat = new TextFormat();
// ptt_tf.font = "Arial";
// ptt_tf.size = 14;
// ptt_tf.color = 0xf15a22;
ptt_tf.underline = true;

function prepareFirstFrame()
{
	/*what_to_know.text = firstFrameStrings["what_to_know"];
	// what_to_know.setTextFormat(wtk_tf);
	
	pass_the_test._txt.text = firstFrameStrings["pass_the_test"];
	pass_the_test._txt.setTextFormat(ptt_tf);*/
}

/*function onLogoClick(ev:flash.events.MouseEvent)
{
	var req:URLRequest = new URLRequest(firstFrameStrings["logo_link"]);
	
	navigateToURL(req, '_blank');	
}*/

function onPassTheTestClick(ev:flash.events.MouseEvent)
{
	gotoAndStop(2);
}