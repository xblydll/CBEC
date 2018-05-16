<?php
namespace Home\Controller;
use Think\Controller;
class BaseController extends Controller {
	public function _initialize(){
		if(!session('?userid')){
			$this->redirect('Index/index');
		}
		$userid = session('userid');
		$is_open = open_time();
        if($is_open == 1){
          $this->error('00:00~03:00，系统拆分时间，限制进入！','',30);
          die;
        }
		$row = M('user')->find($userid);
        ##判断账户是否在别处登录
        if($row['login_rand'] != session('login_rand')){
            session('userid',null);
            $this->error('您的账号已在别处登录,请重新登录','/Home/Index/index');
            die;
        }
        ##判断账号是否被封停
        if($row['is_stop'] == 1){
        	session('userid',null);
        	$this->error('玩家已封停，请联系客服','/Home/Index/index');
			die;
        }
        
        ##玩家出局
		// if($row['is_outgoing'] ==1){
		// 	$this->error('玩家已出局，请联系客服');
		// 	die;
		// }
		$result = $this->is_outGoing($row,$userid);
		// if($result == 999){
		// 	$this->error('玩家已出局，请联系客服');
		// 	die;
		// }

	}


	#规则
	#静态玩家,							总收益达到:45000
	#直推人数达到10人                    总收益达到:60000
	#直推人数达到20人                    总收益达到:80000
	#直推人数达到30人                    总收益达到:100000
	#直推人数达到30人并且团队人数300人    总收益达到:120000
	#直推人数达到30人并且团队人数500人    总收益达到:160000
	#直推人数达到30人并且团队人数1000人   总收益达到:200000
	#直推人数达到30人并且团队人数3000人   总收益达到:380000
	#直推人数达到30人并且团队人数5000人   总收益达到:450000
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
	##出局判断
	public function is_outGoing($row,$uid){
		#直推人数
		$cc = M('recommend')->where('invitation_id	='.$uid)->select();
		$push_people= array();
        foreach ($cc as $k => &$v) {
        	$res=M('user')->where(array('id' =>$v['register_id'],'is_active' =>'1'))->find();
        	if ($res) {
        		$push_people[$k] = $res;
        	}
        	
        }
        $push_people=count($push_people);
		$data = M('recommend')->select();
		$cc1 = get_offspring_3($data,$uid);
        foreach ($cc1 as $k => &$v) {
        	$res=M('user')->where(array('id' =>$v['register_id'],'is_active' =>'1'))->find();
        	if ($res) {
        		$list[$k] = $res;
        	}
        	
        }
		#激活团队人数	
		$group_people = count($list);
		##总收益莲藕数
		$lotus_count = $row['lotus'];
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
		$lotus_count=$he['income_active']+$he['income_static'];
		if($push_people > 0 && $push_people < 10 && $lotus_count >= 45000){
		    $lotus_count=$he['income_active']+$he['income_static'];
		}elseif ($push_people >= 10 && $push_people < 20 && $lotus_count >= 60000) {
		    $lotus_count=$he['income_active']+$he['income_static'];
		}elseif ($push_people >= 20 && $push_people < 30 && $lotus_count >= 80000) {
		    $lotus_count=$he['income_active']+$he['income_static'];
		}elseif ($push_people >= 30 && $group_people < 300 && $lotus_count >= 100000) {
		    $lotus_count=$he['income_active']+$he['income_static'];
		}elseif ($push_people >= 30 && $group_people < 500 && $lotus_count >= 120000) {
		    $lotus_count=$he['income_active']+$he['income_static'];
		}elseif ($push_people >= 30 && $group_people < 1000 && $lotus_count >= 160000) {
		    $lotus_count=$he['income_active']+$he['income_static'];
		}elseif ($push_people >= 30 && $push_people < 3000 && $lotus_count >= 200000) {
		    $lotus_count=$he['income_active']+$he['income_static'];
		}elseif ($push_people >= 30 && $push_people < 5000 && $lotus_count >= 380000) {
		    $lotus_count=$he['income_active']+$he['income_static'];
		}elseif ($push_people >= 30 && $push_people >= 5000 && $lotus_count >= 450000) {
		    $lotus_count=$he['income_active']+$he['income_static'];
		}else{
			return 1;
		}
		$row['is_outgoing'] = 1;
		$res = M('user')->save($row);
		return 999;
	}



	###入口 日复利，统计昨天荷花生长的莲藕数
	public function interestByDay(){
		$uid = session('userid');
		##统计昨天以前池塘荷花数量
		#今天零时时间戳
		$today = strtotime(date("Y-m-d",time()));
		$condition['add_time'] =array('ELT',$today);
		$condition['uid'] = $uid;
		$he_list = M('generate')->where($condition)->select();

		###
		#莲藕 == 每天固定生产莲藕总数的日利率的2%
		#莲蓬 == 每天固定生产莲藕总数的日利率的1%
		foreach ($he_list as $k => &$v) {
			##莲藕增苗时间控制，
			#增苗时间昨天以前算为总莲藕数
			if($v['zeng_time'] < $today){
				$count = $v['price'] +$v['zeng_lotus_old']+$v['zeng_lotus_new'];
			}else {
				$count = $v['price'] + $v['zeng_lotus_old'];
			}
			##如果今天采摘过，莲蓬，莲藕为零
			if($v['pick_time'] > $today){
				$count = 0;
			}
			
			$v['lotus'] = $count*2/100;
			$v['peng'] = $count/100;
			$res =M('generate')->save($v);
		}
		// dump($he_list);
		
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

        #####
        #### 银行卡号四元素实名认证
        ###
        ##	appkey=24865213
        #	AppSecret=eb3f69d0f74e151fcee4fcdc891bbfb0
        #	appcode = 76a02d50bb7b47659cef97d0e32b7f61

        public function cardFourElement($accountNo,$bankPreMobile,$idCardCode,$name){
		    $host = "http://aliyuncardby4element.haoservice.com";
		    $path = "/creditop/BankCardQuery/QryBankCardBy4Element";
		    $method = "GET";
		    $appcode = "76a02d50bb7b47659cef97d0e32b7f61";
		    $headers = array();
		    array_push($headers, "Authorization:APPCODE " . $appcode);
		    // $querys = "accountNo=6212261102005817757&bankPreMobile=18600174444&idCardCode=130321198804010180&name=%E5%BC%A0%E5%BC%BA";
		    $post_data['accountNo'] = $accountNo;
		    $post_data['bankPreMobile'] = $bankPreMobile;
		    $post_data['idCardCode'] = $idCardCode;
		    $post_data['name'] = $name;
		    $querys = http_build_query($post_data);

		    $bodys = "";
		    $url = $host . $path . "?" . $querys;

		    $curl = curl_init();
		    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
		    curl_setopt($curl, CURLOPT_URL, $url);
		    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		    curl_setopt($curl, CURLOPT_FAILONERROR, false);
		    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		    curl_setopt($curl, CURLOPT_HEADER, false);
		    if (1 == strpos("$".$host, "https://"))
		    {
		        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		    }
		    $res = curl_exec($curl);
	    	$res = json_decode($res, true);
		    return $res;
        }

}