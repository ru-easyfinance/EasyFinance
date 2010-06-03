<?php

/**
 * Интерфейс для классов, подключаемых как хук к роутеру,
 * для реализации кастомной логики
 *
 */
interface _Core_Router_iHook
{
    /**
     * Метод хук, вызываемый роутером после разбора запроса,
     * и до вызова комманды ($class->$method( $chunks ))
     *
     * @todo добавить хукам возможность рулить шаблонизатором
     *
     * @param _Core_Request $request Обьект запроса
     * @param string $class Вызываемый класс контроллера (комманды)
     * @param string $method Вызываемый метод контроллера
     * @param array $chunks Массив остатков от разбора uri роутером
     * @param _Core_TemplateEngine $templateEngine обьект шаблонизатора
     */
    public static function execRouterHook( _Core_Request $request, &$class, &$method, array &$chunks, &$templateEngine );
}
