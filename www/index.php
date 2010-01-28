<?php
/**
 * Индексный файл проекта
 * @copyright http://easyfinance.ru/
 * @version SVN $Id$
 */
define('INDEX', true);

error_reporting( E_ALL );

// Подключаем файл с общей конфигурацией проекта
require_once dirname(dirname(__FILE__)) . '/include/config.php';

// Загружаем общие данные
// @todo оторвать! 
require_once SYS_DIR_INC . 'common.php';

// Получаем обьект с параметрами запроса.
$request = _Core_Request::getCurrent();

// Получаем текущий шаблонизатор на основании запроса
$templateEngine = _Core_TemplateEngine::getPrepared( $request );

// Инициализация роутера
$router = new _Core_Router( $request, $templateEngine );

try
{
	// Выполнение запроса (разбор ->вызов контроллера)
	$router->performRequest();
	
	$templateEngine->display( 'index.html' );
	
	// Применение модификаций\удалений моделей
	_Core_ObjectWatcher::getInstance()->performOperations();
}
catch ( Exception $e )
{
	// Вывод отладочной информации
	if(  DEBUG )
	{
		highlight_string( "<?php\n #" . $e->getMessage() . "\n\n in " 
			. $e->getFile() . ':' 
			. $e->getLine() . "\n\n" . $e->getTraceAsString() );
	}
	// Не позволяем бесконечных циклов
	elseif( '/notfound' == $request->uri )
	{
		//exit();
	}
	else
	{
		_Core_Router::redirect('/notfound', false, 404);
	}
}

exit();

//Выводим страницу в браузер
<<<<<<< .working
switch ( $_SERVER['HTTP_HOST'].'/' )
{
	// Загрузка страницы в IFRAME
	case URL_ROOT_IFRAME:
                // Выводим заголовки политики безопастности в IE для поддержки cookies в iframe
                if( $_SERVER['HTTP_HOST'].'/' == URL_ROOT_IFRAME)
                {
                        header('P3P:CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');
                }
=======
switch ( $_SERVER['HTTP_HOST'].'/' ) {
    case URL_ROOT_IFRAME:
        if (( substr($_SERVER['REQUEST_URI'],0,7) == "/login/") && ( $_GET['refer'] == 'azbuka' ) && ( isset($_GET['mail']) ) && ( isset($_SESSION['easyid']) )  ){
            $select = Login_Model::getUserDataByID( $_SESSION('easyid') );
            if ( substr( $select[0]['user_login'] , 0, 6 ) != 'azbuka' )
                die('Аллес!!! Доступ запрещён');
>>>>>>> .merge-right.r2667
            if ( $_GET['mail'] != $select[0]['user_mail'] )
                die('Неверная почта');

<<<<<<< .working
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
=======
            $uar = array(
                'user_id'=>$_SESSION('easyid'),
                'user_name'=>$select[0]['user_login'],
                'user_type'=>0);
            Core::getInstance()->tpl->assign('user_info', $uar);
            Core::getInstance()->tpl->assign('template_view', 'iframe');
            setcookie(COOKIE_NAME, encrypt(array($select[0]['user_login'],$select[0]['user_pass'])), time() + COOKIE_EXPIRE, COOKIE_PATH, COOKIE_DOMEN, COOKIE_HTTPS);
            header("Location: https://" . URL_ROOT_IFRAME .  "info/");
            break;
        }
        if (( substr($_SERVER['REQUEST_URI'],0,7) == "/login/") && ( $_GET['refer'] == 'azbuka' ) && ( isset($_GET['id_ef']) )){
            $_SESSION['easyid'] = $_GET['id_ef'];//записываем айди в сессию
            $ch = curl_init();
            $id = $_GET['id_ef'];
            curl_setopt($ch, CURLOPT_URL, "http://www.azbukafinansov.ru/ef/confirmmail.php?ef_id=".$id);

            curl_exec($ch);//запрашиваем почту юзера если он залогинен

            curl_close($ch);
            break;
        }
        if (( substr($_SERVER['REQUEST_URI'],0,7) == "/login/") && ( $_GET['refer'] == 'azbuka' ) && ( isset($_GET['login'] ) && ( isset($_GET['mail']) )) ){
            $log = new Login_Model();
            //$requeststring = substr($_SERVER[argv][0], 20);
            //$array = explode("&", $requeststring);
            $login = $_GET['login'];
            $mail = $_GET['mail'];
            $newId = $log->generateUserByAzbukaLogin( $login , $mail );

            $select = Login_Model::getUserDataByID( $newId );
            if ( substr( $select[0]['user_login'] , 0, 6 ) != 'azbuka' )
                die('Аллес!!! Доступ запрещён');

            $uar = array(
                'user_id'=>$newId,
                'user_name'=>$select[0]['user_login'],
                'user_type'=>0);
            Core::getInstance()->tpl->assign('user_info', $uar);
            Core::getInstance()->tpl->assign('template_view', 'iframe');
            setcookie(COOKIE_NAME, encrypt(array($select[0]['user_login'],$select[0]['user_pass'])), time() + COOKIE_EXPIRE, COOKIE_PATH, COOKIE_DOMEN, COOKIE_HTTPS);
            header("Location: https://" . URL_ROOT_IFRAME . "info/");

            return $newId;
            break;
        }
        if ( ( ! Core::getInstance()->user->getId() ) AND ($_SERVER['REQUEST_URI'] != "/login/" ) ) {
            if ( $_SERVER['REQUEST_URI'] != '/registration/' &&  $_SERVER['REQUEST_URI'] != '/restore/') {
                header("Location: https://" . URL_ROOT_IFRAME . "login/");
            }
        }
        Core::getInstance()->tpl->assign('template_view', 'iframe');
        Core::getInstance()->tpl->display("iframe/index.iframe.html");
        break;
    default:
        Core::getInstance()->tpl->assign('template_view', 'index');
        Core::getInstance()->tpl->display("index.html");
        break;
>>>>>>> .merge-right.r2667
}

