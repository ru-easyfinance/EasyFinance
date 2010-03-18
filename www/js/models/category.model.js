/**
 * @desc Category Model
 * @author Andrey [Jet] Zharikov
 */

easyFinance.models.category = function(){
    // constants
    var LIST_URL = '/category/getCategory/?responseMode=json';
    var ADD_URL = '/category/add/?responseMode=json';
    var EDIT_URL = '/category/edit/?responseMode=json';
    var DELETE_URL = '/category/del/?responseMode=json&confirmed=1';

    // private variables
    var _categories = null;
    var _order = {};

    // private functions
    function _htmlDecodeAll() {
        var key;

        if(_categories.user){
            for (key in _categories.user) {
                _categories.user[key].name = _categories.user[key].name.replace(/&quot;/g, '"');
            }
        }
        if(_categories.system){
            for (key in _categories.system) {
                _categories.system[key].name = _categories.system[key].name.replace(/&quot;/g, '"');
            }
        }
    }

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

    function _compareUserCategoryOrderByName(a, b) {
        var strA = _categories.user[a].name.toLowerCase();
        var strB = _categories.user[b].name.toLowerCase();

        return strA.localeCompare(strB);
    }

    // создаёт массивы, содержащие ключи 
    // отсортированных по имени категорий
    function _createOrderLists() {
        for (var prop in _categories) {
            _order[prop] = new Array();
            for (var key in _categories[prop]) {
                _order[prop].push (_categories[prop][key].id);
            }
        }

        //_order.user.sort(_compareUserCategoryOrderByName);
        //_order.recent.sort(_compareUserCategoryOrderByName);
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
        var _this = this;
        
        if (typeof param1 == 'object') {
            _categories = param1;
            _createOrderLists();
            _htmlDecodeAll();
            if (typeof param2 == 'function')
                param2(_categories);
        } else {
            // load from server
            $.get(LIST_URL, 'responseMode=json',function(data) {
                _categories = data;
                _createOrderLists();
                _htmlDecodeAll();

                $(document).trigger('categoriesLoaded');

                if (typeof param1 == 'function')
                    param1(_this);
            }, 'json');
        }
    }

    function add(name, parent, type, system, callback){
        for(var key in _categories.user){
            if (_categories.user[key].name == name && (_categories.user[key].parent == parent || (_categories.user[key].parent == '0' && parent == ''))){
                callback({
                    error: {
                        text: "Категория с данным именем уже существует."
                    }
                });
                return;
            }
        }

        _update(ADD_URL, -1, name, parent, type, system, function(data){
            if (data.result) {
                var id = data.result.id;

                _categories.user[id] = {};
                _categories.user[id].id = id.toString();
                _categories.user[id].name = name;
                _categories.user[id].parent = parent == "" ? 0 : parent;
                _categories.user[id].type = type;
                _categories.user[id].system = system;

                _createOrderLists();

                $(document).trigger('categoryAdded');

                // получаем новый список заново - отсортированный и т.п.
                load();
            }

            callback(data);
        });
    }

    function editById(id, name, parent, type, system, callback){
        if (parent == "")
            parent = "0";
        
        var oldCat = $.extend({}, _categories.user[id]);

        _update(EDIT_URL, id, name, parent, type, system, function(data){
            if (data.error && data.error.text) {
                $.jGrowl(data.error.text, {theme: 'red'});
                return false;
            }                

            _categories.user[id].name = name;
            _categories.user[id].parent = parent;
            _categories.user[id].type = type;
            _categories.user[id].system = system;

            if (parent !="0" && (parent != oldCat.parent || type!=oldCat.type)) {
                // при перемещении подкатегории в другую категорию
                // или при изменении типа подкатегории
                var newParent = _categories.user[parent];
                if (newParent.type !== 0 && newParent.type != type) {
                    // если тип подкатегории конфликтует с родительской категорией,
                    // надо сделать родительскую категорию универсальной
                    newParent.type = 0;
                }
            }

            $(document).trigger('categoryEdited');

            callback(_categories.user[id]);
	    return true;
        });
    }

    function deleteById(id, callback){
        $.post(DELETE_URL, {id:id}, function(){
                delete _categories.user[id];
                _createOrderLists();

                $(document).trigger('categoryDeleted');

                callback();
        }, 'json');
    }

    function getAllCategories(){
        return $.extend({}, _categories, true);
    }

    function getSystemCategories(){
        return $.extend({}, _categories.system, true);
    }

    function getUserCategories(){
        return $.extend({}, _categories.user, true);
    }

    function getUserCategoriesKeysOrderedByName(){
        return $.extend({}, _order.user, true);
    }

    function getUserCategoryNameById(id){
        if (_categories.user[id])
            return _categories.user[id]["name"];
        else
            return '';
    }

    function getRecentCategories(){
        var list = {};

        for (var key in _categories.recent) {
            list[_categories.recent[key]] = _categories.user[_categories.recent[key]];
        }

        return list;
    }

    function _treeAddChildren(arrParent, idParent) {
        // fill parent categories
        var cat;
        for (var key in _categories.user) {
            cat = _categories.user[key];
            if (cat.parent == idParent) {
                arrParent[cat.id] = $.extend({children: []}, cat);
                _treeAddChildren(arrParent[cat.id].children, cat.id);
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

        // recursive function
        _treeAddChildren(tree, "0");

        return tree;
    }

    // #886. для корректной сортировки по имени
    function _treeAddChildrenOrdered(arrParent, idParent) {
        // fill parent categories
        var cat;

        for (var row in _order.user) {
            cat = _categories.user[_order.user[row]];
            if (cat && cat.parent == idParent) {
                arrParent.push($.extend({children: []}, cat));
                _treeAddChildrenOrdered(arrParent[arrParent.length-1].children, cat.id);
            }
        }
    }

    function getUserCategoriesTreeOrdered() {
        if (!_categories)
            return null;

        var tree = [];

        // recursive function
        _treeAddChildrenOrdered(tree, "0");

        return tree;
    }

    function getUserCategoriesByType(){
        // @TODO implement getUserCategoriesByType
    }

    function getChildrenByParentId(id) {
        var arr = [];
        var parent = _categories.user[id];
        
	if (!parent){
            return arr;
	}

        var parentId = parent.id;
        for (var key in _categories.user) {
            if (_categories.user[key].parent == parentId)
                arr.push(_categories.user[key]);
        }

        return arr;
    }

    function isParentCategory(id){        
            return (_categories.user[id] &&  _categories.user[id].parent == '0') ? true : false;
    }

    // reveal some private things by assigning public pointers
    return {
        load:load,
        add:add,
        editById: editById,
        deleteById: deleteById,
        getAllCategories: getAllCategories,
        getRecentCategories: getRecentCategories,
        getSystemCategories:getSystemCategories,
        getUserCategories:getUserCategories,
        getUserCategoriesKeysOrderedByName: getUserCategoriesKeysOrderedByName,
        getUserCategoryNameById: getUserCategoryNameById,
        getUserCategoriesTree: getUserCategoriesTree,
        getUserCategoriesTreeOrdered: getUserCategoriesTreeOrdered,
        getUserCategoriesByType:getUserCategoriesByType,
        isParentCategory: isParentCategory,
        getChildrenByParentId: getChildrenByParentId
    };
}(); // execute anonymous function to immediatly return object
