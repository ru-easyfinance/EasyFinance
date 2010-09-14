<?php

/**
 * Задача импорта операций из email-нотификаторов
 */
class importOperationFromEmailTask extends sfBaseTask
{
    /**
     * Возвращаемые коды
     */
    const OK                       = 0;    // Без ошибок
    const ERROR_INVALID_INPUT      = 1;    // Невозможно прочитать входящие данные
    const ERROR_EMPTY_INPUT        = 2;    // Пусто на входе
    const ERROR_EMAIL_FORMAT       = 3;    // Неверный формат .eml файла
    const ERROR_UNKNOWN_SENDER     = 4;    // Неизвестный отправитель
    const ERROR_NO_PARSER          = 5;    // Невозможно подобрать подходящий парсер
    const ERROR_PARSER_EXCEPTION   = 6;    // Исключение во время парсинга
    const ERROR_IMPORT_OPERATION   = 7;    // Исключение во время переноса данных в операцию

    /**
     * Емейл отправителя AMT
     */
    const AMT_SOURCE_EMAIL = "card.statement@amtbank.com";


    /**
     * Конфиг
     */
    public function configure()
    {
        $this->addOptions(array(
           new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
        ));

        $this->addArguments(array(
           new sfCommandArgument('file', null, sfCommandArgument::OPTIONAL, null, 'Input file'),
        ));

        $this->namespace = 'import';
        $this->name      = 'parse-email';

        $this->briefDescription    = 'Import operation from email';
        $this->detailedDescription =
            "Import operation from email" . PHP_EOL . PHP_EOL
          . "STDIN:" . PHP_EOL
          . "[./symfony import:parse-email < email.eml|INFO]" . PHP_EOL . PHP_EOL
          . "From file:" . PHP_EOL
          . "[./symfony import:parse-email email.eml|INFO]";
    }


    /**
     * Run
     */
    public function execute($arguments = array(), $options = array())
    {
        // From file
        if ($arguments['file']) {
            if (!is_readable($arguments['file'])) {
                $this->logSection('import', "Failed to read file `{$arguments['file']}`", null, 'ERROR');
                return self::ERROR_INVALID_INPUT;
            }
            $input = file_get_contents($arguments['file']);

        // STDIN
        } else {
            $input = '';
            while ($row = fgets(STDIN)) {
                $input .= $row;
            }
            $input = trim($input);
        }

        if (!$input) {
            $this->logging("Expected not empty input", $input);
            $this->logMessage(
                null,
                'Нет данных на входе',
                'При парсинге входящего письма не получили данных.'
            );
            return self::ERROR_EMPTY_INPUT;
        }

        $mail = myParseEmailImport::getEmailData($input);
        if (false == $mail) {
            $this->logging("Not a valid .eml file format", $input);
            $this->logMessage(
                null,
                'Письмо не может быть распознано',
                'Входящее письмо не может быть распознано, т.к. формат не соответствует стандарту.',
                $input
            );
            return self::ERROR_EMAIL_FORMAT;
        }

        $from    = $mail['from'];
        $subject = $mail['subject'];

        // Инициализировать соединение с БД
        $databaseManager = new sfDatabaseManager($this->configuration);

        if ($from == self::AMT_SOURCE_EMAIL) {
            // Для парсинга AMT
            $importClass = "myParseEmailAmtImport";
            $importForm = "OperationImportAmtForm";
            $parser = null;
        } else {
            $importClass = "myParseEmailImport";
            $importForm = "OperationImportForm";

            // Получаем отправителя
            $source = Doctrine_Core::getTable("EmailSource")->getByEmail($from);

            if (false === $source) {
                $this->logging("Unknown sender", $from);
                $this->logMessage(
                    null,
                    'Неизвестный отправитель',
                    'В нашей БД отсутствует отправитель (БАНК).',
                    sprintf("Отправлено от: %s\nОтправлено для: %s\nТема письма: %s\nТело письма:\n%s", $from, $mail['to'], $mail['subject'], $mail['body'])
                );
                $this->forwardMail($mail['to'], $mail['subject'], $mail['body']);
                return self::ERROR_UNKNOWN_SENDER;
            }

            // Ищем подходящий парсер
            $parser = $source->getParserBySubject($subject);

            if (!is_object($parser) || !($parser instanceof EmailParser)) {
                $this->logging("Can't find any suitable parser for subject", $subject);
                $this->logMessage(
                    null,
                    'Нет парсера для обработки письма',
                    'Невозможно подобрать парсер, необходимый для обработки письма, по его теме.',
                    sprintf("Отправлено от: %s\nОтправлено для: %s\nТема письма: %s\nТело письма:\n%s", $from, $mail['to'], $mail['subject'], $mail['body'])
                );
                $this->forwardMail($mail['to'], $mail['subject'], $mail['body']);
                return self::ERROR_NO_PARSER;
            }

            $input = $mail['body'];
        }

        // Парсинг данных
        try {
            $getEmail = new $importClass($input, $parser, $mail['to']);
            $operationData = $getEmail->getData();
        } catch (Exception $e) {
            $this->logging($e->getMessage(), $input);
            $this->logMessage(
                null,
                'Парсер не смог обработать письмо',
                sprintf('Обработка данных письма вызвала ошибку "%s" при обработке тела письма', $e->getMessage()),
                sprintf("Отправлено для: %s\nТело письма:\n%s", $mail['to'], $input)
            );
            return self::ERROR_PARSER_EXCEPTION;
        }

        // Форма/Сохранить операцию
        $form = new $importForm;
        $form->bind($operationData);
        if ($form->isValid()) {
            $form->save();
        } else {
            $this->logging($form->getErrorSchema(), $input);
            $this->logMessage(
                null,
                'Сохранение операции',
                sprintf("Попытка сохранения операции не удалась.\nВозникшие ошибки при валидации данных: %s", $form->getErrorSchema()),
                sprintf("Отправлено для: %s\nТело письма:\n%s", $mail['to'], $input)
            );
            return self::ERROR_IMPORT_OPERATION;
        }

        $this->logMessage(
            'info',
            'Сохранение операции',
            sprintf('Операция успешно создана.'),
            sprintf("Отправлено для: %s\nТело письма:\n%s", $mail['to'], $input),
            null,
            $form->getObject()->getId()
        );
        $this->logSection('import', 'Done');
        return self::OK;
    }


    /**
     * Записывает лог
     *
     * @param string $message
     * @param string $input raw mail data
     * @return void
     */
    private function logging ($message, $input)
    {
        // Путь к файлу с логами
        $logPath = sfConfig::get('sf_root_dir') . '/log/parse_mail.'.date('Y-m-d-H-i-s-u').'.log';

        file_put_contents($logPath, 'Error: ' . $message . "\n----\n\n" . $input);
        $this->logSection('import', 'Error: ' . $message, null, 'ERROR');
    }


    /**
     * Переслать письмо пользователю
     *
     * @param   string  $to
     * @param   string  $subject
     * @param   string  $body
     */
    private function forwardMail($to, $subject, $body)
    {
        if ($user = Doctrine::getTable('User')->findOneByUserServiceMail($to)) {
            // Надо подключить конфиг
            $configuration = $this->createConfiguration('frontend', 'prod');
            // и инициализировать контекст
            $context = sfContext::createInstance($configuration);
            $configuration->loadHelpers('Partial');

            $context->getRequest()->setRequestFormat('txt');

            // формируем письмо
            $message = Swift_Message::newInstance()
                ->setFrom(array(sfConfig::get('app_emailImport_from') => sfConfig::get('app_emailImport_fromName')))
                ->setSender(sfConfig::get('app_emailImport_from'))
                ->setReplyTo(sfConfig::get('app_emailImport_from'))
                ->setTo(array($user->getUserMail() => $user->getName()))
                ->setSubject(sfConfig::get('app_emailImport_subject') . $subject)
                ->setBody(get_partial('global/mail/importOperation', array('serviceMail' => $to, 'body' => $body)), 'text/plain');

            // посылаем
            $this->getMailer()->sendNextImmediately()->send($message);

            $this->logMessage(
                'notice',
                'Пересылка нераспознанного письма',
                'Т.к. парсер не смог обработать письмо - мы переслали его реальному пользователю.',
                sprintf("Оригинальное письмо:\n\nОтправлено для: %s\nТема письма: %s\nТело письма:\n%s", $to, $subject, $body),
                $user
            );
        // не нашли пользователя по сервисному мылу
        } else {
            $this->logMessage(
                null,
                'Неизвестный получатель',
                'Пользователь не может быть идентифицирован по e-mail для интеграции.',
                sprintf("Отправлено для: %s\nТема письма: %s\nТело письма:\n%s", $to, $subject, $body)
            );
            $this->logging('No such User identified by Service Email', $to);
        }
    }


    /**
     * Сообщить о событии слушателям
     *
     * @see     myDoctrineLoggerPlugin
     * @param   string  $level          Уровень ошибки
     * @param   string  $name           Название события
     * @param   string  $description    Описание (расшифровка) события
     * @param   string  $environment
     * @param   User|integer  $user     Пользователь (идентификатор)
     * @param   integer $object         Идентификатор объекта
     */
    protected function logMessage($level, $name, $description, $environment = '', $user = null, $object = null)
    {
        $this->dispatcher->notify(new sfEvent('Task "importOperationFromEmail"', 'app.activity', array(
            'state'       => ($level ? $level : 'fail'),
            'name'        => $name,
            'description' => $description,
            'component'   => 'OperationEmailParser',
            'env'         => (string) $environment,
            'user'        => $user,
            'object'      => $object,
        )));
    }

}
