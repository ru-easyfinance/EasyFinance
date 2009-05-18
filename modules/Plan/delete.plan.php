<?php
$plan_id = @html($_GET['id']);
if ($ph->deleteUserPlan($_SESSION['user']['user_id'], $plan_id, &$dbs))
{
	$_SESSION['plan'] = false;
	$_SESSION['income'] = false;
	$_SESSION['outcome'] = false;
	$_SESSION['accounts'] = false;
	$_SESSION['plan_temp'] = false;
	header("location: index.php?modules=plan");
}
?>