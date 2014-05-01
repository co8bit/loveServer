<?php
class BillModel extends Model {

	private $nowUserId = -1;//当前操作人Id
	private $pairUserId = -1;//当前操作人配偶Id
	private $isAdd = true;//是加分订单还是减分订单
	
	// 自动验证设置
	protected $_validate = array(
			array('money', 'require', '转账金额不能为空！'),
			array('money', 'number', '转账金额必须为数字且为整数！'),
	);
	
	//自动填充
	protected $_auto = array (
			array('billTime','mydate',Model:: MODEL_BOTH,'callback'),
	);
	
	protected function mydate()
	{
		return date("Y-m-d H:i:s");
	}
	
	public function init($nowUserId,$pairUserId = -1,$isAdd = true)//初始化，传入isAdd,当前登录id.伙伴id可以不传入
	{
		$this->isAdd = $isAdd;
		$this->nowUserId = $nowUserId;
		$this->pairUserId = $pairUserId;
	}
	
	protected function _before_insert(&$data,$options)
	{
		//这里$this->data还没有值
		if ($data["remark"] == "")
		{
			if ($this->isAdd)
			{
				$data["remark"] = _ADD_REMARK;
			}
			else
			{
				$data["remark"] = _SUB_REMARK;
			}
		}
		$data["billTime"] = date("Y-m-d H:i:s");
	}
	
	public function insertTempBill()//插入一个临时账单
	//TODO:public function insertTempBill($remark = NULL,$money = NULL)//插入一个临时账单
	{
		//准备数据
		if ($remark === NULL)
		{
// 			$this->create();
// 			$tmpData = $this->data;
//不知道为什么$this->create()建立不了get的数据

			$tmpData = $_GET ;

			$data = NULL;
			$data["isAdd"] = $this->isAdd;
			$data["remark"] = $tmpData["remark"];
			$data["money"] = $tmpData["money"];
			
			//新增信息
			$data["userStartID"] =$this->nowUserId;
			$data["up1Msg"] = "";
			$data["upUser"] =$data["userStartID"];
			$data["toUser1"] = $tmpData["toUser1"] ;
			$data["toUser2"] =$tmpData["toUser2"]  ;
			$billId = $this->add($data);
		}
		else
		{
			/*
			$data = NULL;
			$data["remark"] = $remark;
			$data["money"] = $money;
			$data["isAdd"] = $this->isAdd;
			$billId = $this->add($data);
			*/
		}
		
		/*
		 * 更新账户billContent
		 */
		 //给对方插入一个账单
		$dbPairUser = D("User");
		$dbPairUser->init($this->pairUserId);
		$dbPairUser->updateUserBillContent($billId);
		
		//给自己插入一个账单
		$dbUser = new UserModel();//因为D方法在已经存在模型类的时候不创建一个新的，所以要用new
		$dbUser->init($this->nowUserId);
		return $dbUser->updateUserBillContent($billId);
	}
	
	public function editTempBill($billId)//用户修改账单，并转给配偶申请审核。传入billId
	{
		$dbUser = D("User");
		$dbUser->init($this->nowUserId);
// 		$this->create();
// TODO:get得不到
		
		//更新bill表
		$data["billId"] = $billId;
		$data["up1Msg"] =  $_GET["up1Msg"] ;
		$data["remark"] = $_GET["remark"];
		$data["money"] = $_GET["money"];
		$data["upUser"] =$_GET["upUser"];
		$data["toUser1"] = $_GET["toUser1"];
		$data["toUser2"] =$_GET["toUser2"];
		$re = $this->save($data);
		if ( ($re === false) || ($re === null) )
			return false;
		else
			return true;
		
		/*不用删除对方账户中的billId
		//更新user表中的tempBillContent内容
		$dbUser->deleteOneBillInContent($billId);//删除本账户的内容
		//创建关于配偶的user操作
		$dbPairUser = new UserModel();//因为D方法在已经存在模型类的时候不创建一个新的，所以要用new
		$dbPairUser->init($this->pairUserId);
		return $dbPairUser->updateUserBillContent($billId);//插入到配偶账户中去
		*/
	}
	
	public function acceptTempBill($billId,$pairId)//接收订单
	{
		//找到billId的详细信息
		$info = NULL;
		$info = $this->where("billId=".$billId)->select();
		
		//更新pair中的确认账单信息
		$dbPair = D("Pair");
		$dbPair->init($pairId);
		$dbPair->updateBillArray($info[0]["isAdd"],$billId,$info[0]["money"]);
		
		/*
		 * 从user的tempBillContent中删除billId
		 */
		//删除自己的
		$dbUser = D("User");
		$dbUser->init($this->nowUserId);
		$dbUser->deleteOneBillInContent($billId);
		
		//删除别人的
		$dbPairUser = new UserModel();//因为D方法在已经存在模型类的时候不创建一个新的，所以要用new
		$dbPairUser->init($this->pairUserId);
		return $dbPairUser->deleteOneBillInContent($billId);
	}
	
	public function getBillInfo($billIdList)//从一个一维数组中获取billId，然后返回billId的详细信息
	//不需要init
	{
		$info = NULL;
		for ($i = 0; $i < count($billIdList); $i++)
		{
			$tmp = $this->where("billId=".$billIdList[$i])->select();
			$info[$i] = $tmp[0];
		}
		return $info;//info[i]["money"] = money
	}
}
?>