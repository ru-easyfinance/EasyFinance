<?php

$help = "
Use: ./userCounter -sdb user:passwd@source_host/database \n";

foreach ( $argv as $k=>$a )
{
	if ( $a == '-sdb' && isset($argv[$k+1]) )
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

if( !isset($sourceDb) )
{
	die($help);
}

$db = new PDO( 'mysql:dbname=' . $sourceDb['dbname'] . ';host=' . $sourceDb['host'], $sourceDb['user'], $sourceDb['pass'] );
$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

//Инициилизируем массив с начальными значениями
$counters = array(
	'users' 		=> 1234, // Пиз**ж
	'operations' 	=> 0,
);

// Выбираем данные из хомяка

$sql = 'select count( money.id ) as operations from homemoney.money';

$stmt = $db->query( $sql );

$counters['operations'] += $stmt->fetchColumn();

$sql = 'select count( users.user_id ) as users from homemoney.users';

$stmt = $db->query( $sql );

$counters['users'] += $stmt->fetchColumn();

// Выбираем из easyfinance

$sql = 'select count( operation.id ) as operations from easyfinance.operation';

$stmt = $db->query( $sql );

$counters['operations'] += $stmt->fetchColumn();

$sql = 'select count( users.id ) as users from easyfinance.users';

$stmt = $db->query( $sql );

$counters['users'] += $stmt->fetchColumn();

// Сохраняем всю эту порнографию

file_put_contents( dirname(dirname(__FILE__)) . '/include/counters.json', json_encode($counters) );
