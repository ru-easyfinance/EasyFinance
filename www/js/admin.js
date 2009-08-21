$(document).ready(function(){
        del = function (mark){
            $('tr#'+mark).empty()
        };
        //add = function (mark){
        //    $(mark+' table')
        //}

        //fields
	$('.fields #btnAdd').click(
            function(){
                save_fields();
            });
	$('.fields #btnCancel').click(
            function(){
                location.href="/admin/accounts/";
            });
	$('.fields .del').click(
            function(){
                del_fields($(this).closest('tr').attr('id'));
            });

	function save_fields()
	{
		$.ajax({
			type: "POST",
			url: "/admin/account_save_fields/",
			data: {
				field_visual_name: $('#field_visual_name').val(),
				field_name: $('#field_name').val(),
				field_type: 'string',
				field_regexp: $('#field_regexp').val(),
				field_permissions: $('#field_permissions').val(),
				field_default_value: $('#field_default_value').val(),
				id: $('#id').val(),
				ajax: true
			},
			success: function(data) {
				$('#data').html(data);
			}
		});
	}

        function del_fields(mark)
        {
            $.post(
                '/admin/account_del_fields/',
                {
                    id: mark
                },
                del(mark),
                'json'
            );
        }
        //type
        $('.type #btnAddAccount').click(
            function(){
                save_type();
            });
	$('.type #btnCancelAdd').click(
            function(){
                location.href="/admin/accounts/";
            });
	$('.type .del').click(
            function(){
                delete_type($(this).closest('tr').attr('id'));
            });
        $('.type .edit').click(
            function(){
                update_type($(this).closest('tr'));
            });

	function save_type()
	{
		$.ajax({
			type: "POST",
			url: "/admin/account_save_type/",
			data: {
				type_name: $('#type_name').val(),
				type_id: $('#type_id').val(),
				ajax: true
			},
			success: function(data) {
				$('#dataAccounts').html(data);
			}
		});
	}

        function update_type(mark)
        {
            $('input#type_id').attr('value',$(mark).attr('id'));
            $('input#type_name').attr('value',$(mark).find('#name').text());
        }

        function delete_type(mark)
        {
            $.post(
                '/admin/account_del_type/',
                {
                    id: mark
                },
                del(mark),
                'json'
            );
        }
        //type_fields
        $('.type_fields #btnAdd').click(
            function(){
                save_type_fields();
            });
	$('.type_fields #btnCancel').click(
            function(){
                location.href="/admin/accounts/";
            });
	$('.type_fields .del').click(
            function(){
                delete_type_fields($(this).closest('tr').attr('id'));
            });

	function save_type_fields()
	{
		$.ajax({
			type: "POST",
			url: "/admin/account_save_type_fields/",
			data: {
				type: $('#type').val(),
				field: $('#field').val(),
				id: $('#id').val(),
				ajax: true
			},
			success: function(data) {
                                $
				$('#data').html(data);
			}
		});
	}

        function delete_type_fields(mark)
        {
            $.post(
                '/admin/account_del_type_fields/',
                {
                    id: mark
                },
                del(mark),
                'json'
            );
        }
})