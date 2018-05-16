<?php
namespace Home\Model;
use Think\Model;
class UserModel extends Model{
	protected $_validate = array(
		array('username','require','账号不能空',1),
		array('username','','帐号已经存在',0,'unique',1),
		array('username','/^1[34578]\d{9}$/','请输入正确手机号'),
		array('code','get_code','邀请码无效',0,'callback'),
		array('name','','昵称已经存在',0,'unique',1),
		// array('number','/^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}([0-9]|X)$/','身份证格式错误'),
	);
	public function get_code($data){
		$list = M('user')->select();
    	foreach($list as $v){
    		if($data == $this->encrypt($v['id'])){
    			$id = $v['id'];
    		}

    	}
           if($id['is_stop']==1){
           	return false;
           }
    	session('code',$id);
    	return $id ? $id : false;
	}

	public function encrypt($uid){
    	$str = md5($uid.KEY);
        preg_match_all("/\d+/",$str,$arr);
        $num = implode('', $arr[0]);
        return msubstr($num,0,6);
    }

	protected $_auto = array(
		array('add_time','time',3,'function'),
		array('entry','get_rand_str',3,'function'),
		array('password','set_password',3,'callback'),
		array('psw2','set_psw2',3,'callback'),
	);
	public function set_password($data){
		return $password = md5(md5($data).md5(session('entry')));
	}
	public function set_psw2($data){
		return $password = md5($data);
	} 
}