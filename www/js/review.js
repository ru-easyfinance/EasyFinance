var wdgtReview = (function() {
    var URL_PREFIX = "/upload/video/review/";

    var container,
        dialog,
        playerContainer,
        player;

    function onPreviewClick(evt) {
        var trgt = $(evt.target).closest('.b-review-clip');
        var title = trgt.attr('title');

        var index = trgt.prevAll().length + 1;
        dialog.dialog('open').dialog('option', 'title', title);
        playClip(index);
    }

    function playClip(index) {
        player = flowplayer(playerContainer.attr('id'), "/swf/flowplayer-3.1.5.swf", {
            clip: {
                url: URL_PREFIX + index + '.mp4',
                autoPlay: true,
                autoBuffering: false
            }
        })
    }

    function init() {
        container = $('.js-widget-review');

        $('.b-review-clip-preview').live('click', onPreviewClick);

        dialog = container.find('.js-review-dialog');
        dialog.dialog({title: "Обзор", bgiframe: true, autoOpen: false, modal:true, width: 'auto'});

        playerContainer = dialog.find('.js-review-playercontainer');
    }

    $(init);
})();