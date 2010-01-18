/**
 * @desc Отображение справки и видео
 * @author Andrey [Jet] Zharikov
 */

easyFinance.widgets.help = function(){
    // private variables
    var _$node = null;
    var _$divTitle = null;
    var _$hrefVideo = null;
    var _$divLinks = null;
    var _player = null;
    var _strVideoPrefix = "/upload/video/help/";

    var _videos = {
        "newOperation" : {
            title: "Добавление операции",
            file: "newOperation.mp4"
        },
        "newCategory" : {
            title: "Добавление категории",
            file: "newCategory.mp4"
        },
        "newAccount" : {
            title: "Добавление счёта",
            file: "newAccount.mp4"
        },
        "newTarget" : {
            title: "Создание финансовой цели",
            file: "newTarget.mp4"
        },
        "newBudget" : {
            title: "Планирование бюджета",
            file: "newBudget.mp4"
        }
    }

    // private functions


    // public functions
    function init(nodeSelector, isDialog) {
        _$node = $(nodeSelector);

        if (_$node.length == 0)
            return;
        
        _$divTitle = _$node.find(".title");
        _$hrefVideo = _$node.find(".video");

        var videoLinkClicked = function() {
            showVideo($(this).val());

            return false;
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

        $("<br>").appendTo(_$divLinks);
        $("<a>")
            .text("Все видео")
            .attr("href", "/help")
            .appendTo(_$divLinks)
            .wrap("<b></b>");
        $("<br>").appendTo(_$divLinks);

        if (isDialog)
            _$node.dialog({title: "Видео-гид", bgiframe: true, autoOpen: false, width: 662, modal:true});
    }

    function showVideo(strVideo) {
        if (!_videos[strVideo])
            return;

        _$divTitle.html('<b>'+_videos[strVideo].title+'</b>');

        if (!_player) {
            _$hrefVideo.attr("href", _strVideoPrefix + _videos[strVideo].file)
            _player = flowplayer("playerHelp", "/swf/flowplayer-3.1.5.swf", {
                clip: {
                    autoPlay: false,
                    autoBuffering: false
                }
            });
        } else {
            _player.play(_strVideoPrefix + _videos[strVideo].file);
        }
    }

    return {
        init: init,
        showVideo: showVideo
    };
}();