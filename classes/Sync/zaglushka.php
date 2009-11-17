<?php
$xmlRequest='<?xml version="1.0" encoding="UTF-8"?>
<methodCall>
    <!--
        Схема данных для синхронизации между easyfinance.ru и keepsoft.ru

        Данные отсылаемые **из программы keepsoft.ru**
    -->
    <methodName>EasyFinSync</methodName>
    <params>
        <!-- Блок авторизации
            login        - (только буквы и цифры, без пробелов. Максимум 50 символов) логин пользователя в системе easyfinance.ru
            pass         - хэш пароля в SHA-1
            lastsync     - дата последней синхронизации (dateTime.iso8601)
            digsignature - цифровая подпись клиента
        -->
        <param>
            <struct>
                <member>
                    <name>type</name>
                    <value>service</value>
                </member>
                <member>
                    <name>name</name>
                    <value>auth</value>
                </member>
                <member>
                    <name>login</name>
                    <value>challenger3</value>
                </member>
                <member>
                    <name>pass</name>
                    <value>b1b3773a05c0ed0176787a4f1574ff0075f7521e</value>
                </member>
                <member>
                    <name>lastsync</name>
                    <value>20091003T10:11:12
                                </value>
                </member>
                <member>
                    <name>digsignature</name>
                    <value>n2jdy303yeer7j2v</value>
                </member>
            </struct>
        </param>

    <!-- Блок с системными данными

        RecordsMap
        Выводит все присвоенные в этой сессии номера ключей

        tablename - Имя таблицы на английском языке, без пробелов
        remotekey - ключ keepsoft.ru
        ekey - ключ easyfinance.ru
    -->

        <param>
            <struct>
                <member>
                    <name>type</name>
                    <value>service</value>
                </member>
                <member>
                    <name>name</name>
                    <value>RecordsMap</value>
                </member>
            </struct>
            <array>
                <data>
                    <value>
                        <struct>
                            <member>
                                <name>tablename</name>
                                <value>Accounts</value>
                            </member>
                            <member>
                                <name>remotekey</name>
                                <value>
                                    <int>4</int>
                                </value>
                            </member>
                        </struct>
                    </value>
                    <value>
                        <struct>
                            <member>
                                <name>tablename</name>
                                <value>Accounts</value>
                            </member>
                            <member>
                                <name>remotekey</name>
                                <value>
                                    <i4>2</i4>
                                </value>
                            </member>
                        </struct>
                    </value>
                    <value>
                        <struct>
                            <member>
                                <name>tablename</name>
                                <value>Categories</value>
                            </member>
                            <member>
                                <name>remotekey</name>
                                <value>
                                    <i4>2</i4>
                                </value>
                            </member>
                        </struct>
                    </value>
                    <value>
                        <struct>
                            <member>
                                <name>tablename</name>
                                <value>Categories</value>
                            </member>
                            <member>
                                <name>remotekey</name>
                                <value>
                                    <i4>1</i4>
                                </value>
                            </member>
                        </struct>
                    </value>
                    <value>
                        <struct>
                            <member>
                                <name>tablename</name>
                                <value>Incomes</value>
                            </member>
                            <member>
                                <name>remotekey</name>
                                <value>
                                    <i4>3</i4>
                                </value>
                            </member>
                        </struct>
                    </value>
                    <value>
                        <struct>
                            <member>
                                <name>tablename</name>
                                <value>Transfers</value>
                            </member>
                            <member>
                                <name>remotekey</name>
                                <value>
                                    <i4>5</i4>
                                </value>
                            </member>
                        </struct>
                    </value>
                </data>
            </array>
        </param>


    <!-- Удалённые записи
         tablename - Имя таблицы на английском языке, без пробелов.
         remotekey - ключ keepsoft.ru
    -->
        <param>
            <struct>
                <member>
                    <name>type</name>
                    <value>service</value>
                </member>
                <member>
                    <name>name</name>
                    <value>DeletedRecords</value>
                </member>
            </struct>
            <array>
                <data>
                    <value>
                        <struct>
                            <member>
                                <name>tablename</name>
                                <value>Accounts</value>
                            </member>
                            <member>
                                <name>remotekey</name>
                                <value>
                                    <i4>4</i4>
                                </value>
                            </member>
                        </struct>
                    </value>
                    <value>
                        <struct>
                            <member>
                                <name>tablename</name>
                                <value>Categories</value>
                            </member>
                            <member>
                                <name>remotekey</name>
                                <value>
                                    <i4>2</i4>
                                </value>
                            </member>
                        </struct>
                    </value>
                    <value>
                        <struct>
                            <member>
                                <name>tablename</name>
                                <value>Transfers</value>
                            </member>
                            <member>
                                <name>remotekey</name>
                                <value>
                                    <i4>5</i4>
                                </value>
                            </member>
                        </struct>
                    </value>
                </data>
            </array>
        </param>


    <!-- Изменённые записи
         tablename - Имя таблицы на английском языке, без пробелов.
         remotekey - ключ keepsoft.ru
    -->
        <param>
            <struct>
                <member>
                    <name>type</name>
                    <value>service</value>
                </member>
                <member>
                    <name>name</name>
                    <value>ChangedRecords</value>
                </member>
            </struct>
            <array>
                <data>
                    <value>
                        <struct>
                            <member>
                                <name>tablename</name>
                                <value>Accounts</value>
                            </member>
                            <member>
                                <name>remotekey</name>
                                <value><i4>4</i4></value>
                            </member>
                        </struct>
                    </value>
                    <value>
                        <struct>
                            <member>
                                <name>tablename</name>
                                <value>Accounts</value>
                            </member>
                            <member>
                                <name>remotekey</name>
                                <value><i4>2</i4></value>
                            </member>
                        </struct>
                    </value>
                    <value>
                        <struct>
                            <member>
                                <name>tablename</name>
                                <value>Categories</value>
                            </member>
                            <member>
                                <name>remotekey</name>
                                <value><i4>1</i4></value>
                            </member>
                        </struct>
                    </value>
                    <value>
                        <struct>
                            <member>
                                <name>tablename</name>
                                <value>Incomes</value>
                            </member>
                            <member>
                                <name>remotekey</name>
                                <value><i4>3</i4></value>
                            </member>
                        </struct>
                    </value>
                    <value>
                        <struct>
                            <member>
                                <name>tablename</name>
                                <value>Transfers</value>
                            </member>
                            <member>
                                <name>remotekey</name>
                                <value><i4>5</i4></value>
                            </member>
                        </struct>
                    </value>
                </data>
            </array>
        </param>



	<!-- Модули -->
        <!-- Счета
            remotekey - идентификатор
            name - имя счёта
            cur - идентификатор валюты
            date - дата изменения начального баланса
            startbalance - начальный баланс
            descr - комментарий
        -->
        <param>
            <struct>
                <member>
                    <name>tablename</name>
                    <value>
                        <string>Accounts</string>
                    </value>
                </member>
            </struct>
            <array>
                <data>
                    <value>
                        <struct>
                            <member>
                                <name>remotekey</name>
                                <value>
                                    <i4>2</i4>
                                </value>
                            </member>
                            <member>
                                <name>name</name>
                                <value>Нали2ч</value>
                            </member>
                            <member>
                                <name>cur</name>
                                <value>
                                    <i4>1</i4>
                                </value>
                            </member>
                            <member>
                                <name>date</name>
                                <value>20091029T10:10:10
                                </value>
                            </member>
                            <member>
                                <name>startbalance</name>
                                <value>
                                    <double>5000</double>
                                </value>
                            </member>
                            <member>
                                <name>descr</name>
                                <value>Мои деньги</value>
                            </member>
                        </struct>
                    </value>
                    <value>
                        <struct>
                            <member>
                                <name>remotekey</name>
                                <value>
                                    <i4>1</i4>
                                </value>
                            </member>
                            <member>
                                <name>name</name>
                                <value>Наличные</value>
                            </member>
                            <member>
                                <name>cur</name>
                                <value>
                                    <i4>1</i4>
                                </value>
                            </member>
                            <member>
                                <name>date</name>
                                <value>20091029T10:10:10
                                </value>
                            </member>
                            <member>
                                <name>startbalance</name>
                                <value>
                                    <double>6000</double>
                                </value>
                            </member>
                            <member>
                                <name>descr</name>
                                <value>Ещё деньги</value>
                            </member>
                        </struct>
                    </value>
                    <value>
                        <struct>
                            <member>
                                <name>remotekey</name>
                                <value>
                                    <i4>4</i4>
                                </value>
                            </member>
                            <member>
                                <name>name</name>
                                <value>Наличник</value>
                            </member>
                            <member>
                                <name>cur</name>
                                <value>
                                    <i4>1</i4>
                                </value>
                            </member>
                            <member>
                                <name>date</name>
                                <value>20091029T10:10:10
                                </value>
                            </member>
                            <member>
                                <name>startbalance</name>
                                <value>
                                    <double>4200
                                    </double>
                                </value>
                            </member>
                            <member>
                                <name>descr</name>
                                <value>Изменено123</value>
                            </member>
                        </struct>
                    </value>
                </data>
            </array>
        </param>



        <!-- Перевод со счёта на счёт
            remotekey - идентификатор
            date - дата перевода
            acfrom - идентификатор счёта, с которого осуществляем перевод
            amount - сумма денег
            acto - идентификатор счёта, на который переводим
            descr - комментарий
        -->
        <param>
            <struct>
                <member>
                    <name>tablename</name>
                    <value>
                        <string>Transfers</string>
                    </value>
                </member>
            </struct>
            <array>
                <data>
                    <value>
                        <struct>
                            <member>
                                <name>remotekey</name>
                                <value>
                                    <i4>5</i4>
                                </value>
                            </member>
                            <member>
                                <name>date</name>
                                <value><dateTime.iso8601>20091029T10:10:10
                                </dateTime.iso8601>
                                </value>
                            </member>
                            <member>
                                <name>acfrom</name>
                                <value>
                                    <i4>2</i4>
                                </value>
                            </member>
                            <member>
                                <name>amount</name>
                                <value>
                                    <double>1000</double>
                                </value>
                            </member>
                            <member>
                                <name>acto</name>
                                <value>
                                    <i4>4</i4>
                                </value>
                            </member>
                            <member>
                                <name>descr</name>
                                <value>Перевёл</value>
                            </member>
                        </struct>
                    </value>
                </data>
            </array>
        </param>



        <!-- Категории
            remotekey - идентификатор
            name - имя категории
            parent - идентификатор родительской категории
		-->
        <param>
            <struct>
                <member>
                    <name>tablename</name>
                    <value>
                        <string>Categories</string>
                    </value>
                </member>
            </struct>
            <array>
                <data>
                    <value>
                        <struct>
                            <member>
                                <name>remotekey</name>
                                <value>
                                    <i4>1</i4>
                                </value>
                            </member>
                            <member>
                                <name>name</name>
                                <value>Образование2</value>
                            </member>
                            <member>
                                <name>parent</name>
                                <value>
                                    <i4>0</i4>
                                </value>
                            </member>
                        </struct>
                    </value>
                    <value>
                        <struct>
                            <member>
                                <name>remotekey</name>
                                <value>
                                    <i4>2</i4>
                                </value>
                            </member>
                            <member>
                                <name>name</name>
                                <value>ВУЗ</value>
                            </member>
                            <member>
                                <name>parent</name>
                                <value>
                                    <i4>1</i4>
                                </value>
                            </member>
                        </struct>
                    </value>
                </data>
            </array>
        </param>



        <!-- Валюты
            remotekey - идентификатор
            name - название валюты
        -->
        <param>
            <struct>
                <member>
                    <name>tablename</name>
                    <value>
                        <string>Currensies</string>
                    </value>
                </member>
            </struct>
            <array>
                <data>
                    <value>
                        <struct>
                            <member>
                                <name>remotekey</name>
                                <value>1</value>
                            </member>
                            <member>
                                <name>name</name>
                                <value>руб.</value>
                            </member>
                        </struct>
                    </value>
                    <value>
                        <struct>
                            <member>
                                <name>remotekey</name>
                                <value>2</value>
                            </member>
                            <member>
                                <name>name</name>
                                <value>евро</value>
                            </member>
                        </struct>
                    </value>
                </data>
            </array>
        </param>


        <!-- Долги
            remotekey - идентификатор
            amount - сумма долга
            currency - валюта долга
            name - название долга
            date - дата долга
            done - сумма погашённая
		-->
        <param>
            <struct>
                <member>
                    <name>tablename</name>
                    <value>
                        <string>Debets</string>
                    </value>
                </member>
            </struct>
            <array>
                <data>
                    <value>
                        <struct>
                            <member>
                                <name>remotekey</name>
                                <value>
                                    <i4>1</i4>
                                </value>
                            </member>
                            <member>
                                <name>amount</name>
                                <value>
                                    <double>3000</double>
                                </value>
                            </member>
                            <member>
                                <name>currency</name>
                                <value>
                                    <i4>1</i4>
                                </value>
                            </member>
                            <member>
                                <name>name</name>
                                <value>Одолжил у друга</value>
                            </member>
                            <member>
                                <name>date</name>
                                <value>
                                    <dateTime.iso8601></dateTime.iso8601>
                                </value>
                            </member>
                            <member>
                                <name>done</name>
                                <value>
                                    <double>1000</double>
                                </value>
                            </member>
                        </struct>
                    </value>
                </data>
            </array>
        </param>


        <!-- Доходы
            remotekey - идентификатор
            date - дата
            category - идентификатор категории
            parent  - идентификатор родительской категории
            account - идентификатор счёта
            amount - сумма дохода
            descr - комментарий
        -->
        <param>
            <struct>
                <member>
                    <name>tablename</name>
                    <value>
                        <string>Incomes</string>
                    </value>
                </member>
            </struct>
            <array>
                <data>
                    <value>
                        <struct>
                            <member>
                                <name>remotekey</name>
                                <value>
                                    <i4>3</i4>
                                </value>
                            </member>
                            <member>
                                <name>date</name>
                                <value>20091029T10:10:10
                                </value>
                            </member>
                            <member>
                                <name>category</name>
                                <value>
                                    <i4>2</i4>
                                </value>
                            </member>
                            <member>
                                <name>parent</name>
                                <value>
                                    <i4>1</i4>
                                </value>
                            </member>
                            <member>
                                <name>account</name>
                                <value>
                                    <i4>4</i4>
                                </value>
                            </member>
                            <member>
                                <name>amount</name>
                                <value>
                                    <double>800</double>
                                </value>
                            </member>
                            <member>
                                <name>descr</name>
                                <value>Стипа</value>
                            </member>
                        </struct>
                    </value>
                    <value>
                        <struct>
                            <member>
                                <name>remotekey</name>
                                <value>
                                    <i4>3</i4>
                                </value>
                            </member>
                            <member>
                                <name>date</name>
                                <value>20091029T10:10:10
                                </value>
                            </member>
                            <member>
                                <name>category</name>
                                <value>
                                    <i4>2</i4>
                                </value>
                            </member>
                            <member>
                                <name>parent</name>
                                <value>
                                    <i4>1</i4>
                                </value>
                            </member>
                            <member>
                                <name>account</name>
                                <value>
                                    <i4>4</i4>
                                </value>
                            </member>
                            <member>
                                <name>amount</name>
                                <value>
                                    <double>1800</double>
                                </value>
                            </member>
                            <member>
                                <name>descr</name>
                                <value>Стипа</value>
                            </member>
                        </struct>
                    </value>
                </data>
            </array>
        </param>


        <!-- Расходы
            remotekey - идентификатор
            date - дата
            category - идентификатор категории
            parent  - идентификаторо родительской категории
            account - идентификатор счёта
            amount - сумма расхода
            descr - комментарий
        -->
        <param>
            <struct>
                <member>
                    <name>tablename</name>
                    <value>
                        <string>Outcomes</string>
                    </value>
                </member>
            </struct>
            <array>
                <data>
                    <value>
                        <struct>
                            <member>
                                <name>remotekey</name>
                                <value>
                                    <i4>1</i4>
                                </value>
                            </member>
                            <member>
                                <name>date</name>
                                <value>
                                    <dateTime.iso8601>19.10.2009</dateTime.iso8601>
                                </value>
                            </member>
                            <member>
                                <name>category</name>
                                <value>
                                    <i4>2</i4>
                                </value>
                            </member>
                            <member>
                                <name>parent</name>
                                <value>
                                    <i4>1</i4>
                                </value>
                            </member>
                            <member>
                                <name>account</name>
                                <value>
                                    <i4>2</i4>
                                </value>
                            </member>
                            <member>
                                <name>amount</name>
                                <value>
                                    <double>2000</double>
                                </value>
                            </member>
                            <member>
                                <name>descr</name>
                                <value>Зачёт</value>
                            </member>
                        </struct>
                    </value>
                </data>
            </array>
        </param>


        <!-- Планирование
            remotekey - идентификатор
            date - дата
            period - период в сутках
            category - идентификатор категории
            parent  - идентификаторо родительской категории
            account - идентификатор счёта
            amount - сумма
            descr - комментарий
		-->
        <param>
            <struct>
                <member>
                    <name>tablename</name>
                    <value>
                        <string>Plans</string>
                    </value>
                </member>
            </struct>
            <array>
                <data>
                    <value>
                        <struct>
                            <member>
                                <name>name</name>
                                <value>Plan</value>
                            </member>
                            <member>
                                <name>remotekey</name>
                                <value>
                                    <i4>1</i4>
                                </value>
                            </member>
                            <member>
                                <name>date</name>
                                <value>20091030T10:10:10
                                </value>
                            </member>
                            <member>
                                <name>period</name>
                                <value>
                                    <i4>7</i4>
                                </value>
                            </member>
                            <member>
                                <name>category</name>
                                <value>
                                    <i4>2</i4>
                                </value>
                            </member>
                            <member>
                                <name>parent</name>
                                <value>
                                    <i4>1</i4>
                                </value>
                            </member>
                            <member>
                                <name>account</name>
                                <value>
                                    <i4>1</i4>
                                </value>
                            </member>
                            <member>
                                <name>amount</name>
                                <value>
                                    <double>200</double>
                                </value>
                            </member>
                            <member>
                                <name>descr</name>
                                <value>Обед</value>
                            </member>
                        </struct>
                    </value>
                </data>
            </array>
        </param>
	<!--Модули -->

    </params>
</methodCall>';
//require ("model.php");
//require ("../Account/Model.php");