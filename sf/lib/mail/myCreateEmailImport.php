<?php

/**
 * Класс для создания тестового письма для импорта операции
 */
class myCreateEmailImport
{
    /**
     * Сообщение
     * @var Swift_Message
     */
    private $_message = null;

    /**
     * Массив с параметрами
     * @var array
     */
    private $_args = array();


    /**
     * Конструктор
     *
     * @param array $args - массив параметров, которые передает банк
     */
    function __construct(array $args)
    {
        $this->_message = Swift_Message::newInstance();
        $this->_args    = $args;

        // Проверяем на ошибки, и если есть, то кидаем стандартное исключение
        $this->_validateArgs($args);

        // Устанавливаем шаблон для сообщения
        $this->_setMessage();
    }


    /**
     * Проверяет пришедший массив на валидность
     *
     * @param array $args - массив параметров, которые передает банк
     * @return bool
     */
    private function _validateArgs(array $args)
    {
        $expected = array(
            'email'=>'',
            'from' => '',
            'subject' => '',
            'body' => ''
        );

        $diff = array_diff_key($expected, $args);

        if (count($diff) > 0) {
            throw new Exception('Expected: '. implode(',', $diff), 3);
        }
    }


    /**
     * Создаём основу сообщения
     */
    private function _setMessage()
    {
    	$to   = $this->_args['email'];
    	$from = $this->_args['from'];
    	$subj = $this->_args['subject'];
        $body = $this->_args['body'];

        $this->_message
            ->setSubject($subj)
            ->setFrom($from)
            ->setTo($to)
            ->setBody($body, 'text/plain');
    }


    /**
     * Raw email
     */
    function  __toString()
    {
        return $this->_message->toString();
    }
}
