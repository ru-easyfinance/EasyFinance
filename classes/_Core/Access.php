<?php

/**
 * Класс регулирующий доступ пользователей
 * к разделам сайта на основе файла конфигурации
 *
 * @copyright http://easyfinance.ru/
 * @author Andrew Tereshko aka mamonth
 */
class _Core_Access implements _Core_Router_iHook
{
    /**
     * Конфигурация доступа
     *
     * @var array
     */
    private $config = array();

    private  $userType;
    private $defaultPage;

    const ALLOW_ALL = -101;
    const ALLOW_AUTHORIZED = -102;

    /**
     * Конструктор
     *
     * @param _User $user
     */
    public function __construct( $user )
    {
        // Если пользователь не авторизован
        if( is_null( $user ) )
        {
            $this->userType     = _User::TYPE_GUEST;
            $this->defaultPage     = '/';
        }
        else
        {
            $this->userType     = $user->getType();
            $this->defaultPage     = $user->getDefaultPage();
        }

        $this->config = $this->loadConfig( DIR_CONFIG . 'access.php' );
    }

    public static function execRouterHook( _Core_Request $request, &$class, &$method, array &$chunks, &$templateEngine )
    {
        $access = new _Core_Access( _User::getCurrent() );

        if( !$access->isAllowed( $request->uri ) )
        {
            // Редирект через заголовок. Дабы явно сменился урл в браузере оО
            _Core_Router::redirect( $access->defaultPage, true, 403 );
        }
    }

    /**
     * Проверяет разрешён ли доступ пользователю к запросу
     *
     * @param uri без get параметров $requestUri
     * @return boolean
     */
    public function isAllowed( $requestUri )
    {
        $allowed = false;

        // Если запрос начинался с "/" - отрезаем начало (пустой элемент)
        if( substr($requestUri,0,1) == '/' )
        {
            $requestUri = substr( $requestUri, 1 );
        }

        $requestArr = explode( '/', $requestUri );

        // Выясняем кол-во элементов в запросе
        $chunksCount = sizeof( $requestArr );

        // Если запрос пуст (т.е. запрос главной страницы) - разрешаем доступ
        if( $chunksCount == 1 && $requestArr[0] == '' )
        {
            // Не самый лучший вариант - выходить из метода в нескольких местах,
            // но лишний раз выполнять проверку на главной - ещё хуже.
            return true;
        }

        // Реверсный (с последнего элемента) поиск правила для запроса
        for ( $i = 1; $i <= $chunksCount; $i++ )
        {
            $requestString = implode( '/', $requestArr );

            // Если нашли правило ...
            if( isset( $this->config[ $requestString ] ) )
            {
                // ... выясняем тип правила (одиночное || массивом) и можно ли дать доступ
                if(
                    is_array($this->config[ $requestString ])
                    && (
                        in_array( $this->userType, $this->config[ $requestString ] )
                        // проверка на общий доступ
                        || in_array( self::ALLOW_ALL, $this->config[ $requestString ])
                        // проверка - только зарегистрированным
                        || (
                            in_array( self::ALLOW_AUTHORIZED , $this->config[ $requestString ])
                            && $this->userType != _User::TYPE_GUEST
                        )
                    )
                )
                {
                    $allowed = true;break;
                }
                elseif (
                    $this->userType == $this->config[ $requestString ]
                    // проверка на общий доступ
                    || self::ALLOW_ALL == $this->config[ $requestString ]
                    // проверка - только зарегистрированным
                    || (
                        $this->config[ $requestString ] == self::ALLOW_AUTHORIZED
                        && $this->userType != _User::TYPE_GUEST
                    )
                )
                {
                    $allowed = true;break;
                }

            }
            // Если правило для uri не найдено
            else
            {
                // отрезаем последний элемент запроса и повторяем
                array_pop( $requestArr );
            }
        }

        return $allowed;
    }

    private function loadConfig( $configPath )
    {
        if( !file_exists( $configPath ) )
        {
            throw new _Core_Exception('Configuration file don\'t exist!');
        }

        include( $configPath );

        if( !isset($accessConfig) || !is_array($accessConfig) )
        {
            throw new _Core_Exception('Configuration file broken!');
        }

        return $accessConfig;
    }
}
