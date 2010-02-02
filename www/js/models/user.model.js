easyFinance.models.user = function(){
    var _data = data
    /**
     * Инициирует модель из реса - пока не реализовано со стороны сервера
     */
    function init(data){
        data = _data;
    }
    /**
     * Перезагружает данные пользователя
     * @param calback {?function}
     * @return void
     */
    function reload(calback){
        $.get("/profile/load_main_settings/",
            {},
            function(data){
                _data = data.profile;//@todo server will be sent 'spamer'
                _data.tooltip = $.cookie('tooltip');
                _data.guide = $.cookie('guide');
                _data.activeInsertInLeftPanel = $.cookie('activelisting');
                if (typeof(calback) == 'function'){
                    calback();
                }
            },
            'json');
    }
    /**
     * Возвращает всю пользовательскую информацию
     * @return {object}
     */
    function getUserInfo(){
        return _data;
    }
    /**
     * Возвращает почту пользователя
     * @return {str}
     */
    function getEmail(){
        return _data.mail;
    }
    /**
     * Возвращает логин пользователя
     * @return {str}
     */
    function getLogin(){
        return _data.login;
    }
    /**
     * Возвращает имя пользователя
     * @return {str}
     */
    function getName(){
        return _data.name;
    }
    /**
     * Возвращает активную вкладку в левой панели
     * @return {str}
     */
    function getActiveInsertInLeftPanel(){
        return _data.activeInsertInLeftPanel;
    }
    /**
     * Сохраняет пользовательскую информацию
     * @param data {obj}
     * @return void
     */
    function setUserInfo(data){
        $.post('/profile/save_main_settings/',
            {
                getNotify: data.getNotify || '',
                login: data.login || '',
                pass: data.password || '',
                newpass: data.newPassword || '',
                confirmpass: data.confirmPassword || '', //@todo server will be add confirm pasword
                mail: data.mail || ''},
            function() {
                $.cookie('tooltip', data.tooltip, {expire: 100, path : '/', domain: false, secure : '1'});
                $.cookie('guide', data.guide, {expire: 100, path : '/', domain: false, secure : '1'});//@todo server will not write cookie
                $.cookie('activelisting', data.activeInsertInLeftPanel, {expire: 100, path : '/', domain: false, secure : '1'});
                $.jGrowl("Личные данные сохранены", {theme: 'green'});
            },
            'json');
    }
    /**
     * Устанавливает значение активной вкладки на левой панели
     * @param data {str}
     * @return void
     */
    function setActiveInsertInLeftPanel(data){
        $.cookie('activelisting', data, {expire: 100, path : '/', domain: false, secure : '1'});
    }
    /**
     * Возвращает факт показывания гида
     * @return bool
     */
    function isUsedGuide(){
        return _data.guide ? true : false;//@todo Now we use this cookie where not use guide
    }
    /**
     * Возвращает факт показывания всплывающих подсказок
     * @return bool
     */
    function isUsedTooltip(){
        return _data.tooltip ? true : false;
    }
}
