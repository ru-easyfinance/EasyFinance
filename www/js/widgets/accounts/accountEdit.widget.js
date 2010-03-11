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
    
    var _id = null;

    // private functions
    function _toggleVisibility(){ 
        _isEditing = false;
        $('#acc_type').removeAttr('disabled');
        $('#acc_name').val('');
        $('#acc_comment').val('');
        $('#acc_balance').val('');

        if (_isVisible)
            hideForm();
        else
            showForm();
    }

    function _initForm() {
        $('#op_btn_Save').click(function(){
            //update_list();
            // @todo: fire accountsLoaded
            _model.load();
        })

        $('#starter_balance').live('keyup',function(e){
            FloatFormat(this, String.fromCharCode(e.which) + $(this).val())
        });

        $('#addacc').click(_toggleVisibility);

        $('#btnCancelAdd').click(function(){ ////button cancel in form click
            hideForm();
        });

        // @todo
        // fill currency combo with options
        var strCurrency = '';
        var currency = _modelCurrency.getCurrencyList();
        for (var key in currency) {
            if ( key != 'default')
                strCurrency = strCurrency + '<option value="' + key + '">' + currency[key].text + '</option>';
        }
        $('#acc_currency').html(strCurrency);

        // save button
        $('#btnAddAccount').click(_saveAccount);
    }

    function _saveAccount(){
        var params = {};
        params.type = _$node.find("#acc_type").val();
        params.name = _$node.find('#acc_name').val();
        params.comment = _$node.find('#acc_comment').val();
        params.currency = _$node.find('#acc_currency').val();
        params.initPayment = _$node.find('#acc_balance').val();

        if (params.name == '') {
            $.jGrowl("Введите название счёта!", {theme: 'red', life: 10000});
            return false;
        }

        if (params.name.length > 20) {
            $.jGrowl("Название счёта должно быть не больше 20 символов!", {theme: 'red', life: 2500});
            return false;
        }
		
        if (params.name.indexOf('<') != -1 || params.name.indexOf('>') != -1) {
            $.jGrowl("Название счёта не должно содержать символов < и >!", {theme: 'red', life: 2500});
            return false;
        }

        if (params.comment.indexOf('<') != -1 || params.comment.indexOf('>') != -1) {
            $.jGrowl("Примечание не должно содержать символов < и >!", {theme: 'red', life: 2500});
            return false;
        }

        var accId = null;
        if (!_isEditing) {
            // при добавлении нового счёта проверяем,
            // есть ли уже счёт с таким же именем
            accId = _model.getAccountIdByName(params.name);
            if (accId) {
                $.jGrowl("Такой счёт уже существует!", {theme: 'red'});
                return false;
            }
        }

        hideForm();

        var handler = function(data) {
            if (data.result && data.result.text)
                $.jGrowl(data.result.text, {theme: 'green'});
            else if (data.error && data.error.text)
                $.jGrowl(data.error.text, {theme: 'red'});
        };

        if (_isEditing) {
            _model.editAccountById(_id, params, handler);
        } else {
            _model.addAccount(params, handler);
        }
    };

    // public variables

    // public functions
    /**
     * @desc init widget
     * @usage init(nodeSelector, modelAccount, modelCurrency)
     */
    function init(nodeSelector, model, modelCurrency) {
        if (!model || !modelCurrency)
            return null;

        _$node = $(nodeSelector);

        _model = model;
        _modelCurrency = modelCurrency;

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
        $('#blockCreateAccounts').show();
    }

    function addAccount() {
        _isEditing = false;

        // clear form
        $('#acc_type').removeAttr('disabled');
        $('#acc_name').val('');
        $('#acc_currency').val(0);

        showForm();
    }

    function copyAccountById(id) {
        editAccountById(id);

        _isEditing = false;
        $('#acc_type').removeAttr('disabled');
    }

    function editAccountById(id) {
        _isEditing = true;
        _id = id;

        var account = _model.getAccounts()[id];
        if (!account)
            return false;      

        $(document).scrollTop(300);
        $('#acc_type')
            .val(account.type)
            .attr('disabled', 'disabled');

        $('#acc_name').val(account.name);
        $('#acc_comment').val(account.comment);
        $('#acc_balance').val(Math.abs(parseFloat(account.initPayment)));
        $('#acc_currency').val(account.currency);
    }

    function setEditMode(mode) {
        _isEditing = mode;
    }

    // reveal some private things by assigning public pointers
    return {
        init: init,
        hideForm: hideForm,
        showForm: showForm,

        setEditMode: setEditMode,
        editAccountById: editAccountById,
        copyAccountById: copyAccountById
    };
}(); // execute anonymous function to immediatly return object
