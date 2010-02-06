<?php

$typesArray = array(
	Category::TYPE_WASTE 		=> 'Расходная',
	Category::TYPE_PROFIT 		=> 'Доходная',
	Category::TYPE_UNIVERSAL 	=> 'Универсальная'
);

?>
<strong><?=(false)?'Редактирование категории' . $catName : 'Добавление категории';?></strong>
<br />
<div class="menu">
	<a href="/category/add/waste" class="<?=($category['type'] == Category::TYPE_WASTE)?'current':''?>">расход</a> | 
	<a href="/category/add/profit" class="<?=($category['type'] == Category::TYPE_PROFIT)?'current':''?>">доход</a> | 
	<a href="/category/add/universal" class="<?=($category['type'] == Category::TYPE_UNIVERSAL)?'current':''?>">универсальная</a>
</div>
<?php
// Вывод сообщений
if( isset($error) && is_array($error) && array_key_exists( 'text', $error ) )
{
	?><p style="color:red; font-weight:bold; font-size: 12px;"><?=$error['text']?></p><?php
}
if( isset($result) && is_array($result) && array_key_exists('text', $result) )
{
	?><p style="color:green; font-weight:bold; font-size: 12px;"><?=$result['text']?></p><?php
}
?>
<form method="POST">
<div class="wide">
	<input type="hidden" name="type" value="<?=$category['type']?>"></input>
	
	<div class="line">Название: <br />
		<input class="wide" name="name" value="<?=isset($category['name'])?$category['name']:''?>" />
	</div>	
	<div class="line">Родительская категория:<br><select name="parent" class="wide">
		<option value="0">Без родительской</option>
	<?php
	while( list(,$userCategory) = each($res['category']['user']) )
	{
		?><option id="<?=$userCategory['id']?>"><?=$userCategory['name']?></option>
		<?php
	}
	?>
	</select></div>
	<div class="line">Системная категория:<br><select name="system" class="wide">
		<option value="0">Не выбрана</option>
	<?php
	while( list(,$sysCategory) = each($res['category']['system']) )
	{
		?><option id="<?=$sysCategory['id']?>"
		<?=(isset($category['system']) && $category['system'] == $sysCategory['id'])?'selected="selected"':''?>
		><?=$sysCategory['name']?></option>
		<?php
	}
	?>
	</select></div>	

	<input id="btnSave" type="submit" style="width:100%" value="Сохранить">
</div>
</form>
