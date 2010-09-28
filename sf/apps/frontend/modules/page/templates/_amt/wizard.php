<b>Ваши личные данные не сохраняются на сайте EasyFinance.ru</b> - они отправляются сразу в банк по защищённому соединению<br><br>

Удобно переходить по полям клавишей TAB, переходить на следующий шаг - клавишей Enter.<br><br>
Для отправки анкеты необходимо заполнить все обязательные поля (отмечены звездочкой). Незаполненные обязательные поля будут выделены красным цветом.<br /><br />

        <div id="wizard">
            <div id="wz_header">
                <ol>
                    <li class="wz_tab_header">Личные данные</li>
                    <li class="wz_tab_header">Прописка / Регистрация</li>
                    <li class="wz_tab_header">Местонахождение</li>
                    <li class="wz_tab_header">Основной документ</li>
                    <li class="wz_tab_header">Для иностранных граждан</li>
                    <li class="wz_tab_header">Контакты</li>
                    <li class="wz_tab_header">Работа</li>
                    <li class="wz_tab_header">Свойства карты</li>
                    <li class="wz_tab_header wz_tab_header_not_main">Дополнительная карта</li>
                    <li class="wz_tab_header wz_tab_header_not_main">Дополнительная карта для лиц до 14</li>
                    <li class="wz_tab_header">Пароль</li>
                </ol>
            </div>

            <div id="wz_content">
                <!-- Tab 0 -->
                <div class="wz_tab_content">
                    <h3>Личные данные</h3>

                    <form id="personal_info" action="" method="post" class="wz_frm" onsubmit="return wzStepNextBack('+');">
                        <fieldset>
                            <input type="hidden" name="step_name" value="personal_info" />

                            <div class="fld_holder">
                                <label for="wz_surname">Фамилия <span class="wz_req_fld">*</span></label><br />
                                <input type="text" name="wz_surname" id="wz_surname" value="" maxlength="255" onblur="dValidator.catchOnChange(this, null, 2);" /><br /><br />
                            </div>

                            <div class="fld_holder">
                                <label for="wz_name">Имя <span class="wz_req_fld">*</span></label><br />
                                <input type="text" name="wz_name" id="wz_name" value="" maxlength="255" onblur="dValidator.catchOnChange(this, null, 2);" /><br /><br />
                            </div>

                            <div class="fld_holder">
                                <label for="wz_midname">Отчество <span class="wz_req_fld">*</span></label><br />
                                <input type="text" name="wz_midname" id="wz_midname" value="" maxlength="255" onblur="dValidator.catchOnChange(this, null, 2);" /><br /><br />
                            </div>

                            <div class="fld_holder">
                                <label for="wz_surname_translit">Фамилия - латинская транскрипция <span class="wz_req_fld">*</span></label><br />
                                <input type="text" name="wz_surname_translit" id="wz_surname_translit" value="" maxlength="255" onblur="dValidator.catchOnChange(this, null, 2);" /><br /><br />
                            </div>

                            <div class="fld_holder">
                                <label for="wz_name_translit">Имя - латинская транскрипция <span class="wz_req_fld">*</span></label><br />
                                <input type="text" name="wz_name_translit" id="wz_name_translit" value="" maxlength="255" onblur="dValidator.catchOnChange(this, null, 2);" /><br /><br />
                            </div>

                            <div class="fld_holder">
                                <label for="wz_birthdate">Дата рождения (в формате дд.мм.гггг, например 20.11.1960)<span class="wz_req_fld">*</span></label><br />
                                <input type="text" name="wz_birthdate" id="wz_birthdate" value="" maxlength="10" onblur="dValidator.catchOnChange(this, null, 2);" /><br /><br />
                            </div>

                            <div class="fld_holder">
                                <label for="wz_birthplace">Место рождения <span class="wz_req_fld">*</span></label><br />
                                <input type="text" name="wz_birthplace" id="wz_birthplace" value="" maxlength="255" onblur="dValidator.catchOnChange(this, null, 2);" /><br /><br />
                            </div>

                            <div class="fld_holder">
                                Пол <span class="wz_req_fld">*</span><br />
                                <input type="radio" name="wz_sex" id="wz_sex_0" value="0" onclick="dValidator.catchOnChange(this, null, 2);" onblur="dValidator.catchOnChange(this, null, 2);" /> <label for="wz_sex_0">Мужской</label><br />
                                <input type="radio" name="wz_sex" id="wz_sex_1" value="1" onclick="dValidator.catchOnChange(this, null, 2);" onblur="dValidator.catchOnChange(this, null, 2);" /> <label for="wz_sex_1">Женский</label><br />
                                <br />
                            </div>

                            <div class="fld_holder">
                                <label for="wz_citizenship">Гражданство <span class="wz_req_fld">*</span></label><br />
                                <select type="text" name="wz_citizenship" id="wz_citizenship"></select><br /><br />
                            </div>

                            <div class="fld_holder">
                                <label for="wz_inn">ИНН</label><br />
                                <input type="text" name="wz_inn" id="wz_inn" value="" maxlength="255" onblur="dValidator.catchOnChange(this, null, 2);" /><br /><br />
                            </div>

                            <input type="submit" value="Далее &gt;&gt;" />
                        </fieldset>
                    </form>
                </div>

                <!-- Tab 1 -->
                <div class="wz_tab_content">
                    <h3>Адрес прописки / регистрации</h3>

                    <form id="registration_address" action="" method="post" class="wz_frm" onsubmit="return wzStepNextBack('+');">
                        <fieldset>
                            <input type="hidden" name="step_name" value="registration_address" />

                            <div class="fld_holder">
                                <label for="wz_reg_country">Страна <span class="wz_req_fld">*</span></label><br />
                                <select type="text" name="wz_reg_country" id="wz_reg_country"></select><br /><br />
                            </div>

                            <div class="fld_holder">
                                <label for="wz_reg_index">Индекс <span class="wz_req_fld">*</span></label><br />
                                <input type="text" name="wz_reg_index" id="wz_reg_index" value="" maxlength="255" onblur="dValidator.catchOnChange(this, null, 2);" /><br /><br />
                            </div>

                            <div class="fld_holder">
                                <label for="wz_reg_region">Регион, район <span class="wz_req_fld">*</span></label><br />
                                <select type="text" name="wz_reg_region" id="wz_reg_region"></select><br /><br />
                            </div>

                            <div class="fld_holder">
                                <label for="wz_reg_city">Название населенного пункта <span class="wz_req_fld">*</span></label><br />
                                <input type="text" name="wz_reg_city" id="wz_reg_city" value="" maxlength="255" onblur="dValidator.catchOnChange(this, null, 2);" /><br /><br />
                            </div>

                            <div class="fld_holder">
                                <label for="wz_reg_street">Улица <span class="wz_req_fld">*</span></label><br />
                                <input type="text" name="wz_reg_street" id="wz_reg_street" value="" maxlength="255" onblur="dValidator.catchOnChange(this, null, 2);" /><br /><br />
                            </div>

                            <div class="fld_holder">
                                <label for="wz_reg_house">Дом <span class="wz_req_fld">*</span></label><br />
                                <input type="text" name="wz_reg_house" id="wz_reg_house" value="" maxlength="255" onblur="dValidator.catchOnChange(this, null, 2);" /><br /><br />
                            </div>

                            <div class="fld_holder">
                                <label for="wz_reg_building">Корпус</label><br />
                                <input type="text" name="wz_reg_building" id="wz_reg_building" value="" maxlength="255" /><br /><br />
                            </div>

                            <div class="fld_holder">
                                <label for="wz_reg_appartment">Квартира</label><br />
                                <input type="text" name="wz_reg_appartment" id="wz_reg_appartment" value="" maxlength="255" /><br /><br />
                            </div>

                            <input type="button" value="&lt;&lt;Назад" onclick="return wzStepNextBack('-');" />
                            <input type="submit" value="Далее &gt;&gt;" />
                        </fieldset>
                    </form>
                </div>

                <!-- Tab 2 -->
                <div class="wz_tab_content">
                    <h3>Адрес местонахождения (для переписки)</h3>
                    <input id="btnCopyRegistrationToAddress" type="button" value="Скопировать из прописки / регистрации"/><br /><br />

                    <form id="actual_address" action="" method="post" class="wz_frm" onsubmit="return wzStepNextBack('+');">
                        <fieldset>
                            <input type="hidden" name="step_name" value="actual_address" />

                            <div class="fld_holder">
                                <label for="wz_actual_country">Страна <span class="wz_req_fld">*</span></label><br />
                                <select type="text" name="wz_actual_country" id="wz_actual_country"></select><br /><br />
                            </div>

                            <div class="fld_holder">
                                <label for="wz_actual_index">Индекс <span class="wz_req_fld">*</span></label><br />
                                <input type="text" name="wz_actual_index" id="wz_actual_index" value="" maxlength="255" onblur="dValidator.catchOnChange(this, null, 2);" /><br /><br />
                            </div>

                            <div class="fld_holder">
                                <label for="wz_actual_region">Регион, район <span class="wz_req_fld">*</span></label><br />
                                <select type="text" name="wz_actual_region" id="wz_actual_region"></select><br /><br />
                            </div>

                            <div class="fld_holder">
                                <label for="wz_actual_city">Название населенного пункта <span class="wz_req_fld">*</span></label><br />
                                <input type="text" name="wz_actual_city" id="wz_actual_city" value="" maxlength="255" onblur="dValidator.catchOnChange(this, null, 2);" /><br /><br />
                            </div>

                            <div class="fld_holder">
                                <label for="wz_actual_street">Улица <span class="wz_req_fld">*</span></label><br />
                                <input type="text" name="wz_actual_street" id="wz_actual_street" value="" maxlength="255" onblur="dValidator.catchOnChange(this, null, 2);" /><br /><br />
                            </div>

                            <div class="fld_holder">
                                <label for="wz_actual_house">Дом <span class="wz_req_fld">*</span></label><br />
                                <input type="text" name="wz_actual_house" id="wz_actual_house" value="" maxlength="255" onblur="dValidator.catchOnChange(this, null, 2);" /><br /><br />
                            </div>

                            <div class="fld_holder">
                                <label for="wz_actual_building">Корпус</label><br />
                                <input type="text" name="wz_actual_building" id="wz_actual_building" value="" maxlength="255" /><br /><br />
                            </div>

                            <div class="fld_holder">
                                <label for="wz_actual_appartment">Квартира</label><br />
                                <input type="text" name="wz_actual_appartment" id="wz_actual_appartment" value="" maxlength="255" /><br /><br />
                            </div>

                            <input type="button" value="&lt;&lt;Назад" onclick="return wzStepNextBack('-');" />
                            <input id="btnAddressNext" type="submit" value="Далее &gt;&gt;" />
                        </fieldset>
                    </form>
                </div>

                <!-- Tab 3 -->
                <div class="wz_tab_content">
                    <h3>Основной документ</h3>

                    <form id="rf_passport" action="" method="post" class="wz_frm" onsubmit="return wzStepNextBack('+');">
                        <fieldset>
                            <input type="hidden" name="step_name" value="rf_passport" />

                            <div class="fld_holder">
                                <label for="wz_doc_title">Наименование документа <span class="wz_req_fld">*</span></label><br />
                                <select name="wz_doc_title" id="wz_doc_title" value="" onblur="dValidator.catchOnChange(this, null, 2);">
                                    <option value="PASSPORT_RF" selected="selected">Паспорт гражданина РФ</option>
                                    <option value="BIRTH_CERTIFICATE">Свидетельство о рождении</option>
                                    <option value="OFFICER_ID">Удостоверение личности офицера</option>
                                    <option value="MINMORFLOT_PASSPORT">Паспорт Минморфлота</option>
                                    <option value="ARMY_DOC">Военный билет</option>
                                    <option value="PASSPORT_RF_DIPLOMATIC">Дипломатический паспорт гражданина РФ</option>
                                    <option value="FOREIGN_PASSPORT">Иностранный паспорт</option>
                                    <option value="REGISTRATION_OF_IMIGRANT">Свидетельство о регистрации и ходатайства иммигранта</option>
                                    <option value="RESIDENCE_PERMIT_IN_RUSSIA">Вид на жительство в РФ</option>
                                    <option value="REFUGEE_RF">Удостоверение беженца</option>
                                    <option value="TEMPORARY_IDENTITY_CARD">Временное удостоверение личности</option>
                                    <option value="INTERNATIONAL_PASSPORT_RF">Загранпаспорт гражданина РФ</option>
                                    <option value="SEAMAN_PASSPORT">Паспорт моряка</option>
                                    <option value="MILITARY_TICKET">Военный билет офицера запаса</option>
                                    <option value="DRIVING_DOCUMENT">Водительское удостоверение</option>
                                    <option value="PERSON_ID">Удостоверение личности</option>
                                    <option value="OTHER_DOC">Иные документы</option>
                                    <option value="LIVE_ORDER">Разрешение на временное проживание</option>
                                    <option value="OTHER_FRGN">Иное ин. удостов.</option>
                                    <option value="05">Справка об освобождении из места лишения свободы</option>
                                </select>
                                <br /><br />
                            </div>

                            <div class="fld_holder">
                                <label for="wz_rf_id_series">Серия <span class="wz_req_fld">*</span></label><br />
                                <input type="text" name="wz_rf_id_series" id="wz_rf_id_series" value="" onblur="dValidator.catchOnChange(this, null, 2);" /><br /><br />
                            </div>

                            <div class="fld_holder">
                                <label for="wz_rf_id_number">Номер <span class="wz_req_fld">*</span></label><br />
                                <input type="text" name="wz_rf_id_number" id="wz_rf_id_number" value="" onblur="dValidator.catchOnChange(this, null, 2);" /><br /><br />
                            </div>

                            <div class="fld_holder">
                                <label for="wz_rf_id_organisation">Кем выдан <span class="wz_req_fld">*</span></label><br />
                                <textarea name="wz_rf_id_organisation" id="wz_rf_id_organisation" cols="50" rows="10" onblur="dValidator.catchOnChange(this, null, 2);"></textarea><br /><br />
                            </div>

                            <div class="fld_holder">
                                <label for="wz_rf_id_organisation_code">Код подразделения</label><br />
                                <input type="text" name="wz_rf_id_organisation_code" id="wz_rf_id_organisation_code" value="" maxlength="10" onblur="dValidator.catchOnChange(this, null, 2);" /><br /><br />
                            </div>

                            <div class="fld_holder">
                                <label for="wz_rf_id_date">Дата выдачи (в формате дд.мм.гггг, например 20.11.1980)<span class="wz_req_fld">*</span></label><br />
                                <input type="text" name="wz_rf_id_date" id="wz_rf_id_date" value="" maxlength="10" onblur="dValidator.catchOnChange(this, null, 2);" /><br /><br />
                            </div>

                            <div class="fld_holder">
                                <label for="wz_rf_expiration_date">Дата окончания действия документа (в формате дд.мм.гггг, например 20.11.1960)</label><br />
                                <input type="text" name="wz_rf_expiration_date" id="wz_rf_expiration_date" value="" maxlength="10" onblur="dValidator.catchOnChange(this, null, 2);" /><br /><br />
                            </div>

                            <input type="button" value="&lt;&lt;Назад" onclick="return wzStepNextBack('-');" />
                            <input type="submit" value="Далее &gt;&gt;" />
                        </fieldset>
                    </form>
                </div>

                <!-- Tab 4 -->
                <div class="wz_tab_content">
                    <h3>Для иностранных граждан</h3>

                    <form id="foreign_passport" action="" method="post" class="wz_frm" onsubmit="return wzStepNextBack('+');">
                        <b>Данные документа, подтверждающего право на жительство</b><br/><br/>

                        <fieldset>
                            <input type="hidden" name="step_name" value="foreign_passport" />

                            <div class="fld_holder">
                                <label for="wz_residence_title">Наименование документа <span class="wz_req_fld">*</span></label><br />
                                <select name="wz_residence_title" id="wz_residence_title" value="" onblur="dValidator.catchOnChange(this, null, 2);">
                                    <option value="OTHER_DOC" selected="selected">Иной документ</option>
                                    <option value="LIVE_ORDER">Разрешение на временное проживание</option>
                                    <option value="12">Вид на жительство в РФ</option>
                                    <option value="VIZA">Виза</option>
                                </select>
                                <br /><br />
                            </div>

                            <div class="fld_holder">
                                <label for="wz_foreign_id_series">Серия</label><br />
                                <input type="text" name="wz_foreign_id_series" id="wz_foreign_id_series" value="" maxlength="50" /><br /><br />
                            </div>

                            <div class="fld_holder">
                                <label for="wz_foreign_id_number">Номер</label><br />
                                <input type="text" name="wz_foreign_id_number" id="wz_foreign_id_number" value="" maxlength="50" /><br /><br />
                            </div>

                            <div class="fld_holder">
                                <label for="wz_foreign_id_date">Дата начала срока пребывания (в формате дд.мм.гггг, например 20.11.1960)</label><br />
                                <input type="text" name="wz_foreign_id_date" id="wz_foreign_id_date" value="" maxlength="10" /><br /><br />
                            </div>

                            <div class="fld_holder">
                                <label for="wz_foreign_id_expire">Дата окончания срока пребывания (в формате дд.мм.гггг, например 20.11.1960)</label><br />
                                <input type="text" name="wz_foreign_id_expire" id="wz_foreign_id_expire" value="" maxlength="10" /><br /><br />
                            </div>

                            <b>Данные миграционной карты</b><br/><br/>

                            <div class="fld_holder">
                                <label for="wz_migration_id_number">Номер</label><br />
                                <input type="text" name="wz_migration_id_number" id="wz_migration_id_number" value="" maxlength="50" /><br /><br />
                            </div>

                            <div class="fld_holder">
                                <label for="wz_migration_id_date">Дата начала срока пребывания (в формате дд.мм.гггг, например 20.11.1960)</label><br />
                                <input type="text" name="wz_migration_id_date" id="wz_migration_id_date" value="" maxlength="10" /><br /><br />
                            </div>

                            <div class="fld_holder">
                                <label for="wz_migration_id_expire">Дата окончания срока пребывания (в формате дд.мм.гггг, например 20.11.1960)</label><br />
                                <input type="text" name="wz_migration_id_expire" id="wz_migration_id_expire" value="" maxlength="10" /><br /><br />
                            </div>

                            <input type="button" value="&lt;&lt;Назад" onclick="return wzStepNextBack('-');" />
                            <input type="submit" value="Далее &gt;&gt;" />
                        </fieldset>
                    </form>
                </div>

                <!-- Tab 5 -->
                <div class="wz_tab_content">
                    <h3>Контактная информация</h3>

                    <form id="contacts" action="" method="post" class="wz_frm" onsubmit="return wzStepNextBack('+');">
                        <fieldset>
                            <input type="hidden" name="step_name" value="contacts" />

                            <div class="fld_holder">
                                <label for="wz_phone_home">Телефон домашний</label><br />
                                <input type="text" name="wz_phone_home" id="wz_phone_home" value="" maxlength="255" onblur="dValidator.catchOnChange(this, null, 2);" /><br /><br />
                            </div>

                            <!--
                            // см. #1512
                            <div class="fld_holder">
                                <label for="wz_mail">Электронный адрес (Ваш личный адрес <b>не</b> на easyfinance.ru)<span class="wz_req_fld">*</span></label><br />
                                <input type="text" name="wz_mail" id="wz_mail" value="" maxlength="255" onblur="dValidator.catchOnChange(this, null, 2);" />
                                <span class="wz_err_msg">укажите корректный e-mail</span>
                                <br /><br />
                            </div>
                            -->

                            <div class="fld_holder">
                                <label for="wz_phone_mob">Телефон мобильный <span class="wz_req_fld">*</span></label><br />
                                <input type="text" name="wz_phone_mob" id="wz_phone_mob" value="" maxlength="255" onblur="dValidator.catchOnChange(this, null, 2);" title="Например: +71234567890" /><br /><br />
                            </div>

                            <div class="fld_holder">
                                <label for="wz_other_contacts">Иное</label><br />
                                <input type="text" name="wz_other_contacts" id="wz_other_contacts" value="" maxlength="255" /><br /><br />
                            </div>

                            <input type="button" value="&lt;&lt;Назад" onclick="return wzStepNextBack('-');" />
                            <input type="submit" value="Далее &gt;&gt;" />
                        </fieldset>
                    </form>
                </div>

                <!-- Tab 6 -->
                <div class="wz_tab_content">
                    <h3>Место работы</h3>

                    <form id="work_info" action="" method="post" class="wz_frm" onsubmit="return wzStepNextBack('+');">
                        <fieldset>
                            <input type="hidden" name="step_name" value="work_info" />

                            <div class="fld_holder">
                                <label for="wz_work_name">Название организации <span class="wz_req_fld">*</span></label><br />
                                <input type="text" name="wz_work_name" id="wz_work_name" value="" maxlength="255" onblur="dValidator.catchOnChange(this, null, 2);" /><br /><br />
                            </div>

                            <div class="fld_holder">
                                <label for="wz_work_position">Должность <span class="wz_req_fld">*</span></label><br />
                                <input type="text" name="wz_work_position" id="wz_work_position" value="" maxlength="255" onblur="dValidator.catchOnChange(this, null, 2);" /><br /><br />
                            </div>

                            <div class="fld_holder">
                                <label for="wz_work_address">Адрес <span class="wz_req_fld">*</span></label><br />
                                <input type="text" name="wz_work_address" id="wz_work_address" value="" maxlength="255" onblur="dValidator.catchOnChange(this, null, 2);" /><br /><br />
                            </div>

                            <div class="fld_holder">
                                <label for="wz_work_phone">Телефон <span class="wz_req_fld">*</span></label><br />
                                <input type="text" name="wz_work_phone" id="wz_work_phone" value="" maxlength="255" onblur="dValidator.catchOnChange(this, null, 2);" /><br /><br />
                            </div>

                            <input type="button" value="&lt;&lt;Назад" onclick="return wzStepNextBack('-');" />
                            <input type="submit" value="Далее &gt;&gt;" />
                        </fieldset>
                    </form>
                </div>

                <!-- Tab 7 -->
                <div class="wz_tab_content">
                    <h3>Международная банковская карта</h3>

                    <form id="card_info" action="" method="post" class="wz_frm" onsubmit="return wzStepNextBack('+');">
                        <fieldset>
                            <input type="hidden" name="step_name" value="card_info" />

                            <div class="fld_holder">
                                Основная или дополнительная <span class="wz_req_fld">*</span><br />
                                <input type="radio" name="wz_card_is_main" id="wz_card_is_main_1" value="1" checked="yes" onclick="dValidator.catchOnChange(this, null, 2);" onblur="dValidator.catchOnChange(this, null, 2);" /> <label for="wz_card_is_main_1">Основная карта</label><br />
                                <input type="radio" name="wz_card_is_main" id="wz_card_is_main_0" value="0" onclick="dValidator.catchOnChange(this, null, 2);" onblur="dValidator.catchOnChange(this, null, 2);" /> <label for="wz_card_is_main_0">Дополнительная карта</label><br />
                                <br />
                            </div>

                            <div class="fld_holder">
                                <label for="wz_card_currency">Валюта счета <span class="wz_req_fld">*</span></label><br />
                                <select name="wz_card_currency" id="wz_card_currency" disabled="disabled">
                                    <option value="0">Рубли</option>
                                    <option value="1">Доллары</option>
                                    <option value="2">Евро</option>
                                </select>
                                <br /><br />
                            </div>

                            <div class="fld_holder">
                                <label for="wz_card_expiration">Срок действия карты <span class="wz_req_fld">*</span></label><br />
                                <select name="wz_card_expiration" id="wz_card_expiration">
                                    <option value="1">1 года</option>
                                    <option value="2" selected="selected">2 года</option>
                                </select>
                                <br /><br />
                            </div>

                            <div class="fld_holder">
                                <table id="tblAmtCards">
                                    <tr style="font-weight: bold;">
                                        <td>Тип карты <span class="wz_req_fld">*</span></td>
                                        <td>Стоимость годового обслуживания</td>
                                        <td>Первоначальное пополнение счёта</td>
                                    </tr>
                                    <tr>
                                        <td><input type="radio" name="wz_card_type" id="wz_card_type_0" value="Visa Gold" checked="yes" onclick="dValidator.catchOnChange(this, null, 2);" onblur="dValidator.catchOnChange(this, null, 2);" /> <label for="wz_card_type_0">VISA Gold</label><br /></td>
                                        <td>150 руб. /5$ /4€</td>
                                        <td>5000 руб. /145$ /115€</td>
                                    </tr>
                                    <tr>
                                        <td><input type="radio" name="wz_card_type" id="wz_card_type_1" value="Visa Classic" onclick="dValidator.catchOnChange(this, null, 2);" onblur="dValidator.catchOnChange(this, null, 2);" /> <label for="wz_card_type_1">VISA Classic</label><br /></td>
                                        <td>150 руб. /5$ /4€</td>
                                        <td>800 руб./ 25$/ 20€</td>
                                    </tr>
                                    <tr>
                                        <td><input type="radio" name="wz_card_type" id="wz_card_type_6" value="Visa Classic Individual Design" onclick="dValidator.catchOnChange(this, null, 2);" onblur="dValidator.catchOnChange(this, null, 2);" /> <label for="wz_card_type_6">Visa Classic Individual Design (нанесение дизайна 500р)</label><br /></td>
                                        <td>150 руб. /5$ /4€</td>
                                        <td>800 руб./ 25$/ 20€</td>
                                    </tr>
                                    <tr>
                                        <td><input type="radio" name="wz_card_type" id="wz_card_type_2" value="Visa Classic Unembossed" onclick="dValidator.catchOnChange(this, null, 2);" onblur="dValidator.catchOnChange(this, null, 2);" /> <label for="wz_card_type_2">VISA Classic Unembossed</label><br /></td>
                                        <td>150 руб. /5$ /4€</td>
                                        <td>150 руб. /5$ /4€</td>
                                    </tr>
                                    <tr>
                                        <td><input type="radio" name="wz_card_type" id="wz_card_type_3" value="Visa Platinum" onclick="dValidator.catchOnChange(this, null, 2);" onblur="dValidator.catchOnChange(this, null, 2);" /> <label for="wz_card_type_3">VISA Platinum</label><br /></td>
                                        <td>6500 руб./185$ /145€</td>
                                        <td>равен стоимости обслуживания</td>
                                    </tr>
                                    <tr>
                                        <td><input type="radio" name="wz_card_type" id="wz_card_type_4" value="MasterCard Gold" onclick="dValidator.catchOnChange(this, null, 2);" onblur="dValidator.catchOnChange(this, null, 2);" /> <label for="wz_card_type_4">MasterCard Gold</label><br /></td>
                                        <td>150 руб. /5$ /4€</td>
                                        <td>5000 руб. /145$ /115€</td>
                                    </tr>
                                    <tr>
                                        <td><input type="radio" name="wz_card_type" id="wz_card_type_5" value="MasterCard Standard" onclick="dValidator.catchOnChange(this, null, 2);" onblur="dValidator.catchOnChange(this, null, 2);" /> <label for="wz_card_type_5">MasterCard Standard</label><br /></td>
                                        <td>150 руб. /5$ /4€</td>
                                        <td>800 руб./ 25$/ 20€</td>
                                    </tr>
                                </table>
                                <br />
                            </div>

                            <div class="fld_holder">
                                Срочность выпуска <span class="wz_req_fld">*</span><br />
                                <input type="radio" name="wz_card_rush" id="wz_card_rush_0" value="0" checked="yes" onclick="dValidator.catchOnChange(this, null, 2);" onblur="dValidator.catchOnChange(this, null, 2);" /> <label for="wz_card_rush_0">Плановый выпуск карты</label><br />
                                <input type="radio" name="wz_card_rush" id="wz_card_rush_1" value="1" onclick="dValidator.catchOnChange(this, null, 2);" onblur="dValidator.catchOnChange(this, null, 2);" /> <label for="wz_card_rush_1">Экстренный выпуск карты</label><br />
                                <br />
                            </div>

                            <div class="fld_holder">
                                Услуга SMS-информирования<br />
                                <input type="checkbox" name="wz_card_sms_info" id="wz_card_sms_info" value="1" /> <label for="wz_card_sms_info">Включить SMS-информирование</label><br />
                                <br />
                            </div>

                            <input type="hidden" name="wz_card_account_info_to" id="wz_card_account_info_to" value="email" >

                            <div class="fld_holder">
                                Также выписки по счёту будут<br>автоматически направляться на<br />
                                <input type="text" disabled="disabled" name="wz_card_account_mail" id="wz_card_account_mail" value="" maxlength="255" size="30" readonly="readonly" /><br /><br />
                                <br />
                            </div>

                            <input type="button" value="&lt;&lt;Назад" onclick="return wzStepNextBack('-');" />
                            <input type="submit" value="Далее &gt;&gt;" />
                        </fieldset>
                    </form>
                </div>

                <!-- Tab 8 -->
                <div class="wz_tab_content">
                    <h3>Дополнительная карта</h3>

                    <p>Если заказывается дополнительная карта, заполните, пожалуйста, нижеследующие графы:</p>

                    <form id="addit_card_info" action="" method="post" class="wz_frm" onsubmit="return wzStepNextBack('+');">
                        <fieldset>
                            <input type="hidden" name="step_name" value="addit_card_info" />

                            <div class="fld_holder">
                                <label for="wz_addit_card_lastname">Фамилия владельца карточного счета</label><br />
                                <input type="text" name="wz_addit_card_lastname" id="wz_addit_card_lastname" value="" maxlength="255" /><br /><br />
                            </div>
                            <div class="fld_holder">
                                <label for="wz_addit_card_firstname">Имя владельца карточного счета</label><br />
                                <input type="text" name="wz_addit_card_firstname" id="wz_addit_card_firstname" value="" maxlength="255" /><br /><br />
                            </div>
                            <div class="fld_holder">
                                <label for="wz_addit_card_secondname">Отчество владельца карточного счета</label><br />
                                <input type="text" name="wz_addit_card_secondname" id="wz_addit_card_secondname" value="" maxlength="255" /><br /><br />
                            </div>
                            <div class="fld_holder">
                                <label for="wz_addit_card_sks_number">Номер СКС основной карты</label><br />
                                <input type="text" name="wz_addit_card_sks_number" id="wz_addit_card_sks_number" value="" maxlength="255" /><br /><br />
                            </div>

                            <div class="fld_holder">
                                <label for="wz_addit_card_limit">Ежемесячный доступный лимит (в валюте счета)</label><br />
                                <input type="text" name="wz_addit_card_limit" id="wz_addit_card_limit" value="" maxlength="255" /><br /><br />
                            </div>

                            <input type="button" value="&lt;&lt;Назад" onclick="return wzStepNextBack('-');" />
                            <input type="submit" value="Далее &gt;&gt;" />
                        </fieldset>
                    </form>
                </div>

                <!-- Tab 9 -->
                <div class="wz_tab_content">
                    <h3>Дополнительная карта для лица до 14 лет</h3>

                    <p>Если заказывается дополнительная карта для лица в возрасте до 14 лет, дополнительно заполните, пожалуйста, нижеследующие графы:</p>

                    <form id="addit_card14_info" action="" method="post" class="wz_frm" onsubmit="return wzStepNextBack('+');">
                        <fieldset>
                            <input type="hidden" name="step_name" value="addit_card14_info" />

                            <div class="fld_holder">
                                Согласие владельца карточного счета<br />
                                <input type="checkbox" name="wz_addit_card14_agreement" id="wz_addit_card14_agreement" value="1" /> <label for="wz_addit_card14_agreement">Денежные средства, размещенные на карточном счете, предоставляются владельцем карточного счета - законным представителем (родителем, усыновителем или попечителем) несовершеннолетнему лицу для свободного распоряжения.</label><br />
                                <br />
                            </div>

                            <div class="fld_holder">
                                Документ, удостоверяющий права законного представителя<br />
                                <input type="radio" name="wz_addit_card14_document" id="wz_addit_card14_document_0" value="birth certiticate" /> <label for="wz_addit_card14_document_0">Свидетельство о рождении</label><br />
                                <input type="radio" name="wz_addit_card14_document" id="wz_addit_card14_document_1" value="judgement" /> <label for="wz_addit_card14_document_1">Решение суда</label><br />
                                <input type="radio" name="wz_addit_card14_document" id="wz_addit_card14_document_2" value="trustee" /> <label for="wz_addit_card14_document_2">Документ, подтверждающий полномочия попечителя</label><br />
                                <br />
                            </div>
                            <div class="fld_holder">
                                <label for="wz_addit_card14_organisation">Выдан кем: </label><br />
                                <textarea name="wz_addit_card14_organisation" id="wz_addit_card14_organisation" cols="50" rows="10"></textarea><br /><br />
                            </div>
                            <div class="fld_holder">
                                <label for="wz_addit_card14_date">Выдан когда: (в формате дд.мм.гггг, например 20.11.2000)*</label><br />
                                <input type="text" name="wz_addit_card14_date" id="wz_addit_card14_date" value="" maxlength="10" /><br /><br />
                            </div>
                            <input type="button" value="&lt;&lt;Назад" onclick="return wzStepNextBack('-');" />
                            <input type="submit" value="Далее &gt;&gt;" />
                        </fieldset>
                    </form>
                </div>

                <!-- Tab 10 -->
                <div class="wz_tab_content">
                    <h3>Пароль</h3>

                    <form id="finish" action="" method="post" class="wz_frm" onsubmit="return false;">
                        <fieldset>
                            <input type="hidden" name="step_name" value="finish" />

                            <div class="fld_holder">
                                <label for="wz_password">Пароль (слово <b>на русском языке</b>  – для идентификации Вашей личности при телефонных обращениях в Call-центр Банка) <span class="wz_req_fld">*</span></label><br />
                                <input name="wz_password" id="wz_password" value="" maxlength="255" onblur="dValidator.catchOnChange(this, null, 2);" /><br /><br />
                            </div>

                            <div>
                                <div class="fl_l">
                                    <input type="button" value="&lt;&lt;Назад" onclick="return wzStepNextBack('-');" />
                                </div>
                                <input id="btnPrintForm" type="submit" value="Отправить" style="font-weight:bold" />
                                <br class="clr" />
                            </div>

                            <div>
                                <div class="fl_l notification-text-node">&nbsp;</div>
                            </div>
                        </fieldset>
                    </form>
                </div>
            </div>
        </div>
