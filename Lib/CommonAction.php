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
	/*
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
	}*/
	
	/**
	 * 序列化一维数组，给数组添加自定义的中断标记并转换成字符串。
	 * @param	array $data;需要转换的原始数据
	 * 					string $tag;中断标记
	 * @return	string;转换完成后的字符串
	 * @note		最后一个元素末尾有中断标记
	 */
	public function serializeWithSlef($data,$tag = _SPECAL_BREAK_FLAG)
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
	public function serializeTwoWithSlef($data,$tag = _SPECAL_BREAK_FLAG)
	{
		$re = "";
		foreach($data as $value)
		{
			foreach ($value as $valueIn)
				$re .= $valueIn.$tag;
		}
		return $re;
	}
	
	/**
	 * 得到对方ID
	 * @param $uid;用户id
	 * @return 对方ID或者错误;
	 */
	protected  function getPartnerID($uid)
	{
		$tmp = null;
		$tmp = D("Pair")->where(array("user1ID"=>$uid))->find();
		if ($tmp)
			return $tmp["user2ID"];
		else
		{
			$tmp = D("Pair")->where(array("user2ID"=>$uid))->find();
			if ($tmp)
			{
				return $tmp["user1ID"];
			}
			else
				return false;
		}
	}
	
	/**
	 * 把字符串按照分隔符分开成数组，要求字符串结尾处要有分隔符
	 * @param	$tmp;要分割的字符串
	 * @return	array[i] = 内容
	 */
	protected function stringToArray($tmp)
	{
		$re		=		null;
		
// 		if ( ($tmp == "") || ($tmp == null) )//如果该用户还没有初始化卡片列表，则初始化
// 		{
// 			for ($i = 0; $i < _CARD_NUM; $i++)
// 				$tmp .= "0"._SPECAL_BREAK_FLAG;
// 		}
	
		$re	= explode(_SPECAL_BREAK_FLAG,$tmp);
		array_pop($re);//弹掉最后一个空的项（因为输入末尾带一个多余的分隔符）
		
		return	$re;
	}
}
?>