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
    if (!isset($selectbox)) {
        $render['wrapperClass'] .= ' no-selectbox';
    }
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

    $render['type'] = ' type="text"';
    if (isset($type)) {
        $render['type'] = " type=\"$type\"";
    }

    $render['disabled'] = '';
    if (isset($disabled)) {
        $render['disabled'] = ' disabled="disabled"';
    }

    $render['value'] = '';
    if (isset($value)) {
        $render['value'] = " value=\"$value\"";
    }

    $render['maxlength'] = '';
    if (isset($maxlength)) {
        $render['maxlength'] = " maxlength=\"$maxlength\"";
    }

    $render['jsparams'] = ''; // should be JSON string. Or whatever.
    if (isset($jsparams)) {
        $render['jsparams'] = " ondblclick=\"return $jsparams\"";
    }

    $render['selectid'] = '';
    if (isset($selectid)) {
        $render['selectid'] = " id=\"$selectid\"";
    }

    $render['selectbox'] = '';
    if (isset($selectbox)) {
        $render['selectbox'] = $selectbox;
    }
?>
<?php echo $render['label']; ?>
<div class="b-custom-input<?php echo $render['wrapperClass']; ?>">
    <div class="b-custom-input-border">
        <?php echo $render['placeholder']; ?>
        <input
            class="b-custom-input-field <?php echo $render['inputClass']; ?>"
            <?php echo $render['id'] . $render['name'] . $render['type'] . $render['disabled'] . $render['value'] . $render['maxlength']; ?>
            <?php echo $render['jsparams']; ?>
        />
        <?php if (isset($selectbox) || isset($empty_selectbox)) : ?>
            <div class="b-custom-select-wrap">
                <select class="b-custom-select" <?php echo $render['selectid'] . $render['disabled']; ?>>
                    <?php echo html_entity_decode($render['selectbox']); ?>
                </select>
            </div>
            <div class="b-custom-select-trigger"><i></i></div>
        <?php endif; ?>
    </div>
</div>
<?php echo $render['hint']; ?>