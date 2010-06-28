<?php
require_once dirname(__FILE__).'/../../../bootstrap/all.php';


/**
 * API: 404 ошибка
 */
class api_common_Error404Test extends myFunctionalTestCase
{
    protected $app   = 'api';

    /**
     * Отключить дебаг, чтобы увидеть ответ
     */
    protected function isDebug()
    {
        return false;
    }


    /**
     * Error 404
     */
    public function test404()
    {
        $this->browser
            ->getAndCheck('', '', '/some/unknown/route', 404)
            ->with('response')->begin()
                ->checkElement('response error[code="404"]', 'Not Found')
            ->end();
    }

}
