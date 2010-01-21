$(document).ready(function(){
    //loader

    //init

    $('div.images form').ajaxForm();
    $('div.images form input#submitImg').click(function(){
        //alert($('div.images form').length);
        $('#imagesFormAdd').ajaxSubmit({
            
            dataType: "json",
            success: function(data){
                alert('1')
                if(!res.images){
                    res.images = []
                }
                res.images.push({link: data.link || '',
                                 previewLink: data.previewLink || ''})
            }
        });
    })
})

