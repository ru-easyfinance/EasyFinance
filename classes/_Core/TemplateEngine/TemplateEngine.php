<?php

/**
 * Базовый класс шаблонизатора
 *
 */
class _Core_TemplateEngine
{
    /**
     * Выбор шаблонизатора
     * в зависимости от политической ситуации =)
     *
     * @return _Core_TemplateEngine_Abstract
     */
    public static function getPrepared( _Core_Request $request )
    {
        switch( self::getResponseMode( $request ) )
        {
            case 'json':
                header('Content-Type: text/plain');
                $templateEngine = new _Core_TemplateEngine_Json();
                $templateEngine->excludeFromOutput(
                    array('res','name_page','js','css')
                );
                break;
            case 'csv':
                $templateEngine = new _Core_TemplateEngine_CSV();
                $templateEngine->excludeFromOutput(
                    array('res','name_page','js','css')
                );
                break;
            default:
                switch ( $request->host . '/' )
                {
                    case URL_ROOT_PDA:

                        $templateEngine = new _Core_TemplateEngine_Native( DIR_TEMPLATES . 'pda/' );

                        break;
                    default:

                        require_once SYS_DIR_LIBS . 'external/smarty/Smarty.class.php';
                        require_once SYS_DIR_LIBS . 'external/smarty/Smarty_Compiler.class.php';
                        require_once SYS_DIR_LIBS . 'external/smarty/Config_File.class.php';

                        $templateEngine = new Smarty();

                        $templateEngine->template_dir         =  SYS_DIR_ROOT.'/views';
                        $templateEngine->compile_dir         =  TMP_DIR_SMARTY.'/cache';

                        $templateEngine->plugins_dir         =  array(SYS_DIR_LIBS.'external/smarty/plugins');
                        $templateEngine->compile_check     =  true;
                        $templateEngine->force_compile     =  false;
                }
        }

        return $templateEngine;
    }

    public static function getResponseMode( _Core_Request $request )
    {
        $responseMode = null;

        if( array_key_exists('responseMode', $request->get) && $request->get['responseMode'] )
        {
            $responseMode = $request->get['responseMode'];
        }

        return $responseMode;
    }
}
