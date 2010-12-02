<div class="inside form">
    <div class="js-widget js-widget-importcsv b-importcsv">
        <p>
            <a href="<?php echo URL_ROOT_WIKI; ?>tiki-index.php?page=Import">Инструкция по импорту</a>
            </p>
        <form class="b-form-skeleton b-importcsv-form" action="/my/profile/import_csv" method="POST">
            <div class="b-row">
                <div class="b-col">
                    <div class="b-col-indent">
                        <?php include_partial('global/common/ui/upload', array('label' => "Выбрать файл", 'name' => 'data')); ?>
                    </div>
                </div>
            </div>
            <div class="b-row">
                <div class="b-col">
                    <div class="b-col-indent">
                        <?php include_partial('global/common/ui/simplebutton', array('value' => "Импортировать")); ?>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>