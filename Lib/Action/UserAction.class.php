<?php
include(CONF_PATH."MyConfigINI.php");
include(LIB_PATH."CommonAction.php");

class UserAction extends CommonAction
{
	
	public function changeMood()
	{
		if (session('myMoodValue') == $this->_param("mood"))//更新前更新后是一样的值会导致$result返回false，即没有更新
		{
			redirect(U('User/index'),0);
		}
		else
		{
			$condition = NULL;
			//$condition['userId'] = session('pairUserId');
			$condition['moodValue'] = $this->_param("mood");
			$dbUser = M("User");
			$result = $dbUser->where('userId='.session('pairUserId'))->save($condition);
			if($result)
			{
				redirect(U('User/index'),0);
			}
			else
			{
				$this->error('登陆有问题，系统退出后请重新登录','__APP__/Index/logout');
			}
		}
	}
	
	public function ping()//连接另一半的账户
	{
		$db = M("Temppair");//大写的话相当于加了一个下划线，比如M("TempPair");相当于连接数据库temp_pair
		if ($db->where("userStartId=".session("userId"))->select())//已发送申请
		{
			redirect(U('User/alreadyPing'),0);
		}
		if ($db->where("userEndId=".session('userId'))->select())//被发送申请
		{
			redirect(U('User/bePing'),0);
		}
		$this->display();
	}
	
	public function alreadyPing()//已经发生关联请求，但对方还没有确认（与bePing对称）
	{
		$dbUser  = M("User");
		$db = M("Temppair");
		$result = $db->where("userStartId=".session('userId'))->select();
		$this->assign('remark',$result[0]['remark']);
		$result = $dbUser->where("userId=".$result[0]['userEndId'])->select();
		$this->assign('pairUserName',$result[0]['userName']);
		$this->display();
	}
	
	public function bePing()//被他人发送了关联请求(与alreadyPing对称)
	{
		$dbUser  = M("User");
		$db = M("Temppair");
		$result = $db->where("userEndId=".session('userId'))->select();
		$this->assign('remark',$result[0]['remark']);
		//////
		session("pairUserId",$result[0]['userStartId']);
		//////
		$result = $dbUser->where("userId=".$result[0]['userStartId'])->select();
		$this->assign('pairUserName',$result[0]['userName']);
		$this->display();
	}
	
	public function verifyTempCon()//确认关联请求
	{
		//得到pairUserId
		$pairUserId = session('pairUserId');
		
		//更新pair
		$dbPair = M("Pair");
		$data = NULL;
		$data["user1Id"] = session('userId');
		$data["user2Id"] = $pairUserId;
		$data["pairDate"] = date("Y-m-d H:i:s");
		$data["money"] = _INIT_MONEY;
		$data["lowId"] = 1;
		$data["targetId"] = 0;
		if (!$dbPair->add($data))
		{
			$this->error("关联失败，请重试");
		}
		//得到pairId
		$result = NULL;
		$result = $dbPair->where("user1Id=".session('userId'))->select();
		if (!$result)
		{
			$this->error("关联失败，请重试");
		}
		$pairId = $result[0]["pairId"];
		
		//更新本账户
		$dbUser = M("User");
		$data = NULL;
		$userName = session('userName');
		$data["pairUserId"] = $pairUserId;
		$data["pairId"] = $pairId;
		if (!$dbUser->where('userName='."'$userName'")->save($data))
		{
			$this->error("关联失败，请重试");
		}
		
		//更新对方账户
		$data = NULL;
		$data["pairId"] = $pairId;
		$data["pairUserId"] = session('userId');
		if (!$dbUser->where("userId=".$pairUserId)->save($data))
		{
			$this->error("关联失败，请重试");
		}
		
		//删除临时匹配记录tempPair
		$dbTempPair = M("Temppair");
		$this->isOk(-1,$dbTempPair->where("userEndId=".session('userId'))->delete(),"关联成功","User/index","关联失败，请重试",0);
		
	}
	
	public function cancelTempCon()//在对方还未确定时取消连接（与ignoreTempPair函数对称）
	{
		$db = M("Temppair");
		$result = $db->where("userStartId=".session('userId'))->delete();
		$this->isOk(-1,$result,'已撤销关联申请','User/ping','撤销申请发送失败，请重试',0);
	}
	
	public function ignoreTempPair()//忽视对方的申请连接（与cancelTempCon函数对称）
	{
		$db = M("Temppair");
		$result = $db->where("userEndId=".session('userId'))->delete();
		$this->isOk(0,$result,0,'User/ping',0,0);
	}
	
	public function cancelCon()//已经确定关联后取消连接
	{
		/*
		$db = M("pair");
		$result = $db->where("userStartId=".session('userId'))->delete();
		$this->isOk($result,'已撤销关联申请','User/ping','撤销申请发送失败，请重试',0);
		*/
	}
	
	public function toPing()
	{
		$dbUser = M("User");
		$db = M("Temppair");
		$toUserName = $this->_post('userName');//要连接的那个人的userName
		if ($toUserName == "")//userName为空
		{
			$this->error("账户名不能为空");
		}
		$result = $dbUser->where("userName="."'$toUserName'")->select();
		if (!$result)//userName不存在
		{
			$this->error("对方还没有开户，请邀请对方来本银行开户");
		}
		if ($result[0]["pairId"] != 0)
			$this->error("对方账户已经被其他用户关联");
		
		$data["userStartId"] = session('userId');
		$data["userEndId"] = $result[0]["userId"];
		$data["remark"] = $this->_post("remark");
		$data["pairDate"] = date("Y-m-d H:i:s");
		if ($db->add($data))
		{
			$this->success('申请已发送',U('User/ping'));
		}
		else
		{
			$this->error('申请发送失败，请重试');
		}
	}
	
	
	/**
	 * 显示新消息
	 */
	public function message()
	{
		$dbUser = D("User");
		$dbUser->init($this->userID);
		$billIdList = $dbUser->getBillContent();
		
		$dbBill = D("Bill");
		$data = $dbBill->getBillInfo($billIdList);
	
		$count = count($data);
		for ($i = 0; $i < $count; $i++)
		{
			if ($data[$i]["isAdd"] == true)
			{
				$output[$i]["isAdd"] = "1";
			}
			else
			{
				$output[$i]["isAdd"] = "0";
			}
			$output[$i]["remark"] = $data[$i]["remark"];
			$output[$i]["mId"] = $data[$i]["billId"];
			$output[$i]["userStartID"] = $data[$i]["userStartID"];
			$output[$i]["sMsg"] = $data[$i]["sMsg"];
			$output[$i]["eMsg"] = $data[$i]["eMsg"];
			$output[$i]["sMoney"] = $data[$i]["sMoney"];
			$output[$i]["eMoney"] = $data[$i]["eMoney"];
			$output[$i]["sTime"] = $data[$i]["sTime"];
			$output[$i]["eTime"] = $data[$i]["eTime"];
			$output[$i]["toUser"] = $data[$i]["toUser"];
		}
	
		header("Content-type: text/html; charset=utf-8");
// 		dump($output);
		echo xuLieHua_2($output);
	}

	
	
	public function toAdd()//与toSub对称
	{
		$dbBill = D("Bill");
		$dbBill->init($this->userID,$this->pairUserID,true);
		
		// 		$dbBill->insertTempBill();
		$this->isOk(-1,$dbBill->insertTempBill(),"转账申请成功，等待对方确认","User/index","转账错误，请重试","User/add");
	}
	
	
	
	public function toSub()//与toAdd对称
	{
		$dbBill = D("Bill");
		$dbBill->init($this->userID,$this->pairUserID,false);
	
		$this->isOk(-1,$dbBill->insertTempBill(),"扣除申请成功，等待对方确认","User/index","转账错误，请重试","User/sub");
	}
	
	
	/**
	 * 修改订单页面
	 */
	public function editMessage()
	{
		$dbBill = D("Bill");
		$dbBill->init($this->userID,$this->pairUserID);
	
// 		 $dbBill->editTempBill($this->_param("mId"));
		$this->isOk(-1,$dbBill->editTempBill($this->_param("mId")),"修改成功","User/message","修改错误，请重试","User/message");
	}
	
	
	
	/*
	 * 接受订单
	 */
	public function acceptMessage()
	{
		$dbBill = D("Bill");
		$dbBill->init($this->userID,$this->pairUserID);
		$pairId = $this->pairID;
	
		//$dbBill->acceptTempBill($this->_param("id"),$pairId);$this->display("add");
		$this->isOk(-1,$dbBill->acceptTempBill($this->_param("id"),$pairId),"确认成功","User/message","确认错误，请重试","User/message");
	}
}