/**
 * @desc Отображение справки и видео
 * @author Andrey [Jet] Zharikov
 */

easyFinance.widgets.help = function(){
    // private variables
    var _$node = null;
    var _$divTitle = null;
    var _$divVideo = null;
    var _$divLinks = null;

    var _strPlayer = '<object width="580" height="360"><param name="movie" value="http://www.youtube.com/v/{videoId}&hl=ru_RU&fs=1&rel=0&color1=0x234900&color2=0x4e9e00&border=1"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="http://www.youtube.com/v/{videoId}&hl=ru_RU&fs=1&rel=0&color1=0x234900&color2=0x4e9e00&border=1" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="580" height="360"></embed></object>';

    var _videos = {
        "newOperation" : {
            title: "Добавление операции",
            videoId: "Hy0jxnbCPDc"
        },
        "newCategory" : {
            title: "Добавление категории",
            videoId: "KyM0XHZCsS8"
        },
        "newAccount" : {
            title: "Добавление счёта",
            videoId: "PvpkgfL4Wo4"
        },
        "newTarget" : {
            title: "Создание финансовой цели",
            videoId: "PvpkgfL4Wo4"
        },
        "newBudget" : {
            title: "Планирование бюджета",
            videoId: "PvpkgfL4Wo4"
        }
    }

    // private functions


    // public functions
    function init(nodeSelector) {
        _$node = $(nodeSelector);
        
        _$divTitle = _$node.find(".title");
        _$divVideo = _$node.find(".video");

        var videoLinkClicked = function() {
            showVideo($(this).val());
        }

        _$divLinks = _$node.find(".links");
        for (var key in _videos) {
            $("<a>")
                .text(_videos[key].title)
                .attr("href", "#")
                .val(key)
                .appendTo(_$divLinks)
                .click(videoLinkClicked);

            $("<br>").appendTo(_$divLinks);
        }

        _$node.dialog({title: "Видео-гид", bgiframe: true, autoOpen: false, width: 600, modal:true});
    }

    function showVideo(strVideo) {
        if (!_videos[strVideo])
            return;

        _$divTitle.html('<b>'+_videos[strVideo].title+'</b>');
        _$divVideo.html(_strPlayer.replace("{videoId}", _videos[strVideo].videoId, "g"));
    }

    return {
        init: init,
        showVideo: showVideo
    };
}();