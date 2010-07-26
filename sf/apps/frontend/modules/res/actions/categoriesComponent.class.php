<?php
/**
 * Готовит js объект(ы) res.category.*
 */
class categoriesComponent extends sfComponent
{

    /**
     * Execute
     *
     * @param sfRequest $request A request object
     */
    public function execute($request)
    {
        $user = $this->getUser()->getUserRecord();

        // выбираем категории:
        //      1) системные
        $system = Doctrine::getTable('SystemCategory')
            ->queryFindWithOrderByName()
            ->fetchArray();

        //      2) пользовательские
        $users = array();

        //      3) последние
        // TODO вынести в настройки: кол-во категорий и мин.частота использования для попадания
        $recent = array();

        $this->setVar('system', $system, $noEscape = true);
        $this->setVar('users',  $users,  $noEscape = true);
        $this->setVar('recent', $recent, $noEscape = true);
    }

}
