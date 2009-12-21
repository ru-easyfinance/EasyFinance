// {* $Id: category.js 113 2009-07-29 11:54:49Z ukko $ *}

$(document).ready(function() {
    var cat;
    var oldCatId = -1;

    $('.block2 .inside').css({width: '679px'});
    $('.block2 .inside .form_block').css({width: '353px'});

    easyFinance.models.category.load(res.category, function(model) {
        // Обновляем список системных категорий
        drawSystemCategoriesCombo();

        // Выводим список пользовательских и родительских категорий
        drawUserCategoriesList();
    });

    $('#add_form').hide();

    //BIND
    $('#btnSave').click(function(){
        saveCategory()
    });

    $('form').submit(function(){return false;});

    $('#btnCancel').click(function(){
        clearForm();$('#add_form').hide();
    });

    $('#add_category').click(function(){
        oldCatId = -1;
        $('#add_form').toggle();
        $('#categoryEditSystem').show();
        $('#divCategoryEditCustom').show();
        $('#subcat').removeAttr('disabled');
        $('form').attr('action','/category/add/');
    });

    $('#subcat').change(function(){
        // если создаём подкатегорию, то скрываем выбор системной категории
        // она будет наследоваться от родительской
        if ($(this).val() == "") {
            $('#divCategoryEditSystem').show();
        } else {
            $('#divCategoryEditSystem').hide();
        }
    });

    // При двойном клике
    $('div.line tr').live('dblclick', function() {
        $(this).find('li.edit').click();
    });
    $('li.edit').live('click',function(){
        clearForm();
        fillForm($(this).closest('tr,.line').attr('id').split("_", 2)[1]);
        $('#add_form').show();
        $(document).scrollTop(500);
        $('form').attr('action','/category/edit/');

    });
    $('li.del').live('click',function(){
        if (confirm('Удалить категорию?')) {
            delCategory($(this).closest('tr,.line').attr('id').split("_", 2)[1]);
        }
    });
    $('li.add').live('click',function(){
        clearForm();
        var catId = $(this).closest('tr,.line').attr('id').split("_", 2)[1];
        fillForm(catId);

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
        // тикет 425
        oldCatId = id;
        // тикеты 299, 389
        if (cat.user[id]['parent'] == "0") {
            $('#categoryEditSystem').show();
            $('#subcat').attr('disabled', true);
        } else {
            $('#categoryEditSystem').hide();
            $('#subcat').removeAttr('disabled');
        }

        if (cat.user[id]['custom'] == "0")
            $('#divCategoryEditCustom').hide();
        else
            $('#divCategoryEditCustom').show();

        $('#cat_id').val(cat.user[id]['id']);
        $('#namecat').val(cat.user[id]['name']);
        $('#subcat').val(cat.user[id]['parent']);
        $('#cattype').val(cat.user[id]['type']);
        $('#catsys').val(cat.user[id]['system']);
        
        if (cat.user[id]['parent'] != "0") {
            $('#divCategoryEditSystem').hide();
        } else {
            $('#divCategoryEditSystem').show();
        }
    }

    /**
     * Очищает форму
     */
    function clearForm() {
        $('#namecat,#cat_id').val('');
        $('#subcat,#cattype,#catsys').removeAttr('selected');
        $('#add_form').find('#btnSave').removeAttr('disabled');
    }

    /**
     * Проверяет валидность заполненных данных
     */
    function cat_checkForm() {
        var name = $('#namecat').val();
        if (name == '') {
            $.jGrowl("Необходимо указать название категории", {theme: 'red', life: 5000});
            return false;
        }

        if (name.indexOf('<') != -1 || name.indexOf('>') != -1) {
            $.jGrowl("Название категории не должно содержать символов < и >!", {theme: 'red', life: 5000});
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
        var system = easyFinance.models.category.getSystemCategories()[cat.system];

        $('<div class="line open" id="category_'+cat.id+'" style="width:496px"><div class="l_n_cont">'
            +'<a class="name">'+cat.name+'</a>'
            +'<div class="icon"><img src="/img/i/icoCatType'+cat.type+'.gif" title="'+["Расходная", "Универсальная", "Доходная"][parseInt(cat.type)+1]+'"/></div><div class="system">'+system.name+'</div>'
                +'<div class="cont">'
                    +'<ul class="ul_head" style="z-index:100; right:0px">'
                        +'<li class="edit"><a class="cat" title="Редактировать">Редактировать</a></li>'
                        +'<li class="del"><a class="cat" title="Удалить">Удалить</a></li>'
                        +'<li class="add"><a class="cat" title="Добавить">Добавить</a></li>'
                    +'</ul></div>'
                +'</div></div>').appendTo('div.categories');
    }

    function listInsertChildCategory(cat, afterNode){
        var parent = easyFinance.models.category.getUserCategories()[cat.parent];
        var system = easyFinance.models.category.getSystemCategories()[parent.system];

        var pr = cat['parent'];

        if (cat['type'] > 0) { // Доходная
            ct ='<div class="t3" title="Доходная">Доходная</div>';
        } else if (cat['type'] < 0) { // Расходная
            ct ='<div class="t1" title="Расходная">Расходная</div>';
        } else { //Универсальная
            ct ='<div class="t2" title="Универсальная">Универсальная</div>';
        }

        if ($('#category_'+pr+' table').length == 0) {
            $('<table>').appendTo($('#category_'+pr));
        }

        var strAppend = '<tr id="category_'+cat.id+'">'
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
                        +'<li class="add"><a title="Добавить">Добавить</a></li></ul>'
                +'</div>'
            +'</td></tr>';

        if (afterNode)
            $(afterNode).after(strAppend);
        else
            $('.categories #category_'+pr+' table').append(strAppend);
    }

    function updateCategory(oldCat, newCat) {
        if (oldCat.parent == "0" || oldCat.parent == "") {
            // родительская категория
            // обновляем название "по месту"
            // остальные изменения внешне не отображаются
            $('#category_' + oldCat.id + ' a:first').text(newCat.name);
        } else {
            // дочерняя категория
            
            // Удаляем старую версию категории из списка
            $('#category_'+oldCat.id).remove();

            // Вставляем обновлённую версию категории в конец списка
            listInsertCategory(newCat);
        }
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
            var id = $('#cat_id').val();
            var name = $('#namecat').val();
            var subcat = $('#subcat').val();
            var type = $('#cattype').val();
            var sys = $('#catsys').val();

            var oldCat;

            if (oldCatId != -1)
                oldCat = $.extend({}, easyFinance.models.category.getUserCategories()[oldCatId]);

            if (subcat =="") {
                // родительская категория
                
                if (oldCatId != -1) {
                    // делаем проверки при редактировании
                    var children = easyFinance.models.category.getChildrenByParentId(id);

                    if (type!=oldCat.type && type!=0 && children.length > 0) {
                        // делаем проверки, если тип категории меняется на
                        // доходный или расходный и у неё есть подкатегории

                        // запрещаем делать категорию доходной или расходной,
                        // если у неё есть подкатегории другого типа
                        var stop = false;
                        for (var key in children) {
                            if (children[key]['type'] != type)
                                stop = true;
                        }

                        if (stop) {
                            var strAlert = 'Вы не можете сделать эту категорию '
                                + (type==1 ? 'доходной' : 'расходной')
                                + ',\nпотому что она содержит '
                                + (type==1 ? 'расходные' : 'доходные') + ' подкатегории';

                            alert(strAlert);
                            return;
                        }
                    }
                }
            } else {
                // подкатегория
                var newParent = easyFinance.models.category.getUserCategories()[subcat];

                if (oldCatId != -1 && type != oldCat.type && newParent.type != 0 && newParent.type != type) {
                    // при изменении типа подкатегории
                    // ЗАПРЕТИТЬ. тикет 389

                    var strType = "расходную";
                    if (type == 1)
                        strType = "доходную"
                    else if (type == 0)
                        strType = "универсальную";

                    var strPrompt = 'Невозможно поместить '
                        + strType + ' подкатегорию в '
                        + (type==1 ? "расходную" : "доходную") + ' категорию.';
                    
                    alert(strPrompt);

                    return;
                }
                
                //if (oldCatId != -1 && subcat != oldCat.subcat) {
                // проверяем совпадение типов новой категории и подкатегории
                if (newParent.type != 0 && newParent.type != type) {
                    // пользователь пытается поместить
                    // доходную подкатегорию в расходную
                    // или расходную подкатегорию в доходную
                    // ЗАПРЕТИТЬ. тикет 389

                    var strType = "расходную";
                    if (type == 1)
                        strType = "доходную"
                    else if (type == 0)
                        strType = "универсальную";

                    var strPrompt = 'Невозможно поместить '
                        + strType + ' подкатегорию в '
                        + (type==1 ? "расходную" : "доходную") + ' категорию.';

                    alert(strPrompt);

                    return;
                }
            }

            var done = function(cat) {
                    $('#btnSave').removeAttr('disabled');

                    $('#add_form').find('#namecat').val('');
                    $('#add_form').find('#btnSave').removeAttr('disabled');
                    $('#add_form').hide();
                    $.jGrowl("Категория успешно сохранена", {theme: 'green'});
                    
                    if (act == '/category/add/') {
                        // категория была добавлена
                        listInsertCategory(cat);
                        $('#subcat').val('');
                    } else {
                        // категория была отредактирована
                        updateCategory(oldCat, cat);
                    }

                    // Обновляем список родительских категорий
                    if (cat.parent == "")
                        drawParentCategoriesCombo();

                    // Обновляем список категорий в диалоге "Добавление операции"
                    $('#op_type').change();
            }

            $.jGrowl("Категория сохраняется", {theme: 'green'});

            $('#btnSave').attr('disabled', true);
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
            $('#category_'+id).remove();

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