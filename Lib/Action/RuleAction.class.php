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
		$data["isUnreadFromID"] =		1;
		$data["isUnreadToID"]	=	 	1;
		$data["isOver"]		=		0;
		$data["createTime"] =		date("Y-m-d H:i:s");
		
		$tmp	=	null;
		$tmp = D("User")->where(array("uid"=>$data["fromID"]))->find();
		$data["pairID"]		=	$tmp["pairID"];
		
		$re = D("Rule")->add($data);
		if ( ($re === null) || ($re === false) )
			exit("false");
		else
			echo $re;
	}
	
	/**
	 * 修改规则
	 * @method	param
	 * @param	id,uid,content,scoreAdd,scoreSub;
	 * @return	成功返回true，失败返回false
	 */
	public function edit()
	{
		$data = null;
		$data["id"]			=		$this->_param("id");
		$data["fromID"] 	=		$this->_param("uid");
		$data["content"]	=		$this->_param("content");
		$data["scoreAdd"]	=		$this->_param("scoreAdd");
		$data["scoreSub"]	=		$this->_param("scoreSub");
		$data["isUnreadFromID"] =		1;
		$data["isUnreadToID"]	=	 	1;
		$data["createTime"] =		date("Y-m-d H:i:s");
		
// 		//修改修改人id
// 		$tmp = null;
// 		$tmp = D("Pair")->where(array("user1ID"=>$data["id"]))->find();
// 		if ($tmp)
// 			$data["id"]	=	$tmp["user2ID"];
// 		else
// 		{
// 			$tmp = D("Pair")->where(array("user2ID"=>$data["id"]))->find();
// 			if ($tmp)
// 			{
// 				$data["id"]	=	$tmp["user1ID"];
// 			}
// 			else
// 				exit("false");
// 		}
		
		$re	=	null;
		$re = D("Rule")->save($data);
		if ( ($re === null) || ($re === false) )
			exit("false");
		else
			exit("true");
	}
	
	/**
	 * 提交规则已读，即更新订单的isUnreadFromID等
	 * @param	id;ruleID
	 * @return	bool;是否成功；成功返回true，失败返回false
	 */
	public function updateState()
	{
		$id		=		$this->_param("id");
		$uid	=		$this->_param("uid");
		
		$re = D("Rule")->where(array("id"=>$id))->find();
		
		$data = null;
		$data["id"] = $id;
		if ( $re["fromID"]  == $uid )
		{
			$data["isUnreadFromID"] = 0;
		}
		else
		{
			$data["isUnreadToID"]	=	0;
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
		$idRule		=		$this->_param("id");
		if ( D("Rule")->save(array("id"=>$idRule,"isOver"=>1,"isUnreadFromID"=>1,"isUnreadToID"=>1)) )
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
		$id		=		$this->_param("id");
	
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
	 * @param	uid；page：当前页数，从1开始计数
	 * @return	多个订单，或者false
	 */
	public function query()
	{
		header("Content-Type:text/html;charset=utf-8");
		$uid 	= $this->_param("uid");
		$page	=	$this->_param("page");
		$tmp	=	null;
		$tmp = D("User")->where(array("uid"=>$uid))->find();
		$pairID		=	$tmp["pairID"];
		
		$re = null;
		$totalNum	=	D("Rule")->where("pairID=".$pairID." and isOver=0")->order("createTime desc")->count();
		$totalPageNum	=	ceil($totalNum / _PAGE_CONTENT_NUM);
		$re		=	D("Rule")->where("pairID=".$pairID." and isOver=0")->order("createTime desc")->page($page,_PAGE_CONTENT_NUM)->select();
		if ($re !== false)
		{
			if ($page < $totalPageNum)
				echo (_PAGE_CONTENT_NUM + 1)._SPECAL_BREAK_FLAG.$this->serializeTwoWithSlef($re,_SPECAL_BREAK_FLAG);
			else
				echo count($re)._SPECAL_BREAK_FLAG.$this->serializeTwoWithSlef($re,_SPECAL_BREAK_FLAG);
		}
		else
			echo "false";
	}
	
	/**
	 * 查询该用户的已完结的规则，不支持分页
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
		$pairID		=	$tmp["pairID"];
		
		$re = null;
		$re	=	D("Rule")->where("pairID=".$pairID." and isOver=1")->order("createTime desc")->select();
		if ($re !== false)
			echo count($re)._SPECAL_BREAK_FLAG.$this->serializeTwoWithSlef($re,_SPECAL_BREAK_FLAG);
		else
			echo "false";
	}
	
	/**
	 * 查询该用户的已完结的规则，支持分页
	 * @method param
	 * @param	uid；page：当前页数，从1开始计数
	 * @return	多个订单，或者false
	 */
	public function queryOverPage()
	{
		header("Content-Type:text/html;charset=utf-8");
		$uid 	= $this->_param("uid");
		$page	=	$this->_param("page");
		$tmp	=	null;
		$tmp = D("User")->where(array("uid"=>$uid))->find();
		$pairID		=	$tmp["pairID"];
	
		$re = null;
		$totalNum	=	D("Rule")->where("pairID=".$pairID." and isOver=1")->order("createTime desc")->count();
		$totalPageNum	=	ceil($totalNum / _PAGE_CONTENT_NUM);
		$re	=	D("Rule")->where("pairID=".$pairID." and isOver=1")->order("createTime desc")->page($page,_PAGE_CONTENT_NUM)->select();
		if ($re !== false)
		{
			if ($page < $totalPageNum)
				echo (_PAGE_CONTENT_NUM + 1)._SPECAL_BREAK_FLAG.$this->serializeTwoWithSlef($re,_SPECAL_BREAK_FLAG);
			else
				echo count($re)._SPECAL_BREAK_FLAG.$this->serializeTwoWithSlef($re,_SPECAL_BREAK_FLAG);
		}
		else
			echo "false";
	}
	
}