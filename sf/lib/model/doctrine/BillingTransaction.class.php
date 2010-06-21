<?php

/**
 * BillingTransaction
 *
 * This class has been auto-generated by the Doctrine ORM Framework
 *
 * @package    EasyFinance
 * @subpackage model
 * @author     EasyFinance
 * @version    SVN: $Id: Builder.php 7380 2010-03-15 21:07:50Z jwage $
 */
class BillingTransaction extends BaseBillingTransaction
{
	public function getServiceName()
	{
		return $this->getService()->getName();
	}

    public function getServiceLink()
    {
        return link_to($this->getService()->getName(), 'services/' . $this->getServiceId() . '/edit' );
    }

    public function getUserName()
    {
        return $this->getUser()->getUserName();
    }
}
