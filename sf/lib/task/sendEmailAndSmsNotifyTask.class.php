<?php

/**
 * Таск отправки смс и email напоминаний о запланированных операциях
 *
 */
class sendEmailAndSmsNotifyTask extends sfBaseTask
{
   #Max: все что можно положить в конфиги с учетом env

    /**
     * Флаг продакшн версии
     *
     * @var boolean
     */
    private $prod;
    private $tmpFile;

    // Id услуги нотификации
    const NOTIFICATION_SERVICE_ID = 1;

    // Типы оповещений
    const TYPE_SMS          = 0;
    const TYPE_EMAIL        = 1;

    // Количество неудачных попыток отправки
    const MAX_FAILS         = 4;

    /**
     * Константы для сервиса отправки SMS
     *
     */
    const WEBSMS_URL        = 'http://www.websms.ru/http_in5.asp?'; // Адрес сервиса отправки SMS
    const WEBSMS_LOGIN      = 'easyfinance';                        // Имя пользователя для WebSMS
    const WEBSMS_PASSWORD   = 'WGj163klSQ!@^&*SAds';                // Пароль для WebSMS

    // Адрес отправителя Email
    const EMAIL_FROM        = 'reminder@easyfinance.ru';
    const EMAIL_NAME        = 'EasyFinance Reminder';

    # Svel: выкинуть это к чертям, кто обнаружит и не поленится
    // Временный файл для тестов
    const TMP_FILENAME      = 'sendEmailAndSmsNotifyTask_test.tmp';


    /**
     * Конфигурация
     */
    public function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
        ));

        $this->namespace = 'cron';
        $this->name      = 'notify-old';

        $this->briefDescription    = 'SMS and Email notification service';
        $this->detailedDescription =
            "SMS and Email notification service" . PHP_EOL . PHP_EOL
          . "STDIN:" . PHP_EOL
          . "[./symfony cron:notify]" . PHP_EOL . PHP_EOL
          . "From file:" . PHP_EOL
          . "[./symfony cron:notify]";

        $this->tmpFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . self::TMP_FILENAME;
    }


    /**
     * Запуск задачи
     *
     * @param array $arguments
     * @param array $options
     * @return int количество отправленых нотификаций
     */
    public function execute($arguments = array(), $options = array())
    {
        $count = 0;
        $this->prod = ($options['env'] == 'prod');

        // Инициализировать соединение с БД
        $databaseManager = new sfDatabaseManager($this->configuration);

        // Выбираем все неотправленные оповещения чья дата <= текущей
        # Svel: связывать с операциями и пользователями, ибо нефик
        $notifications = Doctrine::getTable("OperationNotification")
            ->getUnsentNotifications()
            ->execute();

        // Выбираем те оповещения, для которых у пользователя оплачена услуга
        foreach ($notifications as $notification) {
            # Svel: FIXME как-то не кошерно, сколько ж тут запросов будет
            $operation = $notification->getOperation();
            $user = $operation->getUser();

            // Проверяем наличие подписки на услугу
            $subscription = Doctrine::getTable('ServiceSubscription')->getActiveUserServiceSubscription($user->getId(), self::NOTIFICATION_SERVICE_ID);

            // Если имеется подписка на услугу, можно продолжать
            if (is_object($subscription) && ($subscription instanceof ServiceSubscription)) {

                // Отправка SMS оповещения
                if ($notification->getType() == self::TYPE_SMS) {
                    // В телефоне оставляем только цифры, впереди семерка
                    $smsPhone = preg_replace('/[^\d]/','', $user->getSmsPhone());
                    if (strlen($smsPhone) == 10) {
                        $smsPhone = '7' . $smsPhone;
                    } else if ((strlen($smsPhone) == 11) && ($smsPhone[0] == 8)) {
                        $smsPhone[0] = '7';
                    }

                    if ($this->sendSMS($smsPhone, $operation)) {
                        $notification->setIsSent(true);
                        $notification->setIsDone(true);
                        $count++;
                    } else {
                        $failsCounter = $notification->getFailCounter();
                        $notification->setFailCounter($failsCounter++);
                        // Если количество ошибок привысило максимально допустимое, завершаем с этим оповещением
                        if ($failsCounter > self::MAX_FAILS) {
                            $notification->setIsDone(true);
                        }
                    }
                    $notification->save();
                }

                // Отправка Email оповещения
                # Max: Невозможно отправить SMS и Email одновременно
                if ($notification->getType() == self::TYPE_EMAIL) {
                    $this->sendEmail($user->getUserMail(), $operation);
                    $notification->setIsSent(true);
                    $notification->setIsDone(true);
                    $notification->save();
                    $count++;
                }

            } else {
                // Услуга не активна
                // Просто помечаем уведомление как выполненное
                # Svel: TODO так и было в ТЗ? грязный вариант
                $notification->setIsDone(true);
                $notification->save();
            }
        }

        return $count;
    }


    /**
     * Сообщение вида:
     * ДД.ММ,8888888888 ВАЛ,КАТЕГОРИЯ_15_СИМВ,СЧЕТ_15_СИМВ,КОММЕНТ_15_СИМВ
     *
     * @param Operation $operation
     * @return string
     */
    private function _getShortMessage( $operation )
    {
        return date("d.m", strtotime( $operation->getDate())) . "," .
            mb_substr( strip_tags( $operation->getCategory()->getCatName() ), 0, 15, 'UTF8' ) . "," .
            abs( $operation->getMoney() ) . " " .
            $operation->getAccount()->getCurrency()->getCurCharCode() . "," .
            mb_substr(strip_tags( $operation->getAccount()->getAccountName() ), 0, 15, 'UTF8') ."," .
            mb_substr(strip_tags( $operation->getComment() ), 0, 15, 'UTF8');
    }


    /**
     * Полное сообщение с описанием операции (для email)
     *
     * @param Operation $operation
     * @return string
     */
     # Svel: FIXME а почему генерация без шаблонов для писем?
    private function _getFullMessage($operation)
    {
        return
            "Дата: " . date("d.m.Y", strtotime( $operation->getDate())) . "\n" .
            "Категория: " . strip_tags( $operation->getCategory()->getCatName() ) . "\n" .
            "Счет: " . strip_tags( $operation->getAccount()->getAccountName() ) ."\n" .
            "Сумма: " . abs( $operation->getMoney() ) . " " .
            $operation->getAccount()->getCurrency()->getCurCharCode() . "\n" .
            "Комментарий: " . strip_tags( $operation->getComment() );
    }


    /**
     * Записывает лог
     *
     * @param string $message
     * @param string $input raw mail data
     * @return void

    Max: использовать симфоневскую систему логгирования
     */
    private function logging($message, $input)
    {
        // Путь к файлу с логами
        $logPath = sfConfig::get('sf_root_dir') . '/log/notifications.'.date('Y-m-d-H-i-s-u').'.log';

        file_put_contents($logPath, 'Error: ' . $message . "\n----\n\n" . $input);
        //$this->logSection('import', 'Error: ' . $message, null, 'ERROR');
    }


    /**
     * Отправить SMS через службу websms
     * @param string $phoneNumber номер телефона
     * @param string $msg сообщение
     * @return bool
     */
    private function sendSMS($phoneNumber, $operation)
    {
        // Параметры запроса
        $params = array();

        // Имя пользователя и пароль для доступа к сервису
        $params[] = 'http_username=' . self::WEBSMS_LOGIN;
        $params[] = 'http_password=' . urlencode(self::WEBSMS_PASSWORD);

        // Номер телефона без пробелов и спец знаков, ничинается с 7 (79019551234)
        # Svel: TODO проверить валидатор на номер телефона
        $params[] = 'phone_list=' . $phoneNumber;
        // Текст сообщения должен быть в кодировке 1251
        # Svel: как 1251, а утф в смсках не пошлешь? (
        $params[] = 'message=' . urlencode(iconv('utf-8', 'windows-1251', $this->_getShortMessage($operation)));
        // Сервис вернет ответ в виде обычного текса, альтернатива - xml
        # Svel: имхо в xml'ках надо, и запросы в xml'ках
        $params[] = 'format=text';
        // От кого SMS
        $params[] = 'fromPhone=EasyFinance';
        // Резать длинную смс на части
        $params[] = 'nosplit=0';
        // Флаг тестового режима
        # Svel: FIXME kill this
        $params[] = 'test=' . ((!$this->prod) ? '1' : '0');

        $url = self::WEBSMS_URL . implode('&', $params);

        // Обращаемся к сервису
        $result = file_get_contents($url);

        // Проверим результат
        $matches = array();
        preg_match("/error_num=(.+)/im", $result, $matches);
        if (isset($matches[1]) && trim($matches[1]) == 'OK') {
            // Все в порядке, смс отправлена
            return true;
        } else {
            // Ошибка при отправке SMS
            $this->logging("Отправка СМС не удалась", $url . "\n\n" . $result);
        }

        return false;
    }


    /**
     * Отправить email сообщение
     *
     * @param string $email адрес на который отправлять
     * @param string $msg текст сообщения
     * @return boolean
     */
    public function sendEmail($email, $operation)
    {
        $subject = "Напоминание: " . $this->_getShortMessage($operation);
        $from = array(self::EMAIL_FROM => self::EMAIL_NAME,);

        #Max: не надо так делать. Тестировать надо по-другому. Надо в factories.yml повесить свою заглушку
        if ($this->prod) {
            // На продакшне отправляем письмо
            $result = $this->getMailer()->composeAndSend($from, $email, $subject, $this->_getFullMessage($operation));
        } else {
            // Иначе отправляем текст email сообщения в файл
            $message = $this->getMailer()->compose($from, $email, $subject, $this->_getFullMessage($operation));
            file_put_contents($this->tmpFile, (string) $message);
        }

        return true;
    }
}
