<?php
include(CONF_PATH."MyConfigINI.php");
include(LIB_PATH."CommonAction.php");

class IndexAction extends CommonAction 
{
    public function index()
    {
    	$this->assign('View_SOFTNAME',_SOFTNAME);
    	$this->assign('View_VERSION',_VERSION);
//     	echo CONF_PATH."MyConfigINI.php";
    	
    	$this->display();
	}
	
	private function isLogin()//判断是否已经登陆
	{
		if (session('?userName'))//如果用户已经存在
		{
			redirect(U('User/index'),0);//不能写成$this->redirect(U('User/index'),0);不然地址会变成：http://项目名a/模块名b/操作c/项目名a/模块名b/操作c/..
		}
	}
	
	public function login()
	{
		$this->isLogin();
		$this->display();
	}
	
	public function sign()
	{
		$this->isLogin();//无论isLogin()是不是private，调用的时候都要加$this->。？因为php不能像c++那样可以直接用，必须要指出是谁的
		$this->display();
	}
	
	public function toLogin()//判断登录是否成功
	{
		$dbuser = M("User");//NOTE:thinkphp是用参数名确定是哪个数据库的，比如M("User")的User
    	$condition['userName'] = $this->_post('userName');
    	$condition['userPassword'] = $this->_post('userPassword');
    	$result = $dbuser->where($condition)->select();
    	if($result)
    	{
    		//设置session。session在toLogin和toSign中有设置
    		session('_APPNAME',_SOFTNAME);
    		session('userName',$result[0]['userName']);
    		session('userPower',$result[0]['userPower']);
    		session('userId',$result[0]['userId']);
    		
    		//$this->success('登陆成功','__APP__/User/index');
    		$this->success('登陆成功',U('User/index'));//U方法用于完成对URL地址的组装，特点在于可以自动根据当前的URL模式和设置生成对应的URL地址
    	}
    	else
    	{
    		$this->error('登录失败');
    	}
	}
	
	public function toSign()//判断是否注册成功
	{
		//$this->assign('waitSecond',135);
		
		$dbUser = D("User");
		//trace($fields = $dbUser->getDbFields(),"my output:");     //for debug
		$dbUser->create();
		$userName = $dbUser->userName;
		$userPowere = $dbUser->userPowere;
		$dbUser->userPower = "00000000";
		$dbUser->moodValue = "未设置";
		$userId = $dbUser->add();
		if(!$userId)//添加失败  TODO
		{
			if ( $dbUser->getError() == '非法数据对象！')//! 号后面有个空格
				$this->error('注册失败：'.'有未填项');
			else
				$this->error('注册失败：'.$dbUser->getError());
		}
		else
		{
			session('_APPNAME',_SOFTNAME);
			session('userName',$userName);//////////////////这里进行了session
			session('userPower',$userPowere);
			session('userId',$userId);
			$this->success('注册成功',U('User/index'));//U函数必须要指定具体操作，不然会出错（即,不能U('User')）
		}
	}
	
	public function logout()//安全退出
	{
		//判断session是否存在
		if (!session('?userName'))
		{
			$this->error('非法登录',U('Index/login'));
		}
	
		//删除session
		session('userName',null);
		session('userPower',null);
		session('_APPNAME',null);
		session('userId',null);
		session('moodValue',null);
		session("toUserIndex",null);
		
		//再次判断session是否存在
		if ( (session('?userName')) || (session('?userPower')) || (session('?userId')) )
			$this->error('退出失败');
		else
			$this->success('退出成功',U('Index/index'));////////////////////////////////////////////////////////
	}
	
	public function help()
	{
		redirect(U('@www.co8bit.com'),0);
	}
}