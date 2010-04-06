<?php

/**
 * Таск для импорта операций из AMT Банка по email
 */
class apiImportOperationFromAmtEmailTask extends sfBaseTask
{
    /**
     * Конфиг
     */
    public function configure()
    {
        $this->namespace = 'api';
        $this->name      = 'import-amt-email';

        $this->briefDescription    = 'Import operation from AMT Bank email';
        $this->detailedDescription =
            "Import operation from AMT Bank email" . PHP_EOL . PHP_EOL
          . "Usage (accepts STDIN only):" . PHP_EOL
          . "[./symfony api:import-amt-email < email.eml|INFO]" . PHP_EOL;
    }


    /**
     * Run
     */
    public function execute($arguments = array(), $options = array())
    {
        // STDIN
        $input = '';
        while($row = fgets(STDIN)) {
            $input .= $row;
        }
        $input = trim($input);
        if (!$input) {
            $this->logSection('api', 'Error: Expected STDIN data', null, 'ERROR');
            return 1;
        }


        // Парсинг
        $operationData = array();


        // Инициализировать соединение с БД
        $databaseManager = new sfDatabaseManager($this->configuration);
        // Форма/Сохранить операцию
        $form = new OperationImportAmtForm;
        $form->bind($operationData);
        if ($form->isValid()) {
            $form->save();
        } else {
            $this->logSection('api', 'Error: '.$form->getErrorSchema(), null, 'ERROR');
            return 2;
        }


        $this->logSection('api', 'Done');
        return 0;
    }
}
