<?php
class PairModel extends Model {
	
	private $pairId = 0;
	private $money = 0;
	private $billOrignContent = "";
	private $billContent = NULL;//billContent[i] = billId
	protected $lowId = -1;
	private $diaryIdOrignList = NULL;
	private $diaryIdList = NULL;//diaryId[i] = diaryId
	
	public function init($pairId)
	{
		$this->pairId = $pairId;
		$this->money = $this->getMoney();
	}
	
	public function getUserLowId()
	{
		if ($this->lowId === -1)
		{
			$result = NULL;
			$result = $this->where("pairId=$this->pairId")->select();
			if (!$result)
				return -1;
			$this->lowId = $result[0]["lowId"];
		}
		return $this->lowId;
	}
	
	public function getMoney()
	{
		$result = $this->where("pairId=".$this->pairId)->select();
		if (!$result)
			return false;
		return $this->money = $result[0]["money"];
	}
	
	public function updateLowId($pairId,$lowId)//更新pair的lowId
	{
		$data = NULL;
		$data["pairId"] = $pairId;
		$data["lowId"] = $lowId;
		return $this->save($data);
	}
	
	public function getOriginBillContent()//得到原始内容
	{
		if ($this->billOrignContent === "")
		{
			$result = NULL;
			$result = $this->where("pairId=$this->pairId")->select();
			if (!$result)
				return -1;
			$this->billOrignContent = $result[0]["billContent"];
		}
		return $this->billOrignContent;
	}
	
	public function getBillContent()
	{
		if ($this->billContent === NULL)
		{
			//得到原样
			$this->getOriginBillContent();
	
			//解析content
			$st = 0;
			$count = 0;
			$contentLen = strlen($this->billOrignContent);
			while ($st < $contentLen)
			{
				$breakPoint = strpos($this->billOrignContent,_SPECAL_BILL_END_FLAG,$st);
				if (!$breakPoint)//到字符串最后一个内容了
				{
					$this->billContent[$count] = substr($this->billOrignContent,$st);
					break;
				}
				$this->billContent[$count] = substr($this->billOrignContent,$st,$breakPoint - $st);
				$count++;
				$st = $breakPoint + _SPECAL_BILL_END_FLAG_STRLEN;
			}
		}
	
		return $this->billContent;
	}
	
	public function updateBillArray($isAdd,$billId,$changeMoney)//更新pair的账单id数组
	/*
	 * 参数是：isAdd=true说明是加分账单，一个billId
	*/
	{
		$this->getOriginBillContent();
	
		if ($this->billOrignContent == "")
			$this->billOrignContent = "$billId";
		else
			$this->billOrignContent = $this->billOrignContent . _SPECAL_BILL_END_FLAG . $billId;
	
		$data = NULL;
		$data["pairId"] = $this->pairId;
		$data["billContent"] = $this->billOrignContent;
		if ($isAdd)
			$data["money"] = $this->money + $changeMoney;
		else
			$data["money"] = $this->money - $changeMoney;
		return $this->save($data);
	}
	
	public function getDiaryIdOriginList()//得到原始内容
	{
		if ($this->diaryIdOrignList === NULL)
		{
			$result = NULL;
			$result = $this->where("pairId=$this->pairId")->select();
			if (!$result)
				return -1;
			$this->diaryIdOrignList = $result[0]["diaryIdList"];
		}
		return $this->diaryIdOrignList;
	}
	
	public function getDiaryIdList()
	{
		if ($this->diaryIdOrignList === NULL)
		{
			//得到原样
			$this->getDiaryIdOriginList();
	
			//解析content
			$st = 0;
			$count = 0;
			$contentLen = strlen($this->diaryIdOrignList);
			while ($st < $contentLen)
			{
				$breakPoint = strpos($this->diaryIdOrignList,_SPECAL_DIARY_END_FLAG,$st);
				if (!$breakPoint)//到字符串最后一个内容了
				{
					$this->diaryIdList[$count] = substr($this->diaryIdOrignList,$st);
					break;
				}
				$this->diaryIdList[$count] = substr($this->diaryIdOrignList,$st,$breakPoint - $st);
				$count++;
				$st = $breakPoint + _SPECAL_DIARY_END_FLAG_STRLEN;
			}
		}
	
		return $this->diaryIdList;
	}
	
	public function insertDiaryId($newDiaryId)
	{
		$this->getDiaryIdOriginList();
		
		if ($this->diaryIdOrignList == "")
			$this->diaryIdOrignList = "$newDiaryId";
		else
			$this->diaryIdOrignList = $this->diaryIdOrignList . _SPECAL_BILL_END_FLAG . $newDiaryId;
		
		$data = NULL;
		$data["pairId"] = $this->pairId;
		$data["diaryIdList"] = $this->diaryIdOrignList;
		return $this->save($data);
	}
	
	public function getAllInfoFromBillIdList()//得到完整的记录信息
	{
		$billIdList = $this->getBillContent();
		$dbBill = D("Bill");
		return $dbBill->getBillInfo($billIdList);
	}
	
	public function getAllInfoFromDiaryIdList()//得到完整的记录信息
	{
		$diaryIdList = $this->getDiaryIdList();
		$dbDiary = D("Diary");
		return $dbDiary->getDiaryInfo($diaryIdList);
	}
	
	public function insertTarget($targetId)
	{
		$data["pairId"] = $this->pairId;
		$data["targetId"] = $targetId;
		return $this->save($data);
	}
	
	public function getTargetId()
	{
		$tmp = $this->where("pairId=".$this->pairId)->select();
		return $tmp[0]["targetId"];
	}
}
?>