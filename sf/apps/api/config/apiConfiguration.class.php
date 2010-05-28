<?php

class apiConfiguration extends sfApplicationConfiguration
{
    /**
     * SetUp
     */
    public function configure()
    {
        $this->setWebDir($this->getRootDir().'/web.api');

        $this->getEventDispatcher()->connect(
            'request.method_not_found',
            array('apiConfiguration', 'listenToRequestMethodNotFound')
        );
    }

    /**
     * Вызывает разные способы получения тела POST в зависимости от окружения
     * Хак - эвенты рулят
     * TODO ? найти способ отсылать raw POST body из тестов - либо забить
     *
     * @see http://gist.github.com/170304
     * @see http://groups.google.com/group/symfony-users/msg/8408924d7693bc20
     */
    static public function listenToRequestMethodNotFound(sfEvent $event)
    {
        if ($event['method'] == 'getRawPostBody') {
            $event->setReturnValue(
                sfContext::getInstance()->getConfiguration()->getEnvironment() == 'test' ?
                $event->getSubject()->getParameter('body') : $event->getSubject()->getContent()
            );
            $event->setProcessed(true);
        }
    }


    /**
     * Init
     */
    public function initialize()
    {
    }


    /**
     * Инициализировать плагины
     */
    protected function initPlugins()
    {
        $plugins = array(
            'sfDoctrinePlugin',
        );

        if ('test' == $this->getEnvironment()) {
            $plugins[] = 'sfPhpunitPlugin';
        }

        $this->enablePlugins($plugins);
    }
}
