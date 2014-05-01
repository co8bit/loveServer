<?php
class UserModel extends Model {

	// 自动验证设置
	protected $_validate = array(
			array('userName', 'require', '用户名不能为空！'),
			array('userName','','用户名已经存在！',0,'unique',Model::MODEL_BOTH), //验证name字段是否唯一
			array('userPassword', 'require', '密码不能为空！', 0),
			array('userPassword2', 'require', '请输入第二遍密码', 0),
			array('userPassword','userPassword2','两次输入的密码不一样',0,'confirm',Model::MODEL_BOTH), // 验证确认密码是否和密码一致
			
			/*
			 * 自动验证的模板
			 *
			array('verify','require','验证码必须！'), //默认情况下用正则进行验证
			array('name','','帐号名称已经存在！',0,'unique',1), // 在新增的时候验证name字段是否唯一
			array('value',array(1,2,3),'值的范围不正确！',2,'in'), // 当值不为空的时候判断是否在一个范围内
			array('repassword','password','确认密码不正确',0,'confirm'), // 验证确认密码是否和密码一致
			array('password','checkPwd','密码格式不正确',0,'function'), // 自定义函数验证密码格式
			*/
	);
	
	private $userId = -1;//用户Id
	private $billOrignContent = NULL;//未解析的原始内容，形如：string字符串
	private $billContent = NULL;//billId数组，形如：$billContent[i] = billId;
	private $NoteOrignContent = NULL;
	private $NoteContent = NULL;
	
	public function init($userId)//传入UserId
	{
		$this->userId = $userId;
	}
	
	public function login($userName,$userPassword)
	/*
	 * 判断用户名和密码是否能登录
	* @param string $userPassword 用户密码
	* @return 数据库返回的结果集，数组大小应为1。外面调用形如$re[0][]
	*/
	{
		$condition['userName'] = $userName;
		$condition['userPassword'] = $userPassword;
		$tmp = $this->where($condition)->select();
		return $tmp[0];
	}
	
	/*
	 * 得到指定用户的用户信息
	* @param	string $name;用户名
	* @return	array;
	* 				查询成功返回用户所有字段的数组
	* 				没查到返回null
	* 				查询错误返回false
	*/
	public function getUserInfo($name)
	{
		$tmp = $this->where("userName=\"".$name."\"")->select();
		if ( ($tmp === false) || ($tmp === null) )
			return $tmp;
		else
			return $tmp[0];
	}
	
	public function getOriginBillContent()//得到原始内容
	{
		if ($this->billOrignContent === NULL)
		{
			$result = NULL;
			$result = $this->where("userId=$this->userId")->select();
			if (!$result)
				return -1;
			$this->billOrignContent = $result[0]["tempBillContent"];
		}
		return $this->billOrignContent;
	}
	
	public function getBillContent()
	{
		if ($this->billContent === NULL)
		{
			//得到原样
			$this->getOriginBillContent();
	
			//解析content
			$st = 0;
			$count = 0;
			$contentLen = strlen($this->billOrignContent);
			while ($st < $contentLen)
			{
				$breakPoint = strpos($this->billOrignContent,_SPECAL_BILL_END_FLAG,$st);
				if (!$breakPoint)//到字符串最后一个内容了
				{
					$this->billContent[$count] = substr($this->billOrignContent,$st);
					break;
				}
				$this->billContent[$count] = substr($this->billOrignContent,$st,$breakPoint - $st);
				$count++;
				$st = $breakPoint + _SPECAL_BILL_END_FLAG_STRLEN;
			}
		}
	
		return $this->billContent;
	}
	
	public function updateUserBillContent($billId)//给用户插入一条账单,传入参数：billId
	{
		$this->getOriginBillContent();
	
		if ($this->billOrignContent == "")
			$this->billOrignContent = "$billId";
		else
			$this->billOrignContent = $this->billOrignContent . _SPECAL_BILL_END_FLAG . $billId;
	
		$data = NULL;
		$data["userId"] = $this->userId;
		$data["tempBillContent"] = $this->billOrignContent;
	
		return $this->save($data);
	}
	
	public function deleteOneBillInContent($billId)//从用户的billContent中删除一个账单id
	{
		$this->getBillContent();
		$count = count($this->billContent);
		for ($i = 0; $count; $i++)
		{
			if ($this->billContent[$i] == $billId)//把当前值删掉
			{
				for ($j = $i + 1; $j < $count; $j++)
				{
					$this->billContent[$j - 1] = $this->billContent[$j];
				}
				$this->billContent[$j-1] = NUlL;//这条语句并不能把最后一个元素删除了，count($this->billContent)之后还是$count（没减一）
				$count--;//上面这条语句并不能把最后一个元素删除了，所以需要减掉
				break;
			}
		}
	
		/*
		* 下面开始，$this->billContent的长度必须用$count，因为$this->billContent数组删除掉了一个值。
		*/
	
		//更新tempBill
		$this->billOrignContent = "";
		for ($i = 0; $i < $count; $i++)//必须用$count，因为$this->billContent数组删除掉了一个值。这里的count已经减1了
		{
			$tmp = $this->billContent[$i];
			if ($this->billOrignContent == "")
				$this->billOrignContent = "$tmp";
			else
				$this->billOrignContent = $this->billOrignContent . _SPECAL_BILL_END_FLAG . $tmp;
		}
		$data = NULL;
		$data["userId"] = $this->userId;
		$data["tempBillContent"] = $this->billOrignContent;
		return $this->save($data);
	}
	
	public function getOriginNoteContent()//得到原始内容
	{
		if ($this->NoteOrignContent === NULL)
		{
			$result = NULL;
			$result = $this->where("userId=$this->userId")->select();
			if (!$result)
				return -1;
			$this->NoteOrignContent = $result[0]["note"];
		}
		return $this->NoteOrignContent;
	}
	
	public function getNoteContent()
	{
		if ($this->NoteContent === NULL)
		{
			//得到原样
			$this->getOriginNoteContent();
	
			//解析content
			$st = 0;
			$count = 0;
			$contentLen = strlen($this->NoteOrignContent);
			while ($st < $contentLen)
			{
				$breakPoint = strpos($this->NoteOrignContent,_SELECT_NOTE_BREAK_FLAG,$st);
				if (!$breakPoint)//到字符串最后一个内容了
				{
					$this->NoteContent[$count] = substr($this->NoteOrignContent,$st);
					break;
				}
				$this->NoteContent[$count] = substr($this->NoteOrignContent,$st,$breakPoint - $st);
				$count++;
				$st = $breakPoint + _SELECT_NOTE_BREAK_FLAG_STRLEN;
			}
		}
	
		return $this->NoteContent;
	}
	
	public function insertNote($new)//$new为新添加的内容
	{
		$this->getOriginNoteContent();
		
		if ($this->NoteOrignContent == "")
			$this->NoteOrignContent = "$new";
		else
			$this->NoteOrignContent = $this->NoteOrignContent . _SELECT_NOTE_BREAK_FLAG . $new;
		
		$data = NULL;
		$data["userId"] = $this->userId;
		$data["note"] = $this->NoteOrignContent;
		
		return $this->save($data);
	}
	
	public function updateNote($tmp,$count)//传入的参数是一个2维数组，如：$tmp[i] = note
	{
		$outputData["note"] = "";
		//扔到data里
		for ($i = 0; $i < $count; $i++)
		{
			if ( $tmp[$i] == NULL )
			{
				continue;
			}
			
			//添加中断标志
			if ($outputData["note"] == "")
				$outputData["note"] = $tmp[$i];
			else
				$outputData["note"] = $outputData["note"] . _SELECT_NOTE_BREAK_FLAG . $tmp[$i];
		}
		
		$outputData["userId"] = $this->userId;
		//更新数据库
		return $this->save($outputData);
	}
	
	public function regroupNote($tmp,$count)
	{
		return $this->updateNote($tmp,$count);
	}
	
	/*
	public function getUserIdForDebug()
	{
		return $this->userId;
	}
	*/
}
?>