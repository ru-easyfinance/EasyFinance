<?php

$typesArray = array(
    Category::TYPE_WASTE        => 'Расходная',
    Category::TYPE_PROFIT       => 'Доходная',
    Category::TYPE_UNIVERSAL    => 'Универсальная'
);

?>
<strong><?php echo (isset($category['id']))?'Редактирование категории "' . $category['name'] . '"' : 'Добавление категории';?></strong>
<br />
<div class="menu">
    <a href="/category/add/waste" class="<?php echo ($category['type'] == Category::TYPE_WASTE)?'current':''?>">расход</a> | 
    <a href="/category/add/profit" class="<?php echo ($category['type'] == Category::TYPE_PROFIT)?'current':''?>">доход</a> | 
</div>
<?php
// Вывод сообщений
if( isset($error) && is_array($error) && array_key_exists( 'text', $error ) )
{
    ?><p style="color:red; font-weight:bold; font-size: 12px;"><?php echo $error['text']?></p><?php
}
if( isset($result) && is_array($result) && array_key_exists('text', $result) )
{
    ?><p style="color:green; font-weight:bold; font-size: 12px;"><?php echo $result['text']?></p><?php
    $category['name'] = null;
}
?>
<form method="POST">
<div class="wide">
    <input type="hidden" name="type" value="<?php echo $category['type']?>"></input>
    
    <div class="line"><span class="asterisk">*</span> Название: <br />
        <input class="wide" name="name" value="<?php echo isset($category['name'])?htmlspecialchars($category['name']):''?>" />
    </div>  
    <div class="line"><span class="asterisk">*</span> Родительская категория:<br><select name="parent" class="wide">
        <option value="0">Без родительской</option>
    <?php
    while( list(,$userCategory) = each($res['category']['user']) )
    {
        if( $category[''] )
        ?><option id="<?php echo $userCategory['id']?>"
        ><?php echo $userCategory['name']?></option>
        <?php
    }
    ?>
    </select></div>
    <div class="line"><span class="asterisk">*</span> Системная категория:<br><select name="system" class="wide">
    <?php
    while( list(,$sysCategory) = each($res['category']['system']) )
    {
        ?><option value="<?php echo $sysCategory['id']?>"
        <?php echo (isset($category['system']) && $category['system'] == $sysCategory['id'])?'selected="selected"':''?>
        ><?php echo $sysCategory['name']?></option>
        <?php
    }
    ?>
    </select></div>
    
    
    

    <?
    if (isset($category['id'])) {
    ?>
        <table class="wide" cellspacing="2" cellpadding="0"><tbody><tr>
            <td class="wide"><input id="btnSave" type="submit" style="width:100%" value="Изменить"></td>
            <td>&nbsp;&nbsp;&nbsp;<a href="/category/del/<?php echo $category['id']?>" class="red">удалить</a></td>
        </tbody></table>
    <?
    } else {
    ?>
        <input id="btnSave" type="submit" style="width:100%" value="Сохранить">
    <?
    }
    ?>
</div>
</form>
