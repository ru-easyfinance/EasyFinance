<?php

require_once dirname(__FILE__).'/../lib/emailparsersGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/emailparsersGeneratorHelper.class.php';

/**
 * Дополнительные экшены в админку
 */
class emailparsersActions extends autoEmailparsersActions
{
    /**
     * Экшн проверки регулярных выражений
     *
     * @param sfWebRequest $request
     * @return sfView::NONE
     */
    public function executeRegexp(sfWebRequest $request)
    {
        $regexp = $request->getParameter("regexp", "");
        $source = $request->getParameter("source", "");

        if ( !strlen( trim( $regexp ) ) ) {
            $this->getResponse()->setContent( "Регулярное выражение пустое" );
            return sfView::NONE;
        }

        if ( $regexp[0] == '/' ) {
            $this->getResponse()->setContent( "Пожалуйста, укажите только само выражение.\nРазделители и флаги система подставит сама." );
            return sfView::NONE;
        }

        $matches = array();
        @preg_match( '/' . $regexp . '/im', $source, $matches );

        if ( !count( $matches ) ) {
            $this->getResponse()->setContent( "Ничего не найдено" );
            return sfView::NONE;
        }

        $this->getResponse()->setContent( print_r( $matches, true ) );
        return sfView::NONE;
    }
}
