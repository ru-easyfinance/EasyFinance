$(document).ready(function(){
    //loader
    var tmpImgUrl = null;
    if (res.article){
        $("div.editor #autor").val(res.article.author || '')
        $("div.editor #date").val(res.article.date || '')
        $("div.editor #url").val(res.article.url || '')
        $("div.editor #title").val(res.article.title || '')
        $("div.editor #meta_desc").val(res.article.meta_desc || '')
        $("div.editor #meta_key").val(res.article.meta_key || '')
        $("div.editor #preview").val(res.article.preview || '')
        $("div.editor #text").val(res.article.text || '')
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

