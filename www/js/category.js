// {* $Id: category.js 113 2009-07-29 11:54:49Z ukko $ *}

// @todo: оптимизировать редактирование категории
// @todo: сейчас в этом случае перерисовываются все категории

$(document).ready(function() {
    var cat;

    $('.block2 .inside').css({width: '679px'});
    $('.block2 .inside .form_block').css({width: '353px'});

    easyFinance.models.category.load(function(data) {
        // Обновляем список системных категорий
        drawSystemCategoriesCombo();

        // Выводим список пользовательских и родительских категорий
        drawUserCategoriesList();
    });

    $('#add_form').hide();

    //BIND
    $('#btnSave').click(function(){
        $(this).attr('disabled', true);
        saveCategory()
    });

    $('form').submit(function(){return false;});

    $('#btnCancel').click(function(){
        clearForm();$('#add_form').hide();
    });

    $('#add_category').click(function(){
        $('#add_form').toggle();
        $('form').attr('action','/category/add/');
    });
    // При наведении мыши
//    $('div.line tr').live('mouseover', function() {
//        $(this).addClass('act').find('ul').show();
//
//    });
    // При двойном клике
    $('div.line tr').live('dblclick', function() {
        $(this).find('li.edit').click();
    });
//    $('div.line tr').live('mouseout', function() {
//        $('div.line ul').hide();
//        $('div.line tr').removeClass('act');
//    });
    $('li.edit').live('click',function(){
        clearForm();
        fillForm($(this).closest('tr,.line').attr('id'));
        $('#add_form').show();
        $(document).scrollTop(500);
        $('form').attr('action','/category/edit/');

    });
    $('li.del').live('click',function(){
        if (confirm('Удалить категорию?')) {
            delCategory($(this).closest('tr,.line').attr('id'));
        }
    });
    $('li.add').live('click',function(){
        clearForm();
        fillForm($(this).closest('tr,.line').attr('id'));
        $('#cat_id').val('');
        $('#add_form').show();
        $(document).scrollTop(300);
        $('form').attr('action','/category/add/');
    });
    $('a.name').live('click',function(){
        $(this).closest('div.line').toggleClass('open').toggleClass('close');
    });

    /**
     * Заполняет форму значениями
     * @param id
     */
    function fillForm(id) {
        if (cat.user[id]['parent'] == "0") {
            $('#subcat').attr('disabled', 'disabled');
        } else {
            $('#subcat').removeAttr('disabled');
        }
        $('#cat_id').val(cat.user[id]['id']);
        $('#namecat').val(cat.user[id]['name']);
        $('#subcat').val(cat.user[id]['parent']);
        $('#cattype').val(cat.user[id]['type']);
        $('#catsys').val(cat.user[id]['system']);
    }

    /**
     * Очищает форму
     */
    function clearForm() {
        $('#namecat,#cat_id').val('');
        $('#subcat,#cattype,#catsys').removeAttr('selected');
    }

    /**
     * Проверяет валидность заполненных данных
     */
    function cat_checkForm() {
        if ($('#namecat').val() == '') {
            $.jGrowl("Необходимо указать название категории", {theme: 'red', life: 5000});
            return false;
        }
        return true;
    }

    // Заполняем список системных категорий
    function drawSystemCategoriesCombo() {
        var system = easyFinance.models.category.getSystemCategories();

        var sys = '';
        for(id in system) {
            sys += '<option value="'+system[id]['id']+'">'+system[id]['name']+'</option>';
        }$('#catsys').empty().append(sys);
    }

    // Заполняем список родительских категорий
    function drawParentCategoriesCombo() {
        var user = easyFinance.models.category.getUserCategories();

        var sorted = [];
        for(id in user) {
            if (user[id]['parent'] == 0)
                sorted.push (user[id]);
        }
        sorted.sort(function(a,b){return a.name.localeCompare(b.name)});

        var m='<option value=""> --- </option>';
        for(key in sorted) {
            m += '<option value="'+sorted[key]['id']+'">'+sorted[key]['name']+'</option>';
        }
        
        $('#subcat').html(m);
    }
    
    function listInsertCategory(cat){
        if (cat.parent == 0) {
            listInsertParentCategory(cat);
        } else {
            listInsertChildCategory(cat);
        }
    }

    function listInsertParentCategory(cat){
        $('<div class="line open" id="'+cat.id+'" style="width:496px"><div class="l_n_cont"><a class="name">'
            +cat.name+'</a>'
                +'<div class="cont">'
                    +'<ul class="ul_head" style="z-index:100; right:0px">'
                        +'<li class="edit"><a class="cat" title="Редактировать">Редактировать</a></li>'
                        +'<li class="del"><a class="cat" title="Удалить">Удалить</a></li>'
                        +'<li class="add"><a class="cat" title="Добавить">Добавить</a></li>'
                    +'</ul></div>'
                +'</div></div>').appendTo('div.categories');
    }

    function listInsertChildCategory(cat){
        var system = easyFinance.models.category.getSystemCategories()[cat.system];

        var pr = cat['parent'];

        if (cat['type'] > 0) { // Доходная
            ct ='<div class="t3" title="Доходная">Доходная</div>';
        } else if (cat['type'] < 0) { // Расходная
            ct ='<div class="t1" title="Расходная">Расходная</div>';
        } else { //Универсальная
            ct ='<div class="t2" title="Универсальная">Универсальная</div>';
        }

        if ($('#'+pr+' table').length == 0) {
            $('<table/>').appendTo($('#'+pr));
        }

        $('.categories #'+pr+' table').append(
            '<tr id="'+cat.id+'">'
            +'<td class="w1">'
                +'<a>'+cat['name']+'</a>'
            +'</td>'
            +'<td class="w2">'
                + ct
            +'</td>'
            +'<td class="w3">'+system['name']
            +'</td>'
            +'<td class="w4">'
                +'<div class="cont">'
//                                +'<b>500 руб.</b>'
//                                +'<div class="indicator">'
//                                    +'<div style="width: 10%;">'
//                                        +'<span>10%</span>'
//                                    +'</div>'
//                                +'</div>'
                    +'<ul style="z-index:100; right: -10px;"><li class="edit"><a title="Редактировать">Редактировать</a></li>'
                        +'<li class="del"><a title="Удалить">Удалить</a></li>'
                        +'<li class="add"><a title="Добавить">Добавить</a></li></ul></div>'
            +'</td></tr>'
        );
    }


     /**
     * Выводит таблицу пользовательских категорий
     */
    function drawUserCategoriesList() {
            var data = easyFinance.models.category.getAllCategories();

            cat = data;
            //$('div.categories div').remove('div');
            $('div.categories').empty();

            // Обновляем список родительских категорий
            drawParentCategoriesCombo(data.user);

            var id,pr,ct;
            //var p=[];
            //$('.categories #table').append('<table>');
            for(id in data.user) {
                listInsertCategory(data.user[id]);
            }
    }
    /**
     *slide menu del edit
     */
//    $(".l_n_cont").live('mouseover',function(){
//        $(this).closest('.line').find(".ul_head").show();
//    });
    //$(".l_n_cont").live('mouseout',function(){$(this).closest('.line').find("ul").hide()});
    //('.line').find("ul").show();
    $('div.line tr,div.l_n_cont').live('mouseover',function(){
        $('div.line tr,div.l_n_cont').removeClass('act').find('ul').hide();
        $(this).addClass('act').find('ul').show();
    });
    $('body').mousemove(function(){
            if (!$('ul:hover').length && !$('.act:hover').length)
            {
                $('div.line tr,div.l_n_cont').removeClass('act').find('ul').hide();
            }
    });

    /**
     * Сохраняет категорию
     */
    function saveCategory() {
        if (cat_checkForm()) {
            $.jGrowl("Категория сохраняется", {theme: 'green'});

            var id = $('#cat_id').val();
            var name = $('#namecat').val();
            var subcat = $('#subcat').val();
            var type = $('#cattype').val();
            var sys = $('#catsys').val();

            var done = function(cat) {
                    $('#add_form').find('#btnSave').attr('disabled', false);
                    $('#add_form').hide();
                    $.jGrowl("Категория успешно сохранена", {theme: 'green'});
                    
                    if (act == '/category/add/') {
                        // категория была добавлена
                        listInsertCategory(cat);
                    } else {
                        // категория была отредактирована
                        //drawUserCategoriesList();
                        // @ticket 156
                        // Удаляем старую версию категории из списка
                        $('#'+id).remove();
                        // Вставляем обновлённую версию категории
                        listInsertCategory(cat);
                    }

                    // Обновляем список родительских категорий
                    if (cat.parent == "")
                        drawParentCategoriesCombo();

                    // Обновляем список категорий в диалоге "Добавление операции"
                    $('#op_type').change();
            }

            var act = $('form').attr('action');
            if (act == '/category/add/')
                easyFinance.models.category.add(name, subcat, type, sys, done);
            else
                easyFinance.models.category.editById(id, name, subcat, type, sys, done);
        }
    }

    /**
     * Удаляет категорию
     * @param id
     */
    function delCategory(id) {
        var isParent = easyFinance.models.category.isParentCategory(id);
        
        easyFinance.models.category.deleteById(id, function() {
            // Удаляем категорию из списка
            // @todo: optimize! use _$node.find
            $('#'+id).remove();

            // Обновляем список родительских категорий
            if (isParent == true)
                drawParentCategoriesCombo();

            // Обновляем список категорий в диалоге "Добавление операции"
            $('#op_type').change();
            
            clearForm();
            $('#add_form').hide();
            $.jGrowl("Категория удалена", {theme: 'green'});
        });
    }
});