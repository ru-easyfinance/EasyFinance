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
    function _showInfo(certificates, noClear) {
        _$table.hide();
        _$table.find('tr:gt(0)').remove();

        for (var key in certificates) {
            _showCertificate(certificates[key]);
        }
        _$table.show();
    }

    function _showCertificate(certificate) {
        var $row = $('<tr>').attr('id', 'cert_'+certificate.id);

        // image
        var $col = $('<td>').append($('<img>').attr('src', certificate.smallImage));
        $row.append($col);

        // status
        $col = $('<td>').html('<b>Статус:</b><br>');
        var $div = $('<div>').text(certificate.statusText);
        if (certificate.status == 0) {
            $div.addClass('processing');
        } else if (certificate.status == 2) {
            $div.addClass('denied');
        } else {
            $div.addClass('accepted');
        }
        $col.append($div).append($('<br>'));

        // comment
        $col.append($('<div>').html('<b>Комментарий:</b><br>' + certificate.comment));
        $col.append($('<br>'));

        // button
        $col.append(
            $('<button>').attr('id', 'cert_' + certificate.id).text('удалить').click(function(){
                if (confirm("Удалить сертификат?")) {
                    $.jGrowl("Операция выполняется", {theme: 'green'});

                    $.post(DELETE_CERTIFICATE_URL, {id: this.id.split("_", 2)[1]}, function(data) {
                            $('#cert_' + data.result.id).remove();
                            $.jGrowl(data.result.message, {theme: 'green'});
                    }, 'json');
                }
            })
        );

        $row.append($col);

        _$table.append($row);
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
                            for (var key in data)
                                _showCertificate(data[key]);
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

        _model = model;
        if (_model.isLoaded == false)
            _model.load(function(profile){
                _showInfo(profile.certificates);
            });
        else
            _showInfo(_model.getProfile().certificates);

        return this;
    }

    // reveal some private things by assigning public pointers
    return {
        init: init
    };
}(); // execute anonymous function to immediatly return object