<?php

class Helper_Css implements _Core_Router_iHook
{
    public static function execRouterHook( _Core_Request $request, &$class, &$method, array &$chunks, &$templateEngine )
    {
        if (CSS_MINIFY) {
            $templateEngine->append('css', 'global-min.css');
        } else {
            foreach (file(SYS_DIR_INC . 'assets/css.list') as $css) {
                $templateEngine->append('css', $css);
            }
        }
    }
}
