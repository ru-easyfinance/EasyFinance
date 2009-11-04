<?php
//include('kd_xmlrpc.php');
define('INDEX',1);
require_once "../../include/config.php";
require_once SYS_DIR_INC.'/functions.php';


if (DEBUG) {
    // В режиме DEBUG выводим отладочные сообщения в консоль firebug < http://getfirebug.com/ > через плагин firephp < http://www.firephp.org/ >
    require_once SYS_DIR_LIBS . 'external/FirePHPCore/FirePHP.class.php';
}

// Подгружаем внешние библиотеки
require_once SYS_DIR_LIBS . 'external/DBSimple/Mysql.php';

// Устанавливаем обработчик ошибок
set_error_handler("UserErrorHandler");

define ('HOWMUCH',12);

//$xmlrpc_methods['method_not_found'] = XMLRPC_method_not_found;
//$synchhh = new Sync();

class Sync{
    private $db = null;
    private $user = null;
    private $digitalsign = array("n2jdy303yeer7j2v");
    //$dataarray = array();
    public $dataarray = null;// массив содержащий данные из хмл-ки
    public $recordsMap = null;// рекордс мэп. содержит имя таблицы и айдишник. + присвоенный айдишник в изифинанс.ру
    public $changedRec = null;// чейнжед рекордс. содержит айдишник изменённой записи и имя таблицы
    public $deletedRec = null;// делетед рекордс. содержит айдишник удалённой записи и номер таблицы.
    public $AccountsList = null;//массив счетов.
    public $TransfersList = null;//массив переводов со счёта на счёт
    public $CategoriesList = null;//массив категорий
    public $CurrensiesList = null;//массив валют
    public $DebetsList = null;//массив долгов
    public $IncomesList = null;//массив доходов
    public $OutcomesList = null;//массив расходов
    public $PlansList = null;//массив планирования
    //const $recordsMap = null;

    function __construct(){
       // Инициализируем одно *единственное* подключение к базе данных
        $this->db=DbSimple_Generic::connect("mysql://".SYS_DB_USER.":".SYS_DB_PASS."@".SYS_DB_HOST."/".SYS_DB_BASE);
        // И обработчик ошибок для бд
        $this->db->setErrorHandler('databaseErrorHandler');


        //Логгируем все запросы. Только во включенном режиме DEBUG
        /*if (DEBUG) {
           $this->db->setLogger('databaseLogger');
        }*/
        $this->ReadXmlData();
        /*if (!$this->ReadXmlData()){
                $this->SendError();
                return;
            };
        $this->parsing();*/
    }

    function sync_getAuth($qw){
        $sql = "SELECT user_pass FROM users WHERE user_login=?";
        $a = $this->db->query($sql, $qw['login']);
        //echo ('1'.$qw[0]['login']);
        //echo ($a[0]['user_pass'].'<br>');
        //echo ($a[0]['user_pass'].'<br>');
        if ($a[0]['user_pass']!=$qw['pass'])
            //trigger_error('Ключ не найден, или он устарел!', E_USER_WARNING);
            return false;
    //echo ($qw['digsignature'].'<br>');
            //echo ($this->digitalsign[0]);
    //echo ($os[0]);
        if (!in_array($qw['digsignature'],$this->digitalsign))
            return false;
        return true;
    }

    function SendError(){
        echo ("Не совпадают пароли");
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
            $this->recordsMap[$k]['tablename']=$qw[1][$k]['tablename'];
            $this->recordsMap[$k]['remotekey']=$qw[1][$k]['remotekey'];
            //echo ($recordsMap[$k]['tablename']);
        }
    }
    function getChangedRecords($qw){
        echo ('Получили чейнджед рекордс<br>');
        foreach ($qw[3] as $k=>$value ){
            $this->changedRec[$k]['tablename']=$qw[2][$k]['tablename'];
            $this->changedRec[$k]['remotekey']=$qw[2][$k]['remotekey'];
        }
    }
    function getDeletedRecords($qw){
        echo ('Получили делитед рекордс<br>');
        foreach ($qw[2] as $k=>$value){
            //echo ($qw[3][$k]['tablename']);
            $this->deletedRec[$k]['tablename']=$qw[3][$k]['tablename'];
            $this->deletedRec[$k]['remotekey']=$qw[3][$k]['remotekey'];
        }
    }
    function getAccounts($qw){
        echo ('Парсим счета<br>');
        //echo ($qw[4][0]['name']);
        for ($i=0;$i<HOWMUCH;$i++){
            if ($qw[$i]['tablename']=='Accounts'){
                foreach ($qw[$i] as $k=>$v) if ($qw[$i][$k]['name']!='A'){
                    $this->AccountsList[$k]['remotekey']=$qw[$i][$k]['remotekey'];
                    $this->AccountsList[$k]['name']=$qw[$i][$k]['name'];
                    $this->AccountsList[$k]['cur']=$qw[$i][$k]['cur'];
                    $this->AccountsList[$k]['date']=$qw[$i][$k]['date'];
                    $this->AccountsList[$k]['startbalance']=$qw[$i][$k]['startbalance'];
                    $this->AccountsList[$k]['descr']=$qw[$i][$k]['descr'];
                }
            }
        }
    }
    function getTransfers($qw){
        echo ('Парсим переводы<br>');
        for ($i=0;$i<HOWMUCH;$i++){
            if ($qw[$i]['tablename']=='Transfers'){
                foreach ($qw[$i] as $k=>$v) if ($qw[$i][$k]['name']!='T'){
                    $this->TransfersList[$k]['remotekey']=$qw[$i][$k]['remotekey'];
                    $this->TransfersList[$k]['date']=$qw[$i][$k]['date'];
                    $this->TransfersList[$k]['acfrom']=$qw[$i][$k]['acfrom'];
                    $this->TransfersList[$k]['amount']=$qw[$i][$k]['amount'];
                    $this->TransfersList[$k]['acto']=$qw[$i][$k]['acto'];
                    $this->TransfersList[$k]['descr']=$qw[$i][$k]['descr'];
                }
            }
        }
    }
    function getCategories($qw){
        echo('Парсим категории<br>');
        for ($i=0;$i<HOWMUCH;$i++){
            if ($qw[$i]['tablename']=='Categories'){
                foreach ($qw[$i] as $k=>$v) if ($qw[$i][$k]['name']!='C'){
                    $this->CategoriesList[$k]['remotekey']=$qw[$i][$k]['remotekey'];
                    $this->CategoriesList[$k]['name']=$qw[$i][$k]['name'];
                    $this->CategoriesList[$k]['parent']=$qw[$i][$k]['parent'];
                }
            }
        }
    }
    function getCurrensies($qw){
        echo('Парсим валюты<br>');
        for ($i=0;$i<HOWMUCH;$i++){
            if ($qw[$i]['tablename']=='Currensies'){
                foreach ($qw[$i] as $k=>$v) if ($qw[$i][$k]['name']!='C'){
                    $this->CurrensiesList[$k]['remotekey']=$qw[$i][$k]['remotekey'];
                    $this->CurrensiesList[$k]['name']=$qw[$i][$k]['name'];
                }
            }
        }
    }
    function getDebets($qw){
        echo('Парсим долги<br>');
        for ($i=0;$i<HOWMUCH;$i++){
            if ($qw[$i]['tablename']=='Debets'){
                foreach ($qw[$i] as $k=>$v) if ($qw[$i][$k]['name']!='D'){
                    $this->DebetsList[$k]['remotekey']=$qw[$i][$k]['remotekey'];
                    $this->DebetsList[$k]['amount']=$qw[$i][$k]['amount'];
                    $this->DebetsList[$k]['currency']=$qw[$i][$k]['currency'];
                    $this->DebetsList[$k]['date']=$qw[$i][$k]['date'];
                    $this->DebetsList[$k]['name']=$qw[$i][$k]['name'];
                    $this->DebetsList[$k]['done']=$qw[$i][$k]['done'];
                }
            }
        }
    }
    function getIncomes($qw){
        echo('Парсим доходы<br>');
        for ($i=0;$i<HOWMUCH;$i++){
            if ($qw[$i]['tablename']=='Incomes'){
                foreach ($qw[$i] as $k=>$v) if ($qw[$i][$k]['name']!='I'){
                    $this->IncomesList[$k]['remotekey']=$qw[$i][$k]['remotekey'];
                    $this->IncomesList[$k]['amount']=$qw[$i][$k]['amount'];
                    $this->IncomesList[$k]['currency']=$qw[$i][$k]['currency'];
                    $this->IncomesList[$k]['date']=$qw[$i][$k]['date'];
                    $this->IncomesList[$k]['name']=$qw[$i][$k]['name'];
                    $this->IncomesList[$k]['done']=$qw[$i][$k]['done'];
                }
            }
        }
    }
    function getOutcomes($qw){
        echo('Парсим расходы<br>');
        for ($i=0;$i<HOWMUCH;$i++){
            if ($qw[$i]['tablename']=='Outcomes'){
                foreach ($qw[$i] as $k=>$v) if ($qw[$i][$k]['name']!='O'){
                    $this->OutcomesList[$k]['remotekey']=$qw[$i][$k]['remotekey'];
                    $this->OutcomesList[$k]['amount']=$qw[$i][$k]['amount'];
                    $this->OutcomesList[$k]['currency']=$qw[$i][$k]['currency'];
                    $this->OutcomesList[$k]['date']=$qw[$i][$k]['date'];
                    $this->OutcomesList[$k]['name']=$qw[$i][$k]['name'];
                    $this->OutcomesList[$k]['done']=$qw[$i][$k]['done'];
                }
            }
        }
    }
    function getPlans($qw){
        echo('Парсим расходы<br>');
        for ($i=0;$i<HOWMUCH;$i++){
            if ($qw[$i]['tablename']=='Plans'){
                foreach ($qw[$i] as $k=>$v) if ($qw[$i][$k]['name']!='P'){
                    $this->PlansList[$k]['remotekey']=$qw[$i][$k]['remotekey'];
                    $this->PlansList[$k]['amount']=$qw[$i][$k]['amount'];
                    $this->PlansList[$k]['currency']=$qw[$i][$k]['currency'];
                    $this->PlansList[$k]['date']=$qw[$i][$k]['date'];
                    $this->PlansList[$k]['name']=$qw[$i][$k]['name'];
                    $this->PlansList[$k]['done']=$qw[$i][$k]['done'];
                }
            }
        }
    }

function ReadXmlData(){
    include ('zaglushka.php');
    
    $srv=xmlrpc_server_create();
    xmlrpc_server_register_method($srv, "Rec", "getRecordsMap");
    //$xmlRequest = $HTTP_RAW_POST_DATA;
    $this->dataarray = xmlrpc_decode($xmlRequest);
    //echo ($this->dataarray[1][1]['tablename']);
    //$this->parsing();
    if (!$this->sync_getAuth($this->dataarray[0])){
        $this->SendError();
        return;
    }
    $this->parsing();
    $a = new Account_Model($this->AccountsList,$this->recordsMap,$this->changedRec,$this->deletedRec);
    

    
    //$this->WriteTest();//выводит распарсенные значения
    //echo($this->recordsMap[0]['tablename']);
    $response = xmlrpc_server_call_method($srv, $xmlRequest, Null);
    //print $response;
    xmlrpc_server_destroy($srv);
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
            echo($this->recordsMap[0]['tablename']);
            echo($this->recordsMap[0]['remotekey'].'<br>');
        echo($this->changedRec[0]['tablename']);
            echo($this->changedRec[0]['remotekey'].'<br>');
        echo($this->deletedRec[0]['tablename']);
            echo($this->deletedRec[0]['remotekey'].'<br>');
        echo('<br>');
        foreach ($this->AccountsList as $k=>$v){
            echo('<br>'.$k.$v['remotekey'].$v['name'].$v['cur'].$v['date'].$v['startbalance'].$v['descr']);
        }
        echo('<br>');
        foreach ($this->TransfersList as $k=>$v){
            echo('<br>'.$k.$v['remotekey'].$v['date'].$v['acfrom'].$v['amount'].$v['acto'].$v['descr']);
        }
        echo('<br>');
        foreach ($this->CategoriesList as $k=>$v){
            echo('<br>'.$k.$v['remotekey'].$v['name'].$v['parent']);
        }
        echo('<br>');
        foreach ($this->CurrensiesList as $k=>$v){
            echo('<br>'.$k.$v['remotekey'].$v['name']);
        }
        echo('<br>');
        foreach ($this->DebetsList as $k=>$v){
            echo('<br>'.$k.$v['remotekey'].$v['amount'].$v['currency'].$v['date'].$v['name'].$v['done']);
        }
        echo('<br>');
        foreach ($this->IncomesList as $k=>$v){
            echo('<br>'.$k.$v['remotekey'].$v['date'].$v['category'].$v['parent'].$v['account'].$v['amount'].$v['descr']);
        }
        echo('<br>');
        foreach ($this->OutcomesList as $k=>$v){
            echo('<br>'.$k.$v['remotekey'].$v['date'].$v['category'].$v['parent'].$v['account'].$v['amount'].$v['descr']);
        }
        echo('<br>');
        foreach ($this->PlansList as $k=>$v){
            echo('<br>'.$k.$v['remotekey'].$v['date'].$v['category'].$v['parent'].$v['account'].$v['amount'].$v['descr']);
        }
    }
}