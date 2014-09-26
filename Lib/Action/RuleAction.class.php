<?php
include(CONF_PATH."MyConfigINI.php");
include(LIB_PATH."CommonAction.php");

class RuleAction extends CommonAction
{
	/**
	 * 添加新规则
	 * @method	param;
	 * @param fromID,title,content,scoreAdd,scoreSub
	 * @return  如果成功返回规则id；失败返回false
	 */
	public function add()
	{
		$data = null;
		$data["fromID"]		=		$this->_param("fromID");
		$data["title"]		=		$this->_param("title");
		$data["content"]	=		$this->_param("content");
		$data["scoreAdd"]	=		$this->_param("scoreAdd");
		$data["scoreSub"]	=		$this->_param("scoreSub");
		$data["isEditFromID"] =		1;
		$data["isEditToID"]	=	 	0;
		$data["isOver"]		=		0;
		$data["createTime"] =		date("Y-m-d H:i:s");
		
		$tmp	=	null;
		$tmp = D("User")->where(array("uid"=>$data["fromID"]))->find();
		$data["idPair"]		=	$tmp["pairID"];
		
		$re = D("Rule")->add($data);
		if ( ($re === null) || ($re === false) )
			exit("false");
		else
			echo $re;
	}
	
	/**
	 * 修改规则
	 * @method	param
	 * @param	uid,id,content,scoreAdd,scoreSub;
	 * @return	成功返回true，失败返回false
	 */
	public function edit()
	{
		$data = null;
		$data["uid"] 	=		$this->_param("uid");
		$data["id"]		=		$this->_param("id");
		$data["content"]	=		$this->_param("content");
		$data["scoreAdd"]	=		$this->_param("scoreAdd");
		$data["scoreSub"]	=		$this->_param("scoreSub");
		$data["createTime"] =		date("Y-m-d H:i:s");
		
		$re	=	null;
		$re = D("Rule")->where(array("id"=>$data["id"]))->find();
		
		if ($data["uid"] == $re["fromID"])
		{
			$data["isEditFromID"] =		1;
			$data["isEditToID"]	=	 	0;
		}
		else
		{
			$data["isEditFromID"] =		0;
			$data["isEditToID"]	=	 	1;
		}
		
		$re = D("Rule")->save($data);
		if ( ($re === null) || ($re === false) )
			exit("false");
		else
			exit("true");
	}
	
	/**
	 * 提交规则已读，即更新订单的isEditFromID等
	 * @param	id;ruleID
	 * @return	bool;是否成功；成功返回true，失败返回false
	 */
	public function updateState()
	{
		$id	=		$this->_param("id");
		$uid	=		$this->_param("uid");
		
		$re = D("Rule")->where(array("id"=>$id))->find();
		
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
		
		$tmp = D("Rule")->save($data);
		if ( ($tmp === false) || ($tmp === null) )
			echo "false";
		else
			echo "true";
	}
	
	/**
	 * 接受规则
	 * @method	param
	 * @param	id;规则id
	 * @return	bool；是否成功，成功返回true，失败返回false
	 */
	public function accept()
	{
		$idRule	=		$this->_param("id");
		if ( D("Rule")->save(array("id"=>$idRule,"isOver"=>1,"isEditFromID"=>1,"isEditToID"=>1)) )
		{
			echo "true";
		}
		else
		{
			echo "false";
		}
	}
	
	/**
	 * 标记一张已完成订单为删除状态
	 * @param	id;规则id
	 * @return	bool;是否成功
	 */
	public function delete()
	{
		$id	=		$this->_param("id");
	
		$re = D("Rule")->save(array("id"=>$id,"isOver"=>3));
	
		if ( ($re === null) || ($re === false) )
		{
			echo "false";
		}
		else
		{
			echo "true";
		}
	}
	
	
	
	/**
	 * 查询该用户的未完成规则
	 * @method param
	 * @param	uid
	 * @return	多个订单，或者false
	 */
	public function query()
	{
		header("Content-Type:text/html;charset=utf-8");
		$uid = $this->_param("uid");
		$tmp	=	null;
		$tmp = D("User")->where(array("uid"=>$uid))->find();
		$idPair		=	$tmp["pairID"];
		
		$re = null;
		$re	=	D("Rule")->where("pairID=".$idPair." and isOver=0")->order("timeLast desc")->select();
		if ($re)
			echo count($re)._SPECAL_BREAK_FLAG.$this->serializeTwoWithSlef($re,_SPECAL_BREAK_FLAG);
		else
			echo "false";
	}
	
	/**
	 * 查询该用户的已完结的规则
	 * @method param
	 * @param	uid
	 * @return	多个订单，或者false
	 */
	public function queryOver()
	{
		header("Content-Type:text/html;charset=utf-8");
		$uid = $this->_param("uid");
		$tmp	=	null;
		$tmp = D("User")->where(array("uid"=>$uid))->find();
		$idPair		=	$tmp["pairID"];
		
		$re = null;
		$re	=	D("Rule")->where("pairID=".$idPair." and isOver=1")->order("timeLast desc")->select();
		if ($re)
			echo count($re)._SPECAL_BREAK_FLAG.$this->serializeTwoWithSlef($re,_SPECAL_BREAK_FLAG);
		else
			echo "false";
	}
	
}