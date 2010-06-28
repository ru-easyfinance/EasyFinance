<?php

class commonActions extends sfActions
{
    /**
     * 404 страница
     */
    public function executeError404()
    {
        $this->setVar('code',    404, $noEsc = true);
        $this->setVar('message', 'Not Found', $noEsc = true);

        $this->setTemplate('error');
        return sfView::ERROR;
    }
}
