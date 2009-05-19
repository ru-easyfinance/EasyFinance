<?php
/**
 * Модуль системы экспертов
 */
 
// подключаем все необходимые библиотеки
require_once (SYS_DIR_LIBS . "classes/hmExpertSystem.class.php");
require_once (SYS_DIR_LIBS . "external/DBSimple/Mysql.php");

// если пользователь не авторизован, фигачим его на главную страницу
if (empty($_SESSION['user'])) {
    header("Location: index.php");
}

// получаем действие
$action = html($g_action);

$errors_list = array(
	'-1' => "Ошибка с базой (".mysql_error().")");

switch ($action) {
	case "new_question":
		// пользователь задает вопрос
		$tpl->assign('name_page', 'expert_system.add_question');
		
		$dbs = DbSimple_Generic::connect("mysql://".SYS_DB_USER.":".SYS_DB_PASS."@".SYS_DB_HOST."/".SYS_DB_BASE);
		$exps = new hmExpertSystem(&$dbs);
		$tpl->assign("listExperts", $exps->loadListExperts());
		
		$cost_id = html($g_cost);
		$category_id = html($g_category);
		
		if (!empty($g_exp_id))
		{
			$tpl->assign("select_exp_id", html($g_exp_id));
		}
		if (!empty($p_title))
		{
			$question = html($_POST);
			switch($result = hmExpertSystem::CreateNewTopic($question, $_SESSION['user']['user_id'])) 
			{
				case hmExpertSystem::DatabaseError:
					$errors[] = $errors_list[hmExpertSystem::DatabaseError];
				break;				
				default:
					$_SESSION['success'] = "Вопрос успешно отправлен";
					header("location: index.php?modules=experts");
				break;
			}
			$tpl->assign("errors", $errors);
		}
		$tpl->assign('category_id',$category_id);
		$tpl->assign('cost_id',$cost_id);
		$tpl->assign('categories', $exps->getSystemExpertCategories());
		$tpl->assign('costs', $exps->getSystemExpertCost());
		break;
	case "get_list_experts":
		// грузим всех экспертов, чтобы пользователь мог выбрать, кому задать вопрос
		$tpl->assign('name_page', 'expert_system.list_experts');
		
		$dbs = DbSimple_Generic::connect("mysql://".SYS_DB_USER.":".SYS_DB_PASS."@".SYS_DB_HOST."/".SYS_DB_BASE);
		$exps = new hmExpertSystem(&$dbs);
        $listExperts = $exps->loadListExperts();

		$tpl->assign("listExperts", $listExperts);
		
		break;
	case "view_profile":
		$tpl->assign('name_page', 'expert_system.view_profile');
		$dbs = DbSimple_Generic::connect("mysql://".SYS_DB_USER.":".SYS_DB_PASS."@".SYS_DB_HOST."/".SYS_DB_BASE);
		$exps = new hmExpertSystem(&$dbs);
		$exp_id = html($_GET['exp_id']);
		
		$profile = $exps->getExpertProfile($exp_id);
		$attach_content = $exps->getExpertAttachContent($exp_id);
		$costs = $exps->expert_costs;		
		$system_costs = $exps->system_expert_costs;
		$categories = $exps->expert_categories;
		$system_categories = $exps->system_expert_categories;
		
		$cnt_sys_cost = count($system_costs);
		$cnt_exp_cost = count($costs);
		$cnt_sys_categories = count($system_categories);
		$cnt_exp_categories = count($categories);
		$cnt_attach_content = count($attach_content);
		
		if ($cnt_sys_cost > 0)
		{
			for ($i=0; $i<$cnt_sys_cost; $i++)
			{
				$exp_costs[$i]['cost_id'] = $system_costs[$i]['cost_id'];
				$exp_costs[$i]['cost_name'] = $system_costs[$i]['cost_name'];				
				//$exp_costs[$i]['price'] = 0;
				
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
				$k++;
			}
			if (!empty($attach_content[$i]['url_article']))
			{
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
	case "question": 		
		$dbs = DbSimple_Generic::connect("mysql://".SYS_DB_USER.":".SYS_DB_PASS."@".SYS_DB_HOST."/".SYS_DB_BASE);
		$exps = new hmExpertSystem(&$dbs);
		
		require_once SYS_DIR_LIBS.'/ReportHandler.php';
		require_once SYS_DIR_LIBS.'/money.class.php';
		
		$conf['account'] = $acc;
		$conf['money'] = new Money($db, $user);
		$conf['category'] = $cat;
		
		$rh = new ReportHandler($conf);		
		
		if (!empty($_POST))
		{
			$answer['question'] = $_POST['answer'];
			$answer['exp_id'] = $_POST['exp_id'];		
			
			// Формируем параметры отчета
			$p_account = @html($p_account);
			$p_dateFrom = @html($p_dateFrom);
			$p_dateTo = @html($p_dateTo);
			$p_currency = @html($p_currency);
			$rpd = array(
				// Начало диапазона отбора
				'dateFrom' => $p_dateFrom,
				// Окончание диапазона отбора
				'dateTo' => $p_dateTo,
				// Код пользователя
				'userID' => $_SESSION['user']['user_id'],
				// Код счета пользователя, по которому отбираются данные
				'account' => $p_account, // Не забывать проверять при выборках, чтобы счета принадлежали указанному пользователю, иначе можно будет смотреть информацию других людей
				// Курсы валют по отношению к рублю
				'currency_rates' => $sys_currency,
				// Валюта, в которой показывать суммы. Выбрана пользователем
				'currency' => $p_currency,
				// Название месяцев
				'months' => $sys_month,
			);
			
			$res = serialize($rh->getDetailedLoss($rpd));
			$answer['report'] = $res;
			
			$exps->CreateNewPost(html($answer), html($_GET['id']), &$dbs);
		}
		$question = $exps->loadExpertQuestionId(0, $_SESSION['user']['user_id'], html($_GET['id']));
		
		for ($i=0; $i<count($question); $i++)
		{
			$question[$i]['message'] = stripslashes($question[$i]['message']);
			if (!empty($question[$i]['report']))
			{				
				$question[$i]['report'] = unserialize(htmlspecialchars_decode($question[$i]['report']));
			}
		}
		
		$exps->UnCheckNewQuestion($_SESSION['user']['user_id'], html($_GET['id']));
		
		if (!$exps->CheckExistsByVoice(html($_GET['id'])))
		{
			$tpl->assign("is_voiced", true);
		}
		
		$userAccounts = $rh->getUserAccounts($_SESSION['user']['user_id']);
		$catPaths = $rh->getCategoryPaths($_SESSION['user']['user_id']);
		
		$tpl->assign("question", $question);
		$tpl->assign("dateFrom",date("01.m.Y"));
		$tpl->assign("dateTo", date("d.m.Y"));
		// Берется из системного конфига
		$tpl->assign('currencies',$sys_currency_name);
		$tpl->assign('reports',$sys_reports);
		// Выводим список счетов на экран
		$tpl->assign('userAccounts',$userAccounts);
		// Получаем полный список категорий и их родителей		
		$tpl->assign('parents',$catPaths);
		$tpl->assign('name_page', 'expert_system.question');
		break;
	case "review":
		//отзывы и предложения по эксперту
		$tpl->assign('name_page', 'expert_system.review');
		
        $dbs = DbSimple_Generic::connect("mysql://".SYS_DB_USER.":".SYS_DB_PASS."@".SYS_DB_HOST."/".SYS_DB_BASE);
		$exps = new hmExpertSystem(&$dbs);
		$exp_id = html($g_exp_id);
		
		if (!empty($p_review))
		{
			$review = html($review);
			$exp_id = html($p_exp_id);
			$review = html($p_review);
			if ($exps->saveExpertReview($exp_id, $_SESSION['user']['user_id'], $review))
			{
				header("location: index.php?modules=experts&action=review&exp_id=".$exp_id);
			}
		}
		
        $listReview = $exps->getListReview($exp_id);		
		$tpl->assign('listReview', $listReview);
		$tpl->assign('exp_id', $exp_id);
		break;
    default:        
		// говорим какую страницу будем показывать по-умолчанию, и грузим все вопросы, которые задал когда-либо пользователь
		$tpl->assign('name_page', 'expert_system.list');
		
        $dbs = DbSimple_Generic::connect("mysql://".SYS_DB_USER.":".SYS_DB_PASS."@".SYS_DB_HOST."/".SYS_DB_BASE);
		$exps = new hmExpertSystem(&$dbs);
        $listQuestion = $exps->loadUserQuestion($_SESSION['user']['user_id']);

		$tpl->assign("listQuestion", $listQuestion);
		
		$tpl->assign("success", $_SESSION['success']);
		$_SESSION['success'] = false;
        break;
}
?>