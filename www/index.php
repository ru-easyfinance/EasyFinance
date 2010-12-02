<?php
/**
 * Индексный файл проекта
 * @copyright http://easyfinance.ru/
 * @version SVN $Id$
 */
define('INDEX', true);

error_reporting( E_ALL );

//получим схему явно - в этом месте не удается инстанциировать request,
//так как пути к нему заданы позже - в конфиге, который уже зависит от PROTOCOL_SCHEME
define ('USING_HTTPS', ($_SERVER["SERVER_PORT"] == 443 ) ? 1 : 0);
define('PROTOCOL_SCHEME', USING_HTTPS ? 'https' : 'http');

function createUrlWithScheme($urlWithoutScheme) {
    return PROTOCOL_SCHEME . "://" . $urlWithoutScheme;
}

// Подключаем файл с общей конфигурацией проекта
require_once dirname(dirname(__FILE__)) . '/include/config.php';

// Загружаем общие данные
// @todo оторвать!
require_once SYS_DIR_INC . 'common.php';

// Получаем обьект с параметрами запроса.
$request = _Core_Request::getCurrent();

// Получаем текущий шаблонизатор на основании запроса
$templateEngine = _Core_TemplateEngine::getPrepared( $request );

// Запускаем хелпер для IFRAME авторизации и подключения шаблонов
Helper_IframeLogin::login($templateEngine);

// Инициализация роутера
$router = new _Core_Router( $request, $templateEngine );

try
{
	// Выполнение запроса (разбор ->вызов контроллера)
	$router->performRequest();

    if (_Core_Request::getCurrent()->host . '/' == HOST_ROOT_IFRAME) {
        $templateEngine->display('iframe/index.iframe.html');
    } elseif (_Core_Request::getCurrent()->host . '/' == HOST_ROOT_RAMBLER) {
        $templateEngine->display('index.html');
    } else {

        if (!IS_DEMO) {

            // Если пользователь зашёл с мобильного браузера
            if (
                _Core_Request::getCurrent()->host . '/' != HOST_ROOT_PDA
                && Helper_DetectBrowser::detectMobile()
                && !isset($_COOKIE['DO_WHANT_FULL_VERSION'])
            ) {
                if (
                    isset($_SERVER['HTTP_REFERER'])
                    && strpos($_SERVER['HTTP_REFERER'], HOST_ROOT_PDA) === false
                ) {
                    header( 'Location: ' . URL_ROOT_PDA );
                    exit;
                } else {
                    setcookie('DO_WHANT_FULL_VERSION', true);
                }
            }
        }

        $templateEngine->display( 'index.html' );
    }

	// Применение модификаций\удалений моделей
	_Core_ObjectWatcher::getInstance()->performOperations();
}
catch ( Exception $e )
{
	// Вывод отладочной информации
	if(  DEBUG )
	{
            if ( strtolower(ini_get('html_errors')) == 'on' ) {
		highlight_string( "<?php\n #" . $e->getMessage() . "\n\n in "
			. $e->getFile() . ':'
			. $e->getLine() . "\n\n" . $e->getTraceAsString() );
            } else {
		 print ($e->getMessage() . "\n\n in "
			. $e->getFile() . ':'
			. $e->getLine() . "\n\n" . $e->getTraceAsString());
            }
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
