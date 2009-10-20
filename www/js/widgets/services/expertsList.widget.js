/**
 * @desc Editable Expert Info Widget
 * @author Andrey [Jet] Zharikov
 */

easyFinance.widgets.expertsList = function(){
    // private constants
    var EXPERTS_LIST_URL = '/expertsList/getExpertsList/';
    var EXPERT_URL = '/expertsList/getProfileById/';

    // private variables
    var _$node = null;
    var _$list = null;
    var _$details = null;
    var _$detailsMenu = null;

    // private functions
    function _showExpert() {
        $.post(EXPERT_URL, {id: this.id.split("_")[1]}, function(profile){
            _$details.empty();

            var $expert = $('<dl>').addClass('experts_one');

            // title
            $expert.append($('<dt>').text(profile.fio));

            var $dd = $('<dd>');
            // photo
            $dd.append($('<img>')
                .attr('src', profile.photo)
                .attr('alt', profile.fio)
            );

            // rating
            var starsW = (profile.rating*20).toString();
            $dd.append($('<ul>')
                .addClass('rating')
                    .html(
                        '<li class="h">Рейтинг:</li>' +
                        '<li class="star">' +
                            '<div style="width: ' + starsW + '%;">' + starsW + '%</div>' +
                        '</li>' +
                        '<li class="result"><b>' + profile.votes.toString() + '</b> голосов</li>'
                    )
            );

            // full description
            $dd.append($('<p>').addClass('e-l-p').html(profile.fullInfo));
            
            // mail link
            _$detailsMenu.find('#ic2 a').attr('href', '/mail#' + key);

            $expert.append($dd);

            $expert.append($('<h2>').text('Список услуг'));
            var $services = $('<div>').addClass('services');
            for (var key in profile.services) {
                if (profile.services[key].checked) {
                    $services.append(
                        $('<div>').addClass('serv_name')
                        .text(profile.services[key].title + ' ')
                        .append($('<b>')
                            .text('(' + profile.services[key].price + ' руб.)'))
                        .append($('<a>')
                            .text('Заказать услугу')
                            .attr('href', '/mail#' + profile.fio + ";" + profile.services[key].title))
                    );

                    $services.append(
                        $('<div>').addClass('serv_descr')
                            .html(profile.services[key].comment)
                    );
                }
            }
            $expert.append($services);
            
            var $files = $('<div>').addClass('files').html('<h2>Сертификаты</h2>');
            //var $line = $('<div>').addClass('line').appendTo($files);
            for (var key in profile.certificates) {
                var $link = $('<a>')
                    .attr('href', profile.certificates[key].image)
                    .attr('title', profile.certificates[key].comment);
                $link.append($('<img>').attr('src', profile.certificates[key].smallImage));
                $files.append($link);
            }
            $expert.append($files);

            $files.find("a").fancybox();

            _$details.append($expert);

            _$list.hide();
            _$detailsMenu.show();
            _$details.show();
        }, "json");
    }

    function _showList(experts) {
        _$list.empty().hide();
        for (var key in experts) {
            var $expert = $('<dl>').addClass('experts_list_in');

            // title
            $expert.append($('<dt>').text(experts[key].fio));
            
            var $dd = $('<dd>');
            // photo
            $dd.append($('<img>')
                .attr('src', experts[key].smallPhoto)
                .attr('alt', experts[key].fio)
            );

            // rating
            var starsW = (experts[key].rating*20);
            $dd.append($('<ul>')
                .addClass('rating')
                    .html(
                        '<li class="h">Рейтинг:</li>' +
                        '<li class="star">' +
                            '<div style="width: ' + starsW + '%;">' + starsW + '%</div>' +
                        '</li>' +
                        '<li class="result"><b>' + experts[key].votes + '</b> голосов</li>'
                    )
            );

            // short description
            $dd.append($('<p>').addClass('e-l-p').text(experts[key].shortInfo));

            // link
            $dd.append($('<div>').addClass('more')
                .append($('<span>')
                    .attr('id', 'expert_' + key)
                    .text('Читать полностью').click(_showExpert)
            ));

            $expert.append($dd);

            _$list.append($expert);
        }
        _$list.show();
    }

    // public variables

    // public functions
    /**
     * @desc init widget
     * @usage init(nodeSelector, model)
     */
    function init(nodeSelector) {
        _$node = $(nodeSelector);
        _$list = _$node.children('div:first');
        _$details = _$node.children('div:last');
        _$detailsMenu = _$node.children(':first');

        _$detailsMenu.find('#ic1 a').click(function(){
           _$detailsMenu.hide();
           _$details.hide();
           _$list.show();
        });

        $.post(EXPERTS_LIST_URL, '', _showList, "json");

        return this;
    }

    // reveal some private things by assigning public pointers
    return {
        init: init
    };
}(); // execute anonymous function to immediatly return object