/**
 * @desc Mail Model
 * @author Andrey [Jet] Zharikov
 */

easyFinance.models.mail = function(){
    // constants
    var FOLDER_INBOX = "inbox";
    var FOLDER_OUTBOX = "outbox";
    var FOLDER_DRAFTS = "drafts";
    var FOLDER_TRASH = "trash";

    /*
    var INBOX_URL = '/mail/inbox/';
    var OUTBOX_URL = '/mail/outbox/';
    var DRAFTS_URL = '/mail/drafts/';
    var TRASH_URL = '/mail/trash/';
    */

    var MAIL_LIST_URL = '/mail/listall/';
    var MAIL_URL = '/mail/get/';
    var SEND_MAIL_URL = '/mail/send/';
    var SAVE_DRAFT_URL = '/mail/save_draft/';
    var TRASH_MAIL_URL = '/mail/trash/';
    var RESTORE_MAIL_URL = '/mail/restore/';
    
    //var DELETE_MAIL_URL = '/mail/destroy/';

    // private variables
    var _folders = {};

    // public variables

    // public functions

    /**
     * @desc read initial data from json/server
     * @usage load(json)
     * @usage load(json, callback)
     * @usage load(callback)
     */
    function load(param1, param2){
        if (typeof param == 'string') {
            _folders[FOLDER_INBOX] = param1.inbox;
            _folders[FOLDER_OUTBOX] = param1.outbox;
            _folders[FOLDER_DRAFTS] = param1.drafts;
            _folders[FOLDER_TRASH] = param1.trash;

            if (typeof param2 == 'function')
                param2(_folders.inbox);
        } else {
            // load from server
            $.get(MAIL_LIST_URL, '', function(data) {
                _folders[FOLDER_INBOX] = data.inbox;
                _folders[FOLDER_OUTBOX] = data.outbox;
                _folders[FOLDER_DRAFTS] = data.drafts;
                _folders[FOLDER_TRASH] = data.trash;

                if (typeof param1 == 'function')
                    param1(_folders[FOLDER_INBOX]);
            }, 'json');
        }
    }

    function getFolderMails(folder) {
        return _folders[folder];
    }

    function getInboxMails() {
        return _folders.inbox;
    }

    function getOutboxMails() {
        return _folders.outbox;
    }

    function getDraftMails() {
        return _folders.drafts;
    }

    function getTrashMails() {
        return _folders.trash;
    }

    function sendMail(id, receiverId, subject, body, callback){
        $.post(SEND_MAIL_URL, {receiverId: receiverId, subject: subject, body: body}, function(data) {
            if (data.result) {
                if (data.result.outbox) {
                    _folders.outbox[data.result.outbox[0].id] = $.extend(true, {}, data.result.outbox[0]);
                } else {
                    delete _folders.drafts[id];
                    _folders.outbox[data.result.outbox[0].id] = $.extend(true, {}, data.result.outbox[0]);
                }
            }

            if (typeof callback == 'function')
                callback(data);
        }, 'json');
    }

    function createDraft(receiverId, subject, body, callback){
        $.post(SAVE_DRAFT_URL, {receiverId: receiverId, subject: subject, body: body}, function(data) {
            if (data.id)
                _folders.drafts[data.id] = data;

            if (typeof callback == 'function')
                callback(data);
        }, 'json');
    }

    function editDraft(id, receiverId, body, callback){
        $.post(SAVE_DRAFT_URL, {id: id, receiverId: receiverId, body: body}, function(data) {
            if (data.id)
                _folders.drafts[data.id] = data;

            if (typeof callback == 'function')
                callback(data);
        }, 'json');
    }

    function sendDraft(id, receiverId, body, callback){
        $.post(SEND_MAIL_URL, {draftSource: id, receiverId: receiverId, body: body}, function(data) {
            if (data.id){
                delete _folders.drafts[data.id];
                _folders.outbox[data.id] = data;
            }

            if (typeof callback == 'function')
                callback(data);
        }, 'json');
    }

    /**
     * @desc read full mail data
     * @usage loadMail(id, callback)
     */
    function loadMail(id, callback) {
        var mail = null;
        var unread = false;

        // mark as read
        if(_folders.inbox[id]) {
            unread = _folders.inbox[id].unread;
            mail = _folders.inbox[id];
        } else if(_folders.outbox[id]) {
            unread = _folders.outbox[id].unread;
            mail = _folders.outbox[id];
        } else if(_folders.drafts[id]) {
            unread = _folders.drafts[id].unread;
            mail = _folders.drafts[id];
        } else if(_folders.trash[id]) {
            unread = _folders.trash[id].unread;
            mail = _folders.trash[id];
        }

        if (unread == false) {
            if (typeof callback == 'function')
                callback(mail);

            return;
        }

        // mark as read on server
        $.post(MAIL_URL, {id: id}, function(data) {
            // mark as read
            mail.unread = false;

            if (typeof callback == 'function')
                callback(mail);
        }, 'json');
    }

    function trashMailsById(ids, callback) {
        var _ids = $.extend(true, {}, ids);

        $.post(TRASH_MAIL_URL, {ids: ids.join(',')}, function(data) {
            if (data.result) {
                // move to trash on success
                for(var key in _ids) {
                    var id = _ids[key];
                    
                    if(_folders.inbox[id]) {
                        _folders[FOLDER_TRASH][id] = _folders.inbox[id];
                        delete _folders.inbox[id];
                    } else if(_folders.outbox[id]) {
                        _folders[FOLDER_TRASH][id] = _folders.outbox[id];
                        delete _folders.outbox[id];
                    } else if(_folders.drafts[id]) {
                        _folders[FOLDER_TRASH][id] = _folders.drafts[id];
                        delete _folders.drafts[id];
                    }
                }
            }

            if (typeof callback == 'function')
                callback(data);
        }, 'json');
    }

    function restoreMailsById(ids, callback) {
        var _ids = $.extend(true, {}, ids);

        $.post(RESTORE_MAIL_URL, {ids: ids.join(',')}, function(data) {
            if (data.result) {
                // move from trash on success
                for(var key in _ids) {
                    var id = _ids[key];

                    if(_folders.trash[id]) {
                        if (_folders.trash[id].folder == FOLDER_INBOX) {
                            _folders[FOLDER_INBOX][id] = _folders.trash[id];
                        } else if (_folders.trash[id].folder == FOLDER_OUTBOX) {
                            _folders[FOLDER_OUTBOX][id] = _folders.trash[id];
                        } else if (_folders.trash[id].folder == FOLDER_DRAFTS) {
                            _folders[FOLDER_DRAFTS][id] = _folders.trash[id];
                        }

                        delete _folders.trash[id];
                    }
                }
            }

            if (typeof callback == 'function')
                callback(data);
        }, 'json');
    }

    /**
     * @desc completely delete mail
     * @usage deleteMails(ids, callback)
     * @param ids - array of mail ids
     * @param callback - called after operation
     */
    function deleteMails(ids, callback) {
        $.post(DELETE_MAIL_URL, {ids: ids.join(',')}, function(data) {
            for(var id in data) {
                // delete mail on success
                if (data[id] && _folders.trash[id])
                        delete _folders.trash[id];
            }
            
            if (typeof callback == 'function')
                callback(data);
        }, 'json');
    }

    // reveal some private things by assigning public pointers
    return {
        // constants
        FOLDER_INBOX: FOLDER_INBOX,
        FOLDER_OUTBOX: FOLDER_OUTBOX,
        FOLDER_DRAFTS: FOLDER_DRAFTS,
        FOLDER_TRASH: FOLDER_TRASH,

        // methods
        load:load,
        loadMail: loadMail,
        getFolderMails: getFolderMails,
        getInboxMails: getInboxMails,
        getOutboxMails: getOutboxMails,
        getDraftMails: getDraftMails,
        getTrashMails: getTrashMails,

        trashMailsById: trashMailsById,
        restoreMailsById: restoreMailsById,

        sendMail: sendMail,
        createDraft: createDraft,
        editDraft: editDraft,
        sendDraft: sendDraft
    };
}(); // execute anonymous function to immediatly return object

