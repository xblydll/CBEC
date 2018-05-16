<?php
namespace Admin\Controller;
use Think\Controller;
class IndexController extends Controller {
    public function index(){
        if(session('?adminid')){
            $this->redirect('Main/index');
        }
        $online = cookie('online');
        if(!empty($online)){
            $info = authcode($online,'DECODE');
            if(!empty($info)){
                $user = explode('|', $info);
                $this->assign('user',$user);
            }
        }
        $this->display();
    }
    public function verify(){
    	ob_end_clean();
        $verify = new \Think\Verify();
        $verify->imageW = 150;
        $verify->fontsize = 18;
        $verify->length = 3;
        $verify->useNoise = false;
        $verify->entry(1);
    }
    public function login(){
        $this->assign('name','lty');
    	$username = I('username');
    	$password = I('password');
    	$code = I('code');
    	$online = I('online',0);
    	if(empty($username)){
    		$this->error('账号不能为空');die;
    	}
    	if(empty($password)){
    		$this->error('密码不能为空');die;
    	}
    	if(empty($code) || $code == '验证码:'){
    		$this->error('验证码不能为空');die;
    	}
        $verify = new \Think\Verify();
        if(!$verify->check($code,1)){
            $this->error('验证码错误');die;
        }
        $db = M('admin');
        $row = $db->where(array('username'=>$username))->find();
        if(empty($row)){
            $this->error('账号不存在');
        }
        if($row['password'] != md5(md5($password).md5($row['entry']))){
            $this->error('密码错误');
        }
        session('adminid',$row['id']);
        $data = array(
            'id' => $row['id'],
            'login_num' => $row['login_num'] + 1,
            'login_ip' => get_client_ip(),
            'login_time' => time(),
            );
        if($online == 1){
            $info = $username.'|'.$password;
            $info = authcode($info);
            cookie('online',$info);
            cookie('online',$info,60*60*24*7);
        }else{
            cookie('online',null);
        }
        $db->save($data);
        $this->redirect('Main/index');
    }
    public function logout(){
        session('adminid',null);
        $this->redirect('Index/index');
    }


}

