<?php
class UserModel extends Model {

	/**
	 * 接受订单所产生的信息更改
	 * @param int $billID
	 * @return boolean;是否成功
	 */
	public function acceptBill($billID)
	{
		$re = null;
		$re	=	D("Bill")->where(array("id"=>$billID))->find();
		$user = $this->where("uid=".$re["toID"])->find();
		
		if ( (($re["msgPre"] != "") || ($re["msgPre"] != null)) && ($re["scoreLast"] != $re["scorePre"]) )
			return false;
		
		if ($ew["isAdd"])
			$user["score"] += $re["scoreLast"];
		else 
			$user["score"] -= $re["scoreLast"];
		$tmp = $this->save($user);
		if ( ($tmp === null) || ($tmp === false) )
			return false;
		else
			return true;
	}
}
?>