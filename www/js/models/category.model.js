/* 
 * @desc Category Model
 * @author Andrey [Jet] Zharikov
 */

easyFinance.models.category = function(){
    // constants
    var ADD_URL = '/category/add/';
    var EDIT_URL = '/category/edit/';
    var DELETE_URL = '/category/del/';

    // private variables
    var _categories;

    // private functions
    function _update(actionUrl, id, name, parent, type, system, callback){
        $.post(actionUrl, {
                id: id,
                name: name,
                parent: parent,
                type: type,
                system: system
            }, callback, 'json'
        );
    }

    // public variables

    // public functions

    // @desc read initial data from json/server
    // @usage load(json)
    // @usage load(json, callback)
    // @usage load(callback)
    function load(param1, param2){
        if (typeof param == 'string') {
            _categories = param1;
            if (typeof param2 == 'function')
                param2(_categories);
        } else {
            // load from server
            $.get('/category/getCategory/', '',function(data) {
                _categories = data;
                if (typeof param1 == 'function')
                    param1(_categories);
            }, 'json');
        }
    }

    function add(name, parent, type, system, callback){
        _update(ADD_URL, -1, name, parent, type, system, function(data){
            var id = data.id;

            _categories.user[id] = {};
            _categories.user[id].id = id.toString();
            _categories.user[id].name = name;
            _categories.user[id].parent = parent;
            _categories.user[id].type = type;
            _categories.user[id].system = system;

           callback(_categories.user[id]);
        });
    }

    function editById(id, name, parent, type, system, callback){
        _update(EDIT_URL, id, name, parent, type, system, function(){            
            _categories.user[id].name = name;
            _categories.user[id].parent = parent;
            _categories.user[id].type = type;
            _categories.user[id].system = system;

            callback(_categories.user[id]);
        });
    }

    function deleteById(id, callback){
        $.post('/category/del/', {id:id}, function(){              
                delete _categories.user[id];
                
                callback();
            }
            , 'json'
        );
    }

    function getAllCategories(){
        return _categories;
    }

    function getSystemCategories(){
        return _categories.system;
    }

    function getUserCategories(){
        return _categories.user;
    }

    function getUserCategoriesByType(){
        // @TODO implement getUserCategoriesByType
    }

    function isParentCategory(id){
        return (_categories.user[id].parent == '0') ? true : false;
    }

    // reveal some private things by assigning public pointers
    return {
        load:load,
        add:add,
        editById: editById,
        deleteById: deleteById,
        getAllCategories: getAllCategories,
        getSystemCategories:getSystemCategories,
        getUserCategories:getUserCategories,
        getUserCategoriesByType:getUserCategoriesByType,
        isParentCategory: isParentCategory
    };
}(); // execute anonymous function to immediatly return object