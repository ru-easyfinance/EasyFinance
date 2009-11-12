var rpc:Connection;

function initXMLRPC()
{
	rpc = new ConnectionImpl(xml_rpc_server);
	rpc.addEventListener(Event.COMPLETE, rpcCompleteHandler);
	rpc.addEventListener(ErrorEvent.ERROR, rpcErrorHandler);
}

function rpcCompleteHandler(evt:Event):void
{
	try
	{
		var response:Object = rpc.getResponse();

		if (response.what_to_know!=null)
		{
			firstFrameStrings["what_to_know"] = response.what_to_know.toString();
			firstFrameStrings["pass_the_test"] = response.pass_the_test.toString();
			firstFrameStrings["logo_link"] = response.logo_link.toString();
			
			prepareFirstFrame();
			
			pb.visible = false;
		}
		
		if (response.fill_fields != null)
		{
			secondFrameStrings["fill_fields"] = response.fill_fields.toString();
			secondFrameStrings["forget_finance"] = response.forget_finance.toString();
			secondFrameStrings["forget_finance_link"] = response.forget_finance_link.toString();
			secondFrameStrings["in_last"] = response.in_last.toString(); 
			secondFrameStrings["in_last_tooltip"] = response.in_last_tooltip.toString();
			secondFrameStrings["out_last"] = response.out_last.toString();
			secondFrameStrings["out_last_tooltip"] = response.out_last_tooltip.toString();
			secondFrameStrings["out_credit"] = response.out_credit.toString();
			secondFrameStrings["out_credit_tooltip"] = response.out_credit_tooltip.toString();
			secondFrameStrings["out_plan"] = response.out_plan.toString();
			secondFrameStrings["out_plan_tooltip"] = response.out_plan_tooltip.toString();
			secondFrameStrings["last_money"] = response.last_money.toString();
			secondFrameStrings["last_money_tooltip"] = response.last_money_tooltip.toString();
			secondFrameStrings["get_result"] = response.get_result.toString();
			
			prepareSecondFrame();
			
			pb.visible = false;
		}
		
		if (response.fin_grade !=null)
		{
			gotoAndStop(3);
			
			thirdFrameStrings["fin_grade"] = response.fin_grade.toString();
			thirdFrameStrings["money"] = response.money.toString();
			thirdFrameStrings["credit"] = response.credit.toString();
			thirdFrameStrings["out_manage"] = response.out_manage.toString();
			thirdFrameStrings["in_vs_out"] = response.in_vs_out.toString();
			thirdFrameStrings["fin_state"] = response.fin_state.toString();
			thirdFrameStrings["how_use"] = response.how_use.toString();
			thirdFrameStrings["money_grade"] = response.money_grade.toString();
			thirdFrameStrings["money_text"] = response.money_text.toString();
			thirdFrameStrings["money_color"] = int(response.money_color.toString());
			thirdFrameStrings["credit_grade"] = response.credit_grade.toString();
			thirdFrameStrings["credit_text"] = response.credit_text.toString();
			thirdFrameStrings["credit_color"] = int(response.credit_color.toString());
			thirdFrameStrings["out_manage_grade"] = response.out_manage_grade.toString();
			thirdFrameStrings["out_manage_text"] = response.out_manage_text.toString();
			thirdFrameStrings["out_manage_color"] = int(response.out_manage_color.toString());
			thirdFrameStrings["in_vs_out_grade"] = response.in_vs_out_grade.toString();
			thirdFrameStrings["in_vs_out_text"] = response.in_vs_out_text.toString();
			thirdFrameStrings["in_vs_out_color"] = int(response.in_vs_out_color.toString());
			thirdFrameStrings["final_grade"] = response.final_grade.toString();
			thirdFrameStrings["fin_state_grade"] = response.fin_state_grade.toString();
			thirdFrameStrings["fin_state_color"] = response.fin_state_color.toString();
			thirdFrameStrings["logo_link"] = response.logo_link.toString();
			thirdFrameStrings["how_use_link"] = response.how_use_link.toString();
			
			thirdFrameStrings["money_link"] = response.money_link.toString();
			thirdFrameStrings["credit_link"] = response.credit_link.toString();
			thirdFrameStrings["out_manage_link"] = response.out_manage_link.toString();
			thirdFrameStrings["in_vs_out_link"] = response.in_vs_out_link.toString();

			prepareThirdFrame();
			
			pb.visible = false;

		}
	}
	catch (e:Error)
	{
		pb.visible = true;
	}
}
 
function rpcErrorHandler(evt:ErrorEvent):void
{
	var fault:MethodFault = rpc.getFault();
}