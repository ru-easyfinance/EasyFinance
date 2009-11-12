include "xmlrpc.as";


import flash.events.Event;
import flash.events.ErrorEvent;

import com.mattism.http.xmlrpc.*;
import com.mattism.http.xmlrpc.util.*;

pb.visible = false;

var xml_rpc_server = 'http://widget.easyfinance.ru';

var tlp:Tooltip;

if (stage.loaderInfo.parameters["xml_rpc_server"]!=undefined
	&& stage.loaderInfo.parameters["xml_rpc_server"].length!=0)
{
	xml_rpc_server = stage.loaderInfo.parameters["xml_rpc_server"];
}

initXMLRPC();

include "scene1.as";
