<?php
require_once "File/CSV/DataSource.php";

/**
 * Импорт из CSV в кошельке
 */
class myCsvImportVkoshelke
{
    /**
     * Поскольку на нижнем уровне мы имеем дело с fgetcsv, нам нужно имя файла
     * @var string
     */
    private $_csvFileName;

    /**
     * Имя файла с расширением .yml, поскольку доктрина принимает фикстуры
     * только с расширением yml
     * @var string
     */
    private $_ymlFileName;

    /**
     * @var File_CSV_DataSource
     */
    private $_csvDataSource;

    /**
     * @var array
     */
    private $_heap = array();


    /**
     * Конструктор
     *
     * @param string $csv данные csv или имя файла
     */
    public function __construct($csv)
    {
        if (!file_exists($csv)) {
            $csvFileName = tempnam(sys_get_temp_dir(), 'php_' . __CLASS__);
            file_put_contents($csvFileName, $csv);
        } else {
            $csvFileName = $csv;
        }

        $this->_csvFileName = $csvFileName;

        $ymlFileName = tempnam(sys_get_temp_dir(), 'php_' . __CLASS__) . '.yml';
        touch($ymlFileName);
        $this->_ymlFileName = $ymlFileName;
    }


    /**
     * Делаем основную работу
     * @return string Имя файла с фикстурой или false
     */
    public function execute()
    {
        $this->_csvDataSource = new File_CSV_DataSource($this->_csvFileName);

        if (!$this->_testStructure())
            return false;

        foreach ($this->_hydrateArrayHash() as $k => $csvOperation) {
            $currency = array(
                'code' => $csvOperation['Currency']
            );

            $account = array(
                'name'     => sprintf(
                    "%s (%s)",
                    $csvOperation['Account'],
                    $csvOperation['Currency']
                ),
                'currency' => $this->_replace(
                    'Currency',
                    $currency['code'],
                    $currency
                )
            );

            $category = !$csvOperation['Category'] ? null : array(
                'name' => $csvOperation['Category'],
                'type' =>
                    ($csvOperation['TransactionType'] == 'Доход' ? 1 : -1)
            );

            switch ($csvOperation['TransactionType']) {
                case 'Доход':
                case 'Расход':
                case 'Перевод списание':
                    $operation = array(
                        'date'     => $csvOperation['ReceiptDate'],
                        'amount'   => $csvOperation['Value'],
                        'comment'  => $csvOperation['Comment'],
                        'Account'  => $this->_replace(
                            'Account',
                            $account['name'],
                            $account
                         ),
                        'Category' => $this->_replace(
                            'Category',
                            $category['name'],
                            $category
                        ),
                    );
                break;
                case 'Перевод зачисление':
                    $operation['transfer_amount'] = $csvOperation['Value'];
                    $operation['TransferAccount'] = $this->_replace(
                        'Account',
                        $account['name'],
                        $account
                     );
                break;
            }

            switch ($csvOperation['TransactionType']) {
                case 'Доход':
                case 'Расход':
                case 'Перевод зачисление':
                    $this->_replace('Operation', $k, $operation);
                break;
            }
        }

        $this->_writeYaml();

        return $this->_ymlFileName;
    }


    /**
     * Записываем данные из кучи в Yaml файл
     * @return bool
     */
    private function _writeYaml()
    {
        foreach ($this->_heap as $key => $value) {
            if (preg_match("/_keys$/", $key)) {
                unset($this->_heap[$key]);
            }
        }

        $yamlDumper = new sfYamlDumper();
        $yaml = $yamlDumper->dump($this->_heap);

        return (bool) file_put_contents($this->_ymlFileName, $yaml);
    }

    /**
     * Приводит данные из CSV к массиву хэшей
     * @return array
     */
    private function _hydrateArrayHash()
    {
        $headers = array_map('trim', $this->_csvDataSource->getHeaders());
        $rows    = $this->_csvDataSource->getRows();
        $data    = array();

        foreach ($rows as $row) {
            $data[] = array_combine($headers, $row);
        }
        return $data;
    }


    /**
     * Складываем в кучу объекты
     * @param  array $modelName
     * @param  array $keyCandidate
     * @param  array $data
     * @return string ссылка на объект
     */
    private function _replace($modelName, $keyCandidate, $data)
    {
        if (!isset($this->_heap[$modelName])) {
            $this->_heap[$modelName] = array();
            $this->_heap[$modelName . '_keys'] = array();
        }

        if (isset($this->_heap[$modelName . '_keys'][$keyCandidate])) {
            return $this->_heap[$modelName . '_keys'][$keyCandidate];
        }

        $cnt = count($this->_heap[$modelName]);
        $newKey = sprintf("%s_%d", $modelName, $cnt + 1);
        $this->_heap[$modelName . '_keys'][$keyCandidate] = $newKey;

        $this->_heap[$modelName][$newKey] = $data;

        return $newKey;
    }


    /**
     * Проверяем структуру присланного файла
     * @return bool true если структура верна
     */
    private function _testStructure()
    {
        if (!$this->_csvDataSource->isSymmetric())
            return false;

        $wantedHeaders = array(
            'ReceiptDate',
            'TransactionType',
            'Account',
            'Value',
            'Currency',
            'Category',
            'Comment',
        );

        $intersection = array_intersect(
            $wantedHeaders,
            $this->_csvDataSource->getHeaders()
        );

        if (count($wantedHeaders) != count($intersection)) {
            return false;
        }

        return true;
    }
}