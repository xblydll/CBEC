<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller {
	public function index(){
        if(session('?userid')){
            $this->redirect('Main/index');
        }
        $online = cookie('user');
        if(!empty($online)){
            $info = authcode($online,'DECODE');
            if(!empty($info)){
                $user = explode('|', $info);
                $this->assign('user',$user);
            }
        }
        $question_list = M('safety_question')->select();
        $this->assign('question_list',$question_list);
        $this->display();
    }
    public function register(){
    	if(IS_POST){
            $db = D('User');
            if(!$db->create()){
                $this->error($db->getError());die;
            }
            $b = $db->add();
			$data = array(
				'register_id' => $b,
				'invitation_id' => session('code'),
				'add_time' => time(),
			);
			M('recommend')->add($data);

			if($b){
				$this->ajaxreturn(array('status'=>'y','info'=>'注册成功'));
			}else{
				$this->ajaxreturn(array('status'=>'n','info'=>'注册失败'));
			}
    	}
	}
    public function sendCode(){
        if(IS_POST){ 
            $data = I('post.');
            $rand =  rand(100000,999999);
            $url = 'https://'.$_SERVER['HTTP_HOST']."/aliyun/demo/sendSms.php?telphone=".$data['mobile']."&rand=".$rand;
            $res = file_get_contents($url);
            $_SESSION['code'] = $rand;
            if($res){
                $this->ajaxreturn(array('status'=>'y','info'=>'验证码发送成功'));die;
            }else{
                $this->ajaxreturn(array('status'=>'n','info'=>'验证码发送失败'));die;
            }
            exit;

            }
    }
	public function login(){
		if(IS_POST){
			$data = I('post.');
			$username = $data['user'];
	    	$password = $data['pwd'];
	    	$online = I('online',0);
	        $db = M('user');
	        $row = $db->where(array('username'=>$username))->find();
	        if(empty($row)){
	            $this->error('账号不存在');
	        }
	        if($row['password'] != md5(md5($password).md5($row['entry']))){
	            $this->error('密码错误');
	        }

            ##登录状态随机数
            $login_rand = creat_rand(8);
            $res = M('user')->where('id = '.$row['id'])->setField('login_rand',$login_rand);
            session('login_rand',$login_rand);
	        //成功登录记住密码
	        if($online == 1){
	            $info = $username.'|'.$password;
	            $info = authcode($info);
	            cookie('user',$info,60*60*24*7);
                cookie('password',md5(md5($password).md5($row['entry'])));
	        }else{
	            cookie('user',null);
	        }

        	session('userid',$row['id']);
        	$this->ajaxreturn(array('status'=>'y','info'=>'1','url'=>U('Main/index')));
		}
	}

	public function logout(){
        session('userid',null);
        $this->redirect('Index/index');
    }

    public function back_pwd(){
    	if(IS_AJAX){
    		$data = I('post.');
    		$db = M('user');
    		$info = $db->where(array('username'=>$data['username']))->find();
    		if(empty($info)){
    			$msg = array('status'=>'n','info'=>'账号不存在');
    		}else{
                ###密保问题判断
                    if($data['find_code'] != $_SESSION['code']){
                        $this->ajaxreturn(array('status'=>'n','info'=>'验证码错误'));
                        die;
                    }else{
                        $password = md5(md5($data['pwd']).md5($info['entry']));
                        $data = array(
                            'id' => $info['id'],
                            'password' => $password
                        );
                        $b = $db->save($data);
                        if($b){
                            $msg = array('status'=>'y');
                        }else{
                            $msg = array('status'=>'n','info'=>'修改失败');
                        }
                    }
    		}
    		$this->ajaxreturn($msg);
    	}
    }

    ##注册昵称检测，是否已占用
    public function checkName(){
        $name = trim(I('post.name'));
        if(empty($name)){
            $this->ajaxReturn(array('status'=>0,'info'=>'昵称不能为空'));
        }
        $row =M('user')->where("name ='".$name."'")->find();
        if($row){
            $this->ajaxReturn(array('status' =>0,'info'=>'昵称已存在，请更换昵称'));
        }else {
            $this->ajaxReturn(array('status' =>1,'info'=>'可以使用'));
        }
    }

}	