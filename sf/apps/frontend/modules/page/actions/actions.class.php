<?php

/**
 * Page actions.
 *
 * @package    EasyFinance
 * @subpackage Page
 * @author     EasyFinance
 */
class pageActions extends sfActions
{
    /**
     * Показываем статические страницы
     *
     * @param sfRequest $request A request object
     */
    public function executeIndex(sfWebRequest $request)
    {
        $pageName = $request->getParameter('pageName');

        $templateDir = $this->context->getConfiguration()
            ->getTemplateDir($this->moduleName, $this->getTemplate());

        if (!file_exists(sprintf('%s/_%s.php', $templateDir, $pageName))) {
            $this->forward404('Not Found');
        }

        $this->setVar('pageName', $pageName);
        $this->setLayout('layoutClear');
        return sfView::SUCCESS;
    }
}