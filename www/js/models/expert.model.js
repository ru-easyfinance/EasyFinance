/**
 * @desc Category Model
 * @author Andrey [Jet] Zharikov
 */

easyFinance.models.expert = function(){
    // constants
    var PROFILE_URL = '/expert/getProfile/';
    var EDIT_TOPICS_URL = '/expert/editTopics/';
    var EDIT_SERVICES_URL = '/expert/editServices/';
    var GET_CERTIFICATES_URL = '/expert/getCertificates/';
    var DELETE_CERTIFICATE_URL = '/expert/deleteCertificate/';

    // private variables
    var _profile = null;

    // private functions

    // public variables
    var isLoaded = false;

    // public functions

    /**
     * @desc read initial data from json/server
     * @usage load(json)
     * @usage load(json, callback)
     * @usage load(callback)
     */
    function load(param1, param2){
        var _this = this;

        if (typeof param == 'string') {
            _profile = param1;
            isLoaded = true;
            if (typeof param2 == 'function')
                param2(_categories);
        } else {
            // load from server
            $.get(PROFILE_URL, '',function(data) {
                _profile = data;
                _this.isLoaded = true;
                if (typeof param1 == 'function')
                    param1(_this);
            }, 'json');
        }
    }

    function getProfile(){
        return _profile;
    }

    function editInfo(shortInfo, fullInfo, callback){
        $.post(EDIT_INFO_URL, {shortInfo: shortInfo, fullInfo: fullInfo}, function(data) {
            if (data.id) {
                _profile = data;

                if (typeof callback == 'function')
                    callback(_profile);
            }
        }, 'json');
    }

    function getCertificates(callback){
        $.post(GET_CERTIFICATES_URL, '', function(data) {
            _profile.certificates = data;

            if (typeof callback == 'function')
                callback(data);
        }, 'json');
    }

    function deleteCertificate(id, callback){
        $.post(DELETE_CERTIFICATE_URL, {id: id}, function(data) {
            if (typeof callback == 'function')
                callback(data);
        }, 'json');
    }

    // reveal some private things by assigning public pointers
    return {
        isLoaded: isLoaded,

        load: load,
        getProfile: getProfile,
        editInfo: editInfo,

        getCertificates: getCertificates,
        deleteCertificate: deleteCertificate
    };
}(); // execute anonymous function to immediatly return object