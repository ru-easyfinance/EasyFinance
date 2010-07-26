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
        $users = Doctrine::getTable('Category')
            ->queryFindWithUseCount($user)
            ->fetchArray();

        $this->setVar('system', $system, $noEscape = true);
        $this->setVar('users',  $users,  $noEscape = true);
    }

}
