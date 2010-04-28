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
    // fix for ticket #463
    $('#login form').keypress(function(e){
        //if generated character code is equal to ascii 13 (if enter key)
        if(e.keyCode == 13){
            //submit the form
            $(e.target).closest('form').submit();
            return false;
        } else {
            return true;
        }
    });
});

