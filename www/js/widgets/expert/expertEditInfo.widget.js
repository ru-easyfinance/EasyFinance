/**
 * @desc Editable Expert Info Widget
 * @author Andrey [Jet] Zharikov
 */

easyFinance.widgets.expertEditInfo = function(){
    // private constants

    // private variables
    var _model = null;

    var _$node = null;
    var _$fullEditor = null;

    // private functions
    function _showInfo(profile) {
        _$node.find('#profile-short').val(profile.shortInfo);
        _$node.find('#profile-long').val(profile.fullInfo);
        _$fullEditor.htmlarea('updateHtmlArea');
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

        _$fullEditor = _$node.find('#profile-long').htmlarea({
            toolbar: [
                ["bold","italic","underline","strikethrough","|","subscript","superscript"],
                ["increasefontsize","decreasefontsize"],
                ["orderedlist","unorderedlist"],
                ["indent","outdent"],
                ["justifyleft","justifycenter","justifyright"],
                ["link","unlink"],
                ["h1","h2","h3"]
            ]
        });

        $('#formExpertInfo').ajaxForm({
            // pre-submit callback
            beforeSubmit:  function(formData){
                $.jGrowl("Изменения сохраняются", {theme: 'green'});
            },
            // post-submit callback
            success: function(){
                $.jGrowl("Изменения сохранены", {theme: 'green'});
            },
            error: function(){
                $.jGrowl("Ошибка на сервере!", {theme: 'red'});
            }
        });

        _model = model;
        if (_model.isLoaded == false)
            _model.load(_showInfo);
        else
            _showInfo(_model.getProfile());

        return this;
    }

    // reveal some private things by assigning public pointers
    return {
        init: init
    };
}(); // execute anonymous function to immediatly return object