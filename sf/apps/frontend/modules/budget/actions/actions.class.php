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
        $this->getResponse()->setHttpHeader('Content-Type','application/json; charset=utf-8');
        $data   = $request->getParameter('data');
        $start  = $request->getParameter('start', date('Y-m-01'));
        $start  = preg_replace("/(\d{2})\.(\d{2})\.(\d{4})/", "$3-$2-$1", $start);
        $userId = $this->getUser()->getUserRecord()->getId();

        $data = json_decode(stripslashes($data));

        if (!is_object($data)) {
            return $this->renderText(json_encode(array('error' => array(
                'text' => 'Ничего не добавлено',
            ))));
        }

        foreach (array('0' => $data->p, '1' => $data->d) 
            as $drainOrProfit => $budgetList)
        {
            if (!is_array($budgetList))
                continue;

            foreach ($budgetList as $budgetLine) {
                foreach ($budgetLine as $categoryId => $budgetValue) {
                    $budgetValue = (float) str_replace(' ', '', $budgetValue);
                    $key = "{$userId}-{$categoryId}-1-{$start}";
                    
                    $budgetCategory = new BudgetCategory();
                    $budgetCategory->setKey($key);
                    echo "123";
                    $budgetCategory->load();
                    echo $budgetCategory->getAmount();
                    die("321");
                    
                    $budgetCategory->fromArray(
                        array(
                            'drain'       => $drainOrProfit,
                            'user_id'     => $userId,
                            'category_id' => $categoryId,
                            'date_start'  => $start,
                            'amount'      => $budgetValue
                        )
                    );
                    
                    $budgetCategory->save();
                }
            }
        }
        
        return $this->renderText(json_encode(array('result' => array(
            'text' => 'Бюджет сохранён',
        ))));
    }
    
    public function executeEdit(sfWebRequest $request)
    {
        $this->getResponse()->setHttpHeader('Content-Type','application/json; charset=utf-8');
        $type       = trim($request->getParameter('type')) == 'd' ? '1' : '0';
        $categoryId = $request->getParameter('id');
        $dateStart  = $request->getParameter('start', date('Y-m-01'));
        $dateStart  = preg_replace("/(\d{2})\.(\d{2})\.(\d{4})/", "$3-$2-$1", $dateStart);
        $value      = (float)$request->getParameter('value');
        $key        = "{$userId}-{$categoryId}-{$type}-{$dateStart}";
        
        $budgetCategory = new BudgetCategory();
        $budgetCategory->setKey($key);
        $budgetCategory->load();
        
        $budgetCategory->fromArray(
            array(
                'drain'       => $drainOrProfit,
                'user_id'     => $userId,
                'category_id' => $categoryId,
                'date_start'  => $start,
                'amount'      => $budgetValue
            )
        );
        
        if ($budgetCategory->save()) {
            return $this->renderText(json_encode(array('result' => array(
                'text' => '',
            ))));
        } else {
            return $this->renderText(json_encode(array('error' => array(
                'text' => 'Ошибка при изменении бюджета',
            ))));
        }
    }
    
    public function executeDelete()
    {
        $this->getResponse()->setHttpHeader('Content-Type','application/json; charset=utf-8');
        $type       = trim($request->getParameter('type')) == 'd' ? '1' : '0';
        $categoryId = $request->getParameter('id');
        $dateStart  = $request->getParameter('start', date('Y-m-01'));
        $dateStart  = preg_replace("/(\d{2})\.(\d{2})\.(\d{4})/", "$3-$2-$1", $dateStart);
        $key        = "{$userId}-{$categoryId}-{$type}-{$dateStart}";
        
        $budgetCategory = new BudgetCategory();
        
        if ($budgetCategory->remove($key)) {
            return $this->renderText(json_encode(array('result' => array(
                'text' => '',
            ))));
        } else {
            return $this->renderText(json_encode(array('error' => array(
                'text' => 'Ошибка при удалении бюджета',
            ))));
        }
    }
}
?>