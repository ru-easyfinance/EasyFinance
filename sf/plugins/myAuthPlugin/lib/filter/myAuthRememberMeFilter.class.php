<?php
/**
 * Вспоминает пользователя по cookie "запомнить меня".
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
    /**
     * Filter chain
     */
    public function execute($filterChain)
    {
        $cookieName = sfConfig::get('app_myAuth_rememberMe_cookie', 'myAuthRememberMe');

        if ($this->isFirstCall() && !$this->context->getUser()->isAuthenticated() && $cookie = $this->context->getRequest()->getCookie($cookieName)) {
            $q = Doctrine::getTable('myAuthRememberKey')->findWithUserByRememberKey($cookie);

            if ($q->count()) {
                $this->context->getUser()->signIn($q->fetchOne()->getUser());
            }
        }

        $filterChain->execute();
    }

}
