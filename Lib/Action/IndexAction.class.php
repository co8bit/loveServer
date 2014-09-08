<?php
include(CONF_PATH."MyConfigINI.php");
include(LIB_PATH."CommonAction.php");

class IndexAction extends CommonAction 
{
    public function index()
    {
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
	 * @method	get
	 * @param	name;用户名
	 * @param	password;密码
	 * @return	登录成功；登录失败
	 */
	public function login()//判断登录是否成功
	{
		$condition = null;
    	$condition['name'] = $this->_get('name');
    	$condition['password'] = $this->_get('password');
    	$result = D("User")->where($condition)->find();
    	if($result)
    	{
    		$this->setSessionForLogin($result['uid'],$result['name']);
    		echo '登录成功';
    	}
    	else
    	{
    		echo "登录失败";
    	}
	}
	
	/**
	 *注册函数
	 *@method	get
	 *@param	name;用户名
	 *@param	password；密码
	 *@param	nickName;昵称
	 *@return		用户名重复、注册失败、注册成功
	 */
	public function sign()
	{
		$data = null;
		$data["name"] = $this->_get("name");
		$data["password"] = $this->_get("password");
		$data["nickName"] = $this->_get("nickName");
		$data["score"] = 0;
		$data["pairID"] = 0;
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
	 *@method	get
	 *@return		非法登录、退出失败、退出成功
	 */
	public function logout()
	{
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