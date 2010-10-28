<?php
/**
 * Помощники для JSON ответов
 *
 * @package    EasyFinance
 * @author     Anton Minin <anton.a.minin@gmail.com>
 */
abstract class myBaseFrontendJsonActions extends sfActions
{
    protected function renderJson($data)
    {
        $this->getResponse()
            ->setHttpHeader('Content-Type','application/json; charset=utf-8');
        return $this->renderText(json_encode($data));
    }

    protected function renderJsonSuccess($message)
    {
        return $this->renderJson(array('result' => array('text' => $message)));
    }

    protected function renderJsonError($message)
    {
        return $this->renderJson(array('error' => array('text' => $message)));
    }
}