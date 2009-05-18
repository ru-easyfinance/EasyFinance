<?php
if (!empty($_POST['to_mail']))
{
	$from = $_POST['from_mail'];
	$to = html($_POST['to_mail']);
	$title = html($_POST['title_mail']);
	$text = $_POST['text_mail'];
	$errors = "";
	
	$arrFrom = explode(",", $to);
	
	if ($_POST['is_send_from_db'] == "on")
	{
		exit;
		$sql = "SELECT DISTINCT user_mail FROM users";
		$db->sql_query($sql);
		$row = $db->sql_fetchrowset($result);
		$cnt_mail = count($row);
		
		for ($i=0; $i<$cnt_mail; $i++)
		{
			$body = "<html><head><title>From home-money.ru</title></head>
					 <body>
					 <p>".$text."</p>
					
					 </body>
					 </html>";
			
			$message = "<html><head><title>$title</title></head>
						<body>
							".$body."
						</body>
						</html>";
			$headers = "Content-type: text/html; charset=utf-8\n";
			$headers .= "From: $from\n";
			
			if (!mail(trim($row[$i]['user_mail']), $title, $body, $headers))
			{				
				$errors .= "<li>ошибка: ($i) ".$row[$i]['user_mail']."</li>";
			}else{
				$success .= "<li>($i) удачно: $value</li>";
			}
			//sleep(2);
		}
	}
	
	foreach ($arrFrom as $key=>$value)
	{
		
	$body = "<html><head><title>From home-money.ru</title></head>
					 <body>
					 <p>".$text."</p>
					
					 </body>
					 </html>";
			
			$subject = "Демо-регистрация в home-money.ru";
			$message = "<html><head><title>$title</title></head>
						<body>
							".$body."
						</body>
						</html>";
			$headers = "Content-type: text/html; charset=utf-8\n";
			$headers .= "From: $from\n";
			
			if (!mail(trim($value), $title, $body, $headers))
			{				
				$errors .= "<li>ошибка: $value</li>";
			}else{
				$success .= "<li>удачно: $value</li>";
			}
	}
	
	if (!empty($errors))
	{
		$tpl->assign("errors", $errors);
	}else{
		$tpl->assign("success", $success);
	}
}
	
?>