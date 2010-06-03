<?php

class Helper_Js implements _Core_Router_iHook
{
    public static function execRouterHook( _Core_Request $request, &$class, &$method, array &$chunks, &$templateEngine )
    {
        if ( JS_MINIFY ) {
            $templateEngine->append('js',  'global-min.js');
        } else {
            foreach ( file( SYS_DIR_INC . 'assets/js.list') as $js )
            {
                $templateEngine->append('js', $js);
            }
        }

        if(IS_DEMO)
        {
            $templateEngine->append('js',  'demo_message.js');
        }

        /**
         * Динамическое подключение js файлов
         * в зависимости от модуля
         *
         */
        $jsArr = array();

        $urlArr = explode( '/', $request->uri, 3 );
        // первый элемент
        array_shift($urlArr);

        $module = strtolower( array_shift($urlArr) );

        if( array_key_exists( $module, Core::getInstance()->js ) )
        {
            $jsArr = Core::getInstance()->js[$module];
        }

        foreach ($jsArr as $jsFile)
        {
            $templateEngine->append('js', $jsFile.'.js');
        }
    }
}
