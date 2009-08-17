// {* $Id: category.js 113 2009-07-29 11:54:49Z ukko $ *}
$(document).ready(function() {
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