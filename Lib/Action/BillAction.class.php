<?php
include(CONF_PATH."MyConfigINI.php");
include(LIB_PATH."CommonAction.php");

class BillAction extends CommonAction
{
	/**
	 * 添加新订单
	 * @method	get;
	 * @param fromID,toID,title,msgLast,msgPre,scoreLast,scorePre,timeLast,timePre,isEditFromID,isEditToID
	 * @return  如果成功返回订单id；失败返回false
	 */
	public function addbill()
	{
		$data = null;
		$data["fromID"]	=		$this->_get("fromID");
		$data["toID"]		=		$this->_get("toID");
		$data["title"]			=		$this->_get("title");
		$data["msgLast"]	=		$this->_get("msgLast");
		$data["msgPre"]	=		$this->_get("msgPre");
		$data["scoreLast"]	=		$this->_get("scoreLast");
		$data["scorePre"] =		$this->_get("scorePre");
		$data["timeLast"] =		$this->_get("timeLast");
		$data["timePre"]	=		$this->_get("timePre");
		$data["isEditFromID"] =$this->_get("isEditFromID");
		$data["isEditToID"]	=	 $this->_get("isEditToID");
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
	 * @param	id;
	 * @param	msgLast,msgPre,scoreLast,scorePre,timeLast,timePre,isEditFromID,isEditToID
	 * @return	成功返回ok，失败返回false
	 */
	public function editBill()
	{
		$data = null;
		$data["id"]	=		$this->_get("id");
		$data["msgLast"]	=		$this->_get("msgLast");
		$data["msgPre"]	=		$this->_get("msgPre");
		$data["scoreLast"]	=		$this->_get("scoreLast");
		$data["scorePre"] =		$this->_get("scorePre");
		$data["timeLast"] =		$this->_get("timeLast");
		$data["timePre"]	=		$this->_get("timePre");
		$data["isEditFromID"] =$this->_get("isEditFromID");
		$data["isEditToID"]	=	 $this->_get("isEditToID");
		$re = D("Bill")->save($data);
		if ( ($re === null) || ($re === false) )
			exit("false");
		else
			exit("ok");
	}
	
	/**
	 * 接受订单
	 * @method	get
	 * @param	id;账单id
	 * @return	bool；是否成功
	 */
	public function accept()
	{
		$billID	=		$this->_get("id");
		D("User")->startTrans();
		if ( (D("User")->acceptBill($billID)) && D("Bill")->save(array("id"=>$billID,"isOver"=>1)) )
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
	
	/**
	 * 查询一个订单
	 * @method get
	 * @param	id;账单id
	 * @return	一个订单，或者false
	 */
	public function queryOne()
	{
		$billID = $this->_get("id");
		$re = null;
		$re	=	D("Bill")->where(array("id"=>$billID))->find();
		if ($re)
			echo '1'._SPECAL_BREAK_FLAG.$this->serializeWithSlef($re,_SPECAL_BREAK_FLAG);
		else
			echo "false";
	}
	
	/**
	 * 查询该用户的所有订单（包括已完结的订单）
	 * @method get
	 * @param	uid
	 * @return	多个订单，或者false
	 */
	public function queryAll()
	{
		$uid = $this->_get("uid");
		$re = null;
		$re	=	D("Bill")->where(array("fromID"=>$uid))->select();
		if ($re)
			echo count($re)._SPECAL_BREAK_FLAG.$this->serializeTwoWithSlef($re,_SPECAL_BREAK_FLAG);
		else
			echo "false";
	}
	
	/**
	 * 查询该用户的所有订单（不包括已完结的订单）
	 * @method get
	 * @param	uid
	 * @return	多个订单，或者false
	 */
	public function query()
	{
		$uid = $this->_get("uid");
		$re = null;
		$re	=	D("Bill")->where(array("fromID"=>$uid,"isOver"=>0))->select();
		if ($re)
			echo count($re)._SPECAL_BREAK_FLAG.$this->serializeTwoWithSlef($re,_SPECAL_BREAK_FLAG);
		else
			echo "false";
	}
}