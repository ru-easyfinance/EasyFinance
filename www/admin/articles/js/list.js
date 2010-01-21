$(document).ready(function(){
    if(typeof(res) != 'object'){
        return false;
    }
    var htmlArticle = '<table>'
    for(var key in res){
        if (typeof(res[key]) == 'object'){
            htmlArticle += '<tr>'
                + '<td class="date">' + (res[key].date||'! Нет !') + '</td>'
                + '<td class="title">' + (res[key].title||'') + '</td>'
                + '<td class="status">' + (res[key].status ? 'Опубликована' : 'Не опубликована') + '</td>'
                + '<td class="remove"><a href="index.php?page=articleDel&id=' + (res[key].id||'0') + '"> Удалить </a></td>'
                + '<td class="edit"><a href="index.php?page=editor&id=' + (res[key].id||'0') + '"> Редактировать </a></td>'
            if (res[key].status){
                htmlArticle += '<td class="public event"> &nbsp; </td>';
            }else{
                htmlArticle += '<td class="public"><a href="index.php?page=public&id=' + (res[key].id||'0') + '"> Публиковать </a></td>';
            }
            htmlArticle += '</tr>';
        }
    }
    htmlArticle += '</table>'
    $('div.list div.body').html(htmlArticle);
});

