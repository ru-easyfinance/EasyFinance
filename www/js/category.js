// {* $Id: category.js 113 2009-07-29 11:54:49Z ukko $ *}
$(document).ready(function() {
    // TRUSTED
    var cat;

    loadCategory();


    /**
     * Загружает пользовательские и системные категории
     */
    function loadCategory() {
        $.get('/category/getCategory/', '',function(data) {

            cat = {};
            cat = $.extend(data);
            $('div.categories').remove('div');
            
            c=s='';
            for(var id in data.user) {
                if (data.user[id]['parent'] == 0) {
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
                    s += '<tr>'
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
                        +'<td class="w3">'+data.system[data.user[id]['id']]['system_category_name']+'</td>'





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
        }, 'json');
    }


    // ПОДОЗРИТЕЛЬНАЯ ХУЙНЯ
















    $('#add_form').hide();

    $('#add_category').click(function(){
        categoryAddVisible();
    });
    $('.editCategory').click(function(){
        editCategory($(this).attr('value'));
    });
    $('.visibleCategory').click(function(){
        visibleCategory($(this).val(), $(this).attr('visible'));
    });
    $('.delCategory').click(function(){
        delCategory($(this).val());
    });
    
    $('#btnAddCategory').click(function(){//++
        createNewCategory();
    });

    $('#btnCancelAdd').click(function(){
        categoryAddUnvisible();
    });

    function categoryAddUnvisible() {
        $('#add_form').hide();
        //$('#name,#category_id').val('');
        //$('#parent,#type,#system').val(0);
    }

    function categoryAddVisible() {
        $('#add_form').show();
    }

    function createNewCategory() {
        //$('#loader').html('Подождите, идет сохранение...');
        //$('#information_text').hide();
        $.ajax({
            type: "POST",
            url: "/category/add/",
            data: {
                name:        $('#name').val(),
                parent:      $('#parent').val(),
                type:        $('#type').val(),
                system:      $('#system').val(),
                category_id: $('#category_id').val()
            },
            success: function(data) {
                //$('#loader').html(" ");
                //$('#dataCategories').html(data);
                //$('#information_text').show();
                categoryAddUnvisible();
            }
        });
    }

    function deleteCategory(id) {
        if (!confirm("Вы действительно хотите удалить эту категорию?")) {
            return false;
        }

        $('#information_text').hide();
        $('#loader').html('Подождите, идет удаление...');

        $.ajax({
            type: "POST",
            url: "/category/del/",
            data: {
                id: id
            },
            success: function(data) {
                $('#loader').html(" ");
                $('#dataCategories').html(data);
                $('#information_text').show();
                $('#blockCreateCategories').load('/category/reload_block_create *');
            }
        });
    }

    function editCategory(id)
    {
        categoryAddVisible();
        $('#loader').html('Подождите, идет загрузка...');
        $('#information_text').hide();
        $.ajax({
            type: "POST",
            url: "/category/edit/",
            data: {
                id: id
            },
            success: function(data) {
                $('#blockCreateCategories').html(data);
                $('#loader').html(' ');
                $('#information_text').show();
            }
        });
        scrollTo(0,0);
    }

    function acceptFiltrCategories()
    {
        $('#loader').html('Подождите, идет загрузка...');
        $('#information_text').hide();
        $.ajax({
            type: "GET",
            url: "/category/filtr/",
            data: {
                modules: "categories",
                action: "change_filtr",
                filtr_type: $('#filtr_type').val(),
                filtr_visible: $('#filtr_visible').val(),
                filtr_period: $('#filtr_period').val(),
                ajax: true
            },
            success: function(data) {
                $('#dataCategories').html(data);
                $('#loader').html(' ');
                $('#information_text').show();
            }
        });
    }

    function changePeriodFact()
    {
        var period = document.getElementById('period_fact').value;
        $('#loader').html('Подождите, идет загрузка...');
        $('#information_text').hide();
        $.ajax({
            type: "GET",
            url: "/index.php",
            data: {
                modules: "categories",
                action: "change_period_fact",
                period: period,
                ajax: true
            },
            success: function(data) {
                $('#dataCategories').html(data);
                $('#loader').html(' ');
                $('#information_text').show();
            }
        });
    }

    function visibleCategory(id, visible)
    {
        $('#loader').html('Подождите, идет загрузка...');
        $('#information_text').hide();
        $.ajax({
            type: "GET",
            url: "/index.php",
            data: {
                modules: "categories",
                action: "visible_category",
                id: id,
                visible: visible,
                ajax: true
            },
            success: function(data) {
                $('#dataCategories').html(data);
                $('#loader').html(' ');
                $('#information_text').show();
            }
        });
    }
});