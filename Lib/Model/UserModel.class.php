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
		$user = $this->where("id=".$re["toID"])->find();
		$user["score"] += $re["scoreLast"];
		$tmp = $this->save($user);
		if ( ($tmp === null) || ($tmp === false) )
			return false;
		else
			return true;
	}
}
?>