<?php
/**
 * Готовит js объект res.informers
 */
class tahometerComponent extends sfComponent
{

    /**
     * Execute
     *
     * @param sfRequest $request A request object
     */
    public function execute($request)
    {
        $user = $this->getUser()->getUserRecord();

        $tahometers = new myTahometers($user);

        $this->setVar('data', $tahometers->toArray(), $noEscape = true);
    }

}
