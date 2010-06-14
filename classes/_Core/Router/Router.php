<?php

class _Core_Router
{
    protected $request;
    protected $hooks = array();

    protected $className         = null;
    protected $methodName         = null;
    protected $requestRemains     = array();

    protected $templateEngine;

    protected static $headerByCode = array(
        403 => 'HTTP/1.1 403 Forbidden',
        404 => 'HTTP/1.1 404 Not Found',
    );

    public function __construct( _Core_Request $request, $templateEngine )
    {
        $this->request = $request;

        $this->templateEngine = $templateEngine;

        $this->configureHooks( DIR_CONFIG . 'router_hooks.conf'  );
    }

    public function performRequest()
    {
        // Формируем массив для разбора (substr отрезает "/" в начале)
        $uri         = substr( $this->request->uri, 1 );
        $uriArr        = explode( '/', $uri );

        // Предопределяем переменную для хранения последней части запроса
        $lastPart = null;

        // Цикл по реверсивному поиску класса\метода оО (ниже понятнее) =)
        $iterations = sizeof($uriArr);
        for( $i = $iterations; $i >= 1; $i-- )
        {
            // Пытаемся сформировать название класса из запроса
            $className = $this->formClassName( $uriArr );

            // если таковой существует ...
            if( class_exists( $className ) )
            {
                $this->className = $className;

                // Проверяем нет ли метода с имененем = последнему элементу запроса
                if( method_exists( $this->className, $lastPart ) )
                {
                    $this->methodName    = $lastPart;

                    // Не забываем подчистить остатки
                    array_shift($this->requestRemains);

                }
                // ... ежели нету - устанавливаем метод по умолчанию
                else
                {
                    $this->methodName    = 'index';
                }

                break;
            }

            // Перед переходом на последующую итерацию обрезаем последний элемент запроса
            $lastPart = array_pop( $uriArr );
            // И пропихиваем его в массив остатков
            array_unshift( $this->requestRemains, $lastPart );

            // Если итерация = 1 а класс ещё не определён
            if( $i == 1 && empty($this->className) )
            {
                //Подкидываем ещё один элемент в запрос
                array_unshift( $uriArr, 'index' );

                // Увеличиваем счётчик...
                $i++;
            }
        }

        // Вызов подключённых хуков
        foreach ( $this->hooks as $className )
        {
            call_user_func_array( array($className,'execRouterHook'), array(
                $this->request,
                &$this->className,
                &$this->methodName,
                &$this->requestRemains,
                &$this->templateEngine
            ));
        }

        $controller = new $this->className( $this->templateEngine, $this->request );

        call_user_func( array( $controller, $this->methodName ), $this->requestRemains );

        unset($controller);
    }

    public function addHook( $className )
    {
        if( !class_exists( $className ) )
        {
            throw new _Core_Exception('Class with name "' . $className . '" does not exist!');
        }

        if( !in_array( '_Core_Router_iHook', class_implements($className) ) )
        {
            throw new _Core_Exception('Class "' . $className . '" don\'t implements "_Core_Router_iHook"! ');
        }

        $this->hooks[] = $className;
    }

    public function configureHooks( $configPath )
    {
        if( !file_exists($configPath) )
        {
            throw new _Core_Exception('The file specified for hooks configure is not exist!');
        }

        foreach ( file( $configPath ) as $className )
        {
            $this->addHook( trim($className) );
        }
    }

    public static function redirect( $url, $isExternal = false, $statusCode = 200 )
    {
        if( array_key_exists($statusCode, self::$headerByCode ) )
        {
            header( self::$headerByCode[$statusCode] );
        }

        if( $isExternal )
        {
            header('Location: ' . $url );
            exit();
        }

        $request = _Core_Request::getFake( $url );

        $router = new _Core_Router( $request, _Core_TemplateEngine::getPrepared($request) );

        try
        {
            $router->performRequest();
        }
        catch ( Exception $e )
        {
            // Вывод отладочной информации
            if(  DEBUG )
            {
                echo highlight_string( "<?php\n" . $e->getTraceAsString() );
                exit();
            }
            // Не позволяем бесконечных циклов
            elseif( '/notfound' == $url )
            {
                exit();
            }
            else
            {
                self::redirect('/notfound', false, 404);
            }
        }
    }

    protected function formClassName( array $array )
    {
        $array = array_map( 'ucfirst', $array );

        $className = implode( '_', $array ) . '_Controller';

        return $className;
    }
}
