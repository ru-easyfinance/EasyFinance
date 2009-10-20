#!/usr/local/bin/php
<?php
$count = microtime(true);

foreach ( $argv as $k=>$a )
{
	if( $a == '--debug' )
	{
		define('DEBUG', true );
	}
	
	if( $a == '-c' && isset($argv[$k+1]) && is_numeric($argv[$k+1]) )
	{
		$usersCount = $argv[$k+1];
	}
}

if(!isset($usersCount))
{
	die($help);
}

if( !defined('DEBUG') ) define('DEBUG', false );

define('INDEX',true);
include( dirname(dirname(dirname(__FILE__))) . '/include/config.php' );

$db = new PDO( 'mysql:dbname=easyfinance_demo;host=' . SYS_DB_HOST, SYS_DB_USER, SYS_DB_PASS );
$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

$userTemplate = file_get_contents( 'UserTemplate.sql' );
$usersFile = SYS_DIR_INC . 'demoUsers.php';
$users = array();

// Подчищаем за собой
@unlink( $usersFile );

$deleteTables = array ('users','target_bill', 'target', 'tags', 'periodic', 'operation', 'category', 'calendar', 'account_field_values', 'accounts');

foreach ( $deleteTables as $table )
{
	$query = 'delete quick from ' . $table;
	$db->exec( $query );
}


for( $user = 1; $user < $usersCount*200; $user = $user + 200)
{
	$users[ 'demo_' . $user ] = sha1( microtime() );
	
	$replaceVariables = array(
		'${userId}' => $user,
		'${date}' => date('Y-m'),
		'${day}' => date("d"),
		'${login}' => 'demo_' . $user,
		'${pass}' => $users[ 'demo_' . $user ]
	);
	
	$preparedTemplate = replaceVariables( $userTemplate, $replaceVariables );
	$preparedTemplate = preg_split ( '/\);/', $preparedTemplate, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY); 
	
	if( DEBUG) echo "generate user " . $user . "\n";
	$db->beginTransaction();
	
	try
	{
		foreach ( $preparedTemplate as $a=>$query )
		{
			$db->exec( $query . ')' );
		}
		
		$db->commit();
	}
	catch ( PDOException $e )
	{
		$db->rollBack();
		exit(2);
	}
}

$usersFile = SYS_DIR_INC . 'generatedUsers.php';

if( file_exists($usersFile) )
{
	unlink( $usersFile );
}

file_put_contents(  SYS_DIR_INC . 'generatedUsers.php', '<?php $users = ' . var_export($users,true) . ';');

function replaceVariables( $template, array $values)
{	
	foreach ($values as $variable => $value)
	{
		//echo 'replacing ' . $variable . ' by ' . $value . "\n";
		$template = str_ireplace( $variable, $value, $template );
	}
	
	return $template;
}

//exit(0);
if( DEBUG ) echo microtime(true)  - $count . "\n";
