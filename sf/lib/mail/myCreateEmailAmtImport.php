<?php

/**
 * Класс для создания тестового письма для импорта операции из AMT банка
 */
class myCreateEmailAmtImport
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
     * Шаблон XML
     * @var string xml
     */
    private $_xml = null;


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
        // Устанавливаем шаблон для XML части
        $this->_setXml();
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
            'id'=>'',
            'email'=>'',
            'type'=>'',
            'account'=>'',
            'timestamp'=>'',
            'amount'=>'',
            'description'=>'',
            'place'=>'',
            'balance'=>'',
        );

        $diff = array_diff_key($expected, $args);

        if (count($diff) > 0) {
            throw new Exception('Expected: '. implode(',', $diff), 3);
        }
    }


    /**
     * Создаём XML с параметрами
     */
    private function _setXml()
    {
        $this->_xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n".
           "<message>".
                "<id>{$this->_args['id']}</id>".
                "<email>{$this->_args['email']}</email>".
                "<type>{$this->_args['type']}</type>".
                "<account>{$this->_args['account']}</account>".
                "<timestamp>{$this->_args['timestamp']}</timestamp>".
                "<amount>{$this->_args['amount']}</amount>".
                (isset($this->_args['payment']) ?
                     "<payment>{$this->_args['payment']}</payment>" : "").
                "<description>{$this->_args['description']}</description>".
                "<place>{$this->_args['place']}</place>".
                "<balance>{$this->_args['balance']}</balance>".
            "</message>";
    }


    /**
     * Создаём основу сообщения
     */
    private function _setMessage()
    {
        if ( (int)$this->_args['type'] === 0 ) {
            $subj = 'АМТ банк: списание средств';
            $body = "АМТ банк: Списание средств: {$this->_args['amount']};";
        } else {
            $subj = 'АМТ банк: внесение средств';
            $body = "АМТ банк: Внесение средств: {$this->_args['amount']};";
        }

        $body .= " со счёта: {$this->_args['account']};\n";

        if (isset($this->_args['payment'])) {
            $body .= "на сумму: {$this->_args['payment']};\n";
        }
        
        $body .= "операция: {$this->_args['place']}; дата: ".
                    $this->_args['timestamp'] .";\n".
                "доступный остаток: {$this->_args['balance']};";

        $this->_message->setSubject($subj)
            ->setTo('some.user@mail.easyfinance.ru')
            ->setBody($body, 'text/plain');
    }


    /**
     * Создаём почту, используя метод addPart
     */
    function useAddPart()
    {
        $this->_message->addPart($this->_xml, 'text/xml');
    }


    /**
     * Создаём почту, используя вложения в письма
     */
    function useAttachment()
    {
        $attachment = Swift_Attachment::newInstance($this->_xml, 'send.xml', 'text/xml');
        $this->_message->attach($attachment);
    }


    /**
     * Raw email
     */
    function  __toString()
    {
        return $this->_message->toString();
    }

}
