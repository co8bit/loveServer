<?php
class TargetModel extends Model {

	private $targetId = 0;
	
	public function init($targetId)//初始化，传入
	{
		$this->targetId = $targetId;
	}
	
	public function getTarget()
	{
		$tmp = $this->where("targetId=".$this->targetId)->select();
		return $tmp[0];
	}
}
?>