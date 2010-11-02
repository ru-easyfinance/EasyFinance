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

    $render['inputClass'] = '';
    if (isset($inputClass)) {
        $render['inputClass'] = $inputClass;
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

    $render['jsparams'] = ''; // should be JSON string. Or whatever.
    if (isset($jsparams)) {
        $render['jsparams'] = " ondblclick=\"return $jsparams\"";
    }

?>
<?php echo $render['label']; ?>
<div class="b-custom-input b-custom-input__borderless<?php echo $render['wrapperClass']; ?>">
    <div class="b-custom-input-border">
        <?php echo $render['placeholder']; ?>
        <input type="file" class="b-custom-input-upload" <?php echo $render['name'] . $render['disabled'] . $render['id'] ?>  />

    </div>
</div>
<?php echo $render['hint']; ?>