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
	
	/**
	 * 序列化一维数组，给数组添加自定义的中断标记并转换成字符串。
	 * @param	array $data;需要转换的原始数据
	 * 					string $tag;中断标记
	 * @return	string;转换完成后的字符串
	 * @note		最后一个元素末尾有中断标记
	 */
	public function serializeWithSlef($data,$tag)
	{
		$re = "";
		foreach($data as $value)
		{
			$re .= $value.$tag;
		}
		return $re;
	}
	
	/**
	 * 序列化二维数组，给数组添加自定义的中断标记并转换成字符串。
	 * @param	array $data;需要转换的原始数据
	 * 					string $tag;中断标记
	 * @return	string;转换完成后的字符串
	 * @note		最后一个元素末尾有中断标记
	 */
	public function serializeTwoWithSlef($data,$tag)
	{
		$re = "";
		foreach($data as $value)
		{
			foreach ($value as $valueIn)
				$re .= $valueIn.$tag;
		}
		return $re;
	}
}
?>