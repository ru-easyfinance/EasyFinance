<?php

class dataImportAmtActions extends sfActions
{
    /**
     * Импорт операции
     */
    public function executeImport(sfRequest $request)
    {
        $this->form = new OperationImportAmtForm;

        $this->form->bind($_POST);
        if ($this->form->isValid()) {
            $this->form->save();
            $code = 1;
            $message = 'OK';
        } else {
            if ($this->form['email']->hasError() && $this->form['email']->getError()->getCode() == 'invalid') {
                $code = 2;
                $message = 'User not found';
            } else {
                $code = 3;
                $message = (string) $this->form->getErrorSchema();
            }
        }

        // Vars
        $this->setVar('code', $code);
        $this->setVar('message', $message);
    }

}
