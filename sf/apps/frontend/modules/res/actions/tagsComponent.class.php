<?php
/**
 * Готовит js объект res.tags и res.cloud
 *      (теги и облако тегов пользователя)
 */
class tagsComponent extends sfComponent
{

    /**
     * Execute
     *
     * @param sfRequest $request A request object
     */
    public function execute($request)
    {
        $user = $this->getUser()->getUserRecord();

        $result = Doctrine::getTable('Tag')
            ->queryFindUniqueWithCountByUser($user)
            ->fetchArray();

        $this->setVar('data', $result, $noEscape = true);
    }

}
