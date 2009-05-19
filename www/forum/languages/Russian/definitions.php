<?php
/*
* Copyright 2003 - 2005 Mark O'Sullivan
* This file is part of Vanilla.
* Vanilla is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.
* Vanilla is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
* You should have received a copy of the GNU General Public License along with Vanilla; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
* The latest source code for Vanilla is available at www.lussumo.com
* Contact Mark O'Sullivan at mark [at] lussumo [dot] com
*
* Description: Russian language dictionary by Alexey Fomichov (Pansik) (www.vanillain.ru)
*
* Сorrections, additions, and interpretation of extensions by Alexey Fomichov (Pansik) pansik@gmail.com and Maxim Zabolotniy (adept)
* Version 0.9 (Marth 8th, 2008)
* "Правильное русское сообщество Vanilla - www.vanillain.ru"
*
*   Extensions interpreted so far:
*	Mark O'Sullivan's "Guest Welcome Message 3"
*	Maurice (Jazzman) Krijtenberg's "Attachments 2.1
*	Maurice (Jazzman) Krijtenberg's "Vanillacons 1.3" 
*	Maurice (Jazzman) Krijtenberg's "Poll 1.3"
*	SirNotAppearingOnThisForum's "Preview Post 2.5.1"
*	Mark O'Sullivan's "Discussion Filters 2"
* 	SirNot's "Comment Removal 2.0"
* 	WallPhone's " Comment Links 1.3.2"
*	dinoboff at hotmail dot com "Applicant Email Verification Version v.0.4.2.b"
* 	Chris Vincent's " Category Colour Changer 0.1" 
* 	SirNotAppearingOnThisForum's "Comment Removal 2.1.2" 
* 	EAdam Atlas's "Latest Discussions Prime 1.1.2"
* 	Mark O'Sullivan's "RSS2 Feed 1.0.2" 
* 	SirNotAppearingOnThisForum's "Page Manager 2.5.1"
* 	Jim Wurster's "zip2mail 1.0.6"
* 	David Harris's "MassMailer 1.0"
* 	Maurice Krijtenberg's "Account Pictures 1.2"
* 	Maurice (Jazzman) Krijtenberg's "Inline Images 1.3"
* 	SirNotAppearingOnThisForum's "Signatures 1.2"
* 	Steve Reed's "Next Unread Discussion 1.0"
* 	Maurice (Jazzman) Krijtenberg's "Thankful People 1.2"
* 	Mark O'Sullivan's "Saved Searches 2.0"
* 	Mark O'Sullivan's "Comment Protection 2.0"
*	Christophe Gragnic, Dan Richman (CrudeRSS), Mark O'Sullivan (RSS2, ATOM) "FeedPublisher 0.3.1"
*	Alexander Morland's AKA Mr Do "Dojo Files 0.941" 
*
*   
*   NOTE: follow instructions for each extension below 
*	NOTE: Terms of Use is not interpreted yet; written "I'll be ethical" for now
*  Некоторые дополнения не будут корректно отображать перевод. Будьте уверены, что удалили переменные с английскими значениями в файле дополнения.
*/
// Define the xml:lang attribute for the html tag
$Context->Dictionary['XMLLang'] = 'ru-KOI-8';
// Extensions interpretation starts

// Mark O'Sullivan's "Guest Welcome Message 3" 
// be sure to delete english duplicates in the default.php in path-to-vanilla/extensions/GuestWelcome/ folder
$Context->Dictionary["GuestWelcome"] = "<strong>Добро пожаловать!</strong>
   <br />Хотите поучаствовать в обсуждении тем? Если вы зарегистрированы, <a href=\"".GetUrl($Configuration, "people.php")."\">войдите</a>.
   <br />Если же у вас нет учетной записи, <a href=\"".GetUrl($Configuration, "people.php", "", "", "", "", "PostBackAction=ApplyForm")."\">зарегистрируйтесь сейчас</a>.";
$Context->Dictionary['Quote'] = 'цитата';

// Maurice (Jazzman) Krijtenberg's "Attachments 2.1" 
// be sure to delete english duplicates in the default.php in path-to-vanilla/extensions/Attachments/ folder
// Дополнение переведено командой Правильного русского сообщества www.vanillain.ru
$Context->Dictionary['Attachments'] = 'Вложения';
$Context->Dictionary['DeleteAttachment'] = 'Удалить';
$Context->Dictionary['AttachmentSettings'] = 'Вложения';
$Context->Dictionary['AttachmentUploadSettings'] = 'Путь к вложениям';
$Context->Dictionary['AttachmentUploadSettingsInfo'] = 'Путь к папке вложений должен быть абсолютным. Для большей безопасности лучше не использовать корневой каталог вашего сервера. Помните, у папки должны быть права на запись/чтение/замену. Можно использовать следующие тэги: %day%, %month%, %year%, %userid%. ';
$Context->Dictionary['UploadPath'] = 'Загружать файл';
$Context->Dictionary['MaximumFilesize'] = 'Максимальный размер файла <small>(в байтах)</small>';
$Context->Dictionary['AttachmentFiletypes'] = 'Разрешенные типы файлов';
$Context->Dictionary['AttachmentFiletypesNotes'] = 'Здесь вы можете добавлять типы файлов, разрешенных для загрузки на сервер. Добавляйте тип файла и его расширение. Можно указать более одного расширения, используя запятые.';
$Context->Dictionary['ApplicationType'] = 'Тип приложения';
$Context->Dictionary['FiletypeExtension'] = 'Расширение';
$Context->Dictionary['AddFiletype'] = 'Добавьте приложение/расширение другого типа';
$Context->Dictionary['AttachmentImport'] = 'Импорт вложений';
$Context->Dictionary['AttachmentImportNotes'] = 'Импортируйте вложения с ранее использованой версии данного дополнения (1.x и выше).';
$Context->Dictionary['AttachmentImportPath'] = 'Укажите путь к папке существующих вложений';
$Context->Dictionary['RememberToSetAttachmentsPermissions'] = 'Установите уровень прав пользователям для загрузки файлов (дополнение Attachments). Вы можете сделать это на странице настройки <a href="'.GetUrl($Context->Configuration, 'settings.php', '', '', '', '', 'PostBackAction=Roles').'">полномочий</a>.';
$Context->Dictionary['ErrCreateTable'] = 'Ошибка создания таблицы данного дополнения в базу!';
$Context->Dictionary['ErrCreateConfig'] = 'Ошибка сохранения настроек дополнения в файл конфигурации!';
$Context->Dictionary['ErrCreateAttachmentFolder'] = 'Ошибка при создании новой папки для вложений. Проверьте настройки дополнения.';
$Context->Dictionary['ErrAttachmentNotFound'] = 'Не могу найти файл';
$Context->Dictionary['PERMISSION_ADD_ATTACHMENTS'] = 'Добавлять вложения';
$Context->Dictionary['PERMISSION_MANAGE_ATTACHMENTS'] = 'Управлять вложениями';

// Maurice (Jazzman) Krijtenberg's "Vanillacons 1.3" 
// Url: http://lussumo.com/addons/?PostBackAction=AddOn&AddOnID=26
// be sure to delete english duplicates in the default.php in path-to-vanilla/extensions/Vanillacons/ folder
// Дополнение переведено командой Правильного русского сообщества www.vanillain.ru
$Context->Dictionary['Vanillacons'] = 'Смайлы Vanilla';
$Context->Dictionary['VanillaconsNotes'] = 'Vanillacons позволяет пользователям добавлять смайлы в свои сообщения. Для установки новых смайлов загрузите их в соответствующую директорию и нажмите кнопку "Обновить смайлы".';
$Context->Dictionary['RebuildVanillacons'] = 'Обновить смайлы';
$Context->Dictionary['VanillaconsRebuilded'] = 'Смайлы обновлены и готовы к использованию.';
$Context->Dictionary['SmiliesFound'] = 'Смайлы найдены.';


// Maurice (Jazzman) Krijtenberg's "Poll 1.3" http://lussumo.com/addons/?PostBackAction=AddOn&AddOnID=102
// be sure to delete english duplicates in the language.php in path-to-vanilla/extensions/Poll/conf/ folder
// Дополнение переведено командой Правильного русского сообщества www.vanillain.ru
$Context->Dictionary['ExtensionOptions'] = 'Настройки дополнений';
$Context->Dictionary['PollManagement'] = 'Опрос';
$Context->Dictionary['Polls'] = 'Опросы';
$Context->Dictionary['ShowActivePoll'] = 'Отображать активные опросы в панели';
$Context->Dictionary['GetPollToEdit'] = '1. Выберите опрос для редактирования';
$Context->Dictionary['ModifyPollDefinition'] = '2. Изменить описание опроса';
$Context->Dictionary['DefineNewPoll'] = 'Текст опроса';
$Context->Dictionary['CreateNewPoll'] = 'Создать опрос';
$Context->Dictionary['PollRemoved'] = 'Опрос удален';
$Context->Dictionary['PollSaved'] = 'Изменения сохранены';
$Context->Dictionary['NewPollSaved'] = 'Опрос создан';
$Context->Dictionary['PollReorderNotes'] = 'Сортируйте опросы простым перетаскиванием. Их новый порядок будет сохранен автоматически. Верхний опрос будет показываться участникам форума.';
$Context->Dictionary['PollName'] = 'Название опроса';
$Context->Dictionary['PollNameNotes'] = 'Название опроса &mdash; это заголовок или вопрос. <small>(<tt>HTML</tt> не разрешен)</small>';
$Context->Dictionary['RolesInPoll'] = 'Статусы пользователей, которым позволено принимать участие в этом опросе';
$Context->Dictionary['AddVoteOptions'] = 'Опции добавления опроса';
$Context->Dictionary['AddVoteOptionsNotes'] = 'Введите варианты ответов. Вы можете изменить их количество. <small>(<tt>HTML</tt> не разрешен)</small>';
$Context->Dictionary['VoteOption'] = 'Вариант ответа';
$Context->Dictionary['VoteCount'] = 'Количество голосов';
$Context->Dictionary['AddPollData'] = 'Добавить вариант ответа';
$Context->Dictionary['PollNameLower'] = 'Название опроса';
$Context->Dictionary['VoteOptionLower'] = 'Вариант ответа';
$Context->Dictionary['Vote'] = 'Голосовать';
$Context->Dictionary['TotalVotes'] = 'Всего голосов';
$Context->Dictionary['AddPollToDiscussion'] = 'Добавить опрос';
$Context->Dictionary['EditPollDiscussion'] = 'Редактировать опрос';
$Context->Dictionary['DeletePollDiscussion'] = 'Удалить опрос';
$Context->Dictionary['ErrPollNotFound'] = 'Опрос не найден';
$Context->Dictionary['ErrInstallExtension'] = 'Возникла ошибка при установке';
$Context->Dictionary['ErrAlreadyVoted'] = 'Вы уже голосовали';
$Context->Dictionary['PERMISSION_POLL_MANAGEMENT'] = 'Может управлять опросами';
$Context->Dictionary['PERMISSION_ADD_POLL'] = 'Может создавать опросы';
$Context->Dictionary['PollNotification'] = 'Выскажите свое мнение в опросе на тему //1';
$Context->Dictionary['TurnOff'] = 'Выключить';
$Context->Dictionary['TurnOffPollNotifications'] = 'Отключить уведомления о опросах';

// SirNotAppearingOnThisForum's "Preview Post 2.5.1" 
// Дополнение переведено командой Правильного русского сообщества www.vanillain.ru
$Context->Dictionary['PostPreview'] = 'Предпросмотр';
$Context->Dictionary['PreviewPost'] = 'Просмотреть';

// Mark O'Sullivan's "Discussion Filters 2" http://lussumo.com/addons/?PostBackAction=AddOn&AddOnID=7
// be sure to delete english duplicates in the default.php in path-to-vanilla/extensions/DiscussionFilters/ folder
// Дополнение переведено командой Правильного русского сообщества www.vanillain.ru
$Context->Dictionary["DiscussionFilters"] = "Фильтр тем";
$Context->Dictionary["BookmarkedDiscussions"] = "Закладки";
$Context->Dictionary["YourDiscussions"] = "Ваши темы";
$Context->Dictionary["PrivateDiscussions"] = "Темы шепотом";
$Context->Dictionary["PrivateComments"] = "Весь шепот";
	//Language definitions for vanilla 1.0

// dinoboff at hotmail dot com "Applicant Email Verification Version v.0.4.2.b" http://getvanilla.com/
// Дополнение переведено командой Правильного русского сообщества www.vanillain.ru
$Context->Dictionary['EmailVerification_ErrVanillaVersion'] = 'Для работы дополнения требуется Vanilla 1.0.1 или выше';
$Context->Dictionary['EmailVerificationOption'] = 'Активация по e-mail';
$Context->Dictionary['EmailVerificationOptionNote'] = 'Отметив, вы включите верификацию пользователей по e-mail. После регистрации они получат письмо, содержащее ссылку на активацию аккаунта. (Если вы выберете "Неподтвержденный" как статус для вновь зарегистрированных участников, вам все равно придется подтверждать их регистрацию).';
$Context->Dictionary['EmailVerification'] = 'Верификация по e-mail';
$Context->Dictionary['EmailVerification_ForMembership'] = 'Вам было отправлено письмо от'.$Context->Configuration['SUPPORT_EMAIL'].'. Следуйте инструкциям для активации вашего аккаунта.';
$Context->Dictionary['EmailVerification_ForApplicationReview'] = 'Вам было отправлено письмо от'.GetEmail($Context->Configuration['SUPPORT_EMAIL']).'. Следуйте инструкциям для активации вашего аккаунта.<br /> 
          После верификации вашего e-mail, вы примете участие в обсуждениях (или будете ждать одобрения администратором, в зависимости от настроек форума).';
$Context->Dictionary['EmailVerificationDonePendingApproval'] = 'Ваша заявка на участие будет рассмотрена администратором. Если заявка будет одобрена, вы получите уведомление электронной почтой.';
$Context->Dictionary['EmailVerificationRegister'] = 'Ваше участие в форуме одобрено.';
$Context->Dictionary['EmailVerificationPendingApproval'] = 'E-mail адрес проверен. Регистрация окончится после подтверждения администратором.';
$Context->Dictionary['EmailVerification_EmailValidated'] = 'e-mail корректный';
$Context->Dictionary['EmailVerification_EmailNotValidated'] = 'e-mail не корректный';
$Context->Dictionary['EmailVerification_ErrNoUser'] = 'Ваша заявка на участие не найдена.';
$Context->Dictionary['EmailVerification_ErrWrongKey'] = 'Верификационный ключ неправильный!';
$Context->Dictionary['EmailVerification_ErrAlreadyValidated'] = 'Ваша заявка на участие уже была принята.';
$Context->Dictionary['EmailVerification_ErrNoKeyInDB'] = 'Ваш верификационный ключ уже введен.';


// SirNot's "Comment Removal 2.0" http://lussumo.com/addons/?PostBackAction=AddOn&AddOnID=95
// be sure to delete english duplicates in the default.php in path-to-vanilla/extensions/CommentRemoval/ folder
// Дополнение переведено командой Правильного русского сообщества www.vanillain.ru
$Context->Dictionary['VerifyCommentDeletion'] = 'Вы уверены, что хотите безвозвратно удалить этот комментарий?';
$Context->Dictionary['VerifyDiscussionDeletion'] = 'Вы уверены, что хотите безвозвратно удалить эту тему?';
$Context->Dictionary['delete'] = 'удалить';
$Context->Dictionary['PERMISSION_REMOVE_COMMENTS'] = 'Может удалять темы и комментарии';
$Context->Dictionary['PERMISSION_REMOVE_OWN_COMMENTS'] = 'Может удалять собственне темы и комментарии (без ответов)';

// WallPhone's " Comment Links 1.3.2"
// be sure to delete english duplicates in the default.php in path-to-vanilla/extensions/CommentRemoval/ folder 
// Дополнение переведено командой Правильного русского сообщества www.vanillain.ru
$Context->Dictionary["CommentLinks_Copy"] = "Копировать URL как:";
$Context->Dictionary["CommentLinks_DblClk"] = "Двойной клик копирует URL в текщем форматировании (//1)";
$Context->Dictionary["CommentLinks_Deleted"] = "Удалено";
$Context->Dictionary["CommentLinks_Permalink"] = "Статическая ссылка";
$Context->Dictionary["CommentLinks_Whispered"] = "Прошептали";
$Context->Dictionary["CommentLinks_LoginRequired"] = "Авторизируйтесь для просмотра этой темы.";

// Chris Vincent's " Category Colour Changer 0.1" 
// Дополнение переведено командой Правильного русского сообщества www.vanillain.ru
$Context->Dictionary["CategoryColourChanger"] = "Изменить цвет раздела";
$Context->Dictionary["CategoryColours"] = "Цвета разделов";
$Context->Dictionary["EditColours"] = "Редактировать цвета";

// SirNotAppearingOnThisForum's "Comment Removal 2.1.2" 
// Дополнение переведено командой Правильного русского сообщества www.vanillain.ru
$Context->Dictionary['PERMISSION_REMOVE_COMMENTS'] = 'Может удалять любой коментарий и тему';
$Context->Dictionary['PERMISSION_REMOVE_OWN_COMMENTS'] = 'Может удалять собственные коментарии и темы (без ответов)';
$Context->Dictionary['VerifyCommentRemoval'] = 'Вы действительно хотите удалить этот комментарий?';
$Context->Dictionary['VerifyDiscussionRemoval'] = 'Вы действительно хотите удалить эту тему?';

// EAdam Atlas's "Latest Discussions Prime 1.1.2" 
// Дополнение переведено командой Правильного русского сообщества www.vanillain.ru
$Context->Dictionary['LatestDiscussions'] = 'Последняя тема';
$Context->Dictionary['LatestDiscussionsPrefs'] = 'Список последних тем';
$Context->Dictionary['LatestDiscussionsHidePanel'] = 'Спрятать список последних тем на панель управления';
$Context->Dictionary['LatestDiscussionsSortCreated'] = 'Сортировать темы по дате создания (по умолчанию сортируется по дате последнего коментария)';
$Context->Dictionary['LatestDiscussionsOnlyShowMine'] = 'Показывать только темы, комментированные мной';

// Mark O'Sullivan's "RSS2 Feed 1.0.2" 
// Дополнение переведено командой Правильного русского сообщества www.vanillain.ru
$Context->Dictionary["RSS2Feed"] = "RSS2";
$Context->Dictionary["FailedFeedAuthenticationTitle"] = "Авторизация не прошла успешно";
$Context->Dictionary["FailedFeedAuthenticationText"] = "Для получения RSS этого форума, требуется авторизация.";

// SirNotAppearingOnThisForum's "Page Manager 2.5.1"
// Дополнение переведено командой Правильного русского сообщества www.vanillain.ru
$Context->Dictionary['RoleTabsNotes'] = 'Выберите уровень прав для просмотра вкладки.';
$Context->Dictionary['CreateANewPage'] = 'Создать новую страницу';
$Context->Dictionary['PageManagement'] = 'Менеджер страниц';
$Context->Dictionary['ResyncTabs'] = 'Сбросить синхронизацию вкладок';
$Context->Dictionary['ResyncTabsNotes'] = 'Все системные вкладки будут полностью сброшены на стандартные, вы действительно хотите продолжить?';
$Context->Dictionary['ResyncTabsSaved'] = 'Все системные вкладки были восстановлены.';
$Context->Dictionary['DefineNewPage'] = 'Указать название новой страницы';
$Context->Dictionary['ModifyThePage'] = 'Редактировать страницу/вкладку';
$Context->Dictionary['SelectPage'] = 'Выбрать страницу/вкладку для редактирования';
$Context->Dictionary['TabName'] = 'Имя вкладки';
$Context->Dictionary['TabNameNotes'] = 'Имя &mdash; это текст, указывающий название вкладки, местоположение &mdash; вверху.';
$Context->Dictionary['TabIdentifier'] = 'Идентификатор вкладки';
$Context->Dictionary['TabIdentifierNotes'] = 'Настоятельно рекомендуется определять область идентификации здесь, или оставить первоначальное значение, для совместимости с другими дополнениями.';
$Context->Dictionary['TabAttributes'] = 'Атрибуты вкладки';
$Context->Dictionary['TabAttributesNotes'] = 'Дополнительные HTML атрибуты подключают к вкладке такие возможности, как ключ быстрого доступа, если такая настройка включена (eg. accesskey="m"), или название заголовка.';
$Context->Dictionary['TabURL'] = 'Url вкладки';
$Context->Dictionary['TabURLNotes'] = 'Вкладка будет указывать на URL или HTML-страницу. Если ссылка указана выше, то откроется находящийся по ней ресурс, если не указана, будет открыта страница, указанная ниже. (URL должен быть указан полностью)';
$Context->Dictionary['PageHTML'] = 'HTML-страницы';
$Context->Dictionary['PageHTMLNotes'] = 'PHP-код может быть так же включен в HTML-страницы.';
$Context->Dictionary['PageRoleNotes'] = 'Уровень прав пользователей, которым разрешено видеть вкладки и, если возможно, получать доступ к соответствующим страницам. Если у пользователя нет прав на это по умолчанию, то эти настройки ничего не изменят (например доступ к вкладке Настройки, разрешенный здесь и запрещенный по умолчанию).';
$Context->Dictionary['PageReorderNotes'] = 'Вы можете изменять порядок расположения вкладок перетаскиванием. Изменения производятся автоматически, но для их просмотра, следует обновить страницу. По умолчанию изменения не касаются расположения главной страницы.';
$Context->Dictionary['RoleTabs'] = 'Видимые вкладки/страницы';
$Context->Dictionary['RoleTabsNotes'] = 'Выберите вкладки/страницы, для которых есть права просмотра/изменения';
$Context->Dictionary['TabHidden'] = 'Скрывать/показывать вкладку';
$Context->Dictionary['TabHiddenNotes'] = 'Скрывать/показывать вкладку на навигационной панели';
$Context->Dictionary['HiddenQ'] = 'Вкладка скрыта для навигации';

// Jim Wurster's "zip2mail 1.0.6"
// Дополнение переведено командой Правильного русского сообщества www.vanillain.ru
$Context->Dictionary['Backup'] = 'Резервная копия';
$Context->Dictionary['BackupNotes'] = 'Этот раздел позволяет сделать резервную копию данных форума. Заархивированная копия будет выслана на ваш почтовый ящик (указанный в настройках форума).';
$Context->Dictionary['BuildBackup'] = 'Сделать резервную копию';
$Context->Dictionary['BackupBuilt'] = 'Сделана резервная копия для ';
$Context->Dictionary['FilesFound'] = 'Файлы найдены.';
$Context->Dictionary['TypeBackupNotes'] = 'Вид резервной копии, которую вы хотите сделать.';
$Context->Dictionary['Type'] = 'Тип резервной копии';
$Context->Dictionary['Backup_Basename'] = 'резервная_копия';
$Context->Dictionary["Backup_GlobalOptions_0"] = " База";
$Context->Dictionary["Backup_GlobalOptions_1"] = " Расширения";
$Context->Dictionary["Backup_GlobalOptions_2"] = " Темы";
$Context->Dictionary["Backup_GlobalOptions_3"] = " Настройки";

// David Harris's "MassMailer 1.0"
// Дополнение переведено командой Правильного русского сообщества www.vanillain.ru
$Context->Dictionary['MassMailer'] = 'Массовая расылка почты';
$Context->Dictionary['MassMailerSubject'] = 'Тема:';
$Context->Dictionary['MassMailerMessage'] = 'Сообщение:';
$Context->Dictionary['MassMailerSent'] = 'Сообщение было отослано.';
$Context->Dictionary['MassMailerInformation'] = 'Вы можете использовать следующие метки: {Username}, {Firstname}, {Lastname}, {Email}, {Role}, и {Url}.';
$Context->Dictionary['MassMailerRecipients'] = 'Адресаты:';
$Context->Dictionary['MassMailerSelectAll'] = '(Выбрать все)';
$Context->Dictionary['MassMailerDeselectAll'] = '(Снять выделение со всех)';

// Maurice Krijtenberg's "Account Pictures 1.2"
// Дополнение переведено командой Правильного русского сообщества www.vanillain.ru
$Context->Dictionary['AccountPictures'] = 'Аватары';
$Context->Dictionary['AccountPicturesSettings'] = 'Аватары';
$Context->Dictionary['ChanngeAccountPictures'] = 'Изменить аватар';
$Context->Dictionary['AccountIcon'] = 'Иконка пользователя';
$Context->Dictionary['UploadAccountIconNotes'] = 'Ваша иконка появится рядом с вашим именем в собщениях, а так же на странице профиля. Выбранное изображение будет автоматически центрировано и обрезано //1 px в ширину и //2 px в высоту.';
$Context->Dictionary['AccountPicture'] = 'Аватар';
$Context->Dictionary['UploadAccountPicture'] = 'Загрузить аватар';
$Context->Dictionary['UploadAccountPictureNotes'] = 'Ваш аватар будет отображаться на странице профиля. Выбранное изображение будет автоматически центрировано и обрезано //1 px в ширину и //2 px в высоту';
$Context->Dictionary['AccountPicturesUploadSettings'] = 'Настройки загрузки';
$Context->Dictionary['AccountPicturesUploadSettingsNotes'] = 'Путь к папке загрузок должен быть относительным. Проверьте, чтобы для указаной папки были установлены права на запись';
$Context->Dictionary['AccountPicturesIconSize'] = 'Размер иконки';
$Context->Dictionary['AccountPicturesPictureSize'] = 'Размер аватара';
$Context->Dictionary['AccountPicturesExpertSettings'] = 'Подробные настройки';
$Context->Dictionary['AccountPicturesExpertSettingsNotes'] = 'Здесь находятся подробные настройки дополнения. Только для опытных пользователей';
$Context->Dictionary['ImageMagickPath'] = 'Путь к ImageMagick';
$Context->Dictionary['ImageMagickPathNotes'] = 'Полный путь к ImageMagick конвертируется в двух видах (например: Windows: C:/ImageMagick/convert.exe, Linux: /usr/local/bin/convert)';

// Maurice (Jazzman) Krijtenberg's "Inline Images 1.3"
// Дополнение переведено командой Правильного русского сообщества www.vanillain.ru
$Context->Dictionary['ExtensionOptions'] = 'Настройки дополнений';
$Context->Dictionary['InlineImages'] = 'Inline Images';
$Context->Dictionary['InlineImagesSettings'] = 'Настройки Inline Images';
$Context->Dictionary['InlineImagesNotes'] = 'Эти настройки применяются и к текущим изображениям. С использованием inline images можно показывать изображения из папки дополнений в своих сообщениях, используя [Image_%AttachmentID%]. При изменении максимальной высоты будут обновлены размеры уже опубликованных изображений.';
$Context->Dictionary['InlineImagesMaxWidth'] = 'Максимальная высота';
$Context->Dictionary['UseThickBox'] = 'Использовать ThickBox (требует наличия установленных дополнений JQuery и JQThickBox!)';

// SirNotAppearingOnThisForum's "Signatures 1.2"
// Дополнение переведено командой Правильного русского сообщества www.vanillain.ru
$Context->Dictionary['CUSTOMIZATION_SIGNATURE'] = 'Подписи';
$Context->Dictionary['CUSTOMIZATION_SIGNATURE_DESCRIPTION'] = 'Отображать маленькую однострочную подпись под каждым вашим сообщением. Используйте [url=http://...]название ссылки[/url] для вставки ссылки в подпись.';
$Context->Dictionary['ShowSigs'] = 'Показывать подпись в сообщениях';

// Steve Reed's "Next Unread Discussion 1.0"
// Дополнение переведено командой Правильного русского сообщества www.vanillain.ru
$Context->Dictionary['NextUnreadPreviousLinkLabel'] = '&lt; Предыдущая страница';
$Context->Dictionary['NextUnreadLinkLabel'] = 'Следующая непрочитанная &gt;';

// Maurice (Jazzman) Krijtenberg's "Thankful People 1.2"
// Дополнение переведено командой Правильного русского сообщества www.vanillain.ru
$Context->Dictionary['Thanks'] = 'спасибо';
$Context->Dictionary['ThankfulPeopleList'] = 'Благодарные пользователи: <span>//1</span>';
$Context->Dictionary['ThankyouHistory'] = 'История благодарностей';
$Context->Dictionary['ThankYous'] = 'Спасибо';
$Context->Dictionary['ErrCreateTable'] = 'Ошибка при создании таблицы в базе данных!';
$Context->Dictionary['ErrCreateConfig'] = 'Ошибка при сохранении настроек!';

// Mark O'Sullivan's "Saved Searches 2.0"
// Дополнение переведено командой Правильного русского сообщества www.vanillain.ru
$Context->Dictionary['Searches'] = 'Результаты поисков';
$Context->Dictionary['RemoveLower'] = 'удалить';
$Context->Dictionary['NoSavedSearches'] = 'У вас нет сохраненных результатов';
$Context->Dictionary['SaveSearch'] = 'Сохранить результат';
$Context->Dictionary['DisplaySavedSearches'] = 'Показывать сохраненные результаты поиска на панели';
$Context->Dictionary['MaxSavedSearchesInPanel'] = 'Максимальное количество сохранений на панели';

// Mark O'Sullivan's "Comment Protection 2.0"
// Дополнение переведено командой Правильного русского сообщества www.vanillain.ru
$Context->Dictionary['AllowHtml'] = 'Разрешить HTML в этом сообщении';
$Context->Dictionary['BlockHtml'] = 'Блокировать HTML в этом сообщении';
$Context->Dictionary['BlockComment'] = 'блокировать сообщение';
$Context->Dictionary['BlockCommentTitle'] = 'Блокировать HTML в этом сообщении';
$Context->Dictionary['UnblockComment'] = 'разблокировать сообщение';
$Context->Dictionary['UnblockCommentTitle'] = 'Разрешить HTML в этом сообщении';
$Context->Dictionary['BlockUserHtml'] = 'Блокировать HTML во всех сообщениях этого пользователя';
$Context->Dictionary['AllowUserHtml'] = 'Разрешить HTML во всех сообщениях этого пользователя';
$Context->Dictionary['BlockUser'] = 'блокировать пользователя';
$Context->Dictionary['BlockUserTitle'] = 'Блокировать HTML во всех сообщениях этого пользователя';
$Context->Dictionary['UnblockUser'] = 'разблокировать пользователя';
$Context->Dictionary['UnblockUserTitle'] = 'Разблокировать HTML во всех сообщениях этого пользователя';

// Christophe Gragnic, Dan Richman (CrudeRSS), Mark O'Sullivan (RSS2, ATOM) "FeedPublisher 0.3.1"
// Дополнение переведено командой Правильного русского сообщества www.vanillain.ru
$Context->Dictionary[ "Feeds" ]	= "RSS-лента";
$Context->Dictionary[ "FP_feedLinkToolTip_discussion" ]	= "Подписаться на RSS этой темы";
$Context->Dictionary[ "FP_feedLinkToolTip_search" ]= "Подписаться на RSS этой маски поиска";
$Context->Dictionary[ "RSS2Feed" ]= "RSS2";
$Context->Dictionary[ "ATOMFeed" ]= "ATOM";
$Context->Dictionary[ "FailedFeedAuthenticationTitle" ]	= "Ошибка аутентификации";
$Context->Dictionary[ "FailedFeedAuthenticationText" ]	= "RSS-лента этого форума требует аутентификации.";

// Alexander Morland's AKA Mr Do "Dojo Files 0.941" http://lussumo.com/addons/index.php?PostBackAction=AddOn&AddOnID=282
// Дополнение переведено командой Правильного русского сообщества www.vanillain.ru
$Context->Dictionary[ "FileTab" ]= "Файлы";
$Context->Dictionary[ "Dojo Files" ]= "Dojo файлы";
$Context->Dictionary[ "DojoFilesSettings" ]= "Настройки Dojo файлов";
$Context->Dictionary[ "Upload" ]= "Загрузить файл";
$Context->Dictionary[ "DojoFilesNotes" ]= "Эти  настройки действуют для дополнения Dojo Files. Не забудьте настроить полномочия. Также создайте или укажите путь к папке загружаемых файлов, с соответствующими правами доступа.";
$Context->Dictionary[ "FileHostCat" ]= "Выберите папку для загружаемых файлов";
$Context->Dictionary[ "FilesPerPage" ]= "Количество показываемых обьектов на странице. Укажите 0 или неограничено";
$Context->Dictionary[ "MaxDiscLength" ]= "Максимум знаков в колонке Ссылка на тему";
$Context->Dictionary[ "FileTabPosition" ]= "Положение вкладки Dojo файлов";
$Context->Dictionary[ "EmbedHeight" ]= "Укажите высоту для загружаемых (Of Embeded) Images/Movies/Flash/Shockwave";
$Context->Dictionary[ "EmbedWidth" ]= "Укажите ширину для загружаемых (Of Embeded) Images/Movies/Flash/Shockwave";
$Context->Dictionary[ "PERMISSION_DOJO_VIEW" ]= "Доступ к Dojo Files";
$Context->Dictionary[ "PERMISSION_DOJO_TITLE" ]= "Может изменять имя файлов";
$Context->Dictionary[ "PERMISSION_DOJO_HIDE" ]= "Скрывать файлы в списке (Dojo list)";
$Context->Dictionary[ "PERMISSION_DOJO_DELETE" ]= "Удалять файлы из списка (Dojo list)";
$Context->Dictionary[ "PERMISSION_DOJO_VIEW_HIDDEN" ]= "Просматривать скрытые Dojo файлы";
$Context->Dictionary[ "PERMISSION_DOJO_UNHIDE" ]= "Снимать параметр &laquo;скрытый&raquo; с файлов в списке (Dojo list)";
$Context->Dictionary[ "ALL" ]= "все";
$Context->Dictionary[ "IMAGES" ]= "Изображение";
$Context->Dictionary[ "SHOCKWAVE" ]= "SHOCKWAVE";
$Context->Dictionary[ "MOVIES" ]= "Видео";
$Context->Dictionary[ "FLASH" ]= "флеш";
$Context->Dictionary[ "HIDDEN" ]= "Скрытый";
$Context->Dictionary[ "title" ]= "заголовок";
$Context->Dictionary[ "look at" ]= "look at";
$Context->Dictionary[ "direct link" ]= "direct link";
$Context->Dictionary[ "hotlink url" ]= "hotlink url";
$Context->Dictionary[ "uploaded by" ]= "Кто загрузил:";
$Context->Dictionary[ "discussion" ]= "тема";
$Context->Dictionary[ "hide" ]= "Скрыть";
$Context->Dictionary[ "show" ]= "показать";
$Context->Dictionary[ "delete" ]= "удалить";

// Justin (Krak) Haury's "Hidden Text 2.1"
// Дополнение переведено командой Правильного русского сообщества www.vanillain.ru
$Context->Dictionary['HiddenTextSettings'] = 'Скрытый текст';
$Context->Dictionary['HiddenTextMarkerShow'] = 'Изменить маркер показа скрытого текста';
$Context->Dictionary['HiddenTextMarkerHide'] = 'Изменить маркер скрытия скрытого текста';
$Context->Dictionary['HiddenTextSettingsTitle'] = 'Настройки дополнения Hidden Text';
$Context->Dictionary['HiddenTextSettingsInfoShow'] = 'Введите маркер, обозначающий показ скрытого текста. По умолчанию это (show text)';
$Context->Dictionary['HiddenTextSettingsInfoHide'] = 'Введите маркер, обозначающий скрытие скрытого текста. По умолчанию это (hide text)';
$Context->Dictionary['SettingsInputTextShow'] = 'Показать маркер скрытого текста';
$Context->Dictionary['SettingsInputTextHide'] = 'Скрыть маркер скрытого текста';
$Context->Dictionary['ErrCreateConfig'] = 'Ошибка сохрениния настроек в файл конфигурации!';
$Context->Dictionary['PERMISSION_HIDDEN_TEXT'] = 'Может видеть скрытый текст';



// Define the xml:lang attribute for the html tag
$Context->Dictionary['XMLLang'] = 'en-ca';

// Define all dictionary codes in English
// Движок форума переведен командой Правильного русского сообщества www.vanillain.ru
$Context->Dictionary['NoDiscussionsNotSignedIn'] = 'Вам необходимо зарегистрироваться, чтобы участвовать в этой теме.';
$Context->Dictionary['SelectDiscussionCategory'] = 'Выберите раздел для новой темы';
$Context->Dictionary['WhisperYourCommentsTo'] = 'Прошептать пользователю <small>(не обязательно)</small>';
$Context->Dictionary['And'] = 'и';
$Context->Dictionary['Or'] = 'или';
$Context->Dictionary['ClickHereToContinueToDiscussions'] = 'Перейти к темам';
$Context->Dictionary['ClickHereToContinueToCategories'] = 'Перейти к разделам';
$Context->Dictionary['ReviewNewApplicants'] = 'Новые заявки участников';
$Context->Dictionary['New'] = 'новых';
$Context->Dictionary['NewCaps'] = 'Новых';
$Context->Dictionary['Username'] = 'Логин';
$Context->Dictionary['Password'] = 'Пароль';
$Context->Dictionary['RememberMe'] = 'Запомнить меня';
$Context->Dictionary['ForgotYourPassword'] = 'Забыли пароль?';
$Context->Dictionary['Proceed'] = 'Продолжить';
$Context->Dictionary['ErrorTitle'] = 'Возникли проблемы';
$Context->Dictionary['RealName'] = 'Имя';
$Context->Dictionary['Email'] = 'Почта (e-mail)';
$Context->Dictionary['Style'] = 'Стиль';
$Context->Dictionary['ThemeAndStyleNotes'] = 'Описание тем и стилей';
$Context->Dictionary['AccountCreated'] = 'Учетная запись создана';
$Context->Dictionary['LastActive'] = 'Последнее посещение';
$Context->Dictionary['VisitCount'] = 'Всего посещений';
$Context->Dictionary['DiscussionsStarted'] = 'Начатых тем';
$Context->Dictionary['CommentsAdded'] = 'Сообщений';
$Context->Dictionary['LastKnownIp'] = 'Последний IP-адрес';
$Context->Dictionary['PermissionError'] = 'Вам не разрешено выполнять это действие.';
$Context->Dictionary['ChangePersonalInfo'] = 'Личные данные';
$Context->Dictionary['DefineYourAccountProfile'] = '1. Личные данные учетной записи';
$Context->Dictionary['YourUsername'] = 'Ваш логин';
$Context->Dictionary['YourUsernameNotes'] = 'Ваш логин будет показываться рядом с темами и сообщениями.';
$Context->Dictionary['YourFirstNameNotes'] = 'Это должно быть ваше <b>настоящее</b> имя. Оно будет показываться только в учетной записи.';
$Context->Dictionary['YourLastNameNotes'] = 'Это должна быть ваша <b>настоящая</b> фамилия. Она будет показываться только в учетной записи.';
$Context->Dictionary['YourEmailAddressNotes'] = 'Вы должны указать настоящий адрес электронной почты на случай утери пароля.';
$Context->Dictionary['CheckForVisibleEmail'] = 'Отметьте, чтобы ваш адрес электронной почты был виден участникам форума';
$Context->Dictionary['AccountPictureNotes'] = 'Вы можете ввести любой корректный адрес файла с изображением, например <tt>http://www.mywebsite.com/myaccountpicture.jpg</tt>
	<br />Аватар будет показываться только на вашей странице профиля. Изображение будет автоматически уменьшено до 280 пикселей в ширину и 200 пикселей в высоту.';
$Context->Dictionary['IconNotes'] = 'Вы можете ввести любой корректный адрес файла иконки, например http://www.mywebsite.com/myicon.jpg 
	<br />Иконка будет находиться рядом с вашим логином в сообщениях и на странице профиля. Иконка будет автоматически уменьшена до 32 пикселей в ширину и 32 пикселей в высоту.';
$Context->Dictionary['AddCustomInformation'] = '2. Дополнительная информация';
$Context->Dictionary['AddCustomInformationNotes'] = 'Используя следующие поля, вы можете 
добавлять дополнительную информацию к вашему профилю как комбинацию метки и значения этой метки (например, "День рождения" и "16 сентября", или "Любимая группа" и "The Beatles"). Значения, имеющие префикс протокола типа http://, mailto:, ftp://, aim:, и т.д. будут автоматически превращены в ссылку. Вы можете добавлять сколько угодно меток и значений.';
$Context->Dictionary['Label'] = 'Метка';
$Context->Dictionary['Value'] = 'Значение';
$Context->Dictionary['AddLabelValuePair'] = 'Добавить еще одну метку и значение';
$Context->Dictionary['Save'] = 'Сохранить';
$Context->Dictionary['Cancel'] = 'Отменить и вернуться назад';
$Context->Dictionary['YourOldPasswordNotes'] = 'Введите ваш текущий пароль.';
$Context->Dictionary['YourNewPasswordNotes'] = 'Не используйте даты рождения, номера банковских карт, телефонные номера или что-то легко угадываемое. <strong>И, ПОЖАЛУЙСТА, не используйте пароль, идентичный тому, который вы используете на других сайтах.</strong>';
$Context->Dictionary['Required'] = '(обязательно)';
$Context->Dictionary['YourNewPasswordAgain'] = 'Повтор нового пароля';
$Context->Dictionary['YourNewPasswordAgainNotes'] = 'Еще раз введите ваш новый пароль для надежности.';
$Context->Dictionary['ForumFunctionality'] = 'Опции форума';
$Context->Dictionary['ForumFunctionalityNotes'] = 'Все изменения происходят сразу. Вам не нужно нажимать кнопку подтверждения. Этой кнопки вообще нет.';
$Context->Dictionary['ControlPanel'] = 'Панель управления';
$Context->Dictionary['CommentsForm'] = 'Форма сообщений';
$Context->Dictionary['ShowFormatTypeSelector'] = 'Позволять выбор формата сообщений';
$Context->Dictionary['NewUsers'] = 'Новые участники';
$Context->Dictionary['NewApplicantNotifications'] = 'Сообщать по электронной почте о поступлении заявки на участие в форуме';
$Context->Dictionary['AssignToRole'] = 'Выберите новый статус';
$Context->Dictionary['AssignToRoleNotes'] = 'Все изменения статуса сразу вступают в силу. Если пользователю присваивается статус, не имеющий прав входа на форум, то этот участник автоматически будет выброшен из форума при перезагрузке страницы.';
$Context->Dictionary['RoleChangeInfo'] = 'Причина изменения';
$Context->Dictionary['RoleChangeInfoNotes'] = 'Пожалуйста, предоставляйте некие заметки по поводу изменения статуса. Эти заметки будут видны всем участникам в их истории статуса.';
$Context->Dictionary['AboutMembership'] = '<h2>Об участии в форуме</h2>
	<p><strong>Заявка</strong> на участие сразу не дает прямого доступа к форуму. Все заявки рассматриваются администратором перед одобрением. Вам <strong>не</strong> гарантируется доступ к форуму через заявку.</p>
	<p>Пожалуйста, вводите только достоверную информацию в этой форме, иначе Вам не будет предоставлен доступ.</p>
	<p>Вся информация строго конфиденциальна.</p>';
$Context->Dictionary['BackToSignInForm'] = 'Вернуться назад к форме входа';
$Context->Dictionary['MembershipApplicationForm'] = 'Заполнение формы для участия';
$Context->Dictionary['AllFieldsRequired'] = 'Все поля обязательны';
$Context->Dictionary['IHaveReadAndAgreeTo'] = 'Я буду этичным. Буду следовать правилам форума.'; // "I will be ethical" instead of original message, because Terms of Use are not yet ready in version 0.2
$Context->Dictionary['TermsOfService'] = 'Правила пользования';
$Context->Dictionary['CommentHiddenOnXByY'] = 'Это сообщение было скрыто //1 //2';
$Context->Dictionary['ToYou'] = ' вам';
$Context->Dictionary['ToYourself'] = ' самому (самой) себе';
$Context->Dictionary['ToX'] = ' для //1';
$Context->Dictionary['Edited'] = 'это сообщение исправляли';
$Context->Dictionary['edit'] = 'редактировать';
$Context->Dictionary['Edit'] = 'редактировать';
$Context->Dictionary['Show'] = 'показать';
$Context->Dictionary['Hide'] = 'спрятать';
$Context->Dictionary['WhisperBack'] = 'Шепнуть в ответ';
$Context->Dictionary['AddYourComments'] = 'Добавить сообщение';
$Context->Dictionary['TopOfPage'] = 'наверх';
$Context->Dictionary['BackToDiscussions'] = 'Назад к темам';
$Context->Dictionary['SignOutSuccessful'] = 'Вы вышли из системы';
$Context->Dictionary['SignInAgain'] = 'Войти';
$Context->Dictionary['RequestProcessed'] = 'Ваш запрос был обработан';
$Context->Dictionary['MessageSentToXContainingPasswordInstructions'] = 'Сообщение было послано <strong>//1</strong> и содержит инструкции по сбросу пароля.';
$Context->Dictionary['AboutYourPassword'] = 'О вашем пароле';
$Context->Dictionary['AboutYourPasswordRequestNotes'] = '<strong>Эта форма не изменит ваш пароль.</strong> Заполнив эту форму, вы получите электронное письмо с необходимыми инструкциями.';
$Context->Dictionary['PasswordResetRequestForm'] = 'Форма запроса на сброс пароля';
$Context->Dictionary['PasswordResetRequestFormNotes'] = 'Введите ваш логин, чтобы отправить запрос на сброс пароля.';
$Context->Dictionary['SendRequest'] = 'Сбросить пароль';
$Context->Dictionary['PasswordReset'] = 'Сброс вашего пароля прошел удачно';
$Context->Dictionary['SignInNow'] = 'Нажмите здесь, чтобы войти';
$Context->Dictionary['AboutYourPasswordNotes'] = 'Не используйте даты рождения, номера банковских карт, телефонные номера или что-то легко угадываемое. <strong>И ПОЖАЛУЙСТА, не используйте пароль, идентичный тому, который вы используете на других сайтах.</strong>';
$Context->Dictionary['PasswordResetForm'] = 'Форма для сброса пароля';
$Context->Dictionary['ChooseANewPassword'] = 'Выберите и введите пароль.';
$Context->Dictionary['NewPassword'] = 'Новый пароль';
$Context->Dictionary['ConfirmPassword'] = 'Подтвердить пароль';
$Context->Dictionary['AllCategories'] = 'Все разделы';
$Context->Dictionary['DateLastActive'] = 'Последняя дата активности';
$Context->Dictionary['Topics'] = 'Темы';
$Context->Dictionary['Comments'] = 'Сообщения';
$Context->Dictionary['Users'] = 'Участники';
$Context->Dictionary['AllRoles'] = 'Все статусы';
$Context->Dictionary['Advanced'] = 'Расширенный поиск';
$Context->Dictionary['ChooseSearchType'] = 'Поиск:';
$Context->Dictionary['DiscussionTopicSearch'] = 'Поиск по названиям тем';
$Context->Dictionary['FindDiscussionsContaining'] = 'Найти темы, в названиях которых есть';
$Context->Dictionary['InTheCategory'] = 'в разделе';
$Context->Dictionary['WhereTheAuthorWas'] = 'автор темы';
$Context->Dictionary['Search'] = 'Искать';
$Context->Dictionary['DiscussionCommentSearch'] = 'Поиск по сообщениям';
$Context->Dictionary['FindCommentsContaining'] = 'Найти сообщения, содержащие';
$Context->Dictionary['UserAccountSearch'] = 'Поиск по профилям участников';
$Context->Dictionary['FindUserAccountsContaining'] = 'Найти профиль участника, содержащий';
$Context->Dictionary['InTheRole'] = 'в статусе';
$Context->Dictionary['SortResultsBy'] = 'сортировать результаты по';
$Context->Dictionary['NoResultsFound'] = 'Никаких результатов не найдено';
$Context->Dictionary['DiscussionsCreated'] = 'Создано тем';
$Context->Dictionary['AdministrativeOptions'] = 'Опции администрирования';
$Context->Dictionary['ApplicationSettings'] = 'Настройки форума';
$Context->Dictionary['ManageExtensions'] = 'Дополнения';
$Context->Dictionary['RoleManagement'] = 'Полномочия';
$Context->Dictionary['CategoryManagement'] = 'Разделы';
$Context->Dictionary['MembershipApplicants'] = 'Заявки на участие';
$Context->Dictionary['GlobalApplicationSettings'] = 'Глобальные настройки форума';
$Context->Dictionary['GlobalApplicationSettingsNotes'] = 'БУДЬТЕ ОСТОРОЖНЫ со всеми изменениями на этой странице. Информация, введенная по ошибке, может вывести форум из строя и возможно придется вручную корректировать настройки.';
$Context->Dictionary['AboutSettings'] = 'О настройках';
$Context->Dictionary['AboutSettingsNotes'] = "<p class=\"Description\">Используя этот раздел, вы можете изменять настройки вашего форума Vanilla. Ниже вы можете посмотреть краткое описание разделов меню. В зависимости от вашего статуса, вы можете видеть не все ниже описанные разделы меню:</p>
	<dl><dt>Настройки форума</dt>
	<dd>Это главная страница конфигурации Vanilla. Здесь вы можете изменить заголовок баннера, настроить защиту от спама, установить настройки cookies и изменить главные опции форума.</dd>
	<dt>Напоминания об обновлениях</dt>
	<dd>Настройте частоту обновления Vanilla.</dd>
	<dt>Права и полномочия</dt>
	<dd>Управление правами и полномочиями участников.</dd>
	<dt>Регистрация</dt>
	<dd>Определите статус для новых участников. Указывайте, какой уровень прав им предоставить, требуется ли одобрение регистрации администратором и т.д.</dd>
	<dt>Разделы</dt>
	<dd>Добавление, изменение и сортировка разделов.</dd>
	<dt>Дополнения</dt>
	<dd>Дополнения - это изюминка Vanilla. С их помощью можно добавлять новые возможности в Vanilla. Используйте это меню для активации дополнений и для поиска новых дополнений на сайте Lussumo.</dd>
	<dt>Темы (стили) Vanilla</dt>
	<dd>Изменение тем (xhtml-шаблонов) Vanilla, изменение стиля по умолчанию (css, изображений), и применение ко всем пользователям системы.</dd>
	<dt>Языки</dt>
	<dd>Используйте это меню для изменения языковых настроек вашего форума.</dd>
	<dt>Заявки на участие</dt>
	<dd>Vanilla не имеет списка участников, как многие другие форумы. Вместо этого мы используем функцию поиска, для отображения участников. В этом разделе отображаются еще не одобренные вами участники форума.</dd>
	<dt>Другие настройки</dt>
	<dd> В зависимости от вашего статуса, в меню могут быть другие настройки. Добро пожаловать в магический мир дополнений для Vanilla!</dd>
</dl>";
$Context->Dictionary['HiddenInformation'] = 'Спрятанная информация';
$Context->Dictionary['DisplayHiddenDiscussions'] = 'Показывать спрятанные темы';
$Context->Dictionary['DisplayHiddenComments'] = 'Показывать спрятанные сообщения';
$Context->Dictionary['Choose'] = 'Выбрать...';
$Context->Dictionary['GetCategoryToEdit'] = '1. Выберите раздел для редактирования';
$Context->Dictionary['Categories'] = 'Разделы';
$Context->Dictionary['ModifyCategoryDefinition'] = '2. Редактировать формулировку раздела';
$Context->Dictionary['DefineNewCategory'] = 'Создать новый раздел';
$Context->Dictionary['CategoryName'] = 'Название темы';
$Context->Dictionary['CategoryNameNotes'] = 'Название тем будет видно на странице со списком тем и на странице самой темы. HTML запрещен.';
$Context->Dictionary['CategoryDescription'] = 'Описание раздела';
$Context->Dictionary['CategoryDescriptionNotes'] = 'Введенное здесь значение будет видно на странице разделов. HTML запрещен.';
$Context->Dictionary['RolesInCategory'] = 'Статусы, разрешенные для участия в этом разделе';
$Context->Dictionary['SelectCategoryToRemove'] = '1. Выберите раздел для удаления';
$Context->Dictionary['SelectReplacementCategory'] = '2. Выбрать раздел для замены';
$Context->Dictionary['ReplacementCategory'] = 'Раздел для замены';
$Context->Dictionary['ReplacementCategoryNotes'] = 'Когда вы убираете раздел из системы, все темы, находящиеся в этом раделе, теряют свое значение. Раздел для замены присваивает себе все темы из удаляемого раздела.';
$Context->Dictionary['Remove'] = 'Убрать';
$Context->Dictionary['CreateNewCategory'] = 'Создать новый раздел';
$Context->Dictionary['CategoryRemoved'] = 'Раздел удален.';
$Context->Dictionary['CategorySaved'] = 'Изменения сохранены.';
$Context->Dictionary['NewCategorySaved'] = 'Раздел сохранен.';
$Context->Dictionary['SelectRoleToEdit'] = '1. Выберите статус для редактирования';
$Context->Dictionary['Roles'] = 'Статусы';
$Context->Dictionary['ModifyRoleDefinition'] = '2. Редактировать формулировку статуса';
$Context->Dictionary['DefineNewRole'] = 'Создать новый статус';
$Context->Dictionary['RoleName'] = 'Название статуса';
$Context->Dictionary['RoleNameNotes'] = "Статус будет показываться на странице профиля участника вслед за его/ее именем. HTML запрещен.";
$Context->Dictionary['RoleIconNotes'] = "Вы можете ввести любой корректный адрес (URL) для картинки, например: <strong>http://www.mywebsite.com/myicon.jpg</strong>
	<br />Иконка статуса будет заменять иконку пользователя на всех страницах с сообщениями и на странице профиля. Если вы не желаете ставить здесь какое-либо значение, то иконка, обозначенная участником форума, останется (если таковая имеется).";
$Context->Dictionary['RoleTagline'] = 'Подзаголовок статуса';
$Context->Dictionary['RoleTaglineNotes'] = "Подзаголовок статуса будет показываться на странице профиля под именем участника форума. Если нет значения в этом поле, то подзаголовок не будет показываться.";
$Context->Dictionary['RoleAbilities'] = 'Возможности статуса';
$Context->Dictionary['RoleAbilitiesNotes'] = 'Выберите все возможности, которые будут иметь участники с этом статусом.';
$Context->Dictionary['RoleRemoved'] = 'Статус удален.';
$Context->Dictionary['RoleSaved'] = 'Изменения сохранены.';
$Context->Dictionary['NewRoleSaved'] = 'Статус создан.';
$Context->Dictionary['StartANewDiscussion'] = 'Начать новую тему';
$Context->Dictionary['SelectRoleToRemove'] = '1. Выберите статус для удаления';
$Context->Dictionary['SelectReplacementRole'] = '2. Выберите статус для замены';
$Context->Dictionary['ReplacementRole'] = 'Статус для замены';
$Context->Dictionary['ReplacementRoleNotes'] = 'Когда вы убираете статус из системы, то все участники теряют этот статус. Заменяемый статус будет присвоен участникам форума, статус которых удаляется.';
$Context->Dictionary['CreateANewRole'] = 'Создать новый статус';
$Context->Dictionary['Extensions'] = 'Дополнения';
$Context->Dictionary['YouAreSignedIn'] = 'Вы вошли';
$Context->Dictionary['BottomOfPage'] = 'вниз';
$Context->Dictionary['NotSignedIn'] = 'Вы не вошли';
$Context->Dictionary['SignIn'] = 'Войти';
$Context->Dictionary['Discussions'] = 'Темы';
$Context->Dictionary['Settings'] = 'Настройки';
$Context->Dictionary['Account'] = 'Профиль';
$Context->Dictionary['AllDiscussions'] = 'Все темы';
$Context->Dictionary['Category'] = 'Раздел:';
$Context->Dictionary['StartedBy'] = 'Начал';
$Context->Dictionary['LastCommentBy'] = 'Последним ответил';
$Context->Dictionary['PageDetailsMessage'] = 'с //1 по //2';
$Context->Dictionary['PageDetailsMessageFull'] = 'с //1 по //2 из //3';
$Context->Dictionary['SearchResultsMessage'] = 'Результаты с //1 по //2 для запроса //3';
$Context->Dictionary['NoSearchResultsMessage'] = 'Ничего не найдено';
$Context->Dictionary['Previous'] = 'назад';
$Context->Dictionary['Next'] = 'вперед';
$Context->Dictionary['WrittenBy'] = 'Написано';
$Context->Dictionary['Added'] = 'Добавлено';
$Context->Dictionary['MyAccount'] = 'Мой профиль';
$Context->Dictionary['ApplyForMembership'] = 'Зарегистрироваться';
$Context->Dictionary['SignOut'] = 'Выйти';
$Context->Dictionary['ResetYourPassword'] = 'Сбросить пароль';
$Context->Dictionary['AdministrativeSettings'] = 'Настройки администрирования';
$Context->Dictionary['TermsOfServiceBody'] = "<h1>Правила пользования</h1>
<h2>Пожалуйста, ознакомьтесь с правилами и принципами форума.</h2>

<p>В силу того, что все происходит в режиме реального времени, невозможно просмотреть все сообщения или подтвердить достоверность информации.
Мы не ведем тщательного мониторинга и не берем на себя ответственность за предоставляемую информацию.
Мы не ручаемся за основания и точность, полноту и пользу любого сообщения, и не берем на себя ответственность за контекст и данные, оставленные участниками форума.
Сообщения выражают точку зрения автора сообщения, и не обязательно точку зрения сообщества или отдельного лица, ассоциированного с этим сообществом.
Любой участник форума, который чувствует, что сообщение несет оскорбительный характер, может сразу же связаться с нами по электронной почте.
У нас есть право и возможность убирать оскорбительные сообщения, и мы постараемся сделать все для этого, если сообщение действительно несет в себе неприятный характер и нуждается в удалении.
Это процесс ручной, поэтому вам нужно понять, что мы можем не удалить определенное сообщение сразу же.</p>

<p>При использовании данной услуги, вы соглашаетесь с тем, чтобы не использовать материал, зная, что этот материал ошибочный и/или дискредитирующий, неточный, оскорбительный, вульгарный, ненавистный, раздражающий, непристойный, оскорбительный, сексуально ориентированный, угрожающий, докучающий личности человека, или в том или оном смысле нарушающий закон.
Вы соглашаетесь не использовать материал, защищенный правами собственника, за исключением того, что этот материал принадлежит вам. </p>

<p>Несмотря на то, что это сообщество не может проверять оставленные сообщения и не является ответственным за любое содержание этих сообщений, мы в этом сообществе оставляем за собой право удалить любое сообщение по любой причине или без причины.
Исключительно вы являетесь ответственным за содержание ваших сообщений, и вы соглашаетесь защищать и не причинять ущерба этому сообществу, относиться к Lussumo (разработчики этого продукта для общения), и их агентам с уважением к любому заявлению, построенному на передаче вашего сообщения(й).</p>

<p>В этом сообществе мы оставляем за собой право раскрывать вашу личность (или любую известную о вас информацию) в случае жалобы или судебного иска, возникшего из-за оставленного вами сообщения.</p>

<p>Пожалуйста, имейте в виду, что реклама, письма счастья, пирамидные схемы, настойчивые просьбы и ходатайства неуместны в этом сообществе.</p>

<p><strong>Мы оставляем за собой право прервать ваше участие в форуме по любой причине или без причины.</strong></p>";
$Context->Dictionary['EmailAddress'] = 'Почта (e-mail)';
$Context->Dictionary['PasswordAgain'] = 'Пароль';
$Context->Dictionary['SignedInAsX'] = 'Привет, //1';
$Context->Dictionary['AccountOptions'] = 'Настройки профиля';
$Context->Dictionary['ChangeYourPersonalInformation'] = 'Изменить личную информацию';
$Context->Dictionary['ChangeYourPassword'] = 'Изменить пароль';
$Context->Dictionary['ChangeForumFunctionality'] = 'Поведение форума';
$Context->Dictionary['YourFirstName'] = 'Имя';
$Context->Dictionary['YourLastName'] = 'Фамилия';
$Context->Dictionary['YourEmailAddress'] = 'Почта (e-mail)';
$Context->Dictionary['AccountPicture'] = 'Аватар профиля';
$Context->Dictionary['Icon'] = 'Иконка';
$Context->Dictionary['MakeRealNameVisible'] = 'Отметьте здесь, чтобы ваше имя было видно участникам форума';
$Context->Dictionary['YourOldPassword'] = 'Старый пароль';
$Context->Dictionary['YourNewPassword'] = 'Новый пароль';
$Context->Dictionary['DiscussionTopic'] = 'Заголовок тем';
$Context->Dictionary['EmailLower'] = 'e-mail';
$Context->Dictionary['UsernameLower'] = 'логин';
$Context->Dictionary['PasswordLower'] = 'пароль';
$Context->Dictionary['NewPasswordLower'] = 'новый пароль';
$Context->Dictionary['RoleNameLower'] = 'название статуса';
$Context->Dictionary['DiscussionTopicLower'] = 'заголовок тем';
$Context->Dictionary['CommentsLower'] = 'сообщения';
$Context->Dictionary['CategoryNameLower'] = 'название раздела';
$Context->Dictionary['Options'] = 'Настройки';
$Context->Dictionary['BlockCategory'] = 'Блокировать раздел';
$Context->Dictionary['UnblockCategory'] = 'Разблокировать раздел';
$Context->Dictionary['BookmarkThisDiscussion'] = 'Добавить тему в закладки';
$Context->Dictionary['UnbookmarkThisDiscussion'] = 'Убрать тему из закладок';
$Context->Dictionary['HideConfirm'] = 'Вы уверены, что хотите спрятать это сообщение?';
$Context->Dictionary['ShowConfirm'] = 'Вы уверены, что хотите показывать это сообщение?';
$Context->Dictionary['BookmarkText'] = 'Добавить этот текст в закладки';
$Context->Dictionary['ConfirmHideDiscussion'] = 'Вы уверены, что хотите спрятать эту тему?';
$Context->Dictionary['ConfirmUnhideDiscussion'] = 'Вы уверены, что хотите показывать эту тему?';
$Context->Dictionary['ConfirmCloseDiscussion'] = 'Вы уверены, что хотите закрыть эту тему?';
$Context->Dictionary['ConfirmReopenDiscussion'] = 'Вы уверены, что хотите открыть эту тему?';
$Context->Dictionary['ConfirmSticky'] = 'Вы уверены, что хотите закрепить эту тему?';
$Context->Dictionary['ConfirmUnsticky'] = 'Вы уверены, что хотите открепить эту тему?';
$Context->Dictionary['ChangePersonalInformation'] = 'Изменить персональную информацию';
$Context->Dictionary['ApplicantOptions'] = 'Опции заявок на участие';
$Context->Dictionary['ChangeRole'] = 'Изменить статус';
$Context->Dictionary['NewApplicantSearch'] = 'Новый поиск заявок на участие';
$Context->Dictionary['BigInput'] = 'увеличить поле ввода';
$Context->Dictionary['SmallInput'] = 'уменьшить поле ввода';
$Context->Dictionary['EditYourDiscussionTopic'] = 'Редактировать заголовок темы';
$Context->Dictionary['EditYourComments'] = 'Редактировать свои сообщения';
$Context->Dictionary['FormatCommentsAs'] = 'Форматировать сообщение как ';
$Context->Dictionary['SaveYourChanges'] = 'Сохранить изменения';
$Context->Dictionary['Text'] = 'Текст';
$Context->Dictionary['EnterYourDiscussionTopic'] = 'Введите заголовок темы';
$Context->Dictionary['EnterYourComments'] = 'Введите сообщение';
$Context->Dictionary['StartYourDiscussion'] = 'Начать тему';
$Context->Dictionary['ShowAll'] = 'Показать все';
$Context->Dictionary['DiscussionIndex'] = 'Настройка отображения тем';
$Context->Dictionary['JumpToLastReadComment'] = 'При просмотре темы переходить к последнему прочитанному сообщению';
$Context->Dictionary['NoDiscussionsFound'] = 'Темы не найдены';
$Context->Dictionary['RegistrationManagement'] = 'Регистрация';
$Context->Dictionary['NewMemberRole'] = 'Статус нового участника';
$Context->Dictionary['NewMemberRoleNotes'] = 'Когда новые пользователи подают заявку на участие, то им присваивается этот статус. Если данный статус имеет разрешение на вход, то пользователям, подавшим заявку, сразу открывается возможность войти.';
$Context->Dictionary['RegistrationChangesSaved'] = 'Все изменения по регистрации сохранены.';
$Context->Dictionary['ClickHereToContinue'] = 'Нажмите здесь чтобы продолжить';
$Context->Dictionary['RegistrationAccepted'] = 'Регистрация принята.';
$Context->Dictionary['RegistrationPendingApproval'] = 'Регистрация ждет административного одобрения.';
$Context->Dictionary['Applicant'] = 'Ожидающий подтверждения';
$Context->Dictionary['ThankYouForInterest'] = 'Спасибо за ваш интерес!';
$Context->Dictionary['ApplicationWillBeReviewed'] = 'Ваша заявка на участие будет рассмотрена администратором. После одобрения вы получите письмо по электронной почте.';
$Context->Dictionary['ApplicationComplete'] = 'Оформление заявки окончено!';
$Context->Dictionary['AccountChangeNotification'] = 'Изменение оповещения профиля';
$Context->Dictionary['PasswordResetRequest'] = 'Заявка на сброс пароля';
$Context->Dictionary['LanguageManagement'] = 'Языки';
$Context->Dictionary['LanguageChangesSaved'] = 'Язык установлен.';
$Context->Dictionary['ChangeLanguage'] = 'Выберите язык';
$Context->Dictionary['ChangeLanguageNotes'] = 'Если вашего языка здесь нет, вы можете <a href="http://getvanilla.com/languages" target="_blank">скачать языковые пакеты на сайте Lussumo</a>.';
$Context->Dictionary['CloseThisDiscussion'] = 'Закрыть тему';
$Context->Dictionary['ReOpenThisDiscussion'] = 'Открыть тему';
$Context->Dictionary['MakeThisDiscussionUnsticky'] = 'Открепить тему';
$Context->Dictionary['MakeThisDiscussionSticky'] = 'Прикрепить тему';
$Context->Dictionary['HideThisDiscussion'] = 'Спрятать тему';
$Context->Dictionary['UnhideThisDiscussion'] = 'Показывать тему';

// Warnings
$Context->Dictionary['ErrOpenDirectoryExtensions'] = 'Не могу открыть директорию с дополнениями. Убедитесь, что у PHP есть доступ к директории //1.';
$Context->Dictionary['ErrOpenDirectoryThemes'] = 'Не могу открыть директорию с темами. Убедитесь, что у PHP есть доступ к директории //1.';
$Context->Dictionary['ErrOpenDirectoryStyles'] = 'Не могу открыть директорию с стилями. Убедитесь, что у PHP есть доступ к директории //1.';
$Context->Dictionary['ErrReadExtensionDefinition'] = 'Произошла ошибка при чтении формы формулировок дополнений';
$Context->Dictionary['ErrReadFileExtensions'] = 'Произошла ошибка при чтении файла дополнений';
$Context->Dictionary['ErrOpenFile'] = 'The file could not be opened. Please make sure that PHP has write access to the //1 file.';
$Context->Dictionary['ErrWriteFile'] = 'Не могу записать файл.';
$Context->Dictionary['ErrEmailSubject'] = 'Укажите тему.';
$Context->Dictionary['ErrEmailRecipient'] = 'Укажите хотя бы одного получателя.';
$Context->Dictionary['ErrEmailFrom'] = 'Укажите адрес отправителя электронной почты.';
$Context->Dictionary['ErrEmailBody'] = 'Вы должны предоставить либо HTML, либо текст.';
$Context->Dictionary['ErrCategoryNotFound'] = 'Запрашиваемый раздел не найден.';
$Context->Dictionary['ErrCategoryReplacement'] = 'Выберите раздел для замены.';
$Context->Dictionary['ErrCommentNotFound'] = 'Запрашиваемое сообщение не найдено.';
$Context->Dictionary['ErrDiscussionID'] = 'Идентификатор тем не предоставлен.';
$Context->Dictionary['ErrCommentID'] = 'Идентификатор сообщения не предоставлен.';
$Context->Dictionary['ErrPermissionComments'] = 'У вас нет доступа у администрированию сообщений.';
$Context->Dictionary['ErrWhisperInvalid'] = 'Не найдено имя получателя.';
$Context->Dictionary['ErrDiscussionNotFound'] = 'Запрашиваемая тема не найдена.';
$Context->Dictionary['ErrSelectCategory'] = 'Вы должны выбрать раздел для тем.';
$Context->Dictionary['ErrPermissionEditComments'] = "Вы не можете редактировать сообщения другого участника.";
$Context->Dictionary['ErrPermissionDiscussionEdit'] = 'Данная тема не изменилась, т.к. либо этой темы не существует, либо у вас нет доступа к административным функциям этого раздела.';
$Context->Dictionary['ErrRoleNotFound'] = 'Запрашиваемый статус не найден.';
$Context->Dictionary['ErrPermissionInsufficient'] = 'У вас не достаточно привилегий, чтобы выполнить этот запрос.';
$Context->Dictionary['ErrSearchNotFound'] = 'По вашему запросу ничего не найдено.';
$Context->Dictionary['ErrSearchLabel'] = 'Вы должны указать, что ищете (указать метку). Вы сможете нажать на эту метку чтобы осуществить поиск.';
$Context->Dictionary['ErrRoleNotes'] = 'Вы должны оставить примечание относительно изменения статуса.';
$Context->Dictionary['ErrOldPasswordBad'] = 'Старый пароль не верен.';
$Context->Dictionary['ErrNewPasswordMatchBad'] = 'Подтверждение пароля не совпадает.';
$Context->Dictionary['ErrPasswordsMatchBad'] = 'Введенный пароль не совпадает.';
$Context->Dictionary['ErrAgreeTOS'] = 'Вы должны согласиться с правилами пользования.';
$Context->Dictionary['ErrUsernameTaken'] = 'Введенное имя уже занято другим участником.';
$Context->Dictionary['ErrUserNotFound'] = 'Запрашиваемый участник не найден.';
$Context->Dictionary['ErrRemoveUserStyle'] = 'Участник не может быть удален, так как он/она является автором стиля.';
$Context->Dictionary['ErrRemoveUserComments'] = 'Участник не может быть удален, так как он/она следит за сообщениями тем.';
$Context->Dictionary['ErrRemoveUserDiscussions'] = 'Участник не может быть удален, так как он/она следит за темами.';
$Context->Dictionary['ErrInvalidUsername'] = 'Неправильный логин.';
$Context->Dictionary['ErrInvalidPassword'] = 'Неправильный пароль.';
$Context->Dictionary['ErrAccountNotFound'] = 'Невозможно найти профиль, зарегистрированный на этого участника.';
$Context->Dictionary['ErrPasswordRequired'] = 'Вы должны предоставить новый пароль.';
$Context->Dictionary['ErrUserID'] = 'Не предоставлен идентификатор участника.';
$Context->Dictionary['ErrPermissionUserSettings'] = 'У вас нет доступа к управлению настройками этого пользователя.';
$Context->Dictionary['ErrSpamComments'] = 'Вы создали //1 сообщения в течение //2 секунд. Ваш профиль заморожен. Подождите //3 секунд перед тем как оставить сообщение.';
$Context->Dictionary['ErrSpamDiscussions'] = 'Вы создали //1 тем в течение //2секунд. Ваш профиль заморожен. Подождите //3  секунд перед тем как создать новую тему.';
$Context->Dictionary['ErrUserCombination'] = 'Запрашиваемая комбинация логина с паролем не найдена.';
$Context->Dictionary['ErrNoLogin'] = 'Вам не разрешено заходить на сайт.';
$Context->Dictionary['ErrPasswordResetRequest'] = 'Запрос на сброс пароля не получился. Убедитесь в том, что вы правильно вставили ссылку из вашего почтового ящика.';
$Context->Dictionary['ErrSignInToDiscuss'] = 'Вы не можете принимать участия в этой теме. Войдите в систему.';
$Context->Dictionary['ErrPermissionCommentEdit'] = 'Вам не разрешено редактировать это сообщение.';
$Context->Dictionary['ErrRequiredInput'] = 'Вы должны ввести текст сообщения //1 .';
$Context->Dictionary['ErrInputLength'] = '//1 превышает //2 символов.';
$Context->Dictionary['ErrImproperFormat'] = 'Вы не предоставили правильно отформатированное значение для ';
$Context->Dictionary['ErrOpenDirectoryLanguages'] = 'Не могу найти директорию с переводами на различные языки. Убедитесь что у PHP есть доступ к директории //1';
$Context->Dictionary['ErrPermissionAddComments'] = 'Вам не разрешено добавлять сообщения к темам.';
$Context->Dictionary['ErrPermissionStartDiscussions'] = 'Вам не разрешено создавать темы.';

$Context->Dictionary['Warning'] = 'Предупреждение!';
$Context->Dictionary['ApplicationTitles'] = 'Название форума';
$Context->Dictionary['ApplicationTitle'] = 'Заголовок форума';
$Context->Dictionary['BannerTitle'] = 'Заголовок баннера';
$Context->Dictionary['ApplicationTitlesNotes'] = 'Заголовок форума отображается в строке заголовка браузера. Заголовок баннера отображается над закладками меню на странице.';
$Context->Dictionary['CountsTitle'] = 'Отображение списков';
$Context->Dictionary['DiscussionsPerPage'] = 'Тем на 1 странице';
$Context->Dictionary['CommentsPerPage'] = 'Сообщений на 1 странице.';
$Context->Dictionary['SearchResultsPerPage'] = 'Результатов поиска на 1 странице';
$Context->Dictionary['CountsNotes'] = 'Указанные здесь значения ограничивают максимальное количество тем или сообщений, которые будут появляться в списке тем, на странице сообщений и в панели управления.';
$Context->Dictionary['SpamProtectionTitle'] = 'Защита от спама';
$Context->Dictionary['MaxCommentLength'] = 'Максимум символов в сообщении';
$Context->Dictionary['MaxCommentLengthNotes'] = "База данных может сохранять столько данных, сколько позволяет память сервера, но все же, лучше ограничить объем сообщения до минимума.";
$Context->Dictionary['XDiscussionsYSecondsZFreeze'] = 'Участник не может начинать тем больше, чем //1 в течение //2 секунд, иначе его профиль будет заморожен на время: //3 секунд.';
$Context->Dictionary['XCommentsYSecondsZFreeze'] = 'Участник не может оставлять сообщений больше, чем //1  в течение //2 секунд, иначе его профиль будет заморожен на время: //3 секунд.';
$Context->Dictionary['LogAllIps'] = 'Вести лог и производить мониторинг всех IP-адресов';
$Context->Dictionary['SupportContactTitle'] = 'Контакты команды поддержки форума';
$Context->Dictionary['SupportName'] = 'Название';
$Context->Dictionary['SupportEmail'] = 'Электронная почта';
$Context->Dictionary['SupportContactNotes'] = 'Все электронные письма системы, любого назначения, будут адресованы от этого имени и адреса электронной почты.';
$Context->Dictionary['DiscussionLabelsTitle'] = 'Метки тем';
$Context->Dictionary['LabelPrefix'] = 'Префикс метки';
$Context->Dictionary['LabelSuffix'] = 'Суффикс метки';
$Context->Dictionary['WhisperLabel'] = 'Приватная метка';
$Context->Dictionary['StickyLabel'] = 'Прикрепленная метка';
$Context->Dictionary['SinkLabel'] = 'Утопленная метка';
$Context->Dictionary['ClosedLabel'] = 'Закрытая метка';
$Context->Dictionary['HiddenLabel'] = 'Спрятанная метка';
$Context->Dictionary['BookmarkedLabel'] = 'Метка в закладках';
$Context->Dictionary['WebPathToVanilla'] = 'Путь к форуму';
$Context->Dictionary['CookieDomain'] = 'Домен cookies';
$Context->Dictionary['WebPathNotes'] = 'Web-адрес Vanilla должен быть полным адресом, как будто вы задаете его в своем браузере. Что-то на подобие этого: http://www.yourdomain.com/vanilla/';
$Context->Dictionary['CookieSettingsNotes'] = 'С помощью домена cookies вы можете настроить домен, к которому привязана ваша установка Vanilla. Обычно домен cookies будет тем доменом, на котором установлена Vanilla (www.yourdomain.com). Также может быть настроен путь к cookies. (Если вы хотите, чтобы cookies были настроены на все поддомены вышего домена, то просто введите ".yourdomain.com" как ваш домен для cookies)';
$Context->Dictionary['AllowNameChange'] = 'Разрешить участникам изменять свои имена';
$Context->Dictionary['AllowPublicBrowsing'] = 'Разрешить незарегистрированным пользователям просматривать форум';
$Context->Dictionary['UseCategories'] = 'Все темы должны находиться в разделах';
$Context->Dictionary['DiscussionLabelsNotes'] = "Метки тем будут показываться перед заголовками тем на главной странице тем. Префикс и суффикс будут находиться на обоих сторонах метки тем. Если метка тем будут пустой, то суффикс и префикс показываться не будут.";
$Context->Dictionary['ForumOptions'] = 'Опции форума';
$Context->Dictionary['GlobalApplicationChangesSaved'] = 'Изменения были сохранены';
$Context->Dictionary['ApprovedMemberRole'] = 'Статус одобренного участника';
$Context->Dictionary['ApprovedMemberRoleNotes'] = 'Когда новый пользователь допущен администратором к участию на форуме (если одобрение администратором является необходимым), то этот статус будет присвоен новому участнику.';
$Context->Dictionary['NewMemberWelcomeAboard'] = 'Вы стали участником! Добро пожаловать!';
$Context->Dictionary['RoleCategoryNotes'] = 'Выберите раздел,доступный для этого статуса.';
$Context->Dictionary['DebugTitle'] = 'Vanilla исправляет ошибки';
$Context->Dictionary['DebugDescription'] = 'Если у вас есть достаточные привилегии, вы можете включить режим отладки. После этого на каждой странице будут отображаться сделанные запросы. Вы будете единственным, кто будет видеть эти данные для отладки. Используйте эту страницу для включения и выключения режима отладки.';
$Context->Dictionary['CurrentApplicationMode'] = 'Vanilla сейчас работает в следующем режиме: ';
$Context->Dictionary['DEBUG'] = 'Режим отладки';
$Context->Dictionary['RELEASE'] = 'Стабильный режим';
$Context->Dictionary['SwitchApplicationMode'] = 'Нажмите здесь для переключения режима';
$Context->Dictionary['BackToApplication'] = 'Нажмите здесь для перехода назад к Vanilla';
$Context->Dictionary['ErrReadFileSettings'] = 'Произошла ошибка при попытке чтения конфигурационного файла: ';
$Context->Dictionary['CookiePath'] = 'Путь к cookies';
$Context->Dictionary['Wait'] = 'Подождите';
$Context->Dictionary['OldPostDateFormatCode'] = 'j/m/Y';
$Context->Dictionary['XDayAgo'] = '//1 день назад';
$Context->Dictionary['XDaysAgo'] = '//1 дней назад';
$Context->Dictionary['XHourAgo'] = '//1 час назад';
$Context->Dictionary['XHoursAgo'] = '//1 часов назад';
$Context->Dictionary['XMinuteAgo'] = '//1 минуту назад';
$Context->Dictionary['XMinutesAgo'] = '//1 минут назад';
$Context->Dictionary['XSecondAgo'] = '//1 секунду назад';
$Context->Dictionary['XSecondsAgo'] = '//1 секунд назад';
$Context->Dictionary['nothing'] = 'ничего';
$Context->Dictionary['EnableWhispers'] = 'Разрешить шептаться (приватные сообщения)';
$Context->Dictionary['ExtensionFormNotes'] = 'Чтобы активировать дополнение, отметьте его галочкой. <a href="http://getvanilla.com/extensions">Множество разнообразных дополнений</a> доступно бесплатно на сайте Lussumo</a>.';
$Context->Dictionary['EnabledExtensions'] = 'Включенные дополнения';
$Context->Dictionary['DisabledExtensions'] = 'Выключенные дополнения';
$Context->Dictionary['ErrExtensionNotFound'] = 'Дополнение не найдено.';
$Context->Dictionary['UpdatesAndReminders'] = 'Обновления';
$Context->Dictionary['UpdateCheck'] = 'Проверка обновлений';
$Context->Dictionary['UpdateCheckNotes'] = 'Vanilla постоянно обновляется разработчиками и пользователями. Для того, чтобы иметь всегда самую актуальную и безопасную версию, регулярно проверяйте обновления.';
$Context->Dictionary['CheckForUpdates'] = 'Проверить обновления';
$Context->Dictionary['ErrUpdateCheckFailure'] = 'Ошибка при получении информации от Lussumo. Попробуйте проверить обновления Vanilla позже.';
$Context->Dictionary['PleaseUpdateYourInstallation'] = '<strong>ВНИМАНИЕ:</strong> Ваша версия Vanilla //1, но <span class="Highlight">самая актуальная доступная версия //2</span>. Пожалуйста обновите вашу версию форума, скачав новую версию с сайта <a href="http://getvanilla.com">http://getvanilla.com</a>.';
$Context->Dictionary['YourInstallationIsUpToDate'] = 'Ваша версия форума самая актуальная! Проверьте обновления через некоторое время!';
$Context->Dictionary['ErrPermissionHideDiscussions'] = 'Вам не разрешено скрывать тему.';
$Context->Dictionary['ErrPermissionCloseDiscussions'] = 'Вам не разрешено закрывать тему.';
$Context->Dictionary['ErrPermissionStickDiscussions'] = 'Вам не разрешено прикреплять тему.';
$Context->Dictionary['CategoryReorderNotes'] = 'Вы можете сортировать разделы  простым перетаскиванием. Их новый порядок будет сохранен автоматически.';
$Context->Dictionary['RoleReorderNotes'] = 'Сортируйте статусы простым перетаскиванием. Их новый порядок будет сохранен автоматически.';
$Context->Dictionary['PERMISSION_CHECK_FOR_UPDATES'] = 'Проверять обновления';
$Context->Dictionary['PERMISSION_SIGN_IN'] = 'Входить в форум';
$Context->Dictionary['PERMISSION_ADD_COMMENTS'] = 'Добавлять сообщения';
$Context->Dictionary['PERMISSION_ADD_COMMENTS_TO_CLOSED_DISCUSSION'] = 'Добавлять сообщения в закрытые темы';
$Context->Dictionary['PERMISSION_START_DISCUSSION'] = 'Начинать темы';
$Context->Dictionary['PERMISSION_HTML_ALLOWED'] = 'HTML  изображения разрешены';
$Context->Dictionary['PERMISSION_IP_ADDRESSES_VISIBLE'] = 'Видеть IP';
$Context->Dictionary['PERMISSION_APPROVE_APPLICANTS'] = 'Разрешить участвовать, подавшим заявку';
$Context->Dictionary['PERMISSION_MANAGE_REGISTRATION'] = 'Изменять настройки регистрации';
$Context->Dictionary['PERMISSION_EDIT_USERS'] = 'Изменять профиль любого участника';
$Context->Dictionary['PERMISSION_CHANGE_USER_ROLE'] = 'Изменять уровень прав участников (статус)';
$Context->Dictionary['PERMISSION_SORT_ROLES'] = 'Сортировать';
$Context->Dictionary['PERMISSION_ADD_ROLES'] = 'Добавлять новые статусы';
$Context->Dictionary['PERMISSION_EDIT_ROLES'] = 'Редактировать существующие статусы';
$Context->Dictionary['PERMISSION_REMOVE_ROLES'] = 'Удалять существующие статусы';
$Context->Dictionary['PERMISSION_STICK_DISCUSSIONS'] = 'Прикреплять темы';
$Context->Dictionary['PERMISSION_HIDE_DISCUSSIONS'] = 'Скрывать темы';
$Context->Dictionary['PERMISSION_CLOSE_DISCUSSIONS'] = 'Закрывать темы';
$Context->Dictionary['PERMISSION_EDIT_DISCUSSIONS'] = 'Редактировать темы';
$Context->Dictionary['PERMISSION_HIDE_COMMENTS'] = 'Скрывать комментарии';
$Context->Dictionary['PERMISSION_EDIT_COMMENTS'] = 'Редактировать комментарии';
$Context->Dictionary['PERMISSION_ADD_CATEGORIES'] = 'Добавлять разделы';
$Context->Dictionary['PERMISSION_EDIT_CATEGORIES'] = 'Редактировать разделы';
$Context->Dictionary['PERMISSION_REMOVE_CATEGORIES'] = 'Удалять разделы';
$Context->Dictionary['PERMISSION_SORT_CATEGORIES'] = 'Сортировать разделы';
$Context->Dictionary['PERMISSION_VIEW_HIDDEN_DISCUSSIONS'] = 'Видеть скрытые темы';
$Context->Dictionary['PERMISSION_VIEW_HIDDEN_COMMENTS'] = 'Видеть скрытые комментарии';
$Context->Dictionary['PERMISSION_VIEW_ALL_WHISPERS'] = 'Видеть все сообщения шепотом';
$Context->Dictionary['PERMISSION_CHANGE_APPLICATION_SETTINGS'] = 'Изменять настройки форума';
$Context->Dictionary['PERMISSION_MANAGE_EXTENSIONS'] = 'Управлять дополнениями';
$Context->Dictionary['PERMISSION_MANAGE_LANGUAGE'] = 'Изменять язык';
$Context->Dictionary['PERMISSION_MANAGE_STYLES'] = 'Изменять стили';
$Context->Dictionary['PERMISSION_MANAGE_THEMES'] = 'Изменять темы';
$Context->Dictionary['PERMISSION_RECEIVE_APPLICATION_NOTIFICATION'] = 'Включать уведомления по E-Mail о новых заявках на участие';
$Context->Dictionary['PERMISSION_ALLOW_DEBUG_INFO'] = 'Может видеть &laquo;debug info&raquo;';
$Context->Dictionary['PERMISSION_DATABASE_CLEANUP'] = 'Может удалять дополнения';
$Context->Dictionary['PERMISSION_ADD_ADDONS'] = 'Может добавлять &laquo;модули дополнений&raquo;';
$Context->Dictionary['NoEnabledExtensions'] = 'Сейчас ни одно дополнение не активировано.';
$Context->Dictionary['NoDisabledExtensions'] = 'Сейчас ни одно дополнение не деактивировано.';
$Context->Dictionary['NA'] = 'Недоступно';
$Context->Dictionary['Loading'] = 'Загрузка...';
$Context->Dictionary['Simple'] = 'простой вид';
$Context->Dictionary['AboutExtensionPage'] = 'Дополнительная страница';
$Context->Dictionary['AboutExtensionPageNotes'] = 'Эта страница может быть использована авторами дополнений для показа дополнительных страниц в  Vanilla. Вы попали на эту страницу случайно или из-за ошибки в дополнении.';
$Context->Dictionary['NewApplicant'] = 'Новая заявка на участие';
$Context->Dictionary['PERMISSION_SINK_DISCUSSIONS'] = 'Топить темы';
$Context->Dictionary['MakeThisDiscussionSink'] = 'Утопить тему';
$Context->Dictionary['MakeThisDiscussionUnSink'] = 'Поднять эту тему';
$Context->Dictionary['ConfirmUnSink'] = 'Вы действительно хотите поднять эту тему?';
$Context->Dictionary['ConfirmSink'] = 'Вы действительно хотите утопить эту тему?';
$Context->Dictionary['ErrPermissionSinkDiscussions'] = 'Вам не разрешено топить темы';
$Context->Dictionary['YourCommentsWillBeWhisperedToX'] = 'Ваш шепот будет отправлен: //1';
$Context->Dictionary['SMTPHost'] = 'Сервер SMTP';
$Context->Dictionary['SMTPUser'] = 'Пользователь SMTP';
$Context->Dictionary['SMTPPassword'] = 'Пароль SMTP';
$Context->Dictionary['SMTPSettingsNotes'] = 'По умолчанию Vanilla будет использовать как mail-сервер тот сервер, на котором она установлена. Если вы хотите установить для пересылки e-mail другой SMTP-сервер, вы можете настроить его с помощью этих трех настроек. Если вы не хотите использовать SMTP-сервер, то оставьте эти поля пустыми.';
$Context->Dictionary['PagelistNextText'] = 'вперед';
$Context->Dictionary['PagelistPreviousText'] = 'назад';
$Context->Dictionary['EmailSettings'] = 'Опции e-mail';
$Context->Dictionary['UpdateReminders'] = 'Напоминания об обновлениях';
$Context->Dictionary['UpdateReminderNotes'] = 'Мы все забывчивы, именно поэтому в Vanilla можно настроить автоматические напоминания об обновлениях. Каждый, кто имеет доступ к проверке обновлений, будет видеть это напоминание над списком тем.';
$Context->Dictionary['ReminderLabel'] = 'Проверять обновления';
$Context->Dictionary['Never'] = 'Никогда';
$Context->Dictionary['Weekly'] = 'Каждую неделю';
$Context->Dictionary['Monthly'] = 'Каждый месяц';
$Context->Dictionary['Quarterly'] = 'Каждый квартал (3 месяца)';
$Context->Dictionary['ReminderChangesSaved'] = 'Настройки напомонаний об обновлениях сохранены.';
$Context->Dictionary['NeverCheckedForUpdates'] = "Вы еще не проверили Vanilla на обновления.";
$Context->Dictionary['XDaysSinceUpdateCheck'] = 'Прошло дней с последней проверки Vanilla на обновления //1.';
$Context->Dictionary['CheckForUpdatesNow'] = 'Нажмите здесь для проверки обновлений.';
$Context->Dictionary['ManageThemeAndStyle'] = 'Темы';
$Context->Dictionary['ThemeChangesSaved'] = 'Изменения сохранены';
$Context->Dictionary['ChangeThemeNotes'] = 'Заметки к темам ...';
$Context->Dictionary['ThemeLabel'] = 'Доступные темы';
$Context->Dictionary['ChangeStyleNotes'] = 'Заметки к стилям ...';
$Context->Dictionary['StyleLabel'] = 'Доступные стили';
$Context->Dictionary['ApplyStyleToAllUsers'] = 'Применить этот стиль для всех участников';
$Context->Dictionary['ThemeAndStyleManagement'] = 'Управление темами и стилями';
$Context->Dictionary['Check'] = 'Отметить: ';
$Context->Dictionary['All'] = 'все';
$Context->Dictionary['None'] = 'ничего';
$Context->Dictionary['Simple'] = 'Простой поиск';
$Context->Dictionary['ErrorFopen'] = "Произошла ошибка при попытке востановления данных из внешнего источника (\\1).";
$Context->Dictionary['ErrorFromPHP'] = "PHP отчет об ошибке: \\1";
$Context->Dictionary['InvalidHostName'] = 'Неправильное имя хоста: \\1';
$Context->Dictionary['WelcomeToVanillaGetSomeAddons'] = '<strong>Добро пожаловать в Vanilla!</strong>
<br />Как вы видите, это необычный форум, выдержанный в определенном стиле. Обратите внимание на <a href="http://lussumo.com/addons/">дополнения</a>, которые расширят его функциональность.';
$Context->Dictionary['RemoveThisNotice'] = 'Спрятать сообщение';
$Context->Dictionary['OtherSettings'] = 'Другие настройки';
$Context->Dictionary['ChangesSaved'] = 'Изменения сохранены';
$Context->Dictionary['DiscussionType'] = 'Тип тем';
// Added for Vanilla 1.1 on 2007-02-20
$Context->Dictionary['ErrPostBackKeyInvalid'] = 'Возникла проблема с подтверждением подлинности вашей почтовой информации.';
$Context->Dictionary['ErrPostBackActionInvalid'] = 'Информация о вашей почте не была определена корректно.';
// Moved from settings.php
$Context->Dictionary['TextWhispered'] = 'Шепот';
$Context->Dictionary['TextSticky'] = 'Прикреплено';
$Context->Dictionary['TextClosed'] = 'Закрыто';
$Context->Dictionary['TextHidden'] = 'Скрыто';
$Context->Dictionary['TextSink'] = 'Утоплено';
$Context->Dictionary['TextBookmarked'] = 'Закладка';
$Context->Dictionary['TextPrefix'] = '[';
$Context->Dictionary['TextSuffix'] = ']';
// Added for new update checker
$Context->Dictionary['CheckingForUpdates'] = 'Проверка обновления...';
$Context->Dictionary['ApplicationStatusGood'] = 'Vanilla не требует обновлений.';
$Context->Dictionary['ExtensionStatusGood'] = 'Это дополнение не требует обновлений.';
$Context->Dictionary['ExtensionStatusUnknown'] = 'Дополнение не найдено. <a href="http://lussumo.com/docs/doku.php?id=vanilla:administrators:updatecheck">Выясните причину</a>';
$Context->Dictionary['NewVersionAvailable'] = 'Доступна версия \\1. <a href="\\2">Скачать</a>';
// Altered for new applicant management screen
$Context->Dictionary['ApproveForMembership'] = 'Принять';
$Context->Dictionary['DeclineForMembership'] = 'Отклонить';
$Context->Dictionary['ApplicantsNotes'] = 'Используйте эту форму для принятия или отклонения новых пользователей.';
$Context->Dictionary['NoApplicants'] = 'На данный момент нет претендентов к рассмотрению...';
// Added for ajax/sortroles.php
$Context->Dictionary['ErrPermissionSortRoles'] = 'Вам не разрешено сортировать полномочия';
$Context->Dictionary['ErrPermissionSortCategories'] = 'Вам не разрешено сортировать разделы';
/* Please do not remove or alter this definition */
$Context->Dictionary['PanelFooter'] = '<p id="AboutVanilla"><a href="http://getvanilla.com">Vanilla '.APPLICATION_VERSION.'</a> - продукт компании <a href="http://lussumo.com">Lussumo</a>. Подробнее: <a href="http://lussumo.com/docs">документация</a>, <a href="http://lussumo.com/community">поддержка</a>.</p>';
function RuPluralForm($N, $F1, $F2, $F5){
	$N = intval($N);
	$N = Abs($N) % 100;
	$N1 = $N % 10;
	if($N > 10 && $N < 20) return $F5;
	if($N1 > 1 && $N1 < 5) return $F2;
	if($N1 == 1) return $F1;
	return $F5;
}

# Russian Plural Form TimeDiff function Hack
# library/Framework/Framework.Functions.php (TimeDiff)
function RuTimeDiff(&$Context, $Time, $TimeToCompare = '') {
	if($TimeToCompare == '') $TimeToCompare = time();
	$Difference = $TimeToCompare - $Time;

	$Days = floor($Difference/60/60/24);
	if($Days > 7) return date($Context->GetDefinition('OldPostDateFormatCode'), $Time);
	elseif($Days >= 1) return sprintf('%d %s назад', $Days, RuPluralForm($Days, 'день', 'дня', 'дней'));

	$Difference -= $Days*60*60*24;
	$Hours = floor($Difference/60/60);
	if($Hours >= 1) return sprintf('%d %s назад', $Hours, RuPluralForm($Hours, 'час', 'часа', 'часов'));

	$Difference -= $Hours*60*60;
	$Minutes = floor($Difference/60);
	if($Minutes >= 1) return sprintf('%d %s назад', $Minutes, RuPluralForm($Minutes, 'минуту', 'минуты', 'минут'));
	
	$Difference -= $Minutes*60;
	return sprintf('%d %s назад', $Difference, RuPluralForm($Difference, 'секунду', 'секунды', 'секунд'));
}
?>
