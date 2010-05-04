<?php

/**
 * Заглушка для исходящих писем
 *
 * Всегда возвращает положительный результат, если не указаны исключения
 *
 * @author Max <maxim.olenik@gmail.com>
 */
class TestMailInvokerStub implements Swift_Transport_MailInvoker
{
    /**
     * Список email'ов на которые должен вернуть ошибку
     */
    private $_errors = array();


    /**
     * Установить список ошибочных email'ов
     *
     * @param array $email
     * @return void
     */
    public function setErrors(array $emails)
    {
        $this->_errors = $emails;
    }


    /**
     * Является ли полученный email ошибочным
     *
     * @param string $email
     * @return bool
     */
    public function shouldFail($email)
    {
        if (is_array($email)) {
            return (bool) array_intersect($this->_errors, $email);
        } else {
            return in_array($email, $this->_errors);
        }
    }


    /**
     * Получить email из строки заголовка
     *
     * @param sting $header
     * @return string
     */
    public function extractEmail($header)
    {
        if (preg_match_all('/([a-z0-9\-\._]+@[a-z0-9\-\.]+)/i', $header, $matches)) {
            return $matches[1];
        }
        return array();
    }


    /**
     * Отправить письмо
     */
    public function mail($to, $subject, $body, $headers = null, $extraParams = null)
    {
        return !($this->shouldFail($this->extractEmail($to)));
    }

}
