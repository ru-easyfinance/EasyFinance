/**
 * @desc Editable Expert Services Widget
 * @author Andrey [Jet] Zharikov
 */

easyFinance.widgets.expertEditServices = function(){
    // private constants
    var EDIT_SERVICES_URL = '/expert/editServices/';

    // private variables
    var _model = null;

    var _$node = null;
    var _$table = null;

    // private functions
    function _showInfo(services) {
        _$table.hide();
        _$table.find('tr:gt(0)').remove();

        for (var key in services) {
            var _$row = $('<tr>');

            // checkbox
            var _$col = $('<td>');
            _$col.append($('<input>')
                .attr('name', 'checkService_' + key)
                .attr('type', 'checkbox')
                .attr('checked', services[key].checked)
                .click(function(){
                    if (!$(this).attr('checked'))
                        $(this).parent().parent().find('input[type!=checkbox]').val('');
                })
            );
            _$row.append(_$col);

            // title
            _$col = $('<td>').text(services[key].title);
            _$row.append(_$col);

            // comment
            _$col = $('<td>').text(services[key].comment);
            _$row.append(_$col);

            // price
            _$col = $('<td>');
            _$col.append($('<input>')
                .addClass('price')
                .attr('name', 'txtPrice_' + key)
                .val(services[key].price)
                .keyup (function(e){
                    FloatFormat(this,String.fromCharCode(e.which) + $(this).val());
                    if ($(this).val())
                        $(this).parent().parent().find('input[type=checkbox]').attr('checked', true);
                })
            );
            _$row.append(_$col);

            // days
            _$col = $('<td>');
            _$col.append($('<input>')
                .addClass('days')
                .attr('name', 'txtDays_' + key)
                .val(services[key].days)
                .keyup (function(e){
                    FloatFormat(this,String.fromCharCode(e.which) + $(this).val());
                    if ($(this).val())
                        $(this).parent().parent().find('input[type=checkbox]').attr('checked', true);
                })
            );
            _$row.append(_$col);

            _$row.append(_$col);

            _$table.append(_$row);
        }
        
        _$table.show();
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
        _$table = _$node.find('table:first');

        $('#formEditServices').ajaxForm({
            dataType: "json",
            // pre-submit callback
            beforeSubmit:  function(formData){
                $.jGrowl("Изменения сохраняются", {theme: 'green'});
            },
            // post-submit callback
            success: function(data){
                $.jGrowl("Изменения сохранены", {theme: 'green'});
                _showInfo(data);
            },
            error: function(){
                $.jGrowl("Ошибка на сервере!", {theme: 'red'});
            }
        });

        _model = model;
        if (_model.isLoaded == false)
            _model.load(function(profile){
                _showInfo(profile.services);
            });
        else
            _showInfo(_model.getProfile().services);

        return this;
    }

    // reveal some private things by assigning public pointers
    return {
        init: init
    };
}(); // execute anonymous function to immediatly return object