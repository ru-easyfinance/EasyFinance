<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс контроллера для модуля login
 * @category login
 * @copyright http://easyfinance.ru/
 * @version SVN $Id$
 */
class Login_Controller extends _Core_Controller
{
    /**
     * Ссылка на класс модели пользователя
     * @var Login_Model
     */
    private $model = null;

    /**
     * Конструктор класса
     * @return void
     */
    function __init()
    {
        $this->model = new Login_Model();
        $this->tpl->assign('name_page', 'login');
    }

	/**
	 * Индексная страница
	 * @param array $args  mixed
	 * @return void
	 */
	function index($args)
	{

        session_start();
        
		$user = Core::getInstance()->user;
		
		// Обьект запроса
		$request = _Core_Request::getCurrent();
		
		$errorMessage = null;
		
		// Проверка заполненности формы логина
		if ( 
			!isset($request->post['login']) || !isset($request->post['pass'])
			|| empty($request->post['pass']) || empty($request->post['pass'])
		)
		{
			$errorMessage = 'Заполните, пожалуйста, все поля формы!';
		}
		else
		{
			// Подготавливаем переменную
			$login = htmlspecialchars($request->post['login']);
			$pass = sha1($request->post['pass']);
			
			if( !$user->initUser($login,$pass) )
			{
				$errorMessage = 'Некорректный логин или пароль!';
			}
		}

		// Ошибка передаётся в шаблон только при POST запросе
		if( $request->method != 'POST' )
		{
			$this->tpl->assign( 'errorMessage', null);
		}
		else
		{
			$this->tpl->assign( 'errorMessage', $errorMessage );
		}
		
		if( !$errorMessage )
		{
			// Шифруем и сохраняем куки
			if (isset($_POST['autoLogin']))
			{
				setcookie(COOKIE_NAME, encrypt(array($login,$pass)), time() + COOKIE_EXPIRE, COOKIE_PATH, COOKIE_DOMEN, COOKIE_HTTPS);
				// Шифруем, но куки теперь сохраняются лишь до конца сессии
			}
			else
			{
				setcookie(COOKIE_NAME, encrypt(array($login,$pass)), 0, COOKIE_PATH, COOKIE_DOMEN, COOKIE_HTTPS);
			}


			if ( sizeof($user->getUserCategory()) == 0 )
			{
				setcookie('guide', 'uyjsdhf', 0, COOKIE_PATH, COOKIE_DOMEN, false);
            }

			// У пользователя нет категорий, т.е. надо помочь ему их создать
			if ( sizeof($user->getUserCategory()) == 0 && $user->getType() == 0) {

				$this->model->activate_user();
                
			}

            // Устанавливаем дефолтный путь
            if (!isset($_SESSION['REQUEST_URI'])) {
                $_SESSION['REQUEST_URI'] = '/info/';
            }
		}

        if ( isset($_POST['responseMode']) && $_POST['responseMode'] == 'json') {
            if (!$errorMessage) {
                die(json_encode(
                    array('result' => array(
                            'text' => 'Login success!'
                ))));
            } else {
                die(json_encode(
                    array('error' => array(
                            'text' => $errorMessage
                ))));
            }
        } else {
            if (!$errorMessage) {
                header("Location: ".$_SESSION['REQUEST_URI']);
                unset($_SESSION['REQUEST_URI']);
                exit;
            }
        }

		// Если демо режим - всегда показываем Гид
		if (IS_DEMO)
		{
			setCookie("guide", "uyjsdhf",0,COOKIE_PATH, COOKIE_DOMEN, false);
		}
		
		if( IS_DEMO && !Core::getInstance()->user->getId() )
		{
			$this->model->authDemoUser();
		}
	}
}
