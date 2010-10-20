var wdgtReview = (function() {
    var URL_PREFIX = "/upload/video/review/";

    var container,
        dialog,
        playerContainer,
        player;

    function onPreviewClick(evt) {
        var trgt = $(evt.target).closest('.b-review-clip');
        var title = trgt.attr('title');

        var index = trgt.siblings().length;
        dialog.dialog('open').dialog('option', 'title', title);
        playClip(index);
    }

    function playClip(index) {
        if (!player) {
            player = flowplayer(playerContainer.attr('id'), "/swf/flowplayer-3.1.5.swf", {
                clip: {
                    autoPlay: false,
                    autoBuffering: false
                }
            })
        }

        player.play(URL_PREFIX + index + '.mp4');
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