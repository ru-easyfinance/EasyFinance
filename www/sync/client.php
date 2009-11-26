 <?php
	include(SYS_DIR_LIBS."external/php_xmlrpc/xmlrpc.inc");
        $url = 'hm/sync/';
        $ch = curl_init('hm/sync/');
        //curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, false);
        //curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, false);
        //$result = curl_exec($ch);
        //curl_close($ch);
        echo ('<br>hwfj');
        
        //die (print_r($ch));
	// Play nice to PHP 5 installations with REGISTER_LONG_ARRAYS off
	if(!isset($HTTP_POST_VARS) && isset($_POST))
	{
		$HTTP_POST_VARS = $_POST;
	}

	if(isset($HTTP_POST_VARS["stateno"]) && $HTTP_POST_VARS["stateno"]!="")
	{
		$stateno=(integer)$HTTP_POST_VARS["stateno"];
		$stroka = array ('type' => 'service'
						,'name' => 'auth342'
						,'login' => 'sync'
						,'pass' => 'b1b3773a05c0ed0176787a4f1574ff0075f7521e'
						,'lastsync' => '0091003T10:11:12'
						,'digsignature'=>'n2jdy303yeer7j2v');

		$f=new xmlrpcmsg('sync.getAuthWithTestData',
			//array(php_xmlrpc_encode($stateno))
			array(php_xmlrpc_encode($stroka))
		);
		print "<pre>Sending the following request:\n\n" . htmlentities($f->serialize()) . "\n\nDebug info of server data follows...\n\n";
		//$c=new xmlrpc_client("/server.php", "phpxmlrpc.sourceforge.net", 80);
		//$c = new xmlrpc_client("/server.php", "localhost", 80);
                $c = new xmlrpc_client("sync/", "hm/", 443, 'https');
		$c->setDebug(1);
		$r = &$c->send($f);

		if(!$r->faultCode())
		{
			$v=$r->value();
			print "</pre><br/>State number " . $stateno . " is "
				. htmlspecialchars($v->scalarval()) . "<br/>";
			// print "<HR>I got this value back<BR><PRE>" .
			//  htmlentities($r->serialize()). "</PRE><HR>\n";
		}
		else
		{
			print "An error occurred: ";
			print "Code: " . htmlspecialchars($r->faultCode())
				. " Reason: '" . htmlspecialchars($r->faultString()) . "'</pre><br/>";
		}
	}
	else
	{
		$stateno = "";
	}

	print "<form action=\"client.php\" method=\"POST\">
<input name=\"stateno\" value=\"" . $stateno . "\"><input type=\"submit\" value=\"go\" name=\"submit\"></form>
<p>Enter a state number to query its name</p>";

?>
