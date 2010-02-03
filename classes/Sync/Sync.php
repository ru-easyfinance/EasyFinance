<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
class Sync{

    private $db = null;

    private $user = null;

    private $xmlRequest = null;

    private $digitalSign = array("n2jdy303yeer7j2v");

    //$dataarray = array();

//ERROR - ошибка на стороне сервера, пользователю данные о ошибке не разглашаются
//WARNING  - ошибка пользователя. Пользователю говорится что у него не правильно (запрос, ключ, пароль)
//NOTICE - Предупреждение, сообщение для пользователя (НЕ ОШИБКА)

    private $dataarray = null;// массив содержащий данные из хмл-ки

    private $lastsync = '';

    private $dataarrayE = null;// массив сформированный Easyfinance

    private $recordsMap = null;// рекордс мэп. содержит имя таблицы и айдишник. + присвоенный айдишник в изифинанс.ру

    private $changedRec = null;// чейнжед рекордс. содержит айдишник изменённой записи и имя таблицы

    private $deletedRec = null;// делетед рекордс. содержит айдишник удалённой записи и номер таблицы.

    private $AccountsList = null;//массив счетов.

    private $TransfersList = null;//массив переводов со счёта на счёт

    private $CategoriesList = null;//массив категорий

    private $CurrensiesList = null;//массив валют

    private $DebetsList = null;//массив долгов

    private $IncomesList = null;//массив доходов

    /**
     * Массив расходов
     * @example array (
     *     'remotekey' => '1',
     *     'amount'    => '100.00',
     *     'currency'  => '1',
     *     'date'      => '2009-11-04 07:35Z',
     *     'name'      => 'Расход',
     *     'done'      => '1'
     * )
     * @var array
     */
    public $OutcomesList = null;

    public $PlansList = null;//массив планирования
    //
    //const $recordsMap = null;


    function cleanRec( $user_id = 0 ){
        $sql = "DELETE FROM records_map WHERE user_id=?";
        $a = $this->db->query($sql, $user_id);
    }

    function clearAll($user_id = 0){
        $sql = "DELETE FROM accounts WHERE user_id=?";
        $a = $this->db->query($sql, $user_id);
        $sql = "DELETE FROM category WHERE user_id=?";
        $a = $this->db->query($sql, $user_id);
        $sql = "DELETE FROM operation WHERE user_id=?";
        $a = $this->db->query($sql, $user_id);
        $sql = "DELETE FROM periodic WHERE user_id=?";
        $a = $this->db->query($sql, $user_id);
        $sql = "DELETE FROM records_map WHERE user_id=?";
        $a = $this->db->query($sql, $user_id);
    }

    function deleteAllByUser($xmlReq=''){
        $this->db = DbSimple_Generic::connect( "mysql://" . SYS_DB_USER . ":" . SYS_DB_PASS . "@" . SYS_DB_HOST . "/" . SYS_DB_BASE );
        $sn = php_xmlrpc_decode($xmlReq);
        $this->dataarray = $sn;
        if (!$this->sync_getAuth($this->dataarray[0])){
            // в случае неудачи
            return false;
        }
        $this->clearAll($this->user);
    }

    function deleteRecMapByUser($xmlReq=''){
        $this->db = DbSimple_Generic::connect( "mysql://" . SYS_DB_USER . ":" . SYS_DB_PASS . "@" . SYS_DB_HOST . "/" . SYS_DB_BASE );
        $sn = php_xmlrpc_decode($xmlReq);
        $this->dataarray = $sn;
        if (!$this->sync_getAuth($this->dataarray[0])){
            // в случае неудачи
            return false;
        }
        $this->cleanRec($this->user);
    }

    function getDate(){
        //возвращает время последней синхронизации
        $a = (date("Ymd His"));
        return ( substr($a,0,8) . 'T' . substr($a,9,6) );
    }

    function writeDataAndAnswerRec($xmlReq=''){
        $GLOBALS['xmlrpc_internalencoding'] = 'UTF-8';
        $GLOBALS['xmlrpc_defencoding'] = "UTF-8";
        $ser = $xmlReq;
        $sn = php_xmlrpc_decode($ser);
        $this->dataarray = $sn;
        $this->db = DbSimple_Generic::connect( "mysql://" . SYS_DB_USER . ":" . SYS_DB_PASS . "@" . SYS_DB_HOST . "/" . SYS_DB_BASE );
        $this->db->query("SET NAMES utf8");
        if (!$this->sync_getAuth($this->dataarray[0])){
            //$this->SendError();
            return false;
        }
        if (DEBUG) {
           $this->db->setLogger('databaseLogger');
        }
        // парсим полученную строку и загоняем её в массив
        $this->parsing();

        $account = new Account($this->user, $this->db);
        $account->AccountSync($this->AccountsList, $this->recordsMap, $this->changedRec, $this->deletedRec, $this->dataarrayE);
        $debet = new Debet($this->user, $this->db);
        $debet->DebetSync($this->DebetsList, $this->recordsMap, $this->changedRec, $this->deletedRec, $this->dataarrayE);

        $category = new Category($this->user, $this->db);
        $category->CategorySync($this->CategoriesList, $this->recordsMap, $this->changedRec, $this->deletedRec, $this->dataarrayE);
        $operation = new Operation($this->user, $this->db);
        $operation->OperationSync($this->IncomesList, $this->recordsMap, $this->changedRec, $this->deletedRec, $this->dataarrayE);
        $operation2 = new Operation($this->user, $this->db);
        $operation2->OperationSync($this->OutcomesList, $this->recordsMap, $this->changedRec, $this->deletedRec, $this->dataarrayE);
        $transfer = new Transfer($this->user, $this->db);
        $transfer->TransferSync($this->TransfersList, $this->recordsMap, $this->changedRec, $this->deletedRec, $this->dataarrayE);
        //$plans = new Periodic($this->user, $this->db);
        //$plans->PeriodicSync($this->PlansList, $this->recordsMap, $this->changedRec, $this->deletedRec);
        //$date='', &$data='', $user_id='', $db=''){
        RecordsMap_Model::formRecordsMap($this->lastsync, $this->dataarray, $this->dataarrayE, $this->user, $this->db);

        $this->dataarrayE[4] = array( 'ServerData' => $this->getDate() );

        $ret = $this->dataarrayE;
        $ret = array(
            'RecordsMap' => $this->dataarrayE[1]
            ,'ChangedRecords' => $this->dataarrayE[2]
            ,'DeletedRecords' => $this->dataarrayE[3]
            ,'ServerData' => $this->dataarrayE[4]
            );
        //die(print_r('jewkfjwk'));
        $a = php_xmlrpc_encode($ret);
        return $a;
    }

    function qwe($xmlReq, &$xmlAnswer, $needdec='0'){
        //echo ('<br>parametrov '.$xmlReq->getNumParams());
        $GLOBALS['xmlrpc_internalencoding'] = 'UTF-8';
        $GLOBALS['xmlrpc_defencoding'] = "UTF-8";
        $ser = $xmlReq;
        
                if ($needdec)
		$ser = php_xmlrpc_decode_xml($ser);
		//die (print_r($snv));
		$sn = php_xmlrpc_decode($ser);

        $this->dataarray = $sn;
                

        $this->db = DbSimple_Generic::connect( "mysql://" . SYS_DB_USER . ":" . SYS_DB_PASS . "@" . SYS_DB_HOST . "/" . SYS_DB_BASE );
        $this->db->query("SET NAMES utf8");
        
        //$db->query("SET NAMES utf8");
        // И обработчик ошибок для бд
        $this->db->setErrorHandler('databaseErrorHandler');

        //сохраняем полученный запрос
        // $this->xmlRequest = $xmlReq;
        if (!$this->sync_getAuth($this->dataarray[0])){
            //$this->SendError();
            // в случае неудачи
            return false;
        }
        //return false;
        //echo ('xml '.$this->xmlRequest);

        //Логгируем все запросы. Только во включенном режиме DEBUG
        if (DEBUG) {
           $this->db->setLogger('databaseLogger');
        }
        // парсим полученную строку и загоняем её в массив
        $this->parsing();
        //echo('<pre>');
        //die(print_r($this->CategoriesList));
        //$this->WriteTest();//вывод распарсенных значений
        
        //разбор распарсенных значений и формирование результирующей xml.
        $account = new Account($this->user, $this->db);
        $account->AccountSync($this->AccountsList, $this->recordsMap, $this->changedRec, $this->deletedRec, $this->dataarrayE);
        $debet = new Debet($this->user, $this->db);
        $debet->DebetSync($this->DebetsList, $this->recordsMap, $this->changedRec, $this->deletedRec, $this->dataarrayE);

        $category = new Category($this->user, $this->db);
        $category->CategorySync($this->CategoriesList, $this->recordsMap, $this->changedRec, $this->deletedRec, $this->dataarrayE);
        $operation = new Operation($this->user, $this->db);
        $operation->OperationSync($this->IncomesList, $this->recordsMap, $this->changedRec, $this->deletedRec, $this->dataarrayE);
        $operation2 = new Operation($this->user, $this->db);
        $operation2->OperationSync($this->OutcomesList, $this->recordsMap, $this->changedRec, $this->deletedRec, $this->dataarrayE);
        $transfer = new Transfer($this->user, $this->db);
        $transfer->TransferSync($this->TransfersList, $this->recordsMap, $this->changedRec, $this->deletedRec, $this->dataarrayE);
        //$plans = new Periodic($this->user, $this->db);
        //$plans->PeriodicSync($this->PlansList, $this->recordsMap, $this->changedRec, $this->deletedRec);
        //$date='', &$data='', $user_id='', $db=''){
        RecordsMap_Model::formRecordsMap($this->lastsync, $this->dataarray, $this->dataarrayE, $this->user, $this->db);

        $category->FormArray($this->lastsync, $this->dataarrayE);
        $account->FormArray($this->lastsync, $this->dataarrayE);
        $operation->FormArray($this->lastsync, $this->dataarrayE);
        $transfer->FormArray($this->lastsync, $this->dataarrayE);
        $debet->FormArray($this->lastsync, $this->dataarrayE);
        //$plans->FormArray($this->lastsync, $this->dataarrayE);
        $currency = new Currency($this->user, $this->db);
        $currency->FormArray($this->dataarrayE);
        $ret = array(
            'RecordsMap' => $this->dataarrayE[1]
            ,'ChangedRecords' => $this->dataarrayE[2]
            ,'DeletedRecords' => $this->dataarrayE[3]
            ,'Accounts' => $this->dataarrayE[4]
            ,'Transfers' => $this->dataarrayE[5]
            ,'Categories' => $this->dataarrayE[6]
            ,'Currencies' => $this->dataarrayE[7]
            ,'Debets' => $this->dataarrayE[8]
            ,'Incomes' => $this->dataarrayE[9]
            ,'Outcomes' => $this->dataarrayE[10]
            ,'Plans' => $this->dataarrayE[11]
            );
        //$ret = $this->dataarrayE;
        $a = php_xmlrpc_encode($ret);
        return $a;
    }

    /**
     * Конструктор Sync
     * @param <array> $xmlReq
     * @param <array> $xmlAnswer
     * @return <bool>
     */
    function __construct($xmlReq, &$xmlAnswer){
        
    }

    /**
     * Проверяет корректность пользователя. в случае успеха возвращает true.
     * Устанавливает пользователя
     * @param array $qw
     * @return bool
     */
    function sync_getAuth($qw)
    {
        $sql = "SELECT user_pass,id FROM users WHERE user_login=?";
        $a = $this->db->query($sql, $qw['login']);
        if ($a[0]['user_pass']!=$qw['pass']) {
            //trigger_error('Ключ не найден, или он устарел!', E_USER_WARNING);
            return false;
        }

        /*if (!in_array($qw['digsignature'],$this->digitalsign)) {
            return false;
        }*/
        $this->user = $a[0]['id'];
        $this->lastsync = formatIsoDateToNormal($qw['lastsync']);
        //echo ($this->lastsync);
        //echo ($a[0]['id']);
        return true;
    }

    /**
     * Возвращает ошибку
     */
    function SendError(){
        //header();
        die("Не совпадают пароли");
        //trigger_error();
    }

    /**
     * Функция производит парсинг значений и запись одинаковых по логике данных в собственный массив
     */
    function parsing(){
        //echo ("Парсим");
        $this->getRecordsMap($this->dataarray);
        $this->getChangedRecords($this->dataarray);
        $this->getDeletedRecords($this->dataarray);
        $this->getAccounts($this->dataarray);
        $this->getTransfers($this->dataarray);
        $this->getCategories($this->dataarray);
        $this->getCurrensies($this->dataarray);
        $this->getDebets($this->dataarray);
        $this->getIncomes($this->dataarray);
        $this->getOutcomes($this->dataarray);
        $this->getPlans($this->dataarray);
    }
    /**
     * Парсинг RecordsMap
     * @param array $qw
     */
    function getRecordsMap($qw){
        //echo ('Получили рекордс меп<br>');
        for ($i=0;$i<HOWMUCH;$i++){
            if ($qw[$i][0]['name']=='RecordsMap'){
                foreach ($qw[$i]  as $k=>$value ) if ($k>0){
                    $this->recordsMap[$k]['tablename']=$qw[$i][$k]['tablename'];
                    $this->recordsMap[$k]['remotekey']=$qw[$i][$k]['kkey'];
                }
            }
        }
    }
    /**
     * Парсинг ChangedRecords
     * @param array $qw
     */
    function getChangedRecords($qw){
        //echo ('Получили чейнджед рекордс<br>');
        for ($i=0;$i<HOWMUCH;$i++){
            if ($qw[$i][0]['name']=='ChangedRecords'){
                foreach ($qw[$i] as $k=>$value ) if ($k>0){
                    $this->changedRec[$k]['tablename']=$qw[$i][$k]['tablename'];
                    $this->changedRec[$k]['remotekey']=$qw[$i][$k]['kkey'];
                }
            }
        }
    }
    /**
     * Парсинг DeletedRecords
     * @param array $qw
     */
    function getDeletedRecords($qw){
        //echo ('Получили делитед рекордс<br>');
        for ($i=0;$i<HOWMUCH;$i++){
            if ($qw[$i][0]['name']=='DeletedRecords'){
                foreach ($qw[$i][0] as $k=>$value) if ($k>0){
                    //echo ($qw[3][$k]['tablename']);
                    $this->deletedRec[$k]['tablename']=$qw[$i][$k]['tablename'];
                    $this->deletedRec[$k]['remotekey']=$qw[$i][$k]['kkey'];
                }
            }
        }
    }
    /**
     * Парсинг счетов
     * @param array $qw
     */
    function getAccounts($qw){
        //echo ('Парсим счета<br>');
        for ($i=0;$i<HOWMUCH;$i++){
            if ($qw[$i][0]['tablename']=='Accounts'){
                foreach ($qw[$i] as $k=>$v) if ($qw[$i][$k]['name']!='A') if ($k>0){
                    //$k = $qw[$i][$k]['remotekey'];
                    $this->AccountsList[$k]['remotekey']=$qw[$i][$k]['kkey'];
                    $this->AccountsList[$k]['name']=$qw[$i][$k]['name'];
                    $this->AccountsList[$k]['cur']=$qw[$i][$k]['cur'];
                    $this->AccountsList[$k]['date']=formatIsoDateToNormal($qw[$i][$k]['date']);
                    $this->AccountsList[$k]['startbalance']=$qw[$i][$k]['startbalance'];
                    $this->AccountsList[$k]['descr']=$qw[$i][$k]['descr'];
                }
            }
        }
    }
    /**
     * Парсинг переводов
     * @param array $qw
     */
    function getTransfers($qw){
        //echo ('Парсим переводы<br>');
        for ($i=0;$i<HOWMUCH;$i++){
            if ($qw[$i][0]['tablename']=='Transfers'){
                foreach ($qw[$i] as $k=>$v) if ($qw[$i][$k]['name']!='T') if ($k>0){
                    $this->TransfersList[$k]['remotekey']=$qw[$i][$k]['kkey'];
                    $this->TransfersList[$k]['date']=formatIsoDateToNormal($qw[$i][$k]['date']);
                    $this->TransfersList[$k]['acfrom']=$qw[$i][$k]['acfrom'];
                    $this->TransfersList[$k]['amount']=$qw[$i][$k]['amount'];
                    $this->TransfersList[$k]['acto']=$qw[$i][$k]['acto'];
                    $this->TransfersList[$k]['descr']=$qw[$i][$k]['descr'];
                }
            }
        }
    }
    /**
     * Парсинг категорий
     * @param array $qw
     */
    function getCategories($qw){
        //echo('Парсим категории<br>');
        for ($i=0;$i<HOWMUCH;$i++){
            if ($qw[$i][0]['tablename']=='Categories'){
                foreach ($qw[$i] as $k=>$v) if ($qw[$i][$k]['name']!='C') if ($k>0){
                    $this->CategoriesList[$k]['remotekey']=$qw[$i][$k]['kkey'];
                    $this->CategoriesList[$k]['name']=$qw[$i][$k]['name'];
                    $this->CategoriesList[$k]['parent']=$qw[$i][$k]['parent'];
                }
            }
        }
    }
    /**
     * Парсинг валют
     * @param array $qw
     */
    function getCurrensies($qw){
        //echo('Парсим валюты<br>');
        for ($i=0;$i<HOWMUCH;$i++){
            if ($qw[$i][0]['tablename']=='Currensies'){
                foreach ($qw[$i] as $k=>$v) if ($qw[$i][$k]['name']!='C') if ($k>0){
                    $this->CurrensiesList[$k]['remotekey']=$qw[$i][$k]['kkey'];
                    $this->CurrensiesList[$k]['name']=$qw[$i][$k]['name'];
                }
            }
        }
    }
    /**
     * парсинг долгов
     * @param array $qw
     */
    function getDebets($qw){
        //echo('Парсим долги<br>');
        for ($i=0;$i<HOWMUCH;$i++){
            if ($qw[$i][0]['tablename']=='Debets'){
                foreach ($qw[$i] as $k=>$v) if ($qw[$i][$k]['name']!='D') if ($k>0){
                    $this->DebetsList[$k]['remotekey']=$qw[$i][$k]['kkey'];
                    $this->DebetsList[$k]['amount']=$qw[$i][$k]['amount'];
                    $this->DebetsList[$k]['currency']=$qw[$i][$k]['currency'];
                    $this->DebetsList[$k]['date']=formatIsoDateToNormal($qw[$i][$k]['date']);
                    $this->DebetsList[$k]['name']=$qw[$i][$k]['name'];
                    $this->DebetsList[$k]['done']=$qw[$i][$k]['done'];
                }
            }
        }
    }
    /**
     * Парсинг доходов
     * @param array $qw
     */
    function getIncomes($qw){
        //echo('Парсим доходы<br>');
        for ($i=0;$i<HOWMUCH;$i++){
            if ($qw[$i][0]['tablename']=='Incomes'){
                foreach ($qw[$i] as $k=>$v) if ($qw[$i][$k]['name']!='I') if ($k>0){
                    $this->IncomesList[$k]['remotekey']=$qw[$i][$k]['kkey'];
                    $this->IncomesList[$k]['date']=formatIsoDateToNormal($qw[$i][$k]['date']);
                    $this->IncomesList[$k]['category']=$qw[$i][$k]['category'];
                    $this->IncomesList[$k]['parent']=$qw[$i][$k]['parent'];
                    $this->IncomesList[$k]['account']=$qw[$i][$k]['account'];
                    $this->IncomesList[$k]['amount']=$qw[$i][$k]['amount'];
                    $this->IncomesList[$k]['descr']=$qw[$i][$k]['descr'];
                    //echo ('заценим дату'.formatIsoDateToNormal($qw[$i][$k]['date']));
                }
            }
        }
    }
    /**
     * Парсинг расходов
     * @param array $qw
     */
    function getOutcomes($qw){
        //echo('Парсим расходы<br>');
        for ($i=0;$i<HOWMUCH;$i++){
            if ($qw[$i][0]['tablename']=='Outcomes'){
                foreach ($qw[$i] as $k=>$v) if ($qw[$i][$k]['name']!='O') if ($k>0){
                    $this->OutcomesList[$k]['remotekey']=$qw[$i][$k]['kkey'];
                    $this->OutcomesList[$k]['date']=formatIsoDateToNormal($qw[$i][$k]['date']);
                    $this->OutcomesList[$k]['category']=$qw[$i][$k]['category'];
                    $this->OutcomesList[$k]['parent']=$qw[$i][$k]['parent'];
                    $this->OutcomesList[$k]['account']=$qw[$i][$k]['account'];
                    $this->OutcomesList[$k]['amount']=$qw[$i][$k]['amount'];
                    $this->OutcomesList[$k]['descr']=$qw[$i][$k]['descr'];
                }
            }
        }
    }
    /**
     * Парсим периодические транзакции
     * @param array $qw
     */
    function getPlans($qw){
        //echo('Парсим планы<br>');
        for ($i=0;$i<HOWMUCH;$i++){
            if ($qw[$i][0]['tablename']=='Plans'){
                foreach ($qw[$i] as $k=>$v) if ($qw[$i][$k]['name']!='P') if ($k>0){
                    $this->PlansList[$k]['name']=$qw[$i][$k]['name'];
                    $this->PlansList[$k]['remotekey']=$qw[$i][$k]['kkey'];
                    $this->PlansList[$k]['date']=formatIsoDateToNormal($qw[$i][$k]['date']);
                    $this->PlansList[$k]['period']=$qw[$i][$k]['period'];
                    $this->PlansList[$k]['count']=$qw[$i][$k]['count'];
                    $this->PlansList[$k]['category']=$qw[$i][$k]['category'];
                    $this->PlansList[$k]['account']=$qw[$i][$k]['account'];
                    $this->PlansList[$k]['amount']=$qw[$i][$k]['amount'];
                    $this->PlansList[$k]['descr']=$qw[$i][$k]['descr'];
                }
            }
        }
    }

    /**
     * Вывод полученных значений
     */
    function WriteTest(){
        echo ('<br>');
        foreach ($this->recordsMap as $k=>$v){
            echo('<br>'.$k.' '.$v['tablename'].$v['remotekey']);
        }
        echo ('<br>');
        foreach ($this->changedRec as $k=>$v){
            echo('<br>'.$k.' '.$v['tablename'].$v['remotekey']);
        }
        echo ('<br>');
        foreach ($this->deletedRec as $k=>$v){
            echo('<br>'.$k.' '.$v['tablename'].$v['remotekey']);
        }

        /*echo($this->recordsMap[0]['tablename']);
            echo($this->recordsMap[0]['remotekey'].'<br>');
        echo($this->changedRec[0]['tablename']);
            echo($this->changedRec[0]['remotekey'].'<br>');
        echo($this->deletedRec[0]['tablename']);
            echo($this->deletedRec[0]['remotekey'].'<br>');//*/
        echo('<br>');
        foreach ($this->AccountsList as $k=>$v){
            echo('<br>'.$k.' '.$v['remotekey'].$v['name'].$v['cur'].$v['date'].$v['startbalance'].$v['descr']);
        }
        echo('<br>');
        foreach ($this->TransfersList as $k=>$v){
            echo('<br>'.$k.' '.$v['remotekey'].$v['date'].$v['acfrom'].$v['amount'].$v['acto'].$v['descr']);
        }
        echo('<br>');
        foreach ($this->CategoriesList as $k=>$v){
            echo('<br>'.$k.' '.$v['remotekey'].$v['name'].$v['parent']);
        }
        echo('<br>');
        foreach ($this->CurrensiesList as $k=>$v){
            echo('<br>'.$k.' '.$v['remotekey'].$v['name']);
        }
        echo('<br>');
        foreach ($this->DebetsList as $k=>$v){
            echo('<br>'.$k.' '.$v['remotekey'].$v['amount'].$v['currency'].$v['date'].$v['name'].$v['done']);
        }
        echo('<br>доходы');
        foreach ($this->IncomesList as $k=>$v){
            echo('<br>'.$k.' '.$v['remotekey'].$v['date'].$v['category'].$v['parent'].$v['account'].$v['amount'].$v['descr']);
        }
        echo('<br>');
        foreach ($this->OutcomesList as $k=>$v){
            echo('<br>'.$k.' '.$v['remotekey'].$v['date'].$v['category'].$v['parent'].$v['account'].$v['amount'].$v['descr']);
        }
        echo('<br>');
        //name remotekey date period count category account amount descr
        foreach ($this->PlansList as $k=>$v){
            echo('<br>'.$k.' '.$v['remotekey'].$v['name'].$v['date'].$v['period'].$v['count'].$v['category'].$v['account'].$v['amount'].$v['descr']);
        }
    }
}