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

// Запускаем хелпер для IFRAME авторизации
// @XXX мб есть смысл его перенести в контроллер login??? правда всё равно нужно хидеры для осла ставить
// Helper_IframeLogin::login( $templateEngine );

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
