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
function str_split_php4_utf8($str) {
    // place each character of the string into and array
    $split=1;
    $array = array();
    for ( $i=0; $i < strlen( $str ); ){
        $value = ord($str[$i]);
        if($value > 127){
            if($value >= 192 && $value <= 223)
                $split=2;
            elseif($value >= 224 && $value <= 239)
                $split=3;
            elseif($value >= 240 && $value <= 247)
                $split=4;
        }else{
            $split=1;
        }
            $key = NULL;
        for ( $j = 0; $j < $split; $j++, $i++ ) {
            $key .= $str[$i];
        }
        array_push( $array, $key );
    }
    return $array;
}
//режим категории
function isGlasn($l){//возвращает является ли буква гласной
    $ret = false;
    if ( $l == 'а' || $l == 'е' || $l == 'и' || $l == 'о' || $l == 'у' || $l == 'ё' || $l == 'ы' ||
        $l == 'э' || $l == 'ю' || $l == 'я' )
    {
        $ret = true;
    }
    return $ret;
}
function minstring($name){
    $letters = str_split_php4_utf8($name);//буквы
    $name = '';
    $numberofGl = 0;//количество гласных
    foreach($letters as $k=>$v){
        if ( isGlasn($v) )
        {
            $numberofGl++;//счётчик гласных в слове
        }
        if ( $numberofGl == 3 ){//на третьей гласной
            $name .= '.';//ставим точку - типа сократили слово
            return $name.' ';//гласную не возвращаем
        }
        $name .= $v;
    }
    return $name.' ';
}
function cutCategory($catname){
    $min = $catname;
    if ( strlen($catname) > 50){//если имя категории больше 20
        $strings = explode(' ', $min);//разбиваем на слова
        $min = '';//то режем
        foreach ($strings as $k=>$v){
            if ((strlen($min)+strlen(minstring($v)))<50)//если с сокращённым именем длина укладывается
                $min .= minstring($v);//то добавляем
        }
    }
    //if ( $catname[ strlen($catname) - 1 ] == ',' ) $min .= ','; //пишем запятую если была
    return $min.' ';
}
?>
<table cellspacing="0" cellpadding="0" class="wide"><tbody>
	<tr>
		<td class="wide"><strong>Категории</strong> <a href="/category/add/<?=$categorysType?>">добавить</a></td>
		<td>&nbsp;</td>
		<td align="right">&nbsp;</td>
	</tr>
</tbody></table>
<div class="menu">
	<a href="/category/waste" class="<?=($categorysType == Category::TYPE_WASTE)?'current':''?>">расход</a> | 
	<a href="/category/profit" class="<?=($categorysType == Category::TYPE_PROFIT)?'current':''?>">доход</a> | 
	<a href="/category/universal" class="<?=($categorysType == Category::TYPE_UNIVERSAL)?'current':''?>">универсальные</a>
</div>
<br />
<table cellspacing="0" cellpadding="0" class="wide categories"><tbody>
	<?php
	$row = 1;
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
		<tr class="<?=($row % 2 == 1) ? 'odd' : 'even'?>">
			<td class="wide"><a href="/category/edit/<?=$category['id']?>" class="<?=$linkStyle?>">
			<?=$category['name']?></span></a></td>
			<td><a href="/category/del/<?=$category['id']?>" class="red">X</a></td>
		</tr>
		<?php
		$row++;
		// foreach по причине правильного переключения указателей массива
		foreach ( $res['category']['user'] as $categoryChild )
		{
			if( $categoryChild['parent'] != $category['id'] )
			{
				continue;
			}
			?>
			<tr class="<?=($row % 2 == 1) ? 'odd' : 'even'?>">
				<td class="wide childCategory"><a href="/category/edit/<?=$categoryChild['id']?>" class="<?=$linkStyle?>">
				<?=cutCategory($categoryChild['name'])?></span></a></td>
				<td><a href="/category/del/<?=$categoryChild['id']?>" class="red">X</a></td>
			</tr>
			<?php
			$row++;
		}
	}
	?>
</tbody></table>
