<?php
namespace Admin\Controller;
use Think\Controller;
class BaseController extends Controller {
	public function _initialize(){
		if(!session('?adminid')){
			$this->redirect('Index/index');
		}
		$uid = session('adminid');
		$row = M('admin')->find($uid);
		$this->assign('admin',$row);

		//生成左边菜单
		$menu = array();
		$group_id = M('auth_group_access')->where(array('uid'=>$uid))->getField('group_id',true);
		// dump($group_id);die;	//角色
		if($group_id){
			$rules = M('auth_group')->where(array('id'=>array('in',$group_id)))->getField('rules',true);
			$tmp_arr = array();
			foreach($rules as $v){
				$tmp_arr = array_merge($tmp_arr,explode(',', $v));  
			}
			$tmp_arr = array_unique($tmp_arr); //登录用户的所有权限
			$menu = M('auth_rule')->order('sort')->where(array('id'=>array('in',$tmp_arr)))->field('id,title,parent_id,name,param')->select();
			$menu = rule_tree($menu);
		}
		// echo '<pre>';
		// print_r($menu);
		$this->assign('menu',$menu);

		/* 进行权限管理 */
		if(C('AUTH_CONFIG.AUTH_ON')){
			$auth = new \Think\Auth;
			$name = MODULE_NAME . '/' . CONTROLLER_NAME .'/'. ACTION_NAME;
			// echo $name;die;
			/* 不需要验证的控制器 */		
			if(!in_array(CONTROLLER_NAME, C('no_auth_controller'))){
				$res = $auth->check($name,$uid);
				if(!$res){
					if(IS_AJAX){
						$this->ajaxReturn(array('status'=>'n','info'=>'没有操作权限'));
					}else{
						$this->show("<h3 style='color:red;text-align: center; margin-top:200px;'>没有操作权限</h3>");die;
					}
					
				}
			}
		}
	}

	/* 文件上传 */
	public function uploadImg(){
		$upload = new \Think\Upload();//实例化上传类
		$upload->maxSize = 1024*1024*2;//设置附件上传大小
		$upload->exts = array('jpg','png','gif','jpeg');//设置附件上传类型
		$path = I('get.path','file');
		$upload->savePath = $path.'/';//设置附件上传目录
		//开始上传
		$info = $upload->uploadOne($_FILES['imgFile']);
		if(!$info){
			$this->ajaxReturn(array('error'=>1,'message'=>$upload->getError()));
		}else{
			$url = '/Uploads/'.$info['savepath'].$info['savename'];
			$this->ajaxReturn(array('error'=>0,'url'=>$url));		
		}
	}


        /**
        * 发送post请求
        * @param string $url 请求地址
        * @param array $post_data post键值对数据
        * @return string
        */
        public   function sendHttpPost($url, $post_data) {
              $postdata = http_build_query($post_data);
              $options = array(
                'http' => array(
                  'method' => 'POST',
                  'header' => 'Content-type:application/x-www-form-urlencoded',
                  'content' => $postdata,
                  'timeout' => 15 * 60 // 超时时间（单位:s）
                )
              );
              $context = stream_context_create($options);
              $result = file_get_contents($url, false, $context);
              return $result;
        }


}