<?php
include(CONF_PATH."MyConfigINI.php");
include(LIB_PATH."CommonAction.php");

class IndexAction extends CommonAction 
{
    public function index()
    {
    	header("Content-Type:text/html;charset=utf-8");
    	echo "这里是服务器端，你不应该访问这里的，你知道的太多了";
	}
	
	/**
	 * 为了登录设置session
	 * @param		uid;
	 * @param		name;用户名
	 */
	private function setSessionForLogin($uid,$name)
	{
		//设置session
		session('uid',uid);
		session('userName',name);
	}
	
	
	/**
	 * 登录函数
	 * @method	param
	 * @param	name;用户名
	 * @param	password;密码
	 * @return	登录成功返回序列化后的(自己id，自己昵称，对方id，对方昵称)；登录失败false
	 */
	public function login()//判断登录是否成功
	{
		$condition = null;
    	$condition['name'] = $this->_param('name');
    	$condition['password'] = $this->_param('password');
    	$result = D("User")->where($condition)->find();
    	if($result)
    	{
    		$this->setSessionForLogin($result['uid'],$result['name']);

    		$tmp = null;
    		$partnerID	=	$this->getPartnerID($result["uid"]);
    		$tmp = D("User")->where(array("uid"=>$partnerID))->find();
    		
    		$re = null;
    		$re["uid"]		=	$result["uid"];
    		$re["nickName"]	=	$result["nickName"];
    		$re["partnerID"]=	$tmp["uid"];
    		$re["partnerNickName"]	=	$tmp["nickName"];
    		
    		echo $this->serializeWithSlef($re,_SPECAL_BREAK_FLAG);
    	}
    	else
    	{
    		echo "false";
    	}
	}
	
	/**
	 *注册函数
	 *@method	param
	 *@param	name;用户名
	 *@param	password；密码
	 *@param	nickName;昵称
	 *@return		用户名重复、注册失败、注册成功
	 */
	public function sign()
	{
		header("Content-Type:text/html;charset=utf-8");
		//准备注册内容
		$data = null;
		$data["name"] = $this->_param("name");
		$data["password"] = $this->_param("password");
		$data["nickName"] = $this->_param("nickName");
		$data["score"] = 0;
		$data["pairID"] = 0;
		for ($i = 0; $i < _CARD_NUM; $i++)
			$tmp .= "0"._SPECAL_BREAK_FLAG;
		$data["cardOwn"]	=	$tmp;
		$data["cardAble"]	=	$tmp;
		$data["moodValue"] = "未设置";
		
		if ( D("User")->where(array("name"=>$data["name"]))->find() )
		{
			exit("用户名重复");
		}
			
		$userID = D("User")->add($data);
		if(!$userID)
		{
			exit("注册失败");
		}
		else
		{
			$this->setSessionForLogin($userID,$data["name"]);
			exit("注册成功");
		}
	}
	
	/**
	 *退出
	 *@method	param
	 *@return	非法登录、退出失败、退出成功
	 */
	public function logout()
	{
		header("Content-Type:text/html;charset=utf-8");
		//判断session是否存在
		if (!session('?uid'))
		{
			exit("非法登录");
		}
	
		//删除session
		session('userName',null);
		session('uid',null);
		
		//再次判断session是否存在
		if ( (session('?userName')) || (session('?uid')) )
		{
			exit("退出失败");
		}
		else
		{
			exit("退出成功");
		}
	}
}