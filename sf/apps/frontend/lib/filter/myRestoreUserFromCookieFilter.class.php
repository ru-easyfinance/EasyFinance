<?php
/**
 * Вспоминает пользователя по cookie, установленной сз старого кода.
 *
 * Этот фильтр нужно вставить в файл filters.yml **выше**
 * фильтра security, вот так:
 *
 *    restore_user:
 *      class: myRestoreUserFromCookieFilter
 *
 *    security: ~
 */
class myRestoreUserFromCookieFilter extends sfFilter
{
    /**
     * Filter chain
     */
    public function execute($filterChain)
    {
        $cookieName = COOKIE_NAME;

        if (
            $this->isFirstCall()
            && !$this->context->getUser()->isAuthenticated()
            && $cookie = $this->context->getRequest()->getCookie($cookieName)
        ) {
            list($login, $password) = $this->decrypt($cookie);

            $user = Doctrine::getTable('User')
                ->findOneByLoginAndPassword($login, $password);

            if ($user) {
                $this->context->getUser()->signIn($user);
            }
        }

        $filterChain->execute();
    }

    /**
     * Расшифровывает данные с помощью расширения mcrypt
     * @param string $text Текст, который требуется разшифровать
     * @param string $key // 24 битный ключ
     * @return string Расшифрованную строку
     */
    protected function decrypt($text, $key = CRYPT_KEY)
    {
        $text = base64_decode($text);
        $iv = substr(md5($key), 0, mcrypt_get_iv_size(MCRYPT_CAST_256, MCRYPT_MODE_CFB));
        $p_t = mcrypt_cfb (MCRYPT_CAST_256, $key, $text, MCRYPT_DECRYPT, $iv);
        return unserialize(trim($p_t));
    }
}
?>
