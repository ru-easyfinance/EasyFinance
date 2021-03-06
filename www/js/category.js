// {* $Id: category.js 113 2009-07-29 11:54:49Z ukko $ *}

$(document).ready(function() {
    var cat;
    var oldCatId = -1;

    //$('.block2 .inside').css({width: '679px'});
    $('.block2 .inside .form_block').css({width: '353px'});

    easyFinance.models.category.load(res.category, function() {
        // Обновляем список системных категорий
        drawSystemCategoriesCombo();

        // Выводим список пользовательских и родительских категорий
        drawUserCategoriesList();

        $(document).bind('categoriesLoaded', drawUserCategoriesList);
    });

    $('#add_form').hide();

    //BIND
    $('#btnSave').click(function(){
        saveCategory()
    });

    $('form').submit(function(){return false;});

    $('#btnCancel').click(function(){
        clearForm();
        $('#add_form').hide();
    });

    $('#add_category').click(function(){
        oldCatId = -1;
        $('#add_form').show();
        $('#categoryEditSystem').show();
        $('#divCategoryEditCustom').show();
        $('#namecat').val('');
        $('#subcat').removeAttr('disabled');
        $('#cattype').removeAttr('disabled');
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
    $('.categories div.line tr').live('dblclick', function() {
        $(this).find('li.edit').click();
    });
    $('.categories li.edit').live('click',function(){
        clearForm();
        fillForm($(this).closest('tr,.line').attr('id').split("_", 2)[1]);

        $('#add_form').show();
        $(document).scrollTop(250);
        $('form').attr('action','/category/edit/');

    });
    $('.categories li.del').live('click', function() {
        var el = $(this);
        $("#categoryDeletionConfirm").dialog({
            autoOpen: false,
            title: "Удалить или скрыть?",
            modal: true,
            buttons: {
                "Удалить": function() {
                    delCategory(el.closest('tr, .line').attr('id').split("_", 2)[1]);
                    $(this).dialog('close');
                },
                "Отмена": function() {
                    $(this).dialog('close');
                }
            }
        });
        $("#categoryDeletionConfirm").dialog("open");
    });
    $('.categories li.add').live('click',function(){
        clearForm();
        fillForm($(this).closest('tr,.line').attr('id').split("_", 2)[1]);

        $('#categoryEditSystem').show();
        $('#divCategoryEditCustom').show();
        $('#cat_id').val('');
        $('#subcat').removeAttr("disabled");
        $('#cattype').removeAttr("disabled");

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

        if (cat.user[id]['custom'] == "0") {
            $('#divCategoryEditCustom').hide();
            $('#cattype').attr("disabled", true);
            $('#txtCategoryComment').text ('Это системная категория, вы не можете изменить её тип');
        } else {
            $('#divCategoryEditCustom').show();
            $('#cattype').removeAttr("disabled");
            $('#txtCategoryComment').text ('');
        }

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
        $('#cattype').removeAttr("disabled");
        $('#txtCategoryComment').text ('');
    }

    /**
     * Проверяет валидность заполненных данных
     */
    function cat_checkForm() {
        var name = $('#namecat').val();
        if (name == '') {
            $.jGrowl("Необходимо указать название категории", {theme: 'red', life: 2500});
            return false;
        }

        return true;
    }

    // Заполняем список системных категорий
    function drawSystemCategoriesCombo() {
        var system = easyFinance.models.category.getSystemCategories();

        var sys = '', id;
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

        $('<div class="line open" id="category_'+cat.id+'"><div class="l_n_cont">'
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
        if (!parent){
            return ;
        }
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
            // обновляем название, иконку и сис. категорию "по месту"
            var $cat = $('#category_' + oldCat.id);
            // обновляем название
            $cat.find('a:first').text(newCat.name);
            // обновляем иконку
            $cat.find('img').attr('src', '/img/i/icoCatType'+newCat.type+'.gif');
            // обновляем системную категорию для родителей и детей
            var sysName = easyFinance.models.category.getSystemCategories()[newCat.system].name;
            $cat.find('.system').text(sysName);
            $cat.find('td.w3').text(sysName);
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
            cat = data; // не удалять! нужно для редактирования
            var order = easyFinance.models.category.getUserCategoriesKeysOrderedByName();

            // Обновляем список родительских категорий
            $('div.categories').empty();
            if (data.user){
                drawParentCategoriesCombo(data.user);

                // пробегаемся сначала по родительским категориям,
                // чтобы потом было куда добавлять подкатегории
                for(var row in order) {
                    if (data.user[order[row]].parent == 0) {
                        listInsertCategory(data.user[order[row]]);
                    }
                }

                // добавляем подкатегории
                for(var row in order) {
                    if (data.user[order[row]].parent != 0) {
                        listInsertCategory(data.user[order[row]]);
                    }
                }
            }
    }

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
            var type = $('#cattype').val();
            var sys = $('#catsys').val();

            // #881. Google Chrome возвращает null, если поле скрыто
            var subcat = $('#subcat').val();
            if (subcat == null)
                subcat = '';

            var oldCat;
            var strType = '';
            var strPrompt = '';

            if (oldCatId != -1)
                oldCat = $.extend({}, easyFinance.models.category.getUserCategories()[oldCatId]);

            if (subcat == "") {
                // родительская категория
                if (sys == "" || sys == 0) {
                    alert("Укажите системную категорию!");
                    return;
                }

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
                sys = newParent.system;

                if (oldCatId != -1 && type != oldCat.type && newParent.type != 0 && newParent.type != type) {
                    // при изменении типа подкатегории
                    // ЗАПРЕТИТЬ. тикет 389

                    strType = "расходную";
                    if (type == 1)
                        strType = "доходную"
                    else if (type == 0)
                        strType = "универсальную";

                    strPrompt = 'Невозможно поместить '
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

                    strType = "расходную";
                    if (type == 1)
                        strType = "доходную"
                    else if (type == 0)
                        strType = "универсальную";

                    strPrompt = 'Невозможно поместить '
                        + strType + ' подкатегорию в '
                        + (type==1 ? "расходную" : "доходную") + ' категорию.';

                    alert(strPrompt);

                    return;
                }
            }

            // Jet: для исправления бага #848.
            // для некоторых категорий может быть не указана
            // системная категория (из-за глюков или просто так сделано в базе)
            if (sys == "0" || sys=="")
                sys = "1";

            var done = function(data) {
                $('#btnSave').removeAttr('disabled');

                if (data.error && data.error.text) {
                        $.jGrowl(data.error.text, {theme: 'red'});
                        return false;
                } else {
                    $('#add_form').find('#namecat').val('');
                    $('#add_form').find('#btnSave').removeAttr('disabled');
                    $('#add_form').hide();
                    $.jGrowl("Категория успешно сохранена", {theme: 'green'});

                    if (act == '/category/add/') {
                        // категория была добавлена
                        listInsertCategory(data.category);
                        $('#subcat').val('');
                    } else {
                        // категория была отредактирована
                        updateCategory(oldCat, data);
                    }

                    // Обновляем список родительских категорий
                    if (data.parent == "")
                        drawParentCategoriesCombo();

                    // Обновляем список категорий в диалоге "Добавление операции"
                    $('#op_type').change();
                }
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

    function _delCallback(data) {
        if (data) {
            if (data.result) {
                // Удаляем категорию из списка
                $('#category_' + data.result.id).remove();

                // если удаляли родительскую категорию,
                // обновляем список родительских категорий
                if (easyFinance.models.category.isParentCategory(data.result.id) == true) {
                    drawParentCategoriesCombo();
                }

                // Обновляем список категорий в диалоге "Добавление операции"
                $('#op_type').change();

                clearForm();
                $('#add_form').hide();
                $.jGrowl(data.result.text, {theme: 'green'});
            } else if (data.confirm) {
                if (confirm(data.confirm.text)) {
                    // подтвердили удаление категории
                    easyFinance.models.category.deleteById(data.confirm.id, true, _delCallback);
                }
            } else if (data.error) {
                $.jGrowl(data.error.text, {theme: 'red'});
            }
        }
    }

    /**
     * Удаляет категорию
     * @param id
     */
    function delCategory(id) {
        easyFinance.models.category.deleteById(id, false, _delCallback);
    }
});
