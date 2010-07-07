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
          . "[./symfony import:email < email.eml|INFO]" . PHP_EOL . PHP_EOL
          . "From file:" . PHP_EOL
          . "[./symfony import:email email.eml|INFO]";
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

        if ( !$input )
        {
            $this->logging("Expected not empty input", $input);
            return self::ERROR_EMPTY_INPUT;
        }

        $mail = myParseEmailImport::getEmailData($input);
        if ( false == $mail ) {
            $this->logging("Not a valid .eml file format", $input);
            return self::ERROR_EMAIL_FORMAT;
        }

        $from = $mail['from'];
        $subject = $mail['subject'];

        // Инициализировать соединение с БД
        $databaseManager = new sfDatabaseManager($this->configuration);

        if ( $from == self::AMT_SOURCE_EMAIL )
        {
            // Для парсинга AMT
            $importClass = "myParseEmailAmtImport";
            $importForm = "OperationImportAmtForm";
            $parser = null;
        }
        else
        {
            $importClass = "myParseEmailImport";
            $importForm = "OperationImportForm";

            // Получаем отправителя
            $source = EmailSourceTable::getInstance()->getByEmail( $from );

            if ( false === $source )
            {
                $this->logging("Unknown sender", $from);
                return self::ERROR_UNKNOWN_SENDER;
            }

            // Ищем подходящий парсер
            $parser = $source->getParserBySubject( $subject );

            if ( !is_object( $parser ) || !( $parser instanceof EmailParser ) )
            {
                $this->logging("Can't find any suitable parser for subject", $subject);
                return self::ERROR_NO_PARSER;
            }

            $input = $mail['body'];
        }

        // Парсинг данных
        try {
            $getEmail = new $importClass( $input, $parser, $mail['to'] );
            $operationData = $getEmail->getData();
        } catch (Exception $e) {
            $this->logging($e->getMessage(), $input);
            return self::ERROR_PARSER_EXCEPTION;
        }

        // Форма/Сохранить операцию
        $form = new $importForm;
        $form->bind($operationData);
        if ($form->isValid()) {
            $form->save();
        } else {
            $this->logging($form->getErrorSchema(), $input);
            return self::ERROR_IMPORT_OPERATION;
        }

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
}
