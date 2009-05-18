<?php
/**
 * Отдельная панель для экспертов
 */

// Подключаем все необходимые библиотеки и инициализируем объекты
define("SCRIPTS_ROOT", "/home/rkorostov/data/www/");
define("SYS_DIR_LIBS", "/home/rkorostov/data/www/core");
require_once (SCRIPTS_ROOT . "core/classes/hmExpertSystem.class.php");
require_once (SCRIPTS_ROOT . "core/external/DBSimple/Mysql.php");
require_once (SCRIPTS_ROOT . "include/functions.php");

define('SYS_DB_HOST', 	'localhost');
define('SYS_DB_USER', 	'homemone');
define('SYS_DB_PASS', 	'lw0Hraec');
define('SYS_DB_BASE', 	'wwwhomemoneyru');

define('UPLOAD_DIR',	SCRIPTS_ROOT."easyfinance.ru/upload/photo_experts/");
define('FILE_DIR',	SCRIPTS_ROOT."easyfinance.ru/upload/file_experts/");

// подключаем смарти
require_once (SCRIPTS_ROOT . 'include/smarty/Smarty.class.php');
require_once (SCRIPTS_ROOT . 'include/smarty/Smarty_Compiler.class.php');
require_once (SCRIPTS_ROOT . 'include/smarty/Config_File.class.php');

$tpl = new Smarty();

$tpl->template_dir    =  SCRIPTS_ROOT . 'templates_new';
$tpl->compile_dir     =  SCRIPTS_ROOT . 'cache_new';

$tpl->plugins_dir     =  array(SCRIPTS_ROOT . 'include/smarty/plugins');
$tpl->compile_check   =  true;
$tpl->force_compile   =  false;

$errors_list = array(
	'-1' => "Ошибка с базой (".mysql_error().")", 
	'-2' => "Ошибка в логине", 
	'-3' => "Неверный email", 
	'-4' => "Такой эксперт уже существует",
	'-5' => "ошибка записи фотографии",
	'-6' => "Неверный логин или пароль");

session_start();

$tpl->assign("profile", $_SESSION['expert']);

// Если эксперт не авторизован, посылаем его пройти авторизацию
if (empty($_SESSION['exp_id']) && empty($_GET['action']))
{
	header("location: expert_system.php?action=login");	
}

// Что будем делать?
$action = html($_GET['action']);

switch( $action )
{
	// Регистрация
	case "registration": {
		if (!empty($_POST['registery']))
		{
			$reg = html($_POST['registery']);
			$file = $_FILES;
			
			$dbs = DbSimple_Generic::connect("mysql://".SYS_DB_USER.":".SYS_DB_PASS."@".SYS_DB_HOST."/".SYS_DB_BASE);
			$exps = new hmExpertSystem(&$dbs);
			
			switch($result = hmExpertSystem::CreateNewExpert($reg, $file)) 
			{
				case hmExpertSystem::DatabaseError:
					$errors[] = $errors_list[hmExpertSystem::DatabaseError];
				break;
				case hmExpertSystem::LoginError:
					$errors[] = $errors_list[hmExpertSystem::LoginError];					
				break;
				case hmExpertSystem::MailError:
					$errors[] = $errors_list[hmExpertSystem::MailError];
				break;
				case hmExpertSystem::AlreadyExists:
					$errors[] = $errors_list[hmExpertSystem::AlreadyExists];
				break;
				case hmExpertSystem::PhotoError:
					$errors[] = $errors_list[hmExpertSystem::PhotoError];
				break;
				default:
					$tpl->assign("success", "Регистрация прошла успешно!");
				break;
			}
			$tpl->assign("errors", $errors);
		}
	
		$tpl->assign('name_page', 'expert_system.registration');
		break;
	}
	// Список вопросов
	case "questions_list": {
		$dbs = DbSimple_Generic::connect("mysql://".SYS_DB_USER.":".SYS_DB_PASS."@".SYS_DB_HOST."/".SYS_DB_BASE);
		$exps = new hmExpertSystem(&$dbs);
		$order = "date_created desc, id desc";
		
		if ($_GET['order'] == 'cost')
		{
			$order = "cost_id desc";
		}
		
		if ($_GET['order'] == 'category')
		{
			$order = "category_id desc";
		}
        $listQuestions = $exps->loadExpertQuestion($_SESSION['expert']['id'], $order);

		$tpl->assign("listQuestion", $listQuestions);
		$tpl->assign('name_page', 'expert_system.list_for_expert');
		break;
	}
	// Смотри вопрос и отвечаем
	case "question": {		
		$dbs = DbSimple_Generic::connect("mysql://".SYS_DB_USER.":".SYS_DB_PASS."@".SYS_DB_HOST."/".SYS_DB_BASE);
		$exps = new hmExpertSystem(&$dbs);        
		
		require_once SYS_DIR_LIBS.'/ReportHandler.php';
		require_once SYS_DIR_LIBS.'/money.class.php';
		require_once SYS_DIR_LIBS.'/user.class.php';
		require_once SYS_DIR_LIBS.'/db.class.php';
		require_once SYS_DIR_LIBS.'/category.class.php';
		require_once SYS_DIR_LIBS.'/account.class.php';
		
		$db = new sql_db(SYS_DB_HOST, SYS_DB_USER, SYS_DB_PASS, SYS_DB_BASE);
		if(!$db->db_connect_id)
		{
			message_error(CRITICAL_ERROR, "Could not connect to the database");
		}
		
		$user = new User($db);
		$cat = new Category($db, $user);
		$acc = new Account($db, $user);
		
		$conf['account'] = $acc;
		$conf['money'] = new Money($db, $user);
		$conf['category'] = $cat;
		
		$rh = new ReportHandler($conf);
		
		if (!empty($_POST['answer']))
		{
			$answer['question'] = html($_POST['answer']);
			$answer['exp_id'] = 0;
			$exps->CreateNewPost($answer, html($_GET['id']), &$dbs);
		}
		$question = $exps->loadExpertQuestionId($_SESSION['expert']['id'], 0, html($_GET['id']));
		//$exps->UnCheckNewQuestionForExpert($_SESSION['expert']['id'], html($_GET['id']));
		
		for ($i=0; $i<count($question); $i++)
		{
			$question[$i]['message'] = stripslashes($question[$i]['message']);
			if (!empty($question[$i]['report']))
			{				
				$question[$i]['report'] = unserialize(htmlspecialchars_decode($question[$i]['report']));
			}
		}
		
		$catPaths = $rh->getCategoryPaths($question[0]['user_id']);
		// Берется из системного конфига
		$tpl->assign('currencies',$sys_currency_name);
		// Выводим полный список категорий и их родителей		
		$tpl->assign('parents',$catPaths);
		
		$tpl->assign("question", $question);
		$tpl->assign('name_page', 'expert_system.question_for_expert');
		break;
	}
	// Авторизация
	case "login": {
		$tpl->assign('name_page', 'expert_system.login');		
		
		$dbs = DbSimple_Generic::connect("mysql://".SYS_DB_USER.":".SYS_DB_PASS."@".SYS_DB_HOST."/".SYS_DB_BASE);
		$exps = new hmExpertSystem(&$dbs);
		if (!empty($_POST['login']) && !empty($_POST['pass']))
		{
			$login = html($_POST['login']);
			$pass = md5($_POST['pass']);
			switch($result = $exps->doLogin($login, $pass)) 
			{
				case hmExpertSystem::DatabaseError:
					$errors[] = $errors_list[hmExpertSystem::DatabaseError];
				break;
				case hmExpertSystem::EnterError:
					$errors[] = $errors_list[hmExpertSystem::EnterError];					
				break;
				default:
					$_SESSION['expert'] = $result[0];
					header("location: expert_system.php?action=questions_list");
				break;
			}
			$tpl->assign("errors", $errors);
		}
		break;
	}
	case "profile":
		$tpl->assign('name_page', 'expert_system.profile');		
		
		$dbs = DbSimple_Generic::connect("mysql://".SYS_DB_USER.":".SYS_DB_PASS."@".SYS_DB_HOST."/".SYS_DB_BASE);
		$exps = new hmExpertSystem(&$dbs);
		
		$profile = $exps->getExpertProfile($_SESSION['expert']['id']);
		$attach_content = $exps->getExpertAttachContent($_SESSION['expert']['id']);
		$costs = $exps->expert_costs;		
		$system_costs = $exps->system_expert_costs;
		$categories = $exps->expert_categories;
		$system_categories = $exps->system_expert_categories;
		
		$cnt_sys_cost = count($system_costs);
		$cnt_exp_cost = count($costs);
		$cnt_sys_categories = count($system_categories);
		$cnt_exp_categories = count($categories);
		$cnt_attach_content = count($attach_content);		
		
		if (!empty($_POST))
		{
			if ($_POST['pass'] == $_POST['pass_r'])
			{
				$data['f_name'] = html($_POST['f_name']);
				$data['l_name'] = html($_POST['l_name']);
				$data['m_name'] = html($_POST['m_name']);
				$data['about'] = html($_POST['about']);
				$data['mail'] = html($_POST['mail']);
				if (!empty($_POST['pass']))
				{
					$data['pass'] = md5($_POST['pass']);
				}
				$k=0;
				for ($i=0; $i<$cnt_sys_cost; $i++)
				{
					$c = $i+1;
					if ($_POST["cost_".$c] > 0)
					{						
						$data_cost[$k]['price'] = $_POST["cost_".$c];
						$data_cost[$k]['desc'] = html($_POST["desc_".$c]);
						$data_cost[$k]['cost_id'] = $c;
						$k++;
					}
				}
				$k=0;
				for ($i=0; $i<$cnt_sys_cost; $i++)
				{
					$c = $i+1;
					if ($_POST["desc_".$c] > 0)
					{						
						$data_cost[$k]['desc'] = html($_POST["desc_".$c]);
						$data_cost[$k]['cost_id'] = $c;
						$k++;
					}
				}
				$k=0;
				for ($i=0; $i<$cnt_sys_categories; $i++)
				{
					$c = $i+1;
					if (isset($_POST["cat_".$c]))
					{
						$data_cat[$k]['category_id'] = $c;
						$k++;
					}
				}
				
				if ($exps->saveExpertProfile($_SESSION['expert']['id'], $data, $data_cost, $data_cat, $_FILES))
				{
					header("location: expert_system.php?action=profile");
				}else{
					echo mysql_error();
				}
			}else{
				$errors[] = "Пароли не совпадают!";
			}
		}

		if ($cnt_sys_cost > 0)
		{
			for ($i=0; $i<$cnt_sys_cost; $i++)
			{
				$exp_costs[$i]['cost_id'] = $system_costs[$i]['cost_id'];
				$exp_costs[$i]['cost_name'] = $system_costs[$i]['cost_name'];				
				$exp_costs[$i]['price'] = 0;
				
				for ($j=0; $j<$cnt_exp_cost; $j++)
				{					
					if ($exp_costs[$i]['cost_id'] == $costs[$j]['cost_id'])
					{
						$exp_costs[$i]['price'] = $costs[$j]['price'];
						$exp_costs[$i]['desc'] = $costs[$j]['desc'];
					}
				}
			}
		}
		
		if ($cnt_sys_categories > 0)
		{
			for ($i=0; $i<$cnt_sys_categories; $i++)
			{
				$exp_categories[$i]['category_id'] = $system_categories[$i]['category_id'];
				$exp_categories[$i]['category_name'] = $system_categories[$i]['category_name'];				
				$exp_categories[$i]['expert'] = 0;
				
				for ($j=0; $j<$cnt_exp_categories; $j++)
				{
					if ($system_categories[$i]['category_id'] == $categories[$j]['category_id'])
					{
						$exp_categories[$i]['expert'] = 1;
					}
				}
			}
		}
		$j = 0;
		$k = 0;
		for ($i=0; $i<$cnt_attach_content;$i++)
		{
			if (!empty($attach_content[$i]['file_name']))
			{
				$attach_file[$k]['file_name'] = $attach_content[$i]['file_name'];
				$attach_file[$k]['about_file'] = $attach_content[$i]['about_file'];
				$k++;
			}
			if (!empty($attach_content[$i]['url_article']))
			{
				$attach_url[$j]['id'] = $attach_content[$i]['id'];
				$attach_url[$j]['url_article'] = $attach_content[$i]['url_article'];
				$attach_url[$j]['article'] = $attach_content[$i]['article'];
				$attach_url[$j]['article_active'] = $attach_content[$i]['article_active'];
				$j++;
			} 
		}
		
		$tpl->assign("profile", $profile[0]);
		$tpl->assign("attach_file", $attach_file);
		$tpl->assign("attach_url", $attach_url);
		$tpl->assign("costs", $exp_costs);
		$tpl->assign("categories", $exp_categories);
		break;
	case "delete_photo":
		$dbs = DbSimple_Generic::connect("mysql://".SYS_DB_USER.":".SYS_DB_PASS."@".SYS_DB_HOST."/".SYS_DB_BASE);
		$exps = new hmExpertSystem(&$dbs);		
		
		if ($exps->deleteExpertPhoto($_SESSION['expert']['id']))
		{
			header("location: expert_system.php?action=profile");
		}
		break;
	case "delete_articles":
		$dbs = DbSimple_Generic::connect("mysql://".SYS_DB_USER.":".SYS_DB_PASS."@".SYS_DB_HOST."/".SYS_DB_BASE);
		$exps = new hmExpertSystem(&$dbs);		
		
		if ($exps->deleteExpertArticles(html($_GET['id'])))
		{
			header("location: expert_system.php?action=profile");
		}
		break;
	case "delete_file":
		$dbs = DbSimple_Generic::connect("mysql://".SYS_DB_USER.":".SYS_DB_PASS."@".SYS_DB_HOST."/".SYS_DB_BASE);
		$exps = new hmExpertSystem(&$dbs);		
		
		if ($exps->deleteExpertFile(html($_GET['id'])))
		{
			header("location: expert_system.php?action=profile");
		}
		break;
	case "attach_content":
		$file = '';
		$url = '';
		if (!empty($_POST['url_articles']))
		{
			$url = html($_POST['url_articles']);
		}
		
		if (!empty($_FILES))
		{
			$file_type = substr($_FILES['attach_file']['name'], 1 + strrpos($_FILES['attach_file']['name'], "."));
			$file_name = md5(time()).".".$file_type;

			if (move_uploaded_file($_FILES['attach_file']['tmp_name'], FILE_DIR . $file_name))
			{
				$file['file_name'] = $file_name;
				$file['file_about'] = html($_POST['about_file']);
			}
		}
		
		$dbs = DbSimple_Generic::connect("mysql://".SYS_DB_USER.":".SYS_DB_PASS."@".SYS_DB_HOST."/".SYS_DB_BASE);
		$exps = new hmExpertSystem(&$dbs);
		if ($exps->saveExpertAttachContent($_SESSION['expert']['id'], $file, $url))
		{
			header("location: expert_system.php?action=profile");
		}
		break;
	case "report":
		$tpl->assign('name_page', 'expert_system.expert_report');		
		
		$order = "date_created desc";		
		$start_day = date("Y.m.d", mktime(0, 0, 0, date("m")  , date("d")-7, date("Y")));
		$start_day2 = date("d.m.Y", mktime(0, 0, 0, date("m")  , date("d")-7, date("Y")));
		$finish_day = date("Y-m-d");
		$finish_day2 = date("d.m.Y");
		$where = "and date_created >= '".$start_day."' and date_created <= '".$finish_day."'";
		
		//echo date("d.m.Y", $start_day);
		
		$dbs = DbSimple_Generic::connect("mysql://".SYS_DB_USER.":".SYS_DB_PASS."@".SYS_DB_HOST."/".SYS_DB_BASE);
		$exps = new hmExpertSystem(&$dbs);

		$listQuestions = $exps->loadExpertQuestion($_SESSION['expert']['id'], $order, $where);
		
		$total['all_questions'] = count($listQuestions);
		$total['costs'] = 0;
		$total['view_costs'] = 0;
		$total['work_costs'] = 0;
		$total['questions'] = 0;
		$total['view_questions'] = 0;
		$total['work_questions'] = 0;
		
		for($i=0; $i<$total['all_questions']; $i++)
		{
			if ($listQuestions[$i]['cost_id'] != 0)
			{
				$total['costs']++;
				if ($in_work = $exps->checkExpertQuestionInWork($listQuestions[$i]['id'], $_SESSION['expert']['expert_id']))
				{
					$total['view_costs']++;
				}else{
					$total['work_costs']++;
				}
			}else{
				$total['questions']++;
				if ($exps->checkExpertQuestionInWork($listQuestions[$i]['id'], $_SESSION['expert']['expert_id']))
				{
					$total['view_questions']++;
				}else{
					$total['work_questions']++;
				}
			}
			
		}

		$tpl->assign("start_day", $start_day2);
		$tpl->assign("total", $total);
		$tpl->assign("finish_day", $finish_day2);
		$tpl->assign("listQuestion", $listQuestions);		
		break;
	// Ничего не делаем
	default: {		
		break;
	}
} // switch

$tpl->display("expert_system.html");
?>