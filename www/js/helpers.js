$(document).ready(function() {

  //Глобальный стиль для qTip элементов
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

if(!$.cookie('help')){

 $("#review").qtip({
   content: 'Описание основных элементов и сервисов',
   show: {delay: 1000},
   position: {target: 'mouse'},
   style: 'mystyle'
})
$("#feed").qtip({
   content: 'Мнения пользователей о работе сайта и их пожелания',
   show: {delay: 1000},
   position: {target: 'mouse'},
   style: 'mystyle'
})
$("#blog").qtip({
   content: 'Корпоративный блог',
   show: {delay: 1000},
   position: {target: 'mouse'},
   style: 'mystyle'
})
$("#forum").qtip({
   content: 'Обсуждение вопросов пользователей',
   show: {delay: 1000},
   position: {target: 'mouse'},
   style: 'mystyle'
})
$("#rules").qtip({
   content: 'Инструкция по примению',
   show: {delay: 1000},
   position: {target: 'mouse'},
   style: 'mystyle'
})
$("#secur").qtip({
   content: 'Политика безопасности сайта и рекомендации пользователю',
   show: {delay: 1000},
   position: {target: 'mouse'},
   style: 'mystyle'
})
$("#about").qtip({
   content: 'Основная информация и факты о компании',
   show: {delay: 1000},
   position: {target: 'mouse'},
   style: 'mystyle'
})
$("#fnlogin").qtip({
   content: 'Имя Вашего аккаунта на сайте',
   show: {delay: 1000},
   position: {target: 'mouse'},
   style: 'mystyle'
})
$("#pass").qtip({
   content: 'Секретный ключ доступа к Вашему аккаунту',
   show: {delay: 1000},
   position: {target: 'mouse'},
   style: 'mystyle'
})
$("#autoLogin").qtip({
   content: 'Автоматический вход в аккаунт',
   show: {delay: 1000},
   position: {target: 'mouse'},
   style: 'mystyle'
})
$("#addacc").qtip({
   content: 'Создать новый счет',
   show: {delay: 1000},
   position: {target: 'mouse'},
   style: 'mystyle'
})
$("#add_category").qtip({
   content: 'Прежде чем заводить новую категорию, удостоверьтесь, что в справочнике нет подходящей.',
   show: {delay: 1000},
   position: {target: 'mouse'},
   style: 'mystyle'
})
$("#namecat").qtip({
   content: 'Введите название категории. Например, «Автомобиль»',
   show: {delay: 1000},
   position: {target: 'mouse'},
   style: 'mystyle'
})
$("#subcat").qtip({
   content: 'Введите название подкатегории. Например, «Бензин»',
   show: {delay: 1000},
   position: {target: 'mouse'},
   style: 'mystyle'
})
$("#cattype").qtip({
   content: 'Расходная, доходная или универсальная',
   show: {delay: 1000},
   position: {target: 'mouse'},
   style: 'mystyle'
})
$("#catsys").qtip({
   content: 'Выберете категорию, которой будет соответствовать Ваша',
   show: {delay: 1000},
   position: {target: 'mouse'},
   style: 'mystyle'
})
$("h3:contains('Регулярные транзакции')").qtip({
   content: 'Финансовые операции, совершаемые с определенной регулярностью:раз неделю, 1ого числа, по четным дням; например: зарплата, алименты и т.д.',
   show: {delay: 1000},
   position: {target: 'mouse'},
   style: 'mystyle'
})
$(".hasDatepicker").qtip({
   content: 'Выбрать месяц для просмотра календаря',
   show: {delay: 1000},
   position: {target: 'mouse'},
   style: 'mystyle'
})
$("#op_account").qtip({
   content: 'Счет с которого Вы переводите деньги',
   show: {delay: 1000},
   position: {target: 'mouse'},
   style: 'mystyle'
})
$("#op_type").qtip({
   content: 'Расход, доход или перевод между счетами',
   show: {delay: 1000},
   position: {target: 'mouse'},
   style: 'mystyle'
})
$("#op_category").qtip({
   content: 'Выберите категорию, т.е. статью бюджета, в рамках которой осуществляется данная операция, например, категория зарплата',
   show: {delay: 1000},
   position: {target: 'mouse'},
   style: 'mystyle'
})
$("#op_amount").qtip({
   content: 'Введите сумму операции в валюте счета',
   show: {delay: 1000},
   position: {target: 'mouse'},
   style: 'mystyle'
})
$("#op_date").qtip({
   content: 'Дата совершения операции в формате дд.мм.гггг. По умолчанию текущая',
   show: {delay: 1000},
   position: {target: 'mouse'},
   style: 'mystyle'
});
$("#op_tags").qtip({
   content: 'Пометки для быстрого поиска. Например: аванс',
   show: {delay: 1000},
   position: {target: 'mouse'},
   style: 'mystyle'
});
$("#op_comment").qtip({
   content: 'Описание совершенной операции, например, аванс за сентябрь',
   show: {delay: 1000},
   position: {target: 'mouse'},
   style: 'mystyle'
});
$("#op_btn_Save").qtip({
   content: 'Внести новые данные',
   show: {delay: 1000},
   position: {target: 'mouse'},
   style: 'mystyle'
})
$("#op_btn_Cancel").qtip({
   content: 'Отказаться от внесения новых данных',
   show: {delay: 1000},
   position: {target: 'mouse'},
   style: 'mystyle'
})
$("#dateFrom").qtip({
   content: 'Дата начала периода, дд.мм.гггг',
   show: {delay: 1000},
   position: {target: 'mouse'},
   style: 'mystyle'
})
$("#dateTo").qtip({
   content: 'Дата конца периода, дд.мм.гггг',
   show: {delay: 1000},
   position: {target: 'mouse'},
   style: 'mystyle'
})
$("strong:contains('Мои долги')").qtip({
   content: 'Суммарные показатели счетов: полученные, кредиты, кредитные карты',
   show: {delay: 1000},
   position: {target: 'mouse'},
   style: 'mystyle'
})
$(".addmessage").qtip({
   content: 'Расскажите, что вам нравится на сайте, а чего не хватает. Мы обязательно учтем ваши пожелания и включим их в график работ.',
   show: {delay: 1000},
   position: {target: 'mouse'},
   style: 'mystyle'
})
$("strong:contains('Инвестиции')").qtip({
   content: 'Суммарные показатели счетов: Акции, ОФБУ, ПИФ, металлические счета',
   show: {delay: 1000},
   position: {target: 'mouse'},
   style: 'mystyle'
})
$("strong:contains('Деньги')").qtip({
   content: 'Суммарные показатели счетов: наличные, электронные деньги, дебетовые карты, депозиты',
   show: {delay: 1000},
   position: {target: 'mouse'},
   style: 'mystyle'
})
$("strong:contains('Долги мне')").qtip({
   content: 'Суммарные показатели счетов: Займы выданные',
   show: {delay: 1000},
   position: {target: 'mouse'},
   style: 'mystyle'
})
$("strong:contains('Имущество')").qtip({
   content: 'Суммарные показатели счетов Имущество',
   show: {delay: 1000},
   position: {target: 'mouse'},
   style: 'mystyle'
})

}
})