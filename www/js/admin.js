$(document).ready(function(){
        var del = function (mark){
            $('tr#'+mark).hide();
        };
        var content = function (mark, data){
            $(mark+' table tr').find('td').closest('tr').empty();
            $(mark+' table').append(data);
        };

        //fields
	$('.fields #btnAdd').live('click',
            function(){
                save_fields();
            });
	$('.fields #btnCancel').live('click',
            function(){
                location.href="/admin/accounts/";
            });
	$('.fields .del').live('click',
            function(){
                if (confirm('Вы уверены ?')){
                del_fields($(this).closest('tr').attr('id'));
                }
            });

	function save_fields()
	{
		$.post(
                        "/admin/account_save_fields/",
			 {
				field_visual_name: $('#field_visual_name').val(),
				field_name: $('#field_name').val(),
				field_type: 'string',
				field_regexp: $('#field_regexp').val(),
				field_permissions: $('#field_permissions').val(),
				field_default_value: $('#field_default_value').val(),
				id: $('#id').val()
			},
                        function (data){
			content('.fields', data)},
                        'text'
			
		);
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

        function edit_fields(mark)
        {
            del_fields(mark);
        }
        //type
        $('.type #btnAddAccount').live('click',
            function(){
                save_type();
            });
	$('.type #btnCancelAdd').live('click',
            function(){
                location.href="/admin/accounts/";
            });
	$('.type .del').live('click',
            function(){
                if (confirm('Вы уверены ?')){
                delete_type($(this).closest('tr').attr('id'));
                }
            });
        $('.type .edit').live('click',
            function(){
                update_type($(this).closest('tr'));
            });

	function save_type()
	{
		$.post("/admin/account_save_type/",
			{
				type_name: $('#type_name').val(),
				type_id: $('#type_id').val()
			},
                        function (data){
			content('.type',data)},
                        'text'
			
		);
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
        $('.type_fields #btnAdd').live('click',
            function(){
                save_type_fields();
            });
	$('.type_fields #btnCancel').live('click',
            function(){
                location.href="/admin/accounts/";
            });
	$('.type_fields .del').live('click',
            function(){
                if (confirm('Вы уверены ?')){
                delete_type_fields($(this).closest('tr').attr('id'));
                }
            });

	function save_type_fields()
	{
		$.post(
			"/admin/account_save_type_fields/",
			{
				type: $('#type').val(),
				field: $('#field').val(),
				id: $('#id').val(),
				ajax: true
			},
                        function (data){
			content('.type_fields',data)},
                        'text'
			
		);
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