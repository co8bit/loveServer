<?php
include(CONF_PATH."MyConfigINI.php");
include(LIB_PATH."CommonAction.php");

class PublicAction extends CommonAction
{
	private function index()
	{
	}
	
	public function NoHeader()
	{
		$this->assign('View_SOFTNAME',_SOFTNAME);
		$this->assign('View_VERSION',_VERSION);
		$this->display();
	}
	
	public function NoFooter()
	{
		$this->assign('View_SOFTNAME',_SOFTNAME);
		$this->assign('View_VERSION',_VERSION);
		$this->display();
	}
	
	public function footer()
	{
		$this->assign('View_SOFTNAME',_SOFTNAME);
		$this->assign('View_VERSION',_VERSION);
		$this->display();
	}
	
	public function header()
	{
		$this->assign('View_SOFTNAME',_SOFTNAME);
		$this->assign('View_VERSION',_VERSION);
		$this->display();
	}
}