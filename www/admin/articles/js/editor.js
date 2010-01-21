$(document).ready(function(){
    //loader
    var tmpImgUrl = null;
    $.datepicker.setDefaults({dateFormat: 'yy-mm-dd'});
    $("div.editor #date").datepicker();
    var nowDate = new Date();
    $("div.editor #date").val(nowDate.toLocaleFormat('%Y-%m-%d'));
    if (res.article){
        $("div.editor #id").val(res.article.id || '0')
        $("div.editor #autor").val(res.article.author || '0')
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
        toolbar: [["html"]]
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

    }
    printPreview();
    $('div.images form').ajaxForm();
    $('div.images form input#submitImg').click(function(){
        //alert($('div.images form').length);
        $('#imagesFormAdd').ajaxSubmit({
            
            dataType: "json",
            success: function(data){
                if(!res.images){
                    res.images = []
                }
                res.images.push({link: data.link || '',
                                 previewLink: data.previewLink || ''})
                printPreviews()
            }
        });
    })
})

