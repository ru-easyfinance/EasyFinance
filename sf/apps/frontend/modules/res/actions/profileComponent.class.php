<?php
/**
 * Готовит js объект res.profile
 */
class profileComponent extends sfComponent
{
    /**
     * Execute
     *
     * @param sfRequest $request A request object
     */
    public function execute($request)
    {
        $user = $this->getUser()->getUserRecord();

        $profile['integration'] = array(
            'email'   => $user->getUserServiceMail(),
            'account' => Doctrine::getTable('Account')
                ->findLinkedWithSource($user->getId(), 'amt')
        );

        $this->setVar('profile', $profile, $noEscape = true);
    }

}
