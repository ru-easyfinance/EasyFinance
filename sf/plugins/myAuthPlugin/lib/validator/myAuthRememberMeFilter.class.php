<?php
/**
 * Вспоминает пользователя по печенью "запомнить меня".
 *
 * Этот фильтр нужно вставить в файл filters.yml **выше**
 * фильтра security, вот так:
 *
 *    remember_me:
 *      class: myAuthRememberMeFilter
 *
 *    security: ~
 */
class myAuthRememberMeFilter extends sfFilter
{
    public function execute($filterChain)
    {
        $cookieName = sfConfig::get('app_my_auth_plugin_remember_cookie_name', 'myRemember');

        if (
            $this->isFirstCall()
            &&
            !$this->context->getUser()->isAuthenticated()
            &&
            $cookie = $this->context->getRequest()->getCookie($cookieName)
        )
        {
            $q = Doctrine::getTable('myAuthRememberKey')->createQuery('r')
                ->innerJoin('r.User u')
                ->where('r.remember_key = ?', $cookie);

            if ($q->count())
            {
                $this->context->getUser()->signIn($q->fetchOne()->User);
            }
        }

        $filterChain->execute();
    }
}
