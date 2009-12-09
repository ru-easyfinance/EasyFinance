/**
 * @desc Mail Widget
 * @author Andrey [Jet] Zharikov
 */

easyFinance.widgets.mail = function(){
    // private variables
    var _$node = null;
    var _$table = null;

    var _folder = null;
    var _model = null;

    // private functions
    function _initDialogs() {
        /* Write New Message Popup Window */
        $('#mail-popup').dialog({
            autoOpen: false,
            title: 'Новое сообщение',
            width: 600,
            buttons: {
                "Закрыть": function() {
                    $(this).dialog("close");
                },
                "Сохранить как черновик": function() {
                    _createDraft();
                    $(this).dialog("close");
                },
                "Отправить": function() {
                    _sendMail();
                    $(this).dialog("close");
                }
            }
        });

        var hashParams = window.location.hash.substr(1).split(";");
        if (hashParams.length > 1) {
            $('#mail-popup').dialog('open');

            $('#mail-popup #mail-to-id').val(hashParams[0]);
            $('#mail-popup #mail-to').val(hashParams[1]);
            $('#mail-popup #mail-subject').val(hashParams[2] ? hashParams[2] : '').focus();
            if(hashParams.length == 3)
                $('#mail-popup #mail-text').focus();
        }

        /* Read Message Popup Window */
        $('#mail-popup-read').dialog({
            autoOpen: false,
            title: 'Cообщение',
            width: 600
        });
    }

    function _initToolbar() {
        $('#mailsMenuRestore').hide();

        $('#comboMailFolder').change(function(){
            _folder = this.value;

            if (_folder == _model.FOLDER_INBOX) {
                $('#mailsMenuDelete').show();
                $('#mailsMenuReload').show();
            } else if (_folder == _model.FOLDER_OUTBOX) {
                $('#mailsMenuDelete').show();
                $('#mailsMenuReload').hide();
            } else if (_folder == _model.FOLDER_DRAFTS) {
                $('#mailsMenuDelete').show();
                $('#mailsMenuReload').hide();
            } else if (_folder == _model.FOLDER_TRASH) {
                $('#mailsMenuDelete').hide();
                $('#mailsMenuReload').hide();
            }

            if (_folder == _model.FOLDER_TRASH)
                $('#mailsMenuRestore').show();
            else
                $('#mailsMenuRestore').hide();

           _showMails(_model.getFolderMails(_folder));
        });

        $('#mail-write').click(function(){
            $('#mail-popup #mail-to').val('');
            $('#mail-popup #mail-subject').val('');
            $('#mail-popup #mail-text').val('');

            $('#mail-popup').dialog('open');
        });

        $('#mails-delete').click(function(){
            var ids = _getCheckedIds();

            if (ids.length == 0) {
                alert('Отметьте галочками письма для удаления!');
            } else {
                _model.trashMailsById(ids, function(){
                    _showMails(_model.getFolderMails(_folder));
                });
            }
        })

        $('#mails-restore').click(function(){
            var ids = _getCheckedIds();

            if (ids.length == 0) {
                alert('Отметьте галочками письма для восстановления!');
            } else {
                _model.restoreMailsById(ids, function(){
                    _showMails(_model.getFolderMails(_folder));
                });
            }
        })

        $('#mails-reload').click(_reloadInbox);

        $('#mailToolBar').show();
    }

    function _getCheckedIds() {
        var ids = [];

        var _$rows = _$table.find('input:gt(0):checked').closest('tr').get();
        for (var i =0; i < _$rows.length; i++) {
            var id = $(_$rows[i]).attr('id').split("_", 2)[1];
            ids.push (id);
        }

        return ids;
    }

    function _checkAllMailsClicked() {
        var check = _$node.find('#checkAllMails').attr('checked');
        _$table.find('input[type="checkbox"]').attr('checked', check);
    }

    function _reloadInbox() {
        // @todo: reload only inbox, not all mails
        _model.load(_showMails);
    }

    function _showMails(mails){
        // clear table
        _$table.hide();

        var strIcon = '';

        if (_folder == _model.FOLDER_TRASH) {
            _$table.find('#divAddressHeader').text('Автор/Адресат');
            strIcon = 'trash'; // @todo: use CORRECT icon (inbox/outbox/draft)
        } else if (_folder == _model.FOLDER_OUTBOX) {
            _$table.find('#divAddressHeader').text('Адресат');
            strIcon = 'outbox';
        } else if (_folder == _model.FOLDER_DRAFTS) {
            _$table.find('#divAddressHeader').text('Адресат');
            strIcon = 'drafts';
        } else {
            _$table.find('#divAddressHeader').text('Автор');
            strIcon = 'inbox';
        }
            

        _$table.find('#checkAllMails').attr('checked', false);
        _$table.find('tr:gt(0)').remove();
        
        // add rows
        var str ='';
        for (key in mails)
        {
            if (mails[key]){
                var addr = (_folder == _model.FOLDER_OUTBOX || _folder == _model.FOLDER_DRAFTS) ? mails[key]['receiverName'] : mails[key]['senderName'];

                str = '<tr class="item ' + (mails[key]['unread']==true?'unread':'') + '" id="mail_'+key+'">'
                    +'<td><input class="checkMail" type="checkbox" value=""/></td>'
                    +'<td><img width="16" height="16" src="/img/i/mail_'+strIcon+'.png"/></td>'
                    +'<td class="mail-title"><a href="#">'+mails[key]['subject']+'</a></td>'
                    +'<td><b>'+ addr +'</b></td>'
                    +'<td>'+mails[key]['date']+'</td>'
                +'</tr>' +str;
            }
        }
        _$table.append(str);
        _$table.show();
    }

    function _openMail() {
        // read mail by default
        var $row = $(this).closest('tr');
        $row.removeClass('unread');
        var id = $row.attr('id').split("_", 2)[1];

        _model.loadMail(id, _showMail);

        return false;
    }

    function _showMail(mail) {
        var buttons = {};

        buttons["Закрыть"] = function() {
            $(this).dialog("close");
        };

        if (_folder == _model.FOLDER_TRASH) {
            buttons["Восстановить"] = function() {
                _model.restoreMailsById([mail.id], function(){
                    _showMails(_model.getFolderMails(_folder));
                });

                $(this).dialog("close");
            };
        } 

        if (_folder != _model.FOLDER_TRASH){
            buttons["Удалить"] = function() {
                _model.trashMailsById([mail.id], function(){
                    _showMails(_model.getFolderMails(_folder));
                });

                $(this).dialog("close");
            };
        }

        if (_folder == _model.FOLDER_DRAFTS) {
            buttons["Сохранить изменения"] = function() {
                _model.editDraft(
                    mail.id,
                    $("#mail-popup-read #mail-text-read").val(),
                    function(){
                        _showMails(_model.getFolderMails(_folder));
                    }
                );

                $(this).dialog("close");
            };

            buttons["Отправить"] = function() {
                _model.sendDraft(
                    mail.id,
                    $("#mail-popup-read #mail-text-read").val(),
                    function(){
                        _showMails(_model.getFolderMails(_folder));
                    }
                );

                $(this).dialog("close");
            };
        }

        if (_folder == _model.FOLDER_INBOX) {
            buttons["Ответить"] = _reply;
        }

        if (_folder == _model.FOLDER_DRAFTS) {
            $('#mail-popup-read').find('#lblMailFromTo').text('Кому:');
            $('#mail-popup-read #mail-from').text(mail.to);
        } else {
            $('#mail-popup-read').find('#lblMailFromTo').text('От кого:');
            $('#mail-popup-read #mail-from').text(mail.from);
        }

        $('#mail-popup-read').dialog('option', 'buttons', buttons).dialog('open');
        
        $('#mail-popup-read #mail-date').text(mail.date);
        $("#mail-popup-read #mail-subject-read").text(mail.subject);
        $("#mail-popup-read #mail-text-read").text(mail.text);//@todo html text
    }

    function _reply(){
        $('#mail-popup').dialog('open');
        $('#mail-popup #mail-subject').val('Re: ' + $('#mail-subject-read').text()); //to == login???
        $('#mail-popup #mail-to').val($('#mail-from').text()); //to == login???
        $('#mail-popup #mail-text').val(">>> В предыдущем письме Вы писали: \n\n" + $('#mail-popup-read #mail-text-read').val()).focus();
        $('#mail-popup-read').dialog('close');
    }

    function _createDraft(){
        _model.createDraft(
            $('#mail-popup #mail-to').val(),
            $('#mail-popup #mail-subject').val(),
            $("#mail-popup #mail-text").val(),
            function () {
                if (_folder == _model.FOLDER_DRAFTS)
                    _showMails(_model.getFolderMails(_folder));
                else
                    $.jGrowl("Черновик сохранён", {theme: 'green'});
            }
        );
    }

    function _editDraft(){
        _model.editDraft(
            $('#mail-popup #mail-to').val(),
            $('#mail-popup #mail-subject').val(),
            $("#mail-popup #mail-text").val(),
            function () {
                if (_folder == _model.FOLDER_DRAFTS)
                    _showMails(_model.getFolderMails(_folder));
                else
                    $.jGrowl("Черновик сохранён", {theme: 'green'});
            }
        );
    }

    function _sendMail(){
        _model.sendMail(
            $('#mail-popup #mail-to').val(),
            $('#mail-popup #mail-subject').val(),
            $("#mail-popup #mail-text").val(),
            function () {
                if (_folder == _model.FOLDER_OUTBOX)
                    _showMails(_model.getFolderMails(_folder));
                else
                    $.jGrowl("Письмо отправлено", {theme: 'green'});
            }
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

        _initDialogs();

        _initToolbar();

        _model = model;
        _folder = _model.FOLDER_INBOX;
        _model.load(_showMails);

        _$node = $(nodeSelector);

        _$table = $('#mailsTable');
        
        _$table.find('#checkAllMails').click(_checkAllMailsClicked);

        $('.mail-title').live('click', _openMail);

        return this;
    }

    // reveal some private things by assigning public pointers
    return {
        init:init
    };
}(); // execute anonymous function to immediatly return object