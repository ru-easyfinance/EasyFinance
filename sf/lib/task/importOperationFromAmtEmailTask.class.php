<?php

/**
 * Таск для импорта операций из AMT Банка по email
 */
class importOperationFromAmtEmailTask extends sfBaseTask
{
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
        $this->name      = 'amt-email';

        $this->briefDescription    = 'Import operation from AMT Bank email';
        $this->detailedDescription =
            "Import operation from AMT Bank email" . PHP_EOL . PHP_EOL
          . "STDIN:" . PHP_EOL
          . "[./symfony import:amt-email < email.eml|INFO]" . PHP_EOL . PHP_EOL
          . "From file:" . PHP_EOL
          . "[./symfony import:amt-email email.eml|INFO]";
    }


    /**
     * Run
     */
    public function execute($arguments = array(), $options = array())
    {
        // From file
        if ($arguments['file']) {
            if (!is_readable($arguments['file'])) {
                $this->logSection('import', "Error: Failed to read file `{$arguments['file']}`", null, 'ERROR');
                return 1;
            }
            $input = file_get_contents($arguments['file']);

        // STDIN
        } else {
            $input = '';
            while($row = fgets(STDIN)) {
                $input .= $row;
            }
            $input = trim($input);
        }

        if (!$input) {
            $this->logSection('import', 'Error: Expected not empty input', null, 'ERROR');
            return 1;
        }


        try {
            $getEmail = new myParseEmailAmtImport($input);
            $operationData = $getEmail->getAmtData();
        } catch (Exception $e) {
            $this->logSection('import', $e->getMessage(), null, 'ERROR');
            return 2;
        }


        // Инициализировать соединение с БД
        $databaseManager = new sfDatabaseManager($this->configuration);
        // Форма/Сохранить операцию
        $form = new OperationImportAmtForm;
        $form->bind($operationData);
        if ($form->isValid()) {
            $form->save();
        } else {
            $this->logSection('import', 'Error: '.$form->getErrorSchema(), null, 'ERROR');
            return 3;
        }

        $this->logSection('import', 'Done');
        return 0;
    }

}
