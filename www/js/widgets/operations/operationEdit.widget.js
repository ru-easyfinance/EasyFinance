/**
 * @desc Add/Edit Operation Widget
 * @author Andrey [Jet] Zharikov
 */

easyFinance.widgets.operationEdit = function(){
    // private constants

    // private variables
    var _model = null;

    var _$node = null;
    var _$fullEditor = null;

    // private functions
    function _showInfo(profile) {

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