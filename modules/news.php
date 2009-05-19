<?
/**
* file: news.php
* author: Roman Korostov
* date: 8/09/07	
**/

/*if (empty($_SESSION['user_news']))
{
	header("Location: index.php?modules=news&action=admin");
	exit;
}*/

$tpl->assign('name_page', 'news');

$action = html($g_action);

switch( $action )
{
	case "add":
		if (empty($_SESSION['user_news']))
		{
			header("Location: index.php?modules=news&action=admin");
			exit;
		}
		$tpl->assign("page_title","news add");	
		$tpl->assign('banks', $news->getBanks());
		
		$n['news_date'] = date("d.m.Y");

		if (!empty($p_news))
		{
			$n['news_title'] = html($p_news['news_title']);
			$n['bank_id'] = $p_news['bank_id'];
			$n['news_short'] = nl2br($p_news['news_short']);
			$n['news_body'] = $p_news_body;
			if ($p_news['news_visible'] == 'on')
			{
				$n['news_visible'] = '1';
			}else{
				$n['news_visible'] = '0';
			}

			if (!empty($p_news['news_date']))
			{
					list($day,$month,$year) = explode(".", $p_news['news_date']);
					$n['news_date'] = $year.".".$month.".".$day;
			}else{
				$error_text['news_date'] = "Дата не должна быть пустой!";
			}
			
			if (empty($error_text))
			{			
				if($news->saveNews($n))
				{
					//$tpl->assign('good_text', "Счет добавлен!");					
					$_SESSION['good_text'] = "Новость добавлена!";
					header("Location: index.php?modules=news&action=news_admin");
				}				
			}
			else
			{
				$tpl->assign('error_text', $error_text);
				$tpl->assign('news', $n);
			}			
		}else{
			$tpl->assign('news', $n);
		}
		
		break;
	case "edit":
		if (empty($_SESSION['user_news']))
		{
			header("Location: index.php?modules=news&action=admin");
			exit;
		}	
		$tpl->assign("page_title","news edit");

		if (!empty($p_news))
		{
			$n['news_id'] = html($p_news['news_id']);
			$n['news_title'] = html($p_news['news_title']);
			$n['bank_id'] = $p_news['bank_id'];
			$n['news_short'] = nl2br($p_news['news_short']);
			$n['news_body'] = $p_news_body;
			if ($p_news['news_visible'] == 'on')
			{
				$n['news_visible'] = '1';
			}else{
				$n['news_visible'] = '0';
			}

			if (!empty($p_news['news_date']))
			{
					list($day,$month,$year) = explode(".", $p_news['news_date']);
					$n['news_date'] = $year.".".$month.".".$day;
			}else{
				$error_text['news_date'] = "Дата не должна быть пустой!";
			}						

			if (empty($error_text))
			{
				if($news->updateNews($n))
				{
					$_SESSION['good_text'] = "НОвость изменена!";
					header("Location: index.php?modules=news&action=news_admin");
				}
			}
			else
			{
				$tpl->assign('error_text', $error_text);				
			}	
		}
		else
		{		
			if (isset($g_id) && is_numeric($g_id))
			{
				$n = $news->selectNews(html($g_id));
				$tpl->assign('banks', $news->getBanks());
				
				if(count($n)>0)
				{
					$tpl->assign('news', $n[0]);
				}
				else
				{
					$error_text['account'] = "Такой записи не существует!";
					$tpl->assign('error_text', $error_text);
				}
			}				
		}
		
		break;
	case "del":	
		$tpl->assign("page_title","news del");

		if (isset($p_news['news_id']) && is_numeric($p_news['news_id']))
		{			
			if($news->deleteNews(html($p_news['news_id'])))
			{
				$_SESSION['good_text'] = "Новость удалена!";
				header("Location: index.php?modules=news&action=news_admin");
				exit;
			}	
		}
		else
		{
			message_error(GENERAL_ERROR, "Получен неверный параметр!");
		}
		
		break;
	case "admin":
		$tpl->assign("page_title","news admin");

		if ( !empty($_POST['auth_news']))
		{			
			$login = html($_POST['auth_news']['login']);
			$pass = html($_POST['auth_news']['pass']);
			if ($login == 'admin' && $pass == 'RQpOV9jg')
			{
				$_SESSION['user_news'] = '1';
				header("Location: index.php?modules=news&action=news_admin");
			}
		}
		
		break;
	
	case "news_admin":
		$tpl->assign("page_title","news admin");

		if (empty($_SESSION['user_news']))
			{
				header("Location: index.php?modules=news&action=admin");
				exit;
			}
			$tpl->assign("page_title","news all");
			
			$allNews = $news->getNews();
			//pre($allBudget);
			$tpl->assign('news', $allNews);
			$tpl->assign('banks', $news->getBanks());
			
			if ($_SESSION['good_text'])
			{
				$tpl->assign('good_text', $_SESSION['good_text']);
				$_SESSION['good_text'] = false;
		}
		
		break;
		
	default:
		if (!empty($g_id))
		{
			$tpl->assign("page_title","news");			
			
			$n = $news->getNewsId(html($g_id));
			$tpl->assign("news",$n);
			
		}else{
			$tpl->assign("page_title","news total");
			
			if ( is_numeric($g_page) && $_GET['page'] > 1)
        	{
				$page_display['page'] = $g_page;
				$page_display['prev_page'] = $g_page - 1;
			}else{
				$page_display['page'] = 1;
				$page_display['prev_page'] = '';
			}
			
			//$start_record = ($page_display['page']* SYS_MAX_PERPAGE);
			$start_record = (($page_display['page']-1)*SYS_MAX_PERPAGE);

			//$finish_record = ($page_display['page']+1)* SYS_MAX_PERPAGE;
			$finish_record = SYS_MAX_PERPAGE;
			if (!empty($_POST['news_date']))
			{
				$_SESSION['news_date'] = $_POST['news_date'];
			}
			if (!empty($_SESSION['news_date']))
			{
				list($year,$month,$day) = explode(".", $_SESSION['news_date']);
				$news_date = $year.".".$month.".".$day;
			}else{
				$news_date = date("Y.m.d");
			}
			$pages_count = $news->getCountNews($news_date);
			
			$page_display['pages_count'] = ceil($pages_count['cnt']/SYS_MAX_PERPAGE);
			
			$allNews = $news->getTotalNews($start_record,$finish_record,$news_date);
			
			if ($page_display['pages_count'] > 1)
			{
				for($i=1; $i<=$page_display['pages_count']; $i++)
				{
					if ($page_display['page'] != $i)
					{
						$a_href .= "<a href=index.php?modules=news&page=".$i.">".$i."</a>&nbsp;&nbsp;";
					}else{
						$a_href .= "[".$i."]&nbsp;&nbsp;";
					}
					//$page_navigation .= "<a href=index.php?modules=news&page=".$i.">".$i."</a>&nbsp;&nbsp;";
				}
				$page_navigation = $a_href;
			}
			
			
			$tpl->assign('page_display', $page_display);
			$tpl->assign('page_navigation', $page_navigation);
			$tpl->assign('news_date', $news_date);

			$tpl->assign('news', $allNews);
		}
		//pre($total);
		break;
}
?>