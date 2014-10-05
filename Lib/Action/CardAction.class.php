<?php
include(CONF_PATH."MyConfigINI.php");
include(LIB_PATH."CommonAction.php");

class CardAction extends CommonAction
{
	
	/**
	 * 新增卡片（进入拥有卡片库）
	 * @method	param;
	 * @param uid;id:卡片id;
	 * @return  bool;
	 */
	private function add($uid,$id)
	{
		$data	=	null;
		$data["uid"]	=		$uid;
		
		$tmp	=	null;
		$tmp = D("User")->where(array("uid"=>$data["uid"]))->find();

		$re		=	null;
		$re	= $this->stringToArray($tmp["cardOwn"]);
		$re[$id]++;
		$data["cardOwn"] = $this->serializeWithSlef($re);
		
		$re = null;
		$re = D("User")->save($data);
		if ( ($re === null) || ($re === false) )
			return false;
		else
			return true;
	}
	
	/**
	 * 钻石购买卡片（进入拥有卡片库）
	 * @method	param;
	 * @param uid;id:卡片id;money:需要的钻石数量
	 * @return  如果成功返回true；失败返回false;当钻石不足时返回error
	 */
	public function buy()
	{
		$data	=	null;
		$data["uid"]	=		$this->_param("uid");
		$id				=		$this->_param("id");
		$money			=		$this->_param("money");
		
		$tmp	=	null;
		$tmp = D("User")->where(array("uid"=>$data["uid"]))->find();
		
		if ( ($tmp["money"] - $money) < 0)
			exit("error");
		
		$tmp["money"] -= $money;
		
		//TODO:数据库事务
		$re = D("User")->save($tmp);
		if ( ($re === null) || ($re === false) )
			exit("false");
		if ($this->add($data["uid"],$id))
			echo "true";
		else
			echo "false";
	}
	
	/**
	 * 积分兑换卡片（进入拥有卡片库）
	 * @method	param;
	 * @param uid;id:卡片id;point:需要的积分数量
	 * @return  如果成功返回true；失败返回false;当钻石不足时返回error
	 */
	public function exchange()
	{
		$data	=	null;
		$data["uid"]	=		$this->_param("uid");
		$id				=		$this->_param("id");
		$point			=		$this->_param("point");
	
		$tmp	=	null;
		$tmp = D("User")->where(array("uid"=>$data["uid"]))->find();
	
		if ( ($tmp["point"] - $point) < 0)
			exit("error");
	
		$tmp["point"] -= $point;
	
		//TODO:数据库事务
		$re = D("User")->save($tmp);
		if ( ($re === null) || ($re === false) )
			exit("false");
		if ($this->add($data["uid"],$id))
			echo "true";
		else
			echo "false";
	}
	
	/**
	 * 使用卡片（从可用卡片库中）
	 * @method	param
	 * @param	uid；id：卡片id
	 * @return	成功返回true，失败返回false;当可用卡片数量为0时返回error
	 */
	public function useCard()
	{
		$data	=	null;
		$data["uid"]	=		$this->_param("uid");
		$id				=		$this->_param("id");
		
		$tmp	=	null;
		$tmp = D("User")->where(array("uid"=>$data["uid"]))->find();

		$re		=	null;
		$re	= $this->stringToArray($tmp["cardAble"]);
		if ($re[$id] <= 0)
			exit("error");
		$re[$id]--;
		$data["cardAble"] = $this->serializeWithSlef($re);
		
		$re = null;
		$re = D("User")->save($data);
		if ( ($re === null) || ($re === false) )
			exit("false");
		else
			echo "true";
	}
	
	/**
	 * 丢弃卡片（从拥有卡片库中）
	 * @method	param
	 * @param	uid；id：卡片id
	 * @return	成功返回true，失败返回false;当可用卡片数量为0时返回error
	 */
	public function delete()
	{
		$data	=	null;
		$data["uid"]	=		$this->_param("uid");
		$id				=		$this->_param("id");
	
		$tmp	=	null;
		$tmp = D("User")->where(array("uid"=>$data["uid"]))->find();
	
		$re		=	null;
		$re	= $this->stringToArray($tmp["cardOwn"]);
		if ($re[$id] <= 0)
			exit("error");
		$re[$id]--;
		$data["cardOwn"] = $this->serializeWithSlef($re);
	
		$re = null;
		$re = D("User")->save($data);
		if ( ($re === null) || ($re === false) )
			exit("false");
		else
			echo "true";
	}
	
	/**
	 * 赠送卡片，从A的拥有卡片库中给B的可用卡片 库
	 * @method	param
	 * @param	uid：发起用户的uid；id：卡片id
	 * @return	成功返回true，失败返回false;当A的拥有卡片数量为0时返回error
	 */
	public function give()
	{
		$data	=	null;
		$data["uid"]	=		$this->_param("uid");
		$id				=		$this->_param("id");
		
		//TODO:数据库事务
		//扣除A
		$tmp	=	null;
		$tmp = D("User")->where(array("uid"=>$data["uid"]))->find();

		$re		=	null;
		$re	= $this->stringToArray($tmp["cardOwn"]);
		if ($re[$id] <= 0)
			exit("error");
		$re[$id]--;
		$data["cardOwn"] = $this->serializeWithSlef($re);
		
		$re = null;
		$re = D("User")->save($data);
		if ( ($re === null) || ($re === false) )
			exit("false");
		
		//增加B
		$partnerID	=	$this->getPartnerID($data["uid"]);
		$data	=	null;
		$data["uid"] = $partnerID;
		$tmp	=	null;
		$tmp = D("User")->where(array("uid"=>$partnerID))->find();
		
		$re		=	null;
		$re	= $this->stringToArray($tmp["cardAble"]);
		$re[$id]++;
		$data["cardAble"] = $this->serializeWithSlef($re);
		
		$re = null;
		$re = D("User")->save($data);
		if ( ($re === null) || ($re === false) )
			exit("false");
		else
			echo "true";
	}
	
	
	
	/**
	 * 查询该用户的拥有卡片库
	 * @method param
	 * @param	uid
	 * @return	卡片库序列化后的字符串
	 */
	public function queryOwn()
	{
		header("Content-Type:text/html;charset=utf-8");
		$uid = $this->_param("uid");
		$tmp	=	null;
		$tmp = D("User")->where(array("uid"=>$uid))->find();
		
		if ($tmp)
			echo $tmp["cardOwn"];
		else
			echo "false";
	}
	
	/**
	 * 查询该用户的可用卡片库
	 * @method param
	 * @param	uid
	 * @return	卡片库序列化后的字符串
	 */
	public function queryAble()
	{
		header("Content-Type:text/html;charset=utf-8");
		$uid = $this->_param("uid");
		$tmp	=	null;
		$tmp = D("User")->where(array("uid"=>$uid))->find();
		
		if ($tmp)
			echo $tmp["cardAble"];
		else
			echo "false";
	}
	
}