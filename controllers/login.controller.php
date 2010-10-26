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
        if (!session_id()) {
            session_start();
        }

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

            $this->model->login($login, $pass, @$_POST['autoLogin']);

        }

        if (isset($_POST['responseMode']) && $_POST['responseMode'] == 'json') {
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
                $redirectUrl = isset($_SESSION['REQUEST_URI']) ?
                    $_SESSION['REQUEST_URI'] : '/info/';
                header("Location: $redirectUrl");
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

    /**
     * Авторизует пользователя рамблера
     *
     * Страница рамблера передаёт в единственном аргументе зашифрованный
     * ассоциативный массив данных о пользователе например:
     * {
     *     "date":        "Sun, 12 Sep 2010 20:16:21 +0400",
     *     "id":          "ef-user-dsdsdsd-122121212",
     *     "name":        "Ivan",
     *     "email":       "ivan@nail.ru",
     *     "redirectUrl": "/my/wikiwrapper/tiki-view_blog.php?blogId=1"
     * }
     * @param $args
     */
    public function rambler($args)
    {
        $ramblerString = $args[0];

        $cipher = MCRYPT_RIJNDAEL_128;
        $key    = 'X9Kls8DR72DqEFKLCMN02DdOQWdfLP2a';
        $iv     = 'dOQWdfLP2aCZM12D';

        $decoded = urlsafe_b64decode($ramblerString);
        $json    = mcrypt_cbc($cipher, $key, $decoded, MCRYPT_DECRYPT, $iv);
        $data    = json_decode(trim($json), true);

        $default = array(
            'id' => null,
            'email' => null,
            'name' => 'Рамблер',
            'redirectUrl' => '/info/'
        );

        $data = array_merge($default, (array)$data);
        $ramblerLogin = $data['id'] ? "rambler_{$data['id']}" : null;

        $user = Core::getInstance()->user;
        $user->destroy();

        // Пытаемся инициализировать пользователя
        $user->initUser($ramblerLogin, sha1($ramblerLogin));
        // Создаём нового пользователя
        if (!$user->getId() && $data['id']) {
            Login_Model::generateUserByRamblerLogin(
                $ramblerLogin,
                $data['email'],
                $data['name']
            );

            $data['redirectUrl'] = '/my/review/';
            $user->initUser($ramblerLogin, sha1($ramblerLogin));
            setCookie("guide", "uyjsdhf", 0, COOKIE_PATH, COOKIE_DOMEN, false);
        }

        if ($user->getId()) {
            $this->model->login($ramblerLogin, sha1($ramblerLogin), true);
            header(sprintf('Location: %s', $data['redirectUrl']));
        } else {
            header('Location: /login/');
        }

        die();
    }
}
