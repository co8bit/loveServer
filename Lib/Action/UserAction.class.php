<?php
include(CONF_PATH."MyConfigINI.php");
include(LIB_PATH."CommonAction.php");

class UserAction extends CommonAction
{
	private function isOk($time,$ok,$trueStr,$trueU,$falseStr,$falseU)//判读是否成功写入数据库
	//参数分别为：跳转时间（-1为默认）、判断量、为真提示符、为真跳转U操作参数（字符串）、为假提示符，为假跳转U操作参数（字符串）,若没有跳转操作则传0
	//注：立即跳转需要传入U函数参数
	{
		//$this->assign('waitSecond',135);
		
		if ($time == -1)//默认跳转时间
		{
			if ($ok)
			{
				if ($trueU === 0)
				{
					$this->success($trueStr);
				}
				else
					$this->success($trueStr,U($trueU));
			}
			else
			{
				if ($falseU === 0)
					$this->error($falseStr);
				else
					$this->error($falseStr,U($falseU));
			}
		}
		else
		if ($time == 0)//立即跳转
		{
			if ($ok)
			{
				redirect(U($trueU),0);
			}
			else
			{
				redirect(U($falseU),0);
			}
		}
		else//延时跳转
		{
			$this->assign('waitSecond',$time);
			if ($ok)
			{
				if ($trueU === 0)
				{
					$this->success($trueStr);
				}
				else
					$this->success($trueStr,U($trueU));
			}
			else
			{
				if ($falseU === 0)
					$this->error($falseStr);
				else
					$this->error($falseStr,U($falseU));
			}
		}
	}
	
	private function isFalse($time,$ok,$falseStr,$falseU)//当$ok为false时进行跳转
	//参数分别为：跳转时间（-1为默认）、判断量、为假提示符，为假跳转U操作参数（字符串）,若没有跳转操作则传0
	{
		if ($time == -1)//默认跳转时间
		{
			if (!$ok)
			{
				if ($falseU === 0)
					$this->error($falseStr);
				else
					$this->error($falseStr,U($falseU));
			}
		}
		else//延时跳转
		{
			if (!$ok)
			{
				$this->assign('waitSecond',$time);
				if ($falseU === 0)
					$this->error($falseStr);
				else
					$this->error($falseStr,U($falseU));
			}
		}
	}
	
	public function index()
	{
		//$this->assign('waitSecond',135);
		
		$dbuser = M("User");
		$condition = NULL;
		$condition['userId'] = session('userId');
		$result = $dbuser->where($condition)->select();
		if($result)
		{
			session('moodValue',$result[0]['moodValue']);
			session('pairId',$result[0]['pairId']);
			session('pairUserId',$result[0]['pairUserId']);
		}
		else
		{
			$this->error('登陆有问题，系统退出后请重新登录','__APP__/Index/logout');
		}
		
		if (session('pairId') == 0)//用户还没有连接
		{
			redirect(U('User/ping'),0);
		}
		
		//下面的操作是建立在 用户已经配对 的前提下的
		$db = M("Pair");
		$condition = NULL;
		$condition['pairId'] = session('pairId');
		$result = $db->where($condition)->select();
		if($result)
		{
			$lowId = $result[0]['lowId'];
			$money = $result[0]['money'];
		}
		else
		{
			$this->error('登陆有问题，系统退出后请重新登录','__APP__/Index/logout');
		}
		
		$db = M("User");
		$condition = NULL;
		$condition['userId'] = session('pairUserId');
		$result = $db->where($condition)->select();
		if($result)
		{
			session('myMoodValue',$result[0]['moodValue']);
			session('pairUserName',$result[0]['userName']);
		}
		else
		{
			$this->error('登陆有问题，系统退出后请重新登录','__APP__/Index/logout');
		}
		
		if ($lowId == 1)//用户还没有创建条约
		{
			redirect(U('User/treaty'),0);
		}
		
		$dbPair = D("Pair");
		$dbPair->init(session("pairId"));
		$tmp = $dbPair->getTargetId();
		if ($tmp == 0)//还没设定目标
		{
			redirect(U('User/target'),0);
		}
		else 
		{
			$dbTarget = D("Target");
			$dbTarget->init($tmp);
			$tmp = $dbTarget->getTarget();
			$target = "当达到".$tmp["tiaojian"]._CURRENCY."时，我们就".$tmp["jiangli"]."!~";
		}
		
		//为统计图表下面的数据做准备
		$dbUser = D("User");
		$dbUser->init(session("userId"));
		$dbLow = D("Low");
		$dbLow->init(session("pairId"));
		$dbPair = D("Pair");
		$dbPair->init(session("pairId"));
		
		$this->assign('View_messageCount',count($dbUser->getBillContent()));
		$this->assign('View_lowCount',count($dbLow->getContentAndScore()));
		$this->assign('View_diaryCount',count($dbPair->getDiaryIdList()) + count($dbPair->getBillContent()));
		$this->assign('View_currency',_CURRENCY);
		$this->assign('View_money',$money);
		$this->assign('View_target',$target);
		$this->assign('View_noteCount',count($dbUser->getNoteContent()));
		
		$this->display();
	}
	
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
	
	public function changeMoodMobile()
	{
		$this->display();
	}
	
	public function toChangeMoodMobile()
	{
		$tmp = $this->_param("radio");
		if (session('myMoodValue') == $tmp)//更新前更新后是一样的值会导致$result返回false，即没有更新
		{
			redirect(U('User/index'),0);
		}
		else
		{
			$condition = NULL;
			//$condition['userId'] = session('pairUserId');
			$condition['moodValue'] = $tmp;
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
	
	public function add()
	{
		$this->assign("_ADD_REMARK",_ADD_REMARK);
		$this->treaty();
	}
	
	public function toAddBySelect()//与toSub对称
	{
		//取出条约
		$data = NULL;
		$dbLow = D("Low");
		$dbLow->init(session("pairId"));
		$list = $dbLow->getContentAndScore();
		
		$radio = $this->_post("radio");
		$data = $list[$radio];

		//更新条约
		$dbBill = D("Bill");
		$dbBill->init(session("userId"),session("pairUserId"),true);
		$this->isOk(-1,$dbBill->insertTempBill($data["content"],$data["score"]),"转账申请成功，等待对方确认","User/index","转账错误，请重试","User/add");
	}
	
	public function toAdd()//与toSub对称
	{
		$dbUser = D("User");
		$dbBill = D("Bill");
		if (isset($_GET["userName"]))
		{
			$re = $dbUser->login($this->_get("userName"),$this->_get("userPassword"));
			if ( ($re == false) || ($re == null) )
			{
				echo "错误的登录";
				return false;
			}
			$dbBill->init($re["userId"],$re["pairUserId"],true);
		}
		else
		{
			$dbBill->init(session("userId"),session("pairUserId"),true);
		}
// 		$dbBill->insertTempBill();
		$this->isOk(-1,$dbBill->insertTempBill(),"转账申请成功，等待对方确认","User/index","转账错误，请重试","User/add");
	}
	
	public function sub()
	{
		$this->assign("_SUB_REMARK",_SUB_REMARK);
		$this->treaty();
	}
	
	public function toSub()//与toAdd对称
	{
		$dbUser = D("User");
		$dbBill = D("Bill");
		if (isset($_GET["userName"]))
		{
			$re = $dbUser->login($this->_get("userName"),$this->_get("userPassword"));
			if ( ($re == false) || ($re == null) )
			{
				echo "错误的登录";
				return false;
			}
			$dbBill->init($re["userId"],$re["pairUserId"],false);
		}
		else
		{
			$dbBill->init(session("userId"),session("pairUserId"),false);
		}
		
		$this->isOk(-1,$dbBill->insertTempBill(),"扣除申请成功，等待对方确认","User/index","转账错误，请重试","User/sub");
	}
	
	public function toSubBySelect()//与toAdd对称
	{
		//取出条约
		$data = NULL;
		$dbLow = D("Low");
		$dbLow->init(session("pairId"));
		$list = $dbLow->getContentAndScore();
	
		$radio = $this->_post("radio");
		$data = $list[$radio];
	
		//更新条约
		$dbBill = D("Bill");
		$dbBill->init(session("userId"),session("pairUserId"),false);
		$this->isOk(-1,$dbBill->insertTempBill($data["content"],$data["score"]),"转账申请成功，等待对方确认","User/index","转账错误，请重试","User/add");
	}
	
	public function friend()
	{
		$this->display();
	}
	
	public function account()
	{
		if (session('pairUserId') == 0)
		{
			redirect(U('User/ping'),0);
		}
		$this->display();
	}
	
	public function toAccount()
	{
		$condition = NULL;
		$condition['userPassword'] = $this->_post("userPassword");
		if ($condition['userPassword'] != "")
		{
			$dbUser = M("User");
			$result = $dbUser->where('userId='.session('userId'))->save($condition);
			if(!$result)
			{
				$this->error('登陆有问题，系统退出后请重新登录','__APP__/Index/logout');
			}
		}
		redirect(U('User/index'),0);
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
	
	public function message()//新消息
	{
		$dbUser = D("User");
		if (isset($_GET["userName"]))
		{
			$re = $dbUser->login($this->_get("userName"),$this->_get("userPassword"));
			if ( ($re == false) || ($re == null) )
			{
				echo "错误的登录";
				return false;
			}
			$dbUser->init($re["userId"]);
			$billIdList = $dbUser->getBillContent();
			
			$dbBill = D("Bill");
			$data = $dbBill->getBillInfo($billIdList);
			
			$count = count($data);
			for ($i = 0; $i < $count; $i++)
			{
				if ($data[$i]["isAdd"] == true)
				{
					$output[$i]["isAdd"] = "1";
					$output[$i]["money"] = $data[$i]["money"];
				}
				else
				{
					$output[$i]["isAdd"] = "0";
					$output[$i]["money"] = (0 - $data[$i]["money"]);
				}
				$output[$i]["remark"] = $data[$i]["remark"];
				$output[$i]["mId"] = $data[$i]["billId"];
				$output[$i]["userStartID"] = $data[$i]["userStartID"];
				$output[$i]["up1Msg"] = $data[$i]["up1Msg"];
				$output[$i]["up2Msg"] = $data[$i]["up2Msg"];
				$output[$i]["upUser"] = $data[$i]["upUser"];
				$output[$i]["toUser1"] = $data[$i]["toUser1"];
				$output[$i]["toUser2"] = $data[$i]["toUser2"];
			}
		}
		else
		{
			$dbUser->init(session("userId"));
			$billIdList = $dbUser->getBillContent();
				
			$dbBill = D("Bill");
			$data = $dbBill->getBillInfo($billIdList);
				
			$count = count($data);
			for ($i = 0; $i < $count; $i++)
			{
			if ($data[$i]["isAdd"] == true)
			{
				$output[$i]["messageTitle"] = "加分订单";
				$output[$i]["messageTitlePic"] = "chat";
				$output[$i]["money"] = $data[$i]["money"];
				$output[$i]["classStr"] = "\"palette palette-peter-river\"";
			}
			else
			{
				$output[$i]["messageTitle"] = "减分订单";
				$output[$i]["messageTitlePic"] = "mail";
				$output[$i]["money"] = $data[$i]["money"];
				$output[$i]["classStr"] = "\"palette palette-alizarin\"";
			}
				$output[$i]["remark"] = $data[$i]["remark"];
				$output[$i]["mId"] = $data[$i]["billId"];
			}
		}
		
		
		
		if (isset($_GET["userName"]))
		{
			header("Content-type: text/html; charset=utf-8");
// 			dump($output);
			echo xuLieHua_2($output);
		}
		else
		{
			$this->assign("list",$output);
			$this->display();
		}
		
	}
	
	public function editMessage()
	{
		$dbUser = D("User");
		$dbBill = D("Bill");
		if (isset($_GET["userName"]))
		{
			$re = $dbUser->login($this->_get("userName"),$this->_get("userPassword"));
			if ( ($re == false) || ($re == null) )
			{
				echo "错误的登录";
				return false;
			}
			$dbBill->init($re["userId"],$re["pairUserId"]);
		}
		else
		{
			$dbBill->init(session("userId"),session("pairUserId"));
		}
		
// 		$dbBill->editTempBill($this->_param("mId"));
		$this->isOk(-1,$dbBill->editTempBill($this->_param("mId")),"修改成功","User/message","修改错误，请重试","User/message");
	}
	
	public function acceptMessage()
	{
		$dbUser = D("User");
		$dbBill = D("Bill");
		if (isset($_GET["userName"]))
		{
			$re = $dbUser->login($this->_get("userName"),$this->_get("userPassword"));
			if ( ($re == false) || ($re == null) )
			{
				echo "错误的登录";
				return false;
			}
			$dbBill->init($re["userId"],$re["pairUserId"]);
			$pairId = $re["pairId"];
		}
		else
		{
			$pairId = session("pairId");
			$dbBill->init(session("userId"),session("pairUserId"));
		}
		
		//$dbBill->acceptTempBill($this->_param("id"),$pairId);
		$this->isOk(-1,$dbBill->acceptTempBill($this->_param("id"),$pairId),"确认成功","User/message","确认错误，请重试","User/message");
	}
	
	public function note()//重要提醒
	{
		//显示未完成的提醒
		$dbUser = D("User");//要对UserModel实例化只能通过D操作
		$dbUser->init(session("userId"));

		$outputList = $dbUser->getNoteContent();
		
		//显示快速选择列表
		$dbPair = D("Pair");
		$dbPair->init(session("pairId"));
		$lowId = $dbPair->getUserLowId();
		
		$dbLow = D("Low");
		$dbLow->init(session("pairId"),$lowId);
		$list = $dbLow->getContentAndScore();
		
		$count = count($list);
		for ($i = 0; $i < $count; $i++)
		{
			$quicklyList[$i] = $list[$i]["content"];
		}
		
		//模板输出
		$this->assign('list',$outputList);//显示未完成对象
		$this->assign('list2',$quicklyList);//显示快速选择
		
		$this->display();
	}
	
	public function addNote()
	{
		$dbUser = D("User");//要对UserModel实例化只能通过D操作
		$dbUser->init(session("userId"));
		
		$this->isFalse(-1,$dbUser->insertNote($this->_post("content")),"添加失败，请重试","User/note");
		redirect(U('User/note'),0);
	}
	
	public function addNoteBySelect()
	{
		//取出条约
		$data = NULL;
		$dbPair = D("Pair");
		$dbPair->init(session("pairId"));
		$lowId = $dbPair->getUserLowId();
		
		$dbLow = D("Low");
		$dbLow->init(session("pairId"),$lowId);
		$list = $dbLow->getContentAndScore();
		
		$radio = $this->_post("radio");
		$data = $list[$radio]["content"];
		
		//更新note
		$dbUser = D("User");
		$dbUser->init(session("userId"));
		$this->isFalse(-1,$dbUser->insertNote($data),"添加失败，请重试","User/note");
		redirect(U('User/note'),0);
	}
	
	public function doneNote()
	{
		//取出条约
		$dbUser = D("User");//要对UserModel实例化只能通过D操作
		$dbUser->init(session("userId"));

		$list = $dbUser->getNoteContent();
		
		//删除掉选中的
		$select = $this->_post("select");
		$selectCount = count($select);
		$listCount = count($list);
		for ($i = 0; $i < $listCount; $i++)
		{
			$tag = true;
			for ($j = 0; $j < $selectCount; $j++)//检查i的值是否在select[]里
			{
				if ($i == $select[$j])
				{
					$tag = false;
					break;
				}
			}
			if ($tag)
			{
				$data[$i] = $list[$i];
			}
		}
		
		//更新note
		/*
		* //因为data是在list范围内组装的，所以必须传list的范围,
		* 不然会出现count($data)范围和条约项的键值不匹配的情况(因为data的下标是select[i])
		*/
		$this->isFalse(-1,$dbUser->regroupNote($data,count($list)),"标记完成失败，请重试","User/note");
		redirect(U('User/note'),0);
	}
	
	public function treaty()//爱情条约
	{
		$dbPair = D("Pair");//要对UserModel实例化只能通过D操作
		$dbPair->init(session("pairId"));
		$lowId = $dbPair->getUserLowId();
		
		if ($lowId == 1)//用户还没有创建条约
		{
			$dbLow = D("Low");
			$result = $dbLow->where("lowId=1")->select();//拷贝内容
			$this->isFalse(-1,$result,"读取爱情条约错误，请重新登录","Index/logout");
			$lowId = $dbLow->insertLow($result);//插入一条新条约
			$this->isFalse(-1,$lowId,"读取爱情条约错误，请重新登录","Index/logout");
			
			if (_DEBUG)
			{
				trace($result,"result");
			}
			
			//更新pair的lowId
			$dbPair = D("Pair");
			$this->isFalse(-1,$dbPair->updateLowId(session("pairId"),$lowId),"读取爱情条约错误，请重新登录","Index/logout");
		
			session("toUserIndex",1);//设置是否第一次到这里
		}

		$dbLow = D("Low");
		$dbLow->init(session("pairId"),$lowId);
		$list = $dbLow->getContentAndScore();
		
		/*
		 * 这段代码是条约显示时，先显示左边，后显示右边的代码
		 * 
		//$count+1才是数量
		if ( ($count == 0) && ($list == NULL) )
		{
			$leftLen = 0;
			$count = -1;
		}
		else
		{
			if ( ($count + 1) % 2 == 0)//能被2整除
				$leftLen = ($count + 1) / 2;
			else
				$leftLen = ($count / 2) + 1;//让左边多
		}
		
		if (_DEBUG)
		{
			trace($leftLen,"leftLen");
			trace($count,"count");
		}
		
		for ($i = 0; $i < $leftLen; $i++)
		{
			$leftList[$i] = $list[$i]["content"]._SELECT_CONTENT_BREAK_FLAG.$list[$i]["score"]._CURRENCY."，没做到-".$list[$i]["score"]._CURRENCY;
		}
		for ($i = $leftLen; $i <= $count; $i++)//count+1才是数量，所以要<=
		{
			$rightList[$i] = $list[$i]["content"]._SELECT_CONTENT_BREAK_FLAG.$list[$i]["score"]._CURRENCY."，没做到-".$list[$i]["score"]._CURRENCY;
		}
		
		//输出
		$this->assign('list1',$leftList);//左边
		$this->assign('list2',$rightList);//右边
		$this->assign('leftLen',$leftLen);//中断点
		*/
		$count = count($list);
		for ($i = 0; $i < $count; $i++)
		{
			$outputList[$i] = $list[$i]["content"]._SELECT_CONTENT_BREAK_FLAG.$list[$i]["score"]._CURRENCY."，没做到-".$list[$i]["score"]._CURRENCY;
		}
		$this->assign('list',$outputList);
		
		$this->display();
	}
	
	public function selectTreaty()//选择条约
	{
		/*
		 * 下面这段代码是根据value的值进行解析select[]，这样做会导致顺序不确定
		 *
		$select = $this->_post("select");
		$selectCount = count($select);
		$data = NULL;
		$data["content"] = "";
		$data["score"] = "";
		for ($i = 0; $i < $selectCount; $i++)
		{
			//解析被选中的条约内容
			$breakPoint = strpos($select[$i],_SELECT_CONTENT_BREAK_FLAG);//内容后的中断符位置
			$scoreBehindBreak = strpos($select[$i],_CURRENCY);//分数后的中断符位置
			$scoreSt = $breakPoint + _SELECT_CONTENT_BREAK_FLAG_STRLEN;//分数的开始处
			if ($data["content"] == "")
				$data["content"] = substr($select[$i],0,$breakPoint);
			else
				$data["content"] = $data["content"] . _SPECIAL_END_FLAG . substr($select[$i],0,$breakPoint);
			if ($data["score"] == "")
				$data["score"] = substr($select[$i],$scoreSt,$scoreBehindBreak - $scoreSt);
			else
				$data["score"] = $data["score"] . _SPECIAL_END_FLAG . substr($select[$i],$scoreSt,$scoreBehindBreak - $scoreSt);
		}
		
		//更新条约	
		$dbLow = D("Low");
		$this->isFalse(-1,$dbLow->updateRecord(session("pairId"),$data),"更新失败，请重试","User/treaty");
		$this->isOk(0,true,0,"User/treaty",0,"User/treaty");
		*/
		
		
		/*
		 * 这里是根据数据库解析select[]，即value的值是数字
		 */
		
		//取出条约
		$data = NULL;
		$dbLow = D("Low");
		$dbLow->init(session("pairId"));
		$list = $dbLow->getContentAndScore();
		
		$select = $this->_post("select");
		$selectCount = count($select);
		for ($i = 0; $i < $selectCount; $i++)
		{
			$data[$select[$i]] = $list[$select[$i]];
			if (_DEBUG)
			{
				echo $select[$i];
			}
		}
		ksort($data,SORT_NUMERIC);//$select[$i]是乱序，导致顺序不对，但是key值的顺序即是当前顺序
		if (_DEBUG)
		{
			dump($data);
		}
		
		//更新条约
		/*
		 * //因为data是在list范围内组装的，所以必须传list的范围,
		 * 不然会出现count($data)范围和条约项的键值不匹配的情况(因为data的下标是select[i])
		*/
		$this->isFalse(-1,$dbLow->updateRecord($data,count($list)),"更新失败，请重试","User/treaty");
		if (session('?toUserIndex'))
		{
			session('toUserIndex',null);
			$this->isOk(0,true,0,"User/index",0,"User/treaty");
		}
		else
			$this->isOk(0,true,0,"User/treaty",0,"User/treaty");
	}
	
	public function newTreaty()//添加新条约的处理
	{
		$dbLow = D("Low");
		$dbLow->init(session("pairId"));
		
		$content = $dbLow->getOriginContent();
		$score = $dbLow->getOriginScore();
		$data = NULL;
		$data["lowId"] = $dbLow->getLowId();
		$data["content"] = $content._SPECIAL_END_FLAG.$this->_post("content"); //默认最后一条尾部没有结束标志
		$data["score"] = $score._SPECIAL_END_FLAG.$this->_post("score");
		$this->isOk(0,$dbLow->save($data),0,"User/treaty",0,"User/treaty");
	}
	
	public function diary()//爱情账户明细
	{
		$dbPair = D("Pair");
		$dbPair->init(session("pairId"));
		$billList = $dbPair->getAllInfoFromBillIdList();
		$diaryList = $dbPair->getAllInfoFromDiaryIdList();
		//dump($billList);
		//dump($diaryList);
		
		//两个进行合并，按时间归并排序
		$billListCount = count($billList);
		$diaryListCount = count($diaryList);
		$dataCount = 0;
		$i = 0;
		$j = 0;
		while (($i < $billListCount) && ($j < $diaryListCount))
		{
			while (($i < $billListCount) && ($billList[$i]["billTime"] < $diaryList[$j]["diaryTime"]))
			{
				$data[$dataCount] = $billList[$i];
				$i++;
				$dataCount++;
			}
			while (($j < $diaryListCount) && ($billList[$i]["billTime"] >= $diaryList[$j]["diaryTime"]))//点滴在相等的情况下优先
			{
				$data[$dataCount] = $diaryList[$j];
				$j++;
				$dataCount++;
			}
		}
		while ($i < $billListCount)
		{
			$data[$dataCount] = $billList[$i];
			$i++;
			$dataCount++;
		}
		while ($j < $diaryListCount)//点滴在相等的情况下优先
		{
			$data[$dataCount] = $diaryList[$j];
			$j++;
			$dataCount++;
		}
		//dump($data);
		
		//对输出进行处理
		for ($i = 0; $i < $dataCount; $i++)
		{
			if (($data[$i]["billId"] == NULL) || ($data[$i]["billId"] ==  ""))//是diary
			{
				$output[$i]["gouxuan"] = "\"todo-done\"";//是否被选上（绿色）
				$output[$i]["icon"] = "\"todo-icon fui-heart\"";
				$output[$i]["contant"] = $data[$i]["content"];
				$output[$i]["score"] = $data[$i]["diaryTime"];
			}
			else//是bill
			{
				if ($data[$i]["isAdd"] == true)
				{
					$output[$i]["icon"] = "\"todo-icon fui-plus\"";
					$output[$i]["score"] = "现金流：+".$data[$i]["money"]."    日期：".$data[$i]["billTime"];
				}
				else
				{
					$output[$i]["icon"] = "\"todo-icon fui-cross\"";
					$output[$i]["score"] = "现金流：".(0 - $data[$i]["money"])."    日期：".$data[$i]["billTime"];
				}
				$output[$i]["contant"] = $data[$i]["remark"];
			}
		}
		
		$this->assign("list",$output);
		$this->display();
	}
	
	public function displayAddDiary()
	{
		$this->display();
	}
	
	public function addDiary()
	{
		$dbDiary = D("Diary");
		$dbDiary->create();
		
		//更新pair表的diaryIdList
		$dbPair = D("Pair");
		$dbPair->init(session("pairId"));
		
		$this->isOk(-1,$dbPair->insertDiaryId($dbDiary->add()),"记录成功","User/index","发布错误，请重试","User/displayAddDiary");
	}
	
	public function target()
	{
		$this->display();
	}
	
	public function newTarget()
	{
		$dbTarget = D("Target");
		$dbTarget->create();
		
		$dbPair = D("Pair");
		$dbPair->init(session("pairId"));
		$this->isOk(-1,$dbPair->insertTarget($dbTarget->add()),"目标设置成功","User/index","目标设置失败，请重试","User/target");
	}
}