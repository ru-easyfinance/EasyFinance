$(document).ready(function() {
    /////////////////////////////////////////////////Cтили
    /**
     * Классическое отображение подсказки
     * реализовано как будующая фича
     */
    $.fn.qtip.styles.modern = { // Last part is the name of the style
        width: 200,
        background: '#F6F6F6',
        color: '#303030',
        textAlign: 'center',
        show: 'mouseover',
        hide: 'mouseout',
        border: {
            width: 1,
            radius: 2,
            color: '#20201E'
        },
        tip: 'bottomRight',
        style: {
            name: 'grey' // Inherit from preset style
        }
    }

    /**
     * Старое "голубое отображение"
     */
    $.fn.qtip.styles.mystyle = { // Last part is the name of the style
        width: 200,
        background: '#abcdef',
        color: 'black',
        textAlign: 'center',
        show: 'mouseover',
        hide: 'mouseout',
        border: {
            width: 3,
            radius: 2,
            color: '#f5f5ff'
        },
        tip: 'bottomRight',
        style: {
            name: 'blue' // Inherit from preset style
        }
    }
    if($.cookie('tooltip') != '0'){
        initToltips('modern')
    }

})
    ///////////////////////////////////////////////////////////////////////////
    /**
     * Инициализация всплывающих подсказок
     * @param styleTheme {str} название стиля отображения
     * @return void
     */
    function initToltips(styleTheme){
        $(document).ready(function() {
            $("#review").qtip({
                content: 'Описание основных элементов и сервисов',
                show: {delay: 1000},
                position: {target: 'mouse'},
                style: styleTheme
            })
            $("#feed").qtip({
                content: 'Мнения пользователей о работе сайта и их пожелания',
                show: {delay: 1000},
                position: {target: 'mouse'},
                style: styleTheme
            })
            $("#blog").qtip({
                content: 'Корпоративный блог',
                show: {delay: 1000},
                position: {target: 'mouse'},
                style: styleTheme
            })
            $("#forum").qtip({
                content: 'Обсуждение вопросов пользователей',
                show: {delay: 1000},
                position: {target: 'mouse'},
                style: styleTheme
            })
            $("#rules").qtip({
                content: 'Инструкция по примению',
                show: {delay: 1000},
                position: {target: 'mouse'},
                style: styleTheme
            })
            $("#secur").qtip({
                content: 'Политика безопасности сайта и рекомендации пользователю',
                show: {delay: 1000},
                position: {target: 'mouse'},
                style: styleTheme
            })
            $("#about").qtip({
                content: 'Основная информация и факты о компании',
                show: {delay: 1000},
                position: {target: 'mouse'},
                style: styleTheme
            })
            $("#fnlogin").qtip({
                content: 'Имя Вашего аккаунта на сайте',
                show: {delay: 1000},
                position: {target: 'mouse'},
                style: styleTheme
            })
            $("#pass").qtip({
                content: 'Секретный ключ доступа к Вашему аккаунту',
                show: {delay: 1000},
                position: {target: 'mouse'},
                style: styleTheme
            })
            $("#autoLogin").qtip({
                content: 'Автоматический вход в аккаунт',
                show: {delay: 1000},
                position: {target: 'mouse'},
                style: styleTheme
            })
            $("#addacc").qtip({
                content: 'Создать новый счет',
                show: {delay: 1000},
                position: {target: 'mouse'},
                style: styleTheme
            })
            $("#add_category").qtip({
                content: 'Прежде чем заводить новую категорию, удостоверьтесь, что в справочнике нет подходящей.',
                show: {delay: 1000},
                position: {target: 'mouse'},
                style: styleTheme
            })
            $("#namecat").qtip({
                content: 'Введите название категории. Например, «Автомобиль»',
                show: {delay: 1000},
                position: {target: 'mouse'},
                style: styleTheme
            })
            $("#subcat").qtip({
                content: 'Введите название подкатегории. Например, «Бензин»',
                show: {delay: 1000},
                position: {target: 'mouse'},
                style: styleTheme
            })
            $("#cattype").qtip({
                content: 'Расходная, доходная или универсальная',
                show: {delay: 1000},
                position: {target: 'mouse'},
                style: styleTheme
            })
            $("#catsys").qtip({
                content: 'Выберете категорию, которой будет соответствовать Ваша',
                show: {delay: 1000},
                position: {target: 'mouse'},
                style: styleTheme
            })
            $("h3:contains('Регулярные операции')").qtip({
                content: 'Финансовые операции, совершаемые с определенной регулярностью:раз неделю, 1ого числа, по четным дням; например: зарплата, алименты и т.д.',
                show: {delay: 1000},
                position: {target: 'mouse'},
                style: styleTheme
            })
            $(".hasDatepicker").qtip({
                content: 'Выбрать месяц для просмотра календаря',
                show: {delay: 1000},
                position: {target: 'mouse'},
                style: styleTheme
            })
            $("#op_account").qtip({
                content: 'Счет с которого Вы переводите деньги',
                show: {delay: 1000},
                position: {target: 'mouse'},
                style: styleTheme
            })
            $("#op_type").qtip({
                content: 'Расход, доход или перевод между счетами',
                show: {delay: 1000},
                position: {target: 'mouse'},
                style: styleTheme
            })
            $("#op_category").qtip({
                content: 'Выберите категорию, т.е. статью бюджета, в рамках которой осуществляется данная операция, например, категория зарплата',
                show: {delay: 1000},
                position: {target: 'mouse'},
                style: styleTheme
            })
            $("#op_amount").qtip({
                content: 'Введите сумму операции в валюте счета',
                show: {delay: 1000},
                position: {target: 'mouse'},
                style: styleTheme
            })
            $("#op_date").qtip({
                content: 'Дата совершения операции в формате дд.мм.гггг. По умолчанию текущая',
                show: {delay: 1000},
                position: {target: 'mouse'},
                style: styleTheme
            });
            $("#op_tags").qtip({
                content: 'Пометки для быстрого поиска. Например: аванс',
                show: {delay: 1000},
                position: {target: 'mouse'},
                style: styleTheme
            });
            $("#op_comment").qtip({
                content: 'Описание совершенной операции, например, аванс за сентябрь',
                show: {delay: 1000},
                position: {target: 'mouse'},
                style: styleTheme
            });

            /*
            $("#op_btn_Save").qtip({
                content: 'Внести новые данные',
                show: {delay: 1000},
                position: {target: 'mouse'},
                style: styleTheme
            })
            $("#op_btn_Cancel").qtip({
                content: 'Отказаться от внесения новых данных',
                show: {delay: 1000},
                position: {target: 'mouse'},
                style: styleTheme
            })
            */

            $("#dateFrom").qtip({
                content: 'Дата начала периода, дд.мм.гггг',
                show: {delay: 1000},
                position: {target: 'mouse'},
                style: styleTheme
            })
            $("#dateTo").qtip({
                content: 'Дата конца периода, дд.мм.гггг',
                show: {delay: 1000},
                position: {target: 'mouse'},
                style: styleTheme
            })
            $("strong:contains('Мои долги')").qtip({
                content: 'Суммарные показатели счетов: полученные, кредиты, кредитные карты',
                show: {delay: 1000},
                position: {target: 'mouse'},
                style: styleTheme
            })
            $(".addmessage").qtip({
                content: 'Расскажите, что вам нравится на сайте, а чего не хватает. Мы обязательно учтем ваши пожелания и включим их в график работ.',
                show: {delay: 1000},
                position: {target: 'mouse'},
                style: styleTheme
            })
            $("strong:contains('Инвестиции')").qtip({
                content: 'Суммарные показатели счетов: Акции, ОФБУ, ПИФ, металлические счета',
                show: {delay: 1000},
                position: {target: 'mouse'},
                style: styleTheme
            })
            $("strong:contains('Деньги')").qtip({
                content: 'Суммарные показатели счетов: наличные, электронные деньги, дебетовые карты, депозиты',
                show: {delay: 1000},
                position: {target: 'mouse'},
                style: styleTheme
            })
            $("strong:contains('Долги мне')").qtip({
                content: 'Суммарные показатели счетов: Займы выданные',
                show: {delay: 1000},
                position: {target: 'mouse'},
                style: styleTheme
            })
            $("strong:contains('Имущество')").qtip({
                content: 'Суммарные показатели счетов Имущество',
                show: {delay: 1000},
                position: {target: 'mouse'},
                style: styleTheme
            })

            /*-----------------------Бюджет-----------------------------*/
            $('#btnBudgetWizard').qtip({
               content: 'Создать новый или отредактировать уже существующий бюджет.',
               show: {delay: 1000},
               position: {target: 'mouse'},
               style: 'mystyle'
            });

            $('#budget .w1,#master .w1').qtip({
               content: 'Категория, по которой будет учитываться бюджет.',
               show: {delay: 1000},
               position: {target: 'mouse'},
               style: 'mystyle'
            });

            $('#budget .w2,#master .w2').qtip({
               content: 'Сумма, выделенная в бюджете на эту категорию.',
               show: {delay: 1000},
               position: {target: 'mouse'},
               style: 'mystyle'
            });

            $('#budget .w3,#master .w3').qtip({
               content: 'Риска в линии времени отображает позицию текущей даты в выбранном периоде. \n\
    Зелёная полоска сигнализирует, что вы укладывайтесь в бюджет, а красная - что с текущим темпом трат, вы можете не уложиться в планируемую сумму.',
               show: {delay: 1000},
               position: {target: 'mouse'},
               style: 'mystyle'
            });

            $('#budget .w5,#master .w5').qtip({
               content: 'Остаток - это разница между планом и фактом.',
               show: {delay: 1000},
               position: {target: 'mouse'},
               style: 'mystyle'
            });
        

           //$('.w4').qtip({
           //    content: 'Средний расход(доход) за предыдущие три месяца по выбранной категории',
           //    show: {delay: 1000},
           //    position: {target: 'mouse'},
           //    style: 'mystyle'
           // });

            /*----------------------------------------------------------*/
        })
    }

    /**
     * Разрушение всплывающих подсказок
     * @return void
     */
    function destroyToltips(){
        $(document).ready(function() {
            $("#review").qtip('destroy');
            $("#feed").qtip('destroy');
            $("#blog").qtip('destroy');
            $("#forum").qtip('destroy');
            $("#rules").qtip('destroy');
            $("#secur").qtip('destroy');
            $("#about").qtip('destroy');
            $("#fnlogin").qtip('destroy');
            $("#pass").qtip('destroy');
            $("#autoLogin").qtip('destroy');
            $("#addacc").qtip('destroy');
            $("#add_category").qtip('destroy');
            $("#namecat").qtip('destroy');
            $("#subcat").qtip('destroy');
            $("#cattype").qtip('destroy');
            $("#catsys").qtip('destroy');
            $("h3:contains('Регулярные операции')").qtip('destroy');
            $(".hasDatepicker").qtip('destroy');
            $("#op_account").qtip('destroy');
            $("#op_type").qtip('destroy');
            $("#op_category").qtip('destroy');
            $("#op_amount").qtip('destroy');
            $("#op_date").qtip('destroy');
            $("#op_tags").qtip('destroy');
            $("#op_comment").qtip('destroy');
            //$("#op_btn_Save").qtip('destroy');
            //$("#op_btn_Cancel").qtip('destroy');
            $("#dateFrom").qtip('destroy');
            $("#dateTo").qtip('destroy');
            $("strong:contains('Мои долги')").qtip('destroy');
            $(".addmessage").qtip('destroy');
            $("strong:contains('Инвестиции')").qtip('destroy');
            $("strong:contains('Деньги')").qtip('destroy');
            $("strong:contains('Долги мне')").qtip('destroy');
            $("strong:contains('Имущество')").qtip('destroy');

            /*-----------------------Бюджет-----------------------------*/
            $('#btnBudgetWizard').qtip('destroy');
            $('.w1').qtip('destroy');
            $('.w2').qtip('destroy');
            $('.w3').qtip('destroy');
           //$('.w4').qtip('destroy');
            /*----------------------------------------------------------*/
        })
    }

