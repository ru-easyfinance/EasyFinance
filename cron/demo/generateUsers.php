#!/usr/local/bin/php
<?php
$count = microtime(true);

$help = "
Use: ./genUserTemplate -sdb user:passwd@source_host/database -uid Source_user_Id -c Count_Users_To_Generate [--debug]
";

foreach ( $argv as $k=>$a )
{
	if( $a == '--debug' )
	{
            	define('DEBUG', true );
	}
	elseif( $a == '-uid' && isset($argv[$k+1]) && is_numeric($argv[$k+1]) )
	{
            	$sourceUserId = (int)$argv[$k+1];
	}
	elseif( $a == '-c' && isset($argv[$k+1]) && is_numeric($argv[$k+1]) )
	{
            	$usersCount = $argv[$k+1];
	}
	elseif ( $a == '-sdb' && isset($argv[$k+1]) )
	{
		if(!preg_match('/([^:]+):([^@]+)@([^\/]+)\/(.*)/' , $argv[$k+1], $matches ))
		{
			die( $help );
		}
		
		$sourceDb = array(
			'host' => $matches[3],
			'user' => $matches[1],
			'pass' => $matches[2],
			'dbname' => $matches[4]
		);
	}	
}

if( !isset($sourceUserId) || !isset($usersCount) || !isset($sourceDb) )
{
	die($help);
}

if( !defined('DEBUG') ) define('DEBUG', false );

define('INDEX',true);
// Подключаем конфиг демы
include( dirname(dirname(dirname(__FILE__))) . '/include/config.php' );

// Удостоверяемся что запущены в демо режиме, дабы не потереть лишнего
if( !IS_DEMO ) die("Must be runned only for demo !\n");

// Файл для хранения массива сгенерённых пользователей
$usersFile = SYS_DIR_INC . 'generatedUsers.php';

// Конфиг таблиц для выборки
$tablesTemplate = array(
	'users' => array(
		// поле по которому будет производится выборка, и которое будет заменено на ключ (user_id)
		'userId'		=> 'id'
	),
	'accounts' => array(
		'userId' 	=> 'user_id',
		// к значениям каких полей добавлять значение ключа (user_id)
		'addUserId'	=> array('account_id'),
	),
	'account_field_values' => array(
		// Специальное условие выборки, если невозможно выбрать напрямую по userId
		'selectCase'	=> '`account_fieldsaccount_field_id` IN (select `account_id` from `accounts` where `user_id`={$userId})',
		'addUserId'	=> array('field_value_id', 'account_fieldsaccount_field_id'),
	),
	'calendar' => array(
		'userId' 	=> 'user_id',
		'addUserId'	=> array('id')
	),
	'category' => array(
		'userId'		=> 'user_id',
		'addUserId'	=> array('cat_id','cat_parent')
	),
	'operation' => array(
		'userId'		=> 'user_id',
		'addUserId'	=> array('id', 'cat_id', 'account_id')
	),
	'periodic' => array(
		'userId'		=> 'user_id',
		'addUserId'	=> array('id', 'category', 'account')
	),
	'tags' => array(
		'userId'		=> 'user_id',
		'addUserId'	=> array('oper_id'),
	),
	'target' => array(
		'userId'		=> 'user_id',
		'addUserId'	=> array('id', 'target_account_id')
	),
	'target_bill' => array(
		'userId'		=> 'user_id',
		'addUserId'	=> array('id', 'bill_id', 'target_id')
	),
	'budget' => array(
		'userId'		=> 'user_id',
		'addUserId'	=> array('category')
		
		// из за странного составного ключа - похачен алгоритм !
	)
);

// Подчищаем за собой
if( DEBUG ) echo "Cleanup old generated users ...\n";
@unlink( $usersFile );

// Подключаем базу - источник
if( DEBUG ) echo "Connecting to source database ...\n";
$db = new PDO( 'mysql:dbname=' . $sourceDb['dbname'] . ';host=' . $sourceDb['host'], $sourceDb['user'], $sourceDb['pass'] );
$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

$tablesSelected = array();

// Делаем выборку таблиц и их данных из массива
if( DEBUG ) echo "\nSelecting tables ...\n";
foreach ( $tablesTemplate as $table => $params )
{
	if( !isset( $params['userId'] ) && !isset($params['selectCase']))
	{
		die( 'Can\'t select table without key or `where` statement!' );
	}
	
	$sql = 'select * from `' . $table . '` where ';
	
	if( isset($params['selectCase']) )
	{
		$sql .= str_ireplace('{$userId}', $sourceUserId, $params['selectCase']);
	}
	else
	{
		$sql .= ' `' . $params['userId'] . '`=' . $sourceUserId . ';';
	}
	
	$stmt = $db->query( $sql );
	
	$tablesSelected[ $table ] = $stmt->fetchAll(PDO::FETCH_ASSOC);
	
	if( DEBUG )  echo '`' . $table . "` selected;\n"; 
}

// Отключаемся от бд
unset( $db );

// Подключаем базу - приёмник
if( DEBUG ) echo "Connecting to target database ...\n";

$db = new PDO( 'mysql:dbname=' . SYS_DB_BASE . ';host=' . SYS_DB_HOST, SYS_DB_USER, SYS_DB_PASS );
$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

// Очищаем таблицы
if( DEBUG ) echo "\nCleanup tables ...\n";
foreach ( array_keys($tablesTemplate) as $table )
{
	$query = 'delete quick from ' . $table;
	$db->exec( $query );
}

$userTemplate = '';

$users = array();

if( DEBUG ) echo "\nProcessing " . $usersCount . " users:\n";
// формируем sql с заменой значений указанных полей

for( $user = 1; $user < $usersCount*200; $user = $user + 200)
{
	$users[ 'demo_' . $user ] = sha1( microtime() );
	
	$db->beginTransaction();
	
	try
	{
		foreach ( $tablesSelected as $table => $rows )
		{
			$rowCount = sizeof( $rows );
			
			// Если в селекте из таблицы не было данных - игнорим
			if( !$rowCount ) continue;
			
			$sql = 'insert into `' . $table . '` (' . implode( ', ', array_map( 'quoteKey', array_keys( $rows[0] ) ) ) . ') values ';
			
			foreach ( $rows as $id => $row )
			{
				// Подменяем указанные поля на нужные нам значения
				
				// UserId
				if( isset($tablesTemplate[ $table ]['userId']) )
				{
					$row[ $tablesTemplate[ $table ]['userId'] ] = $user;
				}
				
				// Прибавление
				if( isset($tablesTemplate[ $table ]['addUserId']) )
				{
					foreach ( $tablesTemplate[ $table ]['addUserId'] as $key)
					{
						// Если значение нулевое - оно специальное, пропускаем
						if( $row[ $key ] == 0 ) continue;
						
						$row[ $key ] += $user;
					}
				}
				
				// Хак для таблицы users
				if( $table == 'users' )
				{
					$row['user_login'] = 'demo_' . $user;
					$row['user_pass'] = $users[ $row['user_login'] ];
				}
				
				//Хак для таблицы бюджета
				if( $table == 'budjet' )
				{
					$row['key'] = implode( '-', array($user, $row['category'], $row['drain'], $row['date_start']) );
				}
				
				$sql .= "\n(" . implode( ',', array_map( 'quoteSql', $row ) ) . ')' . (( $id < $rowCount-1 )?',':'');
			}
			
			$sql .= ";\n";
			
			$db->exec( $sql );
		}
		
		$db->commit();
		
		if( DEBUG ) echo "user #" . $user . " done;\n";
	}
	catch ( PDOException $e )
	{
		$db->rollBack();
		echo "\n\n" . $e->getMessage() ."\n\n" . $e->getTraceAsString() . "\n\n" . $sql;
		exit(2);
	}
}

file_put_contents( $usersFile, '<?php $users = ' . var_export($users,true) . ';');
chmod( $usersFile, 0777 );

if( DEBUG ) echo "\nDone for " , microtime(true)  - $count , " ms.\n";

function quoteSql( $value )
{
	if( is_numeric($value) )
	{
		return $value;
	}
	else
	{
		return '\'' . $value . '\'';
	}
}

function quoteKey( $key )
{
	return '`' . $key . '`';
}
