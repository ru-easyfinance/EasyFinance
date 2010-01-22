$(document).ready(function(){
    //loader
    var tmpImgUrl = null;
    $.datepicker.setDefaults({dateFormat: 'yy-mm-dd'});
    $("div.editor #date").datepicker();
    var nowDate = new Date();
    $("div.editor #date").val(nowDate.toLocaleFormat('%Y-%m-%d'));
    if (res.article){
        $("div.editor #id").val(res.article.id || '0')
        $("div.editor #author").val(res.article.author || '0')
        if(res.article.date){
            $("div.editor #date").val(res.article.date)
        }
        $("div.editor #url").val(res.article.url || '0')
        $("div.editor #title").val(res.article.title || '0')
        $("div.editor #meta_desc").val(res.article.meta_desc || '0')
        $("div.editor #meta_key").val(res.article.meta_key || '0')
        $("div.editor #preview").val(res.article.preview || '0')
        $("div.editor #text").val(res.article.text || '0')
    }

    //init
    $('div.editor #preview').htmlarea({
        // Override/Specify the Toolbar buttons to show
        toolbar: []
    });
    $("div.editor #text").htmlarea({
        // Override/Specify the Toolbar buttons to show
        toolbar: [
            ["html"], ["bold", "italic", "underline", "|", "forecolor"],
            ["h1", "h2", "h3", "h4", "h5", "h6"],
            ["link", "unlink", "|"],
            [{
                // This is how to add a completely custom Toolbar Button
                css: "Image",
                text: "Добавить картинку",
                action: function(btn) {
                    var jHtmlArea = this;
                    tmpImgUrl = null;
                    $('#imgSelector').dialog({
                        autoopen: true,
                         buttons: {
                            "Отмена": function() {
                                $(this).dialog("close");
                            }
                         },
                        close: function(){
                            if(tmpImgUrl != null){
                                jHtmlArea.image(tmpImgUrl)
                            }
                            $('#imgSelector').dialog('destroy')
                        }
                    })
                    // 'this' = jHtmlArea object
                    // 'btn' = jQuery object that represents the <A> "anchor" tag for the Toolbar Button

                    // Take some action or Do Something Here
                }
            }]
        ]
    });
    function printPreview(){
        var html_previewer = '', html_viewer = '', img_ids = '';
        for(var key in res.images){
            if (res.images[key]){
                html_previewer += '<tr id="'+key+'"><td><img src="'+res.images[key].previewLink+'"></td><td>'+res.images[key].link+'</td><td class="del"> Удалить </td></tr>';
                html_viewer += '<div id="'+key+'"><img src="'+res.images[key].previewLink+'"></div>';
                img_ids += key + ';'
            }
        }
        $("div.editor #ides").val(img_ids);
        $('div.imgSelector').html(html_viewer);
        $('div.imgSelector div').click(function(){
            tmpImgUrl = res.images[$(this).attr('id')].link;
            $('#imgSelector').dialog('close');
        })
        $('div.images table').html(html_previewer)
        $('div.images table td.del').click(function(){
            var id = $(this).closest('tr').attr('id');
            $.post('index.php', {id : id}, function(data){
                delete res.images[id];
                printPreview();
            },'json')
        })
    }
    printPreview();

    $('div.editor form#general_img #image').click(function(){
        //alert($('div.images form').length);
        $('div.editor form#general_img').ajaxSubmit({

            dataType: "json",
            success: function(data){
                
            }
        });
    });

    $('div.images form').ajaxForm();
    $('div.images form input#submitImg').click(function(){
        //alert($('div.images form').length);
        $('#imagesFormAdd').ajaxSubmit({
            
            dataType: "json",
            success: function(data){
                if(!res.images){
                    res.images = []
                }
                res.images[data.id] = {link: data.link || '',
                                 previewLink: data.previewLink || ''};
                printPreviews();
            }
        });
    });
});

