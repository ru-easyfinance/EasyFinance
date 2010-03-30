<?php if ( !defined( 'INDEX' ) ) trigger_error( "Index required!", E_USER_WARNING );

    /**
     * Класс контроллера для модуля счетов пользователя
     * @copyright http://easyfinance.ru/
     * @version SVN $Id$
     */
    class Api_In_Btaintegrate_Controller extends _Core_Controller
    {

        /**
         * 1 - успешно,
         */
        const SUCCESS = 1;

        /**
         * 2 - ошибка: нет такого пользователя;
         */
        const ERROR_USER_NOT_FOUND = 2;

        /**
         * 3 - ошибка в данных;
         */
        const ERROR_VALIDATE_DATA = 3;

        /**
         * 4 - другая ошибка, нужно повторить,
         */
        const ERROR_UNKNOWN = 4;

        /**
         * 5 - операция с таким id уже существует
         */
        const ERROR_ID_NOT_UNIQUE = 5;

        /**
         * Конструктор класса
         * @return void
         */
        function __init ()
        {
            
        }

        /**
         * Индексная страница
         * @param $args array mixed
         * @return void
         */
        function index ()
        {

            header('Content-type: text/xml');

            switch ( ( int ) @$_GET['status'] ) {

                case self::ERROR_USER_NOT_FOUND : {
                    die('<?xml version="1.0" encoding="UTF-8"?>'
                        . '<result><status>ERROR</status><error><code>2</code>'
                        . '<message>User not found</message></error></result>');
                }

                case self::ERROR_VALIDATE_DATA : {
                    die('<?xml version="1.0" encoding="UTF-8"?>'
                        . '<result><status>ERROR</status><error><code>3</code>'
                        . '<message>Error validation data</message></error></result>');
                }

                case self::ERROR_UNKNOWN : {
                    die('<?xml version="1.0" encoding="UTF-8"?>'
                        . '<result><status>ERROR</status><error><code>4</code>'
                        . '<message>Another error, please repeat the request</message></error></result>');
                }

                case self::ERROR_ID_NOT_UNIQUE : {
                    die('<?xml version="1.0" encoding="UTF-8"?>'
                        . '<result><status>ERROR</status><error><code>5</code>'
                        . '<message>No unique ID</message></error></result>');
                }

                case self::SUCCESS : {
                    // Идём дальше
                }

                default: {
                    die('<?xml version="1.0" encoding="UTF-8"?>'
                        . '<result><status>OK</status></result>');
                }
            }

//    * source - AMT код источника;
//    * id - уникальный id в пределах источника
//    * email - email пользователя, указанный для интеграции
//    * type - тип операции: 0 - снятие, 1 - пополнение
//    * account - краткий номер счета/карты (последние цифры аналогично email-выпискам)
//    * timestamp - дата и время совершения операции ISO-8601 (example: 2005-08-15T15:52:01+0000)
//    * amount - количество денег в операции (float) с точкой
//    * description - описание операции (Снятие наличных/Платеж/Перевод на счет <реквизиты счета>/ другие возможные значения)
//    * place - Банкомат <адрес или хотя бы код> /Офис банка <адрес> /Магазин <код> /другие возможные с ключевыми словами
//    * balance - (float) текущий баланс по счету/карте (пока не используется в нашей логике, но в комментарии хорошо бы его видеть)
        }

    }