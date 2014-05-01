<?php
Class CommonAction extends Action
{
	/**
	 * 登录后得到的该用户的用户ID
	 */
	protected $userID = null;
	
	/**
	 * 登录后得到的配偶ID
	 */
	protected $pairUserID = null;

	/**
	 * 登录后得到的pairID
	 */
	protected $pairID = null;
	
	Public function _initialize()
	{
		//移动设备浏览，则切换模板
		if (ismobile()) 
		{
			//设置默认默认主题为 Mobile
			C('DEFAULT_THEME','Mobile');
			C('TMPL_ACTION_ERROR','Tpl/Mobile/dispatch_jump.php');
			C('TMPL_ACTION_SUCCESS','Tpl/Mobile/dispatch_jump.php');
		}
		
		
		//登录
		$dbUser = D("User");
		$re = $dbUser->login($this->_get("userName"),$this->_get("userPassword"));
		if ( ($re == false) || ($re == null) )
		{
			echo "错误的登录";
			return false;
		}
		else
		{
			$this->userID = $re["userId"];
			$this->pairUserID = $re["pairUserId"];
			$this->pairID = $re["pairId"];
		}
	}
}
?>