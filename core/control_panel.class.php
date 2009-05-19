<?php

require_once("/www/homemone/www/core/CourseResourses/course_resourses.class.php");

class DragMetDynamic {
    public $fromDate;
    public $ToDate;
}

class ControlPanel
{
	protected $cr; //список ресурсов
	protected $checkErrors = array();
	protected $checkSuccessfully;
	
	public function ControlPanel()
	{		
		$this->_init();	
	}
	
	private function _init()
	{
		$this->_checkErrors();
		$count = count($this->checkErrors);

		for ($i=0; $i<$count; $i++)
		{
			switch ($this->checkErrors[$i])
			{
				case "none":
					$this->_checkSuccessfully();
					break;
			}
		}
		
		if ($this->checkSuccessfully == 'none')
		{
			$this->_runGetResourses();
		}
	}
	
	private function _checkErrors()
	{
		$this->checkErrors[] = "none";
	}
	
	private function _checkSuccessfully()
	{
		$this->checkSuccessfully = "none";
	}
	
	private function _runGetResourses()
	{
		$this->cr = new CourseResourses();
		//filesize($this->cr->resourses[0][0]);
		//$handle = fopen($this->cr->resourses[0][0], "r");
		//$contents = fread($handle, filesize($this->cr->resourses[0][0]));
		//fclose($handle);
		$params = new DragMetDynamic ();
		$params->fromDate = date("Y-m-d")."T00:00:00+04:00";
		$params->ToDate = date("Y-m-d")."T00:00:00+04:00";
		try {
				$client   = new SoapClient($this->cr->resourses[0][0]);
				$result = $client->__getFunctions();
				
				echo "<pre>";
				print_r($result);
				echo "</pre>";
				  
				$result = $client->DragMetDynamic($params);
				
				echo "<pre>";
				print_r($result);
				echo "</pre>";
			
			} catch (SoapFault $fault) {
				trigger_error("SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})", E_USER_ERROR);
			}
	}
}
?>