<?php
namespace Admin\Controller;

class UserController extends BaseController {

    public function index(){

    	$this->display();

    }

	public function user_list(){

		//运行一下推广
		// $recommend = new CountController;
		// $recommend->index();

		$db = M('user');
		$db2= M('recommend');
		$where = array();
		$where['log_type'] = I('get.type',1);
		$word = trim(I('get.word'));
		if($where['log_type'] == 1){
			$where['username|name|id'] = array('like',"%$word%");
		}else{
		    $where2=$db2->where(array('invitation_id' => $word, ))->select();
		    foreach ($where2 as  $k =>&$v) {
		    	$where2[$k]=$v;
		    }
			$where['id']= array('like',"%$word%");
		}
		$count = $db->where($where)->count();
		$page = new \Think\Page($count,10);
		$list = $db->order('id desc')->where($where)->limit($page->firstRow,$page->listRows)->select();
		$this->assign('list',$list);
		$this->assign('list2',$list2);
		$this->assign('page',$page->show());
		$this->display();

	}
	//所有荷花的藕蓬
	// public function get_alllotus($uid){
	// 	$row = M('user')->find($uid);
	// 	$list = M('generate')->where(array('uid'=>$uid))->select();

	// 	foreach($list as $v){
	// 		//可用荷花
	// 		if($v['state'] != 3){
	// 			//荷花生活了几天
	// 			$life = get_life($v['start_time']);
	// 			//荷花的价格倍数
	// 			$type = $v['price'] / 3000;

	// 			if($life < 30){
	// 				//只为显示
	// 				$show_lotus += $type * $life * 12;
	// 				$show_peng  += $type * $life * 3;

	// 			}else{
	// 				//30天保存
	// 				$user_lotus = M('user')->where(array('id'=>$uid))->getField('lotus');
	// 				$user_peng = M('user')->where(array('id'=>$uid))->getField('peng');

	// 				$this_lotus = $type * 12 * 30;
	// 				$this_peng  = $type * 3 * 30;

	// 				$data = array(
	// 					'id' => $uid,
	// 					'profit_lotus' => $user_lotus + $this_lotus - $row['h_lotus'],
	// 					'profit_peng' => $user_peng + $this_peng - $row['h_lotus'],
	// 					'h_lotus' => 0,
	// 					'h_peng' => 0,
	// 				);
	// 				M('user')->save($data);
	// 				M('generate')->save(array('id'=>$v['id'],'state'=>3));
	// 			}
	// 		}
	// 	}
	// 	return array('lotus'=>$show_lotus,'peng'=>$show_peng);
	// }


	public function user_del(){
		$id = trim(I('id'),',');
		$b = M('user')->delete($id);
		M('recommend')->where(array('register_id'=>array('in',$id)))->delete();
		M('generate')->where(array('uid'=>array('in',$id)))->delete();
		if($b){
			$this->ajaxreturn(array('status'=>'y','info'=>'删除成功'));
		}else{
			$this->ajaxreturn(array('status'=>'n','info'=>'删除失败'));
		}
	}

    ##批量暂停
	public function user_stops(){
		$id = trim(I('post.id'));
		$type = trim(I('post.type'));
		if(!empty($id) && !empty($type)){
			if($type ==1){
				$res = M('user')->where(array('id'=>array('in',$id)))->setField('is_stop',1);
			}else{
				$res = M('user')->where(array('id'=>array('in',$id)))->setField('is_stop',0);
			}
			if($res){
				$this->ajaxreturn(array('status'=>'y','info'=>'操作成功'));
			}else{
				$this->ajaxreturn(array('status'=>'n','info'=>'操作失败'));
			}
		}else{
			$this->ajaxreturn(array('status'=>'n','info'=>'操作失败'));
		}
	}
    ##批量启用
	public function user_start(){
		$id = trim(I('post.id'));
		$type = trim(I('post.type'));
		if(!empty($id) && $type ==0){
			if($type ==0){
				$res = M('user')->where(array('id'=>array('in',$id)))->setField('is_stop',0);
			}else{
				$res = M('user')->where(array('id'=>array('in',$id)))->setField('is_stop',1);
			}
			if($res){
				$this->ajaxreturn(array('status'=>'y','info'=>'修改成功'));
			}else{
				$this->ajaxreturn(array('status'=>'n','info'=>'修改失败'));
				dump('保存失败');
			}
		}else{
			$this->ajaxreturn(array('status'=>'n','info'=>'操作失败'));
		}
	}
	##暂停用户
	#
	public function user_stop(){
		$id = trim(I('post.id'));
		$type = trim(I('post.type'));
		if(!empty($id) && !empty($type)){
			if($type ==1){
				$res = M('user')->where('id ='.$id)->setField('is_stop',1);
			}else{
				$res = M('user')->where('id ='.$id)->setField('is_stop',0);
			}
			if($res){
				$this->ajaxreturn(array('status'=>'y','info'=>'操作成功'));
			}else{
				$this->ajaxreturn(array('status'=>'n','info'=>'操作失败'));
			}
		}else{
			$this->ajaxreturn(array('status'=>'n','info'=>'操作失败'));
		}
	}

	public function recommend_list(){

		$where = array();

		$i_id = I('get.i_id');
		$type = I('get.type');
		if(!empty($i_id)){

			$where = array('invitation_id'=>$i_id);

		}

		$db = M('recommend');
		$count = $db->where($where)->count();

		$page = new \Think\Page($count,10);

		$list = $db->order('add_time desc')->where($where)->limit($page->firstRow,$page->listRows)->select();
/*		if($type==1){
            foreach ($list as $k => &$v) {
            	$row=$v['register_id'];
            	$list2= M('user')->where(array('id' => $row ,'is_active'=>'1' ))->find();
		        $list[$k]= $db->order('add_time desc')->where(array('register_id' =>$list2['id'] , ))->limit($page->firstRow,$page->listRows)->find();
            }
		}*/
		$this->assign('list',$list);
		$this->assign('page',$page->show());

		$this->display();

	}

	public function recommend_del(){

		$id = trim(I('id'),',');

		$b = M('recommend')->where(array('register_id'=>array('in',$id)))->delete();

		if($b){

			$this->ajaxreturn(array('status'=>'y','info'=>'删除成功'));

		}else{

			$this->ajaxreturn(array('status'=>'n','info'=>'删除失败'));

		}

	}

	//加速入口
	public function time_axis(){

		$db = M('user');

		$list = $db->order('id desc')->select();

		foreach($list as $v){
			//荷花生成产
			$this->harvest($v['id'],$v['lotus'],$v['peng']);
			//推广收获
			// $this->income_main($v['id']);
		}
		header("Location: ".U('user_list'));
    }



	// private function income_main($uid){
	// 		$list = M('recommend')->select();
	// 		$list = get_parent($list,$uid);
	// 		if($list){
	// 			foreach($list as $v){
	// 				$rid .= $v['register_id'].',';
	// 			}
	// 			//有效推荐
	// 			$num = M('generate')->where(array('uid'=>array('in',$rid)))->count();
	// 		}
	// 		//邀请有效享受几代
	// 		if($num >=1){	
	// 			//可以享受第一代 ...
	// 			$this->income($list,$uid,1);
	// 		}
	// 		if($num >=2){
	// 			//可以享受第二代 ...
	// 			$this->income($list,$uid,2);
	// 		}
	// 		if($num >=3){
	// 			//可以享受第三代 ...
	// 			$this->income($list,$uid,3);
	// 		}
	// }
	
	// private function income($list,$uid,$level){
	// 	switch ($level) {
	// 		case 1:
	// 			$child = get_child($list,$level);
	// 			if($child){
	// 				//第一代人荷花价格
	// 				$generate = M('generate')->where(array('uid'=>array('in',$child),'sharea'=>0))->getField('price',true);
	// 				if($generate){
	// 					$data = array();
	// 					foreach($generate as $v){
	// 						$bs = $v/3000;
	// 						$data['profit_lotus'] += 12*$bs;
	// 						$data['profit_peng']  += 3*$bs;
	// 					}
	// 					$row = M('user')->find($uid);
	// 					$data['id'] = $uid;
	// 					$data['profit_lotus'] += $row['profit_lotus']; 
	// 					$data['profit_peng'] += $row['profit_peng'];
	// 					$b = M('user')->save($data);
	// 					if($b){
	// 						M('generate')->where(array('uid'=>array('in',$child)))->save(array('sharea'=>1));
	// 					}
	// 				}
	// 			}
	// 		break;
	// 		case 2:
	// 			$child = get_child($list,$level);
	// 			if($child){
	// 				//第二代人荷花价格
	// 				$generate = M('generate')->where(array('uid'=>array('in',$child),'shareb'=>0))->getField('price',true);
	// 				$data = array();
	// 				if($generate){
	// 					foreach($generate as $v){
	// 						$bs = $v/3000;
	// 						$data['profit_lotus'] += 7*$bs;
	// 						$data['profit_peng']  += 2*$bs;
	// 					}
	// 					$row = M('user')->find($uid);
	// 					$data['id'] = $uid;
	// 					$data['profit_lotus'] += $row['profit_lotus']; 
	// 					$data['profit_peng'] += $row['profit_peng'];
	// 					$b = M('user')->save($data);
	// 					if($b){
	// 						M('generate')->where(array('uid'=>array('in',$child)))->save(array('shareb'=>1));
	// 					}
	// 				}
	// 			}
	// 		break;
	// 		case 3:
	// 			$child = get_child($list,$level);
	// 			if($child){
	// 				$generate = M('generate')->where(array('uid'=>array('in',$child),'sharec'=>0))->getField('price',true);
	// 				$data = array();
	// 				foreach($generate as $v){
	// 					$bs = $v/3000;
	// 					$data['profit_lotus'] += 5*$bs;
	// 					$data['profit_peng']  += 1*$bs;
	// 				}
	// 				$row = M('user')->find($uid);
	// 				$data['id'] = $uid;
	// 				$data['profit_lotus'] += $row['profit_lotus']; 
	// 				$data['profit_peng'] += $row['profit_peng'];
	// 				$b = M('user')->save($data);
	// 				if($b){
	// 					M('generate')->where(array('uid'=>array('in',$child)))->save(array('sharec'=>1));
	// 				}
	// 			}
	// 		break;
	// 	}
	// }


	//每个人的荷花收益（加速）
	private function harvest($id){
		$conf = get_conf();
		$db = M('generate');//荷花
		$generate = $db->where(array('uid'=>$id))->select();
		foreach($generate as $v){
			$life_day = get_life($v['start_time']);//当前荷花的天龄
			if($life_day < 30 && $v['state'] == 1){
				// !!!模拟时间
				$db->where(array('uid'=>$id,'id'=>$v['id']))->save(array('start_time'=>$v['start_time'] - 86400));
			}
		}
	}

	public function user_add(){
		$db = M('user');
		if(IS_POST){	
			$data = I('post.');
			###
			#账号唯一：
			$row = $db->find($data['id']);
			if($row['username'] != $data['username']){
				$where['username'] = $data['username'];
				$count = $db->where($where)->count();
				if($count>= 1){
					$this->ajaxreturn(array('status'=>'n','info'=>'此手机号已存在，无法更改'));
				}
			}
	
			$b = $db->save($data);
			if($b !== false){
				$this->ajaxreturn(array('status'=>'y','info'=>'操作成功'));
			}else{
				$this->ajaxreturn(array('status'=>'y','info'=>'操作失败'));
			}
		}else{
			$id = I('get.id');
			$list = $db->find($id);
			$question_list = M('safety_question')->select();
            $this->assign('question_list',$question_list);	
			$this->assign($list);
			$this->display();
		}
	}
	public function encrypt($id){
    	$str = md5($id.KEY);
        preg_match_all("/\d+/",$str,$arr);
        $num = implode('', $arr[0]);
        return msubstr($num,0,6);
    }

	public function user_add2(){
		$db = M('user');
		$list = M('user')->select();
		if(IS_POST){	
			$data = I('post.');
            $data['entry']= creat_rand(6);
    	    foreach($list as $v){
    	    	if($data['username'] == $v['username']){
                     $this->error('手机号码已存在');die;    	    		
    	    	}
    	    }
    	    foreach($list as $v){
    	    	if($data['code'] == $this->encrypt($v['id'])){
    	    		$id = $v['id'];
    	    		$name =$v['name'];
    	    	}
    	    }
    	    if($id==null){
                $this->error('请填写正确邀请码');die;
    	    }
    	    if($data['yqrname']!==$name){
                $this->error('用户姓名与邀请码对应姓名不匹配');die;    	    	
    	    }
            if($data['password'] !=$data['passwordq']){
                $this->error('一级密码不一致');die;
            }
            if($data['psw2'] !=$data['pswq']){
                $this->error('二级密码不一致');die;
            }
            $data['password']=md5(md5($data['password']).md5($data['entry']));
            $data['psw2']=md5(md5($data['psw2']).md5($data['entry']));
            $url = 'http://'.$_SERVER['HTTP_HOST']."/aliyun/demo/sendSms1.php?telphone=".$data['username']."&nicheng1=".$data['name']."&password1=".$data['passwordq']."&password2=".$data['pswq'];
            $res = file_get_contents($url); 
			#账号唯一：
			$b = $db->add($data);
			$data = array(
				'register_id' => $b,
				'invitation_id' => $id,
				'add_time' => time(),
			);
			M('recommend')->add($data);				
			if($b !== false){
				$this->ajaxreturn(array('status'=>'y','info'=>'操作成功'));
			}else{
				$this->ajaxreturn(array('status'=>'y','info'=>'操作失败'));
			}
		}else{
			$this->display();
		}
	}
	private function get_alllotus($uid){
    	//当前用户的荷花
    	$db = M('user');
    	$user = $db->find($uid);
    	$ge = M('generate');
		$list = $ge->where(array('uid'=>$uid))->select();
		$time = time();
		$day = 86400;
		foreach($list as $v){
			if($v['state'] != 3){
				// 荷花的价格倍数
				$type = $v['price'] / 3000;
				//荷花生长天数
				$life = get_life($v['start_time']);
					
				if($life > 0 && $life <= 30){
					$lotus += ($life - $v['sharea']) * $type * 12;
					$peng += ($life - $v['sharea']) * $type * 3;
					$share = array(
						'id' => $v['id'],
						'sharea' => $life,
					);
					$ge->save($share);
				}
			}
		}
		$data = array(
			'id' => $uid,
			'lotus' => $user['lotus'] + $lotus,
			'peng' => $user['peng'] + $peng,
		);
		$b = $db->save($data);
	}

	public function log(){

		$db = M('log');

		$where = array();
		//类型
		$where['log_type'] = I('get.type',1);

		$word = trim(I('get.word'));

		if(!empty($word) ){
			$where['uid'] = array('like',"%$word%");
		}

		$get = I('get.');
		$start_time = $get['start'] ? strtotime($get['start']) : 0;
		$end_time = $get['end'] ? strtotime($get['end']) + 86399 : 0;

		if($start_time && $end_time){
			$where['add_time'] = array(array('egt',$start_time),array('lt',$end_time));
		}elseif($start_time){
			$where['add_time'] = array('egt',$start_time);
		}elseif($end_time){
			$where['add_time'] = array('elt',$end_time);
		}


		$count = $db->where($where)->count();

		$page = new \Think\Page($count,10);

		$list = $db->order('id desc')->where($where)->limit($page->firstRow,$page->listRows)->select();

		$this->assign('list',$list);

		$this->assign('page',$page->show());

		$this->display();

	}

	public function get_money_del(){
		$id = trim(I('id'),',');
		$b = M('get_money')->delete($id);
		if($b){
			$this->ajaxreturn(array('status'=>'y','info'=>'删除成功'));
		}else{
			$this->ajaxreturn(array('status'=>'n','info'=>'删除失败'));
		}
	}

	public function log_del(){
		$id = trim(I('id'),',');
		$b = M('log')->delete($id);
		if($b){
			$this->ajaxreturn(array('status'=>'y','info'=>'删除成功'));
		}else{
			$this->ajaxreturn(array('status'=>'n','info'=>'删除失败'));
		}
	}

	##提现记录表
	public function get_money(){
		$db = M('get_money');
		$db2 = M('user');
		$where = array();
		$word = trim(I('get.word'));
		if(!empty($word) ){
			$where['uid|alipay'] = array('like',"%$word%");
		}
		$type = trim(I('get.type'));
		if(!empty($type) ){
			if($type != 9){
				$where['type'] = $type -1;
			}
		}
		$count = $db->where($where)->count();
		$page = new \Think\Page($count,10);
		$list = $db->order('id desc')->where($where)->limit($page->firstRow,$page->listRows)->select();
		##计算已处理总金额，
		$list_deal = $db->where('type =1')->select();
		$money_deal = 0;
		foreach ($list_deal as $k => $v) {
			$money_deal+=$v['lotus'];
		}
		##计算未处理总金额
		$list_nodeal = $db->where('type =0')->select();
		$money_nodeal = 0;
		foreach ($list_nodeal as $k => $v) {
			$money_nodeal+=$v['lotus'];
		}
		$this->assign('money_deal',$money_deal);
		$this->assign('money_nodeal',$money_nodeal);
		$this->assign('list',$list);
		$this->assign('page',$page->show());
		$this->display();
	}

	##提现记录修改
	public function get_money_add(){
		$db = M('get_money');
		if(IS_POST){	
			$data = I('post.');
			$b = $db->save($data);
			if($b !== false){
				$this->ajaxreturn(array('status'=>'y','info'=>'操作成功'));
			}else{
				$this->ajaxreturn(array('status'=>'y','info'=>'操作失败'));
			}
		}else{
			$id = I('get.id');
			$row = $db->find($id);
			$this->assign($row);
			$this->display();
		}
	}

	##商品兑换记录
	public function goods_exchange(){
		$db = M('goods_exchange');
		$where = array();
		$word = trim(I('get.word'));
		if(!empty($word) ){
			$where['uid|alipay'] = array('like',"%$word%");
		}
		$count = $db->where($where)->count();
		$page = new \Think\Page($count,10);
		$list = $db->order('id desc')->where($where)->limit($page->firstRow,$page->listRows)->select();
		$this->assign('list',$list);
		$this->assign('page',$page->show());
		$this->display();
	}
	##商品兑换记录修改
	public function goods_exchange_add(){
		$db = M('goods_exchange');
		if(IS_POST){	
			$data = I('post.');
			$b = $db->save($data);
			if($b !== false){
				$this->ajaxreturn(array('status'=>'y','info'=>'操作成功'));
			}else{
				$this->ajaxreturn(array('status'=>'y','info'=>'操作失败'));
			}
		}else{
			$id = I('get.id');
			$row = $db->find($id);
			$this->assign($row);
			$this->display();
		}
	}
	public function goods_exchange_del(){
		$id = trim(I('id'),',');
		$b = M('goods_exchange')->delete($id);
		if($b){
			$this->ajaxreturn(array('status'=>'y','info'=>'删除成功'));
		}else{
			$this->ajaxreturn(array('status'=>'n','info'=>'删除失败'));
		}
	}
	
}