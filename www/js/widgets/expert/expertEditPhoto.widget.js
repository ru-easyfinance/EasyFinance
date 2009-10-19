/**
 * @desc Editable Expert Photo Widget
 * @author Andrey [Jet] Zharikov
 */

easyFinance.widgets.expertEditPhoto = function(){
    // private constants
    var DELETE_PHOTO_URL = '/expert/deletePhoto/';

    // private variables
    var _model = null;

    var _$node = null;

    // private functions
    function _showInfo(profile) {
        if (profile.photo == ""){
            $('#divExpertPhoto').empty().text('нет фото');
        } else {
            $('#divExpertPhoto').empty().append(
                $('<img>').attr('src', profile.photo)
            );
        }
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
            _model.load(_showInfo);
        else
            _showInfo(_model.getProfile());

        _$node = $(nodeSelector);

        $('#btnDeleteExpertPhoto').click(function(){
            $.jGrowl("Фото удаляется", {theme: 'green'});

            $.post(DELETE_PHOTO_URL, '', function(profile){
                $.jGrowl("Фото удалено", {theme: 'green'});
                _showInfo(profile);
            },
            "json");

            return false;
        });

        $('#formExpertPhoto').ajaxForm({
            dataType: "json",
            // pre-submit callback
            beforeSubmit:  function(formData){
                $.jGrowl("Фото загружается", {theme: 'green'});
            },
            // post-submit callback
            success: function(profile){
                $.jGrowl("Фото загружено", {theme: 'green'});
                _showInfo(profile);
            },
            error: function(){
                $.jGrowl("Ошибка на сервере!", {theme: 'red'});
            }
        });

        return this;
    }

    // reveal some private things by assigning public pointers
    return {
        init: init
    };
}(); // execute anonymous function to immediatly return object