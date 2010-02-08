<div class="line"><strong>Журнал</strong></div>
<div class="menu">
	<a href="/operation/listOperations/?period=day" 
		class="<?=($period == 'day')?'current':''?>">сутки</a> | 
	<a href="/operation/listOperations/?period=week" 
		class="<?=($period == 'week')?'current':''?>">неделя</a> | 
	<a href="/operation/listOperations/?period=month" 
		class="<?=($period == 'month')?'current':''?>">месяц</a>
</div>

<?=$this->display('blocks/operationsList.tpl')?>
