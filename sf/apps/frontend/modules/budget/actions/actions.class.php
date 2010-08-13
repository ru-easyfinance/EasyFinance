<?php
/**
 * Бюджет
 */
class budgetActions extends sfActions
{
    public function executeIndex(sfWebRequest $request)
    {
        return sfView::SUCCESS;
    }

    /**
     * Отдаёт бюджет в виде JSON списка
     * @param $request
     */
    public function executeLoad(sfWebRequest $request)
    {
        $start = $request->getParameter('start', date('Y-m-01'));
        $vars  = array(
            'start' => $start,
            'returnJSON' => '1'
        );

        $this->getResponse()->setHttpHeader('Content-Type','application/json; charset=utf-8');

        return $this->renderComponent('res', 'budget', $vars);
    }

    /**
     * Добавляет в записи в бюджет
     * На входе JSON список статей бюджета и дата начала месяца
     * @param $request
     */
    public function executeAdd(sfWebRequest $request)
    {
        $data   = $request->getParameter('data');
        $start  = $request->getParameter('start', date('Y-m-01'));
        $userId = $this->getUser()->getUserRecord()->getId();

        $data = json_decode(stripslashes($data));

        if (!is_object($data)) {
            return $this->renderJson(array('error' => array(
                'text' => 'Ничего не добавлено',
            )));
        }

        foreach (array('0' => $data->p, '1' => $data->d)
            as $drainOrProfit => $budgetList)
        {
            if (!is_array($budgetList))
                continue;

            foreach ($budgetList as $budgetLine) {
                foreach ($budgetLine as $categoryId => $budgetValue) {
                    $budgetValue = (float) str_replace(' ', '', $budgetValue);
                    $key = "{$userId}-{$categoryId}-{$drainOrProfit}-{$start}";
                    // Коль скоро Doctrine_Connection_Mysql::replace() fails
                    // пляшем с бубном
                    $budgetCategory = Doctrine::getTable('BudgetCategory')
                        ->find($key);

                    $budgetCategory = $budgetCategory === false ?
                        new BudgetCategory() : $budgetCategory ;

                    $budgetCategory->fromArray(
                        array(
                            'drain'       => $drainOrProfit,
                            'user_id'     => $userId,
                            'category_id' => $categoryId,
                            'date_start'  => $start,
                            'amount'      => $budgetValue
                        )
                    );

                    if (!$budgetCategory->getKey()) {
                        $budgetCategory->setKey($key);
                    }

                    $budgetCategory->save();
                }
            }
        }

        return $this->renderJson(array('result' => array(
            'text' => 'Бюджет сохранён',
        )));
    }

    public function executeEdit(sfWebRequest $request)
    {
        $this->form     = new BudgetCategoryEditForm();
        $getParameters  = 'get' . ucfirst(strtolower($request->getMethod()));
        $getParameters .= 'Parameters';

        $this->form->bind($request->$getParameters());
        $budgetCategory = $this->form->getObject();

        $budgetCategory->setAmount($this->form->getValue('value'));
        $budgetCategory->save();

        if (!$budgetCategory->isModified()) {
            return $this->renderJson(array('result' => array(
                'text' => '',
            )));
        } else {
            return $this->renderJson(array('error' => array(
                'text' => 'Ошибка при изменении бюджета',
            )));
        }
    }

    public function executeDelete(sfWebRequest $request)
    {
        $this->form     = new BudgetCategoryEditForm();
        $getParameters  = 'get' . ucfirst(strtolower($request->getMethod()));
        $getParameters .= 'Parameters';

        $this->form->bind($request->$getParameters());
        $budgetCategory = $this->form->getObject();

        if ($budgetCategory && $budgetCategory->delete()) {
            return $this->renderJson(array('result' => array(
                'text' => '',
            )));
        } else {
            return $this->renderJson(array('error' => array(
                'text' => 'Ошибка при удалении бюджета',
            )));
        }
    }

    protected function renderJson($data) {
        $this->getResponse()->setHttpHeader('Content-Type','application/json; charset=utf-8');
        return $this->renderText(json_encode($data));
    }
}
?>