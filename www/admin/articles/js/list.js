$(document).ready(function(){
//    <table>
//                  <tbody>
//                      <tr>
//                          <td class="date">19-03-2007</td>
//                          <td class="title">Тестовая статья</td>
//                          <td class="status">Опубликована</td>
//                          <td class="remove"> Удалить </td>
//                          <td class="edit"> Редактировать </td>
//                          <td class="public event"> Опубликовать </td>
//                      </tr>
//                  </tbody>
//              </table>
//    if(typeof res != 'Array' || typeof res != 'Object'){
//        return false;
//    }
    var htmlArticle = '<table>'
    for(var key in res){
        if (typeof res.key == 'Object'){
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
    htmlArticle = '</table>'
    $('div.list div.body').html(htmlArticle);
});

