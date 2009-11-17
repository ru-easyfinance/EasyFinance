<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
class Sync{
    
    private $db = null;

    private $user = null;

    private $digitalsign = array("n2jdy303yeer7j2v");

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

    /**
     * Конструктор
     */
    function __construct(){
       // Инициализируем одно *единственное* подключение к базе данных
        $this->db = DbSimple_Generic::connect( "mysql://" . SYS_DB_USER . ":" . SYS_DB_PASS . "@" . SYS_DB_HOST . "/" . SYS_DB_BASE );

        // И обработчик ошибок для бд
        $this->db->setErrorHandler('databaseErrorHandler');

        //Логгируем все запросы. Только во включенном режиме DEBUG
        /*if (DEBUG) {
           $this->db->setLogger('databaseLogger');
        }*/

        if (!$this->ReadXmlData()){
            trigger_error('Не верный ключ', E_USER_WARNING);
            $this->SendError();
        }
        //trigger_error('Получилось авторизироваться', E_USER_NOTICE);
        $this->parsing();
        //$this->parsing();
        $account = new Account($this->user);
        $account->AccountSync($this->AccountsList, $this->recordsMap, $this->changedRec, $this->deletedRec);
        $category = new Category($this->user);
        $category->CategorySync($this->CategoriesList, $this->recordsMap, $this->changedRec, $this->deletedRec);
        $operation = new Operation($this->user);
        $operation->OperationSync($this->IncomesList, $this->recordsMap, $this->changedRec, $this->deletedRec);
        $transfer = new Transfer($this->user);
        $transfer->TransferSync($this->TransfersList, $this->recordsMap, $this->changedRec, $this->deletedRec);

        //$date='', &$data='', $user_id='', $db=''){
        RecordsMap_Model::formRecordsMap($this->lastsync, $this->dataarray, $this->dataarrayE, $this->user, $this->db);

        $category->FormArray($this->lastsync, $this->dataarrayE);
        $account->FormArray($this->lastsync, $this->dataarrayE);
        $operation->FormArray($this->lastsync, $this->dataarrayE);
        $transfer->FormArray($this->lastsync, $this->dataarrayE);
        $c = xmlrpc_encode($this->dataarrayE);
        echo ($c);
        $fp = fopen("counter.xml", "w");
        fwrite($fp, $c);
        //echo ($this->dataarrayE[9][3]['descr']);
        //$this->WriteTest();//выводит распарсенные значения
        //$c = xmlrpc_encode($this->dataarray);
        //echo ($c);
    }

    /**
     * Проверяет авторизацию
     * @param <type> 
     * @return bool
     */
    function sync_getAuth($qw)
    {
        $sql = "SELECT user_pass,id FROM users WHERE user_login=?";
        $a = $this->db->query($sql, $qw['login']);
        //echo ('1'.$qw[0]['login']);
        //echo ($a[0]['user_pass'].'<br>');
        //echo ($a[0]['user_pass'].'<br>');
        if ($a[0]['user_pass']!=$qw['pass']) {
            //trigger_error('Ключ не найден, или он устарел!', E_USER_WARNING);
            return false;
        }

    //echo ($qw['digsignature'].'<br>');
            //echo ($this->digitalsign[0]);
    //echo ($os[0]);
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
        $this->getPlans($this->dataarray);
    }

    function getRecordsMap($qw){
        echo ('Получили рекордс меп<br>');
        foreach ($qw[1]  as $k=>$value ){
            //$recordsMap = 1;
            //echo ('ethetr');
            $this->recordsMap[$k+1]['tablename']=$qw[1][$k]['tablename'];
            $this->recordsMap[$k+1]['remotekey']=$qw[1][$k]['remotekey'];
            //echo ($recordsMap[$k]['tablename']);
        }
    }
    function getChangedRecords($qw){
        echo ('Получили чейнджед рекордс<br>');
        foreach ($qw[3] as $k=>$value ){
            $this->changedRec[$k+1]['tablename']=$qw[3][$k]['tablename'];
            $this->changedRec[$k+1]['remotekey']=$qw[3][$k]['remotekey'];
        }
    }
    function getDeletedRecords($qw){
        echo ('Получили делитед рекордс<br>');
        foreach ($qw[2] as $k=>$value){
            //echo ($qw[3][$k]['tablename']);
            $this->deletedRec[$k+1]['tablename']=$qw[2][$k]['tablename'];
            $this->deletedRec[$k+1]['remotekey']=$qw[2][$k]['remotekey'];
        }
    }
    function getAccounts($qw){
        echo ('Парсим счета<br>');
        //echo ($qw[4][0]['name']);
        for ($i=0;$i<HOWMUCH;$i++){
            if ($qw[$i]['tablename']=='Accounts'){
                foreach ($qw[$i] as $k=>$v) if ($qw[$i][$k]['name']!='A'){
                    //$k = $qw[$i][$k]['remotekey'];
                    $this->AccountsList[$k+1]['remotekey']=$qw[$i][$k]['remotekey'];
                    $this->AccountsList[$k+1]['name']=$qw[$i][$k]['name'];
                    $this->AccountsList[$k+1]['cur']=$qw[$i][$k]['cur'];
                    $this->AccountsList[$k+1]['date']=formatIsoDateToNormal($qw[$i][$k]['date']);
                    $this->AccountsList[$k+1]['startbalance']=$qw[$i][$k]['startbalance'];
                    $this->AccountsList[$k+1]['descr']=$qw[$i][$k]['descr'];
                }
            }
        }
    }
    function getTransfers($qw){
        echo ('Парсим переводы<br>');
        for ($i=0;$i<HOWMUCH;$i++){
            if ($qw[$i]['tablename']=='Transfers'){
                foreach ($qw[$i] as $k=>$v) if ($qw[$i][$k]['name']!='T'){
                    $this->TransfersList[$k+1]['remotekey']=$qw[$i][$k]['remotekey'];
                    $this->TransfersList[$k+1]['date']=formatIsoDateToNormal($qw[$i][$k]['date']);
                    $this->TransfersList[$k+1]['acfrom']=$qw[$i][$k]['acfrom'];
                    $this->TransfersList[$k+1]['amount']=$qw[$i][$k]['amount'];
                    $this->TransfersList[$k+1]['acto']=$qw[$i][$k]['acto'];
                    $this->TransfersList[$k+1]['descr']=$qw[$i][$k]['descr'];
                }
            }
        }
    }
    function getCategories($qw){
        echo('Парсим категории<br>');
        for ($i=0;$i<HOWMUCH;$i++){
            if ($qw[$i]['tablename']=='Categories'){
                foreach ($qw[$i] as $k=>$v) if ($qw[$i][$k]['name']!='C'){
                    $this->CategoriesList[$k+1]['remotekey']=$qw[$i][$k]['remotekey'];
                    $this->CategoriesList[$k+1]['name']=$qw[$i][$k]['name'];
                    $this->CategoriesList[$k+1]['parent']=$qw[$i][$k]['parent'];
                }
            }
        }
    }
    function getCurrensies($qw){
        echo('Парсим валюты<br>');
        for ($i=0;$i<HOWMUCH;$i++){
            if ($qw[$i]['tablename']=='Currensies'){
                foreach ($qw[$i] as $k=>$v) if ($qw[$i][$k]['name']!='C'){
                    $this->CurrensiesList[$k+1]['remotekey']=$qw[$i][$k]['remotekey'];
                    $this->CurrensiesList[$k+1]['name']=$qw[$i][$k]['name'];
                }
            }
        }
    }
    function getDebets($qw){
        echo('Парсим долги<br>');
        for ($i=0;$i<HOWMUCH;$i++){
            if ($qw[$i]['tablename']=='Debets'){
                foreach ($qw[$i] as $k=>$v) if ($qw[$i][$k]['name']!='D'){
                    $this->DebetsList[$k+1]['remotekey']=$qw[$i][$k]['remotekey'];
                    $this->DebetsList[$k+1]['amount']=$qw[$i][$k]['amount'];
                    $this->DebetsList[$k+1]['currency']=$qw[$i][$k]['currency'];
                    $this->DebetsList[$k+1]['date']=formatIsoDateToNormal($qw[$i][$k]['date']);
                    $this->DebetsList[$k+1]['name']=$qw[$i][$k]['name'];
                    $this->DebetsList[$k+1]['done']=$qw[$i][$k]['done'];
                }
            }
        }
    }
    function getIncomes($qw){
        echo('Парсим доходы<br>');
        for ($i=0;$i<HOWMUCH;$i++){
            if ($qw[$i]['tablename']=='Incomes'){
                foreach ($qw[$i] as $k=>$v) if ($qw[$i][$k]['name']!='I'){
                    $this->IncomesList[$k+1]['remotekey']=$qw[$i][$k]['remotekey'];
                    $this->IncomesList[$k+1]['date']=formatIsoDateToNormal($qw[$i][$k]['date']);
                    $this->IncomesList[$k+1]['category']=$qw[$i][$k]['category'];
                    $this->IncomesList[$k+1]['parent']=$qw[$i][$k]['parent'];
                    $this->IncomesList[$k+1]['account']=$qw[$i][$k]['account'];
                    $this->IncomesList[$k+1]['amount']=$qw[$i][$k]['amount'];
                    $this->IncomesList[$k+1]['descr']=$qw[$i][$k]['descr'];
                    //echo ('заценим дату'.formatIsoDateToNormal($qw[$i][$k]['date']));
                }
            }
        }
    }
    function getOutcomes($qw){
        echo('Парсим расходы<br>');
        for ($i=0;$i<HOWMUCH;$i++){
            if ($qw[$i]['tablename']=='Outcomes'){
                foreach ($qw[$i] as $k=>$v) if ($qw[$i][$k]['name']!='O'){
                    $this->OutcomesList[$k+1]['remotekey']=$qw[$i][$k]['remotekey'];
                    $this->OutcomesList[$k+1]['date']=formatIsoDateToNormal($qw[$i][$k]['date']);
                    $this->OutcomesList[$k+1]['category']=$qw[$i][$k]['category'];
                    $this->OutcomesList[$k+1]['parent']=$qw[$i][$k]['parent'];
                    $this->OutcomesList[$k+1]['account']=$qw[$i][$k]['account'];
                    $this->OutcomesList[$k+1]['amount']=$qw[$i][$k]['amount'];
                    $this->OutcomesList[$k+1]['descr']=$qw[$i][$k]['descr'];
                }
            }
        }
    }
    function getPlans($qw){
        echo('Парсим расходы<br>');
        for ($i=0;$i<HOWMUCH;$i++){
            if ($qw[$i]['tablename']=='Plans'){
                foreach ($qw[$i] as $k=>$v) if ($qw[$i][$k]['name']!='P'){
                    $this->PlansList[$k+1]['remotekey']=$qw[$i][$k]['remotekey'];
                    $this->PlansList[$k+1]['amount']=$qw[$i][$k]['amount'];
                    $this->PlansList[$k+1]['currency']=$qw[$i][$k]['currency'];
                    $this->PlansList[$k+1]['date']=formatIsoDateToNormal($qw[$i][$k]['date']);
                    $this->PlansList[$k+1]['name']=$qw[$i][$k]['name'];
                    $this->PlansList[$k+1]['done']=$qw[$i][$k]['done'];
                }
            }
        }
    }

    /**
     * Считывает данные из XML
     * @return bool
     */
    function ReadXmlData(){
        include ('zaglushka.php');

        $srv = xmlrpc_server_create();

        xmlrpc_server_register_method($srv, "Rec", "getRecordsMap");

        //$xmlRequest = $HTTP_RAW_POST_DATA;

        $this->dataarray = xmlrpc_decode($xmlRequest);

        //echo ($this->dataarray[1][1]['tablename']);
        //$this->parsing();
        
        if (!$this->sync_getAuth($this->dataarray[0])){
            $this->SendError();
            return false;
        } 
        return true;
        


        //$this->WriteTest();//выводит распарсенные значения
        //echo($this->recordsMap[0]['tablename']);
        $response = xmlrpc_server_call_method($srv, $xmlRequest, Null);
        //print $response;
        xmlrpc_server_destroy($srv);
        //DateTime::ATOM();
        //XMLRPC_response(XMLRPC_prepare($response), WEBLOG_XMLRPC_USERAGENT);
          //$c = new Zend_XmlRpc_Client('http://framework.zend.com/xmlrpc');
    //echo $c->call('test.sayHello');
        //XMLRPC_response(XMLRPC_prepare($array), WEBLOG_XMLRPC_USERAGENT);
            //XMLRPC_response(XMLRPC_prepare($array[1]), WEBLOG_XMLRPC_USERAGENT);
        //echo "123";
        /*$clients = simplexml_load_file('clients.xml');
        foreach ($clients->client as $client) {
             print "$client->name has account number $client->account_number ";
        }*/
    }

//SendRemoteAnswer();
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
        foreach ($this->PlansList as $k=>$v){
            echo('<br>'.$k.' '.$v['remotekey'].$v['amount'].$v['currency'].$v['date'].$v['name'].$v['done']);
        }
    }
}