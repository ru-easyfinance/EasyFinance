<?php

/**
 * Класс для парсинга почты из строки и возврата её содержимого в виде массива
 */
class myParseEmailImport
{
    /**
     * Письмо в виде декодированной строки в формате UTF-8
     * @var string
     */
    private $_mail;

    /**
     * Парсер
     *
     * @var EmailParser
     */
    private $_parser;

    /**
     * Email адрес получателя письма
     *
     * @var string
     */
    private $_to;


    /**
     * Инициализация
     *
     * @param string      $mail   текст письма
     * @param EmailParser $parser парсер
     * @param string      $to     email получателя
     */
    public function __construct ($mail, EmailParser $parser, $to)
    {
        $this->_parser = $parser;
        $this->_mail = $mail;
        $this->_to = $to;
    }


    /**
     * Распарсить email и получить массив данных
     *
     * @param  mixed     $forced_operation_id если установлен - id операции устанавливаемый в ручную
     * @return array|int в случае ошибки
     */
    public function getData($forced_operation_id = false)
    {
        // Последние 4 цифры карты
        preg_match('/' . $this->_parser->getAccountRegexp() . '/im', $this->_mail, $matches);
        $lastFourDigits = (isset($matches[1])) ? $matches[1] : false;

        // Сумма платежа
        preg_match('/' . $this->_parser->getTotalRegexp() . '/im', $this->_mail, $matches);
        $amount = (isset($matches[1])) ? $matches[1] : false;

        // Вычищаем левые символы в сумме
        $amount = preg_replace("/[^\d\.]+/", "", $amount);

        // Детали операции
        preg_match('/' . $this->_parser->getDescriptionRegexp() . '/im', $this->_mail, $matches);

        if (isset($matches[0])) {
            unset($matches[0]);
        }

        $description = (count($matches)) ? implode(" ", $matches) : "";

        // Направление движения средств (тип)
        $type = $this->_parser->getType() ? Operation::TYPE_PROFIT : Operation::TYPE_EXPENSE;

        $operationData = array(
            'email'       => $this->_to,
            'amount'      => (string) $amount,
            'description' => trim($description),
            'type'        => (string) $type,
            'account'     => (string) $lastFourDigits,
            // Источник, что-то наподобие citi1234 для привязки источника и 4-х последних знаков карты к счету
            'source'      => substr($this->_parser->getEmailSource()->getName(), 0, 4) . $lastFourDigits,
            'id'          => ($forced_operation_id) ? $forced_operation_id : uniqid(),
        );

        return $operationData;
    }


    /**
     * Статический метод производящий декодировку письма
     *
     * @param  string $input полный оригинальный текст email с заголовками
     * @return array|false, в случае неверного формата входящих данных (отсутствует заголовок или часть заголовка)
     */
    public static function getEmailData($input)
    {
        // подключить ZF autoloader
        ProjectConfiguration::registerZend();

        $message = new Zend_Mail_Message(array('raw' => $input));

        $headers = $message->getHeaders();

        if (!count($headers)) {
            return false;
        }

        if (!isset($headers['subject']) || !isset($headers['from']) || !isset($headers['to'])) {
            return false;
        }

        // выдергиваем текстовое тело письма, если можем
        $body = null;
        if($message->isMultipart()) {
            foreach (new RecursiveIteratorIterator($message) as $part) {
                try {
                    if (strtok($part->contentType, ';') == 'text/plain') {
                        $body = trim($part);
                        $partHeaders = $part->getHeaders();
                        break;
                    }
                } catch (Zend_Mail_Exception $e) {  }
            }

            // в письме нет текстового варианта
            // FIXME как-то по человечески обрабатывать
            if (!$body) {
                throw new Exception();
            }
        } else {
            $body = trim($message->getContent());
            $partHeaders = $message->getHeaders();
        }

        if (isset($partHeaders['content-transfer-encoding'])) {
            switch ($partHeaders['content-transfer-encoding']) {
                // FIXME хз нужно ли нам оно, на локалке не стоит imap_*
                //       м.б. есть альтернативные варианты
                /*
                case '7bit':
                    break;
                case '8bit':
                    $body = quoted_printable_decode(imap_8bit($body));
                    break;
                case 'binary':
                    $body = imap_base64(imap_binary($body));
                    break;
                case 'base64':
                    $body = imap_base64($body);
                    break;
                */
                case 'quoted-printable':
                    $body = quoted_printable_decode($body);
                    break;
                case 'base64':
                    $body = base64_decode($body);
                    break;
            }
        }

        if (isset($partHeaders['content-type']) &&
            preg_match("/^(?:.+?);\scharset=(.+)$/", $partHeaders['content-type'], $matches)) {
                $charset = trim($matches['1']);
                if (!preg_match("/^(utf-8|utf8)$/i", $charset)) {
                    $body = iconv($charset, "UTF-8//IGNORE", $body);
                }
        }

        $data = array(
            'from'    => self::_cleanEmail($headers['from']),
            'to'      => self::_cleanEmail($headers['to']),
            'subject' => $headers['subject'],
            'body'    => $body,
        );

        return $data;
    }


    /**
     * Извлекает email в чистом виде
     * email'ы могут быть: name@domain.zone,
     *                    <name@domain.zone>
     *                имя <name@domain.zone>
     *
     * @param string $email
     * @return string
     */
    protected static function _cleanEmail($email) {
        $email = mb_strtolower($email);

        if (preg_match("/<(.+?@.+?\..{2,5})>/i", $email, $matches)) {
            if (isset($matches[1])) {
                $email = $matches[1];
            }
        }

        return $email;
    }

}
