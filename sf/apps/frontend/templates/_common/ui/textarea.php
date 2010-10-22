<?php
    $render = array();

    $render['label'] = '';
    if (isset($label)) {
        $render['label'] = "<label class=\"b-custom-input-label\">$label</label>";
    }

    $render['placeholder'] = '';
    if (isset($placeholder) && $placeholder != 0) {
        $render['placeholder'] = "<em class=\"b-placeholder\">{$placeholder}</em>";
    }

    $render['hint'] = '';
    if (isset($hint)) {
        $render['hint'] = "<p class=\"b-custom-input-hint\">$hint</p>";
    }

    $render['wrapperClass'] = '';
    if (isset($class)) {
        $render['wrapperClass'] .= ' ' . $class;
    }
    if (isset($disabled)) {
        $render['wrapperClass'] .= ' ' . 'b-disabled';
    }

    $render['taClass'] = '';
    if (isset($taClass)) {
        $render['taClass'] = $taClass;
    }

    $render['id'] = '';
    if (isset($id)) {
        $render['id'] = " id=\"$id\"";
    }

    $render['name'] = '';
    if (isset($name)) {
        $render['name'] = " name=\"$name\"";

    }

    $render['disabled'] = '';
    if (isset($disabled)) {
        $render['disabled'] = ' disabled="disabled"';
    }

    $render['value'] = '';
    if (isset($value)) {
        $render['value'] = $value;
    }

    $render['jsparams'] = ''; // should be JSON string. Or whatever.
    if (isset($jsparams)) {
        $render['jsparams'] = " ondblclick=\"return $jsparams\"";
    }
?>
<?php echo $render['label']; ?>
<div class="b-custom-textarea<?php echo $render['wrapperClass']; ?>">
    <div class="b-custom-textarea-border">
        <?php echo $render['placeholder']; ?>
        <textarea class="b-custom-textarea-field <?php echo $render['taClass']; ?>" <?php echo $render['id'] . $render['name'] . $render['disabled'] ?>><?php echo $render['value'] ?></textarea>
    </div>
</div>
<?php echo $render['hint']; ?>