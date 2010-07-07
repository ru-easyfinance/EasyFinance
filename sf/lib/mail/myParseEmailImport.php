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
     * @param string $mail текст письма
     * @param EmailParser $parser парсер
     * @param string $to email получателя
     */
    public function __construct ( $mail, EmailParser $parser, $to )
    {
        $this->_parser = $parser;
        $this->_mail = $mail;
        $this->_to = $to;
    }


    /**
     * Распарсить email и получить массив данных
     *
     * @param mixed $forced_operation_id если установлен - id операции устанавливаемый в ручную
     * @return array | int в случае ошибки
     */
    public function getData( $forced_operation_id=false )
    {
        // Последние 4 цифры карты
        preg_match( '/' . $this->_parser->getAccountRegexp() . '/im', $this->_mail, $matches );
        $lastFourDigits = ( isset( $matches[1] ) ) ? $matches[1] : false;

        // Сумма платежа
        preg_match( '/' . $this->_parser->getTotalRegexp() . '/im', $this->_mail, $matches );
        $amount = ( isset( $matches[1] ) ) ? $matches[1] : false;

        // Вычищаем левые символы в сумме
        $amount = preg_replace( "/[^\d\.]+/", "", $amount);

        // Детали операции
        preg_match( '/' . $this->_parser->getDescriptionRegexp() . '/im', $this->_mail, $matches );

        if ( isset( $matches[0] ) ) unset( $matches[0] );
        $description = ( count( $matches ) ) ? implode( " ", $matches ) : "";

        // Направление движения средств (тип)
        $type = $this->_parser->getType() ? Operation::TYPE_PROFIT : Operation::TYPE_EXPENSE;

        $operationData = array(
            'email'         => $this->_to,
            'amount'        => (string)$amount,
            'description'   => trim( $description ),
            'type'          => (string)$type,
            'account'       => (string)$lastFourDigits,
            // Источник, что-то наподобие citi1234 для привязки источника и 4-х последних знаков карты к счету
            'source'        => substr( $this->_parser->getEmailSource()->getName(), 0, 4) . $lastFourDigits,
            'id'            => ( $forced_operation_id ) ? $forced_operation_id : uniqid()
        );

        return $operationData;
    }


    /**
     * Статический метод производящий декодировку письма
     *
     * @param string $input полный оригинальный текст email с заголовками
     * @return array либо false, в случае неверного формата входящих данных (отсутствует заголовок или часть заголовка)
     */
    public static function getEmailData( $input )
    {
        $zPath = sfConfig::get('sf_root_dir') . '/lib/vendor';
        set_include_path($zPath . PATH_SEPARATOR . get_include_path());

        $message = new Zend_Mail_Message(array('raw' => $input));

        $headers = $message->getHeaders();
        if ( !count( $headers ) ) return false;
        if ( !isset( $headers['subject'] ) ||
             !isset( $headers['from'] ) ||
             !isset( $headers['to'] )
        ) return false;

        if ( isset( $headers['content-transfer-encoding'] ) && ( $headers['content-transfer-encoding'] == "quoted-printable" ) )
        {
            $subject = iconv_mime_decode($headers['subject'], 0, 'UTF-8');
            $body = quoted_printable_decode( $message->getContent() );
        }
        else
        {
            $subject = $headers['subject'];
            $body = $message->getContent();
        }

        $data = array(
            'from' => $headers['from'],
            'to' => $headers['to'],
            'subject' => $subject,
            'body' => $body
        );
        return $data;
    }

}
