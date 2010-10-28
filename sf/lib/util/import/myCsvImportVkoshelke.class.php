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
        $this->_replaceBom($this->_csvFileName);

        $ymlFileName = tempnam(sys_get_temp_dir(), 'php_' . __CLASS__) . '.yml';
        touch($ymlFileName);
        $this->_ymlFileName = $ymlFileName;
    }


    /**
     * Делаем основную работу
     * @param User $user
     * @return string Имя файла с фикстурой или false
     */
    public function execute(User $user)
    {
        $this->_csvDataSource = new File_CSV_DataSource($this->_csvFileName);

        if (!$this->_testStructure())
            return false;

        foreach ($this->_hydrateArrayHash() as $k => $csvOperation) {
            $csvOperation['ReceiptDate'] = preg_replace(
                "/^[\s\S]*?(\d{2})\.(\d{2})\.(\d{4})[\s\S]*?$/",
                "$3-$2-$1",
                $csvOperation['ReceiptDate']
            );

            $csvOperation['Value'] =
                str_replace(',', '.', $csvOperation['Value']);

            $account = array(
                'user_id'  => $user->getId(),
                'name'     => sprintf(
                    "%s (%s)",
                    $csvOperation['Account'],
                    $csvOperation['Currency']
                ),
                'currency_id' => $this->_getCurrencyId(
                    $csvOperation['Currency']
                ),
                'type_id' => 1
            );

            $category = !$csvOperation['Category'] ? null : array(
                'user_id' => $user->getId(),
                'name' => $csvOperation['Category'],
                'type' =>
                    ($csvOperation['TransactionType'] == 'Доход' ? 1 : -1)
            );

            switch ($csvOperation['TransactionType']) {
                case 'Доход':
                case 'Расход':
                    $operation = array(
                        'user_id'  => $user->getId(),
                        'date'     => $csvOperation['ReceiptDate'],
                        'amount'   => $csvOperation['Value'],
                        'comment'  => $csvOperation['Comment'],
                        'type'     => (
                            $csvOperation['TransactionType'] == 'Доход' ?
                            Operation::TYPE_PROFIT :
                            Operation::TYPE_EXPENSE),
                        'Account'  => $this->_getRecordPointer(
                            'Account',
                            $account['name'],
                            $account
                         ),
                        'Category' => $this->_getRecordPointer(
                            'Category',
                            $category['name'],
                            $category
                        ),
                    );
                break;
                case 'Перевод списание':
                    $operation = array_merge(
                        $operation,
                        array(
                            'user_id'  => $user->getId(),
                            'date'     => $csvOperation['ReceiptDate'],
                            'amount'   => $csvOperation['Value'],
                            'comment'  => $csvOperation['Comment'],
                            'type'     => Operation::TYPE_TRANSFER,
                            'Account'  => $this->_getRecordPointer(
                                'Account',
                                $account['name'],
                                $account
                             ),
                        )
                    );
                break;
                case 'Перевод зачисление':
                    $operation['transfer_amount'] = $csvOperation['Value'];
                    $operation['TransferAccount'] = $this->_getRecordPointer(
                        'Account',
                        $account['name'],
                        $account
                     );
                break;
            }

            if (
                (
                    isset($operation['TransferAccount']) &&
                    isset($operation['Account'])
                )
                ||
                (
                    isset($operation['type']) &&
                    $operation['type'] != 2
                )
            ) {
                $this->_getRecordPointer('Operation', $k, $operation);
                $operation = array();
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
            } else {

            }
        }

        $yamlDumper = new sfYamlDumper();
        $yaml = $yamlDumper->dump($this->_heap, 3);

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
     * Складывает в кучу объекты, возвращая уникальный идентификатор объекта
     * в куче
     *
     * @param array $modelName
     * @param array $keyCandidate фактически ид, но не всегда в удобном формате
     * @param array $data
     * @return string ссылка на объект
     */
    private function _getRecordPointer($modelName, $keyCandidate, $data)
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

    /**
     * Убираем BOM из файла
     * @param string $csvFileName
     * @return void
     */
    private function _replaceBom($csvFileName)
    {
        $csvData = file_get_contents($csvFileName);
        $csvData = str_replace(pack('H*', 'EFBBBF'), '', $csvData);
        file_put_contents($csvFileName, $csvData);
    }

    /**
     * Получаем ид валюты по коду
     * @param string $code
     * @return int идентификатор валюты
     */
    private function _getCurrencyId($code)
    {
        $currency = Doctrine::getTable('Currency')
            ->findOneByCode($code, Doctrine::HYDRATE_ARRAY);
        return isset($currency['id']) ? $currency['id'] : 1;
    }
}