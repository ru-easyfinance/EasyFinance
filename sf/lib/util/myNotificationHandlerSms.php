<?php

/**
 * Обработчик уведомлений об обперациях по sms
 */
class myNotificationHandlerSms implements myNotificationHandlerInterface
{
    /**
     * Отправить уведомление
     *
     * @param  OperationNotification $notification
     * @return bool
     */
    public function run(OperationNotification $notification)
    {
        $operation = $notification->getOperation();

        // Получить телефон
        $phone = $this->_getPhone($operation->getUser());
        if (!$phone) {
            return false;
        }

        // Обращаемся к сервису
        $params = $this->_prepareHttpQuery($phone, $operation);
        $result = $this->_sendRequest(sfConfig::get('app_notification_sms_url'), $params);

        // Проверим результат
        $matches = array();
        preg_match("/error_num=(.+)/im", $result, $matches);
        if (!isset($matches[1]) || trim($matches[1]) != 'OK') {
            // Ошибка при отправке SMS
            return false;
        }

        return true;
    }


    /**
     * Получить и подготовить телефон
     * В телефоне оставляем только цифры, впереди семерка
     *
     * @param  User $user
     * @return string
     */
    private function _getPhone(User $user)
    {
        $phone = preg_replace('/[^\d]/','', $user->getSmsPhone());
        if (strlen($phone) == 10) {
            $phone = '7' . $phone;
        } else if ((strlen($phone) == 11) && ($phone[0] == 8)) {
            $phone[0] = '7';
        }
        return $phone;
    }


    /**
     * Отправить запрос в шлюз
     *
     * @param  string $url
     * @param  array  $params
     * @return string - Ответ шлюза
     */
    protected function _sendRequest($url, array $params)
    {
        // Создать контекст и инициализировать POST запрос
        $context = stream_context_create(array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded' . PHP_EOL,
                'content' => $query = http_build_query($params),
            ),
        ));

        // Отправить запрос
        return file_get_contents(
            $url,
            $use_include_path = false,
            $context);
    }


    /**
     * Подготовить массив параметров для запроса к шлюзу
     *
     * @param  string    $phone
     * @param  Operation $operation
     * @return array
     */
    private function _prepareHttpQuery($phone, Operation $operation)
    {
        // Параметры запроса
        $params = array();

        // Имя пользователя и пароль для доступа к сервису
        $params['http_username'] = sfConfig::get('app_notification_sms_login');
        $params['http_password'] = sfConfig::get('app_notification_sms_pass');

        // Номер телефона без пробелов и спец знаков, ничинается с 7 (79019551234)
        $params['phone_list'] = $phone;
        // Текст сообщения должен быть в кодировке 1251 - требование шлюза
        $params['message'] = iconv('utf-8', 'windows-1251', $this->_makeMessage($operation));
        // Сервис вернет ответ в виде обычного текса, альтернатива - xml
        $params['format'] = 'text';
        // От кого SMS
        $params['fromPhone'] = 'EasyFinance';
        // Резать длинную смс на части
        $params['nosplit'] = '0';

        // dry-run
        // $params['test'] = '0';

        return $params;
    }


    /**
     * Сообщение вида:
     * ДД.ММ,8888888888 ВАЛ,КАТЕГОРИЯ_15_СИМВ,СЧЕТ_15_СИМВ,КОММЕНТ_15_СИМВ
     *
     * @param  Operation $operation
     * @return string
     */
    private function _makeMessage($operation)
    {
        // Template
        return date("d.m", strtotime( $operation->getDate())) . "," .
            mb_substr( strip_tags( $operation->getCategory()->getCatName() ), 0, 15, 'UTF8' ) . "," .
            abs( $operation->getMoney() ) . " " .
            $operation->getAccount()->getCurrency()->getCurCharCode() . "," .
            mb_substr(strip_tags( $operation->getAccount()->getAccountName() ), 0, 15, 'UTF8') ."," .
            mb_substr(strip_tags( $operation->getComment() ), 0, 15, 'UTF8');
    }

}
