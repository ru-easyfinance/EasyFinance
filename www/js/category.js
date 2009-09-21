// {* $Id: category.js 113 2009-07-29 11:54:49Z ukko $ *}
$(document).ready(function() {
    var cat;

    loadCategory();
    $('#add_form').hide();

    //BIND
    $('#btnSave').click(function(){
        saveCategory()
    });
    $('#btnCancel').click(function(){
        clearForm();$('#add_form').hide();
    });
    $('#add_category').click(function(){
        $('#add_form').toggle();
        $('form').attr('action','/category/add/');
    });
    // При наведении мыши
    $('div.line tr').live('mouseover', function() {
        $(this).addClass('act').find('ul').show();

    });
    // При двойном клике
    $('div.line tr').live('dblclick', function() {
        $(this).find('li.edit').click();
    });
    $('div.line tr').live('mouseout', function() {
        $('div.line ul').hide();
        $('div.line tr').removeClass('act');
    });
    $('li.edit').live('click',function(){
        clearForm();
        fillForm($(this).closest('tr,.line').attr('id'));
        $('#add_form').show();
        (document).scrollTop(500);
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
        $('#id').val('');
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
        if (parseInt(cat.user[id]['type']) == -1) {
            $('#subcat').attr('disabled', 'disabled');
        } else {
            $('#subcat').removeAttr('disabled');
        }
        $('#id').val(cat.user[id]['id']);
        $('#namecat').val(cat.user[id]['name']);
        $('#subcat').val(cat.user[id]['parent']);
        $('#cattype').val(cat.user[id]['type']);
        $('#catsys').val(cat.user[id]['system']);
    }

    /**
     * Очищает форму
     */
    function clearForm() {
        $('#namecat,#id').val('');
        $('#subcat,#cattype,#catsys').removeAttr('selected');
    }

    /**
     * Проверяет валидность заполненных данных
     */
    function checkForm() {
        return true;
    }

    /**
     * Загружает пользовательские и системные категории
     */
    function loadCategory() {
        $.get('/category/getCategory/', '',function(data) {

            cat = {};
            cat = data;
            $('div.categories div').remove('div');
            // Обновляем системные категории
            sys = '';
            for(var id in data.system) {
                sys += '<option value="'+data.system[id]['id']+'">'+data.system[id]['name']+'</option>';
            }$('#catsys').empty().append(sys);

            // Обновляем список категорий
            m='<option value=""> --- </option>';p=[];
            
            for(var id in data.user) {
                // Если это родительская категория
                if (data.user[id]['parent'] == 0) {
                    m += '<option value="'+data.user[id]['id']+'">'+data.user[id]['name']+'</option>'; // Заполняем список родительских категорий
                    p[id] = $('<div class="line open" id="'+id+'"><div class="l_n_cont"><a class="name">'
                    +data.user[id]['name']+'</a>'
                                        +'<div class="cont">'
                                            +'<ul class="ul_head">'
                                                +'<li class="edit"><a class="cat" title="Редактировать">Редактировать</a></li>'
                                                +'<li class="del"><a class="cat" title="Удалить">Удалить</a></li>'
                                                +'<li class="add"><a class="cat" title="Добавить">Добавить</a></li>'
                                            +'</ul></div>'
                                        +'</div></div>').appendTo('div.categories');
                } else {
                    pr = data.user[id]['parent'];

                    if (data.user[id]['type'] > 0) { // Доходная
                        ct ='<div class="t3" title="Доходная">Доходная</div>';
                    } else if (data.user[id]['type'] < 0) { // Расходная
                        ct ='<div class="t1" title="Расходная">Расходная</div>';
                    } else { //Универсальная
                        ct ='<div class="t2" title="Универсальная">Универсальная</div>';
                    }

                    if ($('#'+pr+' table').length == 0) {
                        $('<table/>').appendTo($('#'+pr));
                    }
                    $('.categories #'+pr+' table').append(
                        '<tr id="'+id+'">'
                        +'<td class="w1">'
                            +'<a>'+data.user[id]['name']+'</a>'
                        +'</td>'
                        +'<td class="w2">'
                            + ct
                        +'</td>'
                        +'<td class="w3">'+data.system[data.user[id]['system']]['name']
                        +'</td>'
                        +'<td class="w4">'
                            +'<div class="cont">'
//                                +'<b>500 руб.</b>'
//                                +'<div class="indicator">'
//                                    +'<div style="width: 10%;">'
//                                        +'<span>10%</span>'
//                                    +'</div>'
//                                +'</div>'
                                +'<ul><li class="edit"><a title="Редактировать">Редактировать</a></li>'
                                    +'<li class="del"><a title="Удалить">Удалить</a></li>'
                                    +'<li class="add"><a title="Добавить">Добавить</a></li></ul></div>'
                        +'</td></tr>'
                    );
                }
            }
            //$('div.categories').append(c);
            $('#subcat').html(m);
			$(".l_n_cont").live('mouseover',function(){
				$(this).closest('.line').find(".ul_head").show();
			});
			$(".l_n_cont").live('mouseout',function(){$(this).closest('.line').find("ul").hide()});
        }, 'json');
    }

    /**
     * Сохраняет категорию
     */
    function saveCategory() {
        if (checkForm()) {
            $.post($('form').attr('action'), {
                id     : $('#id').val(),
                name   : $('#namecat').val(),
                parent : $('#subcat').val(),
                type   : $('#cattype').val(),
                system : $('#catsys').val()
            }, function() {
                $('#add_form').hide();
                loadCategory();
            }, 'json');
        }
    }

    /**
     * Удаляет категорию
     * @param id
     */
    function delCategory(id) {
        $.post('/category/del/', {id:id}, function() {
            clearForm();
            $('#add_form').hide();
            loadCategory();
        }, 'json');
    }
	

	
	    /* Show/ Hide Mini-Menu For List Container */
    //$(".l_n_cont").hover(
    //        function(){$(this).next().find("ul").show();},
    //        function(){$(this).next().find("ul").hide();}
    //        );
    //$(".cont ul").hover(
    //    function(){$(this).show();},
    //    function(){$(this).hide();}
    //    );
});