<?php
include(CONF_PATH."MyConfigINI.php");
include(LIB_PATH."CommonAction.php");

class TaskAction extends CommonAction
{
	
	/**
	 * 新建任务
	 * @method	param;
	 * @param fromID,title,content,score
	 * @return  如果成功返回规则id；失败返回false;发起人score不够、score小于等于0：返回error
	 */
	public function add()
	{
		$data = null;
		$data["fromID"]		=		$this->_param("fromID");
		$data["title"]		=		$this->_param("title");
		$data["content"]	=		$this->_param("content");
		$data["score"]		=		$this->_param("score");
		$data["isUnreadFromID"] =		1;
		$data["isUnreadToID"]	=	 	1;
		$data["state"]		=		0;
		$data["createTime"] =		date("Y-m-d H:i:s");
		
		//TODO:数据库事务
		$re = D("Task")->add($data);
		if ( ($re === null) || ($re === false) )
			exit("false");
		
		$fromUser		=	null;
		$fromUser		=	D("User")->where(array("uid"=>$data["fromID"]))->find();
		if ($fromUser["score"] - $data["score"] < 0)
			exit("error");
		if ($data["score"] <= 0)
			exit("error");
		$fromUser["score"] -= $data["score"];
		
		if ( D("User")->save($fromUser) )
			echo $re;
		else
			echo "false";
	}
	
	/**
	 * 接受任务
	 * 要执行该操作uid要求只能是toid
	 * @method	param
	 * @param	id：规则id；uid：操作人uid
	 * @return	成功返回true，失败返回false;操作人非法返回error
	 */
	public function accept()
	{
		$id		=	$this->_param("id");
		$uid	=	$this->_param("uid");
		
		//验证
		$tmp	=	null;
		$tmp	=	D("Task")->where(array("id"=>$id))->find();
		if ( ($tmp === null) || ($tmp === false) )
			exit("false");
		if ($tmp["fromID"] == $uid)
			exit("error");
		
		//修改
		if ( D("Task")->save(array("id"=>$id,"state"=>1,"isUnreadFromID"=>1,"isUnreadToID"=>1)) )
		{
			echo "true";
		}
		else
		{
			echo "false";
		}
	}
	
	/**
	 * 结束任务（发起方确认任务完成，分数转移）
	 * 要执行该操作的uid只能是fromid
	 * @method	param
	 * @param	id：规则id；uid：操作人uid
	 * @return	成功返回true，失败返回false;操作人非法返回error
	 */
	public function over()
	{
		$id		=	$this->_param("id");
		$uid	=	$this->_param("uid");
	
		//验证
		$tmp	=	null;
		$tmp	=	D("Task")->where(array("id"=>$id))->find();
		if ( ($tmp === null) || ($tmp === false) )
			exit("false");
		if ($tmp["fromID"] != $uid)
			exit("error");

		//TODO:数据库事务
		//修改
		$toUser		=	null;
		$toUser		=	D("User")->where(array("uid"=>$this->getPartnerID($uid)))->find();
		$toUser["score"] += $tmp["score"];
		
		if ( D("Task")->save(array("id"=>$id,"state"=>2,"isUnreadFromID"=>1,"isUnreadToID"=>1))
			&&( D("User")->save($toUser) )
			)
		{
			echo "true";
		}
		else
		{
			echo "false";
		}
	}
	
	
	/**
	 * 标记一个任务为删除状态（接收与不接受状态都可以删除，即状态为0、1都可以删除）
	 * 状态为0时，要执行该操作uid要求只能是fromid
	 * 状态为1时，要执行该操作uid要求只能是toid
	 * @param	id：规则id；uid：操作人uid
	 * @return	成功返回true，失败返回false;操作人非法返回error
	 */
	public function delete()
	{
		$id		=	$this->_param("id");
		$uid	=	$this->_param("uid");
		
		//验证
		$tmp	=	null;
		$tmp	=	D("Task")->where(array("id"=>$id))->find();
		if ( ($tmp === null) || ($tmp === false) )
			exit("false");
		if ( ($tmp["state"] == 0) && ($tmp["fromID"] != $uid) )
			exit("error");
		if ( ($tmp["state"] == 1) && ($this->getPartnerID($uid) != $uid) )
			exit("error");
		
		//TODO:数据库事务
		//修改
		$fromUser		=	null;
		$fromUser		=	D("User")->where(array("uid"=>$data["fromID"]))->find();
		$fromUser["score"] += $tmp["score"];
		
		if ( D("Task")->save(array("id"=>$id,"state"=>3,"isUnreadFromID"=>0,"isUnreadToID"=>0)) 
			&& ( D("User")->save($fromUser) )
			)
		{
			echo "true";
		}
		else
		{
			echo "false";
		}
	}
	
	
	/**
	 * 提交任务已读，即更新订单的isUnreadFromID等
	 * @param	id:任务id;uid:用户的uid
	 * @return	bool;是否成功；成功返回true，失败返回false
	 */
	public function updateState()
	{
		$id		=		$this->_param("id");
		$uid	=		$this->_param("uid");
	
		$re	=	null;
		$re = D("Task")->where(array("id"=>$id))->find();
	
		$data = null;
		$data["id"] = $id;
		if ( $re["fromID"]  == $uid )
		{
			$data["isUnreadFromID"] = 0;
		}
		else
		{
			$data["isUnreadToID"] = 0;
		}
	
		$tmp = D("Task")->save($data);
		if ( ($tmp === false) || ($tmp === null) )
			echo "false";
		else
			echo "true";
	}
	
	/**
	 * 查询该用户的任务（状态为0、1的）
	 * @method param
	 * @param	uid
	 * @return	多个订单，或者false
	 */
	public function query()
	{
		header("Content-Type:text/html;charset=utf-8");
		$uid = $this->_param("uid");
		
		$re = 	null;
		$re	=	D("Rule")->where("(fromID=".$uid." or fromID=".$this->getPartnerID($uid).") and (state=0 or state=1)")->order("createTime desc")->select();
		if ($re !== false)
			echo count($re)._SPECAL_BREAK_FLAG.$this->serializeTwoWithSlef($re,_SPECAL_BREAK_FLAG);
		else
			echo "false";
	}
	
}