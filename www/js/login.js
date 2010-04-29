function checkLogin() {
    if ($('#flogin').val() == '') {
        $.jGrowl("Введите логин!", {theme: 'red'});
        return false;
    }

    if ($('#pass').val() == '') {
        $.jGrowl("Введите пароль!", {theme: 'red'});
        return false;
    }

    return true;
}
    
$(document).ready(function(){
    var loginCanClick = true;
    $('#btnLogin').click(function(e){
        // @TODO: prevent double-click
        //if (!loginCanClick) {
        //    return false;
        //}

        if (document.location.pathname.indexOf("integration") != -1) {
            // #1241 авторизация без редиректа на странице интеграции
            //$("#btnLogin").attr("disabled", "disabled");

            $.post(
                "/login/",
                {
                    responseMode: "json",
                    login: $('#flogin').val(),
                    pass: $('#pass').val()
                }, function(data) {
                    // @TODO: prevent double-click
                    //$("#btnLogin").removeAttr("disabled");
                    
                    if (data) {
                        if (data.error) {
                            if (data.error.text)
                                $.jGrowl(data.error.text, {theme: 'red', life: 2500});
                        } else if (data.result) {
                            //if (data.result.text)
                            //    $.jGrowl(data.result.text, {theme: 'green', life: 2500});

                            //$("#integrationSteps").accordion("activate" , 1);
                            // перезагружаем страницу.
                            // поскольку пользователь будет уже залогинен,
                            // после обновления на странице будут данные в res
                            window.location.reload();
                        }
                    } else {
                        $.jGrowl('Ошибка на сервере!', {theme: 'red'});
                    }
                }, 'json'
            );
        } else {
            $(e.target).closest('form').submit();
        }
        
        return false;
    });

    // fix for ticket #463
    $('#login form').keypress(function(e){
        //if generated character code is equal to ascii 13 (if enter key)
        if(e.keyCode == 13){
            //submit the form
            $("#btnLogin").click();
            return false;
        } else {
            return true;
        }
    });
});

