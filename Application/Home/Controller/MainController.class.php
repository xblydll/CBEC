<?php
//
//                       _oo0oo_
//                      o8888888o
//                      88" . "88
//                      (| -_- |)
//                      0\  =  /0
//                    ___/`---'\___
//                  .' \\|     |// '.
//                 / \\|||  :  |||// \
//                / _||||| -:- |||||- \
//               |   | \\\  -  /// |   |
//               | \_|  ''\---/''  |_/ |
//               \  .-\__  '-'  ___/-. /
//             ___'. .'  /--.--\  `. .'___
//          ."" '<  `.___\_<|>_/___.' >' "".
//         | | :  `- \`.;`\ _ /`;.`/ - ` : | |
//         \  \ `_.   \_ __\ /__ _/   .-` /  /
//     =====`-.____`.___ \_____/___.-`___.-'=====
//                       `=---='
//
//
//     ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//               佛祖保佑         永无BUG
//               佛祖出征         寸草不生
//  
namespace Home\Controller;
class MainController extends BaseController {
    public function index(){
        $hftx=M('config')->where('id='.'13')->find();
        $whgg=M('config')->where('id='.'14')->find();
		if($hftx['value']==0){
			$this->error($whgg['value']);die;
		}
		$uid = session('userid');
		$rb=M('member_certified')->where(array('user_id' =>$uid ,))->count();
		#收入
		// $this->get_alllotus();
		$he = array();
		$user = M('user')->find($uid);
        if($user['password']!=cookie('password')){
            session('userid',null);
            $this->error('您的账号密码已被修改,请重新登录','/Home/Index/index');
            die;
        }
		##统计昨天的池塘的产量。
		$num = $this->interestByDay();
		if($row['is_outgoing'] ==1){
			$this->error('玩家已出局，请联系客服');
		 	die;
		}
		##我的团队
		$he['offspring'] = $this->offspring($uid);
		##拆分我的一级团队,二级，三级团队
		$he['offspring_1'] = array();
		$he['offspring_2'] = array();
		$he['offspring_3'] = array();
		foreach ($he['offspring'] as $k => &$v) {
			$row = M('user')->where('id ='.$v['register_id'])->find();
			$v['username'] = $row['username']?$row['username']:'null';
			$v['lotus'] = $row['lotus'];
			$v['peng'] = $row['peng'];
			$v['is_active'] = $row['is_active'];
			if($v['level'] == 0){
				$he['offspring_1'][$k] = $v;
			}elseif ($v['level'] == 1) {
				$he['offspring_2'][$k] = $v;
			}elseif ($v['level'] == 2) {
				$he['offspring_3'][$k] = $v;
			}
		}
		##拆分结束
		// dump($he['offspring_1']);

		$he['offspring'] = count($he['offspring_1']) + count($he['offspring_2'])+count($he['offspring_3']);
		#一级激活用户
		foreach ($he['offspring_1'] as $k => $v) {
			if($v['is_active'] == 1){
				$he['offspring_1son'][$k] = $v;
			}
		}
		#二级级激活用户
		foreach ($he['offspring_2'] as $k => $v) {
			if($v['is_active'] == 1){
				$he['offspring_2son'][$k] = $v;
			}
		}
		#三级激活用户
		foreach ($he['offspring_3'] as $k => $v) {
			if($v['is_active'] == 1){
				$he['offspring_3son'][$k] = $v;
			}
		}
		#团队激活用户
		$he['offspringson'] = count($he['offspring_1son']) + count($he['offspring_2son'])+count($he['offspring_3son']);
		###记录
		#收益记录
		$he['distribution_log'] = M('distribution_log')->where('did ='.$uid)->order('id desc')->select();
		##莲藕静态总收入。
		$he['income_static'] = 0;
		##莲藕动态总收入
		$he['income_active'] = 0;
		foreach ($he['distribution_log'] as $k => $v) {
			if($v['type'] == 1){
				$he['income_static'] += $v['lotus'];
			}else{
				$he['income_active'] += $v['lotus'];
			}
		}
		##莲蓬静态总收入。
		$he['income1_static'] = 0;
		##莲蓬动态总收入
		$he['income1_active'] = 0;
		foreach ($he['distribution_log'] as $k => $v) {
			if($v['type'] == 1){
				$he['income1_static'] += $v['peng'];
			}else{
				$he['income1_active'] += $v['peng'];
			}
		}
		###配置
		$config = M('config')->select();
		$he['config_1'] = $config[5]['value'];
		$he['config_2'] = $config[6]['value'];
		$he['config_3'] = $config[7]['value'];

		$he['user'] = $user;
		$generate = M('generate');
		$generate_list = $generate->where(array('uid'=>$uid))->select();
		$he['generate'] = $generate_list;

		$he['count'] = $this->he_sum($generate_list);
		$he['recommend'] =  get_recommend($uid,false);
		$he['code'] =  $this->encrypt($uid);
		$he['generate'] = $generate->where(array('uid'=>$uid,'state'=>1))->order('id desc')->limit(10)->select();
		$hehuanum=count($he['generate']);
		$log = M('log');
		$get_money = M('get_money');
		$goods_exchange = M('goods_exchange');
		$he['received'] = $log->where(array('others_id'=>$uid))->order('id desc')->select();
		$he['give'] = $log->where(array('uid'=>$uid,'log_type'=>1))->order('id desc')->select();
		$he['exchange'] = $log->where(array('uid'=>$uid,'log_type'=>2))->order('id desc')->select();
		$he['tixian']= $get_money->where(array('uid'=>$uid))->order('id desc')->select();
		$he['huanwu']= $goods_exchange->where(array('uid'=>$uid))->order('id desc')->select();
		##池塘莲藕总量,莲蓬总数
		$list = M('generate')->where('uid ='.$uid)->select();
		$he['lotus_count'] = 0;
		$he['peng_count'] = 0;
		foreach ($list as $v) {
			$he['lotus_count'] =$he['lotus_count']+$v['zeng_lotus_new']+$v['zeng_lotus_old']+$v['price'];
			$he['peng_count'] +=$v['peng'];
		}
		$jihuo=M('mactive')->where(array('user_id' =>$uid ,))->select();
		$zslog=M('log')->where(array('uid' =>$uid,'log_type'=>'1'))->select();
        foreach ($zslog as $v) {
        	$zslog2+=$v['number'];
        }
		##总利益
		$zongli=$he['income_static']+$he['income1_active']-count($jihuo)*330;
        ##现有总数
        $zongshu=$he['lotus_count']+$he['user']['lotus']+count($jihuo)*330+$zslog2*0.1;
        
/*        dump($he['income_static']);//静态莲藕
        dump($he['income_active']);//动态莲藕
        dump(count($jihuo)*330);//激活下级消耗的莲藕
        dump($he['lotus_count']);//荷塘莲藕
        dump($he['user']['lotus']);//仓库莲藕
        dump(count($jihuo)*330);//激活下级消耗莲藕
        dump($zslog2*0.1); //赠送手续费*/       

        if(count($jihuo)==0 && count($zslog)==0){
            $gkxs=0.50;
        }else{
           $gkxs=round($zongli/$zongshu,2);

        }

        #当前莲花剩余可种植最大数量value
        $he['kz']=$he['price']*10-$he['zeng_lotus_old'];
		##小卖部，商品列表
		$he['goods_list'] =M('seedling')->where('type = 1')->order('sort desc')->select();
		$question_list = M('safety_question')->select();
		$this->assign('gkxs',$gkxs);
		$this->assign('zongli',$zongli);
		$this->assign('zongshu',$zongshu);
        $this->assign('question_list',$question_list);
        $this->assign('hehuanum',$hehuanum);
		$this->assign('he',$he);
		$this->assign('rb',$rb);
		$this->display();
	}
	public function he_sum($list){
		$he = array();
		foreach($list as $v){
			$life = get_life($v['start_time']);
			$bs = $v['price'] / 3000;
			$he['lotus'] += 12 * $life * $bs;
			$he['peng'] += 3 * $life * $bs;
		}
		return $he ?$he : array();
	}
    //二级密码找回zpf
    public function back_password(){
    	$uid =session('userid');
		$data = I('post.');
		$h = M('user')->find($uid);
        if($data['code']!= $_SESSION['code']){
            $this->error('验证码错误!');die;
            }
		$arpassword=array(
           'id' => $uid,
           'psw2' => md5($data['ejpassword'])
			);
		$b = M('user')->save($arpassword);
		if($b){
			$this->ajaxreturn(array('status'=>'y','info'=>'二级密码修改成功'));
		}else{
			$this->ajaxreturn(array('status'=>'n','info'=>'二级密码修改失败'));
		}
    }
	public function generate(){
		$uid = session('userid');
		$hid = I('post.id');
		$num = I('post.num');
		$h = M('seedling')->find($hid);

		$row = M('user')->field('id,lotus')->find($uid);
		//莲藕购买荷花的总价格
		//dump($row['peng']);die;
		$price = $h['price'] * $num;
		if($row['lotus'] < $price){
			$this->ajaxreturn(array('status'=>'n','info'=>'您的莲藕不足，请联系客服，购买礼包。'));die;
		}
		$udata = array(
			'id' => $row['id'],
			'lotus' => $row['lotus']-$price
		);
		if($row['is_active'] == 0){
			$udata['is_active'] = 1;
		}

		###区分兑换的是实物商品还是何苗
		#0 何苗 1：荷花
		if($h['type'] == 0){

			$data = array(
				'uid' => $uid,
				'hid' => $hid,
				'name' => $h['name'],
				'price' => $h['price'],
				'state' => 1,
				'start_time' => time(),
				'add_time' => time()
			);

			$count = M('generate')->where(array('uid'=>$uid,'state'=>1))->count();
			##绿荷花的数量
			$count_lv =   M('generate')->where(array('uid'=>$uid,'state'=>1,'hid'=>1))->count();
			##金荷花数量
			$count_jin =   M('generate')->where(array('uid'=>$uid,'state'=>1,'hid'=>10))->count();
			if($count + $num <= 10){
				if($hid == 1){
					if($count_lv + $num > 6){
						$this->ajaxreturn(array('status'=>'n','info'=>'绿荷花最多不超过6朵'));die;
					}
				}elseif ($hid == 10) {
					if($count_jin + $num > 4) {
						$this->ajaxreturn(array('status'=>'n','info'=>'金荷花最多不超过4朵'));die;
					}
				}


				$b = M('user')->save($udata);
				if($b == false){
					$this->ajaxreturn(array('status'=>'n','info'=>'系统繁忙，请稍后再试'));die;
				}
				$n = 0;
				for($i=1;$i<=$num;$i++){

					$b = M('generate')->add($data);

					$n = $b ? $n+1 : $n;
				}
				if($n > 0){
					##购买荷花 即激活
					if($row['is_active'] == 0){
						$b = M('user')->where('id ='.$row['id'])->setField('is_active',1);
					}
					$this->ajaxreturn(array('status'=>'y','info'=>'您成功购买了'.$n.'朵'));
				}else{
					$this->ajaxreturn(array('status'=>'n','info'=>'购买失败'));
				}
			}else{
				$this->ajaxreturn(array('status'=>'n','info'=>'池塘满了'));die;
			}
		}elseif ($h['type'] == 1) {
			$b = M('user')->save($udata);
			if($b == false){
				$this->ajaxreturn(array('status'=>'n','info'=>'系统繁忙，请稍后再试'));die;
			}
			$data = array(
				'uid' => $uid,
				'goods_id' => $hid,
				'lotus' => $price,
				'num' => $num,
				'add_time' => time()
			);
			$b = M('goods_exchange')->add($data);
			if($b>0){
				$this->ajaxreturn(array('status'=>'y','info'=>'兑换成功'));
			}else{
				$this->ajaxreturn(array('status'=>'n','info'=>'兑换失败'));
			}
		}
	}

	###实物购买，
	#进行实物兑换
	public function goods_exchange(){
		$data = I('post.');
		$id = trim($data['goods_id']);
		$goods = M('seedling')->find($id);
		if(empty($goods)){
			$this->error('数据错误，请重试');
		}
		$he2=M('seedling')->where(array('type'=>1))->find($id);
		$peng = $goods['price'] * $data['num'];
		$peng += $peng/10;
		##判断兑换商品的莲蓬数是否足够
		$userid = session('userid');
		$user = M('user')->find($userid);
		$data['lotus'] = $peng;
		$data['uid'] = $userid;
		$data['add_time'] = time();
		if ($he2['num']<$data['num']) {
			$this->ajaxreturn(array('status'=>'n','info'=>'库存不足，无法兑换！'));die;
		}
		if($user['peng'] < $peng){
			$this->ajaxreturn(array('status'=>'n','info'=>'您的莲蓬不足，请联系客服，购买礼包。'));die;
		}
		$res = M('goods_exchange')->add($data);
		$udata = array(
			'id' => $userid,
			'peng' => $user['peng']-$peng
		);
		$sdata=array(
            'id' =>$id,
            'num'=>$he2['num']-$data['num']
			 );
		$res=M('seedling')->save($sdata);
		if($res){
			$res = M('user')->save($udata);
			}
		if($res){
			$this->ajaxreturn(array('status'=>'y','info'=>'兑换成功'));
		}else{
			$this->ajaxreturn(array('status'=>'n','info'=>'兑换失败'));
		}

	}

	public function clearLotus(){
		$uid = session('userid');
		$id = I('post.id');
		if(empty($id)){
			$this->ajaxreturn(array('status'=>'n','info'=>'(*/ω＼*)'));
		}
		$db = M('user');
		$user = $db->find($uid);
		$row = M('generate')->where(array('uid'=>$uid))->find($id);
		$life = get_life($row['start_time']);
		//荷花倍数
		$double = $row['price'] / 3000;
		if($life == 20){
			$data = array(
				'lotus' => $user['lotus'] + 66 * $double,
				'peng' => $user['peng'] + round(16.5 * $double),
			);
		}elseif($life == 25){
			$data = array(
				'lotus' => $user['lotus'] + 24 * $double,
				'peng' => $user['peng'] + 6 * $double,
			);
		}else{
			$this->ajaxreturn(array('status'=>'n','info'=>'荷花需要生长到20,25天才可以铲除'));
		}
		if($data){
			$data['id'] = $uid;
			M('generate')->save(array('id' => $id,'state' => 3));
			$b = $db->save($data);
		}
		if($b){
			$this->ajaxreturn(array('status'=>'1','info'=>'铲除成功'));
		}else{
			$this->ajaxreturn(array('status'=>'0','info'=>'铲除失败'));
		}
	}

	// 采摘
	public function pickLotus(){
		$uid = session('userid');
		$id = I('post.id');
		if(empty($id)){
			$this->ajaxreturn(array('status'=>'n','info'=>'123'));
		}
		$db = M('user');
		$user = $db->find($uid);
		if($user['is_outgoing'] ==1){
			$this->error('您已出局，请联系客服');
			die;
		}	
		$row = M('generate')->where(array('uid'=>$uid))->find($id);
		if(empty($row)){
			$this->error('没有操作权限');
			die;
		}
		// $life = get_life($row['start_time']);
		####限制一天只能采摘一次
		##今天是否已采摘过，00:00后为明天
		$now = strtotime(date("Y-m-d",time()));
		$past = strtotime(date("Y-m-d",$row['pick_time']));
		if($now == $past){
			$this->ajaxreturn(array('status'=>'n','info'=>'您今天已经采摘过，无法进行采摘'));
			die;
		}
		if($row['add_time'] > $now){
			$this->ajaxreturn(array('status'=>'n','info'=>'新种植荷花，明日才能采摘'));
			die;
		}


		###采摘，加上昨天生产的收益 
		$user['lotus'] = $user['lotus'] + $row['lotus'];
		$user['peng'] = $user['peng'] + $row['peng'];
		$res = $db->save($user);
		if($res){
			##分销上级收益增加
			$this->distribution($uid,$row['lotus'],$id);

			$peng = $row['peng'];
			$lotus = $row['lotus'];
			$row['pick_time'] = time();
			##清空收获的莲藕
			$row['lotus'] = 0;
			$row['peng'] = 0;
			#今日产藕,今日产蓬
			$row['lotus_b'] = $lotus;
			$row['peng_b'] = $peng;
			#历史产藕,历史产蓬
			$row['lotus_c'] = $row['lotus_c'] +$lotus;
			$row['peng_c'] = $row['peng_c'] +$peng;
			$res = M('generate')->save($row);
			##添加记录
			$data2 = array(
				'did' => $uid,
				'hid' => $id,
				'lotus' => $lotus,
				'peng' =>  $peng,
				'type' =>  1,
				'add_time' =>  time(),
			);
			$res = M('distribution_log')->add($data2);
		}

		if($res){
			$this->ajaxreturn(array('status'=>'1','info'=>'采摘成功','peng'=>$peng,'lotus'=>$lotus));
		}else{
			$this->ajaxreturn(array('status'=>'0','info'=>'采摘失败'));
		}
	}

####收取荷花，收取除本金之外所有的莲藕
	public function cutLotus(){
		$id = I('id');
		$uid = session('userid');
		$row = M('generate')->where(array('uid'=>$uid))->find($id);
		// if(empty($row)){
		// 	$this->error('没有操作权限');
		// 	die;
		// }
		$count = intval($row['zeng_lotus_old'] + $row['zeng_lotus_new']);
		if($count == 0){
			$this->ajaxreturn(array('status'=>'2','info'=>'可采摘数量为零'));
		}

		$res = M('user')->where('id ='.$uid)->setInc('lotus',$count);

		if($res){
			$updata = array(
				'zeng_lotus_old'=>0,
				'zeng_lotus_new'=>0
			);
			$res = M('generate')->where('id ='.$id)->save($updata);
		}
		if($res){
			$this->ajaxreturn(array('status'=>'1','info'=>'已成功采摘莲藕'.$count));
		}else{
			$this->ajaxreturn(array('status'=>'0','info'=>'操作失败'));
		}
	}



	private function encrypt($uid){
    	$str = md5($uid.KEY);
        preg_match_all("/\d+/",$str,$arr);
        $num = implode('', $arr[0]);
        return msubstr($num,0,6);
    }

    public function give(){
        $hftx=M('config')->where('id='.'11')->find();
        $myid=session('userid');
		$rb=M('member_certified')->where(array('user_id' =>$myid ,))->count();
		if($rb<1){
			$this->error('您还未实名，请到个人中心进行实名');die;
		}
		if($hftx['value']==0){
			$this->error('赠送功能暂时锁定，请等待管理员开放');die;
		}
    	$db = M('user');
    	//#赠送方
    	$uid = I('post.uid');
    	$num = I('post.num');
    	$psw2 = I('post.psw2');
    	$code =I('post.code');
    	if(empty($uid) || empty($num) || empty($psw2)){
    		$this->error('操作失败');die;
    	}
    	$user = $db->find($uid);
        if($code!= $_SESSION['code']){
            $this->error('验证码错误!');die;
            }
    	//#我方
    	$this_id = session('userid');
    	$this_user = $db->find($this_id);
        $username=$db->find($this_id);
    	##判断二级密码是否正确
    	if(md5($psw2) != $this_user['psw2']){
			$this->error('二级密码错误');die;
    	}
    	if(empty($user)){
    		$this->error('对方ID不存在');die;
    	}
    	if($user['id'] == $this_id){
    		$this->error('赠送对象不能是自己');die;
    	}
    	##实际转账，消耗莲藕数
    	$real = $num +$num/10;
    	if($real > $this_user['lotus']){
    		$this->error('莲藕不足');die;
    	}

    	###限制：一朵绿莲花最多转账150莲藕，一朵金莲花最多转账500莲藕
    	##
    	##拥有绿荷花的数量
		$count_lv =  M('generate')->where(array('uid'=>$this_id,'state'=>1,'hid'=>1))->count();
		##拥有金荷花数量
		$count_jin =   M('generate')->where(array('uid'=>$this_id,'state'=>1,'hid'=>10))->count();
		##可转账莲藕数
		$give_num = $count_lv*150 + $count_jin*500;
		##计算已经转账的莲藕数
		$rule['uid'] = $this_id;
		$rule['log_type'] = 1;
		$todaydate=strtotime(date('Y-m-d'),time());
		$rule['add_time'] = array('GT',$todaydate);
		$list =M('log')->where($rule)->field('number')->select();
		$give_count = 0;
		foreach ($list as $k => $v) {
			$give_count +=$v['number'];
		}

		if($give_count+$num > $give_num){
			$this->error('您今天可转账莲藕数最多为:'.$give_num.'已转账莲藕:'.$give_count);die;
		}

    	//先赠送
		$data = array(
			'id' => $uid,
			'lotus' => $user['lotus'] + $num,
		);


		//记录
		$log = array(
			'uid' => $this_id,
			'username'=>$username["name"],
			'others_id' => $uid,
			'number' => $num,
			'log_type' => 1,
			'lotus' => $this_user['lotus'] - $real,
			'peng' => $this_user['peng'],
			'add_time' => time()
		);
		M('log')->add($log);

		//扣藕方式
    	if($this_user['lotus'] >= $real){
			$this_data = array(
				'id' => $this_id,
				'lotus' => $this_user['lotus'] - $real
			);
    	}
    	//修改
    	// $db->startTrans();	#开事务处理
    	$b = $db->save($data);
    	if($b){
    		$this_b = $db->save($this_data);
    	}else{
    		// $db->rollback();	#回滚 回到开启事务;
    	}
    	// $db->commit();//提交
    	//++结果++
    	if($b || $this_b){
    		$this->ajaxreturn(array('status'=>'y'));
    	}else{
    		$this->ajaxreturn(array('status'=>'n','info'=>'赠送失败'));
    	}
   
    }

    public function yaoqing(){
		$uid = session('userid');
    	if(IS_POST){
            $db = D('User');
            if(!$db->create()){
                $this->error($db->getError());die;
            }
            $b = $db->add();
			$data = array(
				'register_id' => $b,
				'invitation_id' => $uid,
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

    private function get_alllotus(){
		$uid = session('userid');
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
					
				if($life > 0 && $life < 30){
					$lotus += ($life - $v['sharea']) * $type * 12;
					$peng += ($life - $v['sharea']) * $type * 3;
					$share = array(
						'id' => $v['id'],
						'sharea' => $life,
					);
					$ge->save($share);
				}elseif($life >= 30){

					$ge->save(
						array(
							'id' => $v['id'],
							'state'=>3
						)
					);

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

	public function exchange(){
		if(IS_AJAX){
			$data = I('post.');
			$lotus = intval($data['lotus']);
			$peng = intval($data['peng']);

			$db = M('user');
			$uid = session('userid');
			$user = $db->find($uid);

			if($lotus > $user['lotus']){
				$this->ajaxreturn(array('status'=>'n','info'=>'莲藕不足'));die;
			}elseif($peng > $user['peng']){	
				$this->ajaxreturn(array('status'=>'n','info'=>'莲蓬不足'));die;
			}

			//兑换莲藕
			if(!empty($lotus) && $lotus > 0){
				$arr_lotus = array(
					'id' =>$uid,
					'lotus' => $user['lotus'] - $lotus,
					'integral' => $user['integral'] + $lotus * 10,
				);
				$l = $db->save($arr_lotus);
			}

			//兑换莲蓬
			if(!empty($peng) && $peng > 0){
				$user = $db->find($uid);
				$arr_peng = array(
					'id' =>$uid,
					'peng' => $user['peng'] - $peng,
					'integral' => $user['integral'] + $peng * 10,
				);
				$p = $db->save($arr_peng);
			}
			if($l || $p){
				if($lotus || $peng){
					$user = $db->find($uid);
					$log = array(
						'uid' => $uid,
						'number' => $lotus,
						'number1' => $peng,
						'log_type' => 2,
						'lotus' => $user['lotus'],
						'peng' => $user['peng'],
						'add_time' => time()
					);
					M('log')->add($log);
				}
				$this->ajaxreturn(array('status'=>'y'));
			}
		}
	}


	//推广
	//处理推荐收益
	// public function recommend($uid){

	// 	$data = M('recommend')->select();
	// 	//和$uid有关系的树
	// 	$list = get_parent($data,$uid);
	// 	if(empty($list)){
	// 		return false;
	// 	}

	// 	foreach($list as $v){
	// 		if($v['level'] == 1){
	// 			//第一代
	// 			$recommend[] = $v['register_id'];
	// 		}
	// 	}

	// 	//推荐了多少人
	// 	if(count($recommend) < 5){
	// 		return false;
	// 	}

	// 	$i = 0;#绝对有效推广人
	// 	foreach($recommend as $v){
	// 		//有效
	// 		$is_r = M('generate')->where(array('uid'=>$v))->find();
	// 		if($is_r){
	// 			$i++;
	// 		}
	// 	}
	// 	#绝对有效推广人
	// 	if($i < 5){
	// 		return false;
	// 	}
		
	// 	//邀请有效享受几代
	// 	if($i >=5 && $i<10 ){	
	// 		$this->recommend_count(1,$uid,$list,$recommend);//级/uid/推荐树
	// 	}
	// 	if($num >=10 && $i<20){
	// 		$this->recommend_count(2,$uid,$list,$recommend);
	// 	}
	// 	if($num >=20){
	// 		$this->recommend_count(3,$uid,$list,$recommend);
	// 	}
	// }

	//通过审核开始计算
	// public function recommend_count($d,$uid,$list,$recommend){

	// 	//查询会员结算日期
	// 	$sum = M('generate')->where(array('uid'=>$recommend[0]))->order('id desc')->getField('start_time');

	// 	$db = M('user');
	// 	$row = $db->find($uid);
		
	// 	$sum_m = date('m',$sum);	//购买月
	// 	$sum_d = date('d',$sum);	//每月的这天结算

	// 	$this_time = time();		
	// 	$this_m = date('m',$this_time);	//当前月

	// 	//当前月大于购买月
	// 	// $dif = ($this_time - $sum)/(68400*30);

	// 	// dump($dif);die;

	// 	// if($dif < 1 ){
	// 	// 	return false;
	// 	// }
		
	// 	//记录过了当前月
	// 	if($row['date_m'] == $this_m){
	// 		return false;
	// 	}


	// 	//开始分销

	// 	foreach($list as $v){
	// 		if($v['level'] == $d){
	// 			//查询用户所有荷花
	// 			$he[] = M('generate')->where(array('uid'=>$v['register_id'],'state'=>1))->select();
	// 		}
	// 	}

	// 	if($d == 1){
	// 		$lotus = 12;
	// 		$peng = 3;
	// 	}elseif($d == 2){
	// 		$lotus = 7;
	// 		$peng = 2;
	// 	}elseif($d == 3){
	// 		$lotus = 5;
	// 		$peng = 1;
	// 	}

	// 	//可用荷花的价格
	// 	foreach($he as $vo){
	// 		foreach($vo as $v){
	// 			$profit_lotus += ($v['price'] / 3000) * $lotus;
	// 			$profit_peng += ($v['price'] / 3000) * $peng;
	// 		}
	// 	}
	
	// 	$data = array(
	// 		'id' => $uid,
	// 		'lotus' => $row['lotus'] + $profit_lotus,
	// 		'peng' => $row['peng'] + $profit_peng,
	// 		'date_m' => $this_m,
	// 		'profit_lotus' => $row['profit_lotus'] + $profit_lotus,
	// 		'profit_peng' => $row['profit_peng'] + $profit_peng,
	// 		//+1或者直接当前月,直接加当前月，如果网站几个月没有运行则只会增加一个月的邀请收益
	// 	);
	// 	$b = $db->save($data);
	// }

	//分销..
	public function distribution($uid,$mylotus,$hid){
		$tg = get_config('base');
		$db = M('user');
		$list = M('recommend')->select();
		##获得上三级父类
		$parent_id = get_parentid($uid,$list);

		//有效人数
		if($parent_id){
			if($parent_id[0]){
				//上1级有效推广人数，种植荷花算有效人数	
				$parent0 = $this->get_effective($parent_id[0]);
			}
			if($parent_id[1]){
				$parent1 = $this->get_effective($parent_id[1]);
			}
			if($parent_id[2]){
				$parent2 = $this->get_effective($parent_id[2]);
			}
		}
		if(!empty($parent_id)){
	// 一代 下级玩家所收益的10%，莲蓬固定收益0.5个*每玩家，
	// 二代 下级玩家所收益莲藕总数的的5%，莲蓬固定收益0.3个*每玩家/天
	// 三代 下级玩家所收益的1%，莲蓬固定收益0.1个*每玩家/天
    
		# 一代收益10%,莲藕固定0.5；
		$c=$db->find($parent_id[0]);
		$d=$db->find($parent_id[1]);
		$e=$db->find($parent_id[2]);		
        if($c['is_outgoing']==0){
			if($parent_id[0]){
				$info = $db->find($parent_id[0]);
				$lotus = round($mylotus/10,2);
				$peng = 0.5;
				$data = array(
					'id' => $info['id'],
					'lotus' => $info['lotus'] + $lotus,
					'peng' => $info['peng'] + $peng,
					'profit_lotus' => $info['profit_lotus'] + $lotus,
					'profit_peng' => $info['profit_peng'] + $peng,
				);
				##添加记录
				$data2 = array(
					'uid' => $uid,
					'hid' => $hid,
					'did' => $info['id'],
					'lotus' => $lotus,
					'peng' =>  $peng,
					'type' =>  2,
					'add_time' =>  time(),
				);
				$res = $db->save($data);
				$res = M('distribution_log')->add($data2);
			}
        }
		# 二代收益5%,莲藕固定0.3；
        if($d['is_outgoing']==0){
			if($parent_id[1]){
				if($parent1 >= $tg['tg2']){
					$info = $db->find($parent_id[1]);
					$lotus = round($mylotus/20,2);
					$peng = 0.3;
					$data = array(
						'id' => $info['id'],
						'lotus' => $info['lotus'] + $lotus,
						'peng' => $info['peng'] + $peng,
						'profit_lotus' => $info['profit_lotus'] + $lotus,
						'profit_peng' => $info['profit_peng'] + $peng,
					);
					##添加记录
					$data2 = array(
						'uid' => $uid,
						'hid' => $hid,
						'did' => $info['id'],
						'lotus' => $lotus,
						'peng' =>  $peng,
						'type' =>  2,
						'add_time' =>  time(),
					);
					$res = $db->save($data);
					$res = M('distribution_log')->add($data2);
				}
			}
        }
		# 三代收益1%,莲藕固定0.1；
        if($e['is_outgoing']==0){
			if($parent_id[2]){
				if($parent2 >= $tg['tg3']){
					$info = $db->find($parent_id[2]);
					$lotus = round($mylotus/100,2);
					$peng = 0.1;
					$data = array(
						'id' => $info['id'],
						'lotus' => $info['lotus'] + $lotus,
						'peng' => $info['peng'] + $peng,
						'profit_lotus' => $info['profit_lotus'] + $lotus,
						'profit_peng' => $info['profit_peng'] + $peng,
					);
					##添加记录
					$data2 = array(
						'uid' => $uid,
						'hid' => $hid,
						'did' => $info['id'],
						'lotus' => $lotus,
						'peng' =>  $peng,
						'type' =>  2,
						'add_time' =>  time(),
					);
					$res = $db->save($data);
					$res = M('distribution_log')->add($data2);
				}
			}
		}
		}
	}

	//return 有效人数,种植了荷花的算有效人数
	public function get_effective($uid){
		$list = M('recommend')->where(array('invitation_id'=>$uid))->getField('register_id',true);
		$i = 0;
		foreach($list as $v){
			$he = M('generate')->where(array('uid'=>$v))->find();
			if($he){
				$i++;
			}
		}
		return $i ? $i : 0;
	}



	public function satisfy($parent_id,$uid){
		$db = M('user');
		$pinfo = $db->find($parent_id);
		if($pinfo['level'] == 1){
			$b = $db->save(array('id'=>$pinfo['id'],'level'=>$pinfo['level']+1));
			$child = M('recommend')->where(array('invitation_id'=>$uid))->getField('register_id',true);
			$price = M("generate")->where(array('uid'=>array('in',$child)))->getField('price',true);
			$he = array();
			foreach($price as $v){
				$he['lotus'] += $v / 3000 * 12;
				$he['peng'] += $v / 3000 * 3;
			}
		}
		return $he ? $he : array();
	}
	public function offspring($uid){
		$data = M('recommend')->select();
		$list = get_offspring($data,$uid);
		return $list ? $list : array();
	}

	// 激活新用户
	public function member_active (){
		$id =I('post.id');
		$uid = session('userid');
		$rb=M('member_certified')->where(array('user_id' =>$uid ,))->count();
		if($rb<1){
			$this->error('您还未实名，请到个人中心进行实名');die;
		}
		if(empty($id)){
			$this->error('操作失败');die;
		}
		$userdb = M('user');
		##查询激活者莲藕数是否足够，
		$row = M('recommend')->where('register_id='.$id)->find();
		$user = $userdb->where('id ='.$row['invitation_id'])->find();
		if($user['lotus'] <330){
			$this->ajaxreturn(array('status'=>'n','info'=>'拥有莲藕数不足，无法激活'));die;
		}
		$user['lotus'] = $user['lotus'] -330;
		$mactive = array(
			'consume'=>330,
			'user_id'=>$user['id'],
			'active_id'=>$id,
			'add_time'=>time()
		);
		$res =$userdb->save($user);
		if($res){
			$update['lotus'] = 300;
			$update['is_active'] = 1;
			$res = $userdb->where('id ='.$row['register_id'])->save($update);
			$res2=M('mactive')->add($mactive);
		}
		if($res){
			$this->ajaxreturn(array('status'=>'y','info'=>'激活成功'));die;
		}else{
			$this->ajaxreturn(array('status'=>'n','info'=>'激活失败'));die;
		}
	}
    //激活二三级用户
	public function member_active2(){
		$id =I('post.id');
		if(empty($id)){
			$this->error('操作失败');die;
		}
		$userdb = M('user');
		$uid = session('userid');
		##查询激活者莲藕数是否足够，
		$row = M('recommend')->where('register_id='.$id)->find(); 
		$user = $userdb->where('id ='.$uid)->find();
		// dump($row);dump($user);die;
		if($user['lotus'] <330){
			$this->ajaxreturn(array('status'=>'n','info'=>'拥有莲藕数不足，无法激活'));die;
		}
		$user['lotus'] = $user['lotus'] -330;
		$mactive = array(
			'consume'=>330,
			'user_id'=>$user['id'],
			'active_id'=>$id,
			'add_time'=>time()
		);
		$res =$userdb->save($user);
		if($res){
			$update['lotus'] = 300;
			$update['is_active'] = 1;
			$res = $userdb->where('id ='.$row['register_id'])->save($update);
			$res2=M('mactive')->add($mactive);
		}
		if($res){
			$this->ajaxreturn(array('status'=>'y','info'=>'激活成功'));die;
		}else{
			$this->ajaxreturn(array('status'=>'n','info'=>'激活失败'));die;
		}
	}
	##种植莲藕
	public function add_lotus(){
		$num = floatval(trim(I('post.num')));
		$uid = session('userid');
		$lotus_id =floatval(trim(I('post.lotus_id')));
		if(empty($num)||empty($lotus_id)){
			$this->error('操作失败');die;
		}
		$userdb = M('user');
		##查询莲藕数是否足够，
		$user = $userdb->where('id ='.$uid)->find();
		$he = M('generate')->find($lotus_id);       
		if($user['lotus'] < $num){
			$this->ajaxreturn(array('status'=>'n','info'=>'种植莲藕数超过拥有莲藕数'));die;
		}
		if($he['zeng_lotus_new']+$he['zeng_lotus_old']+$num > $he['price']*10){
			$this->ajaxreturn(array('status'=>'n','info'=>'增值莲藕数超过不能超过'. $he['price']*10));die;
		}
		##增加莲藕数
		$he['zeng_lotus_old'] = $he['zeng_lotus_new']+$he['zeng_lotus_old'];
		$he['zeng_lotus_new'] = $num;
		$he['zeng_time'] = time();
		$res = M('generate')->save($he);
		if($res){
			##扣除金额
			$lotus = $user['lotus'] - $num;
			$res =$userdb->where('id ='.$uid)->setField('lotus',$lotus);
		}
		if($res){
			$this->ajaxreturn(array('status'=>'y','info'=>'增苗成功'));die;
		}else{
			$this->ajaxreturn(array('status'=>'n','info'=>'增苗失败'));die;
		}
	}

	##提现
	public function get_money(){
        $hftx=M('config')->where('id='.'11')->find();
		if($hftx['value']==0){
			$this->error('提现功能暂时锁定，请等待管理员开放');die;
		}
		$num = floatval(trim(I('post.num')));
		$data = I('post.');
		$uid = session('userid');
        $count_lv =   M('generate')->where(array('uid'=>$uid,'state'=>1,'hid'=>1))->count();
		if($count_lv<2){
			$this->error('当前荷塘莲花交易上限为150，提现失败。');die;
		}
		$rb=M('member_certified')->where(array('user_id' =>$uid ,))->count();
		if($rb<1){
			$this->error('您还未实名，请到个人中心进行实名');die;
		}
		$userdb = M('user');
		$alipay=M('config')->where('id='.'9')->find();
		if($alipay['value']==0){
			$this->error('今日名额已满，请明天再试');die;
		}
		if(empty($data)){
			$this->error('操作失败');die;
		}
		##查看今天是否已提现
		#零时时间戳
		$today = strtotime(date("Y-m-d",time()));
		$rule['add_time']= array('GT',$today);
		$rule['uid'] = $uid;
		$rule['cztxtype']=0;
		$res = M('get_money')->where($rule)->find();
		if(!empty($res)){
			$this->ajaxreturn(array('status'=>'n','info'=>'今天已提现一次，请明天再试'));die;
		}
		##单次提现额度不大于200
		if($num > 200){
			$this->ajaxreturn(array('status'=>'n','info'=>'提现莲藕数不能大于200'));die;
		}
		##查询莲藕数是否足够，
		$user = $userdb->where('id ='.$uid)->find();
		if($user['lotus'] < $num){
			$this->ajaxreturn(array('status'=>'n','info'=>'提现莲藕数超过拥有莲藕数'));die;
		}
		$htlouts =M('generate')->where('uid='.$uid)->count();
		if($htlouts<1){
			$this->ajaxreturn(array('status'=>'n','info'=>'荷塘未种植荷花，无法提现'));die;
		}
		##减少用户莲藕数
		$user['lotus'] = $user['lotus'] - $num*1.2;
		$res = $userdb->save($user);
		##手续费 20%
		$lotus_fees = round($num/5,2);
		
		#实际提现
		$lotus_rel = $num -$lotus_fees;
		$updata = array(
			'uid'=>$uid,
			'type'=>0,
			'lotus'=>$num,
			'lotus_fees'=>$lotus_fees,
			'lotus_rel'=>$lotus_rel,
			'name'=>$data['name'],
			'alipay'=>$data['pay'],
			'cztxtype'=>0,
			'add_time'=>time()
		);

		if($res){
			$res = M('get_money')->add($updata);
		}
		if($res){
			$this->ajaxreturn(array('status'=>'y','info'=>'提现申请成功'));die;
		}else{
			$this->ajaxreturn(array('status'=>'n','info'=>'提现申请失败'));die;
		}
	}

    ##短信验证码

	public function telephone_fare(){
		$num = floatval(trim(I('post.num')));
		$data = I('post.');
		$uid = session('userid');
		$rb=M('member_certified')->where(array('user_id' =>$uid ,))->count();
        $count_lv =   M('generate')->where(array('uid'=>$uid,'state'=>1,'hid'=>1))->count();
		if($count_lv<2){
			$this->error('当前荷塘莲花交易上限为150，兑换失败');die;
		}
		if($rb<1){
			$this->error('您还未实名，请到个人中心进行实名');die;
		}
		$userdb = M('user');
		$hftx=M('config')->where('id='.'10')->find();
		if($hftx['value']==0){
			$this->error('今日名额已满，请明天再试');die;
		}
		if(empty($data)){
			$this->error('操作失败');die;
		}
		##查看今天是否已提现
		#零时时间戳
		$today = strtotime(date("Y-m-d",time()));
		$rule['add_time']= array('GT',$today);
		$rule['uid'] = $uid;
		$rule['cztxtype']=1;
		$res = M('get_money')->where($rule)->find();
		if(!empty($res)){
			$this->ajaxreturn(array('status'=>'n','info'=>'今天已申请一次，请明天再试'));die;
		}
		##单次提现额度不大于200
		if($num > 200){
			$this->ajaxreturn(array('status'=>'n','info'=>'充值话费莲藕数不能大于200'));die;
		}
		##查询莲藕数是否足够，
		$user = $userdb->where('id ='.$uid)->find();
		if($user['lotus'] < $num){
			$this->ajaxreturn(array('status'=>'n','info'=>'充值话费莲藕数超过拥有莲藕数'));die;
		}
		$htlouts =M('generate')->where('uid='.$uid)->count();
		if($htlouts<1){
			$this->ajaxreturn(array('status'=>'n','info'=>'荷塘未种植荷花，无法充值话费'));die;
		}
		##减少用户莲藕数
		$user['lotus'] = $user['lotus'] - $num*1.2;
		$res = $userdb->save($user);
		##手续费 20%
		$lotus_fees = round($num/5,2);
		
		#实际提现
		$lotus_rel = $num -$lotus_fees;
		$updata = array(
			'uid'=>$uid,
			'type'=>0,
			'lotus'=>$num,
			'lotus_fees'=>$lotus_fees,
			'lotus_rel'=>$num,
			'name'=>$data['name'],
			'alipay'=>$data['tel'],
			'cztxtype'=>1,
			'add_time'=>time()
		);

		if($res){
			$res = M('get_money')->add($updata);
		}
		if($res){
			$this->ajaxreturn(array('status'=>'y','info'=>'充值申请成功'));die;
		}else{
			$this->ajaxreturn(array('status'=>'n','info'=>'充值申请失败'));die;
		}
	}

	####
	#实名认证
	public function	member_certified(){
		$data = I('post.');
		$uid = session('userid');
		$data['user_id'] = $uid;
		$accountNo = $data['account_no'];
		$bankPreMobile = $data['bank_pre_mobile'];
		$idCardCode = $data['id_card_code'];
		$bank_name=$data['bank_name'];
		$name = $data['name'];
		$rb=M('member_certified')->where(array('user_id' =>$uid ,))->count();
		$rb2=M('member_certified')->where(array('id_card_code' =>$idCardCode ,))->count();
        if($data['code']!= $_SESSION['code']){
            $this->error('验证码错误!');die;
            }
		if(empty($name) || empty($idCardCode) ||empty($bankPreMobile) ||empty($accountNo) || empty($bank_name)){
			$this->ajaxreturn(array('status'=>'n','info'=>'缺少必传参数a'));
		}elseif($rb>=2){
				$this->ajaxreturn(array('status'=>'n','info'=>'认证信息已存在'));
		}elseif($rb2>=1){
				$this->ajaxreturn(array('status'=>'n','info'=>'该身份证已被认证'));

		}else{
			$res = $this->cardFourElement($accountNo,$bankPreMobile,$idCardCode,$name);
			if($res['result']['result'] == 'T'){
				###认证成功，存储数据
				$data['add_time']=time();
				$res = M('member_certified')->add($data);
				if($res){
					$this->ajaxreturn(array('status'=>'y','info'=>'成功'));
				}else{
					$this->ajaxreturn(array('status'=>'y','info'=>'存储数据失败'));
				}
			}else{
				$this->ajaxreturn(array('status'=>'n','info'=>'认证信息不匹配'));
			}
		}
	}

}






//         
//         
//         
//               
//                       .::::.  
//                     .::::::::.  
//                    :::::::::::  
//                 ..:::::::::::'  
//              '::::::::::::'  
//                .::::::::::  
//           '::::::::::::::..  
//                ..::::::::::::.  
//              ``::::::::::::::::  
//               ::::``:::::::::'        .:::.  
//              ::::'   ':::::'       .::::::::.  
//            .::::'      ::::     .:::::::'::::.  
//           .:::'       :::::  .:::::::::' ':::::.  
//          .::'        :::::.:::::::::'      ':::::.  
//         .::'         ::::::::::::::'         ``::::.  
//     ...:::           ::::::::::::'              ``::.  
//    ```` ':.          ':::::::::'                  ::::..  
//                       '.:::::'                    ':'````..  
//               佛曰：
//               天天改  日日忙
//               相顾无言惟有泪千行