/**
 * @desc Account Add/Edit Widget
 * @author Andrey [Jet] Zharikov
 */

easyFinance.widgets.accountEdit = function(){
    // private constants

    // private variables
    var _model = null;

    var _$node = null;

    var _isEditing = false;
    var _isVisible = false; // виден ли виджет

    // private functions
    function _toggleVisibility(){ 
        easyFinance.widgets.accountEdit._isEditing = false;
        if (_isVisible)
            hideForm();
        else
            showForm();
        
        $('#type_account').removeAttr('disabled');
    }

    function _initForm() {
        $('#op_btn_Save').click(function(){
            //update_list();
            // @todo: fire accountsLoaded
            easyFinance.models.accounts.load();
        })

        $('#starter_balance').live('keyup',function(e){
            FloatFormat(this, String.fromCharCode(e.which) + $(this).val())
        });

        $('#addacc').click(_toggleVisibility);

        $('#btnCancelAdd').click(function(){ ////button cancel in form click
            hideForm();
        });

        /**
         * select type in form selected change
         * @deprecated delete //where rewrite account model, controller, admin
         */
        $('#type_account').change(function(){
            _changeTypeAccount($(this).attr('value'));
        });

        // save button
        $('#btnAddAccount').click(function(){
            var str = $('#blockCreateAccounts #name').val();
            var description = $('#blockCreateAccounts #description').val();
            var id =$('#blockCreateAccounts').find('table').attr('id');
            var l = 1;

            if (str == ''){
                $.jGrowl("Введите название счёта!", {theme: 'red', life: 5000});
                return false;
            }

            if (str.indexOf('<') != -1 || str.indexOf('>') != -1) {
                $.jGrowl("Название счёта не должно содержать символов < и >!", {theme: 'red', life: 5000});
                return false;
            }

            if (description.indexOf('<') != -1 || description.indexOf('>') != -1) {
                $.jGrowl("Примечание не должно содержать символов < и >!", {theme: 'red', life: 5000});
                return false;
            }

            if (!_isEditing) {
                // при добавлении нового счёта
                // проверяем, чтобы не было счёта с таким же именем
                $('.item .name').each(function(){
                    if (id != $(this).closest('tr').attr('id')){
                        if($(this).text()==str)
                            l=0;
                    }
                });
            }

            if (l){
                hideForm();
                if (easyFinance.widgets.accountEdit._isEditing)
                    correctaccount();
                else
                    _createNewAccount();
            }
            else
            {
                $.jGrowl("Такой счёт уже существует!", {theme: 'red'});
            }
        });
    }

    /**
     * функция - пережиток прошлого;
     * перезагружает форму ввода счёта;
     * @return void
     * @todo rewrite without Ajax//where rewrite account model, controller, admin
     */
    function _changeTypeAccount(id)
    {
        $.post(
            "/accounts/changeType/",
            {
                id: id
            },
            function(data) {
                $('#account_form_fields').html(data);
            },
            'text'
        );
    }

    // public variables

    // public functions
    /**
     * @desc init widget
     * @usage init(nodeSelector, model)
     */
    function init(nodeSelector, model) {
        if (!model)
            return null;

        _$node = $(nodeSelector);

        _model = model;

        _initForm();

        return this;
    }

    /**
     * скрывает поле с добавлением счёта
     * @return void
     */
    function hideForm() {
        _isVisible = false;
        $('#blockCreateAccounts').hide();
    }
    /**
     * раскрывает поле с добавлением счёта
     * @return void
     */
    function showForm() {
        _isVisible = true;
        _changeTypeAccount($('#type_account').val());
        $('#blockCreateAccounts').show();
        $('#blockCreateAccounts').val('');
    }

    /**
     * функция добавляет новый счёт
     * @return void
     * @deprecated rewrite without update_list//on freetime
     */
    function _createNewAccount()
    {
        var cur_id = $("#formAccount select:[name='currency_id']").val();
        //var type = $("#formAccount id='type_account']").val();

        $.ajax({
            type: "POST",
            url: "/accounts/add/",
            data: $("#formAccount input,select,textarea"),
            success: function(data) {
                var id = data;
                if (!_isEditing){
                    $.jGrowl("Добавлен счёт", {theme: 'green'});
                    //update_list({id: id,cur_id: cur_id});
                }else{
                    $.jGrowl("Cчёт изменён", {theme: 'green'});
                    easyFinance.widgets.accountEdit._isEditing = false;
                }

                easyFinance.models.accounts.load();
                // @todo use model, which will fire
                // $(document).trigger('accountsChanged');

                $('li#c2').click()
            }
        });
    }
    /**
     * функция УДАЛЯЕТ счёт, а затем СОЗДАЁТ с новыми параметрами
     * @return void
     * @deprecated rewrite all//on freetime where rewrite account model, controller, admin
     */
    function correctaccount()
    {//del
            $.post('/accounts/del/',
            {
                id :$('#blockCreateAccounts').find('table').attr('id')
            },
            function(data){
                //$.jGrowl("Счёт Изменён", {theme: 'green'});
                easyFinance.widgets.accountEdit._isEditing = true;
                _createNewAccount();
            },
            'text'
        );
    }

    function setEditMode(mode) {
        _isEditing = mode;
    }

    // reveal some private things by assigning public pointers
    return {
        init: init,
        hideForm: hideForm,
        showForm: showForm,

        setEditMode: setEditMode
    };
}(); // execute anonymous function to immediatly return object