<?php
include(CONF_PATH."MyConfigINI.php");
include(LIB_PATH."CommonAction.php");

class BillAction extends CommonAction
{
	/**
	 * 添加新订单
	 * @method	get;
	 * @param fromID,toID,isAdd,title,msgLast,scoreLast
	 * @return  如果成功返回订单id；失败返回false
	 */
	public function addbill()
	{
		$data = null;
		$data["fromID"]	=		$this->_param("fromID");
		$data["toID"]		=		$this->_param("toID");
		$data["isAdd"]		=		$this->_param("isAdd");
		$data["title"]			=		$this->_param("title");
		$data["msgLast"]	=		$this->_param("msgLast");
// 		$data["msgPre"]	=		"";
		$data["scoreLast"]	=		$this->_param("scoreLast");
// 		$data["scorePre"] =		$this->_param("scorePre");
		$data["timeLast"] =		date("Y-m-d H:i:s");
// 		$data["timePre"]	=		$this->_param("timePre");
		$data["isEditFromID"] =1;
		$data["isEditToID"]	=	 1;
		$data["lastIsFrom"]	=	1;
		$data["isOver"]		=		0;
		$re = D("Bill")->add($data);
		if ( ($re === null) || ($re === false) )
			exit("false");
		else
			echo $re;
	}
	
	/**
	 * 修改订单
	 * @method	get
	 * @param	uid,id;
	 * @param	msgLast,scoreLast
	 * @return	成功返回ok，失败返回false
	 */
	public function editBill()
	{
		$data = null;
		$data["uid"] 	=		$this->_param("uid");
		$data["id"]	=		$this->_param("id");
		
		$re = D("Bill")->where(array("id"=>$data["id"]))->find();
		
		if ($data["uid"] == $re["fromID"])
		{
			if ( $re["lastIsFrom"] )
			{
				$data["msgLast"]	=		$this->_param("msgLast");
				$data["scoreLast"]	=		$this->_param("scoreLast");
				$data["timeLast"]	=		date("Y-m-d H:i:s");
			}
			else
			{
				$data["msgPre"] = $re["msgLast"];
				$data["scorePre"] = $re["scoreLast"];
				$data["timePre"]  =  $re["timeLast"];
				
				$data["msgLast"]	=		$this->_param("msgLast");
				$data["scoreLast"]	=		$this->_param("scoreLast");
				$data["timeLast"] = 		date("Y-m-d H:i:s");
				$data["lastIsFrom"]	=	1;
			}
		}
		else
		{
			if ($re["lastIsFrom"])
			{
				$data["msgPre"] = $re["msgLast"];
				$data["scorePre"] = $re["scoreLast"];
				$data["timePre"]  =  $re["timeLast"];
				
				$data["msgLast"]	=		$this->_param("msgLast");
				$data["scoreLast"]	=		$this->_param("scoreLast");
				$data["timeLast"] = 		date("Y-m-d H:i:s");
				$data["lastIsFrom"]	=	0;
			}
			else
			{
				$data["msgLast"]	=		$this->_param("msgLast");
				$data["scoreLast"]	=		$this->_param("scoreLast");
				$data["timeLast"]	=		date("Y-m-d H:i:s");
			}
		}
		
		$data["isEditFromID"] = 1;
		$data["isEditToID"]	=	1;
		
		$re = D("Bill")->save($data);
		if ( ($re === null) || ($re === false) )
			exit("false");
		else
			exit("ok");
	}
	
	/**
	 * 更新订单的isEditFromID等
	 * @param	id;billID
	 * @return	bool;是否成功；成功返回true，失败返回false
	 */
	public function updateState()
	{
		$id	=		$this->_param("id");
		$uid	=		$this->_param("uid");
		
		$re = D("Bill")->where(array("id"=>$id))->find();
		
		$data = null;
		$data["id"] = $id;
		if ( $re["fromID"]  == $uid )
		{
			$data["isEditFromID"] = 0;
		}
		else
		{
			$data["isEditToID"]	=	0;
		}
		
		$tmp = D("Bill")->save($data);
		if ( ($tmp === false) || ($tmp === null) )
			echo "false";
		else
			echo "true";
	}
	
	/**
	 * 接受订单
	 * @method	get
	 * @param	id;账单id
	 * @return	bool；是否成功，成功返回true，失败返回false
	 */
	public function accept()
	{
		$billID	=		$this->_param("id");
		D("User")->startTrans();
		if ( (D("User")->acceptBill($billID)) && D("Bill")->save(array("id"=>$billID,"isOver"=>1,"isEditFromID"=>1,"isEditToID"=>1)) )
		{
			D("User")->commit();
			echo "true";
		}
		else
		{
			D("User")->rollback();
			echo "false";
		}
	}
	
// 	/**
// 	 * 查询一个订单
// 	 * @method get
// 	 * @param	id;账单id
// 	 * @return	一个订单，或者false
// 	 */
// 	public function queryOne()
// 	{
// 		header("Content-Type:text/html;charset=utf-8");
// 		$billID = $this->_param("id");
// 		$re = null;
// 		$re	=	D("Bill")->where(array("id"=>$billID))->find();
// 		if ($re["isAdd"])
// 			$re["isAdd"] = "+";
// 		else
// 			$re["isAdd"] = "-";
// 		if ($re !== false)
// 			echo '1'._SPECAL_BREAK_FLAG.$this->serializeWithSlef($re,_SPECAL_BREAK_FLAG);
// 		else
// 			echo "false";
// 	}
	
// 	/**
// 	 * 查询该用户的所有订单（包括已完结的订单）
// 	 * @method get
// 	 * @param	uid
// 	 * @return	多个订单，或者false
// 	 */
// 	public function queryAll()
// 	{
// 		header("Content-Type:text/html;charset=utf-8");
// 		$uid = $this->_param("uid");
// 		$re = null;
// 		$re	=	D("Bill")->where("fromID=".$uid." or toID=".$uid)->order("timeLast desc")->select();
// 		foreach ($re as $key=>$value)
// 		{
// 			if ($re[$key]["isAdd"])
// 				$re[$key]["isAdd"] = "+";
// 			else
// 				$re[$key]["isAdd"] = "-";
// 		}
// 		if ($re !== false)
// 			echo count($re)._SPECAL_BREAK_FLAG.$this->serializeTwoWithSlef($re,_SPECAL_BREAK_FLAG);
// 		else
// 			echo "false";
// 	}
	
	/**
	 * 查询该用户的未完成订单
	 * @method get
	 * @param	uid
	 * @return	多个订单，或者false
	 */
	public function query()
	{
		header("Content-Type:text/html;charset=utf-8");
		$uid = $this->_param("uid");
		$partnerID	=	$this->getPartnerID($uid);
		
		$re = null;
		$re	=	D("Bill")->where("(fromID=".$uid." or toID=".$uid." or fromID=".$partnerID." or toID=".$partnerID.")and isOver=0")->order("timeLast desc")->select();
		foreach ($re as $key=>$value)
		{
			if ($re[$key]["isAdd"])
				$re[$key]["isAdd"] = "+";
			else
				$re[$key]["isAdd"] = "-";
		}
		if ($re !== false)
			echo count($re)._SPECAL_BREAK_FLAG.$this->serializeTwoWithSlef($re,_SPECAL_BREAK_FLAG);
		else
			echo "false";
	}
	
	/**
	 * 查询该用户的已完结的订单
	 * @method param
	 * @param	uid
	 * @return	多个订单，或者false
	 */
	public function queryOver()
	{
		header("Content-Type:text/html;charset=utf-8");
		$uid = $this->_param("uid");
		$partnerID	=	$this->getPartnerID($uid);
		
		$re = null;
		$re	=	D("Bill")->where("(fromID=".$uid." or toID=".$uid." or fromID=".$partnerID." or toID=".$partnerID.")and isOver=1")->order("timeLast desc")->select();
		foreach ($re as $key=>$value)
		{
			if ($re[$key]["isAdd"])
				$re[$key]["isAdd"] = "+";
			else
				$re[$key]["isAdd"] = "-";
		}
		if ($re !== false)
			echo count($re)._SPECAL_BREAK_FLAG.$this->serializeTwoWithSlef($re,_SPECAL_BREAK_FLAG);
		else
			echo "false";
	}
	
	/**
	 * 标记一张已完成订单为删除状态
	 * @param	id;billID
	 * @return	bool;是否成功
	 */
	public function delete()
	{
		$id	=		$this->_param("id");
		
		$re = D("Bill")->save(array("id"=>$id,"isOver"=>3));
		
		if ( ($re === null) || ($re === false) )
		{
			echo "false";
		}
		else
		{
			echo "true";
		}
	}
}