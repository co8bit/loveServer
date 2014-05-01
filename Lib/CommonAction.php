<?php
Class CommonAction extends Action
{
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
		//............你的更多代码.......
	}
}
?>