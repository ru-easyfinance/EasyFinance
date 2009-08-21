// {* $Id: category.js 113 2009-07-29 11:54:49Z ukko $ *}
$(document).ready(function() {
    // TRUSTED
    var cat;

    loadCategory();
    $('#add_form').hide();

    //BIND
    $('#btnSave').click(function(){
        saveCategory()
    });
    $('#btnCancel').click(function(){
        clearForm(); $('#add_form').hide();
    });
    $('#add_category').click(function(){
        $('#add_form').toggle();
        $('form').attr('action','/category/add/');
    });
    // При наведении мыши
    $('div.line tr').live('mouseover', function() {
        $(this).addClass('act').find('ul').show();

    });
    $('div.line tr').live('mouseout', function() {
        $('div.line ul').hide();
        $('div.line tr').removeClass('act');
    });
    $('li.edit').live('click',function(){
        clearForm();
        fillForm($(this).closest('tr').attr('id'));
        $('#add_form').show();
        $(document).scrollTop(300);
        $('form').attr('action','/category/edit/');

    });
    $('li.del').live('click',function(){
        if (confirm('Удалить категорию?')) {
            delCategory($(this).closest('tr').attr('id'));
        }
    });
    $('li.add').live('click',function(){
        clearForm();
        fillForm($(this).closest('tr').attr('id'));
        $('#id').val('');
        $('#add_form').show();
        $(document).scrollTop(300);
        $('form').attr('action','/category/add/');
    });

    /**
     * Заполняет форму значениями
     * @param id
     */
    function fillForm(id) {
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
            sys = '';
            for(var id in data.system) {
                sys += '<option value="'+data.system[id]['id']+'">'+data.system[id]['name']+'</option>';
            }
            $('#catsys').empty().append(sys);
            c=s=m='';
            for(var id in data.user) {
                if (data.user[id]['parent'] == 0) {
                    m += '<option value="'+data.user[id]['id']+'">'+data.user[id]['name']+'</option>';
                    if (c != '') {
                        if (s != '') {
                            c = c + s + '</table></div><div class="line open" id="'+id+'">';
                        } else {
                            c += '</div><div class="line open" id="'+id+'">';
                        }
                        s = '';
                    } else {
                        c += '<div class="line open" id="'+id+'">';
                    }
                    c += '<a class="name" href="#">'+data.user[id]['name']+'</a>';
                } else {
                    if (s == '') s += '<table>';
                    s += '<tr id="'+id+'">'
                        +'<td class="w1">'
                            +'<a href="#">'+data.user[id]['name']+'</a>'
                        +'</td>'
                        +'<td class="w2">';
                            if (data.user[id]['type'] > 0) { // Доходная
                                s +='<div class="t3" title="Доходная">Доходная</div>';
                            } else if (data.user[id]['type'] < 0) { // Расходная
                                s +='<div class="t1" title="Расходная">Расходная</div>';
                            } else { //Универсальная
                                s +='<div class="t2" title="Универсальная">Универсальная</div>';
                            }
                        s +='</td>'
                        //
                        +'<td class="w3">'+data.system[data.user[id]['system']]['name']+'</td>'
                        +'<td class="w4">'

                            +'<div class="cont"><b>500 руб.</b>'
                                +'<div class="indicator">'
                                    +'<div style="width: 10%;">'
                                        +'<span>10%</span>'
                                    +'</div>'
                                +'</div>'
                                +'<ul><li class="edit"><a title="Редактировать">Редактировать</a></li><li class="del"><a title="Удалить">Удалить</a></li><li class="add"><a title="Добавить">Добавить</a></li></ul></div>'
                        +'</td>'
                    +'</tr>';
                }
            }
            if (c != '') {
                if (s != '') {
                    c += s +'</table></div>';
                } else {
                    c += '</div>';
                }
            }
            $('div.categories').append(c);
            $('#subcat').append(m);
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
});