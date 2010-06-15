<?php
require_once dirname(__FILE__).'/../../../bootstrap/all.php';


/**
 * Синхронизация: отдать категории
 */
class api_sync_OutCategoryTest extends myFunctionalTestCase
{
    protected $app = 'api';


    /**
     * Отдать список категорий
     */
    public function testGetOps()
    {
        $cat = $this->helper->makeCategory();

        $this->browser
            ->getAndCheck('sync', 'syncOut', $this->generateUrl('sync_get_modified', array(
                'model'  => 'category',
                'user_id' =>$cat->getUserId(),
                'from'    => $this->_makeDate(-100),
                'to'      => $this->_makeDate(+100),
            )), 200)
            ->with('response')->begin()
                ->checkContains('<recordset type="Category">')
                ->checkElement('record', 1)
                ->checkElement('#'.$cat->getId())
                ->checkElement('record parent_id')
                ->checkElement('record system_id')
                ->checkElement('record name')
                ->checkElement('record type')
                ->checkElement('record updated_at')
                ->checkElement('record updated_at')
            ->end();
    }

}
