<?php
class DiaryModel extends Model {

	private $pairId = 0;
	
	//自动填充
	protected $_auto = array (
			array('diaryTime','mydate',Model:: MODEL_BOTH,'callback'),
	);
	
	protected function mydate()
	{
		return date("Y-m-d H:i:s");
	}
	
	public function init()//初始化，传入
	{
	}
	
public function getDiaryInfo($diaryIdList)//从一个一维数组中获取diaryId，然后返回diaryId的详细信息
	//不需要init
	{
		$info = NULL;
		for ($i = 0; $i < count($diaryIdList); $i++)
		{
			$tmp = $this->where("diaryId=".$diaryIdList[$i])->select();
			$info[$i] = $tmp[0];
		}
		return $info;//info[i]["content"] = content
	}
}
?>