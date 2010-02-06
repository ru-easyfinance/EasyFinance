<?php
// Определяем класс для подсветки цветом
switch($categorysType)
{
	case Category::TYPE_PROFIT:
		$linkStyle = 'green';break;
	case Category::TYPE_WASTE:
		$linkStyle = 'red';break;
	case Category::TYPE_UNIVERSAL:
	default:
		$linkStyle = '';break;
}
?>
<table cellspacing="0" cellpadding="0" class="wide"><tbody>
	<tr>
		<td><strong>Категории</strong></td>
		<td class="wide">&nbsp;</td>
		<td align="right"><a href="/category/add/<?=$categorysType?>">добавить</a></td>
	</tr>
</tbody></table>
<div class="menu">
	<a href="/category/waste" class="<?=($categorysType == Category::TYPE_WASTE)?'current':''?>">расход</a> | 
	<a href="/category/profit" class="<?=($categorysType == Category::TYPE_PROFIT)?'current':''?>">доход</a> | 
	<a href="/category/universal" class="<?=($categorysType == Category::TYPE_UNIVERSAL)?'current':''?>">универсальные</a>
</div>
<br />
<table cellspacing="0" cellpadding="0" class="wide categorys"><tbody>
	<?php
	foreach( $res['category']['user'] as $category )
	{
		//Фильтруем по типу
		if( $category['type'] != $categorysType && $category['visible'] )
		{
			continue;
		}
		
		//Дочерние пропускаем, для построения дерева
		if( $category['parent'] )
		{
			continue;
		}
		?>
		<tr>
			<td class="wide"><a href="/category/edit/<?=$category['id']?>" class="<?=$linkStyle?>">
			<?=$category['name']?></span></a></td>
			<td><a href="/category/del/<?=$category['id']?>">(X)</a></td>
		</tr>
		<?php
		// foreach по причине правильного переключения укаателей массива
		foreach ( $res['category']['user'] as $categoryChild )
		{
			if( $categoryChild['parent'] != $category['id'] )
			{
				continue;
			}
			?>
			<tr>
				<td class="wide childCategory"><a href="/category/edit/<?=$categoryChild['id']?>" class="<?=$linkStyle?>">
				<?=$categoryChild['name']?></span></a></td>
				<td><a href="/category/del/<?=$categoryChild['id']?>">(X)</a></td>
			</tr>
			<?php
		}
	}
	?>
</tbody></table>
