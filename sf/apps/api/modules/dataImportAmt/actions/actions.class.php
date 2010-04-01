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
            $errors = $this->form->getErrorSchema();

            if (isset($errors['email']) && $errors['email']->getCode() == 'invalid') {
                $code = 2;
                $message = 'User not found';

            } else if (isset($errors['source_uid'])) {
                $code = 5;
                $message = 'id [An operation with same UID already exists]';

            } else {
                $code = 3;
                $message = (string) $errors;
            }
        }

        // Vars
        $this->setVar('code', $code);
        $this->setVar('message', $message);
    }

}
