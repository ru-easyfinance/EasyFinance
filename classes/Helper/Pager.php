<?php

/**
 * Хелпер для генерации постраничной навигации
 * все методы должны возвращать стандартный массив вида:
 *
 * array(
 *     * => array(
 *         'title'     => Название для вывода (номер страницы, пропуск "..." или след\пред)
 *         'link'    => ссылка
 *         'current' => булев указатель - текущая страница или нет
 *     )
 * )
 *
 */
class Helper_Pager
{
    /**
     * Простейшая реализация с ссылками на следующую и предыдущую страницы
     *
     * @param integer $pagesTotal - общее кол-во страниц
     * @param integer $pageCurrent - номер текущей страницы
     * @param string $linkBegin - ссылка до номера страницы
     * @return array
     */
    public static function generateSimple( $pagesTotal, $pageCurrent, $linkBegin )
    {
        $pagerArray = array();

        for( $page = 1; $page <= $pagesTotal; $page++)
        {
            $pagerArray[ $page ] = array(
                'title'        => $page,
                'link'         => ($page != $pageCurrent)?$linkBegin . $page:false,
                // Это больше для единого интерфейса нежели для необходимости.
                'current'    => ($page == $pageCurrent)?true:false,
            );
        }

        // Ссылки на следующую и предыдущую страницы
        array_unshift( $pagerArray,
            array(
                'title'        => '&larr; предыдущая',
                'link'        => ( $pageCurrent != 1 )?$linkBegin . ($pageCurrent - 1):false,
                'current'    => false,
            )
        );

        $pagerArray[] = array(
            'title'        => 'следующая &rarr;',
            'link'        => ( $pageCurrent < $pagesTotal )?$linkBegin . ($pageCurrent + 1):false,
            'current'    => false,
        );

        return $pagerArray;
    }


    /**
     * Навигация с пропусками
     *
     */
    public static function generateWithPass()
    {

    }
}
