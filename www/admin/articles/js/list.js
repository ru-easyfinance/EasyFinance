$(document).ready(function(){
    if(typeof(res) != 'object'){
        return false;
    }
    var htmlArticle = '';
    for(var key in res){
        if (typeof(res[key]) == 'object'){
            htmlArticle += '<tr>' +
                '<td class="date">' + (res[key].date||'! Нет !') + '</td>' +
                '<td class="title"><a href="/articles/' + (res[key].id||'0') + '" target=_blank>' + (res[key].title||'') + '</a></td>'+
                '<td class="status">' + (res[key].status == '1' ? 'Опубликована' : 'Не опубликована') + '</td>'+
                '<td class="remove"><a href="index.php?page=articleDel&id=' + (res[key].id||'0') + '"> Удалить </a></td>'+
                '<td class="edit"><a href="index.php?page=editor&id=' + (res[key].id||'0') + '"> Редактировать </a></td>';
            if (res[key].status == '1'){
                htmlArticle += '<td class="public event"> &nbsp; </td>';
            }else{
                htmlArticle += '<td class="public"><a href="index.php?page=public&id=' + (res[key].id||'0') + '"> Публиковать </a></td>';
            }
            htmlArticle += '</tr>';
        }
    }
    $('div.list div.body tbody').html(htmlArticle);
});

