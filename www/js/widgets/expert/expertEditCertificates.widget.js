/**
 * @desc Editable Expert Certificates Widget
 * @author Andrey [Jet] Zharikov
 */

easyFinance.widgets.expertEditCertificates = function(){
    // private constants
    var DELETE_CERTIFICATE_URL = '/expert/deleteCertificate/';

    // private variables
    var _model = null;

    var _$node = null;
    var _$table = null;

    // private functions
    function _showInfo(certificates) {
        _$table.hide();
        _$table.find('tr:gt(0)').remove();

        for (var key in certificates) {
            var _$row = $('<tr>');

            // image
            var _$col = $('<td>').append($('<img>').attr('src', certificates[key].image));
            _$row.append(_$col);

            // status
            _$col = $('<td>').html('<b>Статус:</b><br>');
            var _$div = $('<div>').text(certificates[key].status);
            if (certificates[key].processed == false) {
                _$div.addClass('processing');
            } else if (certificates[key].accepted == false) {
                _$div.addClass('denied');
            } else {
                _$div.addClass('accepted');
            }
            _$col.append(_$div).append($('<br>'));

            // comment
            _$col.append($('<div>').html('<b>Комментарий:</b><br>' + certificates[key].comment));
            _$col.append($('<br>'));

            // button
            _$col.append(
                $('<button>').attr('id', 'cert_' + key).text('удалить').click(function(){
                    if (confirm("Удалить сертификат?")) {
                        $.jGrowl("Операция выполняется", {theme: 'green'});

                        $.post(DELETE_CERTIFICATE_URL, {id: this.id.split("_", 2)[1]}, function(data) {
                                _showInfo(data);

                                $.jGrowl("Сертификат удалён", {theme: 'green'});
                        }, 'json');
                    }
                })
            );

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

        _model = model;
        if (_model.isLoaded == false)
            _model.load(function(profile){
                _showInfo(profile.certificates);
            });
        else
            _showInfo(_model.getProfile().certificates);

        _$node = $(nodeSelector);
        _$table = _$node.find('table:first');

        $('#certificate-popup').dialog({
            autoOpen: false,
            title: "Добавление сертификата",
            width: 600,
            buttons: {
                "Закрыть": function() {
                    $(this).dialog("close");
                },
                "Сохранить": function() {
                    var fname = $('#cert-file').val().toLowerCase();
                    if (fname == "")
                        return alert('Выберите файл!');

                    var dot = fname.lastIndexOf(".");
                    var ext = fname.substr(dot+1, fname.length);
                    if (ext != "jpg" && ext != "jpeg")
                        return alert('Выберите файл в формате JPG!');

                    $('#formAddCertificate').ajaxSubmit({
                        dataType: "json",
                        // pre-submit callback
                        beforeSubmit:  function(formData){
                            $.jGrowl("Сертификат загружается", {theme: 'green'});
                        },
                        // post-submit callback
                        success: function(data){
                            $.jGrowl("Сертификат загружен", {theme: 'green'});
                            _showInfo(data);
                        },
                        error: function(){
                            $.jGrowl("Ошибка на сервере!", {theme: 'red'});
                        }
                    });

                    $(this).dialog("close");
                }
            }
        });

        $('#btnAddCertificate').click(function(){
            $('#formAddCertificate').clearForm();
            $('#certificate-popup').dialog('open')
        });
        
        return this;
    }

    // reveal some private things by assigning public pointers
    return {
        init: init
    };
}(); // execute anonymous function to immediatly return object