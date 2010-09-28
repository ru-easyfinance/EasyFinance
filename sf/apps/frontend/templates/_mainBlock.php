<!--левая колонка-->
<?php include_partial('global/common/left.quick'); ?>

<div class="block2">
    <div class="l-indent">
    <?php include_partial('global/common') ?>

    <!--наполнение-->
    <?php echo $sf_content; ?>
    <!--/наполнение-->
    </div>
</div>
<!--правая колонка-->
<?php include_partial('global/rightColumn', array()) ?>