<?
session_start();

	$_SESSION['pda'] = 'on';
	
	if ($_GET['pda'] == 'off')
	{
		$_SESSION['pda'] = 'off';
	}

	header('Location: ../index.php');
?>