<?php
require_once dirname(__FILE__).'/../../../bootstrap/all.php';

/**
 * Обработка ошибок
 */
class ErrorTest extends myFunctionalTestCase
{
    /**
     * Отключить отладку
     */
    protected $debug = false;


    /**
     * Проверка авторизации
     */
    protected function getAuthenticationRequiredTestPlan()
    {
        return array();
    }


    /**
     * 404 страница
     */
    public function test404Error()
    {
        $this->browser
            ->get('/u-n-k-n-o-w-n/r-o-u-t-e')
            ->with('response')->begin()
                ->isStatusCode(404)
                ->matches('#страница не найдена#')
                ->matches('#<title>(.*?404.*?)</title>#')
            ->end();
    }

}
