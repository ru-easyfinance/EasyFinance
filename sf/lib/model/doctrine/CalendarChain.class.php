<?php

/**
 * CalendarChain
 *
 * This class has been auto-generated by the Doctrine ORM Framework
 *
 * @package    EasyFinance
 * @subpackage model
 * @author     EasyFinance
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class CalendarChain extends BaseCalendarChain
{
    /**
     * Периодичность создания операций
     */
    const REPEAT_NO             = 0;   // Не повторять
    const REPEAT_EVERY_DAY      = 1;   // Ежедневно
    const REPEAT_EVERY_WEEK     = 7;   // Еженедельно
    const REPEAT_EVERY_MONTH    = 30;  // Ежемесячно
    const REPEAT_EVERY_QUARTER  = 90;  // Ежеквартально
    const REPEAT_EVERY_YEAR     = 365; // Ежегодно


}