<?php
$render = array();

$render['className'] = 'b-button-simple';
if (isset($class)) {
    $render['className'] .= ' ' . $class;
}

$render['type'] = 'submit';
if (isset($type)) {
    $render['type'] = $type;
}

$render['value'] = 'Отправить';
if (isset($value)) {
    $render['value'] = $value;
}

$render['id'] = ''; // left only for compatibility with old code
if (isset($id)) {
    $render['id'] = " id=\"$id\"";
}
?>

<input class="<?php echo $render['className']?>" type="<?php echo $render['type']?>" value="<?php echo $render['value']?>" <?php echo $render['id']?>/>