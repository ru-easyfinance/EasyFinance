/**
 * @desc Category Model
 * @author Andrey [Jet] Zharikov
 */

easyFinance.models.category = function(){
    // constants
    var LIST_URL = '/category/getCategory/';
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

    function _sortUserCategories() {
        var arr = [];

        for(var cat in _categories.user)
            arr.push(_categories.user[cat]);

        arr.sort(function(a,b){return a.name.localeCompare(b.name)});

        _categories.user = [];
        _categories.user = arr;
    }

    // public variables

    // public functions

    /**
     * @desc read initial data from json/server
     * @usage load(object)
     * @usage load(object, callback)
     * @usage load(callback)
     */
    function load(param1, param2){
        if (typeof param1 == 'object') {
            _categories = param1;
            if (typeof param2 == 'function')
                param2(_categories);
        } else {
            // load from server
            $.get(LIST_URL, '',function(data) {
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
            _categories.user[id].parent = parent == "" ? 0 : parent;
            _categories.user[id].type = type;
            _categories.user[id].system = system;

//            _sortUserCategories();

           callback(_categories.user[id]);
        });
    }

    function editById(id, name, parent, type, system, callback){
        var oldCat = $.extend({}, _categories.user[id]);

        _update(EDIT_URL, id, name, parent, type, system, function(){            
            _categories.user[id].name = name;
            _categories.user[id].parent = parent;
            _categories.user[id].type = type;
            _categories.user[id].system = system;

            if (parent !="" && (parent != oldCat.parent || type!=oldCat.type)) {
                // при перемещении подкатегории в другую категорию
                // или при изменении типа подкатегории
                var newParent = _categories.user[parent];
                if (newParent.type != 0 && newParent.type != type) {
                    // если тип подкатегории конфликтует с родительской категорией,
                    // надо сделать родительскую категорию универсальной
                    newParent.type = 0;
                }
            }

            callback(_categories.user[id]);
        });
    }

    function deleteById(id, callback){
        $.post(DELETE_URL, {id:id}, function(){
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

    function _treeAddChildren(arrParent, idParent) {
        // fill parent categories
        for (var key in _categories.user) {
            var cat = _categories.user[key];
            if (cat.parent == idParent) {
                arrParent[cat.id] = $.extend("", cat);
                arrParent[cat.id].children = [];
                _treeAddChildren(arrParent[cat.id].children, cat.id)
            }
        }
    }

    function getUserCategoriesTree(){
        /*
        [
            id : {
                id: 101,
                name: "rootCategory",
                children: [ "202" : { id, name, children } ]
            },

            id: {
                id: 102,
                name: "emptyRootCategory",
            }
        ]
        */
        if (!_categories)
            return null;

        var tree = [];
        var cat = null;

        // recursive function
        _treeAddChildren(tree, "0");

        return tree;
    }

    function getUserCategoriesByType(){
        // @TODO implement getUserCategoriesByType
    }

    function getChildrenByParentId(id) {
        var arr = [];
        var parent = _categories.user[id];
        if (!parent)
            return arr;

        var parentId = parent.id;
        for (var key in _categories.user) {
            if (_categories.user[key].parent == parentId)
                arr.push(_categories.user[key]);
        }

        return arr;
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
        getUserCategoriesTree: getUserCategoriesTree,
        getUserCategoriesByType:getUserCategoriesByType,
        isParentCategory: isParentCategory,
        getChildrenByParentId: getChildrenByParentId
    };
}(); // execute anonymous function to immediatly return object