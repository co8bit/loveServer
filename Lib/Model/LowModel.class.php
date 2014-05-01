<?php
class LowModel extends Model {

	private $content = -1;//原样本
	private $score = -1;//原样本
	private $list = NULL;//解析后的content和score。实际上为list[i]["content"],list[i]["score"]
	private $lowId = 1;
	private $outputData = NULL;//准备写回数据库前的数据，写回数据库时应已被添加了中断符。eg:data["content"],data["score"]
	
	public function init($pairId,$lowId = -1)
	{
		if ($lowId === -1)//还没有lowId，则根据pairId查找
		{
			$dbPair = D("Pair");//要对UserModel实例化只能通过D操作
			$dbPair->init($pairId);
			$this->lowId = $dbPair->getUserLowId();
		}
		else
		{
			$this->lowId = $lowId;
		}
		
		$this->outputData["content"] = "";
		$this->outputData["score"] = "";
		$this->outputData["lowId"] = $this->lowId;
	}
	
	public function getOriginContentAndScore()
	{
		$result = NULL;
		$result = $this->where("lowId=$this->lowId")->select();
		if (!$result)//userName不存在
			return -1;
		$this->content = $result[0]["content"];
		$this->score = $result[0]["score"];
	}
	
	public function getOriginContent()
	{
		if ($this->content === -1)
		{
			$this->getOriginContentAndScore();
		}
		return $this->content;
	}
	
	public function getOriginScore()
	{
		if ($this->score === -1)
		{
			$this->getOriginContentAndScore();
		}
		return $this->score;
	}
	
	public function getLowId()
	{
		return $this->lowId;
	}
	
	public function getContentAndScore()
	{
		if ($this->list === NULL)
		{
			//得到原样
			$this->getOriginContentAndScore();
			//echo "<br>inMode:"."cont:".$this->content;echo "score:".$this->score;
			 
			//解析
			$this->analysisContent();
			$this->analysisScore();
		}
		
		return $this->list;
	}
	
	private function analysisContent()//调用前需已获得score和content
	{
		//解析content
		$st = 0;
		$count = 0;
		$contentLen = strlen($this->content);
		while ($st < $contentLen)
		{
			$breakPoint = strpos($this->content,_SPECIAL_END_FLAG,$st);
			if (!$breakPoint)//到字符串最后一个内容了
			{
				$this->list[$count]["content"] = substr($this->content,$st);
				break;
			}
			$this->list[$count]["content"] = substr($this->content,$st,$breakPoint - $st);
			$count++;
			$st = $breakPoint + _SPECIAL_END_FLAG_STRLEN;
		}
	}
	
	private function analysisScore()//调用前需已获得score和content
	{
		//解析score
		$st = 0;
		$count = 0;
		$scoreLen = strlen($this->score);
		while ($st < $scoreLen)
		{
			$breakPoint = strpos($this->score,_SPECIAL_END_FLAG,$st);
			if (!$breakPoint)//到字符串最后一个内容了
			{
				$this->list[$count]["score"] = substr($this->score,$st);
				break;
			}
			$this->list[$count]["score"] = substr($this->score,$st,$breakPoint - $st);
			$count++;
			$st = $breakPoint + _SPECIAL_END_FLAG_STRLEN;
		}
	}
	
	public function insertLow($orignData)//插入一条新条约
	/*
	 * @参数：$data默认是selector查询回来的result，即是一个二维数组，真正的值在$data[0]["content"]
	 */
	{
		$newData = NULL;
		$newData["content"] = $orignData[0]["content"];
		$newData["score"] = $orignData[0]["score"];
		if (_DEBUG)
		{
			//echo dump($data);
			//trace(dump($orignData),"data");
			//trace($data["lowId"],"data[lowId]");
		}
		return $this->add($newData);
	}
	
	private function push($tmp)//往$this->data里扔原始数据，本函数用来加上间断标志
	{
		if ($this->outputData["content"] == "")
			$this->outputData["content"] = $tmp["content"];
		else
			$this->outputData["content"] = $this->outputData["content"] . _SPECIAL_END_FLAG . $tmp["content"];
		if ($this->outputData["score"] == "")
			$this->outputData["score"] = $tmp["score"];
		else
			$this->outputData["score"] = $this->outputData["score"] . _SPECIAL_END_FLAG . $tmp["score"];
	}
	
	public function updateRecord($tmp,$count)//传入的参数是一个2维数组，如：$tmp[i]["content"],$tmp[i]["score"]
	{
		//扔到data里
		for ($i = 0; $i < $count; $i++)
		{
			if (_DEBUG)
			{
				echo "<br>".$i;
				dump($tmp[$i]);
			}
			if ( $tmp[$i] == NULL ) 
			{
				if (_DEBUG)
				{
					echo "this is a null"; 
				}
				continue;
			}
			$this->push($tmp[$i]);
		}
		
		//更新数据库
		if ($this->save($this->outputData) === false)//必须是这样，因为save返回的是影响多少条记录，如果更新前后的记录内容一样会返回0，如果用0判断就有问题
			return false;
		else
			return 1;
	}
	
	
}
?>