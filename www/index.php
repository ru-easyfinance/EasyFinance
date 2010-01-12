<?php
/**
 * Индексный файл проекта
 * @copyright http://easyfinance.ru/
 * @version SVN $Id$
 */
 
define('INDEX', true);

// Загружаем общие данные
require_once dirname(dirname(__FILE__)). "/include/common.php";

Core::getInstance()->parseUrl();

// Новая схема 
/*
try
{
	_Core::getInstance()->router->performRequest();
}
catch ( Exception $e )
{
	// Вывод отладочной информации
	if(  DEBUG )
	{
		$e->getTraceAsString();
	}
	// Не позволяем бесконечных циклов
	elseif( '/404' == $url )
	{
		exit();
	}
	else
	{
		_Core_Router::redirect('/404', false, 404);
	}
}

*/

// Определяем информацию о пользователе
//@TODO Переместить это в конструктор базового контроллера.
if (Core::getInstance()->user->getId())
{
	$uar = array(
		'user_id'   => Core::getInstance()->user->getId(),
		'user_name' => $_SESSION['user']['user_name'],
		'user_type' => $_SESSION['user']['user_type']
	);
	
	Core::getInstance()->tpl->assign('user_info', $uar);
}

//Выводим страницу в браузер
switch ( $_SERVER['HTTP_HOST'].'/' )
{
	// Загрузка страницы в IFRAME
	case URL_ROOT_IFRAME:
                // Выводим заголовки политики безопастности в IE для поддержки cookies в iframe
                if( $_SERVER['HTTP_HOST'].'/' == URL_ROOT_IFRAME)
                {
                        header('P3P:CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');
                }

                // Если это партнёр Азбука-Финансов и у нас есть ИД пользователя
		if (
			(substr($_SERVER['REQUEST_URI'], 0, 14) == "/login/azbuka/")
			&& ( substr($_SERVER['REQUEST_URI'],15,5) == 'id_ef')
		)
		{
			// @TODO Нет проверки подлинности пользователя. Не проверяется, сайт откуда пришли
			// нет проверки почты пользователя (которая может измениться)
			// Блок в контроллер. 
			$select = Login_Model::getUserDataByID( substr($_SERVER[argv][0], 20) );
			$uar = array(
				'user_id'=>substr($_SERVER[argv][0], 20),
				'user_name'=>$select[0]['user_login'],
				'user_type'=>0
			);
			Core::getInstance()->tpl->assign('user_info', $uar);
			setcookie(COOKIE_NAME, encrypt(array($select[0]['user_login'],$select[0]['user_pass'])), time() + COOKIE_EXPIRE, COOKIE_PATH, 'iframe.'.COOKIE_DOMEN, COOKIE_HTTPS);
			
			// Блок что делает непонятно, но видимо в шаблонизатор ?
			Core::getInstance()->tpl->assign('template_view', 'iframe');
			
			// Опять контроллер. Редирект перевести в internal
			header("Location: https://iframe." . URL_ROOT_MAIN . "info/");
			break;
		}
		
		// Если это партнёр Азбука-Финансов, но у нас нет ИД пользователя
		if (
                        (substr($_SERVER['REQUEST_URI'], 0, 14) == "/login/azbuka/")
			&& ( substr($_SERVER['REQUEST_URI'],15,5) == 'login')
		)
		{
			$newId = Login_Model::generateUserByAzbukaLogin( substr($_SERVER[argv][0], 20) );
			return $newId;
		}
		if (
			!Core::getInstance()->user->getId()
			&& !in_array( $_SERVER['REQUEST_URI'], array("/login/", '/registration/',  '/restore/') )
		)
		{
			header("Location: https://iframe." . URL_ROOT_MAIN . "login/");
		}
		
		Core::getInstance()->tpl->assign('template_view', 'iframe');
		Core::getInstance()->tpl->display("iframe/index.iframe.html");
		break;
		
	// Загрузка обычной страницы
	default:
		Core::getInstance()->tpl->assign('template_view', 'index');
		Core::getInstance()->tpl->display("index.html");
		break;
}

// Применение модификаций\удалений моделей
_Core_ObjectWatcher::getInstance()->performOperations();
